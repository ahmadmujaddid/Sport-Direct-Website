<?php /* expects $p (product row with brand_name, total_stock) */ $bd = badges($p); $sold = (int)$p['total_stock'] <= 0; ?>
<article class="card <?= $sold ? 'is-sold' : '' ?>">
  <a href="<?= e(url('product.php?id=' . (int)$p['id'])) ?>" class="card-img"><img src="<?= e(img_src($p)) ?>" alt="<?= e($p['brand_name'] . ' ' . $p['name']) ?>" loading="lazy"></a>
  <?php if ($bd): ?><div class="badges"><?php foreach ($bd as $x): ?><a class="badge b-<?= e($x[1]) ?>" href="<?= e(url($x[2])) ?>"><?= e($x[0]) ?></a><?php endforeach; ?></div><?php endif; ?>
  <a href="<?= e(url('product.php?id=' . (int)$p['id'])) ?>" class="card-info">
    <span class="c-price"><?php if (on_sale($p)): ?><s><?= money($p['price']) ?></s> <b class="red"><?= money(price_now($p)) ?></b><?php else: ?><b><?= money($p['price']) ?></b><?php endif; ?></span>
    <span class="c-brand"><?= e($p['brand_name']) ?></span>
    <span class="c-name"><?= e($p['name']) ?></span>
  </a>
</article>
