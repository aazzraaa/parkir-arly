<?php

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/auth.php';

requireRole('admin');

$pdo = getDatabaseConnection('admin');

$namaUser = getNamaUser();

$error = '';


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
        jenis_kendaraan,
        warna,
        pemilik,
        id_user
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
// PROSES UPDATE
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $platNomor = strtoupper(trim($_POST['plat_nomor'] ?? ''));
    $jenis     = $_POST['jenis_kendaraan'] ?? '';
    $warna     = trim($_POST['warna'] ?? '');
    $pemilik   = trim($_POST['pemilik'] ?? '');


    // ==================================================
    // VALIDASI
    // ==================================================

    if ($platNomor === '') {

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
              AND id_kendaraan != ?
        ");

        $stmtCek->execute([
            $platNomor,
            $idKendaraan
        ]);

        $jumlahPlat = $stmtCek->fetchColumn();


        if ($jumlahPlat > 0) {

            $error = 'Plat nomor tersebut sudah digunakan kendaraan lain.';

        } else {

            try {

                $pdo->beginTransaction();


                // ======================================
                // UPDATE KENDARAAN
                // ======================================

                $stmtUpdate = $pdo->prepare("
                    UPDATE tb_kendaraan
                    SET
                        plat_nomor = ?,
                        jenis_kendaraan = ?,
                        warna = ?,
                        pemilik = ?
                    WHERE id_kendaraan = ?
                ");

                $stmtUpdate->execute([
                    $platNomor,
                    $jenis,
                    $warna,
                    $pemilik,
                    $idKendaraan
                ]);


                // ======================================
                // LOG AKTIVITAS
                // ======================================

                $aktivitas =
                    "Mengubah kendaraan {$platNomor} - Pemilik: {$pemilik}";

                $stmtUser = $pdo->prepare("
                    SELECT id_user
                    FROM tb_user
                    WHERE nama_lengkap = ?
                      AND role = 'admin'
                    LIMIT 1
                ");

                $stmtUser->execute([$namaUser]);

                $adminUserId = $stmtUser->fetchColumn();


                if ($adminUserId) {

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


                header('Location: kendaraan.php?status=edit');
                exit;

            } catch (PDOException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Gagal memperbarui data: ' . $e->getMessage();
            }

        }
    }


    // ==================================================
    // UPDATE DATA YANG DITAMPILKAN
    // ==================================================

    $kendaraan['plat_nomor'] = $platNomor;
    $kendaraan['jenis_kendaraan'] = $jenis;
    $kendaraan['warna'] = $warna;
    $kendaraan['pemilik'] = $pemilik;
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

    <title>Edit Kendaraan - Admin Parkir</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <script src="https://unpkg.com/lucide@latest"></script>


    <style>

        .edit-page {
            padding: 34px;
            min-height: calc(100vh - 88px);
        }

        .edit-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            gap: 20px;
        }

        .edit-header-left {
            display: flex;
            align-items: center;
            gap: 17px;
        }

        .edit-header-icon {
            width: 60px;
            height: 60px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 19px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #541747,
                    #8b3b7c,
                    #b85aa9
                );

            box-shadow:
                0 12px 27px rgba(91,35,81,.22);
        }

        .edit-header-icon svg {
            width: 28px;
            height: 28px;
        }

        .edit-header h1 {
            margin: 0;

            font-size: 29px;
            font-weight: 800;

            color: #2f1b30;
        }

        .edit-header p {
            margin: 6px 0 0;

            color: #877587;

            font-size: 14px;
        }

        .edit-back {
            height: 45px;

            padding: 0 17px;

            display: inline-flex;
            align-items: center;
            gap: 8px;

            color: #63485f;

            background: #fff;

            border: 1px solid #e8dfe9;

            border-radius: 13px;

            text-decoration: none;

            font-size: 13px;
            font-weight: 700;
        }

        .edit-back svg {
            width: 17px;
            height: 17px;
        }

        .edit-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                310px;

            gap: 25px;

            max-width: 1250px;
        }

        .edit-card {
            background:
                linear-gradient(
                    145deg,
                    #fff,
                    #fbf7fb
                );

            border: 1px solid #eee4ef;

            border-radius: 25px;

            padding: 32px;

            box-shadow:
                0 18px 45px rgba(75,27,68,.09);
        }

        .edit-card-title {
            display: flex;
            align-items: center;
            gap: 12px;

            padding-bottom: 20px;

            margin-bottom: 27px;

            border-bottom: 1px solid #eee5ef;
        }

        .edit-card-title-icon {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 13px;

            color: #74366b;

            background: #f0e1ee;
        }

        .edit-card-title-icon svg {
            width: 21px;
            height: 21px;
        }

        .edit-card-title h2 {
            margin: 0;

            color: #382538;

            font-size: 18px;
        }

        .edit-card-title p {
            margin: 4px 0 0;

            color: #958596;

            font-size: 12px;
        }

        .edit-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 23px 20px;
        }

        .edit-group {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .edit-label {
            display: flex;
            align-items: center;
            gap: 7px;

            color: #402c3e;

            font-size: 13px;
            font-weight: 800;
        }

        .edit-label svg {
            width: 15px;
            height: 15px;

            color: #8b4c82;
        }

        .edit-input-wrap {
            position: relative;
        }

        .edit-input-icon {
            position: absolute;

            left: 16px;
            top: 50%;

            transform: translateY(-50%);

            width: 18px;
            height: 18px;

            color: #99708f;

            pointer-events: none;
        }

        .edit-control {
            width: 100%;
            height: 54px;

            box-sizing: border-box;

            padding: 0 16px 0 48px;

            border: 1px solid #e5dce6;

            border-radius: 15px;

            outline: none;

            background: #fff;

            color: #382637;

            font-size: 14px;
            font-weight: 600;
        }

        .edit-control:focus {
            border-color: #96518a;

            box-shadow:
                0 0 0 4px rgba(150,81,138,.10);
        }

        .edit-help {
            color: #9b8c9a;

            font-size: 11px;
        }

        .edit-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;

            margin-top: 30px;
            padding-top: 23px;

            border-top: 1px solid #eee5ef;
        }

        .edit-btn {
            height: 49px;

            padding: 0 21px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            border-radius: 14px;

            border: none;

            text-decoration: none;

            font-size: 13px;
            font-weight: 800;

            cursor: pointer;
        }

        .edit-btn svg {
            width: 17px;
            height: 17px;
        }

        .edit-btn-cancel {
            color: #654e62;
            background: #f1ebf2;
        }

        .edit-btn-save {
            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #61204f,
                    #873875,
                    #a74e98
                );

            box-shadow:
                0 9px 22px rgba(103,36,91,.22);
        }

        .edit-alert {
            max-width: 1250px;

            display: flex;
            align-items: flex-start;
            gap: 11px;

            margin-bottom: 20px;

            padding: 15px 17px;

            border-radius: 14px;

            background: #fff1f2;

            border: 1px solid #ffd6da;

            color: #a52f3c;

            font-size: 13px;
            font-weight: 700;
        }

        .edit-alert svg {
            width: 18px;
            height: 18px;

            flex-shrink: 0;
        }

        .edit-info {
            padding: 27px;

            border-radius: 25px;

            color: #fff;

            background:
                linear-gradient(
                    150deg,
                    #431438,
                    #652254,
                    #81356f
                );

            box-shadow:
                0 18px 42px rgba(76,26,68,.19);
        }

        .edit-info-icon {
            width: 53px;
            height: 53px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 18px;

            border-radius: 16px;

            background: rgba(255,255,255,.12);
        }

        .edit-info-icon svg {
            width: 26px;
            height: 26px;
        }

        .edit-info h3 {
            margin: 0 0 8px;

            font-size: 19px;
        }

        .edit-info p {
            margin: 0;

            color: rgba(255,255,255,.68);

            font-size: 12px;
            line-height: 1.7;
        }

        .edit-info-item {
            margin-top: 18px;

            padding: 13px;

            border-radius: 14px;

            background: rgba(255,255,255,.07);

            border: 1px solid rgba(255,255,255,.07);
        }

        .edit-info-item strong {
            display: block;

            margin-bottom: 4px;

            font-size: 11px;
        }

        .edit-info-item span {
            color: rgba(255,255,255,.61);

            font-size: 10px;
            line-height: 1.5;
        }

        @media (max-width: 1000px) {

            .edit-layout {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 700px) {

            .edit-page {
                padding: 20px;
            }

            .edit-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .edit-header h1 {
                font-size: 24px;
            }

            .edit-grid {
                grid-template-columns: 1fr;
            }

            .edit-card {
                padding: 22px;
            }

            .edit-actions {
                flex-direction: column;
            }

            .edit-btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<?php include 'navbar.php'; ?>


<main class="main-content">

    <div class="edit-page">


        <div class="edit-header">

            <div class="edit-header-left">

                <div class="edit-header-icon">

                    <i data-lucide="pencil"></i>

                </div>

                <div>

                    <h1>Edit Kendaraan</h1>

                    <p>
                        Perbarui informasi kendaraan yang terdaftar.
                    </p>

                </div>

            </div>


            <a
                href="kendaraan.php"
                class="edit-back"
            >

                <i data-lucide="arrow-left"></i>

                Kembali

            </a>

        </div>


        <?php if ($error): ?>

            <div class="edit-alert">

                <i data-lucide="circle-alert"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>

            </div>

        <?php endif; ?>


        <div class="edit-layout">


            <div class="edit-card">


                <div class="edit-card-title">

                    <div class="edit-card-title-icon">

                        <i data-lucide="clipboard-pen"></i>

                    </div>

                    <div>

                        <h2>
                            Informasi Kendaraan
                        </h2>

                        <p>
                            Ubah data sesuai kondisi kendaraan.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <div class="edit-grid">


                        <!-- PLAT -->

                        <div class="edit-group">

                            <label
                                class="edit-label"
                                for="plat_nomor"
                            >

                                <i data-lucide="hash"></i>

                                Plat Nomor

                            </label>


                            <div class="edit-input-wrap">

                                <i
                                    data-lucide="car-front"
                                    class="edit-input-icon"
                                ></i>

                                <input
                                    type="text"
                                    id="plat_nomor"
                                    name="plat_nomor"
                                    class="edit-control"
                                    value="<?= htmlspecialchars($kendaraan['plat_nomor']) ?>"
                                    required
                                >

                            </div>

                            <span class="edit-help">
                                Plat nomor harus unik.
                            </span>

                        </div>


                        <!-- JENIS -->

                        <div class="edit-group">

                            <label
                                class="edit-label"
                                for="jenis_kendaraan"
                            >

                                <i data-lucide="layers-3"></i>

                                Jenis Kendaraan

                            </label>


                            <div class="edit-input-wrap">

                                <i
                                    data-lucide="car"
                                    class="edit-input-icon"
                                ></i>

                                <select
                                    id="jenis_kendaraan"
                                    name="jenis_kendaraan"
                                    class="edit-control"
                                    required
                                >

                                    <option
                                        value="motor"
                                        <?= $kendaraan['jenis_kendaraan'] === 'motor' ? 'selected' : '' ?>
                                    >
                                        Motor
                                    </option>

                                    <option
                                        value="mobil"
                                        <?= $kendaraan['jenis_kendaraan'] === 'mobil' ? 'selected' : '' ?>
                                    >
                                        Mobil
                                    </option>

                                    <option
                                        value="lainnya"
                                        <?= $kendaraan['jenis_kendaraan'] === 'lainnya' ? 'selected' : '' ?>
                                    >
                                        Lainnya
                                    </option>

                                </select>

                            </div>

                        </div>


                        <!-- WARNA -->

                        <div class="edit-group">

                            <label
                                class="edit-label"
                                for="warna"
                            >

                                <i data-lucide="palette"></i>

                                Warna Kendaraan

                            </label>


                            <div class="edit-input-wrap">

                                <i
                                    data-lucide="paintbrush"
                                    class="edit-input-icon"
                                ></i>

                                <input
                                    type="text"
                                    id="warna"
                                    name="warna"
                                    class="edit-control"
                                    value="<?= htmlspecialchars($kendaraan['warna']) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- PEMILIK -->

                        <div class="edit-group">

                            <label
                                class="edit-label"
                                for="pemilik"
                            >

                                <i data-lucide="user-round"></i>

                                Pemilik Kendaraan

                            </label>


                            <div class="edit-input-wrap">

                                <i
                                    data-lucide="user"
                                    class="edit-input-icon"
                                ></i>

                                <input
                                    type="text"
                                    id="pemilik"
                                    name="pemilik"
                                    class="edit-control"
                                    value="<?= htmlspecialchars($kendaraan['pemilik']) ?>"
                                    required
                                >

                            </div>

                        </div>


                    </div>


                    <div class="edit-actions">

                        <a
                            href="kendaraan.php"
                            class="edit-btn edit-btn-cancel"
                        >

                            <i data-lucide="x"></i>

                            Batal

                        </a>


                        <button
                            type="submit"
                            class="edit-btn edit-btn-save"
                        >

                            <i data-lucide="save"></i>

                            Simpan Perubahan

                        </button>

                    </div>


                </form>

            </div>


            <div class="edit-info">

                <div class="edit-info-icon">

                    <i data-lucide="file-pen-line"></i>

                </div>

                <h3>
                    Edit Data
                </h3>

                <p>
                    Perubahan akan langsung tersimpan ke database
                    setelah tombol Simpan Perubahan ditekan.
                </p>


                <div class="edit-info-item">

                    <strong>
                        Data yang dapat diubah
                    </strong>

                    <span>
                        Plat nomor, jenis kendaraan, warna,
                        dan nama pemilik.
                    </span>

                </div>


                <div class="edit-info-item">

                    <strong>
                        User pencatat
                    </strong>

                    <span>
                        ID user tidak diubah karena merupakan
                        pencatat data pada sistem.
                    </span>

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