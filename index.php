<?php
require __DIR__ . '/includes/page.php';
$active = 'home';
require __DIR__ . '/includes/header.php';

part('hero');
part('services');
part('online', ['teaser' => true]);
part('about', ['teaser' => true]);
part('clients');
part('cta');

require __DIR__ . '/includes/footer.php';
