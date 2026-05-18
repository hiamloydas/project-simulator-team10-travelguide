<?php
// ================================================================
// index.php  –  Front Controller (Router)
// ================================================================
session_start();

require_once 'config/db.php';
require_once 'models/UserModel.php';
require_once 'models/PostModel.php';
require_once 'models/CommentModel.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/ScoutController.php';
require_once 'controllers/UserController.php';

// Auto-seed admin
seedAdmin();

// Restore session from remember-me cookie
if (!isset($_SESSION['user']) && !empty($_COOKIE['tg_remember'])) {
    $u = getUserByToken($_COOKIE['tg_remember']);
    if ($u) {
        $_SESSION['user'] = [
            'id'          => $u['id'],
            'name'        => $u['name'],
            'email'       => $u['email'],
            'role'        => $u['role'],
            'is_verified' => $u['is_verified'],
            'picture'     => $u['profile_picture'],
        ];
    }
}

$page = $_GET['page'] ?? 'home';

/* ---------- Helpers ---------- */
function requireAuth(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?page=login'); exit;
    }
}
function requireRole(string $role): void {
    requireAuth();
    if ($_SESSION['user']['role'] !== $role) {
        header('Location: index.php?page=login'); exit;
    }
}
function requireVerified(): void {
    if (!$_SESSION['user']['is_verified']) {
        header('Location: index.php?page=pending'); exit;
    }
}
function e(mixed $v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        require_once 'config/db.php';
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/* ---------- Logout ---------- */
if ($page === 'logout') {
    if (isset($_SESSION['user'])) clearRememberToken($_SESSION['user']['id']);
    $_SESSION = []; session_destroy();
    setcookie('tg_remember', '', time() - 3600, '/');
    header('Location: index.php?page=login'); exit;
}

/* ========== AJAX endpoints ========== */
if ($page === 'api') {
    header('Content-Type: application/json');
    $endpoint = $_GET['endpoint'] ?? '';

    /* -- Search posts -- */
    if ($endpoint === 'search_posts') {
        requireAuth();
        $q = trim($_GET['q'] ?? '');
        echo json_encode($q !== '' ? searchPosts($q) : getApprovedPosts());
        exit;
    }

    /* -- Filter posts -- */
    if ($endpoint === 'filter_posts') {
        requireAuth();
        echo json_encode(filterPosts($_GET['country'] ?? '', $_GET['genre'] ?? '', $_GET['cost'] ?? ''));
        exit;
    }

    /* -- Wishlist add -- */
    if ($endpoint === 'wishlist_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requireRole('user');
        requireVerified();
        $postId = intval($_POST['post_id'] ?? 0);
        if (!$postId) { http_response_code(400); echo json_encode(['error'=>'Invalid post.']); exit; }
        $ok = addToWishlist($_SESSION['user']['id'], $postId);
        echo json_encode(['success' => $ok, 'in_wishlist' => true]);
        exit;
    }

    /* -- Wishlist remove -- */
    if ($endpoint === 'wishlist_remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requireRole('user');
        requireVerified();
        $postId = intval($_POST['post_id'] ?? 0);
        $ok = removeFromWishlist($_SESSION['user']['id'], $postId);
        echo json_encode(['success' => $ok, 'in_wishlist' => false]);
        exit;
    }

    /* -- Add comment -- */
    if ($endpoint === 'add_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requireRole('user');
        requireVerified();
        $postId  = intval($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if ($postId < 1 || $content === '') { http_response_code(400); echo json_encode(['error'=>'Invalid data.']); exit; }
        if (strlen($content) > 1000) { http_response_code(400); echo json_encode(['error'=>'Comment too long (max 1000 chars).']); exit; }
        $id = addComment($postId, $_SESSION['user']['id'], $content);
        $u  = $_SESSION['user'];
        echo json_encode(['success'=>true,'id'=>$id,'name'=>$u['name'],'content'=>$content,'picture'=>$u['picture'],'created_at'=>date('Y-m-d H:i:s')]);
        exit;
    }

    /* -- Delete comment -- */
    if ($endpoint === 'delete_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requireAuth();
        $id     = intval($_POST['comment_id'] ?? 0);
        $userId = ($_SESSION['user']['role'] === 'admin') ? null : $_SESSION['user']['id'];
        $ok = deleteComment($id, $userId);
        echo json_encode(['success' => $ok]);
        exit;
    }

    /* -- Admin: toggle verification -- */
    if ($endpoint === 'toggle_verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requireRole('admin');
        $id = intval($_POST['user_id'] ?? 0);
        if ($id && $id !== $_SESSION['user']['id']) {
            toggleVerification($id);
            $u = getUserById($id);
            echo json_encode(['success'=>true,'is_verified'=>$u['is_verified']]);
        } else {
            echo json_encode(['success'=>false]);
        }
        exit;
    }

    /* -- Scout: delete request -- */
    if ($endpoint === 'delete_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requireRole('scout');
        $id = intval($_POST['request_id'] ?? 0);
        $ok = deletePostRequest($id, $_SESSION['user']['id']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found.']);
    exit;
}

/* ---------- Auth gates ---------- */
$publicPages = ['login', 'register', 'home', 'pending'];

if (in_array($page, ['login','register']) && isset($_SESSION['user'])) {
    header('Location: index.php?page=' . $_SESSION['user']['role']); exit;
}

if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login'); exit;
}

// Role gates
if ($page === 'admin'   && ($_SESSION['user']['role'] ?? '') !== 'admin')   { header('Location: index.php?page=login'); exit; }
if ($page === 'scout'   && ($_SESSION['user']['role'] ?? '') !== 'scout')   { header('Location: index.php?page=login'); exit; }
if ($page === 'user'    && ($_SESSION['user']['role'] ?? '') !== 'user')    { header('Location: index.php?page=login'); exit; }
if ($page === 'profile' && !isset($_SESSION['user']))                        { header('Location: index.php?page=login'); exit; }

/* ---------- Dispatch ---------- */
switch ($page) {
    case 'home':     homeCtrl();       break;
    case 'login':    loginCtrl();      break;
    case 'register': registerCtrl();   break;
    case 'profile':  profileCtrl();    break;
    case 'admin':    adminCtrl();      break;
    case 'scout':    scoutCtrl();      break;
    case 'user':     userCtrl();       break;
    case 'pending':
        require 'views/auth/pending.php';
        break;
    default:
        header('Location: index.php?page=home'); exit;
}
?>
