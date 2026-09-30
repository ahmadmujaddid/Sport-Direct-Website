<?php
$pageTitle = $pageTitle ?? '';
$cartCount = getCartCount($conn);
$curPage = basename($_SERVER['SCRIPT_NAME']);
$g = $_GET['gender'] ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $pageTitle ? e($pageTitle) . ' | ' : '' ?><?= e(SITE_NAME) ?></title>
<link rel="icon" href="<?= e(url('assets/logo.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,600;0,6..96,800;1,6..96,400;1,6..96,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
  <video class="bg-video" autoplay muted loop playsinline>
  <source src="<?= e(url('assets/site-bg.mp4')) ?>" type="video/mp4">
</video>
<div class="announce">Free delivery over RM<?= FREE_SHIP_OVER ?> &nbsp;&#9670;&nbsp; Sale up to 50% off &nbsp;&#9670;&nbsp; Customer service <?= e(SUPPORT_PHONE) ?></div>
<header class="site-header">
  <button class="burger" aria-label="Menu" data-toggle="nav">Menu</button>
  <nav class="nav-left" id="nav">
    <a href="<?= e(url('shop.php?gender=men')) ?>" class="<?= $g==='men'?'on':'' ?>">Men</a>
    <a href="<?= e(url('shop.php?gender=women')) ?>" class="<?= $g==='women'?'on':'' ?>">Women</a>
    <a href="<?= e(url('shop.php?gender=kids')) ?>" class="<?= $g==='kids'?'on':'' ?>">Kids</a>
    <a href="<?= e(url('shop.php?gender=baby')) ?>" class="<?= $g==='baby'?'on':'' ?>">Baby</a>
    <a href="<?= e(url('shop.php?sale=1')) ?>" class="sale-link">Sale</a>
    <a href="<?= e(url('stockist.php')) ?>">Stockist</a>
    <a href="<?= e(url('about.php')) ?>">About</a>
    <a href="<?= e(url('contact.php')) ?>">Contact</a>
  </nav>
  <a href="<?= e(url('index.php')) ?>" class="logo" aria-label="<?= e(SITE_NAME) ?>"><img src="<?= e(url('assets/logo-header.png')) ?>" alt="<?= e(SITE_NAME) ?>" class="logo-img"></a>
  <div class="nav-right">
    <a href="#" data-toggle="search">Search</a>
    <?php if (isLoggedIn()): ?>
      <a href="<?= e(url('account.php')) ?>">Hi, <?= e(explode(' ', $_SESSION['full_name'])[0]) ?></a>
      <?php if (isAdmin()): ?><a href="<?= e(url('admin/index.php')) ?>">Admin</a><?php endif; ?>
      <a href="<?= e(url('logout.php')) ?>">Logout</a>
    <?php else: ?>
      <a href="<?= e(url('login.php')) ?>">Login</a>
    <?php endif; ?>
    <a href="<?= e(url('cart.php')) ?>">Bag (<?= $cartCount ?>)</a>
  </div>
</header>
<form class="search-bar" id="search" action="<?= e(url('shop.php')) ?>" method="get" hidden>
  <input type="search" name="q" placeholder="Search brands, shoes, kit, supplements..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search">
  <button class="btn">Search</button>
</form>
<main>
<?php flash_show(); ?>
