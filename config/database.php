<?php

/**
 * Konfigurasi database Sistem Parkir
 */

$host = 'localhost';
$db   = 'db_parkir';

$admin_username   = 'admin_parkir';
$admin_password   = 'Admin123!';

$petugas_username = 'petugas_parkir';
$petugas_password = 'Petugas123!';

$owner_username   = 'owner_parkir';
$owner_password   = 'Owner123!';


/**
 * Membuat koneksi berdasarkan role
 */
function getDatabaseConnection($role)
{
    global $host;
    global $db;

    global $admin_username;
    global $admin_password;

    global $petugas_username;
    global $petugas_password;

    global $owner_username;
    global $owner_password;

    switch ($role) {

        case 'admin':
            $username = $admin_username;
            $password = $admin_password;
            break;

        case 'petugas':
            $username = $petugas_username;
            $password = $petugas_password;
            break;

        case 'owner':
            $username = $owner_username;
            $password = $owner_password;
            break;

        default:
            throw new Exception('Role tidak valid.');
    }

    try {

        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $username,
            $password
        );

        $pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $pdo->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );

        return $pdo;

    } catch (PDOException $e) {

        die(
            "Koneksi database gagal: " .
            $e->getMessage()
        );
    }
}