<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>Admin Dashboard</h1><p>Overview of your travel guide platform</p></div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon terra">👥</div>
            <div><div class="stat-value"><?= $stats['total_users'] ?></div><div class="stat-label">Total Users</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal">🧭</div>
            <div><div class="stat-value"><?= $stats['scouts'] ?></div><div class="stat-label">Scouts</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon sand">⏳</div>
            <div><div class="stat-value"><?= $stats['pending_verify'] ?></div><div class="stat-label">Pending Verification</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">📋</div>
            <div><div class="stat-value"><?= $stats['pending_posts'] ?></div><div class="stat-label">Pending Requests</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon terra">🗺️</div>
            <div><div class="stat-value"><?= $stats['total_posts'] ?></div><div class="stat-label">Published Posts</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal">💬</div>
            <div><div class="stat-value"><?= $stats['total_comments'] ?></div><div class="stat-label">Comments</div></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <div class="card card-body">
            <h3 class="card-title">Quick Actions</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <a href="index.php?page=admin&sub=requests" class="btn btn-teal">📋 Review Post Requests <?php if($stats['pending_posts']>0): ?><span class="badge badge-red" style="margin-left:auto;"><?= $stats['pending_posts'] ?></span><?php endif; ?></a>
                <a href="index.php?page=admin&sub=users"    class="btn btn-ghost">👥 Manage Users <?php if($stats['pending_verify']>0): ?><span class="badge badge-yellow" style="margin-left:auto;"><?= $stats['pending_verify'] ?> pending</span><?php endif; ?></a>
                <a href="index.php?page=admin&sub=posts"    class="btn btn-ghost">🗺️ Manage Posts</a>
                <a href="index.php?page=admin&sub=comments" class="btn btn-ghost">💬 Moderate Comments</a>
            </div>
        </div>
        <div class="card card-body">
            <h3 class="card-title">Platform Health</h3>
            <?php $total = max(1, $stats['scouts'] + $stats['general_users']); ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted);margin-bottom:5px;">
                    <span>Scouts</span><span><?= $stats['scouts'] ?></span>
                </div>
                <div style="background:#ebe4db;border-radius:99px;height:8px;">
                    <div style="background:var(--terra);border-radius:99px;height:8px;width:<?= round($stats['scouts']/$total*100) ?>%;transition:.4s;"></div>
                </div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted);margin-bottom:5px;">
                    <span>General Users</span><span><?= $stats['general_users'] ?></span>
                </div>
                <div style="background:#ebe4db;border-radius:99px;height:8px;">
                    <div style="background:var(--teal);border-radius:99px;height:8px;width:<?= round($stats['general_users']/$total*100) ?>%;transition:.4s;"></div>
                </div>
            </div>
        </div>
    </div>
</main>
<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>
</body>
</html>
