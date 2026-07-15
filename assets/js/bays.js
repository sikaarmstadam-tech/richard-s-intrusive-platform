/* =============================================================
   bays.js — Bay board, bay modal, stats
   Shows BOTH CS7 (1-30) and CS8 (51-80) simultaneously.
   Auto-refreshes every 15 seconds.
   ============================================================= */

var _bayRefreshTimer = null;

// ── Load & Render All Bays ────────────────────────────────────
async function loadBays() {
    var data = await api('bays');
    if (data.error) { toast('Failed to load bays', 'error'); return; }
    bays = data;
    renderBays();
    updateStats();
    populateBayDropdown();
    // Update live timestamp
    var now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    var el = document.getElementById('lastRefreshed');
    if (el) {
        el.innerHTML =
            '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"' +
            ' d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0' +
            'a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> ' + now;
    }
    var el2 = document.getElementById('bayLastUpdate');
    if (el2) {
        el2.textContent = 'Live · ' + now;
    }
}

// ── Render a single grid (CS7 or CS8) ────────────────────────
function renderGrid(gridId, bayNums) {
    var grid = document.getElementById(gridId);
    if (!grid) return;
    grid.innerHTML = '';
    bayNums.forEach(function(num) {
        var bay = bays[num] || { status: 'free' };
        var st  = bay.status || 'free';

        // Label: show plate if occupied, otherwise status text
        var lbl;
        if (st === 'free') {
            lbl = 'FREE';
        } else if (st === 'occupied' || st === 'incoming') {
            lbl = bay.plate ? bay.plate.substring(0, 8) : 'OCC';
        } else {
            lbl = 'PEND';
        }

        var el = document.createElement('div');
        el.className = 'bay-cell bay-' + st;
        el.setAttribute('role', 'button');
        el.setAttribute('aria-label', 'Bay ' + num + ' — ' + st);
        el.innerHTML =
            '<div class="bay-num">' + num + '</div>' +
            '<div class="bay-lbl">' + lbl + '</div>';
        el.onclick = function() { openBayModal(num); };
        grid.appendChild(el);
    });
}

// ── Render BOTH grids ─────────────────────────────────────────
function renderBays() {
    var cs7 = Array.from({ length: 30 }, function(_, i) { return i + 1; });
    var cs8 = Array.from({ length: 30 }, function(_, i) { return i + 51; });
    renderGrid('bayGridCS7', cs7);
    renderGrid('bayGridCS8', cs8);
}

// ── Bay stats cards ───────────────────────────────────────────
function updateStats() {
    var free = 0, occ = 0, pend = 0, total = 0;
    var cs7Free = 0, cs8Free = 0;

    Object.entries(bays).forEach(function(entry) {
        var num = parseInt(entry[0]);
        var b = entry[1];
        total++;
        if (b.status === 'free') {
            free++;
            if (num <= 30) cs7Free++;
            else           cs8Free++;
        }
        else if (b.status === 'occupied' || b.status === 'incoming') occ++;
        else if (b.status === 'pending')                             pend++;
    });

    // Update main dashboard stats (if active)
    var statTotal = document.getElementById('statTotal');
    if (statTotal) statTotal.textContent = total;
    var statFree = document.getElementById('statFree');
    if (statFree) statFree.textContent = free;
    var statOcc = document.getElementById('statOcc');
    if (statOcc) statOcc.textContent = occ;
    var statPend = document.getElementById('statPend');
    if (statPend) statPend.textContent = pend;

    // Update dedicated Bay Grid page stats
    var bsTotal = document.getElementById('bsTotal');
    if (bsTotal) bsTotal.textContent = total;
    var bsFree = document.getElementById('bsFree');
    if (bsFree) bsFree.textContent = free;
    var bsOcc = document.getElementById('bsOcc');
    if (bsOcc) bsOcc.textContent = occ;
    var bsPend = document.getElementById('bsPend');
    if (bsPend) bsPend.textContent = pend;

    // Update Call Sign group free badges
    var elCs7 = document.getElementById('cs7Free');
    if (elCs7) elCs7.textContent = cs7Free + ' Free';
    var elCs8 = document.getElementById('cs8Free');
    if (elCs8) elCs8.textContent = cs8Free + ' Free';
}

