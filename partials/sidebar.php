<?php
/**
 * partials/sidebar.php
 * Offcanvas sidebar (desktop: static) + bottom nav (mobile)
 * Expects: $admin (bool), $activeShift (array), $user (array)
 */

$navItems = [
    ['id' => 'board',     'label' => 'Dashboard',
     'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['id' => 'bays',      'label' => 'Bays Grid',
     'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z'],
    ['id' => 'book',      'label' => 'Book Entry',
     'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['id' => 'logs',      'label' => 'Booking Logs',
     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ['id' => 'roster',    'label' => 'Shift Roster',
     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['id' => 'schedule',  'label' => 'Shift Schedule',
     'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['id' => 'incidents', 'label' => 'Incidents',
     'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
];

if ($admin) {
    $navItems[] = [
        'id'    => 'admin',
        'label' => 'Admin',
        'icon'  => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ];
}
?>

<!-- ═══════════════════════════════════════════════════════ SIDEBAR -->
<div class="offcanvas offcanvas-start plex-sidebar" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">

  <!-- Offcanvas close header (mobile only) -->
  <div class="offcanvas-header border-bottom border-dark pb-3">
    <div class="d-flex align-items-center gap-2">
      <svg class="logo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
          d="M12 2v20m0-20L8 6m4-4l4 4M4 12h16m-4 8a4 4 0 11-8 0"/>
      </svg>
      <span class="fw-bold text-white" id="sidebarLabel">Piplex</span>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <!-- Nav links -->
  <div class="offcanvas-body p-0 d-flex flex-column">
    <nav class="sidebar-nav flex-grow-1">
      <?php foreach ($navItems as $item): ?>
      <button
        class="sidebar-nav-btn<?= $item['id'] === 'board' ? ' active' : '' ?>"
        data-tab="<?= $item['id'] ?>"
        onclick="switchTab('<?= $item['id'] ?>')"
      >
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
        </svg>
        <span><?= $item['label'] ?></span>
        <?php if ($item['id'] === 'incidents'): ?>
          <span class="inc-badge d-none" id="incBadgeSidebar"></span>
        <?php endif; ?>
      </button>
      <?php endforeach; ?>
    </nav>

    <!-- User info + sign out -->
    <div class="sidebar-footer">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="avatar-circle">👤</div>
        <div style="min-width:0">
          <div class="fw-600 text-white text-truncate"><?= htmlspecialchars($user['name']) ?></div>
          <div class="text-muted small text-truncate"><?= htmlspecialchars($user['email']) ?></div>
        </div>
      </div>
      <button class="btn btn-danger w-100 btn-sm" onclick="doLogout()">Sign Out</button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════ BOTTOM NAV (mobile) -->
<?php
$bnavItems = [
    ['id' => 'board',     'label' => 'Dash',
     'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['id' => 'bays',      'label' => 'Bays',
     'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z'],
    ['id' => 'book',      'label' => 'Book',
     'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['id' => 'logs',      'label' => 'Logs',
     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ['id' => 'roster',    'label' => 'Roster',
     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['id' => 'incidents', 'label' => 'Alerts',
     'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
];
?>
<nav class="bottom-nav d-md-none">
  <?php foreach ($bnavItems as $n): ?>
  <button
    class="bnav-btn<?= $n['id'] === 'board' ? ' active' : '' ?>"
    data-tab="<?= $n['id'] ?>"
    onclick="switchTab('<?= $n['id'] ?>')"
  >
    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $n['icon'] ?>"/>
    </svg>
    <span><?= $n['label'] ?></span>
    <?php if ($n['id'] === 'incidents'): ?>
      <span class="bnav-badge d-none" id="incBadgeNav"></span>
    <?php endif; ?>
  </button>
  <?php endforeach; ?>
</nav>
