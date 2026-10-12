<?php
require __DIR__ . '/_layout.php';
$me = require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'password') {
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?'); $st->execute([$me['id']]);
        $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['new'] ?? ''); $new2 = (string)($_POST['new2'] ?? '');
        if (!password_verify($cur, (string)$st->fetchColumn())) flash('Mevcut şifre hatalı.', 'err');
        elseif (mb_strlen($new) < 8) flash('Yeni şifre en az 8 karakter olmalı.', 'err');
        elseif ($new !== $new2) flash('Yeni şifreler birbiriyle aynı değil.', 'err');
        else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            session_regenerate_id(true);
            flash('Şifreniz değiştirildi.');
        }
    } elseif ($action === 'add') {
        $u = trim((string)($_POST['username'] ?? '')); $p = (string)($_POST['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,40}$/', $u)) flash('Kullanıcı adı 3-40 karakter olmalı; harf, rakam, nokta, tire veya alt çizgi içerebilir.', 'err');
        elseif (mb_strlen($p) < 8) flash('Şifre en az 8 karakter olmalı.', 'err');
        else {
            try {
                $pdo->prepare('INSERT INTO users(username, password_hash) VALUES(?, ?)')->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
                flash('Yönetici eklendi: ' . $u);
            } catch (PDOException $ex) { flash('Bu kullanıcı adı zaten var.', 'err'); }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$me['id']) flash('Kendi hesabınızı silemezsiniz.', 'err');
        else { $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]); flash('Yönetici silindi.'); }
    }
    redirect('account.php');
}

$users = $pdo->query('SELECT id, username, created_at FROM users ORDER BY id')->fetchAll();
admin_header('Hesabım', 'account');
?>
<div class="grid2">
  <form class="card stack" method="post">
    <h2>Şifre değiştir</h2>
    <?= csrf_field() ?><input type="hidden" name="action" value="password">
    <label class="f"><span>Mevcut şifre</span><input type="password" name="current" required autocomplete="current-password"></label>
    <label class="f"><span>Yeni şifre (en az 8 karakter)</span><input type="password" name="new" required autocomplete="new-password"></label>
    <label class="f"><span>Yeni şifre (tekrar)</span><input type="password" name="new2" required autocomplete="new-password"></label>
    <div><button class="b b--pri">Şifreyi değiştir</button></div>
  </form>
  <section class="card stack">
    <h2>Yöneticiler</h2>
    <ul class="ulist">
      <?php foreach ($users as $u): ?>
      <li><span><?= e($u['username']) ?><?= $u['id'] == $me['id'] ? ' <em class="muted">(siz)</em>' : '' ?></span>
        <?php if ($u['id'] != $me['id']): ?><form method="post" data-confirm="Bu yönetici silinsin mi?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="b b--sm b--danger">Sil</button></form><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <form method="post" class="stack">
      <h3>Yönetici ekle</h3>
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <label class="f"><span>Kullanıcı adı</span><input name="username" required autocomplete="off"></label>
      <label class="f"><span>Şifre</span><input type="password" name="password" required autocomplete="new-password"></label>
      <div><button class="b">Yöneticiyi ekle</button></div>
    </form>
  </section>
</div>
<?php admin_footer();
