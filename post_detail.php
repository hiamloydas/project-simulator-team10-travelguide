<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($post['title']) ?> — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">

    <!-- Back link -->
    <a href="index.php?page=user&sub=browse" style="display:inline-flex;align-items:center;gap:6px;font-size:14px;color:var(--muted);margin-bottom:18px;">
        ← Back to Browse
    </a>

    <!-- Hero Image -->
    <?php $genreEmoji = ['beach'=>'🏖️','mountain'=>'🏔️','city'=>'🏙️','historical'=>'🏛️','wildlife'=>'🦁','cultural'=>'🎭','adventure'=>'🧗','other'=>'🌿'][$post['genre']] ?? '🌍'; ?>
    <div class="post-hero">
        <?php if (!empty($post['image'])): ?>
            <img src="<?= e($post['image']) ?>" alt="<?= e($post['title']) ?>">
        <?php else: ?>
            <span class="post-hero-placeholder"><?= $genreEmoji ?></span>
        <?php endif; ?>
        <div class="post-hero-overlay">
            <h1><?= e($post['title']) ?></h1>
            <div class="post-meta-row">
                <span class="meta-chip">📍 <?= e($post['country']) ?></span>
                <span class="meta-chip">🏷️ <?= ucfirst(e($post['genre'])) ?></span>
                <span class="meta-chip">✈️ <?= e($post['travel_medium_info']) ?></span>
                <span class="meta-chip <?= ['low'=>'cost-low','medium'=>'cost-medium','high'=>'cost-high'][$post['cost_level']]??'' ?>" style="background:rgba(255,255,255,.25);">
                    💰 <?= ucfirst(e($post['cost_level'])) ?> Cost
                </span>
            </div>
        </div>
    </div>

    <div class="post-content">
        <!-- Main Content -->
        <div>
            <div class="card card-body" style="margin-bottom:24px;">
                <h2 class="card-title" style="font-size:1.3rem;">About <?= e($post['title']) ?></h2>
                <div class="post-body"><?= nl2br(e($post['short_history'])) ?></div>
                <?php if (!empty($post['scout_name'])): ?>
                    <p style="margin-top:18px;font-size:13px;color:var(--muted);border-top:1px solid #ebe4db;padding-top:12px;">
                        ✍️ Submitted by <strong><?= e($post['scout_name']) ?></strong> · Published <?= date('F j, Y', strtotime($post['created_at'])) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Comments Section -->
            <div class="card card-body">
                <h3 class="card-title">💬 Comments (<?= count($comments) ?>)</h3>

                <!-- Add Comment Form -->
                <div class="comment-form" style="margin-bottom:22px;">
                    <textarea id="commentInput" placeholder="Share your thoughts about this destination…" maxlength="1000" style="width:100%;padding:11px 14px;border:1.5px solid #ddd;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;resize:vertical;min-height:80px;transition:.2s;"></textarea>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                        <span class="char-count"><span id="charCount">0</span>/1000</span>
                        <button class="btn btn-primary" onclick="submitComment()" id="submitBtn" style="padding:9px 20px;">Post Comment</button>
                    </div>
                    <div id="commentErr" class="alert alert-error" style="display:none;margin-top:8px;"></div>
                </div>

                <!-- Comments List -->
                <div class="comments-list" id="commentsList">
                    <?php if (empty($comments)): ?>
                        <div id="noComments" style="text-align:center;padding:24px;color:var(--muted);font-style:italic;">No comments yet. Be the first to share your thoughts!</div>
                    <?php else: ?>
                        <?php foreach ($comments as $c): renderComment($c); endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Wishlist Button -->
            <div class="sidebar-card">
                <button class="wish-btn <?= $inWish?'active':'inactive' ?>" id="wishBtn" onclick="toggleWishlist(<?= $post['id'] ?>)">
                    <?= $inWish ? '❤️ In Your Wishlist' : '🤍 Add to Wishlist' ?>
                </button>
            </div>

            <!-- Cost Calculator -->
            <?php
            $baseCost = $estimate['base_cost'] ?? match($post['cost_level']){ 'low'=>500,'high'=>3000,default=>1500 };
            $currency = $estimate['currency'] ?? 'USD';
            ?>
            <div class="sidebar-card">
                <h4>💰 Cost Estimate</h4>
                <div class="cost-calc">
                    <div class="field" style="margin-bottom:10px;">
                        <label style="font-size:12px;font-weight:600;color:var(--muted);">Base cost per person/week</label>
                        <input type="text" value="<?= $currency ?> <?= number_format($baseCost,2) ?>" disabled style="background:var(--sand);color:var(--slate);padding:8px 12px;border:1.5px solid #ddd;border-radius:6px;width:100%;font-size:14px;">
                    </div>
                    <div class="field" style="margin-bottom:10px;">
                        <label style="font-size:12px;font-weight:600;color:var(--muted);">Number of Travelers</label>
                        <input type="number" id="travelers" value="1" min="1" max="20" class="cost-calc">
                    </div>
                    <div class="field" style="margin-bottom:10px;">
                        <label style="font-size:12px;font-weight:600;color:var(--muted);">Number of Days</label>
                        <input type="number" id="days" value="7" min="1" max="365" class="cost-calc">
                    </div>
                    <div class="cost-result" id="costResult">
                        <?= $currency ?> <?= number_format($baseCost,2) ?>
                        <span>Total estimated cost</span>
                    </div>
                    <p style="font-size:11px;color:var(--muted);margin-top:8px;text-align:center;">Formula: base × travelers × (days ÷ 7)</p>
                </div>
            </div>

            <!-- Post Details -->
            <div class="sidebar-card">
                <h4>📋 Quick Facts</h4>
                <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;font-size:14px;">
                    <li><span style="color:var(--muted);">Country:</span> <strong><?= e($post['country']) ?></strong></li>
                    <li><span style="color:var(--muted);">Genre:</span> <strong><?= ucfirst(e($post['genre'])) ?></strong></li>
                    <li><span style="color:var(--muted);">Budget:</span> <strong><?= ucfirst(e($post['cost_level'])) ?></strong></li>
                    <li><span style="color:var(--muted);">Travel:</span> <strong><?= e($post['travel_medium_info']) ?></strong></li>
                    <li><span style="color:var(--muted);">Comments:</span> <strong><?= count($comments) ?></strong></li>
                </ul>
            </div>
        </div>
    </div>
