<?php

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/auth.php';

requireRole('admin');

$pdo = getDatabaseConnection('admin');


// ======================================================
// STATISTIK
// ======================================================

$totalKendaraan = $pdo->query("
    SELECT COUNT(*) 
    FROM tb_kendaraan
")->fetchColumn();

$totalMotor = $pdo->query("
    SELECT COUNT(*) 
    FROM tb_kendaraan
    WHERE jenis_kendaraan = 'motor'
")->fetchColumn();

$totalMobil = $pdo->query("
    SELECT COUNT(*) 
    FROM tb_kendaraan
    WHERE jenis_kendaraan = 'mobil'
")->fetchColumn();

$totalLainnya = $pdo->query("
    SELECT COUNT(*) 
    FROM tb_kendaraan
    WHERE jenis_kendaraan = 'lainnya'
")->fetchColumn();


// ======================================================
// DATA KENDARAAN
// ======================================================

$stmt = $pdo->query("
    SELECT
        id_kendaraan,
        plat_nomor,
        jenis_kendaraan,
        warna,
        pemilik
    FROM tb_kendaraan
    ORDER BY id_kendaraan DESC
");

$kendaraan = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ======================================================
// STATUS
// ======================================================

$status = $_GET['status'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Kendaraan - Admin Parkir</title>


    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >


    <script src="https://unpkg.com/lucide@latest"></script>


    <style>

        /* =====================================================
           PAGE
        ===================================================== */

        .vehicle-page {
            padding: 32px;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .vehicle-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }

        .vehicle-title-area {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .vehicle-title-icon {
            width: 58px;
            height: 58px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 18px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #541747,
                    #8b3b7c,
                    #b85aa9
                );

            box-shadow:
                0 12px 25px rgba(91, 35, 81, .22);
        }

        .vehicle-title-icon svg {
            width: 28px;
            height: 28px;
        }

        .vehicle-title-area h1 {
            margin: 0;

            font-size: 29px;
            font-weight: 800;

            color: #2f1b30;
        }

        .vehicle-title-area p {
            margin: 5px 0 0;

            font-size: 14px;
            color: #877587;
        }


        /* =====================================================
           ADD BUTTON
        ===================================================== */

        .vehicle-add-button {
            height: 48px;

            padding: 0 19px;

            display: inline-flex;
            align-items: center;
            gap: 9px;

            color: white;

            text-decoration: none;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    #61204f,
                    #873875,
                    #a74e98
                );

            box-shadow:
                0 9px 22px rgba(103, 36, 91, .22);

            font-size: 13px;
            font-weight: 800;

            transition: .2s ease;
        }

        .vehicle-add-button svg {
            width: 18px;
            height: 18px;
        }

        .vehicle-add-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 13px 27px rgba(103, 36, 91, .28);
        }


        /* =====================================================
           STATISTICS
        ===================================================== */

        .vehicle-stats {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 24px;
        }

        .vehicle-stat-card {
            position: relative;
            overflow: hidden;

            padding: 21px;

            border-radius: 20px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.98),
                    rgba(250,246,251,.96)
                );

            border: 1px solid #eee4ef;

            box-shadow:
                0 10px 28px rgba(75, 27, 68, .07);

            transition: .2s ease;
        }

        .vehicle-stat-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 15px 32px rgba(75, 27, 68, .11);
        }

        .vehicle-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 15px;
        }

        .vehicle-stat-icon {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 13px;

            color: #78386e;

            background: #f1e3ef;
        }

        .vehicle-stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .vehicle-stat-label {
            font-size: 12px;
            font-weight: 700;

            color: #8d7a8c;
        }

        .vehicle-stat-value {
            font-size: 27px;
            font-weight: 850;

            color: #332034;
        }


        /* =====================================================
           TABLE CARD
        ===================================================== */

        .vehicle-table-card {
            overflow: hidden;

            background: rgba(255,255,255,.96);

            border: 1px solid #eee4ef;

            border-radius: 23px;

            box-shadow:
                0 14px 38px rgba(75, 27, 68, .08);
        }


        /* =====================================================
           TABLE HEADER
        ===================================================== */

        .vehicle-table-header {
            padding: 23px 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            border-bottom: 1px solid #eee6ef;
        }

        .vehicle-table-title {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .vehicle-table-title-icon {
            width: 39px;
            height: 39px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            color: #78386e;

            background: #f2e5f0;
        }

        .vehicle-table-title-icon svg {
            width: 19px;
            height: 19px;
        }

        .vehicle-table-title h2 {
            margin: 0;

            font-size: 17px;
            font-weight: 800;

            color: #382538;
        }

        .vehicle-table-title span {
            display: block;

            margin-top: 3px;

            font-size: 11px;
            color: #9b8c9a;
        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .vehicle-search {
            position: relative;

            width: 280px;
        }

        .vehicle-search svg {
            position: absolute;

            left: 13px;
            top: 50%;

            transform: translateY(-50%);

            width: 17px;
            height: 17px;

            color: #9b8598;
        }

        .vehicle-search input {
            width: 100%;
            height: 42px;

            box-sizing: border-box;

            border: 1px solid #e5dce6;

            border-radius: 12px;

            padding: 0 13px 0 40px;

            outline: none;

            font-size: 12px;

            background: #fcfafc;
        }

        .vehicle-search input:focus {
            border-color: #98558d;

            background: #fff;

            box-shadow:
                0 0 0 3px rgba(152,85,141,.08);
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .vehicle-table-wrapper {
            overflow-x: auto;
        }

        .vehicle-table {
            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }

        .vehicle-table th {
            padding: 14px 20px;

            text-align: left;

            background: #faf7fa;

            color: #796978;

            font-size: 11px;
            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: .4px;

            border-bottom: 1px solid #eee5ef;
        }

        .vehicle-table td {
            padding: 17px 20px;

            color: #493847;

            font-size: 13px;

            border-bottom: 1px solid #f0eaf1;
        }

        .vehicle-table tbody tr {
            transition: .15s ease;
        }

        .vehicle-table tbody tr:hover {
            background: #fcf9fc;
        }

        .vehicle-table tbody tr:last-child td {
            border-bottom: none;
        }


        /* =====================================================
           VEHICLE ID
        ===================================================== */

        .vehicle-number {
            width: 45px;

            color: #9b8998;

            font-weight: 700;
        }


        /* =====================================================
           PLAT
        ===================================================== */

        .vehicle-plate {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            font-weight: 800;

            color: #352137;
        }

        .vehicle-plate-icon {
            width: 33px;
            height: 33px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            color: #74366b;

            background: #f0e2ee;
        }

        .vehicle-plate-icon svg {
            width: 16px;
            height: 16px;
        }


        /* =====================================================
           TYPE BADGE
        ===================================================== */

        .vehicle-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 7px 10px;

            border-radius: 9px;

            font-size: 11px;
            font-weight: 800;

            background: #f4edf4;
            color: #6e526a;
        }

        .vehicle-type-badge svg {
            width: 14px;
            height: 14px;
        }


        /* =====================================================
           OWNER
        ===================================================== */

        .vehicle-owner {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .vehicle-owner-avatar {
            width: 32px;
            height: 32px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            color: #74366b;

            background:
                linear-gradient(
                    135deg,
                    #f2e2ef,
                    #ead4e7
                );
        }

        .vehicle-owner-avatar svg {
            width: 15px;
            height: 15px;
        }

        .vehicle-owner-name {
            font-weight: 700;
            color: #4a3546;
        }


        /* =====================================================
           ACTION
        ===================================================== */

        .vehicle-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .vehicle-action {
            width: 36px;
            height: 36px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            text-decoration: none;

            transition: .18s ease;
        }

        .vehicle-action svg {
            width: 16px;
            height: 16px;
        }

        .vehicle-action-edit {
            color: #725087;
            background: #f0e8f4;
        }

        .vehicle-action-edit:hover {
            color: white;
            background: #805493;

            transform: translateY(-2px);
        }

        .vehicle-action-delete {
            color: #b33e55;
            background: #fff0f2;
        }

        .vehicle-action-delete:hover {
            color: white;
            background: #c44a60;

            transform: translateY(-2px);
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .vehicle-empty {
            padding: 65px 20px;

            text-align: center;
        }

        .vehicle-empty-icon {
            width: 65px;
            height: 65px;

            margin: 0 auto 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 19px;

            color: #8b6485;

            background: #f4ebf3;
        }

        .vehicle-empty-icon svg {
            width: 29px;
            height: 29px;
        }

        .vehicle-empty h3 {
            margin: 0 0 7px;

            color: #443043;

            font-size: 17px;
        }

        .vehicle-empty p {
            margin: 0;

            color: #9a8998;

            font-size: 12px;
        }


        /* =====================================================
           SUCCESS
        ===================================================== */

        .vehicle-success {
            display: flex;
            align-items: center;
            gap: 11px;

            margin-bottom: 20px;

            padding: 14px 17px;

            border-radius: 14px;

            color: #26734d;

            background: #eefbf4;

            border: 1px solid #cceede;

            font-size: 13px;
            font-weight: 700;
        }

        .vehicle-success svg {
            width: 18px;
            height: 18px;
        }


        /* =====================================================
           DELETE ERROR
        ===================================================== */

        .vehicle-delete-error {
            display: flex;
            align-items: flex-start;
            gap: 11px;

            margin-bottom: 20px;

            padding: 14px 17px;

            border-radius: 14px;

            color: #a52f3c;

            background: #fff1f2;

            border: 1px solid #ffd6da;

            font-size: 13px;
            font-weight: 700;
        }

        .vehicle-delete-error svg {
            width: 18px;
            height: 18px;

            flex-shrink: 0;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 950px) {

            .vehicle-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .vehicle-table-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .vehicle-search {
                width: 100%;
            }

        }


        @media (max-width: 650px) {

            .vehicle-page {
                padding: 20px;
            }

            .vehicle-page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .vehicle-add-button {
                width: 100%;
                justify-content: center;
            }

            .vehicle-stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<?php include 'sidebar.php'; ?>

<?php include 'navbar.php'; ?>


<main class="main-content">

    <div class="vehicle-page">


        <!-- ==================================================
             HEADER
        ================================================== -->

        <div class="vehicle-page-header">

            <div class="vehicle-title-area">

                <div class="vehicle-title-icon">

                    <i data-lucide="car-front"></i>

                </div>

                <div>

                    <h1>Data Kendaraan</h1>

                    <p>
                        Kelola seluruh data kendaraan yang terdaftar.
                    </p>

                </div>

            </div>


            <a
                href="kendaraan-tambah.php"
                class="vehicle-add-button"
            >

                <i data-lucide="plus"></i>

                Tambah Kendaraan

            </a>

        </div>


        <!-- ==================================================
             SUCCESS MESSAGE
        ================================================== -->

        <?php if ($status === 'tambah'): ?>

            <div class="vehicle-success">

                <i data-lucide="circle-check"></i>

                Data kendaraan berhasil ditambahkan.

            </div>

        <?php elseif ($status === 'edit'): ?>

            <div class="vehicle-success">

                <i data-lucide="circle-check"></i>

                Data kendaraan berhasil diperbarui.

            </div>

        <?php elseif ($status === 'hapus'): ?>

            <div class="vehicle-success">

                <i data-lucide="trash-2"></i>

                Data kendaraan berhasil dihapus.

            </div>

        <?php elseif ($status === 'gagal-hapus'): ?>

            <div class="vehicle-delete-error">

                <i data-lucide="triangle-alert"></i>

                <span>
                    Kendaraan tidak dapat dihapus karena masih
                    digunakan dalam data transaksi parkir.
                </span>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             STATISTICS
        ================================================== -->

        <div class="vehicle-stats">


            <div class="vehicle-stat-card">

                <div class="vehicle-stat-top">

                    <div class="vehicle-stat-icon">

                        <i data-lucide="car-front"></i>

                    </div>

                    <span class="vehicle-stat-label">
                        Total Kendaraan
                    </span>

                </div>

                <div class="vehicle-stat-value">
                    <?= number_format($totalKendaraan) ?>
                </div>

            </div>


            <div class="vehicle-stat-card">

                <div class="vehicle-stat-top">

                    <div class="vehicle-stat-icon">

                        <i data-lucide="bike"></i>

                    </div>

                    <span class="vehicle-stat-label">
                        Motor
                    </span>

                </div>

                <div class="vehicle-stat-value">
                    <?= number_format($totalMotor) ?>
                </div>

            </div>


            <div class="vehicle-stat-card">

                <div class="vehicle-stat-top">

                    <div class="vehicle-stat-icon">

                        <i data-lucide="car"></i>

                    </div>

                    <span class="vehicle-stat-label">
                        Mobil
                    </span>

                </div>

                <div class="vehicle-stat-value">
                    <?= number_format($totalMobil) ?>
                </div>

            </div>


            <div class="vehicle-stat-card">

                <div class="vehicle-stat-top">

                    <div class="vehicle-stat-icon">

                        <i data-lucide="ellipsis"></i>

                    </div>

                    <span class="vehicle-stat-label">
                        Lainnya
                    </span>

                </div>

                <div class="vehicle-stat-value">
                    <?= number_format($totalLainnya) ?>
                </div>

            </div>


        </div>


        <!-- ==================================================
             TABLE
        ================================================== -->

        <div class="vehicle-table-card">


            <div class="vehicle-table-header">

                <div class="vehicle-table-title">

                    <div class="vehicle-table-title-icon">

                        <i data-lucide="list"></i>

                    </div>

                    <div>

                        <h2>
                            Daftar Kendaraan
                        </h2>

                        <span>
                            <?= number_format($totalKendaraan) ?>
                            kendaraan terdaftar
                        </span>

                    </div>

                </div>


                <div class="vehicle-search">

                    <i data-lucide="search"></i>

                    <input
                        type="text"
                        id="vehicleSearch"
                        placeholder="Cari plat, jenis, warna, pemilik..."
                    >

                </div>

            </div>


            <div class="vehicle-table-wrapper">

                <table
                    class="vehicle-table"
                    id="vehicleTable"
                >

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Kendaraan</th>

                            <th>Jenis</th>

                            <th>Warna</th>

                            <th>Pemilik</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (count($kendaraan) > 0): ?>

                        <?php foreach ($kendaraan as $index => $item): ?>

                            <tr>


                                <!-- NO -->

                                <td class="vehicle-number">

                                    <?= $index + 1 ?>

                                </td>


                                <!-- KENDARAAN -->

                                <td>

                                    <div class="vehicle-plate">

                                        <div class="vehicle-plate-icon">

                                            <i data-lucide="car-front"></i>

                                        </div>

                                        <?= htmlspecialchars($item['plat_nomor']) ?>

                                    </div>

                                </td>


                                <!-- JENIS -->

                                <td>

                                    <?php

                                    $jenisIcon = 'car';

                                    $jenisLabel = ucfirst(
                                        $item['jenis_kendaraan']
                                    );

                                    if ($item['jenis_kendaraan'] === 'motor') {
                                        $jenisIcon = 'bike';
                                    }

                                    ?>

                                    <span class="vehicle-type-badge">

                                        <i data-lucide="<?= $jenisIcon ?>"></i>

                                        <?= htmlspecialchars($jenisLabel) ?>

                                    </span>

                                </td>


                                <!-- WARNA -->

                                <td>

                                    <?= htmlspecialchars($item['warna']) ?>

                                </td>


                                <!-- PEMILIK -->

                                <td>

                                    <div class="vehicle-owner">

                                        <div class="vehicle-owner-avatar">

                                            <i data-lucide="user-round"></i>

                                        </div>

                                        <span class="vehicle-owner-name">

                                            <?= htmlspecialchars($item['pemilik']) ?>

                                        </span>

                                    </div>

                                </td>


                                <!-- AKSI -->

                                <td>

                                    <div class="vehicle-actions">


                                        <!-- EDIT -->

                                        <a
                                            href="kendaraan-edit.php?id=<?= (int) $item['id_kendaraan'] ?>"
                                            class="vehicle-action vehicle-action-edit"
                                            title="Edit kendaraan"
                                        >

                                            <i data-lucide="pencil"></i>

                                        </a>


                                        <!-- DELETE -->

                                        <a
                                            href="kendaraan-hapus.php?id=<?= (int) $item['id_kendaraan'] ?>"
                                            class="vehicle-action vehicle-action-delete"
                                            title="Hapus kendaraan"
                                        >

                                            <i data-lucide="trash-2"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                style="padding:0;"
                            >

                                <div class="vehicle-empty">

                                    <div class="vehicle-empty-icon">

                                        <i data-lucide="car-front"></i>

                                    </div>

                                    <h3>
                                        Belum Ada Data Kendaraan
                                    </h3>

                                    <p>
                                        Silakan tambahkan kendaraan baru
                                        menggunakan tombol di atas.
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


<script>

    lucide.createIcons();


    // ======================================================
    // SEARCH KENDARAAN
    // ======================================================

    const searchInput = document.getElementById('vehicleSearch');
    const tableRows = document.querySelectorAll('#vehicleTable tbody tr');

    if (searchInput) {

        searchInput.addEventListener('input', function () {

            const keyword = this.value.toLowerCase().trim();

            tableRows.forEach(function (row) {

                const text = row.textContent.toLowerCase();

                row.style.display =
                    text.includes(keyword)
                        ? ''
                        : 'none';

            });

        });

    }

</script>


</body>

</html>