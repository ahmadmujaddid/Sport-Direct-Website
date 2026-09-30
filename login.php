<?php
require 'config/db.php';
$pageTitle = 'Login'; $bodyClass = 'auth-page';
if (isLoggedIn()) redirect('index.php');
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $user = db_one('SELECT * FROM users WHERE email = ?', [$email]);
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id']; $_SESSION['full_name'] = $user['full_name']; $_SESSION['role'] = $user['role'];
        redirect($next ?: 'index.php');
    }
    $error = 'Incorrect email or password.';
}
include 'includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-form">
    <h2>Welcome back</h2><p class="sub">Welcome back! Please enter your details.</p>
    <?php if ($error): ?><div class="err-box"><?= e($error) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
      <label for="email">Email</label><input id="email" type="email" name="email" placeholder="Enter your email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
      <label for="pw">Password</label><input id="pw" type="password" name="password" placeholder="Enter your password" required>
      <div class="auth-row"><span></span><a href="<?= e(url('forgot.php')) ?>">Forgot password?</a></div>
      <button class="btn btn-block" type="submit">Sign in</button>
    </form>
    <p class="auth-switch">Don't have an account? <a href="<?= e(url('register.php')) ?>">Sign up for free</a></p>
  </div>
  <?php include 'includes/auth_side.php'; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
