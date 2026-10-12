<?php
require __DIR__ . '/_layout.php';
require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        $st = $pdo->prepare('SELECT image FROM vehicles WHERE id = ?'); $st->execute([$id]);
        if ($img = $st->fetchColumn()) delete_upload($img);
        $pdo->prepare('DELETE FROM vehicles WHERE id = ?')->execute([$id]);
        flash('Araç silindi.');
        redirect('vehicles.php');
    }
    $data = [
        'name' => mb_substr(trim((string)($_POST['name'] ?? '')), 0, 80),
        'capacity' => mb_substr(trim((string)($_POST['capacity'] ?? '')), 0, 20),
        'features' => mb_substr(trim((string)($_POST['features'] ?? '')), 0, 300),
        'sort' => (int)($_POST['sort'] ?? 0),
        'active' => empty($_POST['active']) ? 0 : 1,
    ];
    if ($data['name'] === '') { flash('Araç adını yazın.', 'err'); redirect('vehicles.php?edit=' . ($id ?: 'new')); }
    try { $img = upload_image('image'); } catch (RuntimeException $ex) { flash($ex->getMessage(), 'err'); redirect('vehicles.php?edit=' . ($id ?: 'new')); }

    if ($id) {
        $old = $pdo->prepare('SELECT image FROM vehicles WHERE id = ?'); $old->execute([$id]); $oldImg = (string)$old->fetchColumn();
        $data['image'] = $oldImg;
        if ($img) { delete_upload($oldImg); $data['image'] = $img; }
        elseif (!empty($_POST['remove_image'])) { delete_upload($oldImg); $data['image'] = ''; }
        $pdo->prepare('UPDATE vehicles SET name=:name, capacity=:capacity, features=:features, sort=:sort, active=:active, image=:image WHERE id=' . $id)->execute($data);
    } else {
        $data['image'] = $img;
        $pdo->prepare('INSERT INTO vehicles(name, capacity, features, sort, active, image) VALUES(:name, :capacity, :features, :sort, :active, :image)')->execute($data);
    }
    flash('Araç kaydedildi.');
    redirect('vehicles.php');
}

if (isset($_GET['edit'])) {
    $v = ['id' => 0, 'name' => '', 'capacity' => '', 'features' => '', 'image' => '', 'sort' => 10, 'active' => 1];
    if ($_GET['edit'] !== 'new') {
        $st = $pdo->prepare('SELECT * FROM vehicles WHERE id = ?'); $st->execute([(int)$_GET['edit']]);
        $v = $st->fetch() ?: $v;
    }
    admin_header($v['id'] ? 'Aracı düzenle' : 'Yeni araç', 'vehicles');
    ?>
    <p><a href="vehicles.php">← Araç filosu</a></p>
    <form class="card stack" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
      <div class="row2">
        <label class="f"><span>Araç tipi *</span><input name="name" required maxlength="80" value="<?= e($v['name']) ?>" placeholder="Örn. Midibüs"></label>
        <label class="f"><span>Kapasite</span><input name="capacity" maxlength="20" value="<?= e($v['capacity']) ?>" placeholder="Örn. 27+1"></label>
      </div>
      <label class="f"><span>Özellikler</span><input name="features" maxlength="300" value="<?= e($v['features']) ?>" placeholder="Örn. Klima, USB şarj, geniş bagaj"></label>
      <div class="row3">
        <label class="f"><span>Sıra</span><input type="number" name="sort" value="<?= (int)$v['sort'] ?>"></label>
        <label class="chk"><input type="checkbox" name="active" value="1" <?= $v['active'] ? 'checked' : '' ?>> Sitede yayında</label>
      </div>
      <div class="f"><span>Fotoğraf (yatay fotoğraf en iyi görünür)</span>
        <?php if ($v['image']): ?><img class="thumb" src="<?= e(upload_url($v['image'])) ?>" alt=""><label class="chk"><input type="checkbox" name="remove_image" value="1"> Fotoğrafı kaldır</label><?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </div>
      <div><button class="b b--pri">Aracı kaydet</button></div>
    </form>
    <?php if ($v['id']): ?>
    <form method="post" data-confirm="Bu araç silinsin mi?" class="mt"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><input type="hidden" name="action" value="delete"><button class="b b--danger">Aracı sil</button></form>
    <?php endif;
    admin_footer();
    exit;
}

$rows = $pdo->query('SELECT * FROM vehicles ORDER BY sort, id')->fetchAll();
admin_header('Araç filosu', 'vehicles');
?>
<div class="toolbar"><p class="muted">Fotoğrafı olmayan araçlar sitede sade bir kart olarak görünür; gerçek araç fotoğrafı eklemeniz önerilir.</p><a class="b b--pri" href="?edit=new">Yeni araç ekle</a></div>
<section class="card">
<?php if (!$rows): ?><p class="empty">Henüz araç eklenmedi.</p><?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th></th><th>Araç</th><th>Kapasite</th><th>Özellikler</th><th>Durum</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $v): ?>
      <tr>
        <td><?php if ($v['image']): ?><img class="mini" src="<?= e(upload_url($v['image'])) ?>" alt=""><?php endif; ?></td>
        <td><strong><?= e($v['name']) ?></strong></td>
        <td><?= e($v['capacity']) ?></td>
        <td class="muted"><?= e($v['features']) ?></td>
        <td><?= $v['active'] ? '<span class="st st--anlasildi">Yayında</span>' : '<span class="st st--iptal">Gizli</span>' ?></td>
        <td class="r"><a class="b b--sm" href="?edit=<?= (int)$v['id'] ?>">Düzenle</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
</section>
<?php admin_footer();
