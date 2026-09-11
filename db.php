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
        exit('BRD database connection failed. Verify the configured database host, database name, username, password, and MySQL service.');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, username VARCHAR(100) NOT NULL UNIQUE, role ENUM("client", "assistant", "admin") NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    foreach (['email' => 'VARCHAR(180) NULL', 'phone' => 'VARCHAR(40) NULL', 'bio' => 'TEXT NULL', 'photo_path' => 'VARCHAR(255) NULL'] as $column => $definition) {
        $columnCheck = $pdo->prepare('SHOW COLUMNS FROM users LIKE ?');
        $columnCheck->execute([$column]);
        if (!$columnCheck->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN {$column} {$definition}");
        }
    }
    $userRoleCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
    if ($userRoleCheck && strpos($userRoleCheck['Type'], "'admin'") === false) {
        $pdo->exec('ALTER TABLE users MODIFY role ENUM("client", "assistant", "admin") NOT NULL');
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS content (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, type ENUM("announcement", "course", "consultancy") NOT NULL, title VARCHAR(255) NOT NULL, details TEXT NOT NULL, image_path VARCHAR(255) NULL, video_path VARCHAR(255) NULL, published_by VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS projects (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, location VARCHAR(150) NOT NULL, status VARCHAR(50) NOT NULL, details TEXT NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS publications (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, type VARCHAR(80) NOT NULL, details TEXT NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS events (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, event_date DATE NOT NULL, location VARCHAR(150) NOT NULL, details TEXT NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS donations (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, supporter_name VARCHAR(150) NOT NULL, support_type VARCHAR(50) NOT NULL, amount VARCHAR(50) NOT NULL, note TEXT NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
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
    foreach (['email' => 'VARCHAR(180) NULL', 'phone' => 'VARCHAR(40) NULL', 'course_name' => 'VARCHAR(255) NULL', 'duration' => 'VARCHAR(80) NULL'] as $column => $definition) {
        $columnCheck = $pdo->prepare('SHOW COLUMNS FROM requests LIKE ?');
        $columnCheck->execute([$column]);
        if (!$columnCheck->fetch()) {
            $pdo->exec("ALTER TABLE requests ADD COLUMN {$column} {$definition}");
        }
    }

    migrateLegacyData($pdo);
    return $pdo;
}

function migrateLegacyData(PDO $pdo)
{
    $adminStatement = $pdo->prepare('SELECT id FROM users WHERE username = ? AND role = ? LIMIT 1');
    $adminStatement->execute(['admin', 'admin']);
    if (!$adminStatement->fetch()) {
        $pdo->prepare('INSERT INTO users (name, username, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?)')
            ->execute(['BRD Administrator', 'admin', 'admin', password_hash('admin123', PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
    }

    $userCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE username <> 'admin'")->fetchColumn();
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

    $projectCount = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    if ($projectCount === 0) {
        $projects = [
            ['title' => 'Community resilience baseline', 'location' => 'Dar es Salaam', 'status' => 'Active', 'details' => 'Research and data collection project focused on household resilience and local preparedness.'],
            ['title' => 'Flood risk mapping', 'location' => 'Kigoma', 'status' => 'In review', 'details' => 'Spatial assessment for flood-prone communities and preparedness planning support.'],
            ['title' => 'Climate adaptation review', 'location' => 'Morogoro', 'status' => 'Planned', 'details' => 'Assessment of local adaptation needs and resilience measures in climate-sensitive areas.']
        ];
        $projectStatement = $pdo->prepare('INSERT INTO projects (title, location, status, details, created_at) VALUES (?, ?, ?, ?, ?)');
        foreach ($projects as $project) {
            $projectStatement->execute([$project['title'], $project['location'], $project['status'], $project['details'], date('Y-m-d H:i:s')]);
        }
    }

    $publicationCount = (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
    if ($publicationCount === 0) {
        $publications = [
            ['title' => 'Climate and disaster resilience brief', 'type' => 'Policy brief', 'details' => 'Insights on local resilience planning and risk communication for communities.'],
            ['title' => 'Research methodology guide for local teams', 'type' => 'Guide', 'details' => 'A practical guide on methods, sampling, and evidence generation.'],
            ['title' => 'Community disaster preparedness report', 'type' => 'Report', 'details' => 'Assessment of preparedness practices and support opportunities in vulnerable communities.']
        ];
        $publicationStatement = $pdo->prepare('INSERT INTO publications (title, type, details, created_at) VALUES (?, ?, ?, ?)');
        foreach ($publications as $publication) {
            $publicationStatement->execute([$publication['title'], $publication['type'], $publication['details'], date('Y-m-d H:i:s')]);
        }
    }

    $eventCount = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    if ($eventCount === 0) {
        $events = [
            ['title' => 'Community resilience planning clinic', 'event_date' => '2026-10-12', 'location' => 'Dar es Salaam', 'details' => 'A practical session on local planning, preparedness and resilience coordination.'],
            ['title' => 'Climate adaptation and GIS workshop', 'event_date' => '2026-11-05', 'location' => 'Morogoro', 'status' => 'Workshop', 'details' => 'A focused workshop on spatial risk mapping and adaptation planning.'],
            ['title' => 'Disaster preparedness outreach week', 'event_date' => '2026-12-01', 'location' => 'Kigoma', 'details' => 'Community engagement and risk communication with local leaders and youth groups.']
        ];
        $eventStatement = $pdo->prepare('INSERT INTO events (title, event_date, location, details, created_at) VALUES (?, ?, ?, ?, ?)');
        foreach ($events as $event) {
            $eventStatement->execute([$event['title'], $event['event_date'], $event['location'], $event['details'], date('Y-m-d H:i:s')]);
        }
    }

    $donationCount = (int) $pdo->query('SELECT COUNT(*) FROM donations')->fetchColumn();
    if ($donationCount === 0) {
        $donations = [
            ['supporter_name' => 'Tanzania Resilience Fund', 'support_type' => 'Partnership', 'amount' => 'TZS 5,000,000', 'note' => 'Supporting community preparedness outreach and field tools.'],
            ['supporter_name' => 'Green Horizon Collective', 'support_type' => 'Donation', 'amount' => 'TZS 2,350,000', 'note' => 'Support for coastal risk mapping and outreach.'],
            ['supporter_name' => 'Kigoma Youth Network', 'support_type' => 'In-kind support', 'amount' => 'TZS 1,100,000', 'note' => 'Volunteer mobilization and community training materials.']
        ];
        $donationStatement = $pdo->prepare('INSERT INTO donations (supporter_name, support_type, amount, note, created_at) VALUES (?, ?, ?, ?, ?)');
        foreach ($donations as $donation) {
            $donationStatement->execute([$donation['supporter_name'], $donation['support_type'], $donation['amount'], $donation['note'], date('Y-m-d H:i:s')]);
        }
    }
}
