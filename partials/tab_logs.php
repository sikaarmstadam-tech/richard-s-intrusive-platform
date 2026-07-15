<?php
/**
 * partials/tab_logs.php
 * Booking Logs tab — active + history sub-tabs
 * No PHP variables needed
 */
?>
<div id="tab-logs" class="tab-panel">

  <!-- Sub-tab switcher -->
  <div class="subtab-bar mb-3">
    <button class="subtab-btn active" id="btnActive" onclick="switchLogTab('active')">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      Active (<span id="activeCount">0</span>)
    </button>
    <button class="subtab-btn" id="btnHistory" onclick="switchLogTab('history')">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      History
    </button>
  </div>

  <!-- Search bar -->
  <div class="plex-search-wrap mb-3">
    <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <input
      type="text"
      id="logSearch"
      class="plex-search"
      placeholder="Search plate, delivery no., bay…"
      oninput="filterLogs()"
    >
  </div>

  <!-- Log cards rendered by bookings.js -->
  <div id="logsList"></div>

</div>
