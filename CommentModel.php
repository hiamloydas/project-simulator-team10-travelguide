<?php
// ================================================================
// models/CommentModel.php  –  Comments & Wishlist DB functions
// ================================================================

/* -------------------- Comments -------------------- */

function getCommentsByPost(int $postId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT c.*, u.name, u.profile_picture FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id=? ORDER BY c.created_at ASC");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function getAllComments(): array {
    $db = getDB();
    return $db->query("SELECT c.*, u.name AS user_name, p.title AS post_title FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC")->fetchAll();
}

function addComment(int $postId, int $userId, string $content): int {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?,?,?)");
    $stmt->execute([$postId, $userId, $content]);
    return (int)$db->lastInsertId();
}

function deleteComment(int $id, ?int $userId = null): bool {
    $db = getDB();
    if ($userId !== null) {
        // User can only delete own comment
        $stmt = $db->prepare("DELETE FROM comments WHERE id=? AND user_id=?");
        return $stmt->execute([$id, $userId]);
    }
    // Admin can delete any
    $stmt = $db->prepare("DELETE FROM comments WHERE id=?");
    return $stmt->execute([$id]);
}

function getCommentById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM comments WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/* -------------------- Wishlist -------------------- */

function getWishlistByUser(int $userId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT w.*, p.title, p.country, p.genre, p.cost_level, p.image FROM wishlist w JOIN posts p ON w.post_id = p.id WHERE w.user_id=? ORDER BY w.added_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function isInWishlist(int $userId, int $postId): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id=? AND post_id=?");
    $stmt->execute([$userId, $postId]);
    return (bool)$stmt->fetch();
}

function addToWishlist(int $userId, int $postId): bool {
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO wishlist (user_id, post_id) VALUES (?,?)");
        return $stmt->execute([$userId, $postId]);
    } catch (PDOException $e) {
        return false; // duplicate
    }
}

function removeFromWishlist(int $userId, int $postId): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id=? AND post_id=?");
    return $stmt->execute([$userId, $postId]);
}
