<?php if ($clients):
$cases = array_values(array_filter($clients, fn($c) => trim($c['detail'] ?? '') !== ''));
?>
<?php if ($cases): ?>
<section class="section cases" aria-label="Referans çalışmalarımız">
  <div class="wrap">
    <div class="sec-head">
      <h2 class="h2">Referans çalışmalarımız</h2>
      <p class="lead">Personel servisini bize emanet eden kurumlardan bazıları.</p>
    </div>
    <ul class="cases__grid">
      <?php foreach ($cases as $c): ?>
      <li class="case">
        <div class="case__top">
          <?php if ($c['logo']): ?><img class="case__logo" src="<?= e(upload_url($c['logo'])) ?>" alt="<?= e($c['name']) ?>" loading="lazy"><?php else: ?><span class="case__mono"><?= e(mb_strtoupper(mb_substr($c['name'], 0, 1))) ?></span><?php endif; ?>
          <div>
            <strong class="case__name"><?= e($c['name']) ?></strong>
            <?php if ($c['sector']): ?><span class="case__sector"><?= e($c['sector']) ?></span><?php endif; ?>
          </div>
        </div>
        <p class="case__detail"><?= e($c['detail']) ?></p>
        <?php if ($c['since']): ?><span class="case__since"><?= icon('clock') ?><?= preg_match('/^\d{4}$/', $c['since']) ? e($c['since']) . ' yılından beri birlikte' : e($c['since']) ?></span><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
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
