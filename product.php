<?php
require 'config/db.php';
$id = (int)($_GET['id'] ?? 0);
$p = db_one(PRODUCT_SELECT . ' WHERE p.id = ?', [$id]);
if (!$p) { http_response_code(404); $pageTitle = 'Not found'; include 'includes/header.php'; echo '<div class="page"><h1>Product not found</h1><a class="btn" href="' . e(url('shop.php')) . '">Back to shop</a></div>'; include 'includes/footer.php'; exit; }
$sizes = db_all('SELECT * FROM product_sizes WHERE product_id = ? ORDER BY id', [$id]);
$related = db_all(PRODUCT_SELECT . ' WHERE (p.brand_id = ? OR p.category = ?) AND p.id <> ? ORDER BY RAND() LIMIT 4', [(int)$p['brand_id'], $p['category'], $id]);
$pageTitle = $p['brand_name'] . ' ' . $p['name'];
$sold = (int)$p['total_stock'] <= 0;
$isShoe = strpos($p['size_type'], 'shoe') === 0;
include 'includes/header.php';
?>
<div class="pdp">
  <div class="pdp-img">
    <img src="<?= e(img_src($p)) ?>" alt="<?= e($p['brand_name'] . ' ' . $p['name']) ?>">
    <?php $bd = badges($p); if ($bd): ?><div class="badges"><?php foreach ($bd as $x): ?><a class="badge b-<?= e($x[1]) ?>" href="<?= e(url($x[2])) ?>"><?= e($x[0]) ?></a><?php endforeach; ?></div><?php endif; ?>
  </div>
  <div class="pdp-info">
    <div class="pdp-brand"><a href="<?= e(url('shop.php?brand=' . (int)$p['brand_id'])) ?>"><?= e($p['brand_name']) ?></a></div>
    <h1><?= e($p['name']) ?></h1>
    <div class="pdp-price"><?php if (on_sale($p)): ?><s><?= money($p['price']) ?></s> <span class="red"><?= money(price_now($p)) ?></span> <span class="badge b-sale">-<?= discount_pct($p) ?>%</span><?php else: ?><?= money($p['price']) ?><?php endif; ?></div>
    <p style="max-width:520px;color:#333"><?= e($p['description']) ?></p>

    <?php if ($sold): ?>
      <p style="margin:26px 0"><span class="badge b-soldout">SOLD OUT</span> &nbsp; This item is currently unavailable in every size.</p>
      <a class="btn btn-line" href="<?= e(url('shop.php?cat=' . urlencode($p['category']))) ?>">Browse similar</a>
    <?php else: ?>
    <form id="add-form" method="post" action="<?= e(url('cart.php')) ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= $id ?>">
      <label style="display:flex;justify-content:space-between"><span><?= e(size_label($p['size_type'])) ?><?= $isShoe ? ' (UK sizes)' : '' ?></span>
        <?php if ($p['size_type'] !== 'one' && $p['size_type'] !== 'flavour'): ?><a class="small-link" href="<?= e(url('size-guide.php')) ?>" target="_blank" rel="noopener">Size guide</a><?php endif; ?></label>
      <div class="sizes">
        <?php foreach ($sizes as $s): $out = (int)$s['stock'] <= 0; ?>
          <label class="size" title="<?= $out ? 'Sold out' : '' ?>"><input type="radio" name="size" value="<?= e($s['size']) ?>" data-stock="<?= (int)$s['stock'] ?>" <?= $out ? 'disabled' : 'required' ?>><span><?= e($s['size']) ?></span></label>
        <?php endforeach; ?>
      </div>
      <div class="stock-note" id="stock-note" aria-live="polite"></div>
      <?php if ($isShoe): ?><p class="hint">Crossed-out sizes are sold out. See the size guide for UK / US / EU conversion.</p><?php endif; ?>
      <div class="qty-row"><input type="number" name="quantity" value="1" min="1" max="10" aria-label="Quantity"><button class="btn btn-red" type="submit" disabled style="flex:1">Select a size</button></div>
    </form>
    <?php endif; ?>

    <div class="acc">
      <details><summary>Delivery</summary><p>Free delivery over RM<?= FREE_SHIP_OVER ?> across Malaysia, otherwise RM<?= SHIP_FEE ?>. Usually 2 to 4 working days.</p></details>
      <details><summary>Returns</summary><p>Unworn items can be returned within 14 days. Bring them to any store (Pudu, TRX, One Utama) or contact <?= e(SUPPORT_PHONE) ?>.</p></details>
      <details><summary>Try in store</summary><p>Not sure about your size? Visit one of our <a class="small-link" href="<?= e(url('stockist.php')) ?>">stockists</a>.</p></details>
    </div>
  </div>
</div>
<?php if ($related): ?>
<section class="section"><div class="section-head"><h2>You may also like</h2></div>
  <div class="grid cols-4"><?php foreach ($related as $p) include 'includes/card.php'; ?></div></section>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
