<?php
require __DIR__ . '/includes/page.php';
$active = 'contact';
$page_title = 'İletişim ve teklif';
$page_desc = 'Yaren Tur iletişim bilgileri ve servis teklif formu.';
$flash = flash();
$old = $_SESSION['old_form'] ?? [];
unset($_SESSION['old_form']);
$pre = $_GET['hizmet'] ?? ($old['service'] ?? '');
require __DIR__ . '/includes/header.php';

page_head('İletişim', 'Teklif almak ya da bilgi almak için bize yazın veya arayın.', ['İletişim' => url('iletisim.php')]);
part('contact');
part('quote');

require __DIR__ . '/includes/footer.php';
