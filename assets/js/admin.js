/* =============================================================
   admin.js — User management (admin only)
   ============================================================= */

// ── Load user list ────────────────────────────────────────────
async function loadUsers() {
    var d = await api('users');

    if (d.error) {
        document.getElementById('userList').innerHTML =
            '<div class="text-danger small">' + d.error + '</div>';
        return;
    }

    var me = window.PIPLEX_USER_EMAIL || '';

    document.getElementById('userList').innerHTML = d.length
        ? d.map(function(u) {
            var canDelete = u.email !== me && u.email !== 'admin@piplex.io';
            return '<div class="user-row">' +
                '<div class="avatar-circle">👤</div>' +
                '<div class="flex-grow-1 min-w-0">' +
                    '<div class="fw-600 text-white text-truncate">' + u.name + '</div>' +
                    '<div class="small text-muted text-truncate">' + u.email +
                        ' · <span class="text-plex text-uppercase" style="font-size:.7rem">' + u.role + '</span>' +
                    '</div>' +
                '</div>' +
                (canDelete
                    ? '<button class="btn btn-sm btn-outline-danger" onclick="deleteUser(' + u.id + ',\'' + u.name.replace(/'/g, "\\'") + '\')">🗑</button>'
                    : '') +
            '</div>';
        }).join('')
        : '<div class="text-muted text-center py-2">No users found.</div>';
}

// ── Create user ───────────────────────────────────────────────
async function createUser(e) {
    e.preventDefault();

    var msgEl = document.getElementById('userFormMsg');
    var btn   = document.getElementById('createUserBtn');
    msgEl.className = 'd-none';
    btn.disabled    = true;
    btn.textContent = 'Creating…';

    var d = await api('create_user', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
            name:     document.getElementById('nuName').value.trim(),
            email:    document.getElementById('nuEmail').value.trim(),
            password: document.getElementById('nuPass').value,
            role:     document.getElementById('nuRole').value
        })
    });

    btn.disabled    = false;
    btn.textContent = '+ Create Account';

    if (d.error) {
        msgEl.className   = 'alert alert-danger';
        msgEl.textContent = d.error;
    } else {
        msgEl.className   = 'alert alert-success';
        msgEl.textContent = '✓ Account created successfully';
        document.getElementById('createUserForm').reset();
        loadUsers();
    }
}

// ── Delete user ───────────────────────────────────────────────
async function deleteUser(id, name) {
    if (!confirm('Remove account for ' + name + '?')) return;

    var d = await api('delete_user', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: id })
    });

    if (d.error) { toast(d.error, 'error'); return; }
    toast(name + ' removed.', 'success');
    loadUsers();
}
