<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

const REQ_STATUSES = [
    'yeni' => 'Yeni',
    'arandi' => 'Arandı',
    'teklif' => 'Teklif verildi',
    'anlasildi' => 'Anlaşıldı',
    'iptal' => 'Olumsuz',
];

function admin_header(string $title, string $active = ''): void
{
    $u = current_user();
    $new = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status = 'yeni'")->fetchColumn();
    $menu = [
        'index' => ['Özet', 'index.php'],
        'requests' => ['Teklif talepleri', 'requests.php'],
        'services' => ['Hizmetler', 'services.php'],
        'vehicles' => ['Araç filosu', 'vehicles.php'],
        'clients' => ['Referanslar', 'clients.php'],
        'settings' => ['Site ayarları', 'settings.php'],
        'account' => ['Hesabım', 'account.php'],
    ];
    $f = flash();
    ?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Yaren Tur Yönetim</title>
<link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(url('admin/admin.css')) ?>?v=1">
</head>
<body class="adm">
<aside class="side" id="side">
  <a class="side__brand" href="index.php"><?= brand_html('brandmark') ?></a>
  <nav class="side__nav">
    <?php foreach ($menu as $k => [$label, $href]): ?>
      <a href="<?= $href ?>" class="<?= $k === $active ? 'is-on' : '' ?>"><?= e($label) ?><?php if ($k === 'requests' && $new): ?><b class="badge"><?= $new ?></b><?php endif; ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side__foot">
    <a href="<?= e(url('')) ?>" target="_blank" rel="noopener">Siteyi görüntüle</a>
    <span><?= e($u['username'] ?? '') ?> · <a href="logout.php">Çıkış</a></span>
  </div>
</aside>
<div class="main">
  <header class="mbar">
    <button class="mbar__menu" type="button" onclick="document.body.classList.toggle('side-open')" aria-label="Menü">☰</button>
    <h1><?= e($title) ?></h1>
  </header>
  <div class="content">
  <?php if ($f): ?><div class="alert alert--<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>
<?php
}

function admin_footer(): void
{
    echo "</div></div>\n<script>document.querySelectorAll('form[data-confirm]').forEach(function(f){f.addEventListener('submit',function(e){if(!confirm(f.dataset.confirm))e.preventDefault();});});</script>\n</body></html>";
}

function post_only(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
    csrf_check();
}
