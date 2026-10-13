<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url('iletisim.php') . '#teklif');
csrf_check();

$back = url('iletisim.php') . '#teklif';
// Bot tuzağı
if (!empty($_POST['website'])) redirect($back);

// Güvenlik sorusu: beklenen cevap tek kullanımlık
$cap_expected = $_SESSION['captcha'] ?? null;
unset($_SESSION['captcha']);
$cap_answer = preg_replace('/\D/', '', (string)($_POST['captcha'] ?? ''));

$f = fn(string $k, int $max) => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max);
$r = [
    'name'        => $f('name', 120),
    'phone'       => $f('phone', 30),
    'email'       => $f('email', 120),
    'service'     => $f('service', 120),
    'route_from'  => $f('route_from', 120),
    'route_to'    => $f('route_to', 120),
    'passengers'  => $f('passengers', 20),
    'travel_date' => $f('travel_date', 20),
    'message'     => $f('message', 2000),
    'company'     => $f('company', 160),
    'shift'       => $f('shift', 60),
];
if (!in_array($r['shift'], shift_options(), true)) $r['shift'] = '';
if ($r['travel_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $r['travel_date'])) $r['travel_date'] = '';

$err = null; $step = 1;
if ($r['name'] === '' || $r['phone'] === '') $err = 'Yetkili ad soyad ve telefon alanlarını doldurun.';
elseif (strlen(preg_replace('/\D/', '', $r['phone'])) < 10) $err = 'Telefon numarasını alan koduyla birlikte yazın.';
elseif ($r['email'] !== '' && !filter_var($r['email'], FILTER_VALIDATE_EMAIL)) $err = 'E-posta adresi geçerli görünmüyor.';
elseif ($cap_expected === null || $cap_answer === '' || (int)$cap_answer !== (int)$cap_expected) { $err = 'Güvenlik sorusunun cevabı hatalı. Lütfen yeni soruyu cevaplayıp tekrar gönderin.'; $step = 3; }
elseif (empty($_POST['consent'])) { $err = 'Göndermek için iletişim onayı kutusunu işaretleyin.'; $step = 3; }

// Aynı IP'den 10 dakikada en fazla 5 talep
if (!$err) {
    $st = db()->prepare("SELECT COUNT(*) FROM requests WHERE ip = ? AND created_at > datetime('now', 'localtime', '-10 minutes')");
    $st->execute([client_ip()]);
    if ((int)$st->fetchColumn() >= 5) { $err = 'Kısa sürede çok fazla talep gönderildi. Lütfen bizi telefonla arayın.'; $step = 3; }
}

if ($err) {
    $_SESSION['old_form'] = $r + ['_step' => $step];
    flash($err, 'err');
    redirect($back);
}

$st = db()->prepare('INSERT INTO requests(name, phone, email, service, route_from, route_to, passengers, travel_date, message, company, shift, ip) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
$st->execute([...array_values($r), client_ip()]);
notify_new_request($r, (int)db()->lastInsertId());

flash('Teklif isteğiniz bize ulaştı. En kısa sürede sizi arayacağız.', 'ok');
redirect($back);
