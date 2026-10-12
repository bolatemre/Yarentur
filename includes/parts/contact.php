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
