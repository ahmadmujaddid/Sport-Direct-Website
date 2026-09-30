<?php
require 'config/db.php';
$pageTitle = 'Forgot password'; $bodyClass = 'auth-page';
$sent = false; $devLink = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $user = db_one('SELECT id FROM users WHERE email = ?', [$email]);
    if ($user) {
        $token = bin2hex(random_bytes(24));
        db_run('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))', [(int)$user['id'], hash('sha256', $token)]);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $link = $scheme . '://' . $_SERVER['HTTP_HOST'] . url('reset.php?token=' . $token);
        if (DEV_MODE) { $devLink = $link; }
        else { @mail($email, 'Reset your ' . SITE_SHORT . ' password', "Click to reset your password (valid 1 hour):\n$link", 'From: ' . SUPPORT_EMAIL); }
    }
    $sent = true;   // same message whether or not the email exists
}
include 'includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-form">
    <h2>Forgot password</h2><p class="sub">Enter your email and we'll send you a reset link.</p>
    <?php if ($sent): ?>
      <div class="ok-box">If that email is registered, a reset link has been sent. It is valid for 1 hour.</div>
      <?php if ($devLink): ?><div class="ok-box"><strong>Demo mode (no mail server):</strong><br><a href="<?= e($devLink) ?>" style="text-decoration:underline"><?= e($devLink) ?></a></div><?php endif; ?>
    <?php endif; ?>
    <form method="post"><?= csrf_field() ?>
      <label>Email</label><input type="email" name="email" required>
      <button class="btn btn-block" type="submit">Send reset link</button>
    </form>
    <p class="auth-switch"><a href="<?= e(url('login.php')) ?>">Back to sign in</a></p>
  </div>
  <?php include 'includes/auth_side.php'; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
