<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url('iletisim.php') . '#teklif');
csrf_check();

$back = url('iletisim.php') . '#teklif';
// Bot tuzağı
if (!empty($_POST['website'])) redirect($back);

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
];

$err = null;
if ($r['name'] === '' || $r['phone'] === '') $err = 'Ad soyad ve telefon alanlarını doldurun.';
elseif (strlen(preg_replace('/\D/', '', $r['phone'])) < 10) $err = 'Telefon numarasını alan koduyla birlikte yazın.';
elseif ($r['email'] !== '' && !filter_var($r['email'], FILTER_VALIDATE_EMAIL)) $err = 'E-posta adresi geçerli görünmüyor.';
elseif (empty($_POST['consent'])) $err = 'Göndermek için iletişim onayı kutusunu işaretleyin.';

// Aynı IP'den 10 dakikada en fazla 5 talep
if (!$err) {
    $st = db()->prepare("SELECT COUNT(*) FROM requests WHERE ip = ? AND created_at > datetime('now', 'localtime', '-10 minutes')");
    $st->execute([client_ip()]);
    if ((int)$st->fetchColumn() >= 5) $err = 'Kısa sürede çok fazla talep gönderildi. Lütfen bizi telefonla arayın.';
}

if ($err) {
    $_SESSION['old_form'] = $r;
    flash($err, 'err');
    redirect($back);
}

$st = db()->prepare('INSERT INTO requests(name, phone, email, service, route_from, route_to, passengers, travel_date, message, ip) VALUES(?,?,?,?,?,?,?,?,?,?)');
$st->execute([...array_values($r), client_ip()]);
notify_new_request($r);

flash('Teklif isteğiniz bize ulaştı. En kısa sürede sizi arayacağız.', 'ok');
redirect($back);
