<?php
/**
 * partials/tab_book.php
 * Book Truck Entry tab panel
 * No PHP variables needed
 */
?>
<div id="tab-book" class="tab-panel">
  <div class="plex-card">

    <h5 class="section-title mb-4">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Book Yard Entry
    </h5>

    <!-- Weighbill capture zone -->
    <div class="scan-zone mb-3">
      <input type="file" id="weighbillInput" accept="image/*" capture="environment" class="d-none">
      <button class="btn btn-plex-outline w-100" onclick="document.getElementById('weighbillInput').click()">
        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-2">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Capture / Upload Weighbill
      </button>
      <div id="weighbillPreview" class="mt-2 d-none">
        <img id="weighbillImg" class="weighbill-preview-img" alt="Weighbill preview">
        <button class="btn btn-sm btn-outline-danger mt-1 w-100" onclick="clearWeighbill()">✕ Remove Image</button>
      </div>
    </div>

    <!-- Booking form -->
    <form id="bookForm">
      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <label class="plex-label">Truck License Plate *</label>
          <input type="text" id="bkPlate" class="form-control plex-input"
            placeholder="GT-8930-23" required style="text-transform:uppercase">
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Delivery No. <span class="text-muted fw-400">(Last 3 digits, e.g., 981 or 981/982) *</span></label>
          <input type="text" id="bkContainer" class="form-control plex-input"
            placeholder="981 or 981/982" maxlength="7" pattern="\d{3}(/\d{3})?" required>
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Allocated Bay *</label>
          <select id="bkBay" class="form-select plex-input" required>
            <option value="">Select Free Bay…</option>
          </select>
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Driver Name *</label>
          <input type="text" id="bkDriver" class="form-control plex-input"
            placeholder="e.g. Kwame Mensah" required>
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Driver Phone *</label>
          <input type="tel" id="bkPhone" class="form-control plex-input"
            placeholder="0553920981" required>
        </div>
        <div class="col-12 col-sm-6">
          <label class="plex-label">Transaction No. <span class="text-muted small fw-400">(optional)</span></label>
          <input type="text" id="bkTxn" class="form-control plex-input"
            placeholder="TXN-20240708-001">
        </div>
        <div class="col-12">
          <label class="plex-label">Notes</label>
          <textarea id="bkNotes" class="form-control plex-input" rows="2"
            placeholder="Custom clearance pending, etc."></textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-plex w-100 mt-4 btn-lg">
        <span id="bookBtnText">Register Arrival &amp; Occupy Bay</span>
        <span id="bookSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
      </button>
    </form>

  </div>
</div>
