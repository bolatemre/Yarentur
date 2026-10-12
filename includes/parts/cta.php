<?php $ctaSlides = banners(); $ctaBg = $ctaSlides ? $ctaSlides[0]['src'] : ''; ?>
<section class="cta<?= $ctaBg ? ' cta--photo' : '' ?>"<?= $ctaBg ? ' style="--cta-bg: url(\'' . e($ctaBg) . '\')"' : '' ?>>
  <div class="wrap cta__in">
    <div>
      <h2 class="cta__title">Servis ihtiyacınızı konuşalım</h2>
      <p class="cta__text">Güzergahınızı ve yolcu sayınızı paylaşın, size uygun araç ve fiyatla dönüş yapalım.</p>
    </div>
    <div class="cta__actions">
      <a class="btn btn--solid btn--lg" href="<?= e(url('iletisim.php')) ?>#teklif">Teklif alın</a>
      <a class="cta__phone" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a>
    </div>
  </div>
</section>
