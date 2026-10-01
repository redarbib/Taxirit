<?php
declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbName = 'taxirit';
$dbUser = 'root';
$dbPassword = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS ritten (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer VARCHAR(150) NOT NULL,
            pickup VARCHAR(255) NOT NULL,
            destination VARCHAR(255) NOT NULL,
            ride_date DATE NULL,
            ride_time TIME NULL,
            status ENUM("Gepland", "Toegewezen", "Afgerond") NOT NULL DEFAULT "Gepland",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB'
    );
        $columns = $pdo->query('SHOW COLUMNS FROM ritten LIKE "driver_name"')->fetch();
        if (!$columns) {
            $pdo->exec('ALTER TABLE ritten ADD driver_name VARCHAR(150) NULL AFTER status');
        }
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM("admin", "driver") NOT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );
        $defaultUsers = [
            ['Admin', 'admin@veelauto.nl', 'admin123', 'admin'],
            ['Ahmed Yilmaz', 'ahmed@veelauto.nl', 'driver123', 'driver'],
        ];
        $findUser = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $createUser = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        foreach ($defaultUsers as [$name, $email, $password, $role]) {
            $findUser->execute([$email]);
            if (!$findUser->fetch()) {
                $createUser->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            }
        }
} catch (PDOException $error) {
    http_response_code(500);
    exit('Databaseverbinding mislukt. Controleer of MySQL draait in XAMPP.');
}