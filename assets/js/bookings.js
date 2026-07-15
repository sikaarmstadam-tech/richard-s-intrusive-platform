/* =============================================================
   bookings.js — Weighbill, book form, booking logs
   ============================================================= */

// ── Weighbill Image Capture ───────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var wi = document.getElementById('weighbillInput');
    if (!wi) return;

    wi.onchange = function() {
        if (!this.files[0]) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('weighbillImg').src = e.target.result;
            document.getElementById('weighbillPreview').classList.remove('d-none');
        };
        reader.readAsDataURL(this.files[0]);
    };
});

function clearWeighbill() {
    document.getElementById('weighbillInput').value = '';
    document.getElementById('weighbillPreview').classList.add('d-none');
    document.getElementById('weighbillImg').src = '';
}

// ── Book Entry Form Submit ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('bookForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        var plate  = document.getElementById('bkPlate').value.trim().toUpperCase();
        var cont   = document.getElementById('bkContainer').value.trim();
        var bay    = document.getElementById('bkBay').value;
        var driver = document.getElementById('bkDriver').value.trim();
        var phone  = document.getElementById('bkPhone').value.trim();
        var txn    = document.getElementById('bkTxn').value.trim();
        var notes  = document.getElementById('bkNotes').value.trim();

        if (!/^\d{3}(\/\d{3})?$/.test(cont)) {
            toast('Delivery No. must be 3 digits (e.g. 981) or 3/3 digits (e.g. 981/982)', 'error');
            return;
        }

        document.getElementById('bookBtnText').textContent = 'Booking…';
        document.getElementById('bookSpinner').classList.remove('d-none');

        var d = await api('create_booking', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                plate:             plate,
                container:         cont,
                bayNum:            +bay,
                phone:             phone,
                driverName:        driver,
                transactionNumber: txn,
                notes:             notes
            })
        });

        document.getElementById('bookBtnText').textContent = 'Register Arrival & Occupy Bay';
        document.getElementById('bookSpinner').classList.add('d-none');

        if (d.error) { toast(d.error, 'error'); return; }

        // Show receipt modal
        document.getElementById('receiptTitle').textContent = 'Booking Confirmed';
        document.getElementById('receiptSub').textContent   = 'Terminal Dispatch Operations — Yard Entry';
        document.getElementById('receiptCheck').style.color = 'var(--c-plex)';
        document.getElementById('receiptBody').innerHTML =
            '<div class="detail-row"><span class="detail-lbl">Plate</span><span class="detail-val text-plex fw-bold">' + plate + '</span></div>' +
            '<div class="detail-row"><span class="detail-lbl">Delivery</span><span class="detail-val">xxx-xxx-' + cont + '</span></div>' +
            '<div class="detail-row"><span class="detail-lbl">Bay</span><span class="detail-val fw-bold">' + bay + '</span></div>' +
            '<div class="detail-row"><span class="detail-lbl">Driver</span><span class="detail-val">' + driver + '</span></div>' +
            '<div class="detail-row"><span class="detail-lbl">Call Sign</span><span class="detail-val">' + d.callSign + '</span></div>' +
            '<div class="detail-row"><span class="detail-lbl">Check-In</span><span class="detail-val">' +
                new Date(d.checkInTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
            '</span></div>';

        receiptModal.show();
        e.target.reset();
        clearWeighbill();
        await loadBays();
        loadCounts();
        switchTab('board');
    });
});

// ── Log Sub-tabs ──────────────────────────────────────────────
function switchLogTab(t) {
    logSubTab = t;
    document.getElementById('btnActive').classList.toggle('active',  t === 'active');
    document.getElementById('btnHistory').classList.toggle('active', t === 'history');
    if (t === 'history') loadHistory();
    else renderLogs();
}

// ── Load Active Bookings ──────────────────────────────────────
async function loadBookings() {
    var d = await api('bookings');
    if (!d.error) {
        bookings = d;
        document.getElementById('activeCount').textContent = d.length;
    }
    renderLogs();
}

// ── Load Completed History ────────────────────────────────────
async function loadHistory() {
    var d = await api('bookings', {}, { status: 'completed' });
    if (!d.error) historyBookings = d;
    renderLogs();
}

// ── Search filter ─────────────────────────────────────────────
function filterLogs() { renderLogs(); }

// ── Render Log Cards ──────────────────────────────────────────
function renderLogs() {
    var q   = document.getElementById('logSearch').value.toLowerCase();
    var src = logSubTab === 'history' ? historyBookings : bookings;

    var lst = src.filter(function(b) {
        return (b.plate || '').toLowerCase().includes(q) ||
               (b.container || '').includes(q) ||
               String(b.bayNum || '').includes(q);
    });

    var el = document.getElementById('logsList');

    if (!lst.length) {
        el.innerHTML = '<div class="text-center text-muted py-4">No entries found.</div>';
        return;
    }

    el.innerHTML = lst.map(function(b) {
        var bayN      = b.bayNum || '—';
        var isHistory = logSubTab === 'history';
        var inT       = b.checkInTime
            ? new Date(b.checkInTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            : '—';
        var outT = b.checkOutTime
            ? new Date(b.checkOutTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            : null;
        var dur = b.durationMinutes ? fmtDur(b.durationMinutes) : null;

        return '<div class="booking-card ' + (isHistory ? 'history' : '') + '">' +
            '<div class="d-flex align-items-start gap-2 flex-grow-1 min-w-0">' +
                '<div class="bay-badge">' + bayN + '</div>' +
                '<div class="min-w-0 flex-grow-1">' +
                    '<div class="fw-600 text-white">' + b.plate +
                        '<span class="container-code">CON-' + b.container + '</span>' +
                    '</div>' +
                    '<div class="small text-muted mt-1">' +
                        (isHistory
                            ? 'In: ' + inT + ' → Out: ' + (outT || '—')
                            : 'In: ' + inT + ' · ' + (b.phone || '')) +
                    '</div>' +
                    (b.driverName ? '<div class="small text-muted">' + b.driverName + '</div>' : '') +
                '</div>' +
            '</div>' +
            '<div class="d-flex align-items-center gap-2 flex-shrink-0">' +
                (dur ? '<span class="dur-badge">' + dur + '</span>' : '') +
                (!isHistory && b.phone
                    ? '<a href="tel:' + b.phone + '" class="btn-icon call">' +
                        '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
                        'd="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98' +
                        'a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725' +
                        '.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></a>'
                    : '') +
                (!isHistory
                    ? '<button class="btn-icon del" onclick="releaseBay(' + bayN + ')">' +
                        '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
                        'd="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4' +
                        'a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>'
                    : '') +
            '</div>' +
        '</div>';
    }).join('');
}

// ── Helpers ───────────────────────────────────────────────────
function fmtDur(m) {
    var h = Math.floor(m / 60), r = m % 60;
    return h > 0 ? h + 'h ' + r + 'm' : r + 'm';
}

async function releaseBay(num) {
    if (!confirm('Release Bay ' + num + '?')) return;
    await updateBay(num, 'free');
    await loadBookings();
    // If user is viewing history, refresh it so the completed entry appears immediately
    if (logSubTab === 'history') await loadHistory();
}
