<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pending Approval — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-body">
<div class="pending-wrap">
    <div class="pending-box">
        <div class="pending-icon">⏳</div>
        <h2>Account Pending Approval</h2>
        <p>Your account has been created and is currently awaiting verification by an administrator. You'll be able to access all features once approved.</p>
        <?php if (isset($_SESSION['user'])): ?>
            <p style="font-size:14px;color:var(--muted);">Logged in as <strong><?= e($_SESSION['user']['email']) ?></strong></p>
        <?php endif; ?>
        <a href="index.php?page=logout" class="btn btn-ghost">Back to Login</a>
    </div>
</div>
</body>
</html>
