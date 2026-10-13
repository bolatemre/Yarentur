<?php
declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------- Ayarlar ---------- */

function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT key, value FROM settings') as $r) $cache[$r['key']] = $r['value'];
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return ($s[$key] ?? '') !== '' ? $s[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    $st = db()->prepare('INSERT INTO settings(key, value) VALUES(?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $st->execute([$key, $value]);
}

/* ---------- URL ---------- */

function base_url(): string
{
    static $base = null;
    if ($base === null) {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $dir = preg_replace('#/admin$#', '', $dir);
        $base = rtrim($dir, '/');
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function upload_url(string $file): string
{
    return url('uploads/' . rawurlencode($file));
}

function logo_url(): string
{
    $logo = setting('logo');
    if ($logo) return upload_url($logo);
    // Panelden logo yüklenmediyse assets/img içindeki logo dosyası kullanılır
    foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.svg', 'logo.webp'] as $f) {
        if (is_file(ROOT . '/assets/img/' . $f)) return url('assets/img/' . $f);
    }
    return '';
}

/** Logo varsa görsel, yoksa yazı tabanlı marka işareti */
function brand_html(string $class = 'brandmark'): string
{
    $src = logo_url();
    $name = setting('site_title', 'Yaren Tur');
    if ($src) return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . e($name) . '">';
    return '<span class="' . e($class) . ' brandmark--text"><svg viewBox="0 0 64 64" aria-hidden="true"><rect width="64" height="64" rx="12" fill="#018231"/><path d="M16 14h9v16c0 3 2 5 5 5h4c3 0 5-2 5-5V14h9v24c0 7-5 12-12 12H20v-7h19c1 0 2-1 2-2v-1c-1 1-3 1-5 1h-8c-7 0-12-5-12-12z" fill="#fff"/></svg><span>YARENTUR</span></span>';
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/* ---------- Güvenlik ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(400);
        exit('Oturum süresi doldu. Sayfayı yenileyip tekrar deneyin.');
    }
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Oturum ---------- */

function current_user(): ?array
{
    if (empty($_SESSION['uid'])) return null;
    $st = db()->prepare('SELECT id, username FROM users WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    return $st->fetch() ?: null;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) redirect(url('admin/login.php'));
    return $u;
}

function has_users(): bool
{
    return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
}

function too_many_attempts(): bool
{
    $pdo = db();
    $pdo->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([time() - 900]);
    $st = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?');
    $st->execute([client_ip()]);
    return (int)$st->fetchColumn() >= 8;
}

function record_attempt(): void
{
    db()->prepare('INSERT INTO login_attempts(ip, at) VALUES(?, ?)')->execute([client_ip(), time()]);
}

/* ---------- Yardımcılar ---------- */

function slugify(string $s): string
{
    $map = ['ç'=>'c','Ç'=>'c','ğ'=>'g','Ğ'=>'g','ı'=>'i','I'=>'i','İ'=>'i','ö'=>'o','Ö'=>'o','ş'=>'s','Ş'=>'s','ü'=>'u','Ü'=>'u'];
    $s = strtr($s, $map);
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'hizmet';
}

function paragraphs(string $text): string
{
    $out = '';
    foreach (preg_split("/\R{2,}/", trim($text)) as $p) {
        if (trim($p) === '') continue;
        $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
    }
    return $out;
}

function highlights(): array
{
    $items = [];
    foreach (preg_split('/\R/', setting('highlights')) as $line) {
        if (trim($line) === '') continue;
        [$t, $d] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
        $items[] = ['title' => $t, 'text' => $d];
    }
    return $items;
}

function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

function whatsapp_href(string $text = ''): string
{
    $n = preg_replace('/[^0-9]/', '', setting('whatsapp'));
    if ($n === '') return '';
    return 'https://wa.me/' . $n . ($text ? '?text=' . rawurlencode($text) : '');
}

function active_services(): array
{
    return db()->query('SELECT * FROM services WHERE active = 1 ORDER BY sort, id')->fetchAll();
}

/**
 * Görsel yükleme. Başarılıysa yeni dosya adını, dosya yoksa '' döner, hata olursa exception atar.
 */
function upload_image(string $field): string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Dosya yüklenemedi (hata kodu ' . $f['error'] . ').');
    if ($f['size'] > 6 * 1024 * 1024) throw new RuntimeException('Görsel en fazla 6 MB olabilir.');

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string)finfo_file($fi, $f['tmp_name']);
    } else {
        $info = @getimagesize($f['tmp_name']);
        $mime = $info['mime'] ?? '';
    }
    if (!isset($allowed[$mime])) throw new RuntimeException('Yalnızca JPG, PNG, WEBP veya GIF yükleyebilirsiniz.');

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('Görsel kaydedilemedi. uploads klasörünün yazma iznini kontrol edin.');
    }
    return $name;
}

