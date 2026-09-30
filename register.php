<?php
require 'config/db.php';
$pageTitle = 'Register'; $bodyClass = 'auth-page';
if (isLoggedIn()) redirect('index.php');
$error = ''; $v = ['name'=>'','email'=>'','phone'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = ['name'=>trim($_POST['name'] ?? ''), 'email'=>trim($_POST['email'] ?? ''), 'phone'=>trim($_POST['phone'] ?? '')];
    $pw = $_POST['password'] ?? '';
    if ($v['name'] === '' || mb_strlen($v['name']) > 100) $error = 'Please enter your name.';
    elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email.';
    elseif (strlen($pw) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($pw !== ($_POST['password2'] ?? '')) $error = 'Passwords do not match.';
    elseif (db_one('SELECT id FROM users WHERE email = ?', [$v['email']])) $error = 'That email is already registered. Try logging in.';
    else {
        $uid = db_run('INSERT INTO users (full_name,email,phone,password) VALUES (?,?,?,?)', [$v['name'], $v['email'], $v['phone'], password_hash($pw, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$uid; $_SESSION['full_name'] = $v['name']; $_SESSION['role'] = 'customer';
        flash('Welcome to ' . SITE_SHORT . '!');
        redirect('index.php');
    }
}
include 'includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-form">
    <h2>Create account</h2><p class="sub">Join us for faster checkout and order tracking.</p>
    <?php if ($error): ?><div class="err-box"><?= e($error) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?>
      <label>Full name</label><input type="text" name="name" value="<?= e($v['name']) ?>" required>
      <label>Email</label><input type="email" name="email" value="<?= e($v['email']) ?>" required>
      <label>Phone</label><input type="tel" name="phone" value="<?= e($v['phone']) ?>" placeholder="01x-xxx xxxx">
      <label>Password (8+ characters)</label><input type="password" name="password" minlength="8" required>
      <label>Confirm password</label><input type="password" name="password2" minlength="8" required>
      <button class="btn btn-block" type="submit">Sign up</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="<?= e(url('login.php')) ?>">Sign in</a></p>
  </div>
  <?php include 'includes/auth_side.php'; ?>
</div></div>
<?php include 'includes/footer.php'; ?>
