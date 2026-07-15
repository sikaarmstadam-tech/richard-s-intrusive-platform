<?php
/**
 * partials/modals.php
 * Bay detail modal, Booking receipt modal, Toast notification
 * No PHP variables needed
 */
?>

<!-- ═══════════════════════════════════════════════ BAY DETAIL MODAL -->
<div class="modal fade" id="bayModal" tabindex="-1" aria-labelledby="bayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content plex-modal">
      <div class="modal-header border-0">
        <h5 class="modal-title text-white" id="bayModalTitle">Bay —</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="bayModalBody"></div>
      <div class="modal-footer border-0 d-flex gap-2" id="bayModalFooter"></div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════ BOOKING RECEIPT MODAL -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content plex-modal text-center">
      <div class="modal-body p-4">
        <div class="receipt-check" id="receiptCheck">✓</div>
        <h5 class="text-white mt-3" id="receiptTitle"></h5>
        <p class="text-muted small" id="receiptSub"></p>
        <hr class="border-secondary">
        <div id="receiptBody" class="text-start"></div>
        <button class="btn btn-plex mt-3 w-100" data-bs-dismiss="modal">Acknowledge</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ TOAST -->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:9999">
  <div id="plexToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <span id="toastIcon"></span>
        <span id="toastMsg"></span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