function delete_upload(string $name): void
{
    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) return;
    $p = UPLOAD_DIR . '/' . $name;
    if (is_file($p)) @unlink($p);
}

/** Teklif formundaki vardiya seçenekleri */
function shift_options(): array
{
    return ['Tek vardiya', 'İki vardiya', 'Üç vardiya', 'Ofis saatleri', 'Tek seferlik / organizasyon'];
}

function request_summary(array $r): string
{
    $lines = [
        'Firma / kurum' => $r['company'] ?? '',
        'Yetkili' => $r['name'],
        'Telefon' => $r['phone'],
        'E-posta' => $r['email'],
        'Hizmet' => $r['service'],
        'Kişi sayısı' => $r['passengers'],
        'Vardiya düzeni' => $r['shift'] ?? '',
        'Kalkış / ilçeler' => $r['route_from'],
        'İş yeri / varış' => $r['route_to'],
        'Başlangıç tarihi' => $r['travel_date'] ? date('d.m.Y', strtotime($r['travel_date'])) : '',
    ];
    $out = '';
    foreach ($lines as $k => $v) if (trim((string)$v) !== '') $out .= "$k: $v\n";
    if (trim($r['message']) !== '') $out .= "\nMesaj:\n{$r['message']}\n";
    return $out;
}

function site_host(): string
{
    return preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'yarentur.com.tr');
}

function notify_new_request(array $r, int $id = 0): void
{
    $summary = request_summary($r);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https' : 'http';
    $link = $id ? $scheme . '://' . site_host() . url('admin/requests.php?id=' . $id) : '';

    // Telegram
    $title = ($r['company'] ?? '') !== '' ? $r['company'] . ' (' . $r['name'] . ')' : $r['name'];
    telegram_send("🚌 Yeni teklif talebi: $title\n\n" . $summary . ($link ? "\nPanelde aç: $link" : ''));

    // E-posta
    $to = setting('notify_email');
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return;
    $host = preg_replace('/:\d+$/', '', site_host());
    $subject = '=?UTF-8?B?' . base64_encode('Yeni teklif talebi: ' . $title) . '?=';
    $body = "Web sitesinden yeni bir teklif talebi geldi.\n\n" . $summary . ($link ? "\nPanelde aç: $link\n" : '');
    $headers = "From: Yaren Tur Web <noreply@{$host}>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    if (filter_var($r['email'], FILTER_VALIDATE_EMAIL)) $headers .= "Reply-To: {$r['email']}\r\n";
    @mail($to, $subject, $body, $headers);
}

/* ---------- Telegram ---------- */

/** Basit HTTP isteği (cURL varsa onunla). Hata olursa null döner. */
function http_request(string $url, ?array $post = null): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 5]);
        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return is_string($res) ? $res : null;
    }
    $opts = ['http' => ['timeout' => 8, 'ignore_errors' => true]];
    if ($post !== null) {
        $opts['http'] += ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($post)];
    }
    $res = @file_get_contents($url, false, stream_context_create($opts));
    return is_string($res) ? $res : null;
}

function telegram_token_ok(string $t): bool
{
    return (bool)preg_match('/^\d{5,}:[A-Za-z0-9_-]{20,}$/', $t);
}

