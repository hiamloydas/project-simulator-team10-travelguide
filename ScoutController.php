<?php
// ================================================================
// controllers/ScoutController.php  –  Scout post requests
// ================================================================

function scoutCtrl(): void {
    requireRole('scout');
    requireVerified();
    $sub = $_GET['sub'] ?? 'requests';

    switch ($sub) {
        case 'approved': scoutApprovedCtrl(); break;
        default:         scoutRequestsCtrl(); break;
    }
}

function scoutRequestsCtrl(): void {
    $error  = '';
    $action  = $_GET['action'] ?? 'list';
    $editing = null;

    /* --- Create --- */
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = buildPostData();
        if ($data['error']) {
            $error = $data['error'];
        } else {
            createPostRequest($_SESSION['user']['id'], $data);
            header('Location: index.php?page=scout&sub=requests&msg=added'); exit;
        }
    }

    /* --- Edit form --- */
    if ($action === 'edit' && !$editing) {
        $id = intval($_GET['id'] ?? 0);
        $req = getPostRequestById($id);
        // Scout can only edit their own pending requests
        if ($req && (int)$req['scout_id'] === $_SESSION['user']['id'] && $req['status'] === 'pending') {
            $editing = $req;
        }
    }

    /* --- Update --- */
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_GET['id'] ?? 0);
        $data = buildPostData();
        if ($data['error']) {
            $error = $data['error'];
            $editing = array_merge(['id' => $id], $data);
        } else {
            updatePostRequest($id, $data);
            header('Location: index.php?page=scout&sub=requests&msg=updated'); exit;
        }
    }

    /* --- Delete --- */
    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        deletePostRequest($id, $_SESSION['user']['id']);
        header('Location: index.php?page=scout&sub=requests&msg=deleted'); exit;
    }

    /* --- Change request for approved post --- */
    if ($action === 'change' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $originalId = intval($_POST['original_post_id'] ?? 0);
        $data = buildPostData();
        $data['original_post_id'] = $originalId;
        if ($data['error']) {
            $error = $data['error'];
        } else {
            createPostRequest($_SESSION['user']['id'], $data);
            header('Location: index.php?page=scout&sub=approved&msg=change_sent'); exit;
        }
    }

    $requests = getPostRequestsByScout($_SESSION['user']['id']);
    require 'views/scout/requests.php';
}

function scoutApprovedCtrl(): void {
    $posts = getApprovedPostsByScout($_SESSION['user']['id']);
    require 'views/scout/approved.php';
}

function buildPostData(): array {
    $title    = trim($_POST['title'] ?? '');
    $history  = trim($_POST['short_history'] ?? '');
    $country  = trim($_POST['country'] ?? '');
    $genre    = $_POST['genre'] ?? '';
    $cost     = $_POST['cost_level'] ?? '';
    $travel   = trim($_POST['travel_medium_info'] ?? '');

    $validGenres = ['beach','mountain','city','historical','wildlife','cultural','adventure','other'];
    $validCosts  = ['low','medium','high'];

    $data = ['title'=>$title,'short_history'=>$history,'country'=>$country,'genre'=>$genre,'cost_level'=>$cost,'travel_medium_info'=>$travel,'image'=>null,'error'=>null,'original_post_id'=>null];

    if ($title===''||$history===''||$country===''||$genre===''||$cost===''||$travel==='') {
        $data['error'] = 'All fields are required.';
    } elseif (!in_array($genre, $validGenres)) {
        $data['error'] = 'Invalid genre.';
    } elseif (!in_array($cost, $validCosts)) {
        $data['error'] = 'Invalid cost level.';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $res = handleUpload($_FILES['image'], 'posts/');
            if ($res['error']) { $data['error'] = $res['error']; }
            else { $data['image'] = $res['path']; }
        }
    }
    return $data;
}
