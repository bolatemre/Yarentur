<?php
require __DIR__ . '/_layout.php';
require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM requests WHERE id = ?')->execute([$id]);
        flash('Talep silindi.');
        redirect('requests.php');
    }
    $status = (string)($_POST['status'] ?? 'yeni');
    if (!isset(REQ_STATUSES[$status])) $status = 'yeni';
    $note = mb_substr(trim((string)($_POST['note'] ?? '')), 0, 4000);
    $pdo->prepare('UPDATE requests SET status = ?, note = ? WHERE id = ?')->execute([$status, $note, $id]);
    flash('Talep güncellendi.');
    redirect('requests.php?id=' . $id);
}

// CSV dışa aktarma
if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="teklif-talepleri-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Tarih', 'Firma', 'Yetkili', 'Telefon', 'E-posta', 'Hizmet', 'Kalkış / ilçeler', 'İş yeri / varış', 'Kişi', 'Vardiya', 'Başlangıç tarihi', 'Mesaj', 'Durum', 'Not'], ';');
    foreach ($pdo->query('SELECT * FROM requests ORDER BY id DESC') as $r) {
        fputcsv($out, [$r['created_at'], $r['company'], $r['name'], $r['phone'], $r['email'], $r['service'], $r['route_from'], $r['route_to'], $r['passengers'], $r['shift'], $r['travel_date'], $r['message'], REQ_STATUSES[$r['status']] ?? $r['status'], $r['note']], ';');
    }
    exit;
}

// Detay
if (isset($_GET['id'])) {
    $st = $pdo->prepare('SELECT * FROM requests WHERE id = ?');
    $st->execute([(int)$_GET['id']]);
    $r = $st->fetch();
    if (!$r) { flash('Talep bulunamadı.', 'err'); redirect('requests.php'); }
    admin_header('Talep: ' . ($r['company'] !== '' ? $r['company'] : $r['name']), 'requests');
    $wa = preg_replace('/\D/', '', $r['phone']);
    if (str_starts_with($wa, '0')) $wa = '9' . $wa;
    elseif (strlen($wa) === 10) $wa = '90' . $wa;
    ?>
    <p><a href="requests.php">← Tüm talepler</a></p>
    <div class="grid2">
      <section class="card">
        <h2>Talep bilgileri</h2>
        <dl class="dl">
          <dt>Gönderim</dt><dd><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></dd>
          <dt>Firma / kurum</dt><dd><?= e($r['company'] ?: '—') ?></dd>
          <dt>Yetkili</dt><dd><?= e($r['name']) ?></dd>
          <dt>Telefon</dt><dd><a href="<?= e(tel_href($r['phone'])) ?>"><?= e($r['phone']) ?></a> · <a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></dd>
          <dt>E-posta</dt><dd><?= $r['email'] ? '<a href="mailto:' . e($r['email']) . '">' . e($r['email']) . '</a>' : '—' ?></dd>
          <dt>Hizmet</dt><dd><?= e($r['service'] ?: '—') ?></dd>
          <dt>Kişi sayısı</dt><dd><?= e($r['passengers'] ?: '—') ?></dd>
          <dt>Vardiya düzeni</dt><dd><?= e($r['shift'] ?: '—') ?></dd>
          <dt>Kalkış / ilçeler</dt><dd><?= e($r['route_from'] ?: '—') ?></dd>
          <dt>İş yeri / varış</dt><dd><?= e($r['route_to'] ?: '—') ?></dd>
          <dt>Başlangıç</dt><dd><?= $r['travel_date'] ? e(date('d.m.Y', strtotime($r['travel_date']))) : '—' ?></dd>
          <dt>Mesaj</dt><dd><?= $r['message'] ? nl2br(e($r['message'])) : '—' ?></dd>
        </dl>
      </section>
      <section class="card">
        <h2>Takip</h2>
        <form method="post" class="stack">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <label class="f"><span>Durum</span>
            <select name="status"><?php foreach (REQ_STATUSES as $k => $v): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select>
          </label>
          <label class="f"><span>İç not (sadece panelde görünür)</span><textarea name="note" rows="6"><?= e($r['note']) ?></textarea></label>
          <button class="b b--pri">Kaydet</button>
        </form>
        <form method="post" data-confirm="Bu talep kalıcı olarak silinsin mi?" class="mt">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="b b--danger">Talebi sil</button>
        </form>
      </section>
    </div>
    <?php
    admin_footer();
    exit;
}

// Liste
$status = (string)($_GET['status'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$where = []; $args = [];
if (isset(REQ_STATUSES[$status])) { $where[] = 'status = ?'; $args[] = $status; }
if ($q !== '') { $where[] = '(name LIKE ? OR company LIKE ? OR phone LIKE ? OR email LIKE ? OR route_from LIKE ? OR route_to LIKE ?)'; array_push($args, ...array_fill(0, 6, "%$q%")); }
$sql = 'SELECT * FROM requests' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 500';
$st = $pdo->prepare($sql); $st->execute($args);
$rows = $st->fetchAll();

admin_header('Teklif talepleri', 'requests');
?>
<form class="filters" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Firma, isim, telefon veya güzergah ara">
  <select name="status" onchange="this.form.submit()">
    <option value="">Tüm durumlar</option>
    <?php foreach (REQ_STATUSES as $k => $v): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
  </select>
  <button class="b">Ara</button>
  <a class="b b--ghost" href="requests.php?csv=1">Excel'e aktar (CSV)</a>
</form>
<section class="card">
<?php if (!$rows): ?>
  <p class="empty"><?= ($q || $status) ? 'Bu filtreye uyan talep yok.' : 'Henüz teklif talebi yok. Sitedeki teklif formundan gelen talepler burada listelenecek.' ?></p>
<?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Tarih</th><th>Firma / yetkili</th><th>Telefon</th><th>Hizmet</th><th>Kişi</th><th>Durum</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr class="<?= $r['status'] === 'yeni' ? 'is-new' : '' ?>">
        <td><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></td>
        <td><a href="?id=<?= (int)$r['id'] ?>"><?= e($r['company'] !== '' ? $r['company'] : $r['name']) ?></a><?php if ($r['company'] !== ''): ?><br><small class="muted"><?= e($r['name']) ?></small><?php endif; ?></td>
        <td><a href="<?= e(tel_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></td>
        <td><?= e($r['service'] ?: '—') ?></td>
        <td><?= e($r['passengers'] ?: '—') ?><?php if ($r['shift']): ?><br><small class="muted"><?= e($r['shift']) ?></small><?php endif; ?></td>
        <td><span class="st st--<?= e($r['status']) ?>"><?= e(REQ_STATUSES[$r['status']] ?? $r['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
</section>
<?php admin_footer();
