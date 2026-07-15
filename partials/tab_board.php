<?php
/**
 * partials/tab_board.php
 * Bay Board tab panel — shows BOTH CS7 and CS8 simultaneously
 * Auto-refreshes every 15 seconds via JS
 * Fully responsive — optimised for mobile phones
 */
?>
<div id="tab-board" class="tab-panel active">

  <!-- ── Traffic counts ── -->
  <div class="counts-bar mb-3" id="countsBar">
    <div class="count-item">
      <div class="count-val text-plex" id="cntIn">—</div>
      <div class="count-lbl">In Today</div>
    </div>
    <div class="count-sep"></div>
    <div class="count-item">
      <div class="count-val text-success" id="cntOut">—</div>
      <div class="count-lbl">Out Today</div>
    </div>
    <div class="count-sep"></div>
    <div class="count-item">
      <div class="count-val text-warning" id="cntYard">—</div>
      <div class="count-lbl">In Yard</div>
    </div>
  </div>

  <!-- ── Bay stats ── -->
  <div class="row g-2 mb-3">
    <div class="col-3">
      <div class="stat-card">
        <div class="stat-val" id="statTotal">—</div>
        <div class="stat-lbl">Total</div>
      </div>
    </div>
    <div class="col-3">
      <div class="stat-card" style="--accent:var(--state-free)">
        <div class="stat-val text-success" id="statFree">—</div>
        <div class="stat-lbl">Free</div>
      </div>
    </div>
    <div class="col-3">
      <div class="stat-card" style="--accent:var(--state-occ)">
        <div class="stat-val text-danger" id="statOcc">—</div>
        <div class="stat-lbl">Occupied</div>
      </div>
    </div>
    <div class="col-3">
      <div class="stat-card" style="--accent:var(--state-pend)">
        <div class="stat-val text-warning" id="statPend">—</div>
        <div class="stat-lbl">Pending</div>
      </div>
    </div>
  </div>

  <!-- ── Legend ── -->
  <div class="bay-legend mb-3">
    <span class="leg-item leg-refresh" id="lastRefreshed">
      <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      Live
    </span>
  </div>

  <!-- ── Operations Quick Overview ── -->
  <div class="plex-card mb-3">
    <h5 class="section-title mb-3">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Operations Overview
    </h5>
    <p class="text-muted small">Welcome to the Piplex Operations Terminal for Meridian Port Services. Use the navigation to manage yard entries and monitor bay statuses.</p>
    
    <div class="row g-2 mt-2">
      <div class="col-12 col-sm-6">
        <button class="btn btn-plex w-100 text-start d-flex align-items-center justify-content-between p-3" onclick="switchTab('bays')">
          <div>
            <div class="fw-bold">View Bays Grid</div>
            <div class="small opacity-75">Check Call Sign 7 &amp; 8 status</div>
          </div>
          <span>→</span>
        </button>
      </div>
      <div class="col-12 col-sm-6">
        <button class="btn btn-plex-outline w-100 text-start d-flex align-items-center justify-content-between p-3" onclick="switchTab('book')">
          <div>
            <div class="fw-bold text-white">Book Truck Entry</div>
            <div class="small text-muted">Register arrival &amp; occupy bay</div>
          </div>
          <span>→</span>
        </button>
      </div>
    </div>
  </div>

</div>
