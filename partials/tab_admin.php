<?php
/**
 * partials/tab_admin.php
 * Admin tab — create accounts + view all users
 * Expects: $user (array), $admin (bool)
 * Only included when $admin === true
 */
?>
<div id="tab-admin" class="tab-panel">

  <!-- Create Account card -->
  <div class="plex-card mb-3">
    <h5 class="section-title mb-4">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
      </svg>
      Create Account
    </h5>

    <div id="userFormMsg" class="d-none mb-3"></div>

    <form id="createUserForm" onsubmit="createUser(event)">
      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <label class="plex-label">Full Name</label>
          <input type="text" id="nuName" class="form-control plex-input"
            placeholder="Marshal's full name" required>
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Email Address</label>
          <input type="email" id="nuEmail" class="form-control plex-input"
            placeholder="marshal@gmail.com" required>
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Password <span class="text-muted small fw-400">(min. 8 chars)</span></label>
          <input type="password" id="nuPass" class="form-control plex-input"
            placeholder="Secure password" required minlength="8">
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Role</label>
          <select id="nuRole" class="form-select plex-input">
            <option value="marshal">Marshal</option>
            <?php if ($user['role'] === 'superadmin'): ?>
            <option value="admin">Admin</option>
            <?php endif; ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-plex mt-3 w-100" id="createUserBtn">
        + Create Account
      </button>
    </form>
  </div>

  <!-- All Accounts card -->
  <div class="plex-card">
    <h5 class="section-title mb-3">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
      All Accounts
    </h5>

    <div id="userList">
      <div class="text-muted small text-center py-2">Loading…</div>
    </div>

    <div class="security-note mt-3">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04
             A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622
             0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      One active session per user. Logging in elsewhere immediately terminates the previous session.
    </div>
  </div>

</div>
