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
// DEFAULT
// ======================================================

$error = '';

$namaLengkap = '';
$username = '';
$role = '';
$statusAktif = '1';


// ======================================================
// PROSES TAMBAH USER
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namaLengkap = trim(
        $_POST['nama_lengkap'] ?? ''
    );

    $username = trim(
        $_POST['username'] ?? ''
    );

    $password = $_POST['password'] ?? '';

    $role = strtolower(
        trim(
            $_POST['role'] ?? ''
        )
    );

    $statusAktif =
        $_POST['status_aktif'] ?? '1';


    // ==================================================
    // VALIDASI
    // ==================================================

    if (
        $namaLengkap === '' ||
        $username === '' ||
        $password === '' ||
        $role === ''
    ) {

        $error =
            'Semua field wajib diisi.';

    } elseif (
        strlen($namaLengkap) < 3
    ) {

        $error =
            'Nama lengkap minimal 3 karakter.';

    } elseif (
        strlen($username) < 3
    ) {

        $error =
            'Username minimal 3 karakter.';

    } elseif (
        strlen($password) < 6
    ) {

        $error =
            'Password minimal 6 karakter.';

    } elseif (
        !in_array(
            $role,
            [
                'admin',
                'petugas',
                'owner'
            ],
            true
        )
    ) {

        $error =
            'Role yang dipilih tidak valid.';

    } elseif (
        !in_array(
            $statusAktif,
            ['0', '1'],
            true
        )
    ) {

        $error =
            'Status user tidak valid.';

    } else {


        // ==================================================
        // DATABASE
        // ==================================================

        try {

            $pdo =
                getDatabaseConnection('admin');


            // ==============================================
            // CEK USERNAME
            // ==============================================

            $stmtCek =
                $pdo->prepare("
                    SELECT
                        COUNT(*)
                    FROM tb_user
                    WHERE username = ?
                ");

            $stmtCek->execute([
                $username
            ]);

            $usernameSudahAda =
                (int) $stmtCek->fetchColumn();


            if (
                $usernameSudahAda > 0
            ) {

                $error =
                    'Username sudah digunakan. Silakan gunakan username lain.';

            } else {


                // ==========================================
                // HASH PASSWORD
                // ==========================================

                $passwordHash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                // ==========================================
                // INSERT USER
                // ==========================================

                $stmtInsert =
                    $pdo->prepare("
                        INSERT INTO tb_user
                        (
                            nama_lengkap,
                            username,
                            password,
                            role,
                            status_aktif
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        )
                    ");


                $stmtInsert->execute([
                    $namaLengkap,
                    $username,
                    $passwordHash,
                    $role,
                    $statusAktif
                ]);


                // ==========================================
                // REDIRECT
                // ==========================================

                header(
                    'Location: user.php?status=tambah'
                );

                exit;

            }


        } catch (Exception $e) {

            $error =
                $e->getMessage();

        }

    }

}

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
        Tambah User - Sistem Parkir
    </title>


    <!-- ==================================================
         CSS GLOBAL
    ================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >


    <!-- ==================================================
         LUCIDE
    ================================================== -->

    <script
        src="https://unpkg.com/lucide@latest"
    ></script>


    <style>

        /* ==================================================
           PAGE
        ================================================== */

        .user-form-page {
            padding: 28px;
        }


        /* ==================================================
           HEADER
        ================================================== */

        .user-form-header {
            display: flex;

            align-items: flex-start;

            gap: 15px;

            margin-bottom: 25px;
        }


        .back-button {
            width: 42px;
            height: 42px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #ffffff;

            border:
                1px solid #eeeaf4;

            color: #5b5265;

            text-decoration: none;

            box-shadow:
                0 6px 18px
                rgba(45, 30, 70, 0.05);

            transition: 0.2s ease;
        }


        .back-button:hover {
            color: #7c3aed;

            border-color: #ddd0f5;

            transform: translateX(-2px);
        }


        .back-button svg {
            width: 19px;
            height: 19px;

            stroke-width: 2.2;
        }


        .user-form-heading h2 {
            margin: 0;

            color: #21172d;

            font-size: 27px;

            font-weight: 800;

            letter-spacing: -0.5px;
        }


        .user-form-heading p {
            margin: 7px 0 0;

            color: #8b8495;

            font-size: 13px;
        }


        /* ==================================================
           FORM CARD
        ================================================== */

        .user-form-card {
            width: 100%;

            max-width: 900px;

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

        .user-form-card-header {
            display: flex;

            align-items: center;

            gap: 13px;

            padding:
                21px 24px;

            border-bottom:
                1px solid #f0edf4;
        }


        .form-header-icon {
            width: 43px;
            height: 43px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    #f3e8ff,
                    #ede9fe
                );

            color: #7c3aed;
        }


        .form-header-icon svg {
            width: 21px;
            height: 21px;

            stroke-width: 2.2;
        }


        .form-header-text h3 {
            margin: 0;

            color: #281c34;

            font-size: 16px;

            font-weight: 800;
        }


        .form-header-text p {
            margin: 4px 0 0;

            color: #938c9c;

            font-size: 11px;
        }


        /* ==================================================
           ERROR
        ================================================== */

        .form-error {
            display: flex;

            align-items: flex-start;

            gap: 10px;

            margin:
                20px 24px 0;

            padding:
                13px 15px;

            border:
                1px solid #fecaca;

            border-radius: 12px;

            background: #fef2f2;

            color: #991b1b;

            font-size: 12px;

            line-height: 1.5;
        }


        .form-error svg {
            width: 18px;
            height: 18px;

            min-width: 18px;

            margin-top: 1px;
        }


        /* ==================================================
           FORM BODY
        ================================================== */

        .user-form-body {
            padding: 25px 24px;
        }


        /* ==================================================
           GRID
        ================================================== */

        .form-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;
        }


        .form-group {
            display: flex;

            flex-direction: column;

            gap: 8px;
        }


        .form-group.full {
            grid-column: 1 / -1;
        }


        /* ==================================================
           LABEL
        ================================================== */

        .form-label {
            display: flex;

            align-items: center;

            gap: 6px;

            color: #40364b;

            font-size: 12px;

            font-weight: 750;
        }


        .form-label svg {
            width: 15px;
            height: 15px;

            color: #8c78a2;

            stroke-width: 2.1;
        }


        .required {
            color: #dc2626;
        }


        /* ==================================================
           INPUT
        ================================================== */

        .form-input,
        .form-select {
            width: 100%;

            box-sizing: border-box;

            height: 45px;

            padding:
                0 13px;

            border:
                1px solid #e7e1ed;

            border-radius: 11px;

            outline: none;

            background: #faf9fc;

            color: #30253a;

            font-family: inherit;

            font-size: 12px;

            transition: 0.2s ease;
        }


        .form-input:focus,
        .form-select:focus {
            border-color: #a78bfa;

            background: #ffffff;

            box-shadow:
                0 0 0 3px
                rgba(124, 58, 237, 0.08);
        }


        .form-input::placeholder {
            color: #aaa3b1;
        }


        /* ==================================================
           PASSWORD
        ================================================== */

        .password-wrapper {
            position: relative;
        }


        .password-wrapper .form-input {
            padding-right: 45px;
        }


        .password-toggle {
            position: absolute;

            right: 10px;
            top: 50%;

            transform: translateY(-50%);

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;

            justify-content: center;

            border: none;

            border-radius: 8px;

            background: transparent;

            color: #958b9f;

            cursor: pointer;

            transition: 0.18s ease;
        }


        .password-toggle:hover {
            background: #f3e8ff;

            color: #7c3aed;
        }


        .password-toggle svg {
            width: 17px;
            height: 17px;
        }


        /* ==================================================
           HELP TEXT
        ================================================== */

        .form-help {
            margin: -2px 0 0;

            color: #9b94a3;

            font-size: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           ROLE OPTIONS
        ================================================== */

        .role-options {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 10px;
        }


        .role-option {
            position: relative;
        }


        .role-option input {
            position: absolute;

            opacity: 0;

            pointer-events: none;
        }


        .role-option label {
            min-height: 78px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 10px;

            box-sizing: border-box;

            border:
                1px solid #e7e1ed;

            border-radius: 12px;

            background: #faf9fc;

            color: #655b6e;

            cursor: pointer;

            transition: 0.2s ease;
        }


        .role-option label svg {
            width: 20px;
            height: 20px;

            stroke-width: 2;
        }


        .role-option label span {
            font-size: 11px;

            font-weight: 750;
        }


        .role-option input:checked + label {
            border-color: #a78bfa;

            background:
                linear-gradient(
                    135deg,
                    #f5edff,
                    #f3e8ff
                );

            color: #6d28d9;

            box-shadow:
                0 5px 15px
                rgba(124, 58, 237, 0.08);
        }


        .role-option label:hover {
            border-color: #c4b5fd;

            transform: translateY(-1px);
        }


        /* ==================================================
           STATUS OPTIONS
        ================================================== */

        .status-options {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 10px;
        }


        .status-option {
            position: relative;
        }


        .status-option input {
            position: absolute;

            opacity: 0;

            pointer-events: none;
        }


        .status-option label {
            height: 45px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            box-sizing: border-box;

            border:
                1px solid #e7e1ed;

            border-radius: 11px;

            background: #faf9fc;

            color: #655b6e;

            cursor: pointer;

            font-size: 11px;

            font-weight: 750;

            transition: 0.2s ease;
        }


        .status-option label svg {
            width: 16px;
            height: 16px;
        }


        .status-option input:checked + label {
            border-color: #86efac;

            background: #f0fdf4;

            color: #166534;
        }


        .status-option:last-child input:checked + label {
            border-color: #fecaca;

            background: #fef2f2;

            color: #991b1b;
        }


        /* ==================================================
           FOOTER BUTTON
        ================================================== */

        .user-form-footer {
            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 10px;

            padding:
                18px 24px;

            border-top:
                1px solid #f0edf4;

            background: #fcfbfd;
        }


        .btn-form {
            height: 42px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding:
                0 17px;

            border-radius: 11px;

            font-family: inherit;

            font-size: 12px;

            font-weight: 750;

            text-decoration: none;

            cursor: pointer;

            transition: 0.2s ease;
        }


        .btn-form svg {
            width: 17px;
            height: 17px;

            stroke-width: 2.2;
        }


        .btn-batal {
            border:
                1px solid #e5e0e9;

            background: #ffffff;

            color: #6b6272;
        }


        .btn-batal:hover {
            background: #f7f5f9;

            transform: translateY(-1px);
        }


        .btn-simpan {
            border: none;

            background:
                linear-gradient(
                    135deg,
                    #7c3aed,
                    #9333ea
                );

            color: #ffffff;

            box-shadow:
                0 8px 20px
                rgba(124, 58, 237, 0.20);
        }


        .btn-simpan:hover {
            transform: translateY(-1px);

            box-shadow:
                0 12px 25px
                rgba(124, 58, 237, 0.28);
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 700px) {

            .user-form-page {
                padding: 18px;
            }


            .form-grid {
                grid-template-columns: 1fr;
            }


            .form-group.full {
                grid-column: auto;
            }


            .role-options {
                grid-template-columns: 1fr;
            }


            .status-options {
                grid-template-columns: 1fr;
            }


            .user-form-footer {
                flex-direction: column-reverse;

                align-items: stretch;
            }


            .btn-form {
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


            <div class="user-form-page">


                <!-- ==================================================
                     PAGE HEADER
                ================================================== -->

                <div class="user-form-header">


                    <a
                        href="user.php"
                        class="back-button"
                        title="Kembali ke Data User"
                    >

                        <i
                            data-lucide="arrow-left"
                        ></i>

                    </a>


                    <div
                        class="user-form-heading"
                    >

                        <h2>
                            Tambah User
                        </h2>

                        <p>
                            Tambahkan akun baru yang dapat
                            mengakses sistem parkir.
                        </p>

                    </div>


                </div>



                <!-- ==================================================
                     FORM CARD
                ================================================== -->

                <div class="user-form-card">


                    <!-- CARD HEADER -->

                    <div
                        class="user-form-card-header"
                    >

                        <div
                            class="form-header-icon"
                        >

                            <i
                                data-lucide="user-plus"
                            ></i>

                        </div>


                        <div
                            class="form-header-text"
                        >

                            <h3>
                                Informasi Akun
                            </h3>

                            <p>
                                Isi informasi akun pengguna
                                dengan lengkap.
                            </p>

                        </div>

                    </div>



                    <!-- ==================================================
                         ERROR
                    ================================================== -->

                    <?php if (
                        $error !== ''
                    ): ?>

                        <div
                            class="form-error"
                        >

                            <i
                                data-lucide="circle-alert"
                            ></i>

                            <div>
                                <?= htmlspecialchars(
                                    $error
                                ) ?>
                            </div>

                        </div>

                    <?php endif; ?>



                    <!-- ==================================================
                         FORM
                    ================================================== -->

                    <form
                        method="POST"
                        action=""
                        autocomplete="off"
                    >


                        <div
                            class="user-form-body"
                        >


                            <div
                                class="form-grid"
                            >


                                <!-- ==========================================
                                     NAMA LENGKAP
                                =========================================== -->

                                <div
                                    class="form-group full"
                                >

                                    <label
                                        class="form-label"
                                        for="nama_lengkap"
                                    >

                                        <i
                                            data-lucide="user"
                                        ></i>

                                        Nama Lengkap

                                        <span
                                            class="required"
                                        >
                                            *
                                        </span>

                                    </label>


                                    <input
                                        type="text"
                                        id="nama_lengkap"
                                        name="nama_lengkap"
                                        class="form-input"
                                        placeholder="Masukkan nama lengkap"
                                        value="<?= htmlspecialchars(
                                            $namaLengkap
                                        ) ?>"
                                        maxlength="100"
                                        required
                                    >


                                </div>



                                <!-- ==========================================
                                     USERNAME
                                =========================================== -->

                                <div
                                    class="form-group"
                                >

                                    <label
                                        class="form-label"
                                        for="username"
                                    >

                                        <i
                                            data-lucide="at-sign"
                                        ></i>

                                        Username

                                        <span
                                            class="required"
                                        >
                                            *
                                        </span>

                                    </label>


                                    <input
                                        type="text"
                                        id="username"
                                        name="username"
                                        class="form-input"
                                        placeholder="Contoh: arly123"
                                        value="<?= htmlspecialchars(
                                            $username
                                        ) ?>"
                                        maxlength="50"
                                        required
                                    >


                                    <div
                                        class="form-help"
                                    >
                                        Username digunakan untuk
                                        login ke sistem.
                                    </div>

                                </div>



                                <!-- ==========================================
                                     PASSWORD
                                =========================================== -->

                                <div
                                    class="form-group"
                                >

                                    <label
                                        class="form-label"
                                        for="password"
                                    >

                                        <i
                                            data-lucide="lock-keyhole"
                                        ></i>

                                        Password

                                        <span
                                            class="required"
                                        >
                                            *
                                        </span>

                                    </label>


                                    <div
                                        class="password-wrapper"
                                    >

                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            class="form-input"
                                            placeholder="Masukkan password"
                                            minlength="6"
                                            required
                                        >


                                        <button
                                            type="button"
                                            class="password-toggle"
                                            id="togglePassword"
                                            title="Tampilkan password"
                                        >

                                            <i
                                                data-lucide="eye"
                                            ></i>

                                        </button>

                                    </div>


                                    <div
                                        class="form-help"
                                    >
                                        Password minimal 6 karakter.
                                    </div>

                                </div>



                                <!-- ==========================================
                                     ROLE
                                =========================================== -->

                                <div
                                    class="form-group"
                                >

                                    <label
                                        class="form-label"
                                    >

                                        <i
                                            data-lucide="shield"
                                        ></i>

                                        Role

                                        <span
                                            class="required"
                                        >
                                            *
                                        </span>

                                    </label>


                                    <div
                                        class="role-options"
                                    >


                                        <!-- ADMIN -->

                                        <div
                                            class="role-option"
                                        >

                                            <input
                                                type="radio"
                                                id="role_admin"
                                                name="role"
                                                value="admin"
                                                <?= $role === 'admin'
                                                    ? 'checked'
                                                    : '' ?>
                                                required
                                            >


                                            <label
                                                for="role_admin"
                                            >

                                                <i
                                                    data-lucide="shield-check"
                                                ></i>

                                                <span>
                                                    Admin
                                                </span>

                                            </label>

                                        </div>



                                        <!-- PETUGAS -->

                                        <div
                                            class="role-option"
                                        >

                                            <input
                                                type="radio"
                                                id="role_petugas"
                                                name="role"
                                                value="petugas"
                                                <?= $role === 'petugas'
                                                    ? 'checked'
                                                    : '' ?>
                                            >


                                            <label
                                                for="role_petugas"
                                            >

                                                <i
                                                    data-lucide="user-round-check"
                                                ></i>

                                                <span>
                                                    Petugas
                                                </span>

                                            </label>

                                        </div>



                                        <!-- OWNER -->

                                        <div
                                            class="role-option"
                                        >

                                            <input
                                                type="radio"
                                                id="role_owner"
                                                name="role"
                                                value="owner"
                                                <?= $role === 'owner'
                                                    ? 'checked'
                                                    : '' ?>
                                            >


                                            <label
                                                for="role_owner"
                                            >

                                                <i
                                                    data-lucide="crown"
                                                ></i>

                                                <span>
                                                    Owner
                                                </span>

                                            </label>

                                        </div>


                                    </div>

                                </div>



                                <!-- ==========================================
                                     STATUS
                                =========================================== -->

                                <div
                                    class="form-group"
                                >

                                    <label
                                        class="form-label"
                                    >

                                        <i
                                            data-lucide="power"
                                        ></i>

                                        Status Akun

                                        <span
                                            class="required"
                                        >
                                            *
                                        </span>

                                    </label>


                                    <div
                                        class="status-options"
                                    >


                                        <!-- AKTIF -->

                                        <div
                                            class="status-option"
                                        >

                                            <input
                                                type="radio"
                                                id="status_aktif"
                                                name="status_aktif"
                                                value="1"
                                                <?= $statusAktif === '1'
                                                    ? 'checked'
                                                    : '' ?>
                                            >


                                            <label
                                                for="status_aktif"
                                            >

                                                <i
                                                    data-lucide="circle-check"
                                                ></i>

                                                Aktif

                                            </label>

                                        </div>



                                        <!-- NONAKTIF -->

                                        <div
                                            class="status-option"
                                        >

                                            <input
                                                type="radio"
                                                id="status_nonaktif"
                                                name="status_aktif"
                                                value="0"
                                                <?= $statusAktif === '0'
                                                    ? 'checked'
                                                    : '' ?>
                                            >


                                            <label
                                                for="status_nonaktif"
                                            >

                                                <i
                                                    data-lucide="circle-x"
                                                ></i>

                                                Nonaktif

                                            </label>

                                        </div>


                                    </div>

                                </div>


                            </div>


                        </div>



                        <!-- ==================================================
                             FORM FOOTER
                        ================================================== -->

                        <div
                            class="user-form-footer"
                        >


                            <a
                                href="user.php"
                                class="
                                    btn-form
                                    btn-batal
                                "
                            >

                                <i
                                    data-lucide="x"
                                ></i>

                                Batal

                            </a>


                            <button
                                type="submit"
                                class="
                                    btn-form
                                    btn-simpan
                                "
                            >

                                <i
                                    data-lucide="user-plus"
                                ></i>

                                Simpan User

                            </button>


                        </div>


                    </form>


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
        // LUCIDE
        // ==================================================

        lucide.createIcons();


        // ==================================================
        // TOGGLE PASSWORD
        // ==================================================

        const passwordInput =
            document.getElementById(
                'password'
            );


        const togglePassword =
            document.getElementById(
                'togglePassword'
            );


        if (
            passwordInput &&
            togglePassword
        ) {

            togglePassword.addEventListener(
                'click',
                function () {

                    const icon =
                        passwordInput.type ===
                        'password'
                            ? 'eye-off'
                            : 'eye';


                    passwordInput.type =
                        passwordInput.type ===
                        'password'
                            ? 'text'
                            : 'password';


                    togglePassword.innerHTML =
                        '<i data-lucide="' +
                        icon +
                        '"></i>';


                    lucide.createIcons();

                }
            );

        }

    }
);

</script>


</body>

</html>