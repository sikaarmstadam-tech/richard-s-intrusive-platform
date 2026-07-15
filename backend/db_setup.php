<?php
// db_setup.php - Database connection and schema manager

$dbPath = __DIR__ . '/database.sqlite';
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 1. Create tables if they do not exist
$db->exec("
    CREATE TABLE IF NOT EXISTS bays (
        bayNum INTEGER PRIMARY KEY,
        status TEXT,
        bookingId INTEGER
    );

    CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        plate TEXT,
        container TEXT,
        bayNum INTEGER,
        phone TEXT,
        callSign TEXT,
        status TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS rosters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        groupName TEXT,
        name TEXT,
        activeRole TEXT,
        present BOOLEAN,
        date TEXT
    );

    CREATE TABLE IF NOT EXISTS incidents (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        severity TEXT DEFAULT 'medium',
        title TEXT NOT NULL,
        statement TEXT,
        location TEXT,
        reportedBy TEXT,
        plateInvolved TEXT,
        imageRef TEXT,
        status TEXT DEFAULT 'open',
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        passwordHash TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'marshal',
        createdBy INTEGER,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        userId INTEGER NOT NULL,
        token TEXT UNIQUE NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        lastActive DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(userId) REFERENCES users(id)
    );
");

// 2. Run migrations - add columns if they don't exist
$migrations = [
    'checkInTime' => 'ALTER TABLE bookings ADD COLUMN checkInTime DATETIME',
    'checkOutTime' => 'ALTER TABLE bookings ADD COLUMN checkOutTime DATETIME',
    'durationMinutes' => 'ALTER TABLE bookings ADD COLUMN durationMinutes INTEGER',
    'weighbillImageRef' => 'ALTER TABLE bookings ADD COLUMN weighbillImageRef TEXT',
    'scanConfidence' => 'ALTER TABLE bookings ADD COLUMN scanConfidence TEXT',
    'driverName' => 'ALTER TABLE bookings ADD COLUMN driverName TEXT',
    'transactionNumber' => 'ALTER TABLE bookings ADD COLUMN transactionNumber TEXT'
];

$existingColumns = [];
$q = $db->query("PRAGMA table_info(bookings)");
while ($row = $q->fetch()) {
    $existingColumns[] = $row['name'];
}

foreach ($migrations as $col => $sql) {
    if (!in_array($col, $existingColumns)) {
        try {
            $db->exec($sql);
        } catch (PDOException $e) {
            // Ignore error if column already exists
        }
    }
}

// 3. Seed superadmin if empty
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'superadmin'");
$res = $stmt->fetch();
if ($res['count'] == 0) {
    $email = 'admin@piplex.io';
    $password = 'PiplexAdmin2024!';
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $name = 'System Administrator';
    $role = 'superadmin';

    $insert = $db->prepare("INSERT INTO users (email, passwordHash, name, role) VALUES (:email, :hash, :name, :role)");
    $insert->execute([
        ':email' => $email,
        ':hash' => $passwordHash,
        ':name' => $name,
        ':role' => $role
    ]);
}
?>
