<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    $file = DATA_DIR . '/yarentur.sqlite';
    $fresh = !is_file($file);

    $pdo = new PDO('sqlite:' . $file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    migrate($pdo);
    if ($fresh) seed($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL DEFAULT ''
    );
    CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        summary TEXT NOT NULL DEFAULT '',
        body TEXT NOT NULL DEFAULT '',
        icon TEXT NOT NULL DEFAULT 'bus',
        image TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    );
    CREATE TABLE IF NOT EXISTS vehicles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        capacity TEXT NOT NULL DEFAULT '',
        features TEXT NOT NULL DEFAULT '',
        image TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    );
    CREATE TABLE IF NOT EXISTS clients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        logo TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    );
    CREATE TABLE IF NOT EXISTS requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL DEFAULT '',
        service TEXT NOT NULL DEFAULT '',
        route_from TEXT NOT NULL DEFAULT '',
        route_to TEXT NOT NULL DEFAULT '',
        passengers TEXT NOT NULL DEFAULT '',
        travel_date TEXT NOT NULL DEFAULT '',
        message TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'yeni',
        note TEXT NOT NULL DEFAULT '',
        ip TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
    );
    CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT NOT NULL,
        at INTEGER NOT NULL
    );
    ");
}

function seed(PDO $pdo): void
{
    $settings = [
        'site_title'       => 'Yaren Tur',
        'meta_description' => 'Yaren Tur: öğrenci taşımacılığı, personel taşımacılığı, tur organizasyonu, havalimanı ve VIP transfer hizmetleri.',
        'phone'            => '0 (5xx) xxx xx xx',
        'phone2'           => '',
        'whatsapp'         => '',
        'email'            => 'info@yarentur.com.tr',
        'notify_email'     => '',
        'address'          => 'Adres bilgisini yönetim panelinden girin',
        'working_hours'    => 'Pazartesi – Cumartesi, 08:00 – 19:00',
        'hero_title'       => 'Her sabah aynı saatte, kapınızın önünde.',
        'hero_text'        => 'Okul servisinden fabrika vardiyasına, hafta sonu gezisinden havalimanı transferine kadar yolcularınızı güvenle taşıyoruz.',
        'about_title'      => 'Yaren Tur hakkında',
        'about_text'       => "Yaren Tur, öğrenci ve personel taşımacılığı başta olmak üzere kurumlara ve ailelere düzenli ulaşım hizmeti veren bir turizm firmasıdır.\n\nAraçlarımız düzenli bakımdan geçer, şoförlerimiz güzergahlarını bilir ve her sefer planlanan saatte başlar.",
        'highlights'       => "Rehber personel | Okul servislerinde çocuklara araç içinde rehber personel eşlik eder.\nZamanında kalkış | Güzergah ve saatler önceden planlanır, değişiklikler size bildirilir.\nBakımlı araçlar | Araçlarımız periyodik bakım ve muayeneden geçer.\nSigortalı yolculuk | Yolcularımız koltuk sigortası kapsamında seyahat eder.",
        'map_embed'        => '',
        'instagram'        => '',
        'facebook'         => '',
        'logo'             => '',
    ];
    $st = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES(?, ?)');
    foreach ($settings as $k => $v) $st->execute([$k, $v]);

    $services = [
        ['ogrenci-tasimaciligi', 'Öğrenci Taşımacılığı', 'Okul servislerinde rehber personel, sabit güzergah ve velilerle düzenli iletişim.', "Öğrencilerinizi evden okula, okuldan eve güvenle taşıyoruz.\n\nHer araçta rehber personel bulunur. Güzergah ve alım saatleri dönem başında velilerle paylaşılır, değişiklikler önceden bildirilir.\n\nOkul yönetimleriyle sözleşmeli çalışır, servis yönetmeliğine uygun araç ve belgelerle hizmet veririz.", 'school', 1],
        ['personel-tasimaciligi', 'Personel Taşımacılığı', 'Vardiya saatlerinize göre planlanmış güzergahlarla çalışanlarınızı işe ve eve taşıyoruz.', "Fabrika, ofis ve şantiyeler için vardiya düzenine uygun personel servisi planlıyoruz.\n\nGüzergahlar çalışanların adreslerine göre çıkarılır, kapasite ihtiyaca göre minibüs, midibüs veya otobüsle karşılanır.\n\nAylık sözleşmeli çalışma ve tek seferlik ek seferler için teklif alabilirsiniz.", 'briefcase', 2],
        ['tur-ve-gezi', 'Tur ve Gezi Organizasyonu', 'Okul gezileri, kurum gezileri ve günübirlik turlar için araç ve rota planlaması.', "Okul gezileri, dernek ve kurum gezileri, günübirlik ve çok günlü turlar için araç tahsis ediyoruz.\n\nRotayı, mola noktalarını ve saatleri sizinle birlikte planlarız.", 'map', 3],
        ['havalimani-transfer', 'Havalimanı Transferi', 'Uçuş saatinize göre karşılama ve uğurlama; tek kişiden kalabalık gruplara kadar.', "Uçuş saatinize göre sizi kapıdan alır, havalimanına zamanında ulaştırırız. Dönüşte karşılama hizmeti de veriyoruz.", 'plane', 4],
        ['vip-transfer', 'VIP Transfer', 'Misafirleriniz ve yöneticileriniz için konforlu, şoförlü VIP araçlar.', "Kurumsal misafirleriniz, toplantı ve organizasyonlarınız için şoförlü VIP araç hizmeti veriyoruz.", 'star', 5],
        ['soforlu-arac-kiralama', 'Şoförlü Araç Kiralama', 'Düğün, nişan, toplantı ve özel günler için saatlik veya günlük araç kiralama.', "Özel günleriniz ve organizasyonlarınız için saatlik ya da günlük şoförlü araç kiralayabilirsiniz.", 'key', 6],
    ];
    $st = $pdo->prepare('INSERT INTO services(slug, title, summary, body, icon, sort) VALUES(?,?,?,?,?,?)');
    foreach ($services as $s) $st->execute($s);

    $vehicles = [
        ['Otobüs', '46+1', 'Klima, uzun yol konforu, geniş bagaj', 1],
        ['Midibüs', '27+1', 'Klima, personel ve okul servisleri için', 2],
        ['Minibüs', '16+1', 'Klima, dar sokaklarda rahat manevra', 3],
        ['VIP Minivan', '8+1', 'Deri koltuk, klima, VIP ve havalimanı transferi', 4],
    ];
    $st = $pdo->prepare('INSERT INTO vehicles(name, capacity, features, sort) VALUES(?,?,?,?)');
    foreach ($vehicles as $v) $st->execute($v);
}
