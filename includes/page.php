<?php
/** Sayfaların ortak verisi */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/mockups.php';

$services = active_services();
$featured = array_slice($services, 0, 2);
$others   = array_slice($services, 2);
$vehicles = db()->query('SELECT * FROM vehicles WHERE active = 1 ORDER BY sort, id')->fetchAll();
$clients  = db()->query('SELECT * FROM clients WHERE active = 1 ORDER BY sort, id')->fetchAll();
$slides   = banners();
$aboutImg = setting_image('about_image', 'about.jpg');

/** İç sayfa başlığı */
function page_head(string $title, string $lead = '', array $crumbs = [], string $icon = ''): void
{
    $slides = banners();
    $bg = $slides ? $slides[count($slides) > 1 ? 1 : 0]['src'] : '';
    ?>
<section class="phead<?= $bg ? ' phead--photo' : '' ?>"<?= $bg ? ' style="--phead-bg: url(\'' . e($bg) . '\')"' : '' ?>>
  <div class="wrap phead__in">
    <nav class="crumbs" aria-label="Konum">
      <a href="<?= e(url('')) ?>">Ana sayfa</a>
      <?php foreach ($crumbs as $label => $href): ?><span aria-hidden="true">/</span><a href="<?= e($href) ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <div class="phead__row">
      <?php if ($icon): ?><span class="phead__ico"><?= icon($icon) ?></span><?php endif; ?>
      <h1 class="phead__title"><?= e($title) ?></h1>
    </div>
    <?php if ($lead): ?><p class="phead__lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
<?php
}

function part(string $name, array $vars = []): void
{
    extract($GLOBALS, EXTR_SKIP);
    extract($vars, EXTR_OVERWRITE);
    include __DIR__ . '/parts/' . $name . '.php';
}
