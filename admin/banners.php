<?php
require __DIR__ . '/_layout.php';
require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        try {
            $img = upload_image('image');
        } catch (RuntimeException $ex) {
            flash($ex->getMessage(), 'err');
            redirect('banners.php');
        }
        if (!$img) { flash('Bir görsel seçin.', 'err'); redirect('banners.php'); }
        $sort = (int)$pdo->query('SELECT COALESCE(MAX(sort), 0) + 1 FROM banners')->fetchColumn();
        $pdo->prepare('INSERT INTO banners(image, focus, sort) VALUES(?, 50, ?)')->execute([$img, $sort]);
        flash('Banner görseli eklendi.');
    } elseif ($action === 'save') {
        $focus = max(0, min(100, (int)($_POST['focus'] ?? 50)));
        $pdo->prepare('UPDATE banners SET focus = ?, sort = ?, active = ? WHERE id = ?')
            ->execute([$focus, (int)($_POST['sort'] ?? 0), empty($_POST['active']) ? 0 : 1, $id]);
        flash('Banner güncellendi.');
    } elseif ($action === 'delete') {
        $st = $pdo->prepare('SELECT image FROM banners WHERE id = ?'); $st->execute([$id]);
        if ($img = $st->fetchColumn()) delete_upload($img);
        $pdo->prepare('DELETE FROM banners WHERE id = ?')->execute([$id]);
        flash('Banner silindi.');
    }
    redirect('banners.php');
}

$rows = $pdo->query('SELECT * FROM banners ORDER BY sort, id')->fetchAll();
admin_header('Banner görselleri', 'banners');
?>
<p class="muted">Ana sayfanın en üstünde dönen fotoğraflar. En iyi sonuç için en az 1920 piksel genişliğinde, yatay ve net fotoğraflar kullanın. Birden fazla görsel eklerseniz 6 saniyede bir değişir.</p>
<form class="card stack" method="post" enctype="multipart/form-data">
  <h2>Görsel ekle</h2>
  <?= csrf_field() ?><input type="hidden" name="action" value="add">
  <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
  <div><button class="b b--pri">Görseli ekle</button></div>
</form>

<?php if (!$rows): ?>
<section class="card"><p class="empty">Henüz banner görseli yok. Görsel eklenene kadar ana sayfanın üst kısmı koyu zeminle gösterilir.</p></section>
<?php endif; ?>

<?php foreach ($rows as $b): ?>
<section class="card banner-row">
  <div class="banner-prev">
    <figure><img src="<?= e(upload_url($b['image'])) ?>" alt="" style="object-position: <?= (int)$b['focus'] ?>% 50%"><figcaption>Bilgisayar</figcaption></figure>
    <figure class="banner-prev__m"><img src="<?= e(upload_url($b['image'])) ?>" alt="" style="object-position: <?= (int)$b['focus'] ?>% 50%"><figcaption>Telefon</figcaption></figure>
  </div>
  <form method="post" class="stack">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
    <label class="f"><span>Fotoğrafın odak noktası: <b class="focus-val"><?= (int)$b['focus'] ?></b>% (0 = sol, 100 = sağ)</span>
      <input type="range" name="focus" min="0" max="100" value="<?= (int)$b['focus'] ?>" oninput="var v=this.value;this.closest('.banner-row').querySelectorAll('.banner-prev img').forEach(function(i){i.style.objectPosition=v+'% 50%'});this.closest('form').querySelector('.focus-val').textContent=this.value">
    </label>
    <div class="row2">
      <label class="f"><span>Sıra</span><input type="number" name="sort" value="<?= (int)$b['sort'] ?>"></label>
      <label class="chk"><input type="checkbox" name="active" value="1" <?= $b['active'] ? 'checked' : '' ?>> Yayında</label>
    </div>
    <div class="row-btns"><button class="b b--pri">Kaydet</button></div>
  </form>
  <form method="post" data-confirm="Bu görsel silinsin mi?" class="banner-del"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><button class="b b--sm b--danger">Sil</button></form>
</section>
<?php endforeach; ?>
<?php admin_footer();
