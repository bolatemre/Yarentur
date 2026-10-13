<?php
/** @var string $page_title */
/** @var string $page_desc */
$siteTitle = setting('site_title', 'Yaren Tur');
$title = isset($page_title) && $page_title ? $page_title . ' | ' . $siteTitle : $siteTitle . ' | Öğrenci ve Personel Taşımacılığı';
$desc = $page_desc ?? setting('meta_description');
$phone = setting('phone');
$home = url('');
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="website">
<meta name="theme-color" content="#018231">
<link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Saira:wdth,wght@112.5,600;112.5,700;112.5,800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>?v=3">
<link rel="stylesheet" href="<?= e(url('assets/css/kurumsal.css')) ?>?v=1">
</head>
<body>
<a class="skip" href="#icerik">İçeriğe geç</a>
<div class="util">
  <div class="wrap util__in">
    <span class="util__item"><?= icon('clock') ?><?= e(setting('working_hours')) ?></span>
    <a class="util__item" href="mailto:<?= e(setting('email')) ?>"><?= icon('mail') ?><?= e(setting('email')) ?></a>
    <a class="util__item util__item--strong" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?><?= e($phone) ?></a>
  </div>
</div>
<header class="topbar" id="top">
  <div class="wrap topbar__in">
    <a class="brand" href="<?= e($home) ?>" aria-label="<?= e($siteTitle) ?> ana sayfa">
      <?= brand_html('brandmark') ?>
    </a>
    <nav class="nav" id="nav" aria-label="Ana menü">
      <?php
      $navItems = [
          'home' => ['Ana sayfa', $home],
          'about' => ['Kurumsal', url('hakkimizda.php')],
          'services' => ['Hizmetler', url('hizmetler.php')],
          'online' => ['Online çözümler', url('online-cozumler.php')],
          'fleet' => ['Araç filomuz', url('filo.php')],
          'contact' => ['İletişim', url('iletisim.php')],
      ];
      foreach ($navItems as $key => [$label, $href]): ?>
      <a href="<?= e($href) ?>"<?= ($active ?? '') === $key ? ' class="is-on" aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a class="btn btn--solid nav__cta" href="<?= e(url('iletisim.php')) ?>#teklif">Teklif alın</a>
    </nav>
    <a class="topbar__phone" href="<?= e(tel_href($phone)) ?>" aria-label="Ara: <?= e($phone) ?>"><?= icon('phone') ?></a>
    <button class="navtoggle" type="button" aria-controls="nav" aria-expanded="false" aria-label="Menüyü aç">
      <?= icon('menu', 'ico ico--open') ?><?= icon('close', 'ico ico--close') ?>
    </button>
  </div>
</header>
<main id="icerik">
