<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>

<main class="main">
    <div class="page-head">
        <div><h1>My Profile</h1><p>Manage your account details</p></div>
    </div>

    <?php if (!empty($error)):   ?><div class="alert alert-error"><?=   e($error)   ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <!-- Profile Info -->
        <div class="card card-body">
            <h3 class="card-title">Account Details</h3>
            <form method="POST" action="index.php?page=profile" enctype="multipart/form-data" class="form" id="profileForm" novalidate>
                <input type="hidden" name="action" value="profile">
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:10px;">
                    <div class="user-av" style="width:64px;height:64px;font-size:24px;" id="avatarPreview">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?= e($user['profile_picture']) ?>" id="previewImg" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <span id="previewInitial"><?= strtoupper(substr($user['name'],0,1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--slate);">Profile Picture</div>
                        <label class="file-upload-label" style="margin-top:6px;padding:7px 12px;font-size:13px;">
                            📷 Choose image
                            <input type="file" name="picture" id="picInput" accept="image/*">
                        </label>
                    </div>
                </div>
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= e($user['name']) ?>" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($user['email']) ?>" required>
                </div>
                <div class="field">
                    <label>Role</label>
                    <input type="text" value="<?= ucfirst(e($user['role'])) ?>" disabled style="background:var(--sand);color:var(--muted);">
                </div>
                <div class="field">
                    <label>Member Since</label>
                    <input type="text" value="<?= date('F j, Y', strtotime($user['created_at'])) ?>" disabled style="background:var(--sand);color:var(--muted);">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card card-body">
            <h3 class="card-title">Change Password</h3>
            <form method="POST" action="index.php?page=profile" class="form" id="passForm" novalidate>
                <input type="hidden" name="action" value="password">
                <div class="field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Current password" required>
                </div>
                <div class="field">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="newPass" placeholder="Min 8 characters" required>
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_new" id="confPass" placeholder="Repeat new password" required>
                </div>
                <div id="passErr" class="alert alert-error" style="display:none;margin-bottom:0;"></div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-teal">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>

<script>
// Preview profile picture
document.getElementById('picInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var container = document.getElementById('avatarPreview');
        container.innerHTML = '<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
    };
    reader.readAsDataURL(file);
});

// Password validation
document.getElementById('passForm').addEventListener('submit', function(ev) {
    var errBox = document.getElementById('passErr');
    errBox.style.display = 'none';
    var np = document.getElementById('newPass').value;
    var cp = document.getElementById('confPass').value;
    if (np.length < 8) { errBox.textContent='New password must be at least 8 characters.'; errBox.style.display='block'; ev.preventDefault(); }
    else if (np !== cp) { errBox.textContent='Passwords do not match.'; errBox.style.display='block'; ev.preventDefault(); }
});
</script>
</body>
</html>
