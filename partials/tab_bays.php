<?php
/**
 * partials/tab_bays.php
 * Dedicated Bay Grid page — CS7 (Bays 1-30) and CS8 (Bays 51-80)
 * Full-screen, auto-refreshing, mobile-optimised
 */
?>
<div id="tab-bays" class="tab-panel">

  <!-- Header row -->
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
      <h5 class="section-title mb-0">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6z
               M14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z
               M4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4z
               M14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
        </svg>
        Bay Grid
      </h5>
      <p class="text-muted small mb-0 mt-1">Tap any bay to view details or manage status</p>
    </div>
    <!-- Live refresh indicator -->
    <div class="d-flex align-items-center gap-2">
      <span class="sync-pill online">
        <span class="sync-dot"></span>
        <span id="bayLastUpdate" class="sync-text">Live</span>
      </span>
      <button class="btn btn-sm btn-plex-outline" onclick="loadBays()" title="Refresh now">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Stats strip -->
  <div class="bays-stats-strip mb-3">
    <div class="bstrip-item">
      <span class="bstrip-val" id="bsTotal">—</span>
      <span class="bstrip-lbl">Total</span>
    </div>
    <div class="bstrip-sep"></div>
    <div class="bstrip-item">
      <span class="bstrip-val" style="color:var(--state-free)" id="bsFree">—</span>
      <span class="bstrip-lbl">Free</span>
    </div>
    <div class="bstrip-sep"></div>
    <div class="bstrip-item">
      <span class="bstrip-val" style="color:var(--state-occ)" id="bsOcc">—</span>
      <span class="bstrip-lbl">Occupied</span>
    </div>
    <div class="bstrip-sep"></div>
    <div class="bstrip-item">
      <span class="bstrip-val" style="color:var(--state-pend)" id="bsPend">—</span>
      <span class="bstrip-lbl">Pending</span>
    </div>
  </div>

  <!-- Legend -->
  <div class="bay-legend mb-3">
    <span class="leg-item"><span class="leg-dot" style="background:var(--state-free)"></span>Free</span>
    <span class="leg-item"><span class="leg-dot" style="background:var(--state-occ)"></span>Occupied</span>
    <span class="leg-item"><span class="leg-dot" style="background:var(--state-pend)"></span>Pending</span>
  </div>

  <!-- ═══ CALL SIGN 7 — Bays 1-30 ═══ -->
  <div class="plex-card mb-3">
    <div class="bay-section-header mb-3">
      <div class="bay-section-title">
        <span class="bay-cs-pill cs7">CS 7</span>
        Call Sign 7 &mdash; Bays 1–30
      </div>
      <div class="cs-mini-stats">
        <span class="cs-mini-free" id="cs7Free">— Free</span>
      </div>
    </div>
    <div class="bay-grid" id="bayGridCS7">
      <div class="text-center text-muted py-3 w-100">
        <div class="spinner-border spinner-border-sm text-plex" role="status"></div>
        <div class="mt-1 small">Loading bays…</div>
      </div>
    </div>
  </div>

  <!-- ═══ CALL SIGN 8 — Bays 51-80 ═══ -->
  <div class="plex-card mb-4">
    <div class="bay-section-header mb-3">
      <div class="bay-section-title">
        <span class="bay-cs-pill cs8">CS 8</span>
        Call Sign 8 &mdash; Bays 51–80
      </div>
      <div class="cs-mini-stats">
        <span class="cs-mini-free" id="cs8Free">— Free</span>
      </div>
    </div>
    <div class="bay-grid" id="bayGridCS8">
      <div class="text-center text-muted py-3 w-100">
        <div class="spinner-border spinner-border-sm text-plex" role="status"></div>
        <div class="mt-1 small">Loading bays…</div>
      </div>
    </div>
  </div>

</div>
