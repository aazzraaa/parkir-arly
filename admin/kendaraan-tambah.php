<?php

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/auth.php';

requireRole('admin');

$pdo = getDatabaseConnection('admin');

$namaUser = getNamaUser();

$error = '';


// ======================================================
// AMBIL ID USER ADMIN YANG SEDANG LOGIN
// ======================================================

$stmtUser = $pdo->prepare("
    SELECT id_user
    FROM tb_user
    WHERE nama_lengkap = ?
      AND role = 'admin'
    LIMIT 1
");

$stmtUser->execute([$namaUser]);

$adminUserId = $stmtUser->fetchColumn();


// ======================================================
// PROSES SIMPAN DATA
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $platNomor = strtoupper(trim($_POST['plat_nomor'] ?? ''));
    $jenis     = $_POST['jenis_kendaraan'] ?? '';
    $warna     = trim($_POST['warna'] ?? '');
    $pemilik   = trim($_POST['pemilik'] ?? '');

    // ==================================================
    // VALIDASI
    // ==================================================

    if (!$adminUserId) {

        $error = 'Data admin yang sedang login tidak ditemukan. Silakan logout dan login kembali.';

    } elseif ($platNomor === '') {

        $error = 'Plat nomor wajib diisi.';

    } elseif ($jenis === '') {

        $error = 'Jenis kendaraan wajib dipilih.';

    } elseif ($warna === '') {

        $error = 'Warna kendaraan wajib diisi.';

    } elseif ($pemilik === '') {

        $error = 'Nama pemilik kendaraan wajib diisi.';

    } elseif (!in_array($jenis, ['motor', 'mobil', 'lainnya'], true)) {

        $error = 'Jenis kendaraan tidak valid.';

    } else {

        // ==============================================
        // CEK PLAT NOMOR
        // ==============================================

        $stmtCek = $pdo->prepare("
            SELECT COUNT(*)
            FROM tb_kendaraan
            WHERE plat_nomor = ?
        ");

        $stmtCek->execute([$platNomor]);

        $jumlahPlat = $stmtCek->fetchColumn();

        if ($jumlahPlat > 0) {

            $error = 'Plat nomor tersebut sudah terdaftar.';

        } else {

            try {

                $pdo->beginTransaction();

                // ======================================
                // SIMPAN KENDARAAN
                // ======================================

                $stmt = $pdo->prepare("
                    INSERT INTO tb_kendaraan
                    (
                        plat_nomor,
                        jenis_kendaraan,
                        warna,
                        pemilik,
                        id_user
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

                $stmt->execute([
                    $platNomor,
                    $jenis,
                    $warna,
                    $pemilik,
                    $adminUserId
                ]);


                // ======================================
                // LOG AKTIVITAS
                // ======================================

                $aktivitas = "Menambahkan kendaraan {$platNomor} - Pemilik: {$pemilik}";

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


                $pdo->commit();

                header('Location: kendaraan.php?status=tambah');
                exit;

            } catch (PDOException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Gagal menyimpan data kendaraan: ' . $e->getMessage();
            }
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

    <title>Tambah Kendaraan - Admin Parkir</title>


    <!-- ADMIN CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >


    <!-- LUCIDE -->

    <script src="https://unpkg.com/lucide@latest"></script>


    <style>

        /* =====================================================
           PAGE
        ===================================================== */

        .vehicle-form-page {
            padding: 34px;
            min-height: calc(100vh - 88px);
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .vehicle-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 30px;
        }

        .vehicle-header-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .vehicle-header-icon {
            width: 62px;
            height: 62px;
            border-radius: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #541747,
                    #8b3b7c,
                    #b85aa9
                );

            box-shadow:
                0 12px 28px rgba(91, 35, 81, .25),
                inset 0 1px 0 rgba(255,255,255,.25);
        }

        .vehicle-header-icon svg {
            width: 30px;
            height: 30px;
        }

        .vehicle-header-text h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 800;
            color: #2e1930;
            letter-spacing: -.5px;
        }

        .vehicle-header-text p {
            margin: 7px 0 0;
            font-size: 14px;
            color: #806d80;
        }


        /* =====================================================
           BACK BUTTON
        ===================================================== */

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            height: 45px;
            padding: 0 17px;

            border-radius: 13px;

            background: rgba(255,255,255,.85);
            border: 1px solid #eadfeb;

            color: #62455f;

            text-decoration: none;
            font-size: 13px;
            font-weight: 700;

            box-shadow: 0 7px 18px rgba(78, 30, 70, .07);

            transition: .2s ease;
        }

        .back-button svg {
            width: 17px;
            height: 17px;
        }

        .back-button:hover {
            transform: translateY(-2px);
            background: #fff;
            border-color: #d9c7d8;
            box-shadow: 0 10px 22px rgba(78, 30, 70, .11);
        }


        /* =====================================================
           MAIN FORM LAYOUT
        ===================================================== */

        .vehicle-form-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 310px;
            gap: 25px;
            max-width: 1250px;
        }


        /* =====================================================
           FORM CARD
        ===================================================== */

        .vehicle-form-card {
            position: relative;
            overflow: hidden;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.98),
                    rgba(252,247,252,.96)
                );

            border: 1px solid rgba(137, 83, 130, .12);

            border-radius: 25px;

            padding: 32px;

            box-shadow:
                0 18px 45px rgba(75, 27, 68, .10),
                0 3px 10px rgba(75, 27, 68, .04);
        }

        .vehicle-form-card::before {
            content: "";

            position: absolute;

            width: 240px;
            height: 240px;

            top: -150px;
            right: -80px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(174, 93, 162, .13),
                    transparent 70%
                );

            pointer-events: none;
        }

        .form-card-title {
            position: relative;

            display: flex;
            align-items: center;
            gap: 13px;

            margin-bottom: 28px;

            padding-bottom: 20px;

            border-bottom: 1px solid #eee5ef;
        }

        .form-card-title-icon {
            width: 42px;
            height: 42px;

            border-radius: 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(
                135deg,
                #f4e5f2,
                #ead5e8
            );

            color: #74366b;
        }

        .form-card-title-icon svg {
            width: 21px;
            height: 21px;
        }

        .form-card-title h2 {
            margin: 0;

            color: #342033;

            font-size: 18px;
            font-weight: 800;
        }

        .form-card-title p {
            margin: 4px 0 0;

            color: #8b7889;

            font-size: 12px;
        }


        /* =====================================================
           FORM GRID
        ===================================================== */

        .vehicle-form-grid {
            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 23px 20px;
        }

        .vehicle-form-group {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .vehicle-form-group.full {
            grid-column: 1 / -1;
        }

        .vehicle-form-label {
            display: flex;
            align-items: center;
            gap: 7px;

            color: #402c3e;

            font-size: 13px;
            font-weight: 800;
        }

        .vehicle-form-label svg {
            width: 15px;
            height: 15px;
            color: #8b4c82;
        }


        /* =====================================================
           INPUT
        ===================================================== */

        .vehicle-input-wrap {
            position: relative;
        }

        .vehicle-input-icon {
            position: absolute;

            left: 16px;
            top: 50%;

            transform: translateY(-50%);

            width: 18px;
            height: 18px;

            color: #99708f;

            pointer-events: none;

            z-index: 2;
        }

        .vehicle-form-control {
            width: 100%;
            height: 54px;

            box-sizing: border-box;

            border: 1px solid #e5dce6;

            border-radius: 15px;

            padding: 0 16px 0 48px;

            background: rgba(255,255,255,.9);

            color: #382637;

            font-size: 14px;
            font-weight: 600;

            outline: none;

            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease,
                transform .2s ease;
        }

        .vehicle-form-control::placeholder {
            color: #b2a4b1;
            font-weight: 500;
        }

        .vehicle-form-control:hover {
            border-color: #d4c2d3;
            background: #fff;
        }

        .vehicle-form-control:focus {
            border-color: #96518a;

            background: #fff;

            box-shadow:
                0 0 0 4px rgba(150,81,138,.10),
                0 8px 20px rgba(100,40,90,.06);
        }

        select.vehicle-form-control {
            cursor: pointer;
            appearance: auto;
        }

        .vehicle-form-help {
            margin-top: -2px;

            color: #9b8c9a;

            font-size: 11px;
            line-height: 1.5;
        }


        /* =====================================================
           INFO BOX
        ===================================================== */

        .vehicle-system-info {
            margin-top: 27px;

            display: flex;
            align-items: flex-start;
            gap: 13px;

            padding: 15px 17px;

            border-radius: 15px;

            background:
                linear-gradient(
                    135deg,
                    #faf2f9,
                    #f7edf6
                );

            border: 1px solid #ead9e8;
        }

        .vehicle-system-info-icon {
            width: 34px;
            height: 34px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #ead3e7;

            color: #74366b;
        }

        .vehicle-system-info-icon svg {
            width: 17px;
            height: 17px;
        }

        .vehicle-system-info strong {
            display: block;

            margin-bottom: 3px;

            color: #56374f;

            font-size: 12px;
            font-weight: 800;
        }

        .vehicle-system-info span {
            color: #8d7889;

            font-size: 11px;
            line-height: 1.5;
        }


        /* =====================================================
           ACTION BUTTONS
        ===================================================== */

        .vehicle-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 11px;

            margin-top: 30px;
            padding-top: 23px;

            border-top: 1px solid #eee5ef;
        }

        .vehicle-btn {
            height: 49px;

            padding: 0 21px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            border-radius: 14px;

            font-size: 13px;
            font-weight: 800;

            text-decoration: none;

            border: none;

            cursor: pointer;

            transition: .2s ease;
        }

        .vehicle-btn svg {
            width: 17px;
            height: 17px;
        }

        .vehicle-btn-cancel {
            color: #654e62;

            background: #f2edf3;

            border: 1px solid #e7dce7;
        }

        .vehicle-btn-cancel:hover {
            background: #e9e0ea;
            transform: translateY(-1px);
        }

        .vehicle-btn-save {
            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #61204f,
                    #873875,
                    #a74e98
                );

            box-shadow:
                0 9px 22px rgba(103, 36, 91, .23),
                inset 0 1px 0 rgba(255,255,255,.16);
        }

        .vehicle-btn-save:hover {
            transform: translateY(-2px);

            box-shadow:
                0 13px 27px rgba(103, 36, 91, .28),
                inset 0 1px 0 rgba(255,255,255,.18);
        }


        /* =====================================================
           SIDE INFORMATION CARD
        ===================================================== */

        .vehicle-info-card {
            position: relative;
            overflow: hidden;

            min-height: 100%;

            padding: 27px;

            border-radius: 25px;

            color: #fff;

            background:
                linear-gradient(
                    150deg,
                    #431438,
                    #652254,
                    #81356f,
                    #5b1d4e
                );

            box-shadow:
                0 18px 42px rgba(76, 26, 68, .20);
        }

        .vehicle-info-card::before {
            content: "";

            position: absolute;

            width: 190px;
            height: 190px;

            right: -80px;
            top: -75px;

            border-radius: 50%;

            background: rgba(255,255,255,.07);
        }

        .vehicle-info-card::after {
            content: "";

            position: absolute;

            width: 120px;
            height: 120px;

            left: -60px;
            bottom: 30px;

            border-radius: 50%;

            background: rgba(255,255,255,.045);
        }

        .vehicle-info-top {
            position: relative;
            z-index: 1;

            margin-bottom: 30px;
        }

        .vehicle-info-icon {
            width: 54px;
            height: 54px;

            margin-bottom: 19px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 17px;

            background: rgba(255,255,255,.13);

            border: 1px solid rgba(255,255,255,.14);

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.12);
        }

        .vehicle-info-icon svg {
            width: 27px;
            height: 27px;
        }

        .vehicle-info-top h3 {
            margin: 0 0 8px;

            font-size: 19px;
            font-weight: 800;
        }

        .vehicle-info-top p {
            margin: 0;

            color: rgba(255,255,255,.68);

            font-size: 12px;
            line-height: 1.7;
        }


        /* =====================================================
           INFORMATION ITEMS
        ===================================================== */

        .vehicle-info-list {
            position: relative;
            z-index: 1;

            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .vehicle-info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            padding: 13px;

            border-radius: 15px;

            background: rgba(255,255,255,.07);

            border: 1px solid rgba(255,255,255,.07);
        }

        .vehicle-info-item-icon {
            width: 31px;
            height: 31px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: rgba(255,255,255,.10);
        }

        .vehicle-info-item-icon svg {
            width: 15px;
            height: 15px;
        }

        .vehicle-info-item strong {
            display: block;

            margin-bottom: 3px;

            font-size: 11px;
            font-weight: 800;
        }

        .vehicle-info-item span {
            display: block;

            color: rgba(255,255,255,.62);

            font-size: 10px;
            line-height: 1.5;
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .vehicle-alert-error {
            max-width: 1250px;

            display: flex;
            align-items: flex-start;
            gap: 12px;

            margin-bottom: 22px;

            padding: 15px 17px;

            border-radius: 15px;

            background: linear-gradient(
                135deg,
                #fff3f4,
                #ffedef
            );

            color: #ad2d38;

            border: 1px solid #ffd5d9;

            box-shadow: 0 8px 20px rgba(170,40,55,.06);
        }

        .vehicle-alert-error svg {
            width: 19px;
            height: 19px;

            flex-shrink: 0;

            margin-top: 1px;
        }

        .vehicle-alert-error span {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.5;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1050px) {

            .vehicle-form-layout {
                grid-template-columns: 1fr;
            }

            .vehicle-info-card {
                min-height: auto;
            }

            .vehicle-info-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 750px) {

            .vehicle-form-page {
                padding: 22px;
            }

            .vehicle-page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .vehicle-header-text h1 {
                font-size: 25px;
            }

            .vehicle-form-grid {
                grid-template-columns: 1fr;
            }

            .vehicle-form-group.full {
                grid-column: auto;
            }

            .vehicle-form-card {
                padding: 23px;
            }

            .vehicle-info-list {
                grid-template-columns: 1fr;
            }

            .vehicle-form-actions {
                flex-direction: column;
            }

            .vehicle-btn {
                width: 100%;
            }

        }


        @media (max-width: 500px) {

            .vehicle-form-page {
                padding: 16px;
            }

            .vehicle-header-left {
                align-items: flex-start;
            }

            .vehicle-header-icon {
                width: 52px;
                height: 52px;
                border-radius: 16px;
            }

            .vehicle-header-icon svg {
                width: 25px;
                height: 25px;
            }

            .vehicle-header-text h1 {
                font-size: 22px;
            }

            .vehicle-form-card {
                border-radius: 20px;
                padding: 19px;
            }

            .vehicle-info-card {
                border-radius: 20px;
                padding: 22px;
            }

        }

    </style>

</head>


<body>


<?php include 'sidebar.php'; ?>

<?php include 'navbar.php'; ?>


<main class="main-content">

    <div class="vehicle-form-page">


        <!-- ==================================================
             HEADER
        ================================================== -->

        <div class="vehicle-page-header">

            <div class="vehicle-header-left">

                <div class="vehicle-header-icon">

                    <i data-lucide="car-front"></i>

                </div>

                <div class="vehicle-header-text">

                    <h1>Tambah Kendaraan</h1>

                    <p>
                        Tambahkan data kendaraan baru ke dalam sistem parkir.
                    </p>

                </div>

            </div>


            <a
                href="kendaraan.php"
                class="back-button"
            >

                <i data-lucide="arrow-left"></i>

                Kembali ke Data Kendaraan

            </a>

        </div>


        <!-- ==================================================
             ERROR
        ================================================== -->

        <?php if ($error): ?>

            <div class="vehicle-alert-error">

                <i data-lucide="circle-alert"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             CONTENT
        ================================================== -->

        <div class="vehicle-form-layout">


            <!-- =================================================
                 FORM
            ================================================== -->

            <div class="vehicle-form-card">


                <div class="form-card-title">

                    <div class="form-card-title-icon">

                        <i data-lucide="clipboard-pen"></i>

                    </div>

                    <div>

                        <h2>Informasi Kendaraan</h2>

                        <p>
                            Lengkapi informasi kendaraan dengan benar.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <div class="vehicle-form-grid">


                        <!-- PLAT NOMOR -->

                        <div class="vehicle-form-group">

                            <label
                                for="plat_nomor"
                                class="vehicle-form-label"
                            >

                                <i data-lucide="hash"></i>

                                Plat Nomor

                            </label>


                            <div class="vehicle-input-wrap">

                                <i
                                    data-lucide="car-front"
                                    class="vehicle-input-icon"
                                ></i>

                                <input
                                    type="text"
                                    id="plat_nomor"
                                    name="plat_nomor"
                                    class="vehicle-form-control"
                                    placeholder="Contoh: N 1234 AB"
                                    value="<?= htmlspecialchars($_POST['plat_nomor'] ?? '') ?>"
                                    autocomplete="off"
                                    required
                                >

                            </div>

                            <span class="vehicle-form-help">
                                Masukkan nomor polisi kendaraan.
                            </span>

                        </div>


                        <!-- JENIS -->

                        <div class="vehicle-form-group">

                            <label
                                for="jenis_kendaraan"
                                class="vehicle-form-label"
                            >

                                <i data-lucide="layers-3"></i>

                                Jenis Kendaraan

                            </label>


                            <div class="vehicle-input-wrap">

                                <i
                                    data-lucide="car"
                                    class="vehicle-input-icon"
                                ></i>

                                <select
                                    id="jenis_kendaraan"
                                    name="jenis_kendaraan"
                                    class="vehicle-form-control"
                                    required
                                >

                                    <option value="">
                                        Pilih jenis kendaraan
                                    </option>

                                    <option
                                        value="motor"
                                        <?= ($_POST['jenis_kendaraan'] ?? '') === 'motor' ? 'selected' : '' ?>
                                    >
                                        Motor
                                    </option>

                                    <option
                                        value="mobil"
                                        <?= ($_POST['jenis_kendaraan'] ?? '') === 'mobil' ? 'selected' : '' ?>
                                    >
                                        Mobil
                                    </option>

                                    <option
                                        value="lainnya"
                                        <?= ($_POST['jenis_kendaraan'] ?? '') === 'lainnya' ? 'selected' : '' ?>
                                    >
                                        Lainnya
                                    </option>

                                </select>

                            </div>

                            <span class="vehicle-form-help">
                                Pilih kategori kendaraan.
                            </span>

                        </div>


                        <!-- WARNA -->

                        <div class="vehicle-form-group">

                            <label
                                for="warna"
                                class="vehicle-form-label"
                            >

                                <i data-lucide="palette"></i>

                                Warna Kendaraan

                            </label>


                            <div class="vehicle-input-wrap">

                                <i
                                    data-lucide="paintbrush"
                                    class="vehicle-input-icon"
                                ></i>

                                <input
                                    type="text"
                                    id="warna"
                                    name="warna"
                                    class="vehicle-form-control"
                                    placeholder="Contoh: Hitam"
                                    value="<?= htmlspecialchars($_POST['warna'] ?? '') ?>"
                                    autocomplete="off"
                                    required
                                >

                            </div>

                            <span class="vehicle-form-help">
                                Masukkan warna kendaraan.
                            </span>

                        </div>


                        <!-- PEMILIK -->

                        <div class="vehicle-form-group">

                            <label
                                for="pemilik"
                                class="vehicle-form-label"
                            >

                                <i data-lucide="user-round"></i>

                                Pemilik Kendaraan

                            </label>


                            <div class="vehicle-input-wrap">

                                <i
                                    data-lucide="user"
                                    class="vehicle-input-icon"
                                ></i>

                                <input
                                    type="text"
                                    id="pemilik"
                                    name="pemilik"
                                    class="vehicle-form-control"
                                    placeholder="Contoh: Arly"
                                    value="<?= htmlspecialchars($_POST['pemilik'] ?? '') ?>"
                                    autocomplete="off"
                                    required
                                >

                            </div>

                            <span class="vehicle-form-help">
                                Masukkan nama pemilik kendaraan.
                            </span>

                        </div>


                    </div>


                    <!-- =================================================
                         SYSTEM INFO
                    ================================================== -->

                    <div class="vehicle-system-info">

                        <div class="vehicle-system-info-icon">

                            <i data-lucide="shield-check"></i>

                        </div>

                        <div>

                            <strong>
                                Pencatatan Otomatis
                            </strong>

                            <span>
                                Data kendaraan akan otomatis dicatat
                                oleh akun admin yang sedang login.
                            </span>

                        </div>

                    </div>


                    <!-- =================================================
                         ACTION
                    ================================================== -->

                    <div class="vehicle-form-actions">

                        <a
                            href="kendaraan.php"
                            class="vehicle-btn vehicle-btn-cancel"
                        >

                            <i data-lucide="x"></i>

                            Batal

                        </a>


                        <button
                            type="submit"
                            class="vehicle-btn vehicle-btn-save"
                        >

                            <i data-lucide="save"></i>

                            Simpan Kendaraan

                        </button>

                    </div>


                </form>

            </div>


            <!-- =================================================
                 SIDE INFO
            ================================================== -->

            <div class="vehicle-info-card">


                <div class="vehicle-info-top">

                    <div class="vehicle-info-icon">

                        <i data-lucide="car-front"></i>

                    </div>

                    <h3>
                        Data Kendaraan
                    </h3>

                    <p>
                        Pastikan informasi yang dimasukkan sesuai
                        dengan kendaraan yang akan didaftarkan.
                    </p>

                </div>


                <div class="vehicle-info-list">


                    <div class="vehicle-info-item">

                        <div class="vehicle-info-item-icon">

                            <i data-lucide="badge-check"></i>

                        </div>

                        <div>

                            <strong>
                                Plat Nomor
                            </strong>

                            <span>
                                Harus unik dan belum terdaftar.
                            </span>

                        </div>

                    </div>


                    <div class="vehicle-info-item">

                        <div class="vehicle-info-item-icon">

                            <i data-lucide="car"></i>

                        </div>

                        <div>

                            <strong>
                                Jenis Kendaraan
                            </strong>

                            <span>
                                Pilih motor, mobil, atau lainnya.
                            </span>

                        </div>

                    </div>


                    <div class="vehicle-info-item">

                        <div class="vehicle-info-item-icon">

                            <i data-lucide="user-round"></i>

                        </div>

                        <div>

                            <strong>
                                Pemilik
                            </strong>

                            <span>
                                Masukkan nama pemilik kendaraan.
                            </span>

                        </div>

                    </div>


                    <div class="vehicle-info-item">

                        <div class="vehicle-info-item-icon">

                            <i data-lucide="database"></i>

                        </div>

                        <div>

                            <strong>
                                Pencatatan Sistem
                            </strong>

                            <span>
                                Admin yang sedang login akan tercatat
                                secara otomatis.
                            </span>

                        </div>

                    </div>


                </div>

            </div>


        </div>

    </div>

</main>


<script>

    lucide.createIcons();

</script>


</body>

</html>