/* =============================================================
   roster.js — Shift roster management
   ============================================================= */

// ── Load Roster from API ──────────────────────────────────────
async function loadRoster() {
    var d = await api('roster');
    if (!d.error) roster = d;
    renderRoster();
}

// ── Switch roster group (A / B / C) ──────────────────────────
function selectRosterGroup(g) {
    rosterGroup = g;
    document.querySelectorAll('#tab-roster .seg-btn').forEach(function(b, i) {
        b.classList.toggle('active', ['A', 'B', 'C'][i] === g);
    });
    renderRoster();
}

// ── Render roster list ────────────────────────────────────────
function renderRoster() {
    var grp     = roster[rosterGroup] || [];
    var present = grp.filter(function(m) { return m.present; }).length;

    document.getElementById('slotCount').textContent     = present + ' / 13 present';
    document.getElementById('slotRemaining').textContent = present >= 13 ? '🔒 Full' : (13 - present) + ' remaining';

    var roles = window.PIPLEX_ROLES || [];
    var el    = document.getElementById('rosterList');

    if (!grp.length) {
        el.innerHTML = '<div class="text-muted text-center py-3">No marshals logged yet.</div>';
        return;
    }

    el.innerHTML = grp.map(function(m, i) {
        var roleOptions = roles.map(function(r) {
            return '<option' + (r === m.activeRole ? ' selected' : '') + '>' + r + '</option>';
        }).join('');

        return '<div class="roster-row ' + (m.present ? '' : 'absent') + '">' +
            '<div class="roster-num">' + (i + 1) + '</div>' +
            '<div class="flex-grow-1 min-w-0">' +
                '<div class="fw-600 text-white text-truncate">' + m.name + '</div>' +
                '<select class="form-select form-select-sm plex-input mt-1" onchange="updateRosterRole(' + i + ',this.value)">' +
                    roleOptions +
                '</select>' +
            '</div>' +
            '<button class="btn btn-sm ' + (m.present ? 'btn-success' : 'btn-outline-secondary') + ' ms-2" ' +
                'onclick="togglePresent(' + i + ')" style="min-width:72px">' +
                (m.present ? '✓ Present' : 'Absent') +
            '</button>' +
        '</div>';
    }).join('');
}

// ── Add marshal ───────────────────────────────────────────────
function addMarshal() {
    var name = document.getElementById('newMarshalName').value.trim();
    var role = document.getElementById('newMarshalRole').value;

    if (!name) { toast('Enter a name', 'error'); return; }
    if (!roster[rosterGroup]) roster[rosterGroup] = [];
    if (roster[rosterGroup].length >= 13) { toast('All 13 slots filled', 'error'); return; }

    var duplicate = roster[rosterGroup].some(function(m) {
        return m.name.toLowerCase() === name.toLowerCase();
    });
    if (duplicate) { toast('Already in roster', 'error'); return; }

    roster[rosterGroup].push({ name: name, activeRole: role, present: true });
    document.getElementById('newMarshalName').value = '';
    renderRoster();
    syncRoster();
}

// ── Toggle present / absent ───────────────────────────────────
function togglePresent(i) {
    roster[rosterGroup][i].present = !roster[rosterGroup][i].present;
    renderRoster();
    syncRoster();
}

// ── Update role inline ────────────────────────────────────────
function updateRosterRole(i, role) {
    roster[rosterGroup][i].activeRole = role;
    syncRoster();
}

// ── Persist roster to server ──────────────────────────────────
async function syncRoster() {
    await api('sync_roster', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ date: window.PIPLEX_TODAY, rosters: roster })
    });
}

// ── Print roster ──────────────────────────────────────────────
function printRoster() {
    var grp   = roster[rosterGroup] || [];
    var shift = window.PIPLEX_SHIFT || '';
    var today = window.PIPLEX_TODAY || '';

    var rows = grp.map(function(m, i) {
        return '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + m.name + '</td>' +
            '<td>' + m.activeRole + '</td>' +
            '<td>' + (m.present ? '✓ Present' : 'Absent') + '</td>' +
        '</tr>';
    }).join('');

    var presentCount = grp.filter(function(m) { return m.present; }).length;

    var w = window.open('', '_blank');
    w.document.write(
        '<!DOCTYPE html><html><head><title>Roster — Group ' + rosterGroup + '</title>' +
        '<style>body{font-family:Arial,sans-serif;margin:20px}' +
        'table{width:100%;border-collapse:collapse}' +
        'th,td{border:1px solid #ccc;padding:8px;text-align:left}' +
        'th{background:#0d1525;color:white}</style></head>' +
        '<body>' +
        '<h2>MPS Marshal Roster — Group ' + rosterGroup + ' · ' + shift + '</h2>' +
        '<p>Date: ' + today + ' | Total Present: ' + presentCount + ' of 13</p>' +
        '<table><thead><tr><th>#</th><th>Name</th><th>Call Sign / Role</th><th>Status</th></tr></thead>' +
        '<tbody>' + rows + '</tbody></table>' +
        '</body></html>'
    );
    w.document.close();
    w.print();
}
