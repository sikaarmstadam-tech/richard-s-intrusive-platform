<?php
/**
 * config.php â€” Piplex Database & Application Configuration
 * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * Responsibilities:
 *   - SQLite connection (lazy singleton via getDb())
 *   - Schema creation & migrations
 *   - Session auth helpers
 *   - Shift cycle calculations
 *   - Ghana public holiday lookup
 *   - Default roster data
 *   - Global role list constant
 *   - JSON response helpers
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// â”€â”€ Database path â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
    define('DB_PATH', '/tmp/database.sqlite');
    // Copy the seed database from root to /tmp if it doesn't exist in /tmp yet
    if (!file_exists('/tmp/database.sqlite')) {
        $sourceDb = __DIR__ . '/backend/database.sqlite';
        if (file_exists($sourceDb)) {
            copy($sourceDb, '/tmp/database.sqlite');
            chmod('/tmp/database.sqlite', 0666);
        }
    }
} else {
    define('DB_PATH', __DIR__ . '/backend/database.sqlite');
}

// â”€â”€ PDO singleton â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function getDb(): PDO {
    static $db = null;
    if ($db === null) {
        if (!isset($_ENV['VERCEL']) && !getenv('VERCEL')) {
            if (!is_dir(__DIR__ . '/backend')) mkdir(__DIR__ . '/backend', 0755, true);
        }
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Disable WAL mode on Vercel as it can sometimes cause issues on ephemeral /tmp filesystems
        if (!isset($_ENV['VERCEL']) && !getenv('VERCEL')) {
            $db->exec('PRAGMA journal_mode=WAL;');
        }
        setupDb($db);
    }
    return $db;
}

// â”€â”€ Schema setup & migrations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function setupDb(PDO $db): void {
    // Create tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            email        TEXT UNIQUE NOT NULL,
            passwordHash TEXT NOT NULL,
            name         TEXT NOT NULL,
            role         TEXT NOT NULL DEFAULT 'marshal',
            createdAt    DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS bays (
            bayNum    INTEGER PRIMARY KEY,
            status    TEXT DEFAULT 'free',
            bookingId INTEGER
        );
        CREATE TABLE IF NOT EXISTS bookings (
            id                INTEGER PRIMARY KEY AUTOINCREMENT,
            plate             TEXT,
            container         TEXT,
            bayNum            INTEGER,
            phone             TEXT,
            driverName        TEXT,
            transactionNumber TEXT,
            callSign          TEXT,
            status            TEXT DEFAULT 'active',
            notes             TEXT,
            checkInTime       DATETIME DEFAULT CURRENT_TIMESTAMP,
            checkOutTime      DATETIME,
            durationMinutes   INTEGER,
            weighbillImageRef TEXT,
            timestamp         DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS rosters (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            groupName  TEXT,
            name       TEXT,
            activeRole TEXT,
            present    INTEGER DEFAULT 1,
            date       TEXT
        );
        CREATE TABLE IF NOT EXISTS incidents (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            type          TEXT NOT NULL,
            severity      TEXT DEFAULT 'medium',
            title         TEXT NOT NULL,
            statement     TEXT,
            location      TEXT,
            reportedBy    TEXT,
            plateInvolved TEXT,
            imageRef      TEXT,
            status        TEXT DEFAULT 'open',
            timestamp     DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Migrations â€” safely add columns that may not exist in older DBs
    $migrate = [
        'checkInTime DATETIME',
        'checkOutTime DATETIME',
        'durationMinutes INTEGER',
        'weighbillImageRef TEXT',
        'driverName TEXT',
        'transactionNumber TEXT',
        'notes TEXT',
    ];
    foreach ($migrate as $col) {
        try { $db->exec("ALTER TABLE bookings ADD COLUMN $col"); } catch (Exception $e) {}
    }

    // Seed superadmin if none exists
    $existing = $db->query("SELECT id FROM users WHERE role='superadmin' LIMIT 1")->fetch();
    if (!$existing) {
        $hash = password_hash('PiplexAdmin2024!', PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO users (email,passwordHash,name,role) VALUES (?,?,?,?)")
           ->execute(['admin@piplex.io', $hash, 'System Administrator', 'superadmin']);
    }

    // Seed all 60 bays as 'free' if none exist yet
    // CS7 = Bays 1-30 | CS8 = Bays 51-80
    $bayCount = (int)$db->query("SELECT COUNT(*) FROM bays")->fetchColumn();
    if ($bayCount === 0) {
        $ins = $db->prepare("INSERT OR IGNORE INTO bays (bayNum, status, bookingId) VALUES (?, 'free', NULL)");
        $allBays = array_merge(range(1, 30), range(51, 80));
        foreach ($allBays as $num) {
            $ins->execute([$num]);
        }
    }
}

// â”€â”€ Auth helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function currentUser(): ?array { return $_SESSION['piplex_user'] ?? null; }
function isLoggedIn():  bool   { return isset($_SESSION['piplex_user']); }
function isAdmin():     bool   {
    $u = currentUser();
    return $u && in_array($u['role'], ['admin', 'superadmin']);
}

// â”€â”€ Shift Cycle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Anchor: Group A = Morning on 2025-06-17
// Cycle: 6 days (2 morning â†’ 2 night â†’ 2 off), offset per group
function getGroupShift(string $dateStr, string $group): string {
    $offsets = ['A' => 0, 'B' => 2, 'C' => 4];
    $offset  = $offsets[$group] ?? 0;
    $anchor  = new DateTime('2025-06-17');
    $target  = new DateTime($dateStr);
    $diff    = (int) $anchor->diff($target)->format('%r%a');
    $cycle   = (($diff + $offset) % 6 + 6) % 6;
    if ($cycle <= 1) return 'Morning';
    if ($cycle <= 3) return 'Night';
    return 'Off';
}

function getActiveGroup(): array {
    $today   = date('Y-m-d');
    $hour    = (int) date('H');
    $isNight = ($hour >= 18 || $hour < 6);
    foreach (['A', 'B', 'C'] as $g) {
        $s = getGroupShift($today, $g);
        if ($isNight && $s === 'Night')    return ['group' => $g, 'shift' => 'Night'];
        if (!$isNight && $s === 'Morning') return ['group' => $g, 'shift' => 'Morning'];
    }
    return ['group' => 'A', 'shift' => 'Morning'];
}

// â”€â”€ Ghana Public Holidays â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function getGhanaHoliday(string $dateStr): ?string {
    static $holidays = [
        '2025' => [
            '01-01' => "New Year's Day",    '01-07' => 'Constitution Day',
            '03-06' => 'Independence Day',  '04-18' => 'Good Friday',
            '04-21' => 'Easter Monday',     '03-31' => 'Eid al-Fitr',
            '06-07' => 'Eid al-Adha',       '05-01' => "May Day",
            '08-04' => "Founders' Day",     '09-21' => 'Kwame Nkrumah Day',
            '12-05' => "Farmer's Day",      '12-25' => 'Christmas',
            '12-26' => 'Boxing Day',
        ],
        '2026' => [
            '01-01' => "New Year's Day",    '01-07' => 'Constitution Day',
            '03-06' => 'Independence Day',  '04-03' => 'Good Friday',
            '04-06' => 'Easter Monday',     '03-20' => 'Eid al-Fitr',
            '05-27' => 'Eid al-Adha',       '05-01' => "May Day",
            '08-04' => "Founders' Day",     '09-21' => 'Kwame Nkrumah Day',
            '12-04' => "Farmer's Day",      '12-25' => 'Christmas',
            '12-26' => 'Boxing Day',
        ],
    ];
    return $holidays[substr($dateStr, 0, 4)][substr($dateStr, 5, 5)] ?? null;
}

// â”€â”€ Default Rosters â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Returned when no roster has been saved for today
function getDefaultRosters(): array {
    return [
        'A' => [
            ['name' => 'Oppong Kyekyeku',  'activeRole' => 'Foreman',               'present' => true],
            ['name' => 'Richard Oteng',    'activeRole' => 'CS7 Booking (B1-30)',   'present' => true],
            ['name' => 'Piplex Chrisbrown','activeRole' => 'CS7 Parking (B1-30)',   'present' => true],
            ['name' => 'Jessica',          'activeRole' => 'CS8 Booking (B51-80)',  'present' => true],
            ['name' => 'Emmanuel Adu',     'activeRole' => 'CS8 Parking (B51-80)', 'present' => true],
            ['name' => 'Habiba',           'activeRole' => 'CS6 Holding Area',      'present' => true],
            ['name' => 'Abeiku Crentsil',  'activeRole' => 'Platform 1',            'present' => true],
            ['name' => 'Nana Yaw',         'activeRole' => 'Platform 2',            'present' => true],
            ['name' => 'Jojo Quansah',     'activeRole' => 'Roaming Yard-wide',     'present' => true],
            ['name' => 'Emmanuel Addo',    'activeRole' => 'CS9 Rotational',        'present' => true],
            ['name' => 'Samuel Tetteh',    'activeRole' => 'CS10 Rotational',       'present' => true],
            ['name' => 'Eric Mensah',      'activeRole' => 'CS11 Rotational',       'present' => true],
            ['name' => 'Bright Owusu',     'activeRole' => 'CS12 Rotational',       'present' => true],
        ],
        'B' => [
            ['name' => 'Amara Diallo',     'activeRole' => 'Foreman',               'present' => true],
            ['name' => 'Kwaku Addae',      'activeRole' => 'CS7 Booking (B1-30)',   'present' => true],
            ['name' => 'Yaw Frimpong',     'activeRole' => 'CS7 Parking (B1-30)',   'present' => true],
            ['name' => 'Kojo Antwi',       'activeRole' => 'CS8 Booking (B51-80)',  'present' => true],
            ['name' => 'Kwabena Baffour',  'activeRole' => 'CS8 Parking (B51-80)', 'present' => true],
            ['name' => 'Sena Dogbe',       'activeRole' => 'CS6 Holding Area',      'present' => true],
            ['name' => 'Mawuli Gbeho',     'activeRole' => 'Platform 1',            'present' => true],
            ['name' => 'Selorm Kpodo',     'activeRole' => 'Platform 2',            'present' => true],
            ['name' => 'Dela Kuma',        'activeRole' => 'Roaming Yard-wide',     'present' => true],
            ['name' => 'Elikplim Agboba',  'activeRole' => 'CS9 Rotational',        'present' => true],
            ['name' => 'Fifi Nyarko',      'activeRole' => 'CS10 Rotational',       'present' => true],
            ['name' => 'Paa Kwesi',        'activeRole' => 'CS11 Rotational',       'present' => true],
            ['name' => 'Ebo Eshun',        'activeRole' => 'CS12 Rotational',       'present' => true],
        ],
        'C' => [
            ['name' => 'Joseph Agyemang',  'activeRole' => 'Foreman',               'present' => true],
            ['name' => 'Albert Kudjoe',    'activeRole' => 'CS7 Booking (B1-30)',   'present' => true],
            ['name' => 'Desmond Cudjoe',   'activeRole' => 'CS7 Parking (B1-30)',   'present' => true],
            ['name' => 'Gideon Lamptey',   'activeRole' => 'CS8 Booking (B51-80)',  'present' => true],
            ['name' => 'Isaac Bortey',     'activeRole' => 'CS8 Parking (B51-80)', 'present' => true],
            ['name' => 'Nii Armah',        'activeRole' => 'CS6 Holding Area',      'present' => true],
            ['name' => 'Emmanuel Sowah',   'activeRole' => 'Platform 1',            'present' => true],
            ['name' => 'Charles Mensah',   'activeRole' => 'Platform 2',            'present' => true],
            ['name' => 'Derrick Abbey',    'activeRole' => 'Roaming Yard-wide',     'present' => true],
            ['name' => 'Richmond Otu',     'activeRole' => 'CS9 Rotational',        'present' => true],
            ['name' => 'Bernard Quaye',    'activeRole' => 'CS10 Rotational',       'present' => true],
            ['name' => 'Stephen Okai',     'activeRole' => 'CS11 Rotational',       'present' => true],
            ['name' => 'Evans Ashitey',    'activeRole' => 'CS12 Rotational',       'present' => true],
        ],
    ];
}

// â”€â”€ Roles list â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const ROLES = [
    'Foreman',
    'CS6 Holding Area',
    'CS7 Booking (B1-30)',
    'CS7 Parking (B1-30)',
    'CS8 Booking (B51-80)',
    'CS8 Parking (B51-80)',
    'CS9 Rotational',
    'CS10 Rotational',
    'CS11 Rotational',
    'CS12 Rotational',
    'Platform 1',
    'Platform 2',
    'Roaming Yard-wide',
];

// â”€â”€ JSON response helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function jsonOk(mixed $data = []): never {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function jsonErr(string $message, int $code = 400): never {
    http_response_code($code);
    jsonOk(['error' => $message]);
}
