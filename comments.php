<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comment Moderation — WanderGuide Admin</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>Comment Moderation</h1><p>Review and remove user comments</p></div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success">Comment deleted.</div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-toolbar">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" id="searchInput" placeholder="Search comments…">
            </div>
            <span class="badge badge-terra" id="cmtCount"><?= count($comments) ?> comments</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Post</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="cmtBody">
                <?php if (empty($comments)): ?>
                    <tr class="empty-row"><td colspan="6">No comments yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($comments as $i => $c): ?>
                    <tr data-search="<?= strtolower(e($c['user_name'].' '.$c['post_title'].' '.$c['content'])) ?>" id="cmt-row-<?= $c['id'] ?>">
                        <td><?= $i+1 ?></td>
                        <td><strong><?= e($c['user_name']) ?></strong></td>
                        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($c['post_title']) ?></td>
                        <td style="max-width:320px;">
                            <span style="font-size:14px;color:var(--slate);"><?= e(mb_strimwidth($c['content'],0,120,'…')) ?></span>
                        </td>
                        <td style="font-size:13px;color:var(--muted);white-space:nowrap;"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                        <td class="text-right">
                            <button class="btn-sm btn-del" onclick="deleteComment(<?= $c['id'] ?>)">Delete</button>
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
var rows = document.querySelectorAll('#cmtBody tr[data-search]');
document.getElementById('searchInput').addEventListener('input', function() {
    var q = this.value.toLowerCase(), n = 0;
    rows.forEach(function(r){ var s=r.dataset.search.includes(q); r.style.display=s?'':'none'; if(s)n++; });
    document.getElementById('cmtCount').textContent = n + ' comments';
});

// AJAX delete
function deleteComment(id) {
    if (!confirm('Delete this comment?')) return;
    fetch('index.php?page=api&endpoint=delete_comment', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + id
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var row = document.getElementById('cmt-row-' + id);
            if (row) row.remove();
            var cnt = document.querySelectorAll('#cmtBody tr[data-search]');
            var visible = Array.from(cnt).filter(function(r){ return r.style.display !== 'none'; });
            document.getElementById('cmtCount').textContent = visible.length + ' comments';
        }
    });
}
</script>
</body>
</html>
