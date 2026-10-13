<?php
// Basit robot kontrolü: her gösterimde yeni bir toplama sorusu
$cap_a = random_int(1, 9);
$cap_b = random_int(1, 9);
$_SESSION['captcha'] = $cap_a + $cap_b;
$shiftOld = $old['shift'] ?? '';
$preSel = $pre !== '' ? $pre : 'personel-tasimaciligi';
$brochure = brochure_url();
?>
<section class="quote" id="teklif">
  <div class="wrap quote__in">
    <div class="quote__intro">
      <h2 class="h2">Kurumsal teklif alın</h2>
      <p>Çalışan sayınızı, vardiya düzeninizi ve iş yeri adresinizi paylaşın; güzergah ve araç planıyla birlikte size özel teklif hazırlayalım. Formu doldurmak 1 dakika sürer.</p>
      <ol class="quote__steps-info">
        <li><b>1</b> Firma ve iletişim bilgileri</li>
        <li><b>2</b> Servis ihtiyacınız</li>
        <li><b>3</b> Not ve gönderim</li>
      </ol>
      <a class="quote__phone" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a>
      <?php if ($brochure): ?>
      <p class="quote__doc"><a href="<?= e($brochure) ?>" target="_blank" rel="noopener"><?= icon('file') ?><?= e(setting('brochure_label', 'Kurumsal tanıtım dosyası')) ?> (PDF)</a></p>
      <?php endif; ?>
    </div>
    <form class="form wiz" method="post" action="<?= e(url('teklif.php')) ?>" data-wizard data-start="<?= (int)($old['_step'] ?? 1) ?>">
      <?php if ($flash): ?>
        <div class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div>
      <?php endif; ?>
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <div class="wiz__bar" aria-hidden="true"><span data-wiz-progress></span></div>
      <p class="wiz__count" data-wiz-count aria-live="polite"></p>

      <fieldset class="wiz__step" data-step="1">
        <legend class="wiz__title"><?= icon('building') ?>Firma ve iletişim</legend>
        <label class="fld"><span>Firma / kurum adı</span><input name="company" maxlength="160" autocomplete="organization" placeholder="Örn. ABC Tekstil A.Ş." value="<?= e($old['company'] ?? '') ?>"></label>
        <div class="form__row">
          <label class="fld"><span>Yetkili ad soyad *</span><input name="name" required maxlength="120" autocomplete="name" value="<?= e($old['name'] ?? '') ?>"></label>
          <label class="fld"><span>Telefon *</span><input name="phone" type="tel" required maxlength="30" autocomplete="tel" placeholder="05xx xxx xx xx" value="<?= e($old['phone'] ?? '') ?>"></label>
        </div>
        <label class="fld"><span>E-posta</span><input name="email" type="email" maxlength="120" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"></label>
      </fieldset>

      <fieldset class="wiz__step" data-step="2">
        <legend class="wiz__title"><?= icon('bus') ?>Servis ihtiyacınız</legend>
        <div class="form__row">
          <label class="fld"><span>Hizmet</span>
            <select name="service">
              <?php foreach ($services as $s): ?>
              <option value="<?= e($s['title']) ?>" <?= ($preSel === $s['slug'] || $preSel === $s['title']) ? 'selected' : '' ?>><?= e($s['title']) ?></option>
              <?php endforeach; ?>
              <option value="Diğer" <?= $preSel === 'Diğer' ? 'selected' : '' ?>>Diğer</option>
            </select>
          </label>
          <label class="fld"><span>Yolcu / çalışan sayısı</span><input name="passengers" inputmode="numeric" maxlength="20" placeholder="Örn. 120" value="<?= e($old['passengers'] ?? '') ?>"></label>
        </div>
        <div class="fld">
          <span>Vardiya düzeni</span>
          <div class="chips">
            <?php foreach (shift_options() as $opt): ?>
            <label class="chip"><input type="radio" name="shift" value="<?= e($opt) ?>" <?= $shiftOld === $opt ? 'checked' : '' ?>><span><?= e($opt) ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <label class="fld"><span>İş yeri adresi / varış noktası</span><input name="route_to" maxlength="120" placeholder="Örn. Hadımköy OSB, Arnavutköy" value="<?= e($old['route_to'] ?? '') ?>"></label>
        <div class="form__row">
          <label class="fld"><span>Çalışanların yoğun olduğu ilçeler</span><input name="route_from" maxlength="120" placeholder="Örn. Esenyurt, Avcılar, Beylikdüzü" value="<?= e($old['route_from'] ?? '') ?>"></label>
          <label class="fld"><span>Başlangıç tarihi</span><input name="travel_date" type="date" value="<?= e($old['travel_date'] ?? '') ?>"></label>
        </div>
      </fieldset>

      <fieldset class="wiz__step" data-step="3">
        <legend class="wiz__title"><?= icon('check') ?>Not ve gönderim</legend>
        <label class="fld"><span>Eklemek istedikleriniz</span><textarea name="message" rows="4" maxlength="2000" placeholder="Vardiya saatleri, mevcut servis firmanız, özel istekler…"><?= e($old['message'] ?? '') ?></textarea></label>
        <label class="fld fld--cap"><span>Güvenlik sorusu: <?= $cap_a ?> + <?= $cap_b ?> kaç eder? *</span><input name="captcha" inputmode="numeric" pattern="[0-9]*" required maxlength="3" autocomplete="off"></label>
        <label class="consent"><input type="checkbox" name="consent" value="1" required> İletişim bilgilerimin teklif hazırlanması için kullanılmasını kabul ediyorum.</label>
      </fieldset>

      <div class="wiz__nav">
        <button class="btn btn--line wiz__back" type="button" data-wiz-back hidden>Geri</button>
        <button class="btn btn--solid btn--lg wiz__next" type="button" data-wiz-next hidden>Devam <?= icon('arrow') ?></button>
        <button class="btn btn--solid btn--lg form__send" type="submit" data-wiz-send>Teklif isteğini gönder</button>
      </div>
    </form>
  </div>
</section>
