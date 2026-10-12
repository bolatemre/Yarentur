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
    $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 120);
    if ($name === '') { flash('Kurum adını yazın.', 'err'); redirect('clients.php'); }
    try { $logo = upload_image('logo'); } catch (RuntimeException $ex) { flash($ex->getMessage(), 'err'); redirect('clients.php'); }
    $pdo->prepare('INSERT INTO clients(name, logo, sort) VALUES(?, ?, ?)')->execute([$name, $logo, (int)($_POST['sort'] ?? 0)]);
    flash('Referans eklendi.');
    redirect('clients.php');
}

$rows = $pdo->query('SELECT * FROM clients ORDER BY sort, id')->fetchAll();
admin_header('Referanslar', 'clients');
?>
<p class="muted">Hizmet verdiğiniz okul ve firmalar ana sayfada "Hizmet verdiğimiz kurumlar" başlığıyla listelenir. Liste boşsa bu bölüm sitede görünmez.</p>
<form class="card stack" method="post" enctype="multipart/form-data">
  <h2>Referans ekle</h2>
  <?= csrf_field() ?>
  <div class="row3">
    <label class="f"><span>Kurum adı *</span><input name="name" required maxlength="120"></label>
    <label class="f"><span>Logo (isteğe bağlı)</span><input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
    <label class="f"><span>Sıra</span><input type="number" name="sort" value="10"></label>
  </div>
  <div><button class="b b--pri">Referansı ekle</button></div>
</form>
<section class="card">
<?php if (!$rows): ?><p class="empty">Henüz referans eklenmedi.</p><?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Logo</th><th>Kurum</th><th>Sıra</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
      <tr>
        <td><?php if ($c['logo']): ?><img class="mini" src="<?= e(upload_url($c['logo'])) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
        <td><?= e($c['name']) ?></td>
        <td><?= (int)$c['sort'] ?></td>
        <td class="r"><form method="post" data-confirm="Bu referans silinsin mi?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="action" value="delete"><button class="b b--sm b--danger">Sil</button></form></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
</section>
<?php admin_footer();
