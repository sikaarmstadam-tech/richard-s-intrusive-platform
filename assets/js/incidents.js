/* =============================================================
   incidents.js — Incident reporting and history
   ============================================================= */

// ── Sub-tab switch ────────────────────────────────────────────
function switchIncTab(t) {
    incSubTab = t;
    document.getElementById('btnIncReport').classList.toggle('active',  t === 'report');
    document.getElementById('btnIncHistory').classList.toggle('active', t === 'history');
    document.getElementById('incReportForm').classList.toggle('d-none', t === 'history');
    document.getElementById('incHistoryList').classList.toggle('d-none', t === 'report');
    if (t === 'history') renderIncidents();
}

// ── Load incidents from API ───────────────────────────────────
async function loadIncidents() {
    var d = await api('incidents');
    if (!d.error) {
        incidents = d;
        var open = d.filter(function(i) { return i.status === 'open'; }).length;

        ['incBadgeSidebar', 'incBadgeNav'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.textContent = open;
                el.classList.toggle('d-none', open === 0);
            }
        });
    }
}

// ── Render incident cards ─────────────────────────────────────
function renderIncidents() {
    var el = document.getElementById('incHistoryList');

    if (!incidents.length) {
        el.innerHTML = '<div class="text-center text-muted py-4">No reports yet.</div>';
        return;
    }

    el.innerHTML = incidents.map(function(inc) {
        var emojiMap = {
            accident: '🚗', injury: '🩹', complaint: '📣',
            theft: '🔒', fire: '🔥', damage: '⚠️', other: '📋'
        };
        var typeEmoji = emojiMap[inc.type] || '📋';
        var sevCls    = { low: 'text-success', medium: 'text-warning', high: 'text-danger', critical: 'text-danger fw-bold' }[inc.severity] || 'text-muted';
        var stCls     = inc.status === 'open' ? 'text-warning' : 'text-success';

        return '<div class="inc-card sev-card-' + inc.severity + ' mb-3">' +
            '<div class="d-flex align-items-center gap-2 mb-2 flex-wrap">' +
                '<span class="inc-type-badge">' + typeEmoji + ' ' + inc.type + '</span>' +
                '<span class="' + sevCls + ' small fw-600">' + inc.severity + '</span>' +
                '<span class="' + stCls  + ' small">' + inc.status + '</span>' +
                '<span class="text-muted small ms-auto">' + new Date(inc.timestamp).toLocaleString() + '</span>' +
            '</div>' +
            '<div class="fw-600 text-white mb-1">' + inc.title + '</div>' +
            (inc.location
                ? '<div class="small text-muted">📍 ' + inc.location +
                    (inc.plateInvolved ? ' · 🚛 ' + inc.plateInvolved : '') +
                    (inc.reportedBy    ? ' · 👤 ' + inc.reportedBy    : '') +
                  '</div>'
                : '') +
            (inc.statement ? '<div class="small text-muted mt-1" style="white-space:pre-wrap">' + inc.statement + '</div>' : '') +
            (inc.imageRef  ? '<img src="' + inc.imageRef + '" class="inc-evidence-img mt-2" alt="Evidence">' : '') +
            (inc.status === 'open'
                ? '<button class="btn btn-sm btn-outline-success mt-2" onclick="resolveInc(' + inc.id + ')">✓ Mark Resolved</button>'
                : '') +
        '</div>';
    }).join('');
}

// ── Resolve an incident ───────────────────────────────────────
async function resolveInc(id) {
    await api('resolve_incident', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: id })
    });
    toast('Incident marked as resolved', 'success');
    await loadIncidents();
    renderIncidents();
}

// ── Severity selector ─────────────────────────────────────────
function setSeverity(s) {
    document.getElementById('incSeverity').value = s;
    document.querySelectorAll('.sev-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.sev === s);
    });
}

// ── Photo preview ─────────────────────────────────────────────
function previewIncPhoto(input) {
    if (!input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('incPhotoPreviewImg').src = e.target.result;
        document.getElementById('incPhotoPreviewImg').classList.remove('d-none');
        document.getElementById('incPhotoPlaceholder').classList.add('d-none');
        document.getElementById('removeIncPhoto').classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
}

function clearIncPhoto() {
    document.getElementById('incPhotoInput').value = '';
    document.getElementById('incPhotoPreviewImg').src = '';
    document.getElementById('incPhotoPreviewImg').classList.add('d-none');
    document.getElementById('incPhotoPlaceholder').classList.remove('d-none');
    document.getElementById('removeIncPhoto').classList.add('d-none');
}

// ── Submit incident form ──────────────────────────────────────
async function submitIncident(e) {
    e.preventDefault();
    var form      = document.getElementById('incidentForm');
    var fd        = new FormData(form);
    var photoFile = document.getElementById('incPhotoInput').files[0];
    if (photoFile) fd.append('photo', photoFile);

    document.getElementById('incBtnText').textContent = 'Submitting…';
    document.getElementById('incSpinner').classList.remove('d-none');

    var res = await fetch('api.php?action=create_incident', { method: 'POST', body: fd });
    var d   = await res.json();

    document.getElementById('incBtnText').textContent = '📋 Submit Incident Report';
    document.getElementById('incSpinner').classList.add('d-none');

    if (d.error) { toast(d.error, 'error'); return; }

    toast('Incident report submitted', 'success');
    form.reset();
    clearIncPhoto();
    setSeverity('medium');
    await loadIncidents();
    switchIncTab('history');
}
