<?php
// Configuració general del projecte
return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=plataforma_videojocs;charset=utf8mb4',
        'user' => 'superadmin',
        'pass' => 'strongpassword',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
];
