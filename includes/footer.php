<?php
$siteTitle = setting('site_title', 'Yaren Tur');
$wa = whatsapp_href('Merhaba, servis hizmeti için bilgi almak istiyorum.');
$home = url('');
?>
</main>
<footer class="foot">
  <div class="wrap foot__grid">
    <div>
      <div class="foot__logo"><?= brand_html('brandmark') ?></div>
      <p class="foot__about"><?= e(setting('meta_description')) ?></p>
    </div>
    <div>
      <h2 class="foot__h">Hizmetler</h2>
      <ul class="foot__list">
        <?php foreach (active_services() as $s): ?>
          <li><a href="<?= e(url('hizmet.php?s=' . $s['slug'])) ?>"><?= e($s['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h2 class="foot__h">İletişim</h2>
      <ul class="foot__list foot__contact">
        <li><?= icon('phone') ?><a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
        <?php if (setting('phone2')): ?><li><?= icon('phone') ?><a href="<?= e(tel_href(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a></li><?php endif; ?>
        <li><?= icon('mail') ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
        <li><?= icon('pin') ?><span><?= e(setting('address')) ?></span></li>
      </ul>
      <?php if (setting('instagram') || setting('facebook')): ?>
      <p class="foot__social">
        <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" rel="noopener" target="_blank">Instagram</a><?php endif; ?>
        <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" rel="noopener" target="_blank">Facebook</a><?php endif; ?>
      </p>
      <?php endif; ?>
    </div>
  </div>
  <div class="wrap foot__bottom">
    <span>© <?= date('Y') ?> <?= e($siteTitle) ?>. Tüm hakları saklıdır.</span>
    <a href="<?= e($home) ?>#top">Başa dön</a>
  </div>
</footer>

<div class="quickbar">
  <a class="quickbar__btn" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?>Arayın</a>
  <?php if ($wa): ?><a class="quickbar__btn quickbar__btn--wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('wa') ?>WhatsApp</a><?php endif; ?>
  <a class="quickbar__btn quickbar__btn--go" href="<?= e($home) ?>#teklif">Teklif alın</a>
</div>
<?php if ($wa): ?>
<a class="wa-float" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın"><?= icon('wa') ?></a>
<?php endif; ?>
<script src="<?= e(url('assets/js/main.js')) ?>?v=2" defer></script>
</body>
</html>
