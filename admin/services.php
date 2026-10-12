<?php
require __DIR__ . '/_layout.php';
require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $st = $pdo->prepare('SELECT image FROM services WHERE id = ?'); $st->execute([$id]);
        if ($img = $st->fetchColumn()) delete_upload($img);
        $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
        flash('Hizmet silindi.');
        redirect('services.php');
    }

    $title = trim((string)($_POST['title'] ?? ''));
    $slug = slugify(trim((string)($_POST['slug'] ?? '')) ?: $title);
    $data = [
        'title' => mb_substr($title, 0, 120),
        'slug' => $slug,
        'summary' => mb_substr(trim((string)($_POST['summary'] ?? '')), 0, 300),
        'body' => mb_substr(trim((string)($_POST['body'] ?? '')), 0, 20000),
        'icon' => array_key_exists($_POST['icon'] ?? '', icon_names()) ? $_POST['icon'] : 'bus',
        'sort' => (int)($_POST['sort'] ?? 0),
        'active' => empty($_POST['active']) ? 0 : 1,
    ];
    if ($title === '') { flash('Hizmet adını yazın.', 'err'); redirect('services.php?edit=' . ($id ?: 'new')); }

    $dup = $pdo->prepare('SELECT id FROM services WHERE slug = ? AND id != ?'); $dup->execute([$slug, $id]);
    if ($dup->fetchColumn()) { flash('Bu sayfa adresi başka bir hizmette kullanılıyor. Farklı bir adres yazın.', 'err'); redirect('services.php?edit=' . ($id ?: 'new')); }

    try { $img = upload_image('image'); } catch (RuntimeException $ex) { flash($ex->getMessage(), 'err'); redirect('services.php?edit=' . ($id ?: 'new')); }

    if ($id) {
        $old = $pdo->prepare('SELECT image FROM services WHERE id = ?'); $old->execute([$id]); $oldImg = (string)$old->fetchColumn();
        $data['image'] = $oldImg;
        if ($img) { delete_upload($oldImg); $data['image'] = $img; }
        elseif (!empty($_POST['remove_image'])) { delete_upload($oldImg); $data['image'] = ''; }
        $pdo->prepare('UPDATE services SET title=:title, slug=:slug, summary=:summary, body=:body, icon=:icon, sort=:sort, active=:active, image=:image WHERE id=' . $id)->execute($data);
    } else {
        $data['image'] = $img;
        $pdo->prepare('INSERT INTO services(title, slug, summary, body, icon, sort, active, image) VALUES(:title, :slug, :summary, :body, :icon, :sort, :active, :image)')->execute($data);
    }
    flash('Hizmet kaydedildi.');
    redirect('services.php');
}

if (isset($_GET['edit'])) {
    $s = ['id' => 0, 'title' => '', 'slug' => '', 'summary' => '', 'body' => '', 'icon' => 'bus', 'image' => '', 'sort' => 10, 'active' => 1];
    if ($_GET['edit'] !== 'new') {
        $st = $pdo->prepare('SELECT * FROM services WHERE id = ?'); $st->execute([(int)$_GET['edit']]);
        $s = $st->fetch() ?: $s;
    }
    admin_header($s['id'] ? 'Hizmeti düzenle' : 'Yeni hizmet', 'services');
    ?>
    <p><a href="services.php">← Hizmetler</a></p>
    <form class="card stack" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
      <div class="row2">
        <label class="f"><span>Hizmet adı *</span><input name="title" required maxlength="120" value="<?= e($s['title']) ?>"></label>
        <label class="f"><span>Sayfa adresi</span><input name="slug" maxlength="120" value="<?= e($s['slug']) ?>" placeholder="Boş bırakırsanız addan oluşturulur"><small>hizmet.php?s=<b><?= e($s['slug'] ?: 'ornek-hizmet') ?></b></small></label>
      </div>
      <label class="f"><span>Kısa açıklama (ana sayfada görünür)</span><textarea name="summary" rows="2" maxlength="300"><?= e($s['summary']) ?></textarea></label>
      <label class="f"><span>Detay metni (paragrafları boş satırla ayırın)</span><textarea name="body" rows="10"><?= e($s['body']) ?></textarea></label>
      <div class="row3">
        <label class="f"><span>İkon</span><select name="icon"><?php foreach (icon_names() as $k => $v): ?><option value="<?= $k ?>" <?= $s['icon'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
        <label class="f"><span>Sıra (küçük olan önce)</span><input type="number" name="sort" value="<?= (int)$s['sort'] ?>"></label>
        <label class="chk"><input type="checkbox" name="active" value="1" <?= $s['active'] ? 'checked' : '' ?>> Sitede yayında</label>
      </div>
      <div class="f"><span>Görsel (detay sayfasında görünür)</span>
        <?php if ($s['image']): ?><img class="thumb" src="<?= e(upload_url($s['image'])) ?>" alt=""><label class="chk"><input type="checkbox" name="remove_image" value="1"> Görseli kaldır</label><?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </div>
      <div><button class="b b--pri">Hizmeti kaydet</button></div>
    </form>
    <?php if ($s['id']): ?>
    <form method="post" data-confirm="Bu hizmet silinsin mi?" class="mt"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="action" value="delete"><button class="b b--danger">Hizmeti sil</button></form>
    <?php endif;
    admin_footer();
    exit;
}

$rows = $pdo->query('SELECT * FROM services ORDER BY sort, id')->fetchAll();
admin_header('Hizmetler', 'services');
?>
<div class="toolbar"><p class="muted">Ana sayfada ilk iki hizmet büyük kutularda, diğerleri liste halinde gösterilir.</p><a class="b b--pri" href="?edit=new">Yeni hizmet ekle</a></div>
<section class="card">
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Sıra</th><th>Hizmet</th><th>Kısa açıklama</th><th>Durum</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $s): ?>
      <tr>
        <td><?= (int)$s['sort'] ?></td>
        <td><strong><?= e($s['title']) ?></strong></td>
        <td class="muted"><?= e(mb_strimwidth($s['summary'], 0, 90, '…')) ?></td>
        <td><?= $s['active'] ? '<span class="st st--anlasildi">Yayında</span>' : '<span class="st st--iptal">Gizli</span>' ?></td>
        <td class="r"><a class="b b--sm" href="?edit=<?= (int)$s['id'] ?>">Düzenle</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<?php admin_footer();
