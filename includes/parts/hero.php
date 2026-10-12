<section class="hero <?= $slides ? 'hero--photo' : 'hero--plain' ?>">
  <?php if ($slides): ?>
  <div class="hero__media" data-slider>
    <?php foreach ($slides as $i => $b): ?>
    <img class="hero__slide<?= $i === 0 ? ' is-on' : '' ?>" src="<?= e($b['src']) ?>" alt="" style="--fx: <?= (int)$b['focus'] ?>%" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
    <?php endforeach; ?>
    <?php if (count($slides) > 1): ?>
    <div class="hero__dots" aria-label="Banner görselleri">
      <?php foreach ($slides as $i => $b): ?><button type="button" class="<?= $i === 0 ? 'is-on' : '' ?>" aria-label="<?= $i + 1 ?>. görsel"></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="hero__body">
    <div class="wrap">
      <div class="hero__copy">
        <h1 class="hero__title"><?= e(setting('hero_title')) ?></h1>
        <p class="hero__text"><?= e(setting('hero_text')) ?></p>
        <div class="hero__actions">
          <a class="btn btn--solid btn--lg" href="<?= e(url('iletisim.php')) ?>#teklif">Teklif alın</a>
          <a class="btn btn--ghost btn--lg" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="routebar">
  <div class="route" aria-hidden="true">
    <div class="wrap route__in">
      <div class="route__line"><span class="route__bus"><?= icon('bus') ?></span></div>
      <ol class="route__stops">
        <li style="--at:0%">Kapınız</li><li style="--at:33.333%">Okul</li><li style="--at:66.666%">Fabrika</li><li style="--at:100%">Havalimanı</li>
      </ol>
    </div>
  </div>
</section>