</main>
<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>

<script>
var POST_ID  = <?= $post['id'] ?>;
var BASE_COST = <?= floatval($baseCost) ?>;
var CURRENCY = '<?= e($currency) ?>';
var MY_ID    = <?= $_SESSION['user']['id'] ?>;
var MY_NAME  = '<?= e(addslashes($_SESSION['user']['name'])) ?>';
var MY_PIC   = '<?= e(addslashes($_SESSION['user']['picture'] ?? '')) ?>';

// ---- Cost Calculator ----
function calcCost() {
    var t = parseInt(document.getElementById('travelers').value) || 1;
    var d = parseInt(document.getElementById('days').value)     || 7;
    t = Math.max(1, Math.min(20, t));
    d = Math.max(1, Math.min(365, d));
    var total = BASE_COST * t * (d / 7);
    document.getElementById('costResult').innerHTML =
        CURRENCY + ' ' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) +
        '<span>Total estimated cost</span>';
}
document.getElementById('travelers').addEventListener('input', calcCost);
document.getElementById('days').addEventListener('input', calcCost);

// ---- Wishlist ----
var inWishlist = <?= $inWish ? 'true' : 'false' ?>;
function toggleWishlist(postId) {
    var endpoint = inWishlist ? 'wishlist_remove' : 'wishlist_add';
    fetch('index.php?page=api&endpoint=' + endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + postId
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success !== undefined) {
            inWishlist = d.in_wishlist;
            var btn = document.getElementById('wishBtn');
            btn.textContent = inWishlist ? '❤️ In Your Wishlist' : '🤍 Add to Wishlist';
            btn.className   = 'wish-btn ' + (inWishlist ? 'active' : 'inactive');
        }
    });
}

