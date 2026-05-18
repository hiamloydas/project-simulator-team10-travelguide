<?php
// ================================================================
// controllers/AuthController.php  –  Login, Register, Logout, Profile
// ================================================================

function loginCtrl(): void {
    $error = '';
    $prefill = $_COOKIE['tg_remember'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $error = 'Please fill in both fields.';
        } else {
            $user = getUserByEmail($email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id'          => $user['id'],
                    'name'        => $user['name'],
                    'email'       => $user['email'],
                    'role'        => $user['role'],
                    'is_verified' => $user['is_verified'],
                    'picture'     => $user['profile_picture'],
                ];
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setRememberToken($user['id'], $token);
                    setcookie('tg_remember', $token, time() + 86400 * 30, '/', '', false, true);
                } else {
                    setcookie('tg_remember', '', time() - 3600, '/');
                }
                header('Location: index.php?page=' . $user['role']);
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }
    require 'views/auth/login.php';
}

function registerCtrl(): void {
    $error = $success = '';
    $old = ['name' => '', 'email' => '', 'role' => 'user'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $old = compact('name', 'email', 'role');

        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['scout', 'user'])) {
            $error = 'Invalid role selected.';
        } elseif (emailExists($email)) {
            $error = 'That email is already registered.';
        } else {
            createUser($name, $email, $password, $role);
            $success = 'Account created! An admin will verify it shortly. You may then log in.';
            $old = ['name' => '', 'email' => '', 'role' => 'user'];
        }
    }
    require 'views/auth/register.php';
}

function profileCtrl(): void {
    requireAuth();
    $user  = getUserById($_SESSION['user']['id']);
    $error = $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'profile';

        if ($action === 'profile') {
            $name    = trim($_POST['name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $picture = null;

            if ($name === '' || $email === '') { $error = 'Name and email are required.'; }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email.'; }
            elseif (emailExists($email, $user['id'])) { $error = 'Email already in use.'; }
            else {
                // Handle file upload
                if (!empty($_FILES['picture']['name'])) {
                    $result = handleUpload($_FILES['picture'], 'profiles/');
                    if ($result['error']) { $error = $result['error']; }
                    else { $picture = $result['path']; }
                }
                if (!$error) {
                    updateUserProfile($user['id'], $name, $email, $picture);
                    $_SESSION['user']['name']    = $name;
                    $_SESSION['user']['email']   = $email;
                    if ($picture) $_SESSION['user']['picture'] = $picture;
                    $user = getUserById($user['id']);
                    $success = 'Profile updated successfully.';
                }
            }
        } elseif ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_new'] ?? '';

            if ($current === '' || $new === '' || $confirm === '') { $error = 'All password fields are required.'; }
            elseif (!password_verify($current, $user['password_hash'] ?? '')) {
                // Re-fetch with hash
                $full = getUserByEmail($_SESSION['user']['email']);
                if (!$full || !password_verify($current, $full['password_hash'])) {
                    $error = 'Current password is incorrect.';
                }
            }
            if (!$error) {
                if (strlen($new) < 8) { $error = 'New password must be at least 8 characters.'; }
                elseif ($new !== $confirm) { $error = 'New passwords do not match.'; }
                else {
                    updateUserPassword($user['id'], $new);
                    $success = 'Password changed successfully.';
                }
            }
        }
    }
    require 'views/auth/profile.php';
}

function handleUpload(array $file, string $subdir): array {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed.', 'path' => null];
    if ($file['size'] > MAX_FILE_SIZE) return ['error' => 'File too large (max 5 MB).', 'path' => null];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_MIME)) return ['error' => 'Invalid file type. Use JPG, PNG, or WebP.', 'path' => null];
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $ext;
    $dir      = UPLOAD_DIR . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    move_uploaded_file($file['tmp_name'], $dir . $filename);
    return ['error' => null, 'path' => UPLOAD_URL . $subdir . $filename];
}
