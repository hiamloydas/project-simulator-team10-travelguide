<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Requests — WanderGuide Admin</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>Post Requests</h1><p>Review and moderate scout submissions</p></div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs=['approved'=>'Post approved and published!','rejected'=>'Request rejected.']; ?>
        <?php if (!empty($msgs[$_GET['msg']])): ?><div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-toolbar">
            <span style="font-weight:600;color:var(--slate);">All Requests</span>
            <span class="badge badge-terra"><?= count($requests) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Scout</th>
                        <th>Country / Genre</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($requests)): ?>
                    <tr class="empty-row"><td colspan="8">No requests found.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $i => $r): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><strong><?= e($r['title']) ?></strong></td>
                        <td><?= e($r['scout_name']) ?></td>
                        <td><?= e($r['country']) ?> / <?= ucfirst(e($r['genre'])) ?></td>
                        <td>
                            <?php if ($r['original_post_id']): ?>
                                <span class="badge badge-teal">Change Request</span>
                            <?php else: ?>
                                <span class="badge badge-sand">New Post</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $sc = ['pending'=>'badge-yellow','approved'=>'badge-green','rejected'=>'badge-red'][$r['status']] ?? 'badge-sand';
                            ?>
                            <span class="badge <?= $sc ?>"><?= ucfirst(e($r['status'])) ?></span>
                        </td>
                        <td style="font-size:13px;color:var(--muted);"><?= date('M j, Y', strtotime($r['requested_at'])) ?></td>
                        <td class="text-right">
                            <!-- Preview toggle -->
                            <button class="btn-sm btn-view" onclick="togglePreview('prev-<?= $r['id'] ?>')">Preview</button>
                            <?php if ($r['status']==='pending'): ?>
                                <a class="btn-sm btn-approve" href="index.php?page=admin&sub=requests&action=approve&id=<?= $r['id'] ?>" onclick="return confirm('Approve and publish this post?')">Approve</a>
                                <button class="btn-sm btn-del" onclick="openRejectModal(<?= $r['id'] ?>)">Reject</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <!-- Preview row -->
                    <tr id="prev-<?= $r['id'] ?>" style="display:none;background:var(--sand);">
                        <td colspan="8" style="padding:18px 24px;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;font-size:14px;">
                                <div>
                                    <strong>History:</strong>
                                    <p style="margin-top:6px;color:var(--slate);line-height:1.7;"><?= nl2br(e($r['short_history'])) ?></p>
                                </div>
                                <div>
                                    <strong>Details:</strong>
                                    <ul style="margin-top:6px;list-style:none;display:flex;flex-direction:column;gap:6px;color:var(--slate);">
                                        <li>🌍 Country: <?= e($r['country']) ?></li>
                                        <li>🏷️ Genre: <?= ucfirst(e($r['genre'])) ?></li>
                                        <li>💰 Cost: <?= ucfirst(e($r['cost_level'])) ?></li>
                                        <li>✈️ Travel: <?= e($r['travel_medium_info']) ?></li>
                                    </ul>
                                    <?php if (!empty($r['image'])): ?>
                                        <img src="<?= e($r['image']) ?>" style="max-height:120px;border-radius:8px;margin-top:10px;object-fit:cover;">
                                    <?php endif; ?>
                                    <?php if (!empty($r['rejection_reason'])): ?>
                                        <div class="alert alert-error" style="margin-top:10px;">Rejection reason: <?= e($r['rejection_reason']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Reject Modal -->
<div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:none;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:32px;max-width:440px;width:100%;margin:24px;">
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:12px;">Reject Request</h3>
        <form method="POST" id="rejectForm">
            <div class="field" style="margin-bottom:16px;">
                <label>Rejection Reason (optional)</label>
                <textarea name="reason" placeholder="Explain why this request is being rejected…" style="min-height:80px;width:100%;padding:10px 13px;border:1.5px solid #ddd;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>

<script>
function togglePreview(id) {
    var row = document.getElementById(id);
    row.style.display = (row.style.display === 'none' || !row.style.display) ? 'table-row' : 'none';
}
function openRejectModal(id) {
    var modal = document.getElementById('rejectModal');
    document.getElementById('rejectForm').action = 'index.php?page=admin&sub=requests&action=reject&id=' + id;
    modal.style.display = 'flex';
}
function closeRejectModal() { document.getElementById('rejectModal').style.display = 'none'; }
</script>
</body>
</html>
