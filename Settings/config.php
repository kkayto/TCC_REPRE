<?php
declare(strict_types=1);

const DB_HOST = 'sql112.infinityfree.com';
const DB_NAME = 'if0_43054171_BancoRepre';
const DB_USER = 'if0_43054171';
const DB_PASS = 'Miranha555';

function db(): PDO
{
    static $pdo = null;
if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST .
               ';port=3306' .
               ';dbname=' . DB_NAME .
               ';charset=utf8mb4';

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
