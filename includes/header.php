<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/view_helpers.php';
generateToken();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'TechGalaxy - Thiết bị công nghệ chính hãng') ?>">
  <meta name="theme-color" content="#2563EB">

  <meta property="og:title"       content="<?= htmlspecialchars($pageTitle ?? APP_NAME) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc ?? '') ?>">
  <meta property="og:type"        content="website">
  <meta property="og:image"       content="<?= BASE_URL ?>/assets/images/og-cover.jpg">

  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>

  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <link href="<?= BASE_URL ?>/assets/css/main.css"       rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/responsive.css" rel="stylesheet">
  <?= $extraCSS ?? '' ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">

<?php if (isset($_SESSION['flash'])): ?>
  <div class="flash-toast flash-<?= $_SESSION['flash']['type'] ?>" id="flashToast">
    <i class="fa-solid <?= $_SESSION['flash']['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>