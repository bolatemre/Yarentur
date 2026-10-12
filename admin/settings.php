<?php
require __DIR__ . '/_layout.php';
require_login();

$groups = [
    'Firma ve iletişim' => [
        'site_title' => ['Firma adı', 'text'],
        'phone' => ['Telefon', 'text'],
        'phone2' => ['İkinci telefon', 'text'],
        'whatsapp' => ['WhatsApp numarası (ülke koduyla, örn. 905321234567)', 'text'],
        'email' => ['E-posta', 'email'],
        'notify_email' => ['Yeni teklif bildirimlerinin gideceği e-posta', 'email'],
        'address' => ['Adres', 'textarea'],
        'working_hours' => ['Çalışma saatleri', 'text'],
        'map_embed' => ['Google Haritalar yerleştirme adresi (https://www.google.com/maps/embed?... ile başlayan)', 'text'],
        'instagram' => ['Instagram adresi', 'url'],
        'facebook' => ['Facebook adresi', 'url'],
    ],
    'Ana sayfa metinleri' => [
        'hero_title' => ['Büyük başlık', 'text'],
        'hero_text' => ['Başlık altı metin', 'textarea'],
        'about_title' => ['Hakkımızda başlığı', 'text'],
        'about_text' => ['Hakkımızda metni (paragrafları boş satırla ayırın)', 'textarea-lg'],
        'highlights' => ['Neden biz maddeleri (her satır: Başlık | Açıklama)', 'textarea-lg'],
    ],
    'Online çözümler bölümü' => [
        'online_label' => ['Küçük başlık', 'text'],
        'online_title' => ['Büyük başlık', 'text'],
        'online_text' => ['Açıklama', 'textarea'],
        'online_company_title' => ['1. blok başlığı', 'text'],
        'online_company' => ['1. blok maddeleri (her satır bir madde)', 'textarea-lg'],
        'online_mobile_title' => ['2. blok başlığı', 'text'],
        'online_mobile' => ['2. blok maddeleri (her satır bir madde)', 'textarea-lg'],
        'online_benefits' => ['Alt kutular (her satır: Başlık | Açıklama, en fazla 3 satır önerilir)', 'textarea-lg'],
    ],
    'Arama motorları' => [
        'meta_description' => ['Site açıklaması (Google sonuçlarında görünür, 160 karakter civarı)', 'textarea'],
    ],
];

$imageFields = [
    'about_image' => ['Hakkımızda bölümü fotoğrafı', 'about.jpg'],
    'online_img_company' => ['Online çözümler: firma ekranı görüntüsü (yüklenmezse çizim gösterilir)', ''],
    'online_img_mobile' => ['Online çözümler: mobil ekran görüntüsü (yüklenmezse çizim gösterilir)', ''],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($groups as $fields) {
        foreach ($fields as $key => [$label, $type]) {
            $val = trim((string)($_POST[$key] ?? ''));
            if ($key === 'map_embed' && $val !== '') {
                // iframe kodu yapıştırıldıysa src adresini çıkar
                if (preg_match('/src="([^"]+)"/', $val, $m)) $val = html_entity_decode($m[1]);
                if (!preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed\?#', $val)) {
                    flash('Harita adresi Google Haritalar "Haritayı yerleştir" bağlantısı olmalı. Diğer ayarlar kaydedilmedi.', 'err');
                    redirect('settings.php');
                }
            }
            if (in_array($type, ['url'], true) && $val !== '' && !preg_match('#^https?://#', $val)) $val = 'https://' . $val;
            save_setting($key, mb_substr($val, 0, 5000));
        }
    }
    try {
        foreach ($imageFields as $key => $label) {
            $img = upload_image($key);
            if ($img) { delete_upload(setting($key)); save_setting($key, $img); }
            elseif (!empty($_POST['remove_' . $key])) { delete_upload(setting($key)); save_setting($key, ''); }
        }
        $logo = upload_image('logo');
        if ($logo) { delete_upload(setting('logo')); save_setting('logo', $logo); }
        elseif (!empty($_POST['remove_logo'])) { delete_upload(setting('logo')); save_setting('logo', ''); }
    } catch (RuntimeException $ex) {
        flash('Ayarlar kaydedildi ama görsel yüklenemedi: ' . $ex->getMessage(), 'err');
        redirect('settings.php');
    }
    flash('Ayarlar kaydedildi.');
    redirect('settings.php');
}

admin_header('Site ayarları', 'settings');
?>
<form method="post" enctype="multipart/form-data" class="stack">
  <?= csrf_field() ?>
  <section class="card stack">
    <h2>Logo</h2>
    <div class="logo-row">
      <div class="logo-prev"><?= brand_html('brandmark') ?></div>
      <div class="stack">
        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
        <?php if (setting('logo')): ?><label class="chk"><input type="checkbox" name="remove_logo" value="1"> Yüklenen logoyu kaldır, varsayılana dön</label><?php endif; ?>
        <small class="muted">Arka planı beyaz veya şeffaf, yatay bir logo en iyi sonucu verir.</small>
      </div>
    </div>
  </section>
  <section class="card stack">
    <h2>Görseller</h2>
    <?php foreach ($imageFields as $key => [$label, $fallback]): $cur = setting_image($key, $fallback); ?>
    <div class="f"><span><?= e($label) ?></span>
      <div class="logo-row">
        <?php if ($cur): ?><img class="thumb" src="<?= e($cur) ?>" alt=""><?php endif; ?>
        <div class="stack">
          <input type="file" name="<?= $key ?>" accept="image/jpeg,image/png,image/webp">
          <?php if (setting($key)): ?><label class="chk"><input type="checkbox" name="remove_<?= $key ?>" value="1"> Görseli kaldır</label><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </section>
  <?php foreach ($groups as $gname => $fields): ?>
  <section class="card stack">
    <h2><?= e($gname) ?></h2>
    <?php foreach ($fields as $key => [$label, $type]): ?>
      <label class="f"><span><?= e($label) ?></span>
        <?php if (str_starts_with($type, 'textarea')): ?>
          <textarea name="<?= $key ?>" rows="<?= $type === 'textarea-lg' ? 7 : 3 ?>"><?= e(settings()[$key] ?? '') ?></textarea>
        <?php else: ?>
          <input name="<?= $key ?>" type="<?= $type === 'url' ? 'text' : $type ?>" value="<?= e(settings()[$key] ?? '') ?>">
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  </section>
  <?php endforeach; ?>
  <div class="savebar"><button class="b b--pri">Ayarları kaydet</button></div>
</form>
<?php admin_footer();
