<?php
require __DIR__ . '/_layout.php';
require_login();

$pdo = db();
$counts = [];
foreach ($pdo->query('SELECT status, COUNT(*) c FROM requests GROUP BY status') as $r) $counts[$r['status']] = (int)$r['c'];
$total = array_sum($counts);
$week = (int)$pdo->query("SELECT COUNT(*) FROM requests WHERE created_at >= datetime('now','localtime','-7 days')")->fetchColumn();
$latest = $pdo->query('SELECT * FROM requests ORDER BY id DESC LIMIT 6')->fetchAll();
$svcCount = (int)$pdo->query('SELECT COUNT(*) FROM services WHERE active = 1')->fetchColumn();
$vehCount = (int)$pdo->query('SELECT COUNT(*) FROM vehicles WHERE active = 1')->fetchColumn();
$needsSetup = str_contains(setting('phone'), 'x') || str_starts_with(setting('address'), 'Adres bilgisini');

admin_header('Özet', 'index');
?>
<?php if ($needsSetup): ?>
<div class="alert alert--warn">Telefon ve adres hâlâ örnek bilgilerle görünüyor. <a href="settings.php">Site ayarlarından</a> gerçek bilgileri girin.</div>
<?php endif; ?>
<div class="stats">
  <a class="stat stat--hot" href="requests.php?status=yeni"><b><?= $counts['yeni'] ?? 0 ?></b><span>Yanıt bekleyen talep</span></a>
  <a class="stat" href="requests.php"><b><?= $week ?></b><span>Son 7 günde gelen talep</span></a>
  <a class="stat" href="requests.php?status=anlasildi"><b><?= $counts['anlasildi'] ?? 0 ?></b><span>Anlaşılan talep</span></a>
  <a class="stat" href="requests.php"><b><?= $total ?></b><span>Toplam talep</span></a>
</div>

<section class="card">
  <div class="card__head"><h2>Son teklif talepleri</h2><a href="requests.php">Tümünü görün</a></div>
  <?php if (!$latest): ?>
    <p class="empty">Henüz teklif talebi yok. Sitedeki teklif formundan gelen talepler burada listelenecek.</p>
  <?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Tarih</th><th>Ad soyad</th><th>Telefon</th><th>Hizmet</th><th>Durum</th></tr></thead>
    <tbody>
    <?php foreach ($latest as $r): ?>
      <tr>
        <td><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></td>
        <td><a href="requests.php?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a></td>
        <td><a href="<?= e(tel_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></td>
        <td><?= e($r['service'] ?: '—') ?></td>
        <td><span class="st st--<?= e($r['status']) ?>"><?= e(REQ_STATUSES[$r['status']] ?? $r['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</section>

<div class="grid2">
  <section class="card"><div class="card__head"><h2>Hizmetler</h2><a href="services.php">Düzenle</a></div><p>Sitede <?= $svcCount ?> hizmet yayında. İlk iki sıradaki hizmet ana sayfada büyük gösterilir.</p></section>
  <section class="card"><div class="card__head"><h2>Araç filosu</h2><a href="vehicles.php">Düzenle</a></div><p>Sitede <?= $vehCount ?> araç tipi yayında. Gerçek araç fotoğraflarınızı ekleyebilirsiniz.</p></section>
</div>
<?php admin_footer();
