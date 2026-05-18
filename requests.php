<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Requests — WanderGuide Scout</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>My Post Requests</h1><p>Submit and manage your destination guides</p></div>
    </div>

    <?php if (!empty($error)):   ?><div class="alert alert-error"><?=   e($error)   ?></div><?php endif; ?>
    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs=['added'=>'Request submitted! Admin will review it shortly.','updated'=>'Request updated.','deleted'=>'Request deleted.']; ?>
        <?php if (!empty($msgs[$_GET['msg']])): ?><div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div><?php endif; ?>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <?php $isEdit = !empty($editing); ?>
    <div class="card card-body" style="margin-bottom:28px;">
        <h3 class="card-title"><?= $isEdit ? '✏️ Edit Request #'.intval($editing['id']) : '+ Submit New Destination' ?></h3>
        <form method="POST"
              action="index.php?page=scout&sub=requests&action=<?= $isEdit ? 'update&id='.intval($editing['id']) : 'add' ?>"
              class="form" enctype="multipart/form-data" novalidate id="requestForm">

            <div class="field-row">
                <div class="field">
                    <label>Destination Title <span style="color:var(--error)">*</span></label>
                    <input type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" placeholder="e.g. The Lost City of Petra" required>
                </div>
                <div class="field">
                    <label>Country <span style="color:var(--error)">*</span></label>
                    <input type="text" name="country" value="<?= e($editing['country'] ?? '') ?>" placeholder="e.g. Jordan" required>
                </div>
            </div>

            <div class="field">
                <label>Short History / Description <span style="color:var(--error)">*</span></label>
                <textarea name="short_history" rows="5" placeholder="Describe the destination — its history, significance, what makes it special…" required><?= e($editing['short_history'] ?? '') ?></textarea>
            </div>

            <div class="field-row">
                <div class="field">
                    <label>Genre <span style="color:var(--error)">*</span></label>
                    <select name="genre" required>
                        <option value="">— Select Genre —</option>
                        <?php foreach (['beach'=>'🏖️ Beach','mountain'=>'🏔️ Mountain','city'=>'🏙️ City','historical'=>'🏛️ Historical','wildlife'=>'🦁 Wildlife','cultural'=>'🎭 Cultural','adventure'=>'🧗 Adventure','other'=>'🌿 Other'] as $val=>$label): ?>
                            <option value="<?= $val ?>" <?= (($editing['genre']??'')===$val)?'selected':'' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Cost Level <span style="color:var(--error)">*</span></label>
                    <select name="cost_level" required>
                        <option value="">— Select Cost —</option>
                        <option value="low"    <?= (($editing['cost_level']??'')==='low')   ?'selected':'' ?>>💚 Low (Budget-friendly)</option>
                        <option value="medium" <?= (($editing['cost_level']??'')==='medium')?'selected':'' ?>>💛 Medium (Moderate)</option>
                        <option value="high"   <?= (($editing['cost_level']??'')==='high')  ?'selected':'' ?>>🔴 High (Luxury)</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Travel Medium Info <span style="color:var(--error)">*</span></label>
                <input type="text" name="travel_medium_info" value="<?= e($editing['travel_medium_info'] ?? '') ?>" placeholder="e.g. Flight to Amman + 3h bus journey" required>
            </div>

            <div class="field">
                <label>Destination Image (optional, max 5 MB)</label>
                <label class="file-upload-label">
                    📷 Choose image (JPG, PNG, WebP)
                    <input type="file" name="image" id="imgInput" accept="image/*">
                </label>
                <?php if (!empty($editing['image'])): ?>
                    <img src="<?= e($editing['image']) ?>" id="imgPreview" style="max-height:140px;border-radius:8px;margin-top:10px;object-fit:cover;">
                <?php else: ?>
                    <img id="imgPreview" class="img-preview">
                <?php endif; ?>
            </div>

            <div id="formErr" class="alert alert-error" style="display:none;margin-bottom:0;"></div>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=scout&sub=requests" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Request</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Submit for Review</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- My Requests Table -->
    <div class="table-card">
        <div class="table-toolbar">
            <span style="font-weight:600;color:var(--slate);">📋 My Submissions</span>
            <span class="badge badge-terra"><?= count($requests) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Country</th>
                        <th>Genre</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($requests)): ?>
                    <tr class="empty-row"><td colspan="8">No requests yet. Submit your first destination above!</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $i => $r): ?>
                    <tr id="req-row-<?= $r['id'] ?>">
                        <td><?= $i+1 ?></td>
                        <td><strong><?= e($r['title']) ?></strong></td>
                        <td>📍 <?= e($r['country']) ?></td>
                        <td><?= ucfirst(e($r['genre'])) ?></td>
                        <td><span class="badge <?= ['low'=>'badge-green','medium'=>'badge-yellow','high'=>'badge-red'][$r['cost_level']]??'badge-sand' ?>"><?= ucfirst(e($r['cost_level'])) ?></span></td>
                        <td>
                            <span class="badge <?= ['pending'=>'badge-yellow','approved'=>'badge-green','rejected'=>'badge-red'][$r['status']]??'badge-sand' ?>">
                                <?= ucfirst(e($r['status'])) ?>
                            </span>
                            <?php if (!empty($r['rejection_reason'])): ?>
                                <div style="font-size:11px;color:var(--error);margin-top:3px;">Reason: <?= e($r['rejection_reason']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:var(--muted);"><?= date('M j, Y', strtotime($r['requested_at'])) ?></td>
                        <td class="text-right">
                            <?php if ($r['status']==='pending'): ?>
                                <a class="btn-sm btn-edit" href="index.php?page=scout&sub=requests&action=edit&id=<?= $r['id'] ?>">Edit</a>
                                <button class="btn-sm btn-del" onclick="deleteRequest(<?= $r['id'] ?>)">Delete</button>
                            <?php else: ?>
                                <span style="font-size:12px;color:var(--muted);">—</span>
                            <?php endif; ?>
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
// Image preview
document.getElementById('imgInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var preview = document.getElementById('imgPreview');
    var reader  = new FileReader();
    reader.onload = function(ev) { preview.src = ev.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(file);
});

// Client-side validation
document.getElementById('requestForm').addEventListener('submit', function(ev) {
    var errBox = document.getElementById('formErr');
    errBox.style.display = 'none';
    var title   = this.querySelector('[name=title]').value.trim();
    var history = this.querySelector('[name=short_history]').value.trim();
    var country = this.querySelector('[name=country]').value.trim();
    var genre   = this.querySelector('[name=genre]').value;
    var cost    = this.querySelector('[name=cost_level]').value;
    var travel  = this.querySelector('[name=travel_medium_info]').value.trim();
    var msg = '';
    if (!title)   msg = 'Destination title is required.';
    else if (!country) msg = 'Country is required.';
    else if (history.length < 20) msg = 'Please write a more detailed description (at least 20 characters).';
    else if (!genre)  msg = 'Please select a genre.';
    else if (!cost)   msg = 'Please select a cost level.';
    else if (!travel) msg = 'Travel medium info is required.';
    if (msg) { errBox.textContent = msg; errBox.style.display = 'block'; ev.preventDefault(); }
});

// AJAX delete request
function deleteRequest(id) {
    if (!confirm('Delete this request? This cannot be undone.')) return;
    fetch('index.php?page=api&endpoint=delete_request', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'request_id=' + id
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var row = document.getElementById('req-row-' + id);
            if (row) row.style.animation = 'fadeOut .3s forwards';
            setTimeout(function(){ if(row) row.remove(); }, 300);
        }
    });
}
</script>
<style>
@keyframes fadeOut { to { opacity:0; transform:translateX(10px); } }
</style>
</body>
</html>
