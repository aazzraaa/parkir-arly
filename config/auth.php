<?php

require_once __DIR__ . '/session.php';


/**
 * Mengecek apakah user sudah login
 */
function isLoggedIn()
{
    return isset($_SESSION['logged_in']) &&
           $_SESSION['logged_in'] === true;
}


/**
 * Mengecek role user
 */
function requireRole($role)
{
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }

    if ($_SESSION['role'] !== $role) {
        http_response_code(403);

        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Akses Ditolak</title>
        </head>
        <body>
            <h1>403 - Akses Ditolak</h1>
            <p>Anda tidak memiliki hak untuk mengakses halaman ini.</p>
            <a href="../index.php">Kembali</a>
        </body>
        </html>
        ';

        exit;
    }
}


/**
 * Mengambil nama user yang sedang login
 */
function getNamaUser()
{
    return $_SESSION['nama_lengkap'] ?? '';
}


/**
 * Mengambil role user
 */
function getRoleUser()
{
    return $_SESSION['role'] ?? '';
}