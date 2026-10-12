<section class="section" id="hizmetler">
  <div class="wrap">
    <?php if (empty($noHead)): ?>
    <div class="sec-head sec-head--row">
      <div>
        <h2 class="h2">Hizmetlerimiz</h2>
        <p class="lead">Düzenli servisler ve tek seferlik transferler için araç, şoför ve güzergahı biz planlıyoruz.</p>
      </div>
      <a class="more" href="<?= e(url('hizmetler.php')) ?>">Tüm hizmetler <?= icon('arrow') ?></a>
    </div>
    <?php endif; ?>
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
