<?php
require __DIR__ . '/includes/page.php';
$active = 'about';
$page_title = 'Kurumsal';
$page_desc = setting('meta_description');
require __DIR__ . '/includes/header.php';

page_head('Kurumsal', 'Yaren Tur\'u, çalışma ilkelerimizi ve hizmet verdiğimiz kurumları tanıyın.', ['Kurumsal' => url('hakkimizda.php')]);
part('about');
part('trust');
part('clients');
part('cta');

require __DIR__ . '/includes/footer.php';