// ---- Comments ----
var charCountEl = document.getElementById('charCount');
document.getElementById('commentInput').addEventListener('input', function() {
    charCountEl.textContent = this.value.length;
});

function submitComment() {
    var content = document.getElementById('commentInput').value.trim();
    var errBox  = document.getElementById('commentErr');
    errBox.style.display = 'none';

    if (!content) { errBox.textContent = 'Please write something before posting.'; errBox.style.display = 'block'; return; }
    if (content.length > 1000) { errBox.textContent = 'Comment is too long (max 1000 characters).'; errBox.style.display = 'block'; return; }

    var btn = document.getElementById('submitBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';

    fetch('index.php?page=api&endpoint=add_comment', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + POST_ID + '&content=' + encodeURIComponent(content)
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        btn.disabled = false; btn.textContent = 'Post Comment';
        if (d.error) { errBox.textContent = d.error; errBox.style.display = 'block'; return; }

        // Remove "no comments" placeholder
        var noEl = document.getElementById('noComments');
        if (noEl) noEl.remove();

        // Build comment HTML
        var initials = MY_NAME.charAt(0).toUpperCase();
        var avHtml = MY_PIC
            ? '<img src="' + esc(MY_PIC) + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">'
            : initials;
        var dateStr = new Date().toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
        var html =
            '<div class="comment" id="cmt-'+d.id+'">'+
            '<div class="comment-av">'+avHtml+'</div>'+
            '<div class="comment-body">'+
            '<div class="comment-header">'+
            '<span class="comment-name">'+esc(d.name)+'</span>'+
            '<div style="display:flex;align-items:center;gap:8px;">'+
            '<span class="comment-date">'+dateStr+'</span>'+
            '<button class="btn-sm btn-del" onclick="deleteComment('+d.id+')" style="padding:3px 8px;font-size:11px;">Delete</button>'+
            '</div></div>'+
            '<p class="comment-text">'+esc(d.content)+'</p>'+
            '</div></div>';

        document.getElementById('commentsList').insertAdjacentHTML('afterbegin', html);
        document.getElementById('commentInput').value = '';
        charCountEl.textContent = '0';
    })
    .catch(function() { btn.disabled=false; btn.textContent='Post Comment'; errBox.textContent='Failed to post. Try again.'; errBox.style.display='block'; });
}

function deleteComment(id) {
    if (!confirm('Delete your comment?')) return;
    fetch('index.php?page=api&endpoint=delete_comment', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + id
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var el = document.getElementById('cmt-' + id);
            if (el) { el.style.opacity='0'; el.style.transition='.3s'; setTimeout(function(){ el.remove(); }, 300); }
        }
    });
}

function esc(s) { var d=document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }
</script>
</body>
</html>

<?php
function renderComment(array $c): void {
    $initials = strtoupper(substr($c['name'],0,1));
    $uid = $_SESSION['user']['id'];
    $isOwn = (int)$c['user_id'] === (int)$uid;
    ?>
    <div class="comment" id="cmt-<?= $c['id'] ?>">
        <div class="comment-av">
            <?php if (!empty($c['profile_picture'])): ?>
                <img src="<?= e($c['profile_picture']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
            <?php else: ?>
                <?= $initials ?>
            <?php endif; ?>
        </div>
        <div class="comment-body">
            <div class="comment-header">
                <span class="comment-name"><?= e($c['name']) ?></span>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="comment-date"><?= date('M j, Y', strtotime($c['created_at'])) ?></span>
                    <?php if ($isOwn): ?>
                        <button class="btn-sm btn-del" onclick="deleteComment(<?= $c['id'] ?>)" style="padding:3px 8px;font-size:11px;">Delete</button>
                    <?php endif; ?>
                </div>
            </div>
            <p class="comment-text"><?= nl2br(e($c['content'])) ?></p>
        </div>
    </div>
    <?php
}
