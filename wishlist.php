<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Wishlist — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div>
            <h1>My Wishlist ❤️</h1>
            <p><?= count($items) ?> saved destination<?= count($items)!==1?'s':'' ?></p>
        </div>
        <a href="index.php?page=user&sub=browse" class="btn btn-ghost">← Browse More</a>
    </div>

    <?php if (empty($items)): ?>
        <div style="text-align:center;padding:72px 24px;">
            <div style="font-size:72px;margin-bottom:16px;">🗺️</div>
            <h2 style="margin-bottom:12px;">Your wishlist is empty</h2>
            <p style="color:var(--muted);margin-bottom:28px;">Start browsing destinations and tap the heart to save your favourites.</p>
            <a href="index.php?page=user&sub=browse" class="btn btn-primary" style="font-size:15px;padding:13px 28px;">Browse Destinations</a>
        </div>
    <?php else: ?>
    <div class="wish-grid">
        <?php foreach ($items as $item):
            $genreEmoji = ['beach'=>'🏖️','mountain'=>'🏔️','city'=>'🏙️','historical'=>'🏛️','wildlife'=>'🦁','cultural'=>'🎭','adventure'=>'🧗','other'=>'🌿'][$item['genre']] ?? '🌍';
        ?>
        <div class="wish-item" id="wish-<?= $item['post_id'] ?>">
            <div class="wish-item-img">
                <?php if (!empty($item['image'])): ?>
                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>">
                <?php else: ?>
                    <?= $genreEmoji ?>
                <?php endif; ?>
            </div>
            <div class="wish-item-body">
                <h4><?= e($item['title']) ?></h4>
                <div style="font-size:13px;color:var(--muted);margin-top:4px;">📍 <?= e($item['country']) ?></div>
                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                    <span class="badge badge-sand" style="font-size:11px;"><?= ucfirst(e($item['genre'])) ?></span>
                    <span class="badge <?= ['low'=>'badge-green','medium'=>'badge-yellow','high'=>'badge-red'][$item['cost_level']]??'badge-sand' ?>" style="font-size:11px;"><?= ucfirst(e($item['cost_level'])) ?> Cost</span>
                </div>
            </div>
            <div class="wish-item-foot">
                <a href="index.php?page=user&sub=post&id=<?= $item['post_id'] ?>" class="btn-sm btn-view">View</a>
                <button class="btn-sm btn-del" onclick="removeWish(<?= $item['post_id'] ?>)">❤️ Remove</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>

<script>
function removeWish(postId) {
    if (!confirm('Remove from wishlist?')) return;
    fetch('index.php?page=api&endpoint=wishlist_remove', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'post_id=' + postId
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var el = document.getElementById('wish-' + postId);
            if (el) { el.style.transition = 'opacity .3s, transform .3s'; el.style.opacity = '0'; el.style.transform = 'scale(.9)'; setTimeout(function(){ el.remove(); }, 320); }
        }
    });
}
</script>
</body>
</html>
