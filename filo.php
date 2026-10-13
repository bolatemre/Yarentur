<?php
require __DIR__ . '/includes/page.php';
$active = 'fleet';
$page_title = 'Araç filomuz';
$page_desc = 'Yaren Tur araç filosu: personel ve öğrenci servisleri için otobüs, midibüs, minibüs ve VIP araçlar. Kapasite ve özellikleri inceleyin.';
require __DIR__ . '/includes/header.php';

page_head('Araç filomuz', 'Yolcu sayınıza göre doğru aracı seçiyoruz.', ['Araç filomuz' => url('filo.php')]);
part('fleet', ['noHead' => true]);
part('cta');

require __DIR__ . '/includes/footer.php';
