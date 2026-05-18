<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Destinations — WanderGuide</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-body">
<?php require 'views/partials/navbar.php'; ?>
<main class="main">
    <div class="page-head">
        <div><h1>Browse Destinations</h1><p>Discover <?= count($posts) ?> curated travel destinations worldwide</p></div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="field" style="flex:2;min-width:200px;">
            <label>🔍 Search</label>
            <input type="text" id="searchQ" class="search-input" placeholder="Search by title, country or description…" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:8px;width:100%;font-size:14px;font-family:'DM Sans',sans-serif;">
        </div>
        <div class="field">
            <label>🌍 Country</label>
            <select id="filterCountry">
                <option value="">All Countries</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= e($c) ?>"><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>🏷️ Genre</label>
            <select id="filterGenre">
                <option value="">All Genres</option>
                <?php foreach (['beach','mountain','city','historical','wildlife','cultural','adventure','other'] as $g): ?>
                    <option value="<?= $g ?>"><?= ucfirst($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>💰 Budget</label>
            <select id="filterCost">
                <option value="">All Budgets</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </div>
        <button class="btn btn-ghost" onclick="clearFilters()" style="align-self:flex-end;">Clear</button>
    </div>

    <!-- Loading indicator -->
    <div id="loadingBar" style="display:none;text-align:center;padding:12px;color:var(--muted);font-size:14px;">
        <span class="spinner"></span> Searching…
    </div>

    <!-- Result count -->
    <div style="margin-bottom:16px;font-size:14px;color:var(--muted);" id="resultInfo">
        Showing <strong id="resultCount"><?= count($posts) ?></strong> destinations
    </div>

    <!-- Posts Grid -->
    <div class="posts-grid" id="postsGrid">
        <?php if (empty($posts)): ?>
            <div style="grid-column:1/-1;" class="alert alert-info">No destinations found. Check back soon!</div>
        <?php else: ?>
        <?php foreach ($posts as $post): renderPostCard($post); endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<footer class="footer">&copy; <?= date('Y') ?> WanderGuide</footer>

<script>
var timer = null;
var genreEmoji = {beach:'🏖️',mountain:'🏔️',city:'🏙️',historical:'🏛️',wildlife:'🦁',cultural:'🎭',adventure:'🧗',other:'🌿'};

function renderCards(posts) {
    var grid = document.getElementById('postsGrid');
    document.getElementById('resultCount').textContent = posts.length;
    if (!posts.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:48px 20px;color:var(--muted);font-style:italic;">No destinations match your search.</div>';
        return;
    }
    var html = '';
    posts.forEach(function(p) {
        var emoji = genreEmoji[p.genre] || '🌍';
        var imgEl = p.image
            ? '<img src="'+esc(p.image)+'" alt="'+esc(p.title)+'">'
            : '<span class="genre-emoji">'+emoji+'</span>';
        var costCls = {low:'cost-low',medium:'cost-medium',high:'cost-high'}[p.cost_level]||'';
        html +=
            '<div class="post-card">'+
            '<div class="post-card-img">'+imgEl+
            '<span class="post-card-genre">'+esc(p.genre)+'</span>'+
            '<span class="post-card-cost '+costCls+'">'+cap(p.cost_level)+'</span>'+
            '</div>'+
            '<div class="post-card-body">'+
            '<h3>'+esc(p.title)+'</h3>'+
            '<div class="post-card-country">📍 '+esc(p.country)+'</div>'+
            '<p class="post-card-excerpt">'+esc(p.short_history.substring(0,110))+(p.short_history.length>110?'…':'')+'</p>'+
            '</div>'+
            '<div class="post-card-footer">'+
            '<span class="post-travel-info">✈️ '+esc(p.travel_medium_info)+'</span>'+
            '<a href="index.php?page=user&sub=post&id='+p.id+'" class="btn-sm btn-view">Read More</a>'+
            '</div></div>';
    });
    grid.innerHTML = html;
}

function doSearch() {
    clearTimeout(timer);
    timer = setTimeout(function() {
        var q       = document.getElementById('searchQ').value.trim();
        var country = document.getElementById('filterCountry').value;
        var genre   = document.getElementById('filterGenre').value;
        var cost    = document.getElementById('filterCost').value;
        document.getElementById('loadingBar').style.display = 'block';

        var url, mode;
        if (q) {
            url  = 'index.php?page=api&endpoint=search_posts&q=' + encodeURIComponent(q);
            mode = 'search';
        } else {
            url  = 'index.php?page=api&endpoint=filter_posts&country='+encodeURIComponent(country)+'&genre='+encodeURIComponent(genre)+'&cost='+encodeURIComponent(cost);
            mode = 'filter';
        }

        fetch(url, {credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(data){ document.getElementById('loadingBar').style.display='none'; renderCards(data); })
            .catch(function(){ document.getElementById('loadingBar').style.display='none'; });
    }, 220);
}

function clearFilters() {
    document.getElementById('searchQ').value        = '';
    document.getElementById('filterCountry').value  = '';
    document.getElementById('filterGenre').value    = '';
    document.getElementById('filterCost').value     = '';
    doSearch();
}

['searchQ','filterCountry','filterGenre','filterCost'].forEach(function(id){
    document.getElementById(id).addEventListener('input', doSearch);
    document.getElementById(id).addEventListener('change', doSearch);
});

function esc(s) { var d=document.createElement('div'); d.appendChild(document.createTextNode(s||'')); return d.innerHTML; }
function cap(s) { return s ? s.charAt(0).toUpperCase()+s.slice(1) : ''; }
</script>
</body>
</html>

<?php
function renderPostCard(array $post): void {
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
            <span class="post-travel-info">✈️ <?= e($post['travel_medium_info']) ?></span>
            <a href="index.php?page=user&sub=post&id=<?= $post['id'] ?>" class="btn-sm btn-view">Read More</a>
        </div>
    </div>
    <?php
}
