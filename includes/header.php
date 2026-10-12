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
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>?v=2">
</head>
<body>
<a class="skip" href="#icerik">İçeriğe geç</a>
<header class="topbar" id="top">
  <div class="wrap topbar__in">
    <a class="brand" href="<?= e($home) ?>" aria-label="<?= e($siteTitle) ?> ana sayfa">
      <?= brand_html('brandmark') ?>
    </a>
    <nav class="nav" id="nav" aria-label="Ana menü">
      <a href="<?= e($home) ?>#hizmetler">Hizmetler</a>
      <a href="<?= e($home) ?>#online">Online çözümler</a>
      <a href="<?= e($home) ?>#filo">Araç filomuz</a>
      <a href="<?= e($home) ?>#hakkimizda">Hakkımızda</a>
      <a href="<?= e($home) ?>#iletisim">İletişim</a>
      <a class="btn btn--solid nav__cta" href="<?= e($home) ?>#teklif">Teklif alın</a>
    </nav>
    <a class="topbar__phone" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?><span><?= e($phone) ?></span></a>
    <button class="navtoggle" type="button" aria-controls="nav" aria-expanded="false" aria-label="Menüyü aç">
      <?= icon('menu', 'ico ico--open') ?><?= icon('close', 'ico ico--close') ?>
    </button>
  </div>
</header>
<main id="icerik">
