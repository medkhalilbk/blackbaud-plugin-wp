<?php
require_once '../includes/blackbaud/blackbaud.php';

// Get one fund record by ID.
if (isset($_GET['id'])) {
  $data = blackbaud\Funds::getById($_GET['id']);

  // Access token has expired. Attempt to refresh.
  if (isset($data['statusCode']) && $data['statusCode'] == 401) {
    $response = blackbaud\Auth::refreshAccessToken();
    $token = json_decode($response, true);

    if (!isset($token['access_token'])) {
      echo json_encode($token);
      return;
    }

    $data = blackbaud\Funds::getById($_GET['id']);
  }

  echo json_encode(array('fund' => $data));
}

// get all funds
else {
    $data = blackbaud\Funds::getAll();

    echo json_encode(array('funds' => $data['value']));
}
