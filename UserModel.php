<?php
// ================================================================
// models/UserModel.php  –  All user-related DB functions
// ================================================================

function getUserById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, role, is_verified, profile_picture, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getUserByEmail(string $email): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() ?: null;
}

function getUserByToken(string $token): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([hash('sha256', $token)]);
    return $stmt->fetch() ?: null;
}

function createUser(string $name, string $email, string $password, string $role): int {
    $db = getDB();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?,?,?,?,?)");
    $verified = ($role === 'admin') ? 1 : 0;
    $stmt->execute([$name, $email, $hash, $role, $verified]);
    return (int)$db->lastInsertId();
}

function updateUserProfile(int $id, string $name, string $email, ?string $picture): bool {
    $db = getDB();
    if ($picture) {
        $stmt = $db->prepare("UPDATE users SET name=?, email=?, profile_picture=? WHERE id=?");
        return $stmt->execute([$name, $email, $picture, $id]);
    }
    $stmt = $db->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    return $stmt->execute([$name, $email, $id]);
}

function updateUserPassword(int $id, string $newPassword): bool {
    $db = getDB();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password_hash=? WHERE id=?");
    return $stmt->execute([$hash, $id]);
}

function setRememberToken(int $id, string $token): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET remember_token=? WHERE id=?");
    $stmt->execute([hash('sha256', $token), $id]);
}

function clearRememberToken(int $id): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET remember_token=NULL WHERE id=?");
    $stmt->execute([$id]);
}

function getAllUsers(): array {
    $db = getDB();
    return $db->query("SELECT id, name, email, role, is_verified, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC")->fetchAll();
}

function getAllUsersWithAdmin(): array {
    $db = getDB();
    return $db->query("SELECT id, name, email, role, is_verified, created_at FROM users ORDER BY created_at DESC")->fetchAll();
}

function toggleVerification(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET is_verified = 1 - is_verified WHERE id=?");
    return $stmt->execute([$id]);
}

function deleteUser(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    return $stmt->execute([$id]);
}

function emailExists(string $email, ?int $excludeId = null): bool {
    $db = getDB();
    if ($excludeId) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email=? AND id != ?");
        $stmt->execute([$email, $excludeId]);
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
    }
    return (bool)$stmt->fetch();
}

function getUserStats(): array {
    $db = getDB();
    $stats = [];
    $stats['total_users']    = $db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
    $stats['scouts']         = $db->query("SELECT COUNT(*) FROM users WHERE role='scout'")->fetchColumn();
    $stats['general_users']  = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
    $stats['pending_verify'] = $db->query("SELECT COUNT(*) FROM users WHERE is_verified=0 AND role != 'admin'")->fetchColumn();
    $stats['pending_posts']  = $db->query("SELECT COUNT(*) FROM post_requests WHERE status='pending'")->fetchColumn();
    $stats['total_posts']    = $db->query("SELECT COUNT(*) FROM posts WHERE status='approved'")->fetchColumn();
    $stats['total_comments'] = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    return $stats;
}
