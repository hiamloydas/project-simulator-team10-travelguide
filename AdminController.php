<?php
// ================================================================
// controllers/AdminController.php  –  Admin dashboard
// ================================================================

function adminCtrl(): void {
    requireRole('admin');
    $sub = $_GET['sub'] ?? 'dashboard';

    switch ($sub) {
        case 'users':        adminUsersCtrl();   break;
        case 'posts':        adminPostsCtrl();   break;
        case 'comments':     adminCommentsCtrl();break;
        case 'requests':     adminRequestsCtrl();break;
        default:             adminDashCtrl();    break;
    }
}

function adminDashCtrl(): void {
    $stats = getUserStats();
    require 'views/admin/dashboard.php';
}

/* ---- User Management ---- */
function adminUsersCtrl(): void {
    $error = $success = '';
    $action  = $_GET['action'] ?? 'list';
    $editing = null;

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = $_POST['role'] ?? 'user';
        if ($name === '' || $email === '' || $pass === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email.';
        } elseif (emailExists($email)) {
            $error = 'Email already registered.';
        } else {
            createUser($name, $email, $pass, $role);
            // Set verified=1 for admin-created users
            $db = getDB(); $uid = $db->lastInsertId();
            $db->prepare("UPDATE users SET is_verified=1 WHERE id=?")->execute([$uid]);
            header('Location: index.php?page=admin&sub=users&msg=added'); exit;
        }
    }

    if ($action === 'toggle') {
        $id = intval($_GET['id'] ?? 0);
        if ($id && $id !== $_SESSION['user']['id']) {
            toggleVerification($id);
        }
        header('Location: index.php?page=admin&sub=users&msg=updated'); exit;
    }

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id && $id !== $_SESSION['user']['id']) deleteUser($id);
        header('Location: index.php?page=admin&sub=users&msg=deleted'); exit;
    }

    $users = getAllUsers();
    require 'views/admin/users.php';
}

/* ---- Post Moderation ---- */
function adminPostsCtrl(): void {
    $error = $success = '';
    $action  = $_GET['action'] ?? 'list';
    $editing = null;

    if ($action === 'edit' && !$editing) {
        $id = intval($_GET['id'] ?? 0);
        $editing = getPostById($id);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id   = intval($_GET['id'] ?? 0);
        $data = [
            'title'              => trim($_POST['title'] ?? ''),
            'short_history'      => trim($_POST['short_history'] ?? ''),
            'country'            => trim($_POST['country'] ?? ''),
            'genre'              => $_POST['genre'] ?? '',
            'cost_level'         => $_POST['cost_level'] ?? '',
            'travel_medium_info' => trim($_POST['travel_medium_info'] ?? ''),
        ];
        if (in_array('', $data)) {
            $error = 'All fields are required.';
            $editing = array_merge(['id' => $id], $data);
        } else {
            if (!empty($_FILES['image']['name'])) {
                $res = handleUpload($_FILES['image'], 'posts/');
                if ($res['error']) { $error = $res['error']; $editing = array_merge(['id' => $id], $data); }
                else { $data['image'] = $res['path']; }
            }
            if (!$error) {
                updatePost($id, $data);
                header('Location: index.php?page=admin&sub=posts&msg=updated'); exit;
            }
        }
    }

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deletePost($id);
        header('Location: index.php?page=admin&sub=posts&msg=deleted'); exit;
    }

    $posts = getAllPostsAdmin();
    require 'views/admin/posts.php';
}

/* ---- Post Requests Moderation ---- */
function adminRequestsCtrl(): void {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'approve') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) approvePostRequest($id);
        header('Location: index.php?page=admin&sub=requests&msg=approved'); exit;
    }

    if ($action === 'reject') {
        $id     = intval($_GET['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) rejectPostRequest($id, $reason);
        header('Location: index.php?page=admin&sub=requests&msg=rejected'); exit;
    }

    $requests = getAllPostRequests();
    require 'views/admin/requests.php';
}

/* ---- Comment Moderation ---- */
function adminCommentsCtrl(): void {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deleteComment($id);
        header('Location: index.php?page=admin&sub=comments&msg=deleted'); exit;
    }

    $comments = getAllComments();
    require 'views/admin/comments.php';
}
