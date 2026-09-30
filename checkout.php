<?php
require 'config/db.php';
requireLogin();
$uid = (int)$_SESSION['user_id'];
$pageTitle = 'Checkout';

$items = db_all('SELECT c.id AS cart_id, c.size, c.quantity, p.*, b.name AS brand_name, s.stock AS size_stock FROM cart c JOIN products p ON p.id=c.product_id JOIN brands b ON b.id=p.brand_id LEFT JOIN product_sizes s ON s.product_id=c.product_id AND s.size=c.size WHERE c.user_id=?', [$uid]);
if (!$items) { flash('Your bag is empty.', 'err'); redirect('cart.php'); }
$subtotal = 0; foreach ($items as $i) { $subtotal += price_now($i) * $i['quantity']; }
$shipping = $subtotal >= FREE_SHIP_OVER ? 0 : SHIP_FEE;
$total = $subtotal + $shipping;
$addrs = db_all('SELECT * FROM addresses WHERE user_id=? ORDER BY id DESC', [$uid]);
$cards = db_all('SELECT * FROM payment_methods WHERE user_id=? ORDER BY id DESC', [$uid]);
$states = ['Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Labuan','Putrajaya'];
$errors = [];

function luhn($n) { $s = 0; $alt = false; for ($i = strlen($n) - 1; $i >= 0; $i--) { $d = (int)$n[$i]; if ($alt) { $d *= 2; if ($d > 9) $d -= 9; } $s += $d; $alt = !$alt; } return $s % 10 === 0; }
function card_brand($n) { if ($n[0] === '4') return 'Visa'; if (preg_match('/^(5[1-5]|2[2-7])/', $n)) return 'Mastercard'; if (preg_match('/^3[47]/', $n)) return 'Amex'; if (strpos($n, '62') === 0) return 'UnionPay'; return 'Card'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // ---- address ----
    $choice = $_POST['addr_choice'] ?? 'new';
    $ship = null;
    if ($choice !== 'new' && ctype_digit($choice)) {
        $ship = db_one('SELECT * FROM addresses WHERE id=? AND user_id=?', [(int)$choice, $uid]);
    }
    if (!$ship) {
        $ship = ['recipient'=>trim($_POST['recipient'] ?? ''), 'phone'=>trim($_POST['phone'] ?? ''), 'line1'=>trim($_POST['line1'] ?? ''), 'line2'=>trim($_POST['line2'] ?? ''), 'city'=>trim($_POST['city'] ?? ''), 'postcode'=>trim($_POST['postcode'] ?? ''), 'state'=>trim($_POST['state'] ?? ''), 'country'=>'Malaysia'];
        foreach (['recipient','phone','line1','city','postcode','state'] as $f) { if ($ship[$f] === '') { $errors[] = 'Please complete your delivery address.'; break; } }
        if (!preg_match('/^\d{5}$/', $ship['postcode']) && !$errors) $errors[] = 'Postcode must be 5 digits.';
    }
    // ---- payment ----
    $pay = $_POST['pay'] ?? '';
    if (!in_array($pay, ['card','fpx','tng','grabpay','cod'], true)) $errors[] = 'Please choose a payment method.';
    $last4 = null; $saveCard = null;
    if ($pay === 'card') {
        $cc = $_POST['card_choice'] ?? 'new';
        if ($cc !== 'new' && ctype_digit($cc)) {
            $saved = db_one('SELECT * FROM payment_methods WHERE id=? AND user_id=?', [(int)$cc, $uid]);
            if ($saved) { $last4 = $saved['last4']; } else { $errors[] = 'Saved card not found.'; }
        } else {
            $num = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
            $holder = trim($_POST['card_holder'] ?? '');
            $exp = trim($_POST['card_exp'] ?? '');
            $cvv = preg_replace('/\D/', '', $_POST['card_cvv'] ?? '');
            $em = 0; $ey = 0;
            if (preg_match('#^(\d{2})/(\d{2})$#', $exp, $m)) { $em = (int)$m[1]; $ey = 2000 + (int)$m[2]; }
            if (strlen($num) < 13 || strlen($num) > 19 || !luhn($num)) $errors[] = 'Card number looks invalid. (For a demo, 4242 4242 4242 4242 works.)';
            elseif ($holder === '') $errors[] = 'Enter the name on the card.';
            elseif ($em < 1 || $em > 12 || ($ey * 12 + $em) < ((int)date('Y') * 12 + (int)date('n'))) $errors[] = 'Card expiry is invalid or expired.';
            elseif (strlen($cvv) < 3 || strlen($cvv) > 4) $errors[] = 'Enter the 3 or 4 digit security code.';
            else { $last4 = substr($num, -4); $saveCard = ['brand'=>card_brand($num), 'holder'=>$holder, 'last4'=>$last4, 'em'=>$em, 'ey'=>$ey]; }
            // the full number and CVV are used for validation only and are never stored
        }
    }

    if (!$errors) {
        global $conn;
        $conn->begin_transaction();
        try {
            // lock + verify stock
            foreach ($items as $i) {
                $r = db_one('SELECT stock FROM product_sizes WHERE product_id=? AND size=? FOR UPDATE', [(int)$i['id'], $i['size']]);
                if (!$r || (int)$r['stock'] < (int)$i['quantity']) { throw new Exception($i['brand_name'] . ' ' . $i['name'] . ' (' . $i['size'] . ') is no longer available in that quantity.'); }
            }
            $addrText = $ship['line1'] . ($ship['line2'] ? ', ' . $ship['line2'] : '') . ', ' . $ship['postcode'] . ' ' . $ship['city'] . ', ' . $ship['state'] . ', ' . ($ship['country'] ?? 'Malaysia');
            $status = ($pay === 'card') ? 'paid' : 'pending';   // DEMO: real gateways would confirm payment here
            $oid = db_run('INSERT INTO orders (user_id, subtotal, shipping, total, payment_method, card_last4, ship_name, ship_phone, ship_address, status) VALUES (?,?,?,?,?,?,?,?,?,?)',
                [$uid, (float)$subtotal, (float)$shipping, (float)$total, $pay, $last4, $ship['recipient'], $ship['phone'], $addrText, $status]);
            foreach ($items as $i) {
                db_run('INSERT INTO order_items (order_id, product_id, name, brand, size, price, quantity) VALUES (?,?,?,?,?,?,?)', [$oid, (int)$i['id'], $i['name'], $i['brand_name'], $i['size'], price_now($i), (int)$i['quantity']]);
                db_run('UPDATE product_sizes SET stock = stock - ? WHERE product_id=? AND size=?', [(int)$i['quantity'], (int)$i['id'], $i['size']]);
            }
            db_run('DELETE FROM cart WHERE user_id=?', [$uid]);
            if (!empty($_POST['save_addr']) && ($choice === 'new')) {
                db_run('INSERT INTO addresses (user_id, recipient, phone, line1, line2, city, postcode, state, country) VALUES (?,?,?,?,?,?,?,?,?)', [$uid, $ship['recipient'], $ship['phone'], $ship['line1'], $ship['line2'], $ship['city'], $ship['postcode'], $ship['state'], 'Malaysia']);
            }
            if ($saveCard && !empty($_POST['save_card'])) {
                db_run('INSERT INTO payment_methods (user_id, brand, holder, last4, exp_month, exp_year) VALUES (?,?,?,?,?,?)', [$uid, $saveCard['brand'], $saveCard['holder'], $saveCard['last4'], $saveCard['em'], $saveCard['ey']]);
            }
            $conn->commit();
            redirect('order_success.php?id=' . $oid);
        } catch (Exception $ex) { $conn->rollback(); $errors[] = $ex->getMessage(); }
    }
}
$post = fn($k) => e($_POST[$k] ?? '');
include 'includes/header.php';
?>
<div class="page">
  <h1>Checkout</h1>
  <?php foreach ($errors as $er): ?><div class="err-box"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="two-col"><?= csrf_field() ?>
    <div>
      <div class="box"><h3>Delivery address</h3>
        <?php foreach ($addrs as $k => $a): ?>
          <label class="opt"><input type="radio" name="addr_choice" value="<?= (int)$a['id'] ?>" <?= $k === 0 && ($_POST['addr_choice'] ?? '') !== 'new' ? 'checked' : '' ?>><span><b><?= e($a['recipient']) ?></b><small><?= e($a['line1'] . ($a['line2'] ? ', ' . $a['line2'] : '') . ', ' . $a['postcode'] . ' ' . $a['city'] . ', ' . $a['state']) ?><br><?= e($a['phone']) ?></small></span></label>
        <?php endforeach; ?>
        <?php $newChecked = !$addrs || ($_POST['addr_choice'] ?? '') === 'new'; ?>
        <label class="opt"><input type="radio" name="addr_choice" value="new" <?= $newChecked ? 'checked' : '' ?>><span><b>Use a new address</b></span></label>
        <div id="new-addr" <?= $newChecked ? '' : 'hidden' ?>>
          <div class="grid2">
            <div><label>Recipient name</label><input type="text" name="recipient" value="<?= $post('recipient') ?: e($_SESSION['full_name']) ?>"></div>
            <div><label>Phone</label><input type="tel" name="phone" value="<?= $post('phone') ?>"></div>
          </div>
          <label>Address line 1</label><input type="text" name="line1" value="<?= $post('line1') ?>" placeholder="Unit, street name">
          <label>Address line 2 (optional)</label><input type="text" name="line2" value="<?= $post('line2') ?>">
          <div class="grid2">
            <div><label>City</label><input type="text" name="city" value="<?= $post('city') ?>"></div>
            <div><label>Postcode</label><input type="text" name="postcode" value="<?= $post('postcode') ?>" inputmode="numeric" maxlength="5"></div>
          </div>
          <label>State</label><select name="state"><option value="">Select state</option><?php foreach ($states as $s): ?><option <?= ($_POST['state'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
          <label style="display:flex;gap:8px;align-items:center;font-weight:400"><input type="checkbox" name="save_addr" value="1" checked style="width:auto"> Save this address for next time</label>
        </div>
      </div>

      <div class="box"><h3>Payment method</h3>
        <?php $pm = $_POST['pay'] ?? 'card'; ?>
        <label class="opt"><input type="radio" name="pay" value="card" <?= $pm==='card'?'checked':'' ?>><span><b>Credit / debit card</b><small>Visa, Mastercard, UnionPay</small></span></label>
        <div id="card-box" <?= $pm==='card' ? '' : 'hidden' ?>>
          <?php foreach ($cards as $k => $c): ?>
            <label class="opt"><input type="radio" name="card_choice" value="<?= (int)$c['id'] ?>" <?= $k===0 && ($_POST['card_choice'] ?? '') !== 'new' ? 'checked' : '' ?>><span><b><?= e($c['brand']) ?> ending <?= e($c['last4']) ?></b><small><?= e($c['holder']) ?> &middot; expires <?= sprintf('%02d/%02d', $c['exp_month'], $c['exp_year'] % 100) ?></small></span></label>
          <?php endforeach; ?>
          <?php $newCard = !$cards || ($_POST['card_choice'] ?? '') === 'new'; ?>
          <label class="opt"><input type="radio" name="card_choice" value="new" <?= $newCard ? 'checked' : '' ?>><span><b>Use a new card</b></span></label>
          <div id="new-card" <?= $newCard ? '' : 'hidden' ?>>
            <label>Card number</label><input type="text" id="card_number" name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456">
            <label>Name on card</label><input type="text" name="card_holder" autocomplete="cc-name" value="<?= $post('card_holder') ?>">
            <div class="grid2"><div><label>Expiry (MM/YY)</label><input type="text" id="card_exp" name="card_exp" autocomplete="cc-exp" placeholder="MM/YY" maxlength="5"></div>
              <div><label>CVV</label><input type="password" name="card_cvv" autocomplete="cc-csc" inputmode="numeric" maxlength="4"></div></div>
            <label style="display:flex;gap:8px;align-items:center;font-weight:400"><input type="checkbox" name="save_card" value="1" style="width:auto"> Save card (we only keep the last 4 digits)</label>
          </div>
        </div>
        <label class="opt"><input type="radio" name="pay" value="fpx" <?= $pm==='fpx'?'checked':'' ?>><span><b>FPX online banking</b><small>Maybank2u, CIMB Clicks, Public Bank and more</small></span></label>
        <label class="opt"><input type="radio" name="pay" value="tng" <?= $pm==='tng'?'checked':'' ?>><span><b>Touch 'n Go eWallet</b></span></label>
        <label class="opt"><input type="radio" name="pay" value="grabpay" <?= $pm==='grabpay'?'checked':'' ?>><span><b>GrabPay</b></span></label>
        <label class="opt"><input type="radio" name="pay" value="cod" <?= $pm==='cod'?'checked':'' ?>><span><b>Cash on delivery</b></span></label>
        <p class="hint">Demo shop: no real money is charged. Card CVV and full numbers are never saved.</p>
      </div>
    </div>

    <div class="summary">
      <h3 style="margin-bottom:12px">Your order</h3>
      <?php foreach ($items as $i): ?><div class="row" style="font-size:14px"><span><?= e($i['brand_name'] . ' ' . $i['name']) ?> <small>(<?= e($i['size']) ?> x<?= (int)$i['quantity'] ?>)</small></span><span><?= money(price_now($i) * $i['quantity']) ?></span></div><?php endforeach; ?>
      <div class="row" style="margin-top:8px"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
      <div class="row"><span>Delivery</span><span><?= $shipping ? money($shipping) : 'Free' ?></span></div>
      <div class="row tot"><span>Total</span><span><?= money($total) ?></span></div>
      <button class="btn btn-red btn-block" style="margin-top:18px" type="submit">Place order</button>
      <p class="hint" style="margin-top:10px"><a href="<?= e(url('cart.php')) ?>" class="small-link">Back to bag</a></p>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
