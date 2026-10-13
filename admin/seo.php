<?php
require __DIR__ . '/_layout.php';
require_login();
require_once dirname(__DIR__) . '/includes/seo.php';

$fields = [
    'Google görünümü' => [
        'seo_home_title' => ['Ana sayfa başlığı (Google\'da mavi başlık; sonuna firma adı otomatik eklenir, 60 karakteri geçmemesi önerilir)', 'text', 70],
        'seo_home_description' => ['Ana sayfa açıklaması (Google\'da başlığın altındaki metin, 150-160 karakter önerilir)', 'textarea', 300],
        'seo_region' => ['Hizmet bölgesi (örn. "İstanbul" ya da "İstanbul Avrupa Yakası"; hizmet sayfası başlıklarına ve Google bilgilerine eklenir)', 'text', 80],
        'site_url' => ['Site adresi (https://yarentur.com.tr gibi, sonunda / olmadan)', 'text', 120],
    ],
    'Doğrulama kodları' => [
        'google_verification' => ['Google Search Console doğrulama kodu (HTML etiketi yöntemindeki content="..." değeri)', 'text', 120],
        'bing_verification' => ['Bing Webmaster doğrulama kodu (isteğe bağlı)', 'text', 120],
    ],
    'Sık sorulan sorular' => [
        'faq' => ['Her satır: Soru | Cevap. Ana sayfada gösterilir ve Google\'a soru-cevap olarak bildirilir. Boş bırakılırsa bölüm gizlenir', 'textarea-lg', 6000],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($fields as $group) {
        foreach ($group as $key => [$label, $type, $max]) {
            $val = trim((string)($_POST[$key] ?? ''));
            if (in_array($key, ['google_verification', 'bing_verification'], true)) {
                // Etiketin tamamı yapıştırıldıysa sadece content değerini al
                if (preg_match('/content="([^"]+)"/', $val, $m)) $val = $m[1];
                $val = preg_replace('/[^A-Za-z0-9_\-]/', '', $val);
            }
            if ($key === 'site_url' && $val !== '') {
                if (!preg_match('#^https?://#', $val)) $val = 'https://' . $val;
                $val = rtrim($val, '/');
            }
            save_setting($key, mb_substr($val, 0, $max));
        }
    }
    flash('SEO ayarları kaydedildi.');
    redirect('seo.php');
}

$origin = site_origin();
admin_header('SEO', 'seo');
?>
<section class="card stack">
  <h2>Google'a bildirilecek adresler</h2>
  <p class="muted">Google Search Console'a site haritası olarak şu adresi ekleyin: <a href="<?= e(url('sitemap.php')) ?>" target="_blank" rel="noopener"><b><?= e($origin) ?>/sitemap.php</b></a></p>
  <p class="muted">Sayfalardaki başlık, açıklama ve işletme bilgileri buradan ve <a href="settings.php">Site ayarları</a>ndaki telefon, adres, e-posta ve sosyal medya alanlarından otomatik oluşturulur. Adresin doğru girilmesi yerel aramalar için önemlidir.</p>
</section>
<form method="post" class="stack">
  <?= csrf_field() ?>
  <?php foreach ($fields as $gname => $group): ?>
  <section class="card stack">
    <h2><?= e($gname) ?></h2>
    <?php foreach ($group as $key => [$label, $type, $max]): ?>
      <label class="f"><span><?= e($label) ?></span>
        <?php if (str_starts_with($type, 'textarea')): ?>
          <textarea name="<?= $key ?>" rows="<?= $type === 'textarea-lg' ? 10 : 3 ?>" maxlength="<?= $max ?>"><?= e(settings()[$key] ?? '') ?></textarea>
        <?php else: ?>
          <input name="<?= $key ?>" value="<?= e(settings()[$key] ?? '') ?>" maxlength="<?= $max ?>">
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  </section>
  <?php endforeach; ?>
  <div class="savebar"><button class="b b--pri">Kaydet</button></div>
</form>
<?php admin_footer();
