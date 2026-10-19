<?php

// ======================================================
// ERROR REPORTING
// ======================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


// ======================================================
// AUTH
// ======================================================

require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

require_once __DIR__ . '/../config/database.php';


// ======================================================
// DATA USER LOGIN
// ======================================================

$namaUser = getNamaUser();


// ======================================================
// DEFAULT DATA
// ======================================================

$dataUser = [];

$totalUser = 0;
$totalAdmin = 0;
$totalPetugas = 0;
$totalOwner = 0;

$errorDatabase = '';


// ======================================================
// KONEKSI DATABASE
// ======================================================

try {

    $pdo = getDatabaseConnection('admin');


    // ==================================================
    // TOTAL USER
    // ==================================================

    $stmtTotal = $pdo->query("
        SELECT COUNT(*)
        FROM tb_user
    ");

    $totalUser = (int) $stmtTotal->fetchColumn();


    // ==================================================
    // TOTAL ADMIN
    // ==================================================

    $stmtAdmin = $pdo->query("
        SELECT COUNT(*)
        FROM tb_user
        WHERE LOWER(role) = 'admin'
    ");

    $totalAdmin = (int) $stmtAdmin->fetchColumn();


    // ==================================================
    // TOTAL PETUGAS
    // ==================================================

    $stmtPetugas = $pdo->query("
        SELECT COUNT(*)
        FROM tb_user
        WHERE LOWER(role) = 'petugas'
    ");

    $totalPetugas = (int) $stmtPetugas->fetchColumn();


    // ==================================================
    // TOTAL OWNER
    // ==================================================

    $stmtOwner = $pdo->query("
        SELECT COUNT(*)
        FROM tb_user
        WHERE LOWER(role) = 'owner'
    ");

    $totalOwner = (int) $stmtOwner->fetchColumn();


    // ==================================================
    // DATA USER
    // ==================================================

    $stmtUser = $pdo->query("
        SELECT
            id_user,
            nama_lengkap,
            username,
            role,
            status_aktif
        FROM tb_user
        ORDER BY id_user DESC
    ");

    $dataUser = $stmtUser->fetchAll(
        PDO::FETCH_ASSOC
    );


} catch (Exception $e) {

    $errorDatabase = $e->getMessage();

}


// ======================================================
// PAGE
// ======================================================

$pageTitle = 'Data User';

$pageDescription =
    'Kelola akun pengguna yang memiliki akses ke sistem parkir';

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Data User - Sistem Parkir
    </title>


    <!-- ==================================================
         CSS GLOBAL
    ================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >


    <!-- ==================================================
         LUCIDE ICON
    ================================================== -->

    <script
        src="https://unpkg.com/lucide@latest"
    ></script>


    <style>

        /* ==================================================
           USER PAGE
        ================================================== */

        .user-page {
            padding: 28px;
        }


        /* ==================================================
           HEADER
        ================================================== */

        .user-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;
        }


        .user-heading h2 {
            margin: 0;

            font-size: 27px;

            font-weight: 800;

            color: #21172d;

            letter-spacing: -0.5px;
        }


        .user-heading p {
            margin: 7px 0 0;

            color: #8b8495;

            font-size: 13px;
        }


        /* ==================================================
           BUTTON TAMBAH
        ================================================== */

        .btn-tambah-user {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            padding:
                12px
                18px;

            border: none;

            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    #7c3aed,
                    #9333ea
                );

            color: #ffffff;

            font-size: 13px;

            font-weight: 750;

            text-decoration: none;

            box-shadow:
                0 10px 25px
                rgba(124, 58, 237, 0.22);

            transition: 0.2s ease;
        }


        .btn-tambah-user:hover {
            transform: translateY(-2px);

            box-shadow:
                0 14px 30px
                rgba(124, 58, 237, 0.30);
        }


        .btn-tambah-user svg {
            width: 18px;
            height: 18px;

            stroke-width: 2.4;
        }


        /* ==================================================
           STATISTIC GRID
        ================================================== */

        .user-stat-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 22px;
        }


        /* ==================================================
           STAT CARD
        ================================================== */

        .user-stat {
            position: relative;

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 19px;

            background: #ffffff;

            border:
                1px solid #eeeaf4;

            border-radius: 17px;

            box-shadow:
                0 8px 28px
                rgba(45, 30, 70, 0.055);

            overflow: hidden;
        }


        .user-stat::after {
            content: "";

            position: absolute;

            width: 75px;
            height: 75px;

            right: -30px;
            top: -30px;

            border-radius: 50%;

            background:
                rgba(124, 58, 237, 0.055);
        }


        /* ==================================================
           STAT ICON
        ================================================== */

        .stat-user-icon {
            width: 47px;
            height: 47px;

            min-width: 47px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    #f3e8ff,
                    #ede9fe
                );

            color: #7c3aed;
        }


        .stat-user-icon svg {
            width: 21px;
            height: 21px;

            stroke-width: 2.2;
        }


        .stat-user-number {
            font-size: 23px;

            font-weight: 800;

            line-height: 1;

            color: #24182f;
        }


        .stat-user-label {
            margin-top: 6px;

            font-size: 11px;

            font-weight: 650;

            color: #8d8797;
        }


        /* ==================================================
           MAIN CARD
        ================================================== */

        .user-card {
            background: #ffffff;

            border:
                1px solid #eeeaf4;

            border-radius: 19px;

            box-shadow:
                0 10px 35px
                rgba(45, 30, 70, 0.06);

            overflow: hidden;
        }


        /* ==================================================
           CARD HEADER
        ================================================== */

        .user-card-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding:
                21px
                23px;

            border-bottom:
                1px solid #f0edf4;
        }


        .card-title-area h3 {
            margin: 0;

            font-size: 17px;

            font-weight: 800;

            color: #281c34;
        }


        .card-title-area p {
            margin: 5px 0 0;

            font-size: 12px;

            color: #938c9c;
        }


        /* ==================================================
           SEARCH
        ================================================== */

        .search-box {
            position: relative;

            width: 280px;
        }


        .search-box input {
            width: 100%;

            box-sizing: border-box;

            padding:
                11px
                13px
                11px
                39px;

            border:
                1px solid #e7e1ed;

            border-radius: 11px;

            outline: none;

            background: #faf9fc;

            color: #30253a;

            font-size: 12px;

            transition: 0.2s ease;
        }


        .search-box input:focus {
            border-color: #a78bfa;

            background: #ffffff;

            box-shadow:
                0 0 0 3px
                rgba(124, 58, 237, 0.08);
        }


        .search-icon {
            position: absolute;

            left: 13px;
            top: 50%;

            transform: translateY(-50%);

            color: #92899d;

            display: flex;

            align-items: center;

            justify-content: center;

            pointer-events: none;
        }


        .search-icon svg {
            width: 16px;
            height: 16px;

            stroke-width: 2;
        }


        /* ==================================================
           TABLE WRAPPER
        ================================================== */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;
        }


        /* ==================================================
           TABLE
        ================================================== */

        .user-table {
            width: 100%;

            border-collapse: collapse;

            min-width: 780px;
        }


        .user-table th {
            padding:
                13px
                18px;

            text-align: left;

            background: #faf9fc;

            color: #827a8d;

            font-size: 10px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.6px;

            border-bottom:
                1px solid #eeeaf3;
        }


        .user-table td {
            padding:
                15px
                18px;

            color: #40364b;

            font-size: 12px;

            border-bottom:
                1px solid #f1eef5;

            vertical-align: middle;
        }


        .user-table tbody tr {
            transition: 0.15s ease;
        }


        .user-table tbody tr:hover {
            background: #fcfaff;
        }


        .user-table tbody tr:last-child td {
            border-bottom: none;
        }


        /* ==================================================
           NOMOR
        ================================================== */

        .nomor-data {
            width: 45px;

            color: #9a92a3;

            font-weight: 700;
        }


        /* ==================================================
           USER PROFILE
        ================================================== */

        .user-profile {
            display: flex;

            align-items: center;

            gap: 11px;
        }


        .user-avatar-table {
            width: 39px;
            height: 39px;

            min-width: 39px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 11px;

            background:
                linear-gradient(
                    135deg,
                    #7c3aed,
                    #a855f7
                );

            color: #ffffff;

            font-size: 13px;

            font-weight: 800;

            box-shadow:
                0 6px 15px
                rgba(124, 58, 237, 0.18);
        }


        .user-profile-name {
            color: #2d2236;

            font-size: 13px;

            font-weight: 800;
        }


        .user-profile-id {
            margin-top: 3px;

            color: #9b94a3;

            font-size: 10px;
        }


        /* ==================================================
           USERNAME
        ================================================== */

        .username-wrapper {
            display: inline-flex;

            align-items: center;

            gap: 7px;

            color: #51475b;

            font-weight: 650;
        }


        .username-wrapper svg {
            width: 15px;
            height: 15px;

            color: #9b8cad;

            stroke-width: 2;
        }


        /* ==================================================
           ROLE BADGE
        ================================================== */

        .role-badge {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                6px
                10px;

            border-radius: 8px;

            font-size: 10px;

            font-weight: 800;

            text-transform: capitalize;
        }


        .role-badge svg {
            width: 13px;
            height: 13px;

            stroke-width: 2.3;
        }


        .role-admin {
            color: #6d28d9;

            background: #f3e8ff;
        }


        .role-petugas {
            color: #0369a1;

            background: #e0f2fe;
        }


        .role-owner {
            color: #047857;

            background: #d1fae5;
        }


        .role-lainnya {
            color: #92400e;

            background: #fef3c7;
        }


        /* ==================================================
           STATUS
        ================================================== */

        .status-badge {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                6px
                10px;

            border-radius: 8px;

            font-size: 10px;

            font-weight: 800;
        }


        .status-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;
        }


        .status-aktif {
            color: #166534;

            background: #dcfce7;
        }


        .status-aktif .status-dot {
            background: #22c55e;
        }


        .status-nonaktif {
            color: #991b1b;

            background: #fee2e2;
        }


        .status-nonaktif .status-dot {
            background: #ef4444;
        }


        .status-tidak-diketahui {
            color: #57534e;

            background: #f5f5f4;
        }


        .status-tidak-diketahui .status-dot {
            background: #a8a29e;
        }


        /* ==================================================
           ACTION
        ================================================== */

        .action-group {
            display: flex;

            align-items: center;

            gap: 7px;
        }


        .action-btn {
            width: 34px;
            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 9px;

            text-decoration: none;

            transition: 0.18s ease;
        }


        .action-btn svg {
            width: 16px;
            height: 16px;

            stroke-width: 2.2;
        }


        .action-edit {
            color: #6d28d9;

            background: #f3e8ff;
        }


        .action-edit:hover {
            background: #e9d5ff;

            transform: translateY(-1px);
        }


        .action-delete {
            color: #dc2626;

            background: #fee2e2;
        }


        .action-delete:hover {
            background: #fecaca;

            transform: translateY(-1px);
        }


        /* ==================================================
           EMPTY STATE
        ================================================== */

        .empty-state {
            text-align: center;

            padding: 65px 20px;
        }


        .empty-icon {
            width: 65px;
            height: 65px;

            margin: 0 auto 14px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 18px;

            background: #f5f3f7;

            color: #aaa2b2;
        }


        .empty-icon svg {
            width: 30px;
            height: 30px;

            stroke-width: 1.8;
        }


        .empty-state h4 {
            margin: 0;

            color: #44394e;

            font-size: 15px;
        }


        .empty-state p {
            margin: 7px 0 0;

            color: #9b94a3;

            font-size: 12px;
        }


        /* ==================================================
           ERROR DATABASE
        ================================================== */

        .database-error {
            margin-bottom: 20px;

            padding:
                15px
                18px;

            border:
                1px solid #fecaca;

            border-radius: 13px;

            background: #fef2f2;

            color: #991b1b;

            font-size: 12px;

            line-height: 1.6;
        }


        .database-error strong {
            display: flex;

            align-items: center;

            gap: 7px;

            margin-bottom: 5px;

            font-size: 13px;
        }


        .database-error strong svg {
            width: 16px;
            height: 16px;
        }


        /* ==================================================
           FLASH MESSAGE
        ================================================== */

        .flash-message {
            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 20px;

            padding:
                13px
                16px;

            border:
                1px solid #bbf7d0;

            border-radius: 13px;

            background: #f0fdf4;

            color: #166534;

            font-size: 12px;

            font-weight: 650;
        }


        .flash-message svg {
            width: 18px;
            height: 18px;

            min-width: 18px;
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 1100px) {

            .user-stat-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 700px) {

            .user-page {
                padding: 18px;
            }


            .user-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .btn-tambah-user {
                width: 100%;
            }


            .user-stat-grid {
                grid-template-columns: 1fr;
            }


            .user-card-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .search-box {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<div class="admin-layout">


    <!-- ==================================================
         SIDEBAR
    ================================================== -->

    <?php require_once __DIR__ . '/sidebar.php'; ?>


    <!-- ==================================================
         MAIN CONTENT
    ================================================== -->

    <div class="main-content">


        <!-- ==================================================
             NAVBAR
        ================================================== -->

        <?php require_once __DIR__ . '/navbar.php'; ?>


        <!-- ==================================================
             CONTENT
        ================================================== -->

        <main class="content">


            <div class="user-page">


                <!-- ==================================================
                     HEADER
                ================================================== -->

                <div class="user-header">


                    <div class="user-heading">

                        <h2>
                            Data User
                        </h2>

                        <p>
                            Kelola akun yang memiliki akses
                            ke dalam sistem parkir.
                        </p>

                    </div>


                    <a
                        href="user-tambah.php"
                        class="btn-tambah-user"
                    >

                        <i
                            data-lucide="user-plus"
                        ></i>

                        <span>
                            Tambah User
                        </span>

                    </a>


                </div>



                <!-- ==================================================
                     FLASH MESSAGE
                ================================================== -->

                <?php if (
                    isset($_GET['status'])
                ): ?>


                    <?php

                    $status =
                        $_GET['status'];

                    $pesanStatus = '';


                    if (
                        $status ===
                        'tambah'
                    ) {

                        $pesanStatus =
                            'User berhasil ditambahkan.';

                    } elseif (
                        $status ===
                        'edit'
                    ) {

                        $pesanStatus =
                            'Data user berhasil diperbarui.';

                    } elseif (
                        $status ===
                        'hapus'
                    ) {

                        $pesanStatus =
                            'User berhasil dihapus.';

                    }

                    ?>


                    <?php if (
                        $pesanStatus !== ''
                    ): ?>

                        <div class="flash-message">

                            <i
                                data-lucide="circle-check"
                            ></i>

                            <span>
                                <?= htmlspecialchars(
                                    $pesanStatus
                                ) ?>
                            </span>

                        </div>

                    <?php endif; ?>


                <?php endif; ?>



                <!-- ==================================================
                     DATABASE ERROR
                ================================================== -->

                <?php if (
                    $errorDatabase !== ''
                ): ?>

                    <div class="database-error">

                        <strong>

                            <i
                                data-lucide="circle-alert"
                            ></i>

                            Terjadi kesalahan database

                        </strong>

                        <?= htmlspecialchars(
                            $errorDatabase
                        ) ?>

                    </div>

                <?php endif; ?>



                <!-- ==================================================
                     STATISTIK
                ================================================== -->

                <div class="user-stat-grid">


                    <!-- TOTAL USER -->

                    <div class="user-stat">

                        <div class="stat-user-icon">

                            <i
                                data-lucide="users"
                            ></i>

                        </div>


                        <div>

                            <div class="stat-user-number">

                                <?= number_format(
                                    $totalUser
                                ) ?>

                            </div>


                            <div class="stat-user-label">

                                Total User

                            </div>

                        </div>

                    </div>



                    <!-- ADMIN -->

                    <div class="user-stat">

                        <div class="stat-user-icon">

                            <i
                                data-lucide="shield-check"
                            ></i>

                        </div>


                        <div>

                            <div class="stat-user-number">

                                <?= number_format(
                                    $totalAdmin
                                ) ?>

                            </div>


                            <div class="stat-user-label">

                                Admin

                            </div>

                        </div>

                    </div>



                    <!-- PETUGAS -->

                    <div class="user-stat">

                        <div class="stat-user-icon">

                            <i
                                data-lucide="user-round-check"
                            ></i>

                        </div>


                        <div>

                            <div class="stat-user-number">

                                <?= number_format(
                                    $totalPetugas
                                ) ?>

                            </div>


                            <div class="stat-user-label">

                                Petugas

                            </div>

                        </div>

                    </div>



                    <!-- OWNER -->

                    <div class="user-stat">

                        <div class="stat-user-icon">

                            <i
                                data-lucide="crown"
                            ></i>

                        </div>


                        <div>

                            <div class="stat-user-number">

                                <?= number_format(
                                    $totalOwner
                                ) ?>

                            </div>


                            <div class="stat-user-label">

                                Owner

                            </div>

                        </div>

                    </div>


                </div>



                <!-- ==================================================
                     DATA USER CARD
                ================================================== -->

                <div class="user-card">


                    <!-- CARD HEADER -->

                    <div class="user-card-header">


                        <div class="card-title-area">

                            <h3>
                                Daftar User
                            </h3>

                            <p>
                                Akun pengguna yang tersimpan
                                dalam database.
                            </p>

                        </div>


                        <!-- SEARCH -->

                        <div class="search-box">

                            <span class="search-icon">

                                <i
                                    data-lucide="search"
                                ></i>

                            </span>


                            <input
                                type="text"
                                id="searchUser"
                                placeholder="Cari nama, username, role..."
                                autocomplete="off"
                            >

                        </div>


                    </div>



                    <!-- ==================================================
                         TABLE
                    ================================================== -->

                    <div class="table-wrapper">


                        <table
                            class="user-table"
                            id="tableUser"
                        >


                            <thead>

                                <tr>

                                    <th>
                                        No
                                    </th>

                                    <th>
                                        User
                                    </th>

                                    <th>
                                        Username
                                    </th>

                                    <th>
                                        Role
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php if (
                                    !empty(
                                        $dataUser
                                    )
                                ): ?>


                                    <?php
                                    $no = 1;
                                    ?>


                                    <?php foreach (
                                        $dataUser
                                        as $user
                                    ): ?>


                                        <?php

                                        // =================================
                                        // ROLE
                                        // =================================

                                        $role =
                                            strtolower(
                                                trim(
                                                    $user[
                                                        'role'
                                                    ]
                                                    ?? ''
                                                )
                                            );


                                        if (
                                            $role ===
                                            'admin'
                                        ) {

                                            $roleClass =
                                                'role-admin';

                                            $roleIcon =
                                                'shield-check';

                                        } elseif (
                                            $role ===
                                            'petugas'
                                        ) {

                                            $roleClass =
                                                'role-petugas';

                                            $roleIcon =
                                                'user-round-check';

                                        } elseif (
                                            $role ===
                                            'owner'
                                        ) {

                                            $roleClass =
                                                'role-owner';

                                            $roleIcon =
                                                'crown';

                                        } else {

                                            $roleClass =
                                                'role-lainnya';

                                            $roleIcon =
                                                'user';

                                        }


                                        // =================================
                                        // STATUS
                                        // =================================

                                        $statusRaw =
                                            $user[
                                                'status_aktif'
                                            ] ?? null;


                                        $statusString =
                                            strtolower(
                                                trim(
                                                    (string)
                                                    $statusRaw
                                                )
                                            );


                                        /*
                                         * Dibuat fleksibel agar bisa
                                         * membaca status 1/0 maupun
                                         * aktif/nonaktif.
                                         */

                                        if (
                                            $statusRaw === 1 ||
                                            $statusRaw === '1' ||
                                            $statusString === 'aktif' ||
                                            $statusString === 'active' ||
                                            $statusString === 'ya'
                                        ) {

                                            $statusClass =
                                                'status-aktif';

                                            $statusLabel =
                                                'Aktif';

                                        } elseif (
                                            $statusRaw === 0 ||
                                            $statusRaw === '0' ||
                                            $statusString === 'nonaktif' ||
                                            $statusString === 'inactive' ||
                                            $statusString === 'tidak'
                                        ) {

                                            $statusClass =
                                                'status-nonaktif';

                                            $statusLabel =
                                                'Nonaktif';

                                        } else {

                                            $statusClass =
                                                'status-tidak-diketahui';

                                            $statusLabel =
                                                $statusRaw !== null &&
                                                $statusRaw !== ''
                                                    ? $statusRaw
                                                    : 'Tidak diketahui';

                                        }


                                        // =================================
                                        // AVATAR
                                        // =================================

                                        $namaLengkap =
                                            trim(
                                                $user[
                                                    'nama_lengkap'
                                                ] ?? ''
                                            );


                                        $inisial =
                                            strtoupper(
                                                substr(
                                                    $namaLengkap,
                                                    0,
                                                    1
                                                )
                                            );


                                        if (
                                            $inisial === ''
                                        ) {

                                            $inisial =
                                                'U';

                                        }

                                        ?>


                                        <tr
                                            class="data-row"
                                        >


                                            <!-- NO -->

                                            <td
                                                class="nomor-data"
                                            >

                                                <?= $no++ ?>

                                            </td>



                                            <!-- USER -->

                                            <td>

                                                <div
                                                    class="user-profile"
                                                >


                                                    <div
                                                        class="user-avatar-table"
                                                    >

                                                        <?= htmlspecialchars(
                                                            $inisial
                                                        ) ?>

                                                    </div>


                                                    <div>

                                                        <div
                                                            class="
                                                                user-profile-name
                                                            "
                                                        >

                                                            <?= htmlspecialchars(
                                                                $namaLengkap
                                                            ) ?>

                                                        </div>


                                                        <div
                                                            class="
                                                                user-profile-id
                                                            "
                                                        >

                                                            ID User:

                                                            <?= htmlspecialchars(
                                                                $user[
                                                                    'id_user'
                                                                ]
                                                            ) ?>

                                                        </div>

                                                    </div>


                                                </div>

                                            </td>



                                            <!-- USERNAME -->

                                            <td>

                                                <span
                                                    class="
                                                        username-wrapper
                                                    "
                                                >

                                                    <i
                                                        data-lucide="at-sign"
                                                    ></i>

                                                    <?= htmlspecialchars(
                                                        $user[
                                                            'username'
                                                        ]
                                                    ) ?>

                                                </span>

                                            </td>



                                            <!-- ROLE -->

                                            <td>

                                                <span
                                                    class="
                                                        role-badge
                                                        <?= $roleClass ?>
                                                    "
                                                >

                                                    <i
                                                        data-lucide="<?= htmlspecialchars(
                                                            $roleIcon
                                                        ) ?>"
                                                    ></i>

                                                    <?= htmlspecialchars(
                                                        ucfirst(
                                                            $role
                                                        )
                                                    ) ?>

                                                </span>

                                            </td>



                                            <!-- STATUS -->

                                            <td>

                                                <span
                                                    class="
                                                        status-badge
                                                        <?= $statusClass ?>
                                                    "
                                                >

                                                    <span
                                                        class="status-dot"
                                                    ></span>

                                                    <?= htmlspecialchars(
                                                        $statusLabel
                                                    ) ?>

                                                </span>

                                            </td>



                                            <!-- AKSI -->

                                            <td>

                                                <div
                                                    class="action-group"
                                                >


                                                    <!-- EDIT -->

                                                    <a
                                                        href="user-edit.php?id=<?= urlencode(
                                                            $user[
                                                                'id_user'
                                                            ]
                                                        ) ?>"
                                                        class="
                                                            action-btn
                                                            action-edit
                                                        "
                                                        title="Edit user"
                                                    >

                                                        <i
                                                            data-lucide="pencil"
                                                        ></i>

                                                    </a>


                                                    <!-- HAPUS -->

                                                    <a
                                                        href="user-hapus.php?id=<?= urlencode(
                                                            $user[
                                                                'id_user'
                                                            ]
                                                        ) ?>"
                                                        class="
                                                            action-btn
                                                            action-delete
                                                        "
                                                        title="Hapus user"
                                                        onclick="
                                                            return confirm(
                                                                'Yakin ingin menghapus user ini?'
                                                            );
                                                        "
                                                    >

                                                        <i
                                                            data-lucide="trash-2"
                                                        ></i>

                                                    </a>


                                                </div>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <!-- ==================================================
                                         EMPTY STATE
                                    ================================================== -->

                                    <tr>

                                        <td
                                            colspan="6"
                                        >

                                            <div
                                                class="empty-state"
                                            >


                                                <div
                                                    class="empty-icon"
                                                >

                                                    <i
                                                        data-lucide="users-round"
                                                    ></i>

                                                </div>


                                                <h4>
                                                    Belum Ada Data User
                                                </h4>


                                                <p>
                                                    Belum ada akun pengguna
                                                    yang tersimpan dalam
                                                    database.
                                                </p>


                                            </div>

                                        </td>

                                    </tr>


                                <?php endif; ?>


                            </tbody>


                        </table>


                    </div>


                </div>


            </div>


        </main>


    </div>


</div>



<!-- ======================================================
     JAVASCRIPT
====================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        // ==================================================
        // SEARCH USER
        // ==================================================

        const searchInput =
            document.getElementById(
                'searchUser'
            );


        const table =
            document.getElementById(
                'tableUser'
            );


        if (
            searchInput &&
            table
        ) {


            searchInput.addEventListener(
                'input',
                function () {


                    const keyword =
                        this.value
                            .toLowerCase()
                            .trim();


                    const rows =
                        table.querySelectorAll(
                            'tbody tr.data-row'
                        );


                    rows.forEach(
                        function (row) {


                            const text =
                                row.textContent
                                    .toLowerCase();


                            row.style.display =
                                text.includes(
                                    keyword
                                )
                                    ? ''
                                    : 'none';


                        }
                    );


                }
            );

        }


        // ==================================================
        // LUCIDE
        // ==================================================

        lucide.createIcons();


    }
);

</script>


</body>

</html>