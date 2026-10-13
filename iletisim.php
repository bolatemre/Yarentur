<?php
require __DIR__ . '/includes/page.php';
$active = 'contact';
$page_title = 'İletişim ve teklif';
$page_desc = 'Yaren Tur iletişim bilgileri. Personel servisi ve kurumsal taşımacılık için 1 dakikada teklif isteyin, size özel güzergah ve fiyatla dönelim.';
$flash = flash();
$old = $_SESSION['old_form'] ?? [];
unset($_SESSION['old_form']);
$pre = $_GET['hizmet'] ?? ($old['service'] ?? '');
require __DIR__ . '/includes/header.php';

page_head('İletişim', 'Teklif almak ya da bilgi almak için bize yazın veya arayın.', ['İletişim' => url('iletisim.php')]);
part('contact');
part('quote');

require __DIR__ . '/includes/footer.php';
