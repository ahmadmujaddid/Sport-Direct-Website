<?php
require 'config/db.php';
requireLogin();
$uid = (int)$_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'profile') {
        $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? '');
        if ($name === '') { flash('Name cannot be empty.', 'err'); }
        else { db_run('UPDATE users SET full_name=?, phone=? WHERE id=?', [$name, $phone, $uid]); $_SESSION['full_name'] = $name; flash('Profile saved.'); }
        redirect('account.php?tab=profile');
    }
    if ($a === 'password') {
        $u = db_one('SELECT password FROM users WHERE id=?', [$uid]);
        if (!password_verify($_POST['current'] ?? '', $u['password'])) flash('Current password is wrong.', 'err');
        elseif (strlen($_POST['new'] ?? '') < 8) flash('New password must be at least 8 characters.', 'err');
        else { db_run('UPDATE users SET password=? WHERE id=?', [password_hash($_POST['new'], PASSWORD_DEFAULT), $uid]); flash('Password changed.'); }
        redirect('account.php?tab=profile');
    }
    if ($a === 'add_addr') {
        $f = []; foreach (['recipient','phone','line1','line2','city','postcode','state'] as $k) $f[$k] = trim($_POST[$k] ?? '');
        if (!$f['recipient'] || !$f['phone'] || !$f['line1'] || !$f['city'] || !preg_match('/^\d{5}$/', $f['postcode']) || !$f['state']) flash('Please complete every required address field (postcode is 5 digits).', 'err');
        else { db_run('INSERT INTO addresses (user_id, recipient, phone, line1, line2, city, postcode, state) VALUES (?,?,?,?,?,?,?,?)', [$uid, $f['recipient'], $f['phone'], $f['line1'], $f['line2'], $f['city'], $f['postcode'], $f['state']]); flash('Address saved.'); }
        redirect('account.php?tab=addresses');
    }
    if ($a === 'del_addr') { db_run('DELETE FROM addresses WHERE id=? AND user_id=?', [(int)$_POST['id'], $uid]); flash('Address removed.'); redirect('account.php?tab=addresses'); }
    if ($a === 'del_card') { db_run('DELETE FROM payment_methods WHERE id=? AND user_id=?', [(int)$_POST['id'], $uid]); flash('Card removed.'); redirect('account.php?tab=cards'); }
}
$user = db_one('SELECT * FROM users WHERE id=?', [$uid]);
$states = ['Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Labuan','Putrajaya'];
$pageTitle = 'My account';
include 'includes/header.php';
?>
<div class="page">
  <h1>My account</h1>
  <div class="tabs"><?php foreach (['orders'=>'Orders','addresses'=>'Addresses','cards'=>'Saved cards','profile'=>'Profile'] as $k => $v): ?><a href="<?= e(url('account.php?tab=' . $k)) ?>" class="<?= $tab===$k?'on':'' ?>"><?= $v ?></a><?php endforeach; ?></div>

  <?php if ($tab === 'orders'): $orders = db_all('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC', [$uid]); ?>
    <?php if (!$orders): ?><p>No orders yet. <a class="small-link" href="<?= e(url('shop.php')) ?>">Start shopping</a></p><?php endif; ?>
    <?php foreach ($orders as $o): $its = db_all('SELECT * FROM order_items WHERE order_id=?', [(int)$o['id']]); ?>
      <div class="box"><div class="admin-bar"><strong>Order #<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?> &middot; <?= e(date('d M Y', strtotime($o['created_at']))) ?></strong><span class="status <?= e($o['status']) ?>"><?= e($o['status']) ?></span></div>
        <?php foreach ($its as $i): ?><div style="display:flex;justify-content:space-between;padding:4px 0"><span><?= e($i['brand'] . ' ' . $i['name']) ?> <small>(<?= e($i['size']) ?> x<?= (int)$i['quantity'] ?>)</small></span><span><?= money($i['price'] * $i['quantity']) ?></span></div><?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;border-top:1px solid var(--line);margin-top:8px;padding-top:8px"><strong>Total</strong><strong><?= money($o['total']) ?></strong></div></div>
    <?php endforeach; ?>

  <?php elseif ($tab === 'addresses'): $addrs = db_all('SELECT * FROM addresses WHERE user_id=? ORDER BY id DESC', [$uid]); ?>
    <div class="two-col"><div>
      <?php if (!$addrs): ?><p>No saved addresses.</p><?php endif; ?>
      <?php foreach ($addrs as $a): ?><div class="box"><strong><?= e($a['recipient']) ?></strong><br><?= e($a['line1'] . ($a['line2'] ? ', ' . $a['line2'] : '')) ?><br><?= e($a['postcode'] . ' ' . $a['city'] . ', ' . $a['state']) ?><br><?= e($a['phone']) ?>
        <form method="post" data-confirm="Remove this address?"><?= csrf_field() ?><input type="hidden" name="action" value="del_addr"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="link-btn">Remove</button></form></div><?php endforeach; ?>
    </div>
    <form method="post" class="box"><?= csrf_field() ?><input type="hidden" name="action" value="add_addr"><h3>Add an address</h3>
      <label>Recipient</label><input type="text" name="recipient" required><label>Phone</label><input type="tel" name="phone" required>
      <label>Address line 1</label><input type="text" name="line1" required><label>Address line 2</label><input type="text" name="line2">
      <div class="grid2"><div><label>City</label><input type="text" name="city" required></div><div><label>Postcode</label><input type="text" name="postcode" maxlength="5" required></div></div>
      <label>State</label><select name="state" required><option value="">Select state</option><?php foreach ($states as $s): ?><option><?= e($s) ?></option><?php endforeach; ?></select>
      <button class="btn" style="margin-top:16px">Save address</button></form></div>

  <?php elseif ($tab === 'cards'): $cards = db_all('SELECT * FROM payment_methods WHERE user_id=? ORDER BY id DESC', [$uid]); ?>
    <?php if (!$cards): ?><p>No saved cards. You can save one at checkout (only the last 4 digits are kept).</p><?php endif; ?>
    <?php foreach ($cards as $c): ?><div class="box" style="max-width:480px"><strong><?= e($c['brand']) ?> ending <?= e($c['last4']) ?></strong><br><?= e($c['holder']) ?> &middot; expires <?= sprintf('%02d/%02d', $c['exp_month'], $c['exp_year'] % 100) ?>
      <form method="post" data-confirm="Remove this card?"><?= csrf_field() ?><input type="hidden" name="action" value="del_card"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="link-btn">Remove</button></form></div><?php endforeach; ?>

  <?php else: ?>
    <div class="two-col">
      <form method="post" class="box"><?= csrf_field() ?><input type="hidden" name="action" value="profile"><h3>Profile</h3>
        <label>Full name</label><input type="text" name="name" value="<?= e($user['full_name']) ?>" required>
        <label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled>
        <label>Phone</label><input type="tel" name="phone" value="<?= e($user['phone']) ?>"><button class="btn" style="margin-top:16px">Save</button></form>
      <form method="post" class="box"><?= csrf_field() ?><input type="hidden" name="action" value="password"><h3>Change password</h3>
        <label>Current password</label><input type="password" name="current" required><label>New password (8+ characters)</label><input type="password" name="new" minlength="8" required><button class="btn" style="margin-top:16px">Update password</button></form>
    </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
