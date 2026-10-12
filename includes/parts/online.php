<section class="online<?= !empty($teaser) ? ' online--teaser' : '' ?>" id="online">
  <div class="wrap">
    <div class="online__top">
      <div class="online__head">
        <p class="online__label"><?= e(setting('online_label')) ?></p>
        <h2 class="online__title"><?= e(setting('online_title')) ?></h2>
        <p class="online__lead"><?= e(setting('online_text')) ?></p>
        <?php if (!empty($teaser)): ?>
        <p class="online__more"><a class="btn btn--solid btn--lg" href="<?= e(url('online-cozumler.php')) ?>">Online çözümleri inceleyin</a></p>
        <?php endif; ?>
      </div>
      <?php if (!empty($teaser)): ?>
      <div class="orow__visual orow__visual--desk">
        <?php $img = setting_image('online_img_company'); ?>
        <?= $img ? '<img class="orow__shot" src="' . e($img) . '" alt="' . e(setting('online_company_title')) . '" loading="lazy">' : mockup_dashboard() ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if (empty($teaser)): ?>
    <div class="orow">
      <div class="orow__visual orow__visual--desk">
        <?php $img = setting_image('online_img_company'); ?>
        <?= $img ? '<img class="orow__shot" src="' . e($img) . '" alt="' . e(setting('online_company_title')) . '" loading="lazy">' : mockup_dashboard() ?>
      </div>
      <div class="orow__text">
        <h3 class="orow__h"><?= icon('monitor', 'ico orow__ico') ?><?= e(setting('online_company_title')) ?></h3>
        <ul class="ticks">
          <?php foreach (setting_lines('online_company') as $line): ?><li><?= icon('check', 'ico ticks__ico') ?><span><?= e($line) ?></span></li><?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="orow orow--rev">
      <div class="orow__visual orow__visual--phone">
        <?php $img = setting_image('online_img_mobile'); ?>
        <?= $img ? '<img class="orow__shot orow__shot--phone" src="' . e($img) . '" alt="' . e(setting('online_mobile_title')) . '" loading="lazy">' : mockup_phone() ?>
      </div>
      <div class="orow__text">
        <h3 class="orow__h"><?= icon('mobile', 'ico orow__ico') ?><?= e(setting('online_mobile_title')) ?></h3>
        <ul class="ticks">
          <?php foreach (setting_lines('online_mobile') as $line): ?><li><?= icon('check', 'ico ticks__ico') ?><span><?= e($line) ?></span></li><?php endforeach; ?>
        </ul>
      </div>
    </div>

    <?php endif; ?>
    <?php $ben = setting_pairs('online_benefits'); if ($ben): $bi = ['chart', 'shield', 'smile']; ?>
    <ul class="benefits">
      <?php foreach ($ben as $i => $b): ?>
      <li><?= icon($bi[$i % 3], 'ico benefits__ico') ?><strong><?= e($b['title']) ?></strong><span><?= e($b['text']) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>
