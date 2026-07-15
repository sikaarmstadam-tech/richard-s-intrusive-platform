/* =============================================================
   app.js — Global state, helpers, tab routing, init
   ============================================================= */

// ── Global State ──────────────────────────────────────────────
var bays             = {};
var bookings         = [];
var historyBookings  = [];
var incidents        = [];
var roster           = {};
var activeTab        = 'board';
var logSubTab        = 'active';
var incSubTab        = 'report';
var selectedCS       = 7;
var rosterGroup      = window.PIPLEX_ROSTER_GROUP || 'A';

// Bootstrap instances (set after DOM ready)
var bayModal;
var receiptModal;
var bsToast;

// ── API Helper ────────────────────────────────────────────────
// action  = the ?action= value
// opts    = fetch options (method, headers, body)
// params  = extra URL query params e.g. { status: 'completed' }
async function api(action, opts, params) {
    var url = 'api.php?action=' + action;
    if (params) {
        Object.entries(params).forEach(function(kv) {
            url += '&' + encodeURIComponent(kv[0]) + '=' + encodeURIComponent(kv[1]);
        });
    }
    try {
        var res = await fetch(url, opts || {});
        return res.json();
    } catch (e) {
        return { error: 'Network error' };
    }
}

// ── Toast ─────────────────────────────────────────────────────
function toast(msg, type) {
    type = type || 'info';
    var icons  = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    var colors = { success: 'bg-success', error: 'bg-danger', warning: 'bg-warning text-dark', info: 'bg-primary' };
    var el = document.getElementById('plexToast');
    el.className = 'toast align-items-center border-0 ' + (colors[type] || 'bg-primary');
    document.getElementById('toastIcon').textContent = icons[type] || 'ℹ️';
    document.getElementById('toastMsg').textContent  = msg;
    bsToast.show();
}

// ── Tab Navigation ────────────────────────────────────────────
function switchTab(id) {
    activeTab = id;

    // Show correct panel
    document.querySelectorAll('.tab-panel').forEach(function(p) {
        p.classList.remove('active');
    });
    var panel = document.getElementById('tab-' + id);
    if (panel) panel.classList.add('active');

    // Highlight nav buttons
    document.querySelectorAll('.bnav-btn, .sidebar-nav-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.tab === id);
    });

    // Close sidebar offcanvas on mobile
    var oc = bootstrap.Offcanvas.getInstance(document.getElementById('sidebar'));
    if (oc) oc.hide();

    // Load data for the selected tab
    if (id === 'board' || id === 'bays') {
        loadBays();
        startBayRefresh();
    } else {
        stopBayRefresh(); // pause refresh when not on board or bays tab
        if      (id === 'logs')      { loadBookings(); }
        else if (id === 'roster')    { loadRoster(); }
        else if (id === 'incidents') { loadIncidents(); switchIncTab('report'); }
        else if (id === 'admin')     { loadUsers(); }
    }
}

// ── Traffic Counts ────────────────────────────────────────────
async function loadCounts() {
    var d = await api('counts');
    if (d.error) return;
    document.getElementById('cntIn').textContent    = d.inToday   != null ? d.inToday   : '—';
    document.getElementById('cntOut').textContent   = d.outToday  != null ? d.outToday  : '—';
    document.getElementById('cntYard').textContent  = d.inYard    != null ? d.inYard    : '—';
}

// ── Logout ────────────────────────────────────────────────────
async function doLogout() {
    await api('logout');
    location.reload();
}

// ── Sidebar toggle (mobile) ───────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            var oc = new bootstrap.Offcanvas(document.getElementById('sidebar'));
            oc.show();
        });
    }

    // Init Bootstrap components
    bayModal     = new bootstrap.Modal(document.getElementById('bayModal'));
    receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    bsToast      = new bootstrap.Toast(document.getElementById('plexToast'), { delay: 3500 });

    // Boot the dashboard
    loadBays();
    loadCounts();
    loadBookings();
    loadIncidents();

    // Start auto-refresh for bay board (15 seconds, pauses on other tabs)
    startBayRefresh();
});
