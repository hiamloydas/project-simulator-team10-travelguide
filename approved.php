<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Approved Posts — WanderGuide Scout</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>My Approved Posts</h1><p>Destinations published from your submissions</p></div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='change_sent'): ?>
        <div class="alert alert-success">Change request submitted! Admin will review it shortly.</div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">None of your submissions have been approved yet. Keep submitting!</div>
    <?php else: ?>
    <div class="posts-grid">
        <?php foreach ($posts as $post):
            $genreEmoji = ['beach'=>'🏖️','mountain'=>'🏔️','city'=>'🏙️','historical'=>'🏛️','wildlife'=>'🦁','cultural'=>'🎭','adventure'=>'🧗','other'=>'🌿'][$post['genre']] ?? '🌍';
        ?>
        <div class="post-card">
            <div class="post-card-img">
                <?php if (!empty($post['image'])): ?>
                    <img src="<?= e($post['image']) ?>" alt="<?= e($post['title']) ?>">
                <?php else: ?>
                    <span class="genre-emoji"><?= $genreEmoji ?></span>
                <?php endif; ?>
                <span class="post-card-genre"><?= e($post['genre']) ?></span>
                <span class="post-card-cost cost-<?= e($post['cost_level']) ?>"><?= ucfirst(e($post['cost_level'])) ?></span>
            </div>
            <div class="post-card-body">
                <h3><?= e($post['title']) ?></h3>
                <div class="post-card-country">📍 <?= e($post['country']) ?></div>
                <p class="post-card-excerpt"><?= e(mb_strimwidth($post['short_history'],0,110,'…')) ?></p>
            </div>
            <div class="post-card-footer">
                <span style="font-size:12px;color:var(--muted);">✅ Published <?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                <button class="btn-sm btn-edit" onclick="openChangeModal(<?= $post['id'] ?>, '<?= e(addslashes($post['title'])) ?>', '<?= e(addslashes($post['short_history'])) ?>', '<?= e($post['country']) ?>', '<?= e($post['genre']) ?>', '<?= e($post['cost_level']) ?>', '<?= e(addslashes($post['travel_medium_info'])) ?>')">Request Change</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Change Request Modal -->
<div id="changeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;align-items:center;justify-content:center;overflow-y:auto;padding:24px;">
    <div style="background:white;border-radius:20px;padding:36px;max-width:600px;width:100%;position:relative;margin:auto;">
        <button onclick="closeChangeModal()" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">✕</button>
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:6px;">Request Changes</h3>
        <p style="font-size:14px;color:var(--muted);margin-bottom:20px;">Submit updated information for admin review. The current post stays live until approved.</p>
        <form method="POST" action="index.php?page=scout&sub=requests&action=change" class="form" enctype="multipart/form-data" novalidate id="changeForm">
            <input type="hidden" name="original_post_id" id="changePostId">
            <div class="field-row">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" id="changeTitle" required>
                </div>
                <div class="field">
                    <label>Country</label>
                    <input type="text" name="country" id="changeCountry" required>
                </div>
            </div>
            <div class="field">
                <label>Description</label>
                <textarea name="short_history" id="changeHistory" rows="4" required></textarea>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Genre</label>
                    <select name="genre" id="changeGenre" required>
                        <?php foreach (['beach','mountain','city','historical','wildlife','cultural','adventure','other'] as $g): ?>
                            <option value="<?= $g ?>"><?= ucfirst($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Cost Level</label>
                    <select name="cost_level" id="changeCost" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Travel Medium</label>
                <input type="text" name="travel_medium_info" id="changeTravelInfo" required>
            </div>
            <div class="field">
                <label>New Image (optional)</label>
                <label class="file-upload-label">📷 Choose image<input type="file" name="image" accept="image/*"></label>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeChangeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Change Request</button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>
<script>
function openChangeModal(id, title, history, country, genre, cost, travel) {
    document.getElementById('changePostId').value    = id;
    document.getElementById('changeTitle').value     = title;
    document.getElementById('changeHistory').value   = history;
    document.getElementById('changeCountry').value   = country;
    document.getElementById('changeGenre').value     = genre;
    document.getElementById('changeCost').value      = cost;
    document.getElementById('changeTravelInfo').value= travel;
    document.getElementById('changeModal').style.display = 'flex';
}
function closeChangeModal() { document.getElementById('changeModal').style.display = 'none'; }
</script>
</body>
</html>
