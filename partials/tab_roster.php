<?php
/**
 * partials/tab_roster.php
 * Shift Roster tab panel
 * Expects: $activeShift (array), $admin (bool)
 */
?>
<div id="tab-roster" class="tab-panel">
  <div class="plex-card">

    <!-- Header row -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="section-title mb-0">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Shift Roster
      </h5>
      <span class="badge bg-plex">
        <?= htmlspecialchars("Group {$activeShift['group']} · {$activeShift['shift']}") ?>
      </span>
    </div>

    <!-- Group A / B / C selector -->
    <div class="seg-ctrl mb-3">
      <?php foreach (['A', 'B', 'C'] as $g): ?>
      <button
        class="seg-btn<?= $g === $activeShift['group'] ? ' active' : '' ?>"
        onclick="selectRosterGroup('<?= $g ?>')"
      ><?= $g ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Present count -->
    <div class="slot-header mb-3">
      <span class="slot-count" id="slotCount">0 / 13 present</span>
      <span class="slot-remaining text-muted small" id="slotRemaining"></span>
    </div>

    <!-- Add marshal form -->
    <div id="addMarshalForm" class="roster-add-form mb-3">
      <div class="row g-2">
        <div class="col-12 col-sm-5">
          <input
            type="text"
            id="newMarshalName"
            class="form-control plex-input"
            placeholder="Marshal name…"
            onkeydown="if(event.key==='Enter') addMarshal()"
          >
        </div>
        <div class="col-12 col-sm-5">
          <select id="newMarshalRole" class="form-select plex-input">
            <?php foreach (ROLES as $r): ?>
            <option><?= htmlspecialchars($r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-sm-2">
          <button class="btn btn-plex w-100" onclick="addMarshal()">+ Add</button>
        </div>
      </div>
    </div>

    <!-- Roster rows rendered by roster.js -->
    <div id="rosterList" class="roster-list"></div>

    <!-- Submit / Print -->
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-plex flex-grow-1" onclick="printRoster()">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-2">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Submit / Print Roster
      </button>
    </div>

  </div>
</div>
