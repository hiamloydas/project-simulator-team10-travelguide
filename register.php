<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-panel">
        <div class="auth-logo">
            <span class="auth-logo-icon">🌍</span>
            <span>WanderGuide</span>
        </div>
        <h1>Join the Adventure</h1>
        <p>Create your account and start exploring curated travel destinations around the world.</p>
        <ul class="feature-list">
            <li>Register as a <strong>General User</strong> to browse, wishlist &amp; comment</li>
            <li>Register as a <strong>Scout</strong> to submit destination guides</li>
            <li>All accounts require admin verification</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="auth-sub">Fill in the form to get started</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="form" novalidate id="regForm">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= e($old['name']) ?>"
                           placeholder="Your full name" required>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= e($old['email']) ?>"
                           placeholder="your@email.com" required>
                </div>
                <div class="field">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" required>
                        <option value="user"  <?= ($old['role']==='user' ?'selected':'' ) ?>>General User — Browse destinations</option>
                        <option value="scout" <?= ($old['role']==='scout'?'selected':'') ?>>Scout — Submit destination guides</option>
                    </select>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Min 8 characters" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repeat password" required>
                    </div>
                </div>
                <div id="formErr" class="alert alert-error" style="display:none;margin-bottom:0;"></div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-foot">
                Already registered? <a href="index.php?page=login">Sign in</a>
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('regForm').addEventListener('submit', function(e) {
    var errBox = document.getElementById('formErr');
    errBox.style.display = 'none'; errBox.textContent = '';
    var name   = document.getElementById('name').value.trim();
    var email  = document.getElementById('email').value.trim();
    var pass   = document.getElementById('password').value;
    var conf   = document.getElementById('confirm_password').value;
    var msg = '';

    if (!name)  { msg = 'Full name is required.'; }
    else if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { msg = 'Enter a valid email address.'; }
    else if (pass.length < 8) { msg = 'Password must be at least 8 characters.'; }
    else if (pass !== conf)   { msg = 'Passwords do not match.'; }

    if (msg) { errBox.textContent = msg; errBox.style.display = 'block'; e.preventDefault(); }
});
</script>
</body>
</html>