/** Telegram mesajı gönderir. Ayarlar boşsa sessizce false döner. */
function telegram_send(string $text, ?string &$error = null): bool
{
    $token = setting('telegram_token');
    $chat = setting('telegram_chat');
    if ($token === '' || $chat === '') { $error = 'Bot token ve sohbet ID girilmemiş.'; return false; }
    if (!telegram_token_ok($token)) { $error = 'Bot token biçimi hatalı.'; return false; }
    $res = http_request('https://api.telegram.org/bot' . $token . '/sendMessage', [
        'chat_id' => $chat,
        'text' => mb_substr($text, 0, 4000),
        'disable_web_page_preview' => 'true',
    ]);
    if ($res === null) { $error = 'Sunucu Telegram\'a bağlanamadı.'; return false; }
    $j = json_decode($res, true);
    if (empty($j['ok'])) { $error = 'Telegram yanıtı: ' . ($j['description'] ?? 'bilinmeyen hata'); return false; }
    return true;
}

/** Bota son mesaj atılan sohbetin ID'sini bulur */
function telegram_find_chat(?string &$error = null): ?array
{
    $token = setting('telegram_token');
    if (!telegram_token_ok($token)) { $error = 'Önce geçerli bir bot token girip kaydedin.'; return null; }
    $res = http_request('https://api.telegram.org/bot' . $token . '/getUpdates');
    if ($res === null) { $error = 'Sunucu Telegram\'a bağlanamadı.'; return null; }
    $j = json_decode($res, true);
    if (empty($j['ok'])) { $error = 'Telegram yanıtı: ' . ($j['description'] ?? 'bilinmeyen hata'); return null; }
    foreach (array_reverse($j['result'] ?? []) as $u) {
        $chat = $u['message']['chat'] ?? $u['channel_post']['chat'] ?? $u['my_chat_member']['chat'] ?? null;
        if ($chat && isset($chat['id'])) {
            $name = $chat['title'] ?? trim(($chat['first_name'] ?? '') . ' ' . ($chat['last_name'] ?? ''));
            return ['id' => (string)$chat['id'], 'name' => $name ?: 'sohbet'];
        }
    }
    $error = 'Bota henüz mesaj gelmemiş. Telegram\'da botunuza bir mesaj yazıp tekrar deneyin.';
    return null;
}

/* ---------- PDF yükleme ---------- */

function upload_pdf(string $field): string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Dosya yüklenemedi (hata kodu ' . $f['error'] . ').');
    if ($f['size'] > 15 * 1024 * 1024) throw new RuntimeException('PDF en fazla 15 MB olabilir.');
    $head = (string)file_get_contents($f['tmp_name'], false, null, 0, 5);
    if ($head !== '%PDF-') throw new RuntimeException('Yalnızca PDF dosyası yükleyebilirsiniz.');
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = 'tanitim-' . date('Ymd') . '-' . bin2hex(random_bytes(4)) . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('Dosya kaydedilemedi. uploads klasörünün yazma iznini kontrol edin.');
    }
    return $name;
}

function brochure_url(): string
{
    $b = setting('brochure');
    return ($b !== '' && is_file(UPLOAD_DIR . '/' . $b)) ? upload_url($b) : '';
}

/* ---------- İkonlar ---------- */

