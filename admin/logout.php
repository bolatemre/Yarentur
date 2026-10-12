<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$_SESSION = [];
session_destroy();
redirect('login.php');
