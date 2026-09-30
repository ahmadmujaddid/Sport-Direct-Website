<?php
require 'config/db.php';
$pageTitle = 'Stockist';
$imgKeys = ['store1','store2','store3'];
include 'includes/header.php';
?>
<div class="page" style="max-width:1300px">
  <h1>Find a store</h1>
  <p style="max-width:620px;margin-bottom:30px">Try, touch and lace up in person. Every store carries the full range of shoes, kit and gear, plus the supplement bar.</p>
  <div class="stores">
    <?php foreach ($STOCKISTS as $i => $s): ?>
      <div class="store">
        <img src="<?= e(asset_src($IMG[$imgKeys[$i]] ?? '')) ?>" alt="<?= e($s['name']) ?> store" loading="lazy">
        <h3><?= e($s['name']) ?></h3>
        <p><?= e($s['address']) ?></p>
        <p><?= e($s['hours']) ?></p>
        <p>Tel: <a class="small-link" href="tel:<?= e(SUPPORT_PHONE_RAW) ?>"><?= e(SUPPORT_PHONE) ?></a></p>
        <p style="margin-top:14px"><a class="btn btn-line" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($s['query']) ?>">Get directions</a></p>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
