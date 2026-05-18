<?php
// ================================================================
// controllers/UserController.php  –  General user browsing
// ================================================================

function userCtrl(): void {
    requireRole('user');
    requireVerified();
    $sub = $_GET['sub'] ?? 'browse';

    switch ($sub) {
        case 'wishlist': wishlistCtrl(); break;
        case 'post':     postDetailCtrl(); break;
        default:         browseCtrl(); break;
    }
}

function browseCtrl(): void {
    $posts     = getApprovedPosts();
    $countries = getDistinctCountries();
    require 'views/user/browse.php';
}

function postDetailCtrl(): void {
    $id   = intval($_GET['id'] ?? 0);
    $post = getPostById($id);
    if (!$post || $post['status'] !== 'approved') {
        header('Location: index.php?page=user'); exit;
    }
    $comments = getCommentsByPost($id);
    $estimate = getCostEstimate($id);
    $inWish   = isInWishlist($_SESSION['user']['id'], $id);
    require 'views/user/post_detail.php';
}

/* ---- Wishlist ---- */
function wishlistCtrl(): void {
    $items = getWishlistByUser($_SESSION['user']['id']);
    require 'views/user/wishlist.php';
}

/* ---- Home page (visible to all verified users) ---- */
function homeCtrl(): void {
    $latestPosts = getApprovedPosts(6);
    require 'views/home.php';
}
