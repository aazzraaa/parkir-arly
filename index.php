<?php

require_once __DIR__ . '/config/session.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {

    header('Location: login.php');
    exit;
}

switch ($_SESSION['role']) {

    case 'admin':
        header('Location: admin/dashboard.php');
        exit;

    case 'petugas':
        header('Location: petugas/dashboard.php');
        exit;

    case 'owner':
        header('Location: owner/dashboard.php');
        exit;

    default:
        session_destroy();

        header('Location: login.php');
        exit;
}