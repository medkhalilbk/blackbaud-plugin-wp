<?php
require_once '../includes/blackbaud/blackbaud.php';

blackbaud\Api_auth::logout();
echo json_encode(array());
