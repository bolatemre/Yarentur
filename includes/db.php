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
    ensure_settings($pdo);
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
    CREATE TABLE IF NOT EXISTS banners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        image TEXT NOT NULL,
        focus INTEGER NOT NULL DEFAULT 50,
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    );
    CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT NOT NULL,
        at INTEGER NOT NULL
    );
    ");

    // Sonradan eklenen sütunlar (mevcut veritabanlarını bozmadan)
    add_column($pdo, 'requests', 'company', "TEXT NOT NULL DEFAULT ''");
    add_column($pdo, 'requests', 'shift', "TEXT NOT NULL DEFAULT ''");
    add_column($pdo, 'clients', 'sector', "TEXT NOT NULL DEFAULT ''");
    add_column($pdo, 'clients', 'detail', "TEXT NOT NULL DEFAULT ''");
    add_column($pdo, 'clients', 'since', "TEXT NOT NULL DEFAULT ''");
}

function add_column(PDO $pdo, string $table, string $col, string $def): void
{
    foreach ($pdo->query('PRAGMA table_info(' . $table . ')') as $c) {
        if ($c['name'] === $col) return;
    }
    $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $col . ' ' . $def);
}

function seed(PDO $pdo): void
{
    // Ayar varsayılanları ensure_settings() ile ekleniyor

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

function ensure_settings(PDO $pdo): void
{
    $settings = [
        'site_title'       => 'Yaren Tur',
        'meta_description' => 'Yaren Tur: öğrenci taşımacılığı, personel taşımacılığı, tur organizasyonu, havalimanı ve VIP transfer hizmetleri.',
        'phone'            => '0212 689 96 59',
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
        'about_image'      => '',
        'online_label'     => 'Online ulaşım çözümleri',
        'online_title'     => 'Taşımacılığı teknolojiyle birleştirdik',
        'online_text'      => 'Servislerinizi veriyle yönetiyoruz: canlı takip, dijital raporlama ve anında bildirim.',
        'online_company_title' => 'Firma ekranında',
        'online_company'   => "Tüm servisleriniz tek listede; her seferin detayına anında ulaşın.\nGüzergâh ve yolcuları canlı harita üzerinde tek bakışta görün.\nSürücü ve araç evraklarını dijital arşivden denetleyin, süresi yaklaşan belgeler için uyarı alın.\nHer seferin süresini, mesafesini ve maliyetini ölçün.\nBiniş-iniş saatlerini raporlayın, raporları Excel veya PDF olarak alın.",
        'online_mobile_title' => 'Yolcu ve veli mobilde',
        'online_mobile'    => "Servisinizin durumunu ve detaylarını anlık görün.\nPlanlanan alınış ve bırakılış saatlerini önceden öğrenin.\nAracın plaka ve belge bilgilerine ulaşın.\nServis sorumlusuna tek dokunuşla ulaşın.\nServisin yaklaşmasını canlı haritadan izleyin, kapıda beklemeyin.",
        'online_benefits'  => "Operasyonel verimlilik | Tüm filo ve güzergâhlar tek ekranda; planlama ve raporlama dakikalar içinde.\nGüvenli ve şeffaf ulaşım | Araç ve sürücü bilgileri güncel; gerçek zamanlı harita takibiyle her servis kontrol altında.\nYolcu memnuniyeti | Yolcular servisini canlı görür, bildirimlerle yolculuğun her adımından haberdar olur.",
        'online_img_company' => '',
        'online_img_mobile'  => '',
        'trust_title'      => 'Kurumların bize güvenme sebepleri',
        'trust_text'       => 'Personel servisinde aksayan tek bir sefer bütün vardiyayı etkiler. Bu yüzden belgeden bakıma, takipten yedek araca kadar her ayrıntıyı önceden planlıyoruz.',
        'trust_items'      => "Yetki belgeli taşımacılık | Personel taşımacılığı için gerekli yetki belgeleri ve ruhsatlarla çalışırız.\nSigortalı yolculuk | Yolcularımız zorunlu koltuk sigortası kapsamında seyahat eder.\nCanlı araç takibi | Servislerinizin konumunu ve seferlerini online takip sistemiyle anlık görürsünüz.\nBakımlı ve denetimli filo | Araçlarımız periyodik bakım ve muayeneden geçer, evrakları dijital olarak takip edilir.\nYedek araç desteği | Arıza durumunda seferin aksamaması için yedek araç planlaması yapılır.\nTek muhatap | Güzergah, vardiya ve faturalama için size tek bir sorumlu kişi atanır.",
        'stats'            => '',
        'brochure'         => '',
        'brochure_label'   => 'Kurumsal tanıtım dosyası',
        'telegram_token'   => '',
        'telegram_chat'    => '',
    ];
    $st = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES(?, ?)');
    foreach ($settings as $k => $v) $st->execute([$k, $v]);
}
