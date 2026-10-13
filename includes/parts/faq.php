<?php $faqs = faq_items(); if ($faqs): ?>
<section class="section faq" id="sss">
  <div class="wrap faq__in">
    <div class="sec-head">
      <h2 class="h2">Sık sorulan sorular</h2>
      <p class="lead">Personel servisi hakkında en çok merak edilenler. Aradığınızı bulamazsanız bizi arayın.</p>
    </div>
    <div class="faq__list">
      <?php foreach ($faqs as $i => $f): ?>
      <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= e($f['title']) ?></summary>
        <p><?= e($f['text']) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
$ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faqs as $f) $ld['mainEntity'][] = ['@type' => 'Question', 'name' => $f['title'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['text']]];
echo json_ld($ld);
endif; ?>
