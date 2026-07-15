<?php
/**
 * index.php â€” Piplex Operations Entry Point
 * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * Tech stack: PHP Â· HTML Â· CSS Â· JavaScript Â· Bootstrap 5
 *
 * Routes:
 *   - Not logged in â†’ Login page
 *   - Logged in     â†’ Full dashboard (partials assembled below)
 */
require_once __DIR__ . '/config.php';

$activeShift = getActiveGroup();
$today       = date('Y-m-d');
$user        = currentUser();
$admin       = isAdmin();

require_once __DIR__ . '/partials/header.php';
?>

<?php if (!isLoggedIn()): ?>
<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• LOGIN PAGE -->
<div class="login-page d-flex align-items-center justify-content-center min-vh-100">
  <div class="login-card">

    <div class="login-logo mb-4">
      <svg class="logo-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
          d="M12 2v20m0-20L8 6m4-4l4 4M4 12h16m-4 8a4 4 0 11-8 0"/>
      </svg>
      <span>Piplex</span>
    </div>

    <h1 class="login-title">Operations Terminal</h1>
    <p class="login-sub">Meridian Port Services Â· Tema, Ghana</p>

    <div id="loginError" class="alert alert-danger d-none mt-3" role="alert"></div>

    <form id="loginForm" class="mt-4">
      <div class="mb-3">
        <label class="form-label text-muted small">Email Address</label>
        <input type="email" id="loginEmail" class="form-control form-control-lg plex-input"
          placeholder="your@email.com" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label text-muted small">Password</label>
        <input type="password" id="loginPass" class="form-control form-control-lg plex-input"
          placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
      </div>
      <button type="submit" id="loginBtn" class="btn btn-plex w-100 btn-lg">
        <span id="loginBtnText">Sign In to Operations</span>
        <span id="loginSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
      </button>
    </form>

    <p class="mt-4 text-center text-muted" style="font-size:.75rem">
      Protected system â€” authorised personnel only
    </p>
  </div>
</div>

<?php else: ?>
<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• DASHBOARD -->

<?php require_once __DIR__ . '/partials/navbar.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<main class="plex-main" id="mainContent">
  <?php require_once __DIR__ . '/partials/tab_board.php';     ?>
  <?php require_once __DIR__ . '/partials/tab_bays.php';      ?>
  <?php require_once __DIR__ . '/partials/tab_book.php';      ?>
  <?php require_once __DIR__ . '/partials/tab_logs.php';      ?>
  <?php require_once __DIR__ . '/partials/tab_roster.php';    ?>
  <?php require_once __DIR__ . '/partials/tab_schedule.php';  ?>
  <?php require_once __DIR__ . '/partials/tab_incidents.php'; ?>
  <?php if ($admin): require_once __DIR__ . '/partials/tab_admin.php'; endif; ?>
</main>

<?php require_once __DIR__ . '/partials/modals.php'; ?>

<?php endif; /* end isLoggedIn */ ?>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• SCRIPTS -->

<!-- Bootstrap 5 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isLoggedIn()): ?>
<!-- Pass PHP data to JavaScript via window globals -->
<script>
  window.PIPLEX_TODAY       = <?= json_encode($today) ?>;
  window.PIPLEX_SHIFT       = <?= json_encode($activeShift['shift']) ?>;
  window.PIPLEX_ROSTER_GROUP = <?= json_encode($activeShift['group']) ?>;
  window.PIPLEX_USER_EMAIL  = <?= json_encode($user['email']) ?>;
  window.PIPLEX_ROLES       = <?= json_encode(ROLES) ?>;
</script>

<!-- Dashboard JS modules (order matters: app first, then features) -->
<script src="assets/js/app.js"></script>
<script src="assets/js/bays.js"></script>
<script src="assets/js/bookings.js"></script>
<script src="assets/js/roster.js"></script>
<script src="assets/js/incidents.js"></script>
<script src="assets/js/admin.js"></script>

<?php else: ?>
<!-- Login page JS (inline â€” small enough to keep here) -->
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    var errEl  = document.getElementById('loginError');
    var btn    = document.getElementById('loginBtn');
    errEl.classList.add('d-none');
    btn.disabled = true;
    document.getElementById('loginSpinner').classList.remove('d-none');
    document.getElementById('loginBtnText').textContent = 'Signing inâ€¦';

    var res = await fetch('api.php?action=login', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
            email:    document.getElementById('loginEmail').value.trim().toLowerCase(),
            password: document.getElementById('loginPass').value
        })
    });
    var d = await res.json();

    btn.disabled = false;
    document.getElementById('loginSpinner').classList.add('d-none');
    document.getElementById('loginBtnText').textContent = 'Sign In to Operations';

    if (d.error) {
        errEl.textContent = d.error;
        errEl.classList.remove('d-none');
    } else {
        location.reload();
    }
});
</script>
<?php endif; ?>

</body>
</html>
