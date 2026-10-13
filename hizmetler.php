<?php
require __DIR__ . '/includes/page.php';
$active = 'services';
$page_title = 'Hizmetlerimiz';
$page_desc = 'Personel ve öğrenci taşımacılığı, tur organizasyonu, havalimanı ve VIP transfer, şoförlü araç kiralama. Yaren Tur hizmetlerini inceleyin, teklif alın.';
require __DIR__ . '/includes/header.php';

page_head('Hizmetlerimiz', 'Düzenli servisler ve tek seferlik transferler için araç, şoför ve güzergahı biz planlıyoruz.', ['Hizmetler' => url('hizmetler.php')]);
part('services', ['noHead' => true]);
part('cta');

require __DIR__ . '/includes/footer.php';
