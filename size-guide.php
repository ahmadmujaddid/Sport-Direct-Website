<?php
require 'config/db.php';
$pageTitle = 'Size guide';
include 'includes/header.php';
?>
<div class="page">
  <h1>Size guide</h1>
  <p style="max-width:640px;margin-bottom:26px">Sizes vary slightly between brands. If you are between sizes, go up for running shoes and stay true for lifestyle sneakers. Still unsure? Call us on <a class="small-link" href="tel:<?= e(SUPPORT_PHONE_RAW) ?>"><?= e(SUPPORT_PHONE) ?></a>.</p>

  <div class="box"><h3>Men's shoes (approximate)</h3><div class="tbl-scroll"><table class="t"><tr><th>UK</th><th>US (men)</th><th>EU</th><th>Foot length (cm)</th></tr>
    <?php foreach ([6,7,8,9,10,11,12] as $uk): ?><tr><td>UK <?= $uk ?></td><td>US <?= $uk + 1 ?></td><td>EU <?= $uk + 34 ?></td><td><?= number_format(20.5 + $uk * 0.75, 1) ?> cm</td></tr><?php endforeach; ?></table></div></div>

  <div class="box"><h3>Women's shoes (approximate)</h3><div class="tbl-scroll"><table class="t"><tr><th>UK</th><th>US (women)</th><th>EU</th><th>Foot length (cm)</th></tr>
    <?php foreach ([3,4,5,6,7,8] as $uk): ?><tr><td>UK <?= $uk ?></td><td>US <?= $uk + 2 ?></td><td>EU <?= $uk + 35 ?></td><td><?= number_format(20.5 + $uk * 0.75, 1) ?> cm</td></tr><?php endforeach; ?></table></div></div>

  <div class="box"><h3>Kids &amp; baby shoes</h3><div class="tbl-scroll"><table class="t"><tr><th>UK</th><th>EU</th><th>Foot length (cm)</th></tr>
    <?php foreach ([['UK 10K',28,17.0],['UK 11K',29,17.8],['UK 12K',31,18.7],['UK 13K',32,19.5],['UK 1',33,20.3],['UK 2',34,21.2],['UK 3',35,22.0],['-','EU 17',10.5],['-','EU 18',11.0],['-','EU 19',11.8],['-','EU 20',12.5],['-','EU 21',13.2]] as $r): ?>
      <tr><td><?= e($r[0]) ?></td><td><?= is_int($r[1]) ? 'EU ' . $r[1] : e($r[1]) ?></td><td><?= number_format($r[2], 1) ?> cm</td></tr><?php endforeach; ?></table></div></div>

  <div class="box"><h3>Tops, kits &amp; bottoms</h3><div class="tbl-scroll"><table class="t"><tr><th>Size</th><th>Chest (cm)</th><th>Waist (cm)</th><th>Hip (cm)</th></tr>
    <?php foreach ([['XS','82-87','66-71','86-91'],['S','88-93','72-77','92-97'],['M','94-99','78-83','98-103'],['L','100-107','84-91','104-111'],['XL','108-115','92-99','112-119'],['XXL','116-123','100-107','120-127']] as $r): ?>
      <tr><?php foreach ($r as $c): ?><td><?= e($c) ?></td><?php endforeach; ?></tr><?php endforeach; ?></table></div></div>

  <div class="box"><h3>Kids &amp; baby clothing</h3><div class="tbl-scroll"><table class="t"><tr><th>Size</th><th>Height (cm)</th></tr>
    <?php foreach ([['0-3M','50-62'],['3-6M','62-68'],['6-9M','68-74'],['9-12M','74-80'],['12-18M','80-86'],['18-24M','86-92'],['4-5Y','104-116'],['6-7Y','116-128'],['8-9Y','128-140'],['10-11Y','140-152'],['12-13Y','152-164']] as $r): ?>
      <tr><td><?= e($r[0]) ?></td><td><?= e($r[1]) ?></td></tr><?php endforeach; ?></table></div></div>

  <div class="box"><h3>Gloves</h3><div class="tbl-scroll"><table class="t"><tr><th>Size</th><th>Palm width (cm)</th></tr>
    <?php foreach ([['S','7.5-8.5'],['M','8.5-9.5'],['L','9.5-10.5'],['XL','10.5-11.5']] as $r): ?><tr><td><?= $r[0] ?></td><td><?= $r[1] ?></td></tr><?php endforeach; ?></table></div></div>

  <div class="box"><h3>How to measure your foot</h3><p>Stand on a sheet of paper against a wall, mark the longest toe, and measure heel to mark in centimetres. Match it to the table above.</p></div>
</div>
<?php include 'includes/footer.php'; ?>
