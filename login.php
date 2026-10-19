<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

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
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if ($username === '' || $password === '' || $role === '') {

        $error = 'Username, password, dan level wajib diisi.';

    } else {

        try {

            /*
             * Koneksi database berdasarkan role
             */
            $pdo = getDatabaseConnection($role);

            /*
             * Kredensial login berdasarkan role
             */
            $validUsers = [

                'admin' => [
                    'username' => 'admin_parkir',
                    'password' => 'Admin123!',
                    'nama'     => 'Administrator'
                ],

                'petugas' => [
                    'username' => 'petugas_parkir',
                    'password' => 'Petugas123!',
                    'nama'     => 'Petugas Parkir'
                ],

                'owner' => [
                    'username' => 'owner_parkir',
                    'password' => 'Owner123!',
                    'nama'     => 'Owner Parkir'
                ]

            ];

            if (
                isset($validUsers[$role]) &&
                $username === $validUsers[$role]['username'] &&
                $password === $validUsers[$role]['password']
            ) {

                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = $role;
                $_SESSION['nama_lengkap'] = $validUsers[$role]['nama'];
                $_SESSION['db_role'] = $role;

                switch ($role) {

                    case 'admin':
                        header('Location: admin/dashboard.php');
                        exit;

                    case 'petugas':
                        header('Location: petugas/dashboard.php');
                        exit;

                    case 'owner':
                        header('Location: owner/dashboard.php');
                        exit;
                }

            } else {

                $error = 'Username atau password salah.';

            }

        } catch (Exception $e) {

            $error = 'Koneksi database gagal: ' . $e->getMessage();

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

    <title>Login - Sistem Parkir</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background: #ffffff;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
        }


        /* ==================================================
           BAGIAN KIRI
        ================================================== */

        .login-left {
            width: 51%;
            min-height: 100vh;
            background: #ffffff;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 50px 70px;
        }

        .login-content {
            width: 100%;
            max-width: 430px;
        }


        /* ==================================================
           LOGO
        ================================================== */

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;

            margin-bottom: 85px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;

            background: #6040a4;

            border-radius: 9px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;
            font-size: 21px;
            font-weight: 700;

            box-shadow: 0 5px 14px rgba(96, 64, 164, 0.22);
        }

        .brand-icon::before {
            content: "P";
        }

        .brand-name {
            font-size: 25px;
            font-weight: 700;
            color: #202020;
            letter-spacing: -0.5px;
        }


        /* ==================================================
           JUDUL
        ================================================== */

        .welcome-title {
            font-size: 42px;
            line-height: 1.15;
            color: #111111;
            font-weight: 700;

            margin-bottom: 10px;
        }

        .welcome-text {
            color: #8a8a98;
            font-size: 16px;

            margin-bottom: 36px;
        }


        /* ==================================================
           ERROR
        ================================================== */

        .error {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;

            padding: 12px 14px;

            border-radius: 8px;

            font-size: 14px;

            margin-bottom: 20px;
        }


        /* ==================================================
           FORM
        ================================================== */

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;

            color: #202020;

            font-size: 15px;
            font-weight: 500;

            margin-bottom: 9px;
        }

        input,
        select {
            width: 100%;

            height: 50px;

            border: 1px solid #d9d9df;

            border-radius: 7px;

            background: #ffffff;

            padding: 0 15px;

            color: #222222;

            font-size: 15px;

            outline: none;

            transition: 0.2s;
        }

        input::placeholder {
            color: #aaaab4;
        }

        input:focus,
        select:focus {
            border-color: #7655b8;

            box-shadow: 0 0 0 3px rgba(118, 85, 184, 0.10);
        }

        select {
            cursor: pointer;
        }


        /* ==================================================
           TOMBOL LOGIN
        ================================================== */

        .login-button {
            width: 100%;

            height: 50px;

            border: none;

            border-radius: 7px;

            background: #6040a4;

            color: white;

            font-size: 15px;
            font-weight: 600;

            cursor: pointer;

            margin-top: 4px;

            transition: 0.2s;

            box-shadow: 0 5px 14px rgba(96, 64, 164, 0.18);
        }

        .login-button:hover {
            background: #51338f;

            transform: translateY(-1px);

            box-shadow: 0 7px 18px rgba(96, 64, 164, 0.25);
        }

        .login-button:active {
            transform: translateY(0);
        }


        /* ==================================================
           FOOTER
        ================================================== */

        .footer {
            text-align: center;

            color: #9999a5;

            font-size: 13px;

            margin-top: 28px;
        }


        /* ==================================================
           BAGIAN KANAN
        ================================================== */

        .login-right {
            width: 49%;
            min-height: 100vh;

            position: relative;
            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #a98ae3 0%,
                    #9370d5 45%,
                    #7654bd 100%
                );

            display: flex;
            align-items: center;
            justify-content: center;
        }


        /* ==================================================
           DEKORASI
        ================================================== */

        .circle {
            position: absolute;

            border: 2px solid rgba(255, 255, 255, 0.18);

            border-radius: 50%;
        }

        .circle-one {
            width: 420px;
            height: 420px;

            right: -170px;
            top: -120px;
        }

        .circle-two {
            width: 300px;
            height: 300px;

            left: -160px;
            bottom: -120px;
        }

        .circle-three {
            width: 150px;
            height: 150px;

            right: 70px;
            bottom: 80px;
        }


        /* ==================================================
           POLA DEKORASI
        ================================================== */

        .dots {
            position: absolute;

            width: 100%;
            height: 100%;

            opacity: 0.25;
        }

        .dot {
            position: absolute;

            width: 7px;
            height: 7px;

            border: 2px solid white;

            border-radius: 50%;
        }

        .line {
            position: absolute;

            height: 2px;

            background: rgba(255, 255, 255, 0.28);

            border-radius: 5px;
        }


        /* ==================================================
           ILUSTRASI SISTEM PARKIR
        ================================================== */

        .illustration {
            position: relative;

            z-index: 5;

            width: 500px;
            max-width: 80%;
        }

        .illustration-card {
            position: relative;

            width: 100%;
            height: 370px;

            background: rgba(255, 255, 255, 0.94);

            border-radius: 25px;

            box-shadow:
                0 30px 70px rgba(50, 20, 100, 0.25);

            padding: 28px;
        }


        /* Header kartu */

        .card-top {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 28px;
        }

        .card-title {
            color: #5b3b9c;

            font-size: 20px;

            font-weight: 700;
        }

        .card-menu {
            display: flex;

            gap: 5px;
        }

        .card-menu span {
            width: 6px;
            height: 6px;

            background: #9876d3;

            border-radius: 50%;
        }


        /* Area parkir */

        .parking-area {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 12px;

            margin-bottom: 25px;
        }

        .parking-slot {
            height: 95px;

            border-radius: 13px;

            border: 2px dashed #c9b7e9;

            background: #f7f3fc;

            display: flex;

            align-items: center;

            justify-content: center;

            position: relative;
        }

        .parking-slot.active {
            background: #e9def8;

            border-color: #9370d5;
        }

        .parking-slot::before {
            content: "P";

            width: 38px;
            height: 38px;

            border-radius: 50%;

            background: #7555b5;

            color: white;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 17px;

            font-weight: 700;
        }


        /* Mobil sederhana */

        .car {
            position: absolute;

            width: 42px;
            height: 20px;

            background: #6848a9;

            border-radius: 7px 7px 5px 5px;

            bottom: 15px;
        }

        .car::before,
        .car::after {
            content: "";

            position: absolute;

            width: 8px;
            height: 8px;

            background: #3e286b;

            border-radius: 50%;

            bottom: -4px;
        }

        .car::before {
            left: 5px;
        }

        .car::after {
            right: 5px;
        }


        /* Informasi */

        .card-information {
            display: flex;

            gap: 12px;
        }

        .info-box {
            flex: 1;

            padding: 15px;

            border-radius: 12px;

            background: #f5f1fb;
        }

        .info-number {
            display: block;

            color: #5d3e9e;

            font-size: 22px;

            font-weight: 700;

            margin-bottom: 4px;
        }

        .info-label {
            color: #9992a8;

            font-size: 12px;
        }


        /* ==================================================
           TEKS KANAN
        ================================================== */

        .right-caption {
            position: absolute;

            bottom: 65px;

            left: 0;
            right: 0;

            text-align: center;

            color: rgba(255, 255, 255, 0.9);

            z-index: 10;
        }

        .right-caption h2 {
            font-size: 25px;

            margin-bottom: 8px;
        }

        .right-caption p {
            font-size: 14px;

            color: rgba(255, 255, 255, 0.72);
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 950px) {

            .login-left {
                width: 100%;
            }

            .login-right {
                display: none;
            }

            .login-left {
                padding: 40px 25px;
            }

            .brand {
                margin-bottom: 60px;
            }

        }


        @media (max-width: 500px) {

            .welcome-title {
                font-size: 34px;
            }

            .brand-name {
                font-size: 22px;
            }

            .login-content {
                max-width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="login-wrapper">


    <!-- ==================================================
         BAGIAN KIRI - FORM LOGIN
    ================================================== -->

    <div class="login-left">

        <div class="login-content">


            <!-- LOGO -->

            <div class="brand">

                <div class="brand-icon"></div>

                <div class="brand-name">
                    Sistem Parkir
                </div>

            </div>


            <!-- JUDUL -->

            <h1 class="welcome-title">
                Selamat datang
            </h1>

            <p class="welcome-text">
                Silakan masukkan data untuk masuk ke sistem
            </p>


            <!-- ERROR -->

            <?php if ($error !== ''): ?>

                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form method="POST">


                <!-- LEVEL -->

                <div class="form-group">

                    <label for="role">
                        Level
                    </label>

                    <select
                        name="role"
                        id="role"
                        required
                    >

                        <option value="">
                            Pilih level pengguna
                        </option>

                        <option
                            value="admin"
                            <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>
                        >
                            Admin
                        </option>

                        <option
                            value="petugas"
                            <?= (($_POST['role'] ?? '') === 'petugas') ? 'selected' : '' ?>
                        >
                            Petugas
                        </option>

                        <option
                            value="owner"
                            <?= (($_POST['role'] ?? '') === 'owner') ? 'selected' : '' ?>
                        >
                            Owner
                        </option>

                    </select>

                </div>


                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="login-button"
                >
                    Masuk ke Sistem
                </button>

            </form>


            <!-- FOOTER -->

            <div class="footer">
                Sistem Manajemen Parkir
            </div>

        </div>

    </div>



    <!-- ==================================================
         BAGIAN KANAN - ILUSTRASI
    ================================================== -->

    <div class="login-right">


        <!-- Lingkaran dekorasi -->

        <div class="circle circle-one"></div>

        <div class="circle circle-two"></div>

        <div class="circle circle-three"></div>


        <!-- Titik dekorasi -->

        <div class="dots">

            <span class="dot" style="top: 12%; left: 15%;"></span>
            <span class="dot" style="top: 22%; left: 28%;"></span>
            <span class="dot" style="top: 15%; right: 20%;"></span>
            <span class="dot" style="top: 32%; right: 12%;"></span>
            <span class="dot" style="top: 45%; left: 10%;"></span>
            <span class="dot" style="top: 55%; right: 8%;"></span>
            <span class="dot" style="bottom: 20%; left: 18%;"></span>
            <span class="dot" style="bottom: 12%; right: 25%;"></span>


            <span
                class="line"
                style="top: 18%; left: 20%; width: 50px;"
            ></span>

            <span
                class="line"
                style="top: 27%; right: 15%; width: 70px;"
            ></span>

            <span
                class="line"
                style="top: 38%; left: 8%; width: 45px;"
            ></span>

            <span
                class="line"
                style="bottom: 28%; right: 12%; width: 60px;"
            ></span>

        </div>


        <!-- ==================================================
             KARTU ILUSTRASI PARKIR
        ================================================== -->

        <div class="illustration">

            <div class="illustration-card">


                <!-- Header -->

                <div class="card-top">

                    <div class="card-title">
                        Area Parkir
                    </div>

                    <div class="card-menu">

                        <span></span>
                        <span></span>
                        <span></span>

                    </div>

                </div>


                <!-- Slot parkir -->

                <div class="parking-area">

                    <div class="parking-slot active">

                        <div class="car"></div>

                    </div>

                    <div class="parking-slot">

                        <div class="car"></div>

                    </div>

                    <div class="parking-slot active">

                        <div class="car"></div>

                    </div>

                    <div class="parking-slot">

                    </div>

                    <div class="parking-slot active">

                        <div class="car"></div>

                    </div>

                    <div class="parking-slot">

                    </div>

                </div>


                <!-- Informasi -->

                <div class="card-information">

                    <div class="info-box">

                        <span class="info-number">
                            18
                        </span>

                        <span class="info-label">
                            Kendaraan Masuk
                        </span>

                    </div>

                    <div class="info-box">

                        <span class="info-number">
                            12
                        </span>

                        <span class="info-label">
                            Slot Tersedia
                        </span>

                    </div>

                    <div class="info-box">

                        <span class="info-number">
                            30
                        </span>

                        <span class="info-label">
                            Kapasitas
                        </span>

                    </div>

                </div>


            </div>

        </div>


        <!-- Caption -->

        <div class="right-caption">

            <h2>
                Sistem Parkir
            </h2>

            <p>
                Kelola parkir dengan mudah dan terintegrasi
            </p>

        </div>


    </div>

</div>

</body>

</html>