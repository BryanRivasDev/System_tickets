<?php
// index.php
include 'includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin() || isTecnico()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>