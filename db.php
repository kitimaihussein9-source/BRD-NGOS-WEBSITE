<?php
function db()
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('BRD_DB_HOST') ?: '127.0.0.1';
    $port = getenv('BRD_DB_PORT') ?: '3306';
    $name = getenv('BRD_DB_NAME') ?: 'brd_ngos';
    $user = getenv('BRD_DB_USER') ?: 'root';
    $password = getenv('BRD_DB_PASSWORD') ?: '';
    try {
        $server = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $password);
        $server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
        $server->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$safeName};charset=utf8mb4", $user, $password);
    } catch (PDOException $error) {
        http_response_code(500);
        exit('BRD database connection failed. Start MySQL in XAMPP and verify the MySQL credentials.');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, username VARCHAR(100) NOT NULL UNIQUE, role ENUM("client", "assistant") NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS content (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, type ENUM("announcement", "course", "consultancy") NOT NULL, title VARCHAR(255) NOT NULL, details TEXT NOT NULL, image_path VARCHAR(255) NULL, video_path VARCHAR(255) NULL, published_by VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $typeCheck = $pdo->query("SHOW COLUMNS FROM content LIKE 'type'")->fetch();
    if ($typeCheck && strpos($typeCheck['Type'], "'consultancy'") === false) {
        $pdo->exec('ALTER TABLE content MODIFY type ENUM("announcement", "course", "consultancy") NOT NULL');
    }
    foreach (['image_path' => 'VARCHAR(255) NULL', 'video_path' => 'VARCHAR(255) NULL'] as $column => $definition) {
        $columnCheck = $pdo->prepare('SHOW COLUMNS FROM content LIKE ?');
        $columnCheck->execute([$column]);
        if (!$columnCheck->fetch()) {
            $pdo->exec("ALTER TABLE content ADD COLUMN {$column} {$definition}");
        }
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS requests (id VARCHAR(80) PRIMARY KEY, name VARCHAR(150) NOT NULL, username VARCHAR(100) NOT NULL, type VARCHAR(30) NOT NULL, subject VARCHAR(255) NOT NULL, message TEXT NOT NULL, status VARCHAR(40) NOT NULL, payment_status VARCHAR(40) NOT NULL, created_at DATETIME NOT NULL, INDEX requests_username_idx (username)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    migrateLegacyData($pdo);
    return $pdo;
}

function migrateLegacyData(PDO $pdo)
{
    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $accountFile = __DIR__ . DIRECTORY_SEPARATOR . 'accounts.json';
    if ($userCount === 0 && file_exists($accountFile)) {
        $accounts = json_decode(file_get_contents($accountFile), true) ?: [];
        $statement = $pdo->prepare('INSERT IGNORE INTO users (name, username, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?)');
        foreach ($accounts as $account) {
            if (in_array($account['role'] ?? '', ['client', 'assistant'], true)) {
                $statement->execute([$account['name'], $account['username'], $account['role'], $account['password'], date('Y-m-d H:i:s')]);
            }
        }
    }

    $contentCount = (int) $pdo->query('SELECT COUNT(*) FROM content')->fetchColumn();
    $contentFile = __DIR__ . DIRECTORY_SEPARATOR . 'portal_content.json';
    if ($contentCount === 0 && file_exists($contentFile)) {
        $content = json_decode(file_get_contents($contentFile), true) ?: [];
        $statement = $pdo->prepare('INSERT INTO content (type, title, details, published_by, created_at) VALUES (?, ?, ?, ?, ?)');
        foreach (($content['announcements'] ?? []) as $item) {
            $statement->execute(['announcement', $item['title'] ?? '', $item['body'] ?? '', 'system', date('Y-m-d H:i:s')]);
        }
        foreach (($content['courses'] ?? []) as $item) {
            $statement->execute(['course', $item['title'] ?? '', $item['details'] ?? '', 'system', date('Y-m-d H:i:s')]);
        }
    }

    $requestCount = (int) $pdo->query('SELECT COUNT(*) FROM requests')->fetchColumn();
    $requestFile = __DIR__ . DIRECTORY_SEPARATOR . 'client_requests.json';
    if ($requestCount === 0 && file_exists($requestFile)) {
        $requests = json_decode(file_get_contents($requestFile), true) ?: [];
        $statement = $pdo->prepare('INSERT IGNORE INTO requests (id, name, username, type, subject, message, status, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($requests as $request) {
            $statement->execute([$request['id'], $request['name'], $request['username'], $request['type'], $request['subject'] ?? '', $request['message'] ?? '', $request['status'] ?? 'Submitted', $request['payment_status'] ?? 'Pending', date('Y-m-d H:i:s', strtotime($request['created_at'] ?? 'now'))]);
        }
    }
}
