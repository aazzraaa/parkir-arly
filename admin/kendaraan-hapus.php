<?php

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/auth.php';

requireRole('admin');

$pdo = getDatabaseConnection('admin');

$namaUser = getNamaUser();


// ======================================================
// AMBIL ID KENDARAAN
// ======================================================

$idKendaraan = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($idKendaraan <= 0) {
    header('Location: kendaraan.php');
    exit;
}


// ======================================================
// AMBIL DATA KENDARAAN
// ======================================================

$stmt = $pdo->prepare("
    SELECT
        id_kendaraan,
        plat_nomor,
        pemilik
    FROM tb_kendaraan
    WHERE id_kendaraan = ?
    LIMIT 1
");

$stmt->execute([$idKendaraan]);

$kendaraan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kendaraan) {
    header('Location: kendaraan.php');
    exit;
}


// ======================================================
// CEK APAKAH SUDAH DIPAKAI TRANSAKSI
// ======================================================

$stmtCek = $pdo->prepare("
    SELECT COUNT(*)
    FROM tb_transaksi
    WHERE id_kendaraan = ?
");

$stmtCek->execute([$idKendaraan]);

$jumlahTransaksi = $stmtCek->fetchColumn();


// ======================================================
// JIKA MASIH DIPAKAI TRANSAKSI
// ======================================================

if ($jumlahTransaksi > 0) {

    header('Location: kendaraan.php?status=gagal-hapus');
    exit;
}


// ======================================================
// HAPUS DATA
// ======================================================

try {

    $pdo->beginTransaction();


    // ==================================================
    // AMBIL ID ADMIN
    // ==================================================

    $stmtUser = $pdo->prepare("
        SELECT id_user
        FROM tb_user
        WHERE nama_lengkap = ?
          AND role = 'admin'
        LIMIT 1
    ");

    $stmtUser->execute([$namaUser]);

    $adminUserId = $stmtUser->fetchColumn();


    // ==================================================
    // HAPUS KENDARAAN
    // ==================================================

    $stmtDelete = $pdo->prepare("
        DELETE FROM tb_kendaraan
        WHERE id_kendaraan = ?
    ");

    $stmtDelete->execute([$idKendaraan]);


    // ==================================================
    // LOG AKTIVITAS
    // ==================================================

    if ($adminUserId) {

        $aktivitas =
            "Menghapus kendaraan {$kendaraan['plat_nomor']} - Pemilik: {$kendaraan['pemilik']}";

        $stmtLog = $pdo->prepare("
            INSERT INTO tb_log_aktivitas
            (
                id_user,
                aktivitas,
                waktu_aktivitas
            )
            VALUES
            (
                ?,
                ?,
                NOW()
            )
        ");

        $stmtLog->execute([
            $adminUserId,
            $aktivitas
        ]);
    }


    $pdo->commit();


    header('Location: kendaraan.php?status=hapus');
    exit;


} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: kendaraan.php?status=gagal-hapus');
    exit;
}