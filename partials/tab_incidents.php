<?php
/**
 * partials/tab_incidents.php
 * Incidents & Reports tab panel
 * No PHP variables required (severity list is hardcoded here)
 */
?>
<div id="tab-incidents" class="tab-panel">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="section-title mb-0">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      Incidents &amp; Reports
    </h5>
    <span class="badge bg-danger" id="incOpenBadge" style="display:none!important"></span>
  </div>

  <!-- Sub-tab switcher -->
  <div class="subtab-bar mb-3">
    <button class="subtab-btn active" id="btnIncReport"  onclick="switchIncTab('report')">New Report</button>
    <button class="subtab-btn"        id="btnIncHistory" onclick="switchIncTab('history')">All Reports</button>
  </div>

  <!-- ── New Report Form ── -->
  <div id="incReportForm" class="plex-card">

    <!-- Photo capture zone -->
    <div class="inc-photo-zone mb-3" id="incPhotoZone"
         onclick="document.getElementById('incPhotoInput').click()">
      <input type="file" id="incPhotoInput" accept="image/*" capture="environment"
             class="d-none" onchange="previewIncPhoto(this)">
      <div id="incPhotoPlaceholder" class="text-center py-2">
        <svg width="30" height="30" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:.4">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <div class="small text-muted mt-1">Tap to capture scene photo</div>
      </div>
      <img id="incPhotoPreviewImg" class="inc-photo-preview d-none" alt="Incident photo">
      <button class="btn btn-sm btn-outline-danger mt-1 d-none w-100" id="removeIncPhoto"
              onclick="event.stopPropagation();clearIncPhoto()">✕ Remove</button>
    </div>

    <form id="incidentForm" enctype="multipart/form-data" onsubmit="submitIncident(event)">
      <div class="row g-3">

        <div class="col-12 col-sm-6">
          <label class="plex-label">Incident Type</label>
          <select name="type" class="form-select plex-input" id="incType">
            <option value="accident">🚗 Accident / Collision</option>
            <option value="injury">🩹 Injury / Medical</option>
            <option value="complaint">📣 Complaint</option>
            <option value="theft">🔒 Theft / Security</option>
            <option value="fire">🔥 Fire / Hazard</option>
            <option value="damage">⚠️ Property Damage</option>
            <option value="other">📋 Other</option>
          </select>
        </div>

        <div class="col-12 col-sm-6">
          <label class="plex-label">Severity</label>
          <div class="severity-row" id="severityRow">
            <?php foreach (['low', 'medium', 'high', 'critical'] as $s): ?>
            <button type="button"
              class="sev-btn sev-<?= $s ?><?= $s === 'medium' ? ' active' : '' ?>"
              data-sev="<?= $s ?>"
              onclick="setSeverity('<?= $s ?>')"
            ><?= ucfirst($s) ?></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="severity" id="incSeverity" value="medium">
        </div>

        <div class="col-12">
          <label class="plex-label">Incident Title *</label>
          <input type="text" name="title" class="form-control plex-input"
            placeholder="Brief description of what happened" required>
        </div>

        <div class="col-12 col-sm-6">
          <label class="plex-label">Location</label>
          <input type="text" name="location" class="form-control plex-input" placeholder="Bay 12, Gate 2…">
        </div>

        <div class="col-12 col-sm-6">
          <label class="plex-label">Plate Involved</label>
          <input type="text" name="plateInvolved" class="form-control plex-input"
            placeholder="GR-1234-24" style="text-transform:uppercase">
        </div>

        <div class="col-12">
          <label class="plex-label">Reported By</label>
          <input type="text" name="reportedBy" class="form-control plex-input"
            placeholder="Your name / marshal ID">
        </div>

        <div class="col-12">
          <label class="plex-label">Full Statement</label>
          <textarea name="statement" class="form-control plex-input" rows="4"
            placeholder="Describe in detail what happened…"></textarea>
        </div>

      </div>

      <button type="submit" class="btn btn-plex w-100 mt-3">
        <span id="incBtnText">📋 Submit Incident Report</span>
        <span id="incSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
      </button>
    </form>
  </div>

  <!-- ── History list rendered by incidents.js ── -->
  <div id="incHistoryList" class="d-none mt-3"></div>

</div>
