<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$setup = !has_users();
$error = '';

if (current_user()) redirect('index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');

    if ($setup) {
        $pass2 = (string)($_POST['password2'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,40}$/', $user)) $error = 'Kullanıcı adı 3-40 karakter olmalı; harf, rakam, nokta, tire veya alt çizgi içerebilir.';
        elseif (mb_strlen($pass) < 8) $error = 'Şifre en az 8 karakter olmalı.';
        elseif ($pass !== $pass2) $error = 'Şifreler birbiriyle aynı değil.';
        elseif (has_users()) $error = 'Yönetici hesabı zaten oluşturulmuş. Giriş yapın.';
        else {
            db()->prepare('INSERT INTO users(username, password_hash) VALUES(?, ?)')->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)db()->lastInsertId();
            flash('Yönetici hesabınız oluşturuldu. Şimdi Site ayarları bölümünden telefon ve adres bilgilerini girin.');
            redirect('settings.php');
        }
    } else {
        if (too_many_attempts()) {
            $error = 'Çok fazla hatalı deneme yapıldı. 15 dakika sonra tekrar deneyin.';
        } else {
            $st = db()->prepare('SELECT * FROM users WHERE username = ?');
            $st->execute([$user]);
            $u = $st->fetch();
            if ($u && password_verify($pass, $u['password_hash'])) {
                if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
                }
                session_regenerate_id(true);
                $_SESSION['uid'] = (int)$u['id'];
                redirect('index.php');
            }
            record_attempt();
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $setup ? 'Kurulum' : 'Giriş' ?> | Yaren Tur Yönetim</title>
<link rel="icon" href="<?= e(url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(url('admin/admin.css')) ?>?v=1">
</head>
<body class="auth">
<form class="auth__box" method="post" autocomplete="on">
  <div class="auth__logo"><?= brand_html('brandmark') ?></div>
  <h1><?= $setup ? 'Yönetici hesabını oluşturun' : 'Yönetim paneline giriş' ?></h1>
  <?php if ($setup): ?><p class="muted">Bu ilk kurulum. Oluşturacağınız hesapla panele giriş yapacaksınız.</p><?php endif; ?>
  <?php if ($error): ?><div class="alert alert--err"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label class="f"><span>Kullanıcı adı</span><input name="username" required autocomplete="username" value="<?= e($_POST['username'] ?? '') ?>"></label>
  <label class="f"><span>Şifre</span><input name="password" type="password" required autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>"></label>
  <?php if ($setup): ?>
  <label class="f"><span>Şifre (tekrar)</span><input name="password2" type="password" required autocomplete="new-password"></label>
  <?php endif; ?>
  <button class="b b--pri b--full" type="submit"><?= $setup ? 'Hesabı oluştur' : 'Giriş yap' ?></button>
  <a class="auth__back" href="<?= e(url('')) ?>">Siteye dön</a>
</form>
</body>
</html>
