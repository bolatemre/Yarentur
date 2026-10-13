<?php
/** Arama motorları için site haritası (XML) */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/seo.php';

$pages = [
    ['', '1.0', 'weekly'],
    ['hizmetler.php', '0.9', 'monthly'],
    ['iletisim.php', '0.9', 'monthly'],
    ['hakkimizda.php', '0.7', 'monthly'],
    ['online-cozumler.php', '0.7', 'monthly'],
    ['filo.php', '0.7', 'monthly'],
];
foreach (active_services() as $s) $pages[] = ['hizmet.php?s=' . rawurlencode($s['slug']), '0.8', 'monthly'];

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as [$path, $prio, $freq]) {
    echo '  <url><loc>' . htmlspecialchars(abs_url(url($path)), ENT_XML1) . '</loc><changefreq>' . $freq . '</changefreq><priority>' . $prio . "</priority></url>\n";
}
echo "</urlset>\n";
