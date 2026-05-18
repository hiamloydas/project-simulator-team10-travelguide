<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management — WanderGuide Admin</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>User Management</h1><p>Add, verify and remove user accounts</p></div>
    </div>

    <?php if (!empty($error)):   ?><div class="alert alert-error"><?=   e($error)   ?></div><?php endif; ?>
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs=['added'=>'User added.','updated'=>'User updated.','deleted'=>'User deleted.']; ?>
        <?php if (!empty($msgs[$_GET['msg']])): ?><div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div><?php endif; ?>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="card card-body" style="margin-bottom:24px;">
        <h3 class="card-title">+ Add New User</h3>
        <form method="POST" action="index.php?page=admin&sub=users&action=add" class="form" novalidate id="addUserForm">
            <div class="field-row">
                <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="Full name" required></div>
                <div class="field"><label>Email</label><input type="email" name="email" placeholder="email@example.com" required></div>
            </div>
            <div class="field-row">
                <div class="field"><label>Password</label><input type="password" name="password" placeholder="Min 8 characters" required></div>
                <div class="field">
                    <label>Role</label>
                    <select name="role">
                        <option value="user">General User</option>
                        <option value="scout">Scout</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-card">
        <div class="table-toolbar">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" id="searchInput" placeholder="Search users…">
            </div>
            <span class="badge badge-terra" id="userCount"><?= count($users) ?> users</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                <?php if (empty($users)): ?>
                    <tr class="empty-row"><td colspan="7">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $u): ?>
                    <tr data-name="<?= strtolower(e($u['name'])) ?>" data-email="<?= strtolower(e($u['email'])) ?>">
                        <td><?= $i+1 ?></td>
                        <td><strong><?= e($u['name']) ?></strong></td>
                        <td><?= e($u['email']) ?></td>
                        <td><span class="badge <?= $u['role']==='scout'?'badge-teal':'badge-sand' ?>"><?= ucfirst(e($u['role'])) ?></span></td>
                        <td>
                            <span class="badge <?= $u['is_verified']?'badge-green':'badge-yellow' ?>" id="vstatus-<?= $u['id'] ?>">
                                <?= $u['is_verified']?'✓ Verified':'⏳ Pending' ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:var(--muted);"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td class="text-right">
                            <button class="btn-sm btn-edit" onclick="toggleVerify(<?= $u['id'] ?>)" id="vbtn-<?= $u['id'] ?>"><?= $u['is_verified']?'Unverify':'Verify' ?></button>
                            <a class="btn-sm btn-del" href="index.php?page=admin&sub=users&action=delete&id=<?= $u['id'] ?>" onclick="return confirm('Delete user <?= e(addslashes($u['name'])) ?>? This cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>

<script>
// Live search
var rows = document.querySelectorAll('#userTableBody tr[data-name]');
document.getElementById('searchInput').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    var count = 0;
    rows.forEach(function(r) {
        var match = r.dataset.name.includes(q) || r.dataset.email.includes(q);
        r.style.display = match ? '' : 'none';
        if (match) count++;
    });
    document.getElementById('userCount').textContent = count + ' users';
});

// AJAX toggle verify
function toggleVerify(id) {
    fetch('index.php?page=api&endpoint=toggle_verify', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'user_id=' + id
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            var statusEl = document.getElementById('vstatus-' + id);
            var btnEl    = document.getElementById('vbtn-' + id);
            if (d.is_verified) {
                statusEl.textContent = '✓ Verified';
                statusEl.className = 'badge badge-green';
                btnEl.textContent = 'Unverify';
            } else {
                statusEl.textContent = '⏳ Pending';
                statusEl.className = 'badge badge-yellow';
                btnEl.textContent = 'Verify';
            }
        }
    });
}
</script>
</body>
</html>
