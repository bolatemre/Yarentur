<?php
require __DIR__ . '/_layout.php';
require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        $st = $pdo->prepare('SELECT logo FROM clients WHERE id = ?'); $st->execute([$id]);
        if ($img = $st->fetchColumn()) delete_upload($img);
        $pdo->prepare('DELETE FROM clients WHERE id = ?')->execute([$id]);
        flash('Referans silindi.');
        redirect('clients.php');
    }
    $t = fn(string $k, int $max) => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max);
    $name = $t('name', 120);
    $back = $id ? 'clients.php?id=' . $id : 'clients.php';
    if ($name === '') { flash('Kurum adını yazın.', 'err'); redirect($back); }
    try { $logo = upload_image('logo'); } catch (RuntimeException $ex) { flash($ex->getMessage(), 'err'); redirect($back); }
    $data = [$name, $t('sector', 80), $t('detail', 200), $t('since', 20), (int)($_POST['sort'] ?? 0), empty($_POST['active']) ? 0 : 1];

    if ($id) {
        $st = $pdo->prepare('SELECT logo FROM clients WHERE id = ?'); $st->execute([$id]);
        $oldLogo = (string)$st->fetchColumn();
        if ($logo) delete_upload($oldLogo);
        elseif (!empty($_POST['remove_logo'])) { delete_upload($oldLogo); $logo = ''; }
        else $logo = $oldLogo;
        $pdo->prepare('UPDATE clients SET name = ?, sector = ?, detail = ?, since = ?, sort = ?, active = ?, logo = ? WHERE id = ?')->execute([...$data, $logo, $id]);
        flash('Referans güncellendi.');
    } else {
        $pdo->prepare('INSERT INTO clients(name, sector, detail, since, sort, active, logo) VALUES(?,?,?,?,?,?,?)')->execute([...$data, $logo]);
        flash('Referans eklendi.');
    }
    redirect('clients.php');
}

$edit = null;
if (isset($_GET['id'])) {
    $st = $pdo->prepare('SELECT * FROM clients WHERE id = ?'); $st->execute([(int)$_GET['id']]);
    $edit = $st->fetch() ?: null;
}
$rows = $pdo->query('SELECT * FROM clients ORDER BY sort, id')->fetchAll();
admin_header($edit ? 'Referansı düzenle' : 'Referanslar', 'clients');
$v = fn(string $k, string $d = '') => e((string)($edit[$k] ?? $d));
?>
<p class="muted">Kurumlar sitede iki şekilde görünür: logolar "Hizmet verdiğimiz kurumlar" şeridinde listelenir. <b>Çalışma özeti</b> doldurulan kurumlar ayrıca "Referans çalışmalarımız" bölümünde kart olarak gösterilir (örn. "3 vardiya, 14 araç, günlük 420 personel").</p>
<?php if ($edit): ?><p><a href="clients.php">← Tüm referanslar</a></p><?php endif; ?>
<form class="card stack" method="post" enctype="multipart/form-data">
  <h2><?= $edit ? 'Referansı düzenle' : 'Referans ekle' ?></h2>
  <?= csrf_field() ?>
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
  <div class="row3">
    <label class="f"><span>Kurum adı *</span><input name="name" required maxlength="120" value="<?= $v('name') ?>"></label>
    <label class="f"><span>Sektör</span><input name="sector" maxlength="80" placeholder="Örn. Tekstil, Otomotiv yan sanayi" value="<?= $v('sector') ?>"></label>
    <label class="f"><span>Birlikte çalışmaya başlanan yıl</span><input name="since" maxlength="20" placeholder="Örn. 2019" value="<?= $v('since') ?>"></label>
  </div>
  <label class="f"><span>Çalışma özeti (doldurulursa kart olarak gösterilir)</span><input name="detail" maxlength="200" placeholder="Örn. 3 vardiya, 14 araç, günlük 420 personel" value="<?= $v('detail') ?>"></label>
  <div class="row3">
    <label class="f"><span>Logo (isteğe bağlı)</span><input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
    <label class="f"><span>Sıra</span><input type="number" name="sort" value="<?= $v('sort', '10') ?>"></label>
    <label class="chk"><input type="checkbox" name="active" value="1" <?= !$edit || $edit['active'] ? 'checked' : '' ?>> Sitede göster</label>
  </div>
  <?php if ($edit && $edit['logo']): ?>
  <div class="logo-row"><img class="thumb" src="<?= e(upload_url($edit['logo'])) ?>" alt=""><label class="chk"><input type="checkbox" name="remove_logo" value="1"> Logoyu kaldır</label></div>
  <?php endif; ?>
  <div><button class="b b--pri"><?= $edit ? 'Kaydet' : 'Referansı ekle' ?></button></div>
</form>
<?php if (!$edit): ?>
<section class="card">
<?php if (!$rows): ?><p class="empty">Henüz referans eklenmedi.</p><?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Logo</th><th>Kurum</th><th>Çalışma özeti</th><th>Sıra</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
      <tr>
        <td><?php if ($c['logo']): ?><img class="mini" src="<?= e(upload_url($c['logo'])) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
        <td><a href="?id=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a><?php if ($c['sector']): ?><br><small class="muted"><?= e($c['sector']) ?></small><?php endif; ?><?php if (!$c['active']): ?> <span class="st">Gizli</span><?php endif; ?></td>
        <td><?= e($c['detail'] ?: '—') ?></td>
        <td><?= (int)$c['sort'] ?></td>
        <td class="r">
          <a class="b b--sm" href="?id=<?= (int)$c['id'] ?>">Düzenle</a>
          <form method="post" data-confirm="Bu referans silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="action" value="delete"><button class="b b--sm b--danger">Sil</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
</section>
<?php endif; ?>
<?php admin_footer();
