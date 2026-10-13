<?php
declare(strict_types=1);
/** Arama motoru etiketleri ve yapısal veri (schema.org) */

/** SEO ayarlarının varsayılanları (yalnızca eksikse eklenir, mevcut değerlere dokunmaz) */
function seo_ensure_defaults(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $defaults = [
        'site_url'         => 'https://yarentur.com.tr',
        'seo_home_title'   => 'Personel Taşımacılığı ve Servis Hizmetleri',
        'seo_home_description' => 'Yaren Tur ile personel taşımacılığı: vardiya düzeninize göre planlanan güzergahlar, canlı araç takibi ve bakımlı filo. Firmanıza özel servis teklifi alın.',
        'seo_region'       => '',
        'google_verification' => '',
        'bing_verification' => '',
        'faq'              => "Personel servisi için nasıl teklif alabilirim? | İletişim sayfasındaki teklif formunu doldurmanız yeterli. Çalışan sayınızı, vardiya düzeninizi ve iş yeri adresinizi paylaştığınızda güzergah ve araç planıyla birlikte size dönüş yapıyoruz. Dilerseniz telefonla da ulaşabilirsiniz.\nHangi araçlarla hizmet veriyorsunuz? | Yolcu sayınıza göre minibüs, midibüs ve otobüs seçeneklerimiz var. Araçlarımızı ve kapasitelerini Araç filomuz sayfasında görebilirsiniz.\nVardiyalı çalışan firmalara hizmet veriyor musunuz? | Evet. Tek, iki ve üç vardiyalı çalışma düzenlerine göre güzergah ve saat planlaması yapıyoruz.\nServislerimizi online takip edebilir miyiz? | Evet. Online takip sistemiyle servislerin konumunu ve seferlerini anlık görebilir, raporları Excel veya PDF olarak alabilirsiniz.",
    ];
    $st = db()->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES(?, ?)');
    foreach ($defaults as $k => $v) $st->execute([$k, $v]);
}
seo_ensure_defaults();

/** Sitenin tam adresi: panelde girilen site adresi, yoksa istekten tahmin */
function site_origin(): string
{
    $u = rtrim(setting('site_url'), '/');
    if ($u !== '') return $u;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || str_contains($_SERVER['HTTP_CF_VISITOR'] ?? '', 'https');
    return ($https ? 'https' : 'http') . '://' . preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'yarentur.com.tr');
}

/** Göreli adresi (url() çıktısı) tam adrese çevirir */
function abs_url(string $rel): string
{
    return preg_match('#^https?://#', $rel) ? $rel : site_origin() . '/' . ltrim($rel, '/');
}

/** Sayfanın tek ve kalıcı adresi (canonical) */
function canonical_url(): string
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    if ($script === 'index.php') return abs_url(url(''));
    if ($script === 'hizmet.php' && !empty($_GET['s'])) return abs_url(url('hizmet.php?s=' . rawurlencode((string)$_GET['s'])));
    return abs_url(url($script));
}

/** Gerçek adres girilmiş mi (varsayılan yer tutucu metin değilse) */
function has_real_address(): bool
{
    $a = setting('address');
    return $a !== '' && !str_starts_with($a, 'Adres bilgisini');
}

function json_ld(array $data): string
{
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . "</script>\n";
}

/** İşletme bilgisi (her sayfada) */
function business_schema(): array
{
    $origin = site_origin();
    $d = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        '@id' => $origin . '/#isletme',
        'name' => setting('site_title', 'Yaren Tur'),
        'url' => $origin . '/',
        'telephone' => setting('phone'),
        'email' => setting('email'),
        'description' => setting('seo_home_description', setting('meta_description')),
    ];
    if ($logo = logo_url()) $d['logo'] = abs_url($logo);
    $slides = banners();
    if ($slides) $d['image'] = abs_url($slides[0]['src']);
    if (has_real_address()) {
        $d['address'] = ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressCountry' => 'TR'];
        if (setting('seo_region')) $d['address']['addressLocality'] = setting('seo_region');
    }
    if (setting('seo_region')) $d['areaServed'] = setting('seo_region');
    $same = array_values(array_filter([setting('instagram'), setting('facebook')]));
    if ($same) $d['sameAs'] = $same;
    return array_filter($d, fn($v) => $v !== '' && $v !== []);
}

/** <head> içindeki SEO etiketleri */
function seo_head(string $title, string $desc): string
{
    $canon = canonical_url();
    $slides = banners();
    $img = $slides ? abs_url($slides[0]['src']) : (logo_url() ? abs_url(logo_url()) : '');
    $is404 = http_response_code() === 404;
    $h = $is404 ? '' : '<link rel="canonical" href="' . e($canon) . "\">\n";
    $h .= '<meta property="og:site_name" content="' . e(setting('site_title', 'Yaren Tur')) . "\">\n";
    $h .= '<meta property="og:locale" content="tr_TR">' . "\n";
    $h .= '<meta property="og:type" content="website">' . "\n";
    $h .= '<meta property="og:url" content="' . e($canon) . "\">\n";
    $h .= '<meta property="og:title" content="' . e($title) . "\">\n";
    $h .= '<meta property="og:description" content="' . e($desc) . "\">\n";
    if ($img) {
        $h .= '<meta property="og:image" content="' . e($img) . "\">\n";
        $h .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    }
    if ($is404) $h .= '<meta name="robots" content="noindex">' . "\n";
    if ($v = setting('google_verification')) $h .= '<meta name="google-site-verification" content="' . e($v) . "\">\n";
    if ($v = setting('bing_verification')) $h .= '<meta name="msvalidate.01" content="' . e($v) . "\">\n";

    $h .= json_ld(business_schema());

    // Konum zinciri (breadcrumb)
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    if ($script !== 'index.php' && http_response_code() !== 404) {
        $items = [['Ana sayfa', abs_url(url(''))]];
        if ($script === 'hizmet.php') $items[] = ['Hizmetler', abs_url(url('hizmetler.php'))];
        $items[] = [$GLOBALS['page_title'] ?? $title, $canon];
        $list = [];
        foreach ($items as $i => [$n, $u]) $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $n, 'item' => $u];
        $h .= json_ld(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list]);
    }

    // Hizmet sayfası
    if ($script === 'hizmet.php' && !empty($GLOBALS['svc'])) {
        $s = $GLOBALS['svc'];
        $svc = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $s['title'],
            'serviceType' => $s['title'],
            'description' => $s['summary'],
            'url' => $canon,
            'provider' => ['@id' => site_origin() . '/#isletme'],
        ];
        if (setting('seo_region')) $svc['areaServed'] = setting('seo_region');
        $h .= json_ld($svc);
    }
    return $h;
}

/** Sık sorulan sorular: "Soru | Cevap" satırları */
function faq_items(): array
{
    return array_values(array_filter(setting_pairs('faq'), fn($f) => $f['title'] !== '' && $f['text'] !== ''));
}
