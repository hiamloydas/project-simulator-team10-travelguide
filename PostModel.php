<?php
// ================================================================
// models/PostModel.php  –  Posts & Post Requests DB functions
// ================================================================

/* -------------------- Posts -------------------- */

function getApprovedPosts(int $limit = 0, int $offset = 0): array {
    $db = getDB();
    $sql = "SELECT p.*, u.name AS scout_name FROM posts p LEFT JOIN users u ON p.scout_id = u.id WHERE p.status='approved' ORDER BY p.created_at DESC";
    if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";
    return $db->query($sql)->fetchAll();
}

function getPostById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, u.name AS scout_name FROM posts p LEFT JOIN users u ON p.scout_id = u.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function searchPosts(string $q): array {
    $db = getDB();
    $like = '%' . $q . '%';
    $stmt = $db->prepare("SELECT p.*, u.name AS scout_name FROM posts p LEFT JOIN users u ON p.scout_id = u.id WHERE p.status='approved' AND (p.title LIKE ? OR p.country LIKE ? OR p.short_history LIKE ?) ORDER BY p.created_at DESC");
    $stmt->execute([$like, $like, $like]);
    return $stmt->fetchAll();
}

function filterPosts(?string $country, ?string $genre, ?string $cost): array {
    $db = getDB();
    $where = ["p.status='approved'"];
    $params = [];
    if ($country && $country !== '') { $where[] = "p.country LIKE ?"; $params[] = '%'.$country.'%'; }
    if ($genre   && $genre !== '')   { $where[] = "p.genre = ?";      $params[] = $genre; }
    if ($cost    && $cost !== '')    { $where[] = "p.cost_level = ?"; $params[] = $cost; }
    $sql = "SELECT p.*, u.name AS scout_name FROM posts p LEFT JOIN users u ON p.scout_id = u.id WHERE " . implode(' AND ', $where) . " ORDER BY p.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function createPost(array $data): int {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, image, status) VALUES (?,?,?,?,?,?,?,?,'approved')");
    $stmt->execute([$data['scout_id'], $data['title'], $data['short_history'], $data['country'], $data['genre'], $data['cost_level'], $data['travel_medium_info'], $data['image'] ?? null]);
    $postId = (int)$db->lastInsertId();
    // Auto-create cost estimate
    $base = match($data['cost_level']) { 'low' => 500, 'high' => 3000, default => 1500 };
    $db->prepare("INSERT INTO cost_estimates (post_id, base_cost) VALUES (?,?) ON DUPLICATE KEY UPDATE base_cost=?")->execute([$postId, $base, $base]);
    return $postId;
}

function updatePost(int $id, array $data): bool {
    $db = getDB();
    $stmt = $db->prepare("UPDATE posts SET title=?, short_history=?, country=?, genre=?, cost_level=?, travel_medium_info=?, image=COALESCE(?,image) WHERE id=?");
    $ok = $stmt->execute([$data['title'], $data['short_history'], $data['country'], $data['genre'], $data['cost_level'], $data['travel_medium_info'], $data['image'] ?? null, $id]);
    if ($ok) {
        $base = match($data['cost_level']) { 'low' => 500, 'high' => 3000, default => 1500 };
        $db->prepare("INSERT INTO cost_estimates (post_id, base_cost) VALUES (?,?) ON DUPLICATE KEY UPDATE base_cost=?")->execute([$id, $base, $base]);
    }
    return $ok;
}

function deletePost(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM posts WHERE id=?");
    return $stmt->execute([$id]);
}

function getAllPostsAdmin(): array {
    $db = getDB();
    return $db->query("SELECT p.*, u.name AS scout_name FROM posts p LEFT JOIN users u ON p.scout_id = u.id ORDER BY p.created_at DESC")->fetchAll();
}

function getDistinctCountries(): array {
    $db = getDB();
    return $db->query("SELECT DISTINCT country FROM posts WHERE status='approved' ORDER BY country ASC")->fetchAll(PDO::FETCH_COLUMN);
}

function getCostEstimate(int $postId): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM cost_estimates WHERE post_id=?");
    $stmt->execute([$postId]);
    return $stmt->fetch() ?: null;
}

/* -------------------- Post Requests -------------------- */

function createPostRequest(int $scoutId, array $data): int {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO post_requests (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, image, original_post_id) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$scoutId, $data['title'], $data['short_history'], $data['country'], $data['genre'], $data['cost_level'], $data['travel_medium_info'], $data['image'] ?? null, $data['original_post_id'] ?? null]);
    return (int)$db->lastInsertId();
}

function getPostRequestsByScout(int $scoutId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM post_requests WHERE scout_id=? ORDER BY requested_at DESC");
    $stmt->execute([$scoutId]);
    return $stmt->fetchAll();
}

function getPostRequestById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT pr.*, u.name AS scout_name FROM post_requests pr JOIN users u ON pr.scout_id = u.id WHERE pr.id=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getAllPostRequests(): array {
    $db = getDB();
    return $db->query("SELECT pr.*, u.name AS scout_name FROM post_requests pr JOIN users u ON pr.scout_id = u.id ORDER BY pr.requested_at DESC")->fetchAll();
}

function getPendingPostRequests(): array {
    $db = getDB();
    return $db->query("SELECT pr.*, u.name AS scout_name FROM post_requests pr JOIN users u ON pr.scout_id = u.id WHERE pr.status='pending' ORDER BY pr.requested_at DESC")->fetchAll();
}

function updatePostRequest(int $id, array $data): bool {
    $db = getDB();
    $stmt = $db->prepare("UPDATE post_requests SET title=?, short_history=?, country=?, genre=?, cost_level=?, travel_medium_info=?, image=COALESCE(?,image) WHERE id=? AND status='pending'");
    return $stmt->execute([$data['title'], $data['short_history'], $data['country'], $data['genre'], $data['cost_level'], $data['travel_medium_info'], $data['image'] ?? null, $id]);
}

function deletePostRequest(int $id, int $scoutId): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM post_requests WHERE id=? AND scout_id=? AND status='pending'");
    return $stmt->execute([$id, $scoutId]);
}

function approvePostRequest(int $requestId): bool {
    $db = getDB();
    $req = getPostRequestById($requestId);
    if (!$req) return false;
    $db->beginTransaction();
    try {
        if ($req['original_post_id']) {
            // It's a change request – update existing post
            updatePost((int)$req['original_post_id'], $req);
        } else {
            // New post
            createPost(array_merge($req, ['scout_id' => $req['scout_id']]));
        }
        $stmt = $db->prepare("UPDATE post_requests SET status='approved' WHERE id=?");
        $stmt->execute([$requestId]);
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function rejectPostRequest(int $requestId, string $reason = ''): bool {
    $db = getDB();
    $stmt = $db->prepare("UPDATE post_requests SET status='rejected', rejection_reason=? WHERE id=?");
    return $stmt->execute([$reason, $requestId]);
}

function getApprovedPostsByScout(int $scoutId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM posts WHERE scout_id=? AND status='approved' ORDER BY created_at DESC");
    $stmt->execute([$scoutId]);
    return $stmt->fetchAll();
}
