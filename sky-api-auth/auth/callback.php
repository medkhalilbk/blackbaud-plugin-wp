<?php
require_once '../includes/blackbaud/blackbaud.php';

$tokens = blackbaud\Auth::exchangeCodeForAccessToken($_GET['code']);

header('Location: /');
exit();
