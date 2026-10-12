<?php
require __DIR__ . '/includes/bootstrap.php';

$services = active_services();
$featured = array_slice($services, 0, 2);
$others   = array_slice($services, 2);
$vehicles = db()->query('SELECT * FROM vehicles WHERE active = 1 ORDER BY sort, id')->fetchAll();
$clients  = db()->query('SELECT * FROM clients WHERE active = 1 ORDER BY sort, id')->fetchAll();
$flash    = flash();
$old      = $_SESSION['old_form'] ?? [];
unset($_SESSION['old_form']);
$pre      = $_GET['hizmet'] ?? ($old['service'] ?? '');

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="wrap hero__in">
    <div class="hero__copy">
      <h1 class="hero__title"><?= e(setting('hero_title')) ?></h1>
      <p class="hero__text"><?= e(setting('hero_text')) ?></p>
      <div class="hero__actions">
        <a class="btn btn--solid btn--lg" href="#teklif">Teklif alın</a>
        <a class="btn btn--line btn--lg" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a>
      </div>
    </div>
    <nav class="pick" aria-labelledby="pick-h">
      <h2 class="pick__h" id="pick-h">Ne için servis arıyorsunuz?</h2>
      <ul>
        <?php foreach ($services as $s): ?>
        <li><a href="<?= e(url('index.php?hizmet=' . $s['slug'])) ?>#teklif"><?= icon($s['icon']) ?><span><?= e($s['title']) ?></span><?= icon('arrow', 'ico pick__arrow') ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
  <div class="route" aria-hidden="true">
    <div class="wrap route__in">
      <div class="route__line"><span class="route__bus"><?= icon('bus') ?></span></div>
      <ol class="route__stops">
        <li style="--at:0%">Kapınız</li><li style="--at:33.333%">Okul</li><li style="--at:66.666%">Fabrika</li><li style="--at:100%">Havalimanı</li>
      </ol>
    </div>
  </div>
</section>

<section class="section" id="hizmetler">
  <div class="wrap">
    <div class="sec-head">
      <h2 class="h2">Hizmetlerimiz</h2>
      <p class="lead">Düzenli servisler ve tek seferlik transferler için araç, şoför ve güzergahı biz planlıyoruz.</p>
    </div>
    <div class="feat">
      <?php foreach ($featured as $i => $s): ?>
      <a class="feat__item <?= $i ? 'feat__item--ink' : '' ?>" href="<?= e(url('hizmet.php?s=' . $s['slug'])) ?>">
        <?= icon($s['icon'], 'ico feat__ico') ?>
        <h3 class="feat__title"><?= e($s['title']) ?></h3>
        <p><?= e($s['summary']) ?></p>
        <span class="feat__more">Ayrıntılar <?= icon('arrow') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($others): ?>
    <ul class="svc">
      <?php foreach ($others as $s): ?>
      <li>
        <a class="svc__item" href="<?= e(url('hizmet.php?s=' . $s['slug'])) ?>">
          <span class="svc__ico"><?= icon($s['icon']) ?></span>
          <span class="svc__body">
            <strong><?= e($s['title']) ?></strong>
            <span><?= e($s['summary']) ?></span>
          </span>
          <?= icon('arrow', 'ico svc__arrow') ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>

<?php if ($vehicles): ?>
<section class="section section--mist" id="filo">
  <div class="wrap">
    <div class="sec-head">
      <h2 class="h2">Araç filomuz</h2>
      <p class="lead">Yolcu sayınıza göre doğru aracı seçiyoruz. Kapasitelerde ilk sayı yolcu, +1 şoför koltuğudur.</p>
    </div>
    <div class="fleet">
      <?php foreach ($vehicles as $v): ?>
      <article class="veh">
        <div class="veh__media">
          <?php if ($v['image']): ?>
            <img src="<?= e(upload_url($v['image'])) ?>" alt="<?= e($v['name']) ?>" loading="lazy">
          <?php else: ?>
            <?= vehicle_silhouette() ?>
          <?php endif; ?>
        </div>
        <div class="veh__cap"><?= e($v['capacity']) ?><small>kişilik</small></div>
        <h3 class="veh__name"><?= e($v['name']) ?></h3>
        <p class="veh__feat"><?= e($v['features']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" id="hakkimizda">
  <div class="wrap about">
    <div class="about__text">
      <h2 class="h2"><?= e(setting('about_title')) ?></h2>
      <?= paragraphs(setting('about_text')) ?>
    </div>
    <?php $hl = highlights(); if ($hl): ?>
    <ul class="hl">
      <?php foreach ($hl as $h): ?>
      <li><?= icon('check', 'ico hl__ico') ?><div><strong><?= e($h['title']) ?></strong><span><?= e($h['text']) ?></span></div></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>

<?php if ($clients): ?>
<section class="clients" aria-label="Referanslarımız">
  <div class="wrap">
    <h2 class="clients__h">Hizmet verdiğimiz kurumlar</h2>
    <ul class="clients__list">
      <?php foreach ($clients as $c): ?>
      <li><?php if ($c['logo']): ?><img src="<?= e(upload_url($c['logo'])) ?>" alt="<?= e($c['name']) ?>" loading="lazy"><?php else: ?><span><?= e($c['name']) ?></span><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

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

<section class="section" id="iletisim">
  <div class="wrap contact">
    <div>
      <h2 class="h2">İletişim</h2>
      <ul class="contact__list">
        <li><?= icon('phone') ?><div><strong>Telefon</strong><a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a><?php if (setting('phone2')): ?><br><a href="<?= e(tel_href(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a><?php endif; ?></div></li>
        <li><?= icon('mail') ?><div><strong>E-posta</strong><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li>
        <li><?= icon('pin') ?><div><strong>Adres</strong><span><?= nl2br(e(setting('address'))) ?></span></div></li>
        <li><?= icon('clock') ?><div><strong>Çalışma saatleri</strong><span><?= e(setting('working_hours')) ?></span></div></li>
      </ul>
    </div>
    <div class="contact__map">
      <?php $map = setting('map_embed'); if ($map && preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed\?#', $map)): ?>
        <iframe src="<?= e($map) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Konum haritası"></iframe>
      <?php else: ?>
        <div class="contact__nomap"><?= icon('pin') ?><span><?= e(setting('address')) ?></span></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php';
