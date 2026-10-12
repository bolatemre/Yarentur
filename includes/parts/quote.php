<section class="quote" id="teklif">
  <div class="wrap quote__in">
    <div class="quote__intro">
      <h2 class="h2">Teklif alın</h2>
      <p>Güzergahınızı ve yolcu sayınızı yazın, size en kısa sürede dönüş yapalım. Acil durumlarda doğrudan arayabilirsiniz.</p>
      <a class="quote__phone" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a>
    </div>
    <form class="form" method="post" action="<?= e(url('teklif.php')) ?>">
      <?php if ($flash): ?>
        <div class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div>
      <?php endif; ?>
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="form__row">
        <label class="fld"><span>Ad soyad *</span><input name="name" required maxlength="120" autocomplete="name" value="<?= e($old['name'] ?? '') ?>"></label>
        <label class="fld"><span>Telefon *</span><input name="phone" type="tel" required maxlength="30" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"></label>
      </div>
      <div class="form__row">
        <label class="fld"><span>E-posta</span><input name="email" type="email" maxlength="120" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"></label>
        <label class="fld"><span>Hizmet</span>
          <select name="service">
            <option value="">Seçin</option>
            <?php foreach ($services as $s): ?>
            <option value="<?= e($s['title']) ?>" <?= ($pre === $s['slug'] || $pre === $s['title']) ? 'selected' : '' ?>><?= e($s['title']) ?></option>
            <?php endforeach; ?>
            <option value="Diğer" <?= $pre === 'Diğer' ? 'selected' : '' ?>>Diğer</option>
          </select>
        </label>
      </div>
      <div class="form__row">
        <label class="fld"><span>Nereden</span><input name="route_from" maxlength="120" value="<?= e($old['route_from'] ?? '') ?>"></label>
        <label class="fld"><span>Nereye</span><input name="route_to" maxlength="120" value="<?= e($old['route_to'] ?? '') ?>"></label>
      </div>
      <div class="form__row">
        <label class="fld"><span>Kişi sayısı</span><input name="passengers" inputmode="numeric" maxlength="20" value="<?= e($old['passengers'] ?? '') ?>"></label>
        <label class="fld"><span>Tarih</span><input name="travel_date" type="date" value="<?= e($old['travel_date'] ?? '') ?>"></label>
      </div>
      <label class="fld"><span>Mesajınız</span><textarea name="message" rows="4" maxlength="2000"><?= e($old['message'] ?? '') ?></textarea></label>
      <label class="consent"><input type="checkbox" name="consent" value="1" required> İletişim bilgilerimin teklif hazırlanması için kullanılmasını kabul ediyorum.</label>
      <button class="btn btn--solid btn--lg form__send" type="submit">Teklif isteğini gönder</button>
    </form>
  </div>
</section>
