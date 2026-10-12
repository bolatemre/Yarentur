<?php if ($vehicles): ?>
<section class="section section--mist" id="filo">
  <div class="wrap">
    <?php if (empty($noHead)): ?>
    <div class="sec-head">
      <h2 class="h2">Araç filomuz</h2>
      <p class="lead">Yolcu sayınıza göre doğru aracı seçiyoruz. Kapasitelerde ilk sayı yolcu, +1 şoför koltuğudur.</p>
    </div>
    <?php else: ?>
    <p class="lead fleet__note">Kapasitelerde ilk sayı yolcu, +1 şoför koltuğudur. Her ihtiyaca uygun aracı birlikte seçelim.</p>
    <?php endif; ?>
    <div class="fleet">
      <?php foreach ($vehicles as $v): ?>
      <article class="veh<?= $v['image'] ? '' : ' veh--plain' ?>">
        <?php if ($v['image']): ?>
        <div class="veh__media"><img src="<?= e(upload_url($v['image'])) ?>" alt="<?= e($v['name']) ?>" loading="lazy"></div>
        <?php endif; ?>
        <div class="veh__cap"><?= e($v['capacity']) ?><small>kişilik</small></div>
        <h3 class="veh__name"><?= e($v['name']) ?></h3>
        <p class="veh__feat"><?= e($v['features']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
