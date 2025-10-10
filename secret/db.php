<?php
$config = require __DIR__ . '/config.php';
try {
    $pdo = new PDO(
        $config['db']['dsn'],
        $config['db']['user'],
        $config['db']['pass'],
        $config['db']['options']
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Error de connexió amb la base de dades.');
}