// ── Open bay detail modal ─────────────────────────────────────
function openBayModal(num) {
    var bay     = bays[num] || { status: 'free' };
    var st      = bay.status || 'free';
    var stColor = st === 'free' ? '#22c55e' : st === 'pending' ? '#f59e0b' : '#ef4444';
    var cs      = num <= 30 ? 'Call Sign 7' : 'Call Sign 8';

    document.getElementById('bayModalTitle').textContent = 'Bay ' + num + ' · ' + cs;

    var body =
        '<div class="detail-row">' +
            '<span class="detail-lbl">Status</span>' +
            '<span class="detail-val" style="color:' + stColor + ';text-transform:uppercase;font-weight:700">' + st + '</span>' +
        '</div>';

    if (st !== 'free' && bay.plate) {
        body +=
            '<div class="detail-row"><span class="detail-lbl">Truck Plate</span>' +
            '<span class="detail-val text-plex fw-bold" style="font-size:1rem">' + bay.plate + '</span></div>' +

            '<div class="detail-row"><span class="detail-lbl">Delivery No.</span>' +
            '<span class="detail-val">xxx-xxx-' + bay.container + '</span></div>' +

            (bay.driverName
                ? '<div class="detail-row"><span class="detail-lbl">Driver</span><span class="detail-val">' + bay.driverName + '</span></div>'
                : '') +

            '<div class="detail-row"><span class="detail-lbl">Phone</span>' +
            '<span class="detail-val"><a href="tel:' + bay.phone + '" class="text-plex">' + bay.phone + '</a></span></div>' +

            (bay.transactionNumber
                ? '<div class="detail-row"><span class="detail-lbl">Transaction</span><span class="detail-val">' + bay.transactionNumber + '</span></div>'
                : '') +

            '<div class="detail-row"><span class="detail-lbl">Check-In</span>' +
            '<span class="detail-val">' +
                (bay.checkInTime ? new Date(bay.checkInTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—') +
            '</span></div>' +

            (bay.notes
                ? '<div class="detail-row"><span class="detail-lbl">Notes</span><span class="detail-val text-muted">' + bay.notes + '</span></div>'
                : '');
    }

    document.getElementById('bayModalBody').innerHTML = body;

    var footer = document.getElementById('bayModalFooter');
    footer.innerHTML = '';

    if (st === 'free') {
        footer.innerHTML =
            '<button class="btn btn-plex flex-grow-1" onclick="bayModal.hide();' +
            'document.getElementById(\'bkBay\').value=\'' + num + '\';switchTab(\'book\')">📋 Book Bay ' + num + '</button>';
    } else if (st === 'occupied' || st === 'incoming') {
        footer.innerHTML =
            '<button class="btn btn-warning flex-grow-1" onclick="updateBay(' + num + ',\'pending\')">Mark Pending</button>' +
            '<button class="btn btn-success flex-grow-1" onclick="updateBay(' + num + ',\'free\')">Release Bay</button>';
    } else {
        // pending
        footer.innerHTML =
            '<button class="btn btn-outline-warning flex-grow-1" onclick="updateBay(' + num + ',\'occupied\')">Mark Occupied</button>' +
            '<button class="btn btn-success flex-grow-1" onclick="updateBay(' + num + ',\'free\')">Clear &amp; Release</button>';
    }

    bayModal.show();
}

// ── Update bay status ─────────────────────────────────────────
async function updateBay(num, status) {
    bayModal.hide();
    var booking   = bays[num];
    var bookingId = (booking && booking.bookingId) ? booking.bookingId : null;

    var d = await api('update_bay', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ bayNum: num, status: status, bookingId: bookingId })
    });

    if (d.error) { toast(d.error, 'error'); return; }

    var msgs = {
        free:     'Bay ' + num + ' released ✓',
        pending:  'Bay ' + num + ' marked pending',
        occupied: 'Bay ' + num + ' marked occupied'
    };
    toast(msgs[status] || 'Bay updated', 'success');
    await loadBays();
    loadCounts();
}

// ── Populate bay dropdown in Book form ────────────────────────
function populateBayDropdown() {
    var sel = document.getElementById('bkBay');
    if (!sel) return;
    var cur = sel.value;
    sel.innerHTML = '<option value="">Select Free Bay…</option>';

    // Group free bays by call sign
    var cs7free = [], cs8free = [];
    Object.entries(bays).forEach(function(entry) {
        var num = parseInt(entry[0]), b = entry[1];
        if (b.status === 'free') {
            if (num <= 30) cs7free.push(num);
            else           cs8free.push(num);
        }
    });

    // Sort numerically
    cs7free.sort(function(a, b) { return a - b; });
    cs8free.sort(function(a, b) { return a - b; });

    if (cs7free.length) {
        var grp7 = document.createElement('optgroup');
        grp7.label = 'Call Sign 7 (Bays 1–30)';
        cs7free.forEach(function(num) {
            grp7.appendChild(new Option('Bay ' + num, num, num == cur, num == cur));
        });
        sel.add(grp7);
    }

    if (cs8free.length) {
        var grp8 = document.createElement('optgroup');
        grp8.label = 'Call Sign 8 (Bays 51–80)';
        cs8free.forEach(function(num) {
            grp8.appendChild(new Option('Bay ' + num, num, num == cur, num == cur));
        });
        sel.add(grp8);
    }

    if (!cs7free.length && !cs8free.length) {
        sel.innerHTML = '<option value="">No free bays available</option>';
    }
}

// ── Start auto-refresh for bay board ─────────────────────────
function startBayRefresh() {
    stopBayRefresh();
    _bayRefreshTimer = setInterval(function() {
        loadBays();
        loadCounts();
    }, 15000); // every 15 seconds
}

function stopBayRefresh() {
    if (_bayRefreshTimer) {
        clearInterval(_bayRefreshTimer);
        _bayRefreshTimer = null;
    }
}
