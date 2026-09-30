<?php
require 'config/db.php';
$pageTitle = 'Reset password'; $bodyClass = 'auth-page';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$row = $token ? db_one('SELECT * FROM password_resets WHERE token_hash = ? AND used = 0 AND expires_at > NOW()', [hash('sha256', $token)]) : null;
$error = ''; $done = false;
if ($row && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pw = $_POST['password'] ?? '';
    if (strlen($pw) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($pw !== ($_POST['password2'] ?? '')) $error = 'Passwords do not match.';
    else {
        db_run('UPDATE users SET password = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), (int)$row['user_id']]);
        db_run('UPDATE password_resets SET used = 1 WHERE user_id = ?', [(int)$row['user_id']]);
        $done = true;
    }
}
include 'includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-form">
    <h2>Reset password</h2>
    <?php if ($done): ?>
      <div class="ok-box">Password updated. You can now sign in.</div><a class="btn btn-block" href="<?= e(url('login.php')) ?>">Go to login</a>
    <?php elseif (!$row): ?>
      <div class="err-box">This reset link is invalid or has expired.</div><a class="btn btn-block" href="<?= e(url('forgot.php')) ?>">Request a new link</a>
    <?php else: ?>
      <?php if ($error): ?><div class="err-box"><?= e($error) ?></div><?php endif; ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
        <label>New password (8+ characters)</label><input type="password" name="password" minlength="8" required>
        <label>Confirm new password</label><input type="password" name="password2" minlength="8" required>
        <button class="btn btn-block" type="submit">Save new password</button></form>
    <?php endif; ?>
  </div>
  <?php include 'includes/auth_side.php'; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
