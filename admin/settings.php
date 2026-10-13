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
    'Kurumsal güven bölümü' => [
        'trust_title' => ['Başlık', 'text'],
        'trust_text' => ['Açıklama', 'textarea'],
        'trust_items' => ['Güven maddeleri (her satır: Başlık | Açıklama). İkon başlığa göre otomatik seçilir', 'textarea-lg'],
        'stats' => ['Rakamlar (her satır: Rakam | Açıklama, örn. "25+ | Araçlık filo"). Boş bırakılırsa gösterilmez', 'textarea'],
        'brochure_label' => ['Tanıtım dosyası buton yazısı', 'text'],
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tg_action'])) {
    csrf_check();
    $token = trim((string)($_POST['telegram_token'] ?? ''));
    $chat = trim((string)($_POST['telegram_chat'] ?? ''));
    if ($token !== '' && !telegram_token_ok($token)) { flash('Bot token biçimi hatalı. BotFather\'ın verdiği "123456789:ABC..." biçimindeki metni olduğu gibi yapıştırın.', 'err'); redirect('settings.php#telegram'); }
    if ($chat !== '' && !preg_match('/^-?\d+$/', $chat)) { flash('Sohbet ID yalnızca rakamlardan oluşmalı.', 'err'); redirect('settings.php#telegram'); }
    save_setting('telegram_token', $token);
    save_setting('telegram_chat', $chat);
    $act = (string)$_POST['tg_action'];
    if ($act === 'find') {
        $found = telegram_find_chat($error);
        if ($found) { save_setting('telegram_chat', $found['id']); flash('Sohbet bulundu: ' . $found['name'] . ' (' . $found['id'] . '). Şimdi test mesajı gönderebilirsiniz.'); }
        else flash($error ?? 'Sohbet bulunamadı.', 'err');
    } elseif ($act === 'test') {
        $ok = telegram_send("✅ Yaren Tur web sitesi bildirimleri bu sohbete gelecek.", $error);
        $ok ? flash('Test mesajı gönderildi. Telegram\'ı kontrol edin.') : flash('Gönderilemedi: ' . $error, 'err');
    } else {
        flash('Telegram ayarları kaydedildi.');
    }
    redirect('settings.php#telegram');
}

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
        $pdf = upload_pdf('brochure');
        if ($pdf) { delete_upload(setting('brochure')); save_setting('brochure', $pdf); }
        elseif (!empty($_POST['remove_brochure'])) { delete_upload(setting('brochure')); save_setting('brochure', ''); }
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
  <section class="card stack">
    <h2>Kurumsal tanıtım dosyası (PDF)</h2>
    <p class="muted">Yüklenen PDF, sitedeki güven bölümünde ve teklif formunun yanında "indir" butonu olarak gösterilir. Dosya yoksa buton görünmez. En fazla 15 MB.</p>
    <?php if ($bu = brochure_url()): ?><p><a href="<?= e($bu) ?>" target="_blank" rel="noopener">Mevcut dosyayı aç</a></p><?php endif; ?>
    <input type="file" name="brochure" accept="application/pdf">
    <?php if (setting('brochure')): ?><label class="chk"><input type="checkbox" name="remove_brochure" value="1"> Dosyayı kaldır</label><?php endif; ?>
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

<form method="post" class="card stack" id="telegram">
  <?= csrf_field() ?>
  <h2>Telegram bildirimi</h2>
  <p class="muted">Siteden yeni teklif geldiğinde telefonunuza anında Telegram mesajı düşer.</p>
  <ol class="muted" style="margin:0;padding-left:1.2rem;display:grid;gap:6px">
    <li>Telegram'da <b>@BotFather</b>'ı açın, <b>/newbot</b> yazın, bota bir ad verin. Size verdiği <b>token</b>'ı aşağıya yapıştırıp <b>Kaydet</b>'e basın.</li>
    <li>Oluşturduğunuz botu Telegram'da açıp <b>Başlat</b>'a basın ya da herhangi bir mesaj yazın. Bildirimleri bir grupta almak isterseniz botu gruba ekleyip grupta <b>/start</b> yazın.</li>
    <li><b>Sohbet ID'sini bul</b> butonuna basın, ardından <b>Test mesajı gönder</b> ile deneyin.</li>
  </ol>
  <div class="row3">
    <label class="f"><span>Bot token</span><input name="telegram_token" value="<?= e(setting('telegram_token')) ?>" placeholder="123456789:ABC..." autocomplete="off"></label>
    <label class="f"><span>Sohbet ID</span><input name="telegram_chat" value="<?= e(setting('telegram_chat')) ?>" placeholder="Otomatik bulunur" autocomplete="off"></label>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <button class="b b--pri" name="tg_action" value="save">Kaydet</button>
    <button class="b" name="tg_action" value="find">Sohbet ID'sini bul</button>
    <button class="b" name="tg_action" value="test">Test mesajı gönder</button>
  </div>
</form>
<?php admin_footer();