function icon(string $name, string $class = 'ico'): string
{
    $paths = [
        'school'    => '<path d="M3 10l9-5 9 5-9 5-9-5z"/><path d="M7 12.2V16c0 1.5 2.2 3 5 3s5-1.5 5-3v-3.8"/><path d="M21 10v5"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/>',
        'map'       => '<path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/>',
        'plane'     => '<path d="M2 16l20-6-2-3-7 2-5-6-2 1 3 6-5 1.5-2-2-1.5.5 1.5 4z"/><path d="M3 21h18"/>',
        'star'      => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3z"/>',
        'key'       => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M17 6l3 3M14 9l2 2"/>',
        'bus'       => '<rect x="4" y="3" width="16" height="15" rx="3"/><path d="M4 11h16M8 18v3M16 18v3"/><circle cx="8" cy="14.5" r="1"/><circle cx="16" cy="14.5" r="1"/>',
        'phone'     => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2z"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'pin'       => '<path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'wa'        => '<path d="M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 0 1-1.8-1.8l.8-1-1-2L9 9.5z"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chart'     => '<path d="M4 4v16h16"/><path d="M8 15l4-4 3 3 5-6"/>',
        'shield'    => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
        'smile'     => '<circle cx="12" cy="12" r="9"/><path d="M8.5 14.5a4.5 4.5 0 0 0 7 0"/><path d="M9 9.5h.01M15 9.5h.01"/>',
        'monitor'   => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        'mobile'    => '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
        'file'      => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M14 3v5h5"/><path d="M12 12v6M9 15l3 3 3-3"/>',
        'doc'       => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'pinmap'    => '<path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z"/><path d="M9 9h6"/>',
        'wrench'    => '<path d="M14.5 6.5a4 4 0 0 0-5.3 5.3L4 17l3 3 5.2-5.2a4 4 0 0 0 5.3-5.3l-2.4 2.4-2.6-.4-.4-2.6 2.4-2.4z"/>',
        'swap'      => '<path d="M4 8h13l-3-3M20 16H7l3 3"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'building'  => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1M10 21v-3h4v3"/>',
    ];
    $p = $paths[$name] ?? $paths['bus'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

function icon_names(): array
{
    return [
        'school' => 'Okul', 'briefcase' => 'Çanta (personel)', 'map' => 'Harita (tur)', 'plane' => 'Uçak',
        'star' => 'Yıldız (VIP)', 'key' => 'Anahtar (kiralama)', 'bus' => 'Otobüs',
    ];
}

function vehicle_silhouette(): string
{
    return '<svg class="veh-sil" viewBox="0 0 220 90" aria-hidden="true"><path d="M14 66V24c0-7 5-12 12-12h150c12 0 22 8 26 20l6 18v16" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="M14 66h196" stroke="currentColor" stroke-width="3"/><path d="M30 24h38v20H30zM78 24h38v20H78zM126 24h38v20h-38zM174 24h14c6 0 10 4 12 9l4 11h-30z" fill="currentColor" opacity=".18"/><circle cx="52" cy="68" r="11" fill="var(--paper)" stroke="currentColor" stroke-width="3"/><circle cx="170" cy="68" r="11" fill="var(--paper)" stroke="currentColor" stroke-width="3"/></svg>';
}

/* ---------- Banner ve görseller ---------- */

/** Ana sayfa banner görselleri: panelden yüklenenler, yoksa assets/img/banner-*.jpg */
function banners(): array
{
    $out = [];
    foreach (db()->query('SELECT * FROM banners WHERE active = 1 ORDER BY sort, id') as $b) {
        $out[] = ['src' => upload_url($b['image']), 'focus' => (int)$b['focus']];
    }
    if (!$out) {
        foreach (glob(ROOT . '/assets/img/banner-*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [] as $f) {
            $out[] = ['src' => url('assets/img/' . basename($f)), 'focus' => 50];
        }
    }
    return $out;
}

/** Ayardaki görsel: panelden yüklendiyse o, yoksa assets/img içindeki varsayılan dosya */
function setting_image(string $key, string $fallback = ''): string
{
    $v = setting($key);
    if ($v !== '') return upload_url($v);
    if ($fallback !== '' && is_file(ROOT . '/assets/img/' . $fallback)) return url('assets/img/' . $fallback);
    return '';
}

/** Her satırı ayrı madde olarak döner */
function setting_lines(string $key): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', setting($key))), 'strlen'));
}

/** "Başlık | açıklama" satırlarını döner */
function setting_pairs(string $key): array
{
    $items = [];
    foreach (setting_lines($key) as $line) {
        [$t, $d] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
        $items[] = ['title' => $t, 'text' => $d];
    }
    return $items;
}

/** Güven maddesi için başlığa göre ikon */
function trust_icon(string $title): string
{
    $t = mb_strtolower($title);
    $map = ['belge' => 'doc', 'ruhsat' => 'doc', 'sigorta' => 'shield', 'takip' => 'pinmap', 'bakım' => 'wrench',
            'yedek' => 'swap', 'muhatap' => 'user', 'sorumlu' => 'user', 'zaman' => 'clock', 'şoför' => 'user', 'sürücü' => 'user'];
    foreach ($map as $k => $ico) if (str_contains($t, $k)) return $ico;
    return 'check';
}
