<?php
require 'config/db.php';
requireLogin();
$o = db_one('SELECT * FROM orders WHERE id=? AND user_id=?', [(int)($_GET['id'] ?? 0), (int)$_SESSION['user_id']]);
if (!$o) redirect('account.php');
$items = db_all('SELECT * FROM order_items WHERE order_id=?', [(int)$o['id']]);
$pageTitle = 'Order confirmed';
include 'includes/header.php';
?>
<div class="page" style="max-width:800px">
  <h1>Thank you!</h1>
  <p style="margin-bottom:20px">Order <strong>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></strong> has been placed. Status: <span class="status <?= e($o['status']) ?>"><?= e($o['status']) ?></span></p>
  <div class="box"><table class="t"><?php foreach ($items as $i): ?><tr><td><?= e($i['brand'] . ' ' . $i['name']) ?> <small>(<?= e($i['size']) ?>)</small></td><td>x<?= (int)$i['quantity'] ?></td><td style="text-align:right"><?= money($i['price'] * $i['quantity']) ?></td></tr><?php endforeach; ?>
    <tr><td colspan="2">Delivery</td><td style="text-align:right"><?= $o['shipping'] > 0 ? money($o['shipping']) : 'Free' ?></td></tr>
    <tr><th colspan="2">Total</th><th style="text-align:right"><?= money($o['total']) ?></th></tr></table></div>
  <div class="box"><h3>Delivering to</h3><p><?= e($o['ship_name']) ?>, <?= e($o['ship_phone']) ?><br><?= e($o['ship_address']) ?></p>
    <p style="margin-top:8px">Payment: <?= e(strtoupper($o['payment_method'])) ?><?= $o['card_last4'] ? ' (card ending ' . e($o['card_last4']) . ')' : '' ?></p></div>
  <a class="btn" href="<?= e(url('shop.php')) ?>">Continue shopping</a> <a class="btn btn-line" href="<?= e(url('account.php')) ?>">My account</a>
</div>
<?php include 'includes/footer.php'; ?>
