<?php
require 'config/db.php';
$pageTitle = 'About us';
$tl = [
 ['2009','A stall in Pudu, one pair of Vans and a lot of confidence','Sexy Sonnet began as a folding table beside a nasi lemak stall in Pudu. The first customer bought a pair of canvas shoes and left a review in verse. That review is still framed in the back office. Nobody has been brave enough to tell him it did not rhyme.','about1'],
 ['2012','A real shop with a real door','We moved into a shoplot with air-conditioning, which felt like a lifestyle upgrade. We stocked Nike, Adidas and Puma, and hung a sign outside that was slightly crooked on purpose, because a perfect sign looks like a bank.','about2'],
 ['2015','We became a Berhad','We added "Berhad" to the name because it sounded serious and our accountant asked nicely. The board of directors was three cousins and a very calm uncle. Board meetings still start with a shoe-smelling competition. Nobody wins.','about3'],
 ['2018','One Utama joins the family','Our second store opened at One Utama with a wall of running shoes taller than the manager. We introduced the Sonnet Fit Test: run down the aisle, we watch your stride and pretend to be scientists. It works surprisingly well.','about4'],
 ['2021','The online shop, built in a very long weekend','When the shops closed, the website opened. Orders came in from Kota Kinabalu to Kangar. We packed parcels in the shop with the shutters half-down and learned that people really do buy trainers at 3am.','about5'],
 ['2024','TRX, our tallest store yet','We opened at TRX with three floors of ambition and one floor of actual store. It has a sneaker wall, a kids corner, a supplement bar and a chair that people keep falling asleep in. We call it the nap-through testimonial.','about6'],
 ['2026','This website, finally','You are here. Men, women, kids, baby, sale and sold out, all in one place, in a font that costs more than our first shop rent. Thank you for being part of the story.','about7'],
];
include 'includes/header.php';
?>
<section class="about-hero">
  <h1>Our story, in fourteen lines and a few extra</h1>
  <p>Sexy Sonnet Sport Berhad is a Malaysian sports retailer with three stores, sixteen major brands and a strong belief that sport should look as good as it feels.</p>
</section>
<div class="timeline">
  <?php foreach ($tl as $t): ?>
    <div class="tl">
      <div class="tl-txt"><div class="yr"><?= e($t[0]) ?></div><h3><?= e($t[1]) ?></h3><p><?= e($t[2]) ?></p></div>
      <?php $src = asset_src($IMG[$t[3]] ?? ''); ?><img src="<?= e($src) ?>" alt="<?= e($t[0] . ': ' . $t[1]) ?>" loading="lazy">
    </div>
  <?php endforeach; ?>
</div>
<section class="section" style="text-align:center">
  <h2>Come and say hello</h2>
  <p style="margin:12px 0 22px">Pudu, TRX and One Utama. Bring your old shoes and your best excuse.</p>
  <a class="btn" href="<?= e(url('stockist.php')) ?>">Find a store</a> <a class="btn btn-line" href="<?= e(url('contact.php')) ?>">Contact us</a>
</section>
<?php include 'includes/footer.php'; ?>
