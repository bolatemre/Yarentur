<?php
$paras = preg_split("/\R{2,}/", trim(setting('about_text')));
// Ana sayfa özetinde araç fotoğrafı (son banner) daha iyi görünür; yoksa hakkımızda görseli
$introImg = $slides ? $slides[count($slides) - 1]['src'] : $aboutImg;
?>
<?php if (!empty($teaser)): ?>
<section class="section" id="hakkimizda">
  <div class="wrap intro<?= $introImg ? ' intro--img' : '' ?>">
    <?php if ($introImg): ?><figure class="intro__img"><img src="<?= e($introImg) ?>" alt="<?= e(setting('site_title', 'Yaren Tur')) ?> servis aracı" loading="lazy"></figure><?php endif; ?>
    <div class="intro__text">
      <h2 class="h2"><?= e(setting('about_title')) ?></h2>
      <p class="intro__lead"><?= e($paras[0] ?? '') ?></p>
      <?php $hl = highlights(); if ($hl): ?>
      <ul class="intro__list">
        <?php foreach (array_slice($hl, 0, 4) as $h): ?><li><?= icon('check', 'ico hl__ico') ?><?= e($h['title']) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <a class="more" href="<?= e(url('hakkimizda.php')) ?>">Bizi daha yakından tanıyın <?= icon('arrow') ?></a>
    </div>
  </div>
</section>
<?php else: ?>
<section class="section" id="hakkimizda">
  <div class="wrap about-page">
    <?php if ($aboutImg): ?><figure class="about-page__img"><img src="<?= e($aboutImg) ?>" alt="<?= e(setting('site_title', 'Yaren Tur')) ?> servis aracı"></figure><?php endif; ?>
    <div class="about-page__grid">
      <div class="about-page__text">
        <h2 class="h2"><?= e(setting('about_title')) ?></h2>
        <?= paragraphs(setting('about_text')) ?>
      </div>
      <?php $hl = highlights(); if ($hl): ?>
      <aside class="about-page__side">
        <h3 class="side__h">Çalışma ilkelerimiz</h3>
        <ul class="hl">
          <?php foreach ($hl as $h): ?>
          <li><?= icon('check', 'ico hl__ico') ?><div><strong><?= e($h['title']) ?></strong><span><?= e($h['text']) ?></span></div></li>
          <?php endforeach; ?>
        </ul>
      </aside>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
