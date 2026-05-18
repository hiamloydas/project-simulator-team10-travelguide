<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-panel">
        <div class="auth-logo">
            <span class="auth-logo-icon">🌍</span>
            <span>WanderGuide</span>
        </div>
        <h1>Explore the World</h1>
        <p>Discover breathtaking destinations, plan your adventures, and share travel stories with a community of explorers.</p>
        <ul class="feature-list">
            <li>Curated travel destinations worldwide</li>
            <li>Scout-submitted destination guides</li>
            <li>Personalised wishlist & cost estimates</li>
            <li>Community reviews and comments</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome back</h2>
            <p class="auth-sub">Sign in to continue your journey</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form" novalidate id="loginForm">
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= e($prefill ?? '') ?>"
                           placeholder="your@email.com" required autofocus>
                    <span class="field-err" style="font-size:12px;color:var(--error);display:none"></span>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                    <span class="field-err" style="font-size:12px;color:var(--error);display:none"></span>
                </div>
                <label class="checkbox">
                    <input type="checkbox" name="remember" <?= !empty($prefill)?'checked':'' ?>>
                    <span>Remember me for 30 days</span>
                </label>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="auth-foot">
                Don't have an account? <a href="index.php?page=register">Register here</a>
            </p>
            <div class="auth-hint">
                <strong>Default Admin:</strong> admin@travelguide.com / admin123
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    var ok = true;
    var errs = this.querySelectorAll('.field-err');
    errs.forEach(function(el){ el.style.display='none'; el.textContent=''; });

    var email = document.getElementById('email').value.trim();
    var pass  = document.getElementById('password').value;

    if (!email) { showErr(0, 'Email is required.'); ok = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showErr(0, 'Enter a valid email.'); ok = false; }
    if (!pass)  { showErr(1, 'Password is required.'); ok = false; }

    if (!ok) e.preventDefault();

    function showErr(i, msg) { errs[i].textContent = msg; errs[i].style.display = 'block'; }
});
</script>
</body>
</html>
