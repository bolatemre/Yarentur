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
