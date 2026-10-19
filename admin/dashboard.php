<?php

require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Dashboard';
$pageDescription = 'Ringkasan sistem parkir';

$namaUser = getNamaUser();


// ======================================================
// DEFAULT DATA
// ======================================================

$jumlahKendaraan = 0;
$jumlahArea = 0;
$jumlahTransaksi = 0;
$jumlahUser = 0;

$kendaraanParkir = 0;
$kendaraanKeluar = 0;
$totalPendapatan = 0;

$motor = 0;
$mobil = 0;
$lainnya = 0;

$areaParkir = [];
$transaksiTerbaru = [];

$grafikTanggal = [];
$grafikMasuk = [];
$grafikKeluar = [];


// ======================================================
// DATABASE
// ======================================================

try {

    $pdo = getDatabaseConnection('admin');


    // ==================================================
    // STATISTIK
    // ==================================================

    $jumlahKendaraan = (int) $pdo->query("
        SELECT COUNT(*)
        FROM tb_kendaraan
    ")->fetchColumn();


    $jumlahArea = (int) $pdo->query("
        SELECT COUNT(*)
        FROM tb_area_parkir
    ")->fetchColumn();


    $jumlahTransaksi = (int) $pdo->query("
        SELECT COUNT(*)
        FROM tb_transaksi
    ")->fetchColumn();


    $jumlahUser = (int) $pdo->query("
        SELECT COUNT(*)
        FROM tb_user
    ")->fetchColumn();


    // ==================================================
    // KENDARAAN SEDANG PARKIR
    // ==================================================

    $kendaraanParkir = (int) $pdo->query("
        SELECT COUNT(*)
        FROM tb_transaksi
        WHERE status = 'masuk'
    ")->fetchColumn();


    // ==================================================
    // KENDARAAN KELUAR
    // ==================================================

    $kendaraanKeluar = (int) $pdo->query("
        SELECT COUNT(*)
        FROM tb_transaksi
        WHERE status = 'keluar'
    ")->fetchColumn();


    // ==================================================
    // PENDAPATAN
    // ==================================================

    $totalPendapatan = (float) $pdo->query("
        SELECT COALESCE(SUM(biaya_total), 0)
        FROM tb_transaksi
        WHERE status = 'keluar'
    ")->fetchColumn();


    // ==================================================
    // JENIS KENDARAAN
    // ==================================================

    $stmtJenis = $pdo->query("
        SELECT
            jenis_kendaraan,
            COUNT(*) AS jumlah
        FROM tb_kendaraan
        GROUP BY jenis_kendaraan
    ");

    $dataJenisKendaraan =
        $stmtJenis->fetchAll(PDO::FETCH_ASSOC);


    foreach ($dataJenisKendaraan as $jenis) {

        $namaJenis = strtolower(
            trim($jenis['jenis_kendaraan'])
        );

        $jumlahJenis = (int) $jenis['jumlah'];

        if ($namaJenis === 'motor') {

            $motor = $jumlahJenis;

        } elseif ($namaJenis === 'mobil') {

            $mobil = $jumlahJenis;

        } else {

            $lainnya += $jumlahJenis;
        }
    }


    // ==================================================
    // AREA PARKIR
    // ==================================================

    $stmtArea = $pdo->query("
        SELECT
            id_area,
            nama_area,
            kapasitas,
            terisi
        FROM tb_area_parkir
        ORDER BY id_area ASC
    ");

    $areaParkir =
        $stmtArea->fetchAll(PDO::FETCH_ASSOC);


    // ==================================================
    // TRANSAKSI TERBARU
    // ==================================================

    $stmtTransaksi = $pdo->query("
        SELECT
            t.id_parkir,
            k.plat_nomor,
            k.jenis_kendaraan,
            t.waktu_masuk,
            t.waktu_keluar,
            t.durasi_jam,
            t.biaya_total,
            t.status
        FROM tb_transaksi t
        LEFT JOIN tb_kendaraan k
            ON t.id_kendaraan = k.id_kendaraan
        ORDER BY t.id_parkir DESC
        LIMIT 5
    ");

    $transaksiTerbaru =
        $stmtTransaksi->fetchAll(PDO::FETCH_ASSOC);


    // ==================================================
    // GRAFIK 7 HARI
    // ==================================================

    for ($i = 6; $i >= 0; $i--) {

        $tanggal = date(
            'Y-m-d',
            strtotime("-{$i} days")
        );

        $grafikTanggal[] = date(
            'd M',
            strtotime($tanggal)
        );


        // MASUK

        $stmtMasuk = $pdo->prepare("
            SELECT COUNT(*)
            FROM tb_transaksi
            WHERE DATE(waktu_masuk) = ?
        ");

        $stmtMasuk->execute([
            $tanggal
        ]);

        $grafikMasuk[] =
            (int) $stmtMasuk->fetchColumn();


        // KELUAR

        $stmtKeluar = $pdo->prepare("
            SELECT COUNT(*)
            FROM tb_transaksi
            WHERE waktu_keluar IS NOT NULL
            AND DATE(waktu_keluar) = ?
        ");

        $stmtKeluar->execute([
            $tanggal
        ]);

        $grafikKeluar[] =
            (int) $stmtKeluar->fetchColumn();
    }


} catch (Exception $e) {

    $jumlahKendaraan = 0;
    $jumlahArea = 0;
    $jumlahTransaksi = 0;
    $jumlahUser = 0;

    $kendaraanParkir = 0;
    $kendaraanKeluar = 0;
    $totalPendapatan = 0;

    $motor = 0;
    $mobil = 0;
    $lainnya = 0;

    $areaParkir = [];
    $transaksiTerbaru = [];

    for ($i = 6; $i >= 0; $i--) {

        $tanggal = date(
            'Y-m-d',
            strtotime("-{$i} days")
        );

        $grafikTanggal[] =
            date('d M', strtotime($tanggal));

        $grafikMasuk[] = 0;
        $grafikKeluar[] = 0;
    }
}


// ======================================================
// JSON
// ======================================================

$grafikTanggalJson = json_encode(
    $grafikTanggal,
    JSON_UNESCAPED_UNICODE
);

$grafikMasukJson = json_encode(
    $grafikMasuk
);

$grafikKeluarJson = json_encode(
    $grafikKeluar
);

$jenisKendaraanJson = json_encode([
    $motor,
    $mobil,
    $lainnya
]);

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
        Dashboard Admin - Sistem Parkir
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <script src="https://unpkg.com/lucide@latest"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<body>

<div class="admin-layout">


    <!-- SIDEBAR -->

    <?php
    require_once __DIR__ . '/sidebar.php';
    ?>


    <!-- MAIN -->

    <div class="main-content">


        <!-- NAVBAR -->

        <?php
        require_once __DIR__ . '/navbar.php';
        ?>


        <main class="content dashboard-page">


            <!-- ==================================================
                 WELCOME
            ================================================== -->

            <section class="welcome-card">

                <div class="welcome-content">

                    <span class="welcome-label">
                        DASHBOARD ADMIN
                    </span>

                    <h2>
                        Selamat datang,
                        <?= htmlspecialchars($namaUser) ?>!
                    </h2>

                    <p>
                        Pantau dan kelola aktivitas
                        sistem parkir melalui dashboard admin.
                    </p>

                </div>


                <div class="welcome-visual">

                    <i
                        data-lucide="parking-square"
                    ></i>

                </div>

            </section>



            <!-- ==================================================
                 STATISTIK UTAMA
            ================================================== -->

            <section class="dashboard-stats">


                <div class="dashboard-stat-card">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="car-front"></i>
                    </div>

                    <div class="dashboard-stat-number">
                        <?= number_format($jumlahKendaraan) ?>
                    </div>

                    <div class="dashboard-stat-label">
                        Total Kendaraan
                    </div>

                </div>


                <div class="dashboard-stat-card">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="square-parking"></i>
                    </div>

                    <div class="dashboard-stat-number">
                        <?= number_format($jumlahArea) ?>
                    </div>

                    <div class="dashboard-stat-label">
                        Area Parkir
                    </div>

                </div>


                <div class="dashboard-stat-card">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="arrow-left-right"></i>
                    </div>

                    <div class="dashboard-stat-number">
                        <?= number_format($jumlahTransaksi) ?>
                    </div>

                    <div class="dashboard-stat-label">
                        Total Transaksi
                    </div>

                </div>


                <div class="dashboard-stat-card">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="users"></i>
                    </div>

                    <div class="dashboard-stat-number">
                        <?= number_format($jumlahUser) ?>
                    </div>

                    <div class="dashboard-stat-label">
                        Total User
                    </div>

                </div>


            </section>



            <!-- ==================================================
                 STATISTIK KEDUA
            ================================================== -->

            <section class="dashboard-stats dashboard-stats-second">


                <div class="dashboard-stat-card">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="car"></i>
                    </div>

                    <div class="dashboard-stat-number">
                        <?= number_format($kendaraanParkir) ?>
                    </div>

                    <div class="dashboard-stat-label">
                        Sedang Parkir
                    </div>

                </div>


                <div class="dashboard-stat-card">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="log-out"></i>
                    </div>

                    <div class="dashboard-stat-number">
                        <?= number_format($kendaraanKeluar) ?>
                    </div>

                    <div class="dashboard-stat-label">
                        Kendaraan Keluar
                    </div>

                </div>


                <div class="dashboard-stat-card dashboard-income">

                    <div class="dashboard-stat-icon">
                        <i data-lucide="wallet"></i>
                    </div>

                    <div class="dashboard-stat-number income-number">

                        Rp <?= number_format(
                            $totalPendapatan,
                            0,
                            ',',
                            '.'
                        ) ?>

                    </div>

                    <div class="dashboard-stat-label">
                        Total Pendapatan
                    </div>

                </div>


            </section>



            <!-- ==================================================
                 GRAFIK
            ================================================== -->

            <section class="dashboard-chart-grid">


                <!-- AKTIVITAS -->

                <div class="dashboard-panel activity-panel">

                    <div class="dashboard-panel-header">

                        <div>

                            <h3>
                                Aktivitas Parkir
                            </h3>

                            <p>
                                Aktivitas kendaraan
                                7 hari terakhir
                            </p>

                        </div>

                        <i
                            data-lucide="chart-no-axes-combined"
                            class="panel-header-icon"
                        ></i>

                    </div>


                    <div class="activity-chart">

                        <canvas
                            id="grafikAktivitas"
                        ></canvas>

                    </div>

                </div>



                <!-- JENIS KENDARAAN -->

                <div class="dashboard-panel vehicle-panel">

                    <div class="dashboard-panel-header">

                        <div>

                            <h3>
                                Jenis Kendaraan
                            </h3>

                            <p>
                                Komposisi kendaraan
                            </p>

                        </div>

                        <i
                            data-lucide="pie-chart"
                            class="panel-header-icon"
                        ></i>

                    </div>


                    <div class="vehicle-chart">

                        <canvas
                            id="grafikKendaraan"
                        ></canvas>

                    </div>


                    <div class="vehicle-summary">


                        <div class="vehicle-summary-item">

                            <span
                                class="vehicle-dot dot-motor"
                            ></span>

                            <div>

                                <strong>
                                    <?= number_format($motor) ?>
                                </strong>

                                <small>
                                    Motor
                                </small>

                            </div>

                        </div>


                        <div class="vehicle-summary-item">

                            <span
                                class="vehicle-dot dot-mobil"
                            ></span>

                            <div>

                                <strong>
                                    <?= number_format($mobil) ?>
                                </strong>

                                <small>
                                    Mobil
                                </small>

                            </div>

                        </div>


                        <div class="vehicle-summary-item">

                            <span
                                class="vehicle-dot dot-lainnya"
                            ></span>

                            <div>

                                <strong>
                                    <?= number_format($lainnya) ?>
                                </strong>

                                <small>
                                    Lainnya
                                </small>

                            </div>

                        </div>


                    </div>

                </div>


            </section>



            <!-- ==================================================
                 AKSES CEPAT + AREA
            ================================================== -->

            <section class="dashboard-bottom-grid">


                <!-- AKSES CEPAT -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-header">

                        <div class="panel-title-with-icon">

                            <i data-lucide="zap"></i>

                            <h3>
                                Akses Cepat
                            </h3>

                        </div>

                    </div>


                    <div class="quick-access-grid">


                        <a
                            href="kendaraan.php"
                            class="quick-access-card"
                        >

                            <div class="quick-access-icon">

                                <i
                                    data-lucide="car-front"
                                ></i>

                            </div>

                            <div class="quick-access-text">

                                <strong>
                                    Data Kendaraan
                                </strong>

                                <span>
                                    Kelola kendaraan
                                </span>

                            </div>

                            <i
                                data-lucide="arrow-right"
                                class="quick-arrow"
                            ></i>

                        </a>


                        <a
                            href="tarif.php"
                            class="quick-access-card"
                        >

                            <div class="quick-access-icon">

                                <i
                                    data-lucide="badge-dollar-sign"
                                ></i>

                            </div>

                            <div class="quick-access-text">

                                <strong>
                                    Tarif Parkir
                                </strong>

                                <span>
                                    Kelola tarif
                                </span>

                            </div>

                            <i
                                data-lucide="arrow-right"
                                class="quick-arrow"
                            ></i>

                        </a>


                        <a
                            href="area.php"
                            class="quick-access-card"
                        >

                            <div class="quick-access-icon">

                                <i
                                    data-lucide="square-parking"
                                ></i>

                            </div>

                            <div class="quick-access-text">

                                <strong>
                                    Area Parkir
                                </strong>

                                <span>
                                    Kelola area
                                </span>

                            </div>

                            <i
                                data-lucide="arrow-right"
                                class="quick-arrow"
                            ></i>

                        </a>


                        <a
                            href="transaksi.php"
                            class="quick-access-card"
                        >

                            <div class="quick-access-icon">

                                <i
                                    data-lucide="receipt-text"
                                ></i>

                            </div>

                            <div class="quick-access-text">

                                <strong>
                                    Transaksi
                                </strong>

                                <span>
                                    Kelola transaksi
                                </span>

                            </div>

                            <i
                                data-lucide="arrow-right"
                                class="quick-arrow"
                            ></i>

                        </a>


                    </div>

                </div>



                <!-- KAPASITAS -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-header">

                        <div>

                            <h3>
                                Kapasitas Area Parkir
                            </h3>

                        </div>

                        <a
                            href="area.php"
                            class="panel-link"
                        >
                            Lihat semua
                            <i data-lucide="arrow-right"></i>
                        </a>

                    </div>


                    <div class="capacity-list">


                        <?php if (!empty($areaParkir)): ?>


                            <?php foreach ($areaParkir as $index => $area): ?>

                                <?php

                                $kapasitas =
                                    (int) $area['kapasitas'];

                                $terisi =
                                    (int) $area['terisi'];

                                $persentase =
                                    $kapasitas > 0
                                        ? (
                                            $terisi
                                            / $kapasitas
                                        ) * 100
                                        : 0;

                                $persentase =
                                    min(
                                        100,
                                        $persentase
                                    );

                                $warnaClass =
                                    'capacity-' .
                                    (($index % 4) + 1);

                                ?>


                                <div class="capacity-item">

                                    <div class="capacity-info">

                                        <span>
                                            <?= htmlspecialchars(
                                                $area['nama_area']
                                            ) ?>
                                        </span>

                                        <strong>
                                            <?= $terisi ?>
                                            /
                                            <?= $kapasitas ?>
                                        </strong>

                                    </div>


                                    <div class="capacity-progress">

                                        <div
                                            class="
                                                capacity-progress-bar
                                                <?= $warnaClass ?>
                                            "
                                            style="
                                                width:
                                                <?= $persentase ?>%;
                                            "
                                        ></div>

                                    </div>


                                    <div class="capacity-percent">

                                        <?= number_format(
                                            $persentase,
                                            0
                                        ) ?>%

                                    </div>

                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>

                            <div class="empty-dashboard">

                                <i
                                    data-lucide="parking-square-off"
                                ></i>

                                <span>
                                    Belum ada data area parkir.
                                </span>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>


            </section>



            <!-- ==================================================
                 TRANSAKSI TERBARU
            ================================================== -->

            <section class="dashboard-panel recent-transactions">

                <div class="dashboard-panel-header">

                    <div>

                        <h3>
                            Transaksi Terbaru
                        </h3>

                    </div>

                    <a
                        href="transaksi.php"
                        class="panel-link"
                    >
                        Lihat semua
                        <i data-lucide="arrow-right"></i>
                    </a>

                </div>


                <div class="transaction-table-wrapper">

                    <table class="transaction-table">

                        <thead>

                            <tr>

                                <th>
                                    No. Transaksi
                                </th>

                                <th>
                                    Kendaraan
                                </th>

                                <th>
                                    Jenis
                                </th>

                                <th>
                                    Waktu Masuk
                                </th>

                                <th>
                                    Durasi
                                </th>

                                <th>
                                    Biaya
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (!empty($transaksiTerbaru)): ?>


                                <?php foreach (
                                    $transaksiTerbaru
                                    as $transaksi
                                ): ?>


                                    <tr>

                                        <td>

                                            <span
                                                class="transaction-id"
                                            >

                                                #<?= htmlspecialchars(
                                                    $transaksi[
                                                        'id_parkir'
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $transaksi[
                                                        'plat_nomor'
                                                    ] ?? '-'
                                                ) ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                ucfirst(
                                                    $transaksi[
                                                        'jenis_kendaraan'
                                                    ] ?? '-'
                                                )
                                            ) ?>

                                        </td>


                                        <td>

                                            <?php

                                            if (
                                                !empty(
                                                    $transaksi[
                                                        'waktu_masuk'
                                                    ]
                                                )
                                            ) {

                                                echo date(
                                                    'd M Y, H:i',
                                                    strtotime(
                                                        $transaksi[
                                                            'waktu_masuk'
                                                        ]
                                                    )
                                                );

                                            } else {

                                                echo '-';

                                            }

                                            ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $transaksi[
                                                    'durasi_jam'
                                                ] ?? '-'
                                            ) ?>

                                        </td>


                                        <td>

                                            Rp
                                            <?= number_format(
                                                (float) (
                                                    $transaksi[
                                                        'biaya_total'
                                                    ] ?? 0
                                                ),
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                $transaksi['status']
                                                === 'masuk'
                                            ): ?>

                                                <span
                                                    class="
                                                        transaction-status
                                                        status-masuk
                                                    "
                                                >
                                                    Masuk
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="
                                                        transaction-status
                                                        status-keluar
                                                    "
                                                >
                                                    Keluar
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>


                                <?php endforeach; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="7"
                                        class="empty-table"
                                    >

                                        <i
                                            data-lucide="receipt"
                                        ></i>

                                        Belum ada transaksi.

                                    </td>

                                </tr>

                            <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </section>



            <!-- FOOTER -->

            <div class="dashboard-footer">

                <span>
                    © <?= date('Y') ?>
                    Sistem Parkir.
                    All rights reserved.
                </span>

                <span>
                    v1.0.0
                </span>

            </div>


        </main>

    </div>

</div>


<script>

const grafikTanggal =
    <?= $grafikTanggalJson ?>;

const grafikMasuk =
    <?= $grafikMasukJson ?>;

const grafikKeluar =
    <?= $grafikKeluarJson ?>;

const jenisKendaraan =
    <?= $jenisKendaraanJson ?>;


// ======================================================
// GRAFIK AKTIVITAS
// ======================================================

const ctxAktivitas =
    document.getElementById(
        'grafikAktivitas'
    );

if (ctxAktivitas) {

    new Chart(
        ctxAktivitas,
        {

            type: 'line',

            data: {

                labels: grafikTanggal,

                datasets: [

                    {
                        label: 'Kendaraan Masuk',

                        data: grafikMasuk,

                        borderWidth: 2.5,

                        tension: 0.4,

                        fill: true,

                        pointRadius: 3,

                        pointHoverRadius: 6
                    },

                    {
                        label: 'Kendaraan Keluar',

                        data: grafikKeluar,

                        borderWidth: 2.5,

                        tension: 0.4,

                        fill: true,

                        pointRadius: 3,

                        pointHoverRadius: 6
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                interaction: {
                    intersect: false,
                    mode: 'index'
                },

                plugins: {

                    legend: {

                        position: 'top',

                        align: 'center',

                        labels: {

                            usePointStyle: true,

                            boxWidth: 8,

                            padding: 18

                        }

                    }

                },

                scales: {

                    x: {

                        grid: {
                            display: false
                        }

                    },

                    y: {

                        beginAtZero: true,

                        ticks: {
                            precision: 0
                        }

                    }

                }

            }

        }
    );

}


// ======================================================
// DONUT JENIS KENDARAAN
// ======================================================

const ctxKendaraan =
    document.getElementById(
        'grafikKendaraan'
    );

if (ctxKendaraan) {

    new Chart(
        ctxKendaraan,
        {

            type: 'doughnut',

            data: {

                labels: [
                    'Motor',
                    'Mobil',
                    'Lainnya'
                ],

                datasets: [

                    {
                        data:
                            jenisKendaraan,

                        borderWidth: 3,

                        hoverOffset: 7
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '70%',

                plugins: {

                    legend: {
                        display: false
                    }

                }

            }

        }
    );

}


// ======================================================
// LUCIDE
// ======================================================

if (typeof lucide !== 'undefined') {

    lucide.createIcons();

}

</script>


<script
    src="../assets/js/admin.js"
></script>

</body>

</html>