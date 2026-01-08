<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../backend/dotenv.php';

$serverHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];

$isLocalhost = in_array($serverHost, ['localhost', '127.0.0.1'], true)
               || str_starts_with($serverHost, 'localhost:');

$baseFromEnv = $_ENV['BASE_URL'] ?? '';

$BASE_URL = $isLocalhost ? '/' : ($baseFromEnv ?: '/yrgopelag/');
$BASE_URL = rtrim($BASE_URL, '/') . '/';

?>

<!DOCTYPE HTML>
    <head>
        <title> DINOSAUR HOTEL </title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= $BASE_URL ?>styles/variables.css">
        <link rel="stylesheet" href="<?= $BASE_URL ?>styles/main.css">
        <link rel="stylesheet" href="<?= $BASE_URL ?>styles/calendar.css">
        <link rel="stylesheet" href="<?= $BASE_URL ?>styles/booking.css">
    </head>
<body>