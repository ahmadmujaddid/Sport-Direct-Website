</main>
<?php
$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'newsletter') {
    csrf_check();
    $em = trim($_POST['email'] ?? '');
    if (filter_var($em, FILTER_VALIDATE_EMAIL)) { db_run('INSERT IGNORE INTO newsletter (email) VALUES (?)', [$em]); $done = true; }
}
?>
<footer class="site-footer">
  <div class="foot-top">
    <div>
      <h4>Be the first to know</h4>
      <p>Sign up for the latest drops, sales and store news.</p>
      <?php if ($done): ?><p class="ok-text">Thank you, you're on the list.</p><?php else: ?>
      <form method="post" action="<?= e(url('index.php')) ?>" class="news-form"><?= csrf_field() ?><input type="hidden" name="form" value="newsletter">
        <input type="email" name="email" placeholder="Email address" required aria-label="Email address"><button class="btn btn-red">Subscribe</button></form>
      <?php endif; ?>
    </div>
    <a class="store-box" href="<?= e(url('stockist.php')) ?>"><strong>Store Finder</strong><span>Pudu &middot; TRX &middot; One Utama</span></a>
  </div>
  <div class="foot-cols">
    <div><h4>Shopping with us</h4><a href="<?= e(url('size-guide.php')) ?>">Size guides</a><a href="<?= e(url('stockist.php')) ?>">Stockist</a><a href="<?= e(url('shop.php?sale=1')) ?>">Sale</a><a href="<?= e(url('shop.php?new=1')) ?>">New arrivals</a></div>
    <div><h4>Help &amp; customer care</h4><a href="<?= e(url('contact.php')) ?>">Contact us</a><a href="<?= e(url('account.php')) ?>">My orders</a><a href="<?= e(url('forgot.php')) ?>">Forgot password</a>
      <a href="tel:<?= e(SUPPORT_PHONE_RAW) ?>">Customer service: <?= e(SUPPORT_PHONE) ?></a></div>
    <div><h4>About us</h4><a href="<?= e(url('about.php')) ?>">Our story</a><a href="<?= e(url('contact.php')) ?>">Returns &amp; refunds</a><a href="<?= e(url('contact.php')) ?>">Terms &amp; conditions</a><a href="<?= e(url('contact.php')) ?>">Privacy policy</a></div>
    <div><h4>Country</h4><div class="country">Malaysia</div>
      <h4>Payment methods</h4>
      <div class="pay-chips"><span>VISA</span><span>Mastercard</span><span>FPX</span><span>Touch 'n Go</span><span>GrabPay</span><span>Cash on delivery</span></div></div>
  </div>
  <p class="copy">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Demo shop.</p>
</footer>
<script src="<?= e(url('js/app.js')) ?>"></script>
</body>
</html>
