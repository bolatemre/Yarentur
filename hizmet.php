<?php
require __DIR__ . '/includes/page.php';

$st = db()->prepare('SELECT * FROM services WHERE slug = ? AND active = 1');
$st->execute([(string)($_GET['s'] ?? '')]);
$svc = $st->fetch();
if (!$svc) {
    http_response_code(404);
    $page_title = 'Sayfa bulunamadı';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="wrap narrow"><h1 class="h2">Bu hizmet sayfası bulunamadı</h1><p>Hizmet kaldırılmış ya da adres değişmiş olabilir.</p><p><a class="btn btn--solid" href="' . e(url('hizmetler.php')) . '">Tüm hizmetlere dönün</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$page_title = $svc['title'];
$page_desc  = $svc['summary'];
$active = 'services';
$others = array_filter(active_services(), fn($s) => $s['id'] !== $svc['id']);
require __DIR__ . '/includes/header.php';
?>
<?php page_head($svc['title'], $svc['summary'], ['Hizmetler' => url('hizmetler.php'), $svc['title'] => url('hizmet.php?s=' . $svc['slug'])], $svc['icon']); ?>
<section class="section section--tight">
  <div class="wrap detail">
    <article class="detail__body">
      <?php if ($svc['image']): ?><img class="detail__img" src="<?= e(upload_url($svc['image'])) ?>" alt="<?= e($svc['title']) ?>"><?php endif; ?>
      <?= paragraphs($svc['body']) ?>
      <p><a class="btn btn--solid btn--lg" href="<?= e(url('iletisim.php?hizmet=' . $svc['slug'])) ?>#teklif">Bu hizmet için teklif alın</a></p>
    </article>
    <aside class="detail__side">
      <div class="callbox">
        <strong>Hemen bilgi alın</strong>
        <a class="callbox__phone" href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><?= e(setting('phone')) ?></a>
        <?php $wa = whatsapp_href($svc['title'] . ' hakkında bilgi almak istiyorum.'); if ($wa): ?>
        <a class="btn btn--line" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('wa') ?>WhatsApp ile yazın</a>
        <?php endif; ?>
      </div>
      <?php if ($others): ?>
      <h2 class="side__h">Diğer hizmetler</h2>
      <ul class="side__list">
        <?php foreach ($others as $o): ?><li><a href="<?= e(url('hizmet.php?s=' . $o['slug'])) ?>"><?= icon($o['icon']) ?><?= e($o['title']) ?></a></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </aside>
  </div>
</section>
<?php part('cta');
require __DIR__ . '/includes/footer.php';
