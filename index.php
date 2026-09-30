<?php
require '../config/db.php';
requireAdmin();
$tab = $_GET['tab'] ?? 'products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'order_status' && in_array($_POST['status'] ?? '', ['pending','paid','shipped','delivered','cancelled'], true)) {
        db_run('UPDATE orders SET status=? WHERE id=?', [$_POST['status'], (int)$_POST['id']]); flash('Order updated.');
        redirect('admin/index.php?tab=orders');
    }
    if (($_POST['action'] ?? '') === 'delete_product') { db_run('DELETE FROM products WHERE id=?', [(int)$_POST['id']]); flash('Product deleted.'); redirect('admin/index.php'); }
}
$pageTitle = 'Admin';
include '../includes/header.php';
?>
<div class="page" style="max-width:1400px">
  <div class="admin-bar"><h1 style="margin:0">Admin</h1><a class="btn btn-red" href="<?= e(url('admin/product.php')) ?>">+ Add product</a></div>
  <div class="tabs"><?php foreach (['products'=>'Products','orders'=>'Orders','messages'=>'Messages'] as $k => $v): ?><a href="?tab=<?= $k ?>" class="<?= $tab===$k?'on':'' ?>"><?= $v ?></a><?php endforeach; ?></div>

  <?php if ($tab === 'products'):
    $q = trim($_GET['q'] ?? '');
    $rows = db_all(PRODUCT_SELECT . ($q !== '' ? ' WHERE p.name LIKE ? OR b.name LIKE ?' : '') . ' ORDER BY p.id DESC LIMIT 300', $q !== '' ? ['%'.$q.'%', '%'.$q.'%'] : []); ?>
    <form method="get" style="margin-bottom:14px;display:flex;gap:8px"><input type="hidden" name="tab" value="products"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products"><button class="btn">Search</button></form>
    <div class="tbl-scroll"><table class="t"><tr><th></th><th>ID</th><th>Product</th><th>Section</th><th>Category</th><th>Price</th><th>Sale</th><th>Stock</th><th>Status</th><th></th></tr>
    <?php foreach ($rows as $p): ?><tr>
      <td><img class="thumb" src="<?= e(img_src($p)) ?>" alt=""></td><td><?= (int)$p['id'] ?></td><td><strong><?= e($p['brand_name']) ?></strong><br><?= e($p['name']) ?></td>
      <td><?= e($p['gender']) ?></td><td><?= e($p['category']) ?></td><td><?= money($p['price']) ?></td><td><?= $p['sale_price'] ? money($p['sale_price']) : '-' ?></td>
      <td><?= (int)$p['total_stock'] ?: '<span class="red">Sold out</span>' ?></td><td><?= e($p['status']) ?></td>
      <td style="white-space:nowrap"><a class="small-link" href="<?= e(url('admin/product.php?id=' . (int)$p['id'])) ?>">Edit</a>
        <form method="post" style="display:inline" data-confirm="Delete this product?"><?= csrf_field() ?><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="link-btn">Delete</button></form></td></tr>
    <?php endforeach; ?></table></div>

  <?php elseif ($tab === 'orders'): $orders = db_all('SELECT o.*, u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 200'); ?>
    <?php if (!$orders) echo '<p>No orders yet.</p>'; ?>
    <?php foreach ($orders as $o): $its = db_all('SELECT * FROM order_items WHERE order_id=?', [(int)$o['id']]); ?>
      <div class="box"><div class="admin-bar"><strong>#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?> &middot; <?= e($o['created_at']) ?> &middot; <?= e($o['email']) ?></strong>
        <form method="post" style="display:flex;gap:6px"><?= csrf_field() ?><input type="hidden" name="action" value="order_status"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
          <select name="status"><?php foreach (['pending','paid','shipped','delivered','cancelled'] as $s): ?><option <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select><button class="btn" style="padding:8px 14px">Save</button></form></div>
        <?php foreach ($its as $i): ?><div><?= e($i['brand'] . ' ' . $i['name'] . ' (' . $i['size'] . ') x' . $i['quantity']) ?> &mdash; <?= money($i['price'] * $i['quantity']) ?></div><?php endforeach; ?>
        <p style="margin-top:8px"><strong>Total <?= money($o['total']) ?></strong> &middot; <?= e(strtoupper($o['payment_method'])) ?> &middot; <?= e($o['ship_name']) ?>, <?= e($o['ship_phone']) ?>, <?= e($o['ship_address']) ?></p></div>
    <?php endforeach; ?>

  <?php else: $msgs = db_all('SELECT * FROM messages ORDER BY id DESC LIMIT 200'); ?>
    <?php if (!$msgs) echo '<p>No messages yet.</p>'; ?>
    <?php foreach ($msgs as $m): ?><div class="box"><strong><?= e($m['subject']) ?></strong><br><small><?= e($m['name']) ?> &lt;<?= e($m['email']) ?>&gt; &middot; <?= e($m['created_at']) ?></small><p style="margin-top:8px;white-space:pre-wrap"><?= e($m['message']) ?></p></div><?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
