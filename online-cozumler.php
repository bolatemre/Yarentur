<?php
require __DIR__ . '/includes/page.php';
$active = 'online';
$page_title = 'Online çözümler';
$page_desc = setting('online_text');
require __DIR__ . '/includes/header.php';

page_head('Online çözümler', 'Servislerinizi canlı takip, dijital raporlama ve anlık bildirimlerle yönetiyoruz.', ['Online çözümler' => url('online-cozumler.php')]);
part('online');
part('cta');

require __DIR__ . '/includes/footer.php';
