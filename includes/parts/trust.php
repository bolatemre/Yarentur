<?php
$trust = setting_pairs('trust_items');
$stats = setting_pairs('stats');
$brochure = brochure_url();
?>
<?php if ($trust): ?>
<section class="section trust" id="guven">
  <div class="wrap">
    <div class="trust__head">
      <div>
        <h2 class="h2"><?= e(setting('trust_title', 'Kurumların bize güvenme sebepleri')) ?></h2>
        <?php if (setting('trust_text')): ?><p class="lead"><?= e(setting('trust_text')) ?></p><?php endif; ?>
      </div>
      <?php if ($brochure): ?>
      <a class="trust__doc" href="<?= e($brochure) ?>" target="_blank" rel="noopener">
        <span class="trust__doc-ico"><?= icon('file') ?></span>
        <span><strong><?= e(setting('brochure_label', 'Kurumsal tanıtım dosyası')) ?></strong><small>PDF olarak indirin, ekibinizle paylaşın</small></span>
      </a>
      <?php endif; ?>
    </div>
    <?php if ($stats): ?>
    <ul class="trust__stats">
      <?php foreach ($stats as $s): ?><li><b><?= e($s['title']) ?></b><span><?= e($s['text']) ?></span></li><?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <ul class="trust__grid">
      <?php foreach ($trust as $t): ?>
      <li>
        <span class="trust__ico"><?= icon(trust_icon($t['title'])) ?></span>
        <strong><?= e($t['title']) ?></strong>
        <?php if ($t['text']): ?><span><?= e($t['text']) ?></span><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
