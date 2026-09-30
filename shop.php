<?php
require 'config/db.php';
$pageTitle = 'Shop';

$gender   = $_GET['gender'] ?? '';
$cat      = $_GET['cat'] ?? '';
$brand    = (int)($_GET['brand'] ?? 0);
$q        = trim($_GET['q'] ?? '');
$size     = trim($_GET['size'] ?? '');
$sale     = !empty($_GET['sale']);
$soldout  = !empty($_GET['soldout']);
$isnew    = !empty($_GET['new']);
$trending = !empty($_GET['trending']);
$sort     = $_GET['sort'] ?? 'latest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 24;

$where = ['1=1']; $args = [];
if (in_array($gender, ['men','women'], true)) { $where[] = "p.gender IN (?, 'unisex')"; $args[] = $gender; }
elseif (in_array($gender, ['kids','baby'], true)) { $where[] = 'p.gender = ?'; $args[] = $gender; }
$cats = ['shoes'=>'Shoes','tops'=>'Shirts & tops','bottoms'=>'Bottoms','kit'=>'Kits','gear'=>'Gloves & gear','equipment'=>'Gym equipment','supplements'=>'Supplements'];
if (isset($cats[$cat])) { $where[] = 'p.category = ?'; $args[] = $cat; }
if ($brand) { $where[] = 'p.brand_id = ?'; $args[] = $brand; }
if ($q !== '') { $where[] = '(p.name LIKE ? OR b.name LIKE ? OR p.category LIKE ?)'; $l = '%' . $q . '%'; array_push($args, $l, $l, $l); }
if ($sale) { $where[] = 'p.sale_price IS NOT NULL AND p.sale_price < p.price'; }
if ($isnew) { $where[] = "p.status = 'new'"; }
if ($trending) { $where[] = "p.status = 'trending'"; }
if ($soldout) { $where[] = '(SELECT COALESCE(SUM(s.stock),0) FROM product_sizes s WHERE s.product_id=p.id) = 0'; }
if ($size !== '') { $where[] = 'EXISTS (SELECT 1 FROM product_sizes s WHERE s.product_id=p.id AND s.size=? AND s.stock>0)'; $args[] = $size; }
$W = implode(' AND ', $where);

$orders = ['latest'=>'p.id DESC','trending'=>"(p.status='trending') DESC, p.id DESC",'low'=>'COALESCE(p.sale_price,p.price) ASC','high'=>'COALESCE(p.sale_price,p.price) DESC','discount'=>'(1 - COALESCE(p.sale_price,p.price)/p.price) DESC, p.id DESC'];
$order = $orders[$sort] ?? $orders['latest'];

