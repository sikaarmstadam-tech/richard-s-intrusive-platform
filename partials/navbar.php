<?php
/**
 * partials/navbar.php
 * Fixed top header bar
 * Expects: $activeShift (array)
 */
?>
<header class="plex-header d-flex align-items-center px-3 px-md-4">

  <!-- Left: hamburger + logo -->
  <div class="d-flex align-items-center gap-3 flex-grow-1">
    <button class="hamburger-btn d-md-none" id="sidebarToggle" aria-label="Open menu">
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <div class="d-flex align-items-center gap-2">
      <svg class="logo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
          d="M12 2v20m0-20L8 6m4-4l4 4M4 12h16m-4 8a4 4 0 11-8 0"/>
      </svg>
      <span class="fw-bold text-white" style="font-size:1.05rem">Piplex Operations</span>
    </div>
  </div>

  <!-- Right: live pill + shift + logout -->
  <div class="d-flex align-items-center gap-2">

    <span class="sync-pill online" id="syncPill">
      <span class="sync-dot"></span>
      <span class="sync-text">Live</span>
    </span>

    <span class="shift-pill">
      Grp <?= htmlspecialchars($activeShift['group']) ?> · <?= htmlspecialchars($activeShift['shift']) ?>
    </span>

    <!-- Hidden logout form (kept for accessibility fallback) -->
    <form method="post" action="api.php?action=logout" id="logoutForm" class="d-none"></form>

    <button
      class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1"
      onclick="doLogout()"
    >
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Logout
    </button>

  </div>
</header>
