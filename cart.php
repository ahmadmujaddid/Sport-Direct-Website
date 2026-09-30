<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        if (!isLoggedIn()) { flash('Please log in to add items to your bag.', 'err'); redirect('login.php?next=' . urlencode('product.php?id=' . $pid)); }
        $uid = (int)$_SESSION['user_id'];
        $size = trim($_POST['size'] ?? '');
        $qty = max(1, min(10, (int)($_POST['quantity'] ?? 1)));
        $row = db_one('SELECT stock FROM product_sizes WHERE product_id = ? AND size = ?', [$pid, $size]);
        if (!$row || (int)$row['stock'] <= 0) { flash('That size is sold out.', 'err'); redirect('product.php?id=' . $pid); }
        $have = db_one('SELECT quantity FROM cart WHERE user_id=? AND product_id=? AND size=?', [$uid, $pid, $size]);
        $newQty = min((int)$row['stock'], ($have['quantity'] ?? 0) + $qty);
        db_run('INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE quantity = ?', [$uid, $pid, $size, $newQty, $newQty]);
        flash('Added to your bag.');
    } else {
        requireLogin();
        $uid = (int)$_SESSION['user_id']; $cid = (int)($_POST['cart_id'] ?? 0);
        if ($action === 'update') {
            $line = db_one('SELECT c.id, s.stock FROM cart c JOIN product_sizes s ON s.product_id=c.product_id AND s.size=c.size WHERE c.id=? AND c.user_id=?', [$cid, $uid]);
            if ($line) { $q = max(1, min((int)$line['stock'], (int)($_POST['quantity'] ?? 1))); db_run('UPDATE cart SET quantity=? WHERE id=?', [$q, $cid]); }
        } elseif ($action === 'remove') {
            db_run('DELETE FROM cart WHERE id=? AND user_id=?', [$cid, $uid]);
        }
    }
    redirect('cart.php');
}

requireLogin();
$uid = (int)$_SESSION['user_id'];
$items = db_all('SELECT c.id AS cart_id, c.size, c.quantity, p.*, b.name AS brand_name, s.stock AS size_stock FROM cart c JOIN products p ON p.id=c.product_id JOIN brands b ON b.id=p.brand_id LEFT JOIN product_sizes s ON s.product_id=c.product_id AND s.size=c.size WHERE c.user_id=? ORDER BY c.id DESC', [$uid]);
$subtotal = 0; foreach ($items as $i) { $subtotal += price_now($i) * $i['quantity']; }
$shipping = ($subtotal == 0 || $subtotal >= FREE_SHIP_OVER) ? 0 : SHIP_FEE;
$pageTitle = 'Bag';
include 'includes/header.php';
?>
<div class="page">
  <h1>Your bag</h1>
  <?php if (!$items): ?>
    <p style="margin-bottom:20px">Your bag is empty.</p><a class="btn" href="<?= e(url('shop.php')) ?>">Start shopping</a>
  <?php else: ?>
  <div class="two-col">
    <div>
      <?php foreach ($items as $i): ?>
        <div class="line">
          <a href="<?= e(url('product.php?id=' . (int)$i['id'])) ?>"><img src="<?= e(img_src($i)) ?>" alt=""></a>
          <div>
            <strong><?= e($i['brand_name']) ?></strong><br><?= e($i['name']) ?>
            <div class="meta"><?= e(size_label($i['size_type'])) ?>: <?= e($i['size']) ?></div>
            <?php if ((int)$i['size_stock'] <= 0): ?><div class="red" style="font-size:13px">This size just sold out. Please remove it.</div><?php endif; ?>
            <form class="inline" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="cart_id" value="<?= (int)$i['cart_id'] ?>">
              <input type="number" name="quantity" value="<?= (int)$i['quantity'] ?>" min="1" max="10" aria-label="Quantity"><button class="link-btn">Update</button></form>
            <form class="inline" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="cart_id" value="<?= (int)$i['cart_id'] ?>"><button class="link-btn">Remove</button></form>
          </div>
          <div><strong><?= money(price_now($i) * $i['quantity']) ?></strong></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="summary">
      <h3 style="margin-bottom:12px">Order summary</h3>
      <div class="row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
      <div class="row"><span>Delivery</span><span><?= $shipping ? money($shipping) : 'Free' ?></span></div>
      <?php if ($shipping): ?><p class="hint">Spend <?= money(FREE_SHIP_OVER - $subtotal) ?> more for free delivery.</p><?php endif; ?>
      <div class="row tot"><span>Total</span><span><?= money($subtotal + $shipping) ?></span></div>
      <a class="btn btn-red btn-block" style="margin-top:18px" href="<?= e(url('checkout.php')) ?>">Checkout</a>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
