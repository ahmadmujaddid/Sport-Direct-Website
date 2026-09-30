<?php
require 'config/db.php';
$pageTitle = 'Contact us';
$sent = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $n = trim($_POST['name'] ?? ''); $em = trim($_POST['email'] ?? ''); $sub = trim($_POST['subject'] ?? ''); $msg = trim($_POST['message'] ?? '');
    if (!$n || !$sub || !$msg) $error = 'Please fill in every field.';
    elseif (!filter_var($em, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email.';
    else { db_run('INSERT INTO messages (name,email,subject,message) VALUES (?,?,?,?)', [mb_substr($n,0,100), mb_substr($em,0,150), mb_substr($sub,0,150), mb_substr($msg,0,3000)]); $sent = true; }
}
include 'includes/header.php';
?>
<div class="page" style="max-width:1200px">
  <h1>Contact us</h1>
  <div class="contact-grid">
    <div>
      <div class="cs-card">
        <div>Customer service</div>
        <a class="big" href="tel:<?= e(SUPPORT_PHONE_RAW) ?>"><?= e(SUPPORT_PHONE) ?></a>
        <p style="margin-bottom:16px;color:#ccc">Daily 10am to 9pm. Call, or message us on WhatsApp.</p>
        <a class="btn" target="_blank" rel="noopener" href="https://wa.me/<?= e(SUPPORT_WHATSAPP) ?>">WhatsApp us</a>
      </div>
      <div class="faq" style="margin-top:30px">
        <h3 style="margin-bottom:8px">Quick answers</h3>
        <details><summary>How long does delivery take?</summary><p>Usually 2 to 4 working days in Peninsular Malaysia, longer for Sabah and Sarawak. Free over RM<?= FREE_SHIP_OVER ?>.</p></details>
        <details><summary>Can I return or exchange?</summary><p>Yes, within 14 days if unworn with tags, online or at any store.</p></details>
        <details><summary>Which payment methods do you take?</summary><p>Visa, Mastercard, UnionPay, FPX, Touch 'n Go, GrabPay and cash on delivery.</p></details>
        <details><summary>What if my size is sold out?</summary><p>Try the size filter in the shop, or ask a store to check the other branches.</p></details>
      </div>
    </div>
    <div>
      <?php if ($sent): ?><div class="ok-box">Thank you, we've got your message and will reply within one working day.</div><?php endif; ?>
      <?php if ($error): ?><div class="err-box"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="box"><?= csrf_field() ?><h3>Send us a message</h3>
        <label>Name</label><input type="text" name="name" value="<?= e($_POST['name'] ?? ($_SESSION['full_name'] ?? '')) ?>" required>
        <label>Email</label><input type="email" name="email" required>
        <label>Subject</label><input type="text" name="subject" required>
        <label>Message</label><textarea name="message" required></textarea>
        <button class="btn" style="margin-top:16px">Send message</button></form>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