$total = (int)db_one("SELECT COUNT(*) AS n FROM products p JOIN brands b ON b.id=p.brand_id WHERE $W", $args)['n'];
$pages = max(1, (int)ceil($total / $perPage)); $page = min($page, $pages);
$products = db_all(PRODUCT_SELECT . " WHERE $W ORDER BY $order LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $args);
$brands = db_all('SELECT * FROM brands ORDER BY name');

// build links that keep current filters
function link_with($over) { $p = array_merge($_GET, $over); unset($p['page']); foreach ($p as $k => $v) { if ($v === '' || $v === null || $v === 0) unset($p[$k]); } return url('shop.php') . ($p ? '?' . http_build_query($p) : ''); }
function page_link($i) { $p = $_GET; $p['page'] = $i; return url('shop.php') . '?' . http_build_query($p); }
$title = $gender ? ucfirst($gender) : ($q !== '' ? 'Search: ' . $q : ($sale ? 'Sale' : 'All products'));
$pageTitle = $title;
$sizeChips = ['XS','S','M','L','XL','XXL','UK 4','UK 6','UK 7','UK 8','UK 9','UK 10','UK 11'];
include 'includes/header.php';
?>
<div class="shop">
  <aside class="side">
    <div class="grp"><a href="<?= e(link_with(['sale' => $sale ? null : 1])) ?>" class="<?= $sale?'on':'' ?>"><?= $sale ? '&#9746;' : '&#9744;' ?> Sale</a>
      <a href="<?= e(link_with(['new' => $isnew ? null : 1])) ?>" class="<?= $isnew?'on':'' ?>"><?= $isnew ? '&#9746;' : '&#9744;' ?> New</a>
      <a href="<?= e(link_with(['trending' => $trending ? null : 1])) ?>" class="<?= $trending?'on':'' ?>"><?= $trending ? '&#9746;' : '&#9744;' ?> Trending</a>
      <a href="<?= e(link_with(['soldout' => $soldout ? null : 1])) ?>" class="<?= $soldout?'on':'' ?>"><?= $soldout ? '&#9746;' : '&#9744;' ?> Sold out</a></div>
    <div class="grp"><h4>Section</h4>
      <?php foreach (['' => 'All','men'=>'Men','women'=>'Women','kids'=>'Kids','baby'=>'Baby'] as $k => $v): ?><a href="<?= e(link_with(['gender' => $k])) ?>" class="<?= $gender===$k?'on':'' ?>"><?= $v ?></a><?php endforeach; ?></div>
    <div class="grp"><h4>Categories</h4>
      <a href="<?= e(link_with(['cat' => ''])) ?>" class="<?= $cat===''?'on':'' ?>">All</a>
      <?php foreach ($cats as $k => $v): ?><a href="<?= e(link_with(['cat' => $k])) ?>" class="<?= $cat===$k?'on':'' ?>"><?= e($v) ?></a><?php endforeach; ?></div>
    <div class="grp"><h4>Size</h4><div class="chips">
      <?php foreach ($sizeChips as $s): ?><a href="<?= e(link_with(['size' => $size === $s ? '' : $s])) ?>" class="<?= $size===$s?'on':'' ?>"><?= e($s) ?></a><?php endforeach; ?></div>
      <p class="hint"><a href="<?= e(url('size-guide.php')) ?>" style="display:inline;text-decoration:underline">Size guide</a></p></div>
    <div class="grp"><h4>Designers</h4><div class="scroll">
      <a href="<?= e(link_with(['brand' => 0])) ?>" class="<?= !$brand?'on':'' ?>">All brands</a>
      <?php foreach ($brands as $b): ?><a href="<?= e(link_with(['brand' => (int)$b['id']])) ?>" class="<?= $brand===(int)$b['id']?'on':'' ?>"><?= e($b['name']) ?></a><?php endforeach; ?></div></div>
  </aside>

  <section>
    <div class="shop-top">
      <div class="crumb"><strong><?= e($title) ?></strong> &middot; <?= $total ?> item<?= $total===1?'':'s' ?></div>
      <div class="toggles"><button type="button" data-cols="3" aria-label="3 columns">&#9633;&#9633;&#9633;</button><button type="button" class="on" data-cols="4" aria-label="4 columns">&#9633;&#9633;&#9633;&#9633;</button></div>
    </div>
    <?php if ($products): ?>
      <div class="grid cols-4"><?php foreach ($products as $p) include 'includes/card.php'; ?></div>
      <?php if ($pages > 1): ?><nav class="pager"><?php for ($i = 1; $i <= $pages; $i++): ?><?php if ($i === $page): ?><span class="on"><?= $i ?></span><?php else: ?><a href="<?= e(page_link($i)) ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?>
    <?php else: ?>
      <div class="empty"><h3>Nothing found</h3><p>Try removing a filter.</p><p style="margin-top:14px"><a class="btn" href="<?= e(url('shop.php')) ?>">Clear all filters</a></p></div>
    <?php endif; ?>
  </section>

  <aside class="side right">
    <div class="grp"><h4>Sort</h4>
      <?php foreach (['latest'=>'Latest arrivals','trending'=>'Trending','low'=>'Price: Low to high','high'=>'Price: High to low','discount'=>'Discount: High to low'] as $k => $v): ?>
        <a href="<?= e(link_with(['sort' => $k])) ?>" class="<?= $sort===$k?'on':'' ?>"><?= $v ?></a><?php endforeach; ?></div>
  </aside>
</div>
<?php include 'includes/footer.php'; ?>
