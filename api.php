<?php
/**
 * api.php â€” Piplex REST API
 * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * All AJAX endpoints. Called via fetch() from the front-end JS.
 * Every action requires authentication (chkAuth).
 * Admin-only actions also require chkAdmin.
 *
 * Usage: api.php?action=<action>
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$db  = getDb();
$act = $_GET['action'] ?? $_POST['action'] ?? '';
$inp = json_decode(file_get_contents('php://input'), true) ?? [];

// â”€â”€ Auth guards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function chkAuth(): void  { if (!isLoggedIn()) jsonErr('Unauthorized', 401); }
function chkAdmin(): void { if (!isAdmin())     jsonErr('Admin required', 403); }

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ROUTING
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
switch ($act) {

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// AUTH
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'login':
    $email = strtolower(trim($inp['email'] ?? ''));
    $pass  = $inp['password'] ?? '';
    if (!$email || !$pass) jsonErr('Email and password required');

    $st = $db->prepare("SELECT * FROM users WHERE email=?");
    $st->execute([$email]);
    $u = $st->fetch();

    if (!$u || !password_verify($pass, $u['passwordHash'])) jsonErr('Invalid credentials', 401);

    $_SESSION['piplex_user'] = [
        'id'    => $u['id'],
        'name'  => $u['name'],
        'email' => $u['email'],
        'role'  => $u['role'],
    ];
    jsonOk(['ok' => true, 'user' => $_SESSION['piplex_user']]);
    break;

case 'logout':
    session_destroy();
    jsonOk(['ok' => true]);
    break;

case 'verify':
    chkAuth();
    jsonOk(['ok' => true, 'user' => currentUser()]);
    break;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// TRAFFIC COUNTS
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'counts':
    chkAuth();
    $inToday  = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE date(checkInTime)=date('now','localtime')")->fetchColumn();
    $outToday = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE date(checkOutTime)=date('now','localtime')")->fetchColumn();
    $inYard   = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE checkInTime IS NOT NULL AND checkOutTime IS NULL AND status!='completed'")->fetchColumn();
    $cs7      = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE checkInTime IS NOT NULL AND checkOutTime IS NULL AND status!='completed' AND bayNum<=30")->fetchColumn();
    $cs8      = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE checkInTime IS NOT NULL AND checkOutTime IS NULL AND status!='completed' AND bayNum>=51")->fetchColumn();
    jsonOk(['inToday' => $inToday, 'outToday' => $outToday, 'inYard' => $inYard, 'cs7' => $cs7, 'cs8' => $cs8]);
    break;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// BAYS
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'bays':
    chkAuth();
    $rows = $db->query("
        SELECT b.bayNum, b.status, b.bookingId,
               bk.plate, bk.container, bk.phone, bk.driverName,
               bk.transactionNumber, bk.checkInTime, bk.notes, bk.callSign
        FROM bays b
        LEFT JOIN bookings bk ON b.bookingId = bk.id
    ")->fetchAll();
    $map = [];
    foreach ($rows as $r) $map[$r['bayNum']] = $r;
    jsonOk($map);
    break;

case 'update_bay':
    chkAuth();
    $bayNum = (int)($inp['bayNum'] ?? 0);
    $status = $inp['status'] ?? 'free';
    if (!$bayNum) jsonErr('Invalid bay');

    if ($status === 'free') {
        // Find and close the active booking for this bay
        $st = $db->prepare("SELECT id, checkInTime FROM bookings WHERE bayNum=? AND checkOutTime IS NULL AND status!='completed' ORDER BY id DESC LIMIT 1");
        $st->execute([$bayNum]);
        $bk = $st->fetch();
        if ($bk) {
            $co  = date('Y-m-d H:i:s');
            $dur = $bk['checkInTime'] ? (int) round((time() - strtotime($bk['checkInTime'])) / 60) : null;
            $db->prepare("UPDATE bookings SET checkOutTime=?, durationMinutes=?, status='completed' WHERE id=?")
               ->execute([$co, $dur, $bk['id']]);
        }
        $db->prepare("INSERT INTO bays (bayNum,status,bookingId) VALUES (?,?,NULL) ON CONFLICT(bayNum) DO UPDATE SET status='free',bookingId=NULL")
           ->execute([$bayNum]);
    } else {
        $bookingId = $inp['bookingId'] ?? null;
        $db->prepare("INSERT INTO bays (bayNum,status,bookingId) VALUES (?,?,?) ON CONFLICT(bayNum) DO UPDATE SET status=excluded.status,bookingId=excluded.bookingId")
           ->execute([$bayNum, $status, $bookingId]);
    }
    jsonOk(['ok' => true]);
    break;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// BOOKINGS
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'bookings':
    chkAuth();
    if (($_GET['status'] ?? '') === 'completed') {
        $rows = $db->query("SELECT * FROM bookings WHERE status='completed' ORDER BY checkOutTime DESC LIMIT 200")->fetchAll();
    } else {
        $rows = $db->query("SELECT * FROM bookings WHERE status!='completed' ORDER BY checkInTime DESC")->fetchAll();
    }
    jsonOk($rows);
    break;

case 'create_booking':
    chkAuth();
    $plate  = strtoupper(trim($inp['plate']             ?? ''));
    $cont   = trim($inp['container']                    ?? '');
    $bayNum = (int)($inp['bayNum']                      ?? 0);
    $phone  = trim($inp['phone']                        ?? '');
    $driver = trim($inp['driverName']                   ?? '');
    $txn    = trim($inp['transactionNumber']            ?? '');
    $notes  = trim($inp['notes']                        ?? '');

    if (!$plate || !$cont || !$bayNum || !$phone || !$driver) jsonErr('Missing required fields');
    if (!preg_match('/^\d{3}(\/\d{3})?$/', $cont)) jsonErr('Delivery No. must be 3 digits (e.g. 981) or 3/3 digits (e.g. 981/982)');

    // Confirm bay is free
    $st = $db->prepare("SELECT status FROM bays WHERE bayNum=?");
    $st->execute([$bayNum]);
    $bay = $st->fetch();
    if ($bay && $bay['status'] !== 'free') jsonErr("Bay $bayNum is currently {$bay['status']}");

    $cs  = $bayNum <= 30 ? 'Call Sign 7' : 'Call Sign 8';
    $now = date('Y-m-d H:i:s');

    $db->prepare("INSERT INTO bookings (plate,container,bayNum,phone,driverName,transactionNumber,callSign,notes,checkInTime,status) VALUES (?,?,?,?,?,?,?,?,?,'active')")
       ->execute([$plate, $cont, $bayNum, $phone, $driver, $txn, $cs, $notes, $now]);
    $id = (int) $db->lastInsertId();

    $db->prepare("INSERT INTO bays (bayNum,status,bookingId) VALUES (?,?,?) ON CONFLICT(bayNum) DO UPDATE SET status='incoming',bookingId=excluded.bookingId")
       ->execute([$bayNum, 'incoming', $id]);

    jsonOk(['id' => $id, 'checkInTime' => $now, 'callSign' => $cs]);
    break;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ROSTER
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'roster':
    chkAuth();
    $date = $_GET['date'] ?? date('Y-m-d');
    $st   = $db->prepare("SELECT * FROM rosters WHERE date=? ORDER BY groupName, id");
    $st->execute([$date]);
    $all  = $st->fetchAll();
    $map  = ['A' => [], 'B' => [], 'C' => []];
    foreach ($all as $r) {
        if (isset($map[$r['groupName']])) {
            $map[$r['groupName']][] = [
                'id'         => $r['id'],
                'name'       => $r['name'],
                'activeRole' => $r['activeRole'],
                'present'    => (bool) $r['present'],
            ];
        }
    }
    // Return defaults if no roster saved yet for today
    $hasAny = array_filter($map);
    jsonOk($hasAny ? $map : getDefaultRosters());
    break;

case 'sync_roster':
    chkAuth();
    $date    = $inp['date']    ?? date('Y-m-d');
    $rosters = $inp['rosters'] ?? [];

    $db->prepare("DELETE FROM rosters WHERE date=?")->execute([$date]);

    $ins = $db->prepare("INSERT INTO rosters (groupName,name,activeRole,present,date) VALUES (?,?,?,?,?)");
    foreach (['A', 'B', 'C'] as $g) {
        foreach (($rosters[$g] ?? []) as $m) {
            $ins->execute([$g, $m['name'], $m['activeRole'], $m['present'] ? 1 : 0, $date]);
        }
    }
    jsonOk(['ok' => true]);
    break;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// INCIDENTS
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'incidents':
    chkAuth();
    if ($_GET['status'] ?? '') {
        $st = $db->prepare("SELECT * FROM incidents WHERE status=? ORDER BY timestamp DESC");
        $st->execute([$_GET['status']]);
        $rows = $st->fetchAll();
    } else {
        $rows = $db->query("SELECT * FROM incidents ORDER BY timestamp DESC LIMIT 300")->fetchAll();
    }
    jsonOk($rows);
    break;

case 'create_incident':
    chkAuth();
    $type  = $_POST['type']          ?? 'other';
    $sev   = $_POST['severity']      ?? 'medium';
    $title = trim($_POST['title']    ?? '');
    $stmt  = trim($_POST['statement']    ?? '');
    $loc   = trim($_POST['location']     ?? '');
    $rep   = trim($_POST['reportedBy']   ?? '');
    $plt   = strtoupper(trim($_POST['plateInvolved'] ?? ''));
    if (!$title) jsonErr('Title required');

    $imgRef = null;
    if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === 0) {
        $upDir = __DIR__ . '/uploads/';
        if (!is_dir($upDir)) mkdir($upDir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) jsonErr('Invalid file type');
        $fn = 'inc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $upDir . $fn);
        $imgRef = 'uploads/' . $fn;
    }

    $db->prepare("INSERT INTO incidents (type,severity,title,statement,location,reportedBy,plateInvolved,imageRef) VALUES (?,?,?,?,?,?,?,?)")
       ->execute([$type, $sev, $title, $stmt, $loc, $rep, $plt, $imgRef]);

    jsonOk(['id' => (int) $db->lastInsertId()]);
    break;

case 'resolve_incident':
    chkAuth();
    $id = (int)($inp['id'] ?? 0);
    if (!$id) jsonErr('Invalid id');
    $db->prepare("UPDATE incidents SET status='resolved' WHERE id=?")->execute([$id]);
    jsonOk(['ok' => true]);
    break;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ADMIN â€” USER MANAGEMENT
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
case 'users':
    chkAuth(); chkAdmin();
    jsonOk($db->query("SELECT id, email, name, role, createdAt FROM users ORDER BY createdAt DESC")->fetchAll());
    break;

case 'create_user':
    chkAuth(); chkAdmin();
    $email = strtolower(trim($inp['email']    ?? ''));
    $name  = trim($inp['name']                ?? '');
    $pass  = $inp['password']                 ?? '';
    $role  = $inp['role']                     ?? 'marshal';

    if (!$email || !$name || !$pass) jsonErr('All fields required');
    if (strlen($pass) < 8) jsonErr('Password must be at least 8 characters');

    $allowed = currentUser()['role'] === 'superadmin' ? ['marshal', 'admin'] : ['marshal'];
    if (!in_array($role, $allowed)) $role = 'marshal';

    $st = $db->prepare("SELECT id FROM users WHERE email=?");
    $st->execute([$email]);
    if ($st->fetch()) jsonErr('Email already exists', 409);

    $db->prepare("INSERT INTO users (email,passwordHash,name,role) VALUES (?,?,?,?)")
       ->execute([$email, password_hash($pass, PASSWORD_BCRYPT), $name, $role]);

    jsonOk(['ok' => true, 'id' => (int) $db->lastInsertId()]);
    break;

case 'delete_user':
    chkAuth(); chkAdmin();
    $id = (int)($inp['id'] ?? 0);
    if ($id === (currentUser()['id'] ?? 0)) jsonErr('Cannot delete your own account');
    $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    jsonOk(['ok' => true]);
    break;

default:
    jsonErr('Unknown action', 404);
}
