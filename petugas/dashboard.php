<?php

require_once __DIR__ . '/../config/auth.php';

requireRole('petugas');

require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection('petugas');


// =========================================================
// DATA DASHBOARD
// =========================================================

// Total kendaraan
$totalKendaraan = 0;

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM tb_kendaraan
    ");

    $totalKendaraan = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $totalKendaraan = 0;
}


// Kendaraan sedang parkir
$kendaraanMasuk = 0;

try {
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM tb_transaksi
        WHERE status = 'masuk'
    ");

    $kendaraanMasuk = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $kendaraanMasuk = 0;
}


// Kendaraan sudah keluar
$kendaraanKeluar = 0;

try {
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM tb_transaksi
        WHERE status = 'keluar'
    ");

    $kendaraanKeluar = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $kendaraanKeluar = 0;
}


// Total area parkir
$totalArea = 0;

try {
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM tb_area_parkir
    ");

    $totalArea = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $totalArea = 0;
}


// =========================================================
// DATA GRAFIK JENIS KENDARAAN
// =========================================================

$motor = 0;
$mobil = 0;
$lainnya = 0;

try {

    $stmt = $pdo->query("
        SELECT 
            jenis_kendaraan,
            COUNT(*) AS total
        FROM tb_kendaraan
        GROUP BY jenis_kendaraan
    ");

    while ($row = $stmt->fetch()) {

        if ($row['jenis_kendaraan'] === 'motor') {
            $motor = (int) $row['total'];
        }

        if ($row['jenis_kendaraan'] === 'mobil') {
            $mobil = (int) $row['total'];
        }

        if ($row['jenis_kendaraan'] === 'lainnya') {
            $lainnya = (int) $row['total'];
        }
    }

} catch (Exception $e) {

    $motor = 0;
    $mobil = 0;
    $lainnya = 0;
}


// =========================================================
// DATA GRAFIK TRANSAKSI 7 HARI TERAKHIR
// =========================================================

$labelHari = [];
$dataMasuk = [];
$dataKeluar = [];

for ($i = 6; $i >= 0; $i--) {

    $tanggal = date(
        'Y-m-d',
        strtotime("-$i days")
    );

    $labelHari[] = date(
        'd M',
        strtotime($tanggal)
    );

    $masuk = 0;
    $keluar = 0;


    // kendaraan masuk
    try {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM tb_transaksi
            WHERE DATE(waktu_masuk) = ?
        ");

        $stmt->execute([$tanggal]);

        $masuk = (int) $stmt->fetchColumn();

    } catch (Exception $e) {

        $masuk = 0;
    }


    // kendaraan keluar
    try {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM tb_transaksi
            WHERE status = 'keluar'
            AND DATE(waktu_keluar) = ?
        ");

        $stmt->execute([$tanggal]);

        $keluar = (int) $stmt->fetchColumn();

    } catch (Exception $e) {

        $keluar = 0;
    }


    $dataMasuk[] = $masuk;
    $dataKeluar[] = $keluar;
}


// =========================================================
// DATA AREA PARKIR
// =========================================================

$areaParkir = [];

try {

    $stmt = $pdo->query("
        SELECT
            nama_area,
            kapasitas,
            terisi
        FROM tb_area_parkir
        ORDER BY id_area ASC
    ");

    $areaParkir = $stmt->fetchAll();

} catch (Exception $e) {

    $areaParkir = [];
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

    <title>Dashboard Petugas - Sistem Parkir</title>


    <!-- CHART.JS -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }


        body {

            min-height: 100vh;

            background:
                radial-gradient(
                    circle at 85% 0%,
                    rgba(133, 99, 199, 0.10),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 5% 90%,
                    rgba(96, 64, 154, 0.06),
                    transparent 25%
                ),
                #f7f6fb;

            color: #332e3b;
        }


        /* =====================================================
           LAYOUT
        ===================================================== */

        .page {

            width: 100%;

            min-height: 100vh;

            padding: 35px 42px 50px;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 28px;
        }


        .header-left h1 {

            font-size: 27px;

            color: #33283f;

            margin-bottom: 7px;
        }


        .header-left p {

            color: #928a9d;

            font-size: 14px;
        }


        .user-box {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 10px 15px;

            border-radius: 13px;

            background: rgba(255, 255, 255, 0.9);

            border: 1px solid rgba(100, 70, 150, 0.10);

            box-shadow:
                0 6px 20px rgba(70, 45, 110, 0.06);
        }


        .avatar {

            width: 43px;

            height: 43px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    145deg,
                    #8867c8,
                    #60409a
                );

            color: white;

            font-weight: bold;

            font-size: 14px;
        }


        .user-name {

            font-size: 13px;

            font-weight: bold;

            color: #393340;
        }


        .user-role {

            margin-top: 4px;

            font-size: 11px;

            color: #9992a3;
        }


        /* =====================================================
           WELCOME
        ===================================================== */

        .welcome {

            position: relative;

            overflow: hidden;

            padding: 28px 31px;

            margin-bottom: 25px;

            border-radius: 17px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #56368d,
                    #704caf 50%,
                    #8964c8
                );

            box-shadow:
                0 16px 38px rgba(86, 55, 141, 0.20);
        }


        .welcome::after {

            content: "";

            position: absolute;

            width: 350px;

            height: 350px;

            right: -150px;

            top: -250px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(255,255,255,0.16),
                    transparent 65%
                );
        }


        .welcome h2 {

            position: relative;

            z-index: 2;

            font-size: 23px;

            margin-bottom: 8px;
        }


        .welcome p {

            position: relative;

            z-index: 2;

            color: rgba(255,255,255,0.80);

            font-size: 13px;
        }


        /* =====================================================
           STATISTICS
        ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }


        .stat-card {

            position: relative;

            overflow: hidden;

            padding: 22px;

            min-height: 120px;

            display: flex;

            align-items: center;

            gap: 17px;

            border-radius: 14px;

            background:
                linear-gradient(
                    145deg,
                    #ffffff,
                    #fcfbfe
                );

            border:
                1px solid rgba(110,80,160,0.11);

            box-shadow:
                0 6px 20px rgba(68,42,110,0.06);

            transition: 0.25s ease;
        }


        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 15px 35px rgba(68,42,110,0.12);
        }


        .stat-icon {

            width: 55px;

            height: 55px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            background:
                linear-gradient(
                    145deg,
                    #f5f0fc,
                    #e7daf6
                );

            color: #6545a4;

            font-size: 20px;
        }


        .stat-number {

            font-size: 26px;

            font-weight: bold;

            color: #2e2935;

            margin-bottom: 6px;
        }


        .stat-label {

            color: #9992a2;

            font-size: 12px;
        }


        /* =====================================================
           CHART GRID
        ===================================================== */

        .chart-grid {

            display: grid;

            grid-template-columns:
                1.6fr 1fr;

            gap: 20px;

            margin-bottom: 20px;
        }


        .chart-card {

            padding: 24px;

            min-height: 370px;

            border-radius: 15px;

            background:
                linear-gradient(
                    145deg,
                    #ffffff,
                    #fcfbfe
                );

            border:
                1px solid rgba(110,80,160,0.11);

            box-shadow:
                0 6px 20px rgba(68,42,110,0.06);
        }


        .chart-title {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 22px;
        }


        .chart-title h3 {

            font-size: 16px;

            color: #332e3b;
        }


        .chart-title span {

            padding: 6px 10px;

            border-radius: 7px;

            background: #f1eafa;

            color: #6a4aa3;

            font-size: 10px;

            font-weight: bold;
        }


        .chart-container {

            position: relative;

            width: 100%;

            height: 275px;
        }


        /* =====================================================
           AREA PARKIR
        ===================================================== */

        .area-card {

            padding: 24px;

            border-radius: 15px;

            background:
                linear-gradient(
                    145deg,
                    #ffffff,
                    #fcfbfe
                );

            border:
                1px solid rgba(110,80,160,0.11);

            box-shadow:
                0 6px 20px rgba(68,42,110,0.06);
        }


        .area-card h3 {

            font-size: 16px;

            margin-bottom: 23px;

            color: #332e3b;
        }


        .area-item {

            margin-bottom: 23px;
        }


        .area-item:last-child {

            margin-bottom: 0;
        }


        .area-header {

            display: flex;

            justify-content: space-between;

            margin-bottom: 9px;
        }


        .area-name {

            font-size: 13px;

            font-weight: bold;

            color: #443d4c;
        }


        .area-number {

            font-size: 11px;

            color: #9992a2;
        }


        .progress {

            width: 100%;

            height: 9px;

            border-radius: 20px;

            overflow: hidden;

            background: #ebe7f0;
        }


        .progress-bar {

            height: 100%;

            border-radius: 20px;

            background:
                linear-gradient(
                    90deg,
                    #6545a4,
                    #8865c8
                );
        }


        /* =====================================================
           QUICK MENU
        ===================================================== */

        .quick-menu {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-top: 20px;
        }


        .quick-item {

            padding: 20px;

            border-radius: 13px;

            background:
                linear-gradient(
                    145deg,
                    #ffffff,
                    #faf8fd
                );

            border:
                1px solid rgba(110,80,160,0.10);

            box-shadow:
                0 5px 16px rgba(68,42,110,0.05);

            transition: 0.25s ease;
        }


        .quick-item:hover {

            transform: translateY(-4px);

            box-shadow:
                0 12px 28px rgba(68,42,110,0.10);
        }


        .quick-icon {

            width: 43px;

            height: 43px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 11px;

            margin-bottom: 13px;

            background: #f0eafa;

            color: #6747a5;

            font-weight: bold;
        }


        .quick-item h4 {

            font-size: 13px;

            color: #3c3644;

            margin-bottom: 5px;
        }


        .quick-item p {

            font-size: 11px;

            color: #9992a2;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .chart-grid {

                grid-template-columns: 1fr;
            }

            .quick-menu {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 700px) {

            .page {

                padding:
                    25px 18px 40px;
            }

            .header {

                align-items: flex-start;

                gap: 15px;
            }

            .user-box {

                display: none;
            }

            .stats {

                grid-template-columns: 1fr;
            }

            .quick-menu {

                grid-template-columns: 1fr;
            }

            .chart-card {

                padding: 18px;
            }
        }

    </style>

</head>


<body>


<div class="page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="header">

        <div class="header-left">

            <h1>Dashboard Petugas</h1>

            <p>
                Pantau aktivitas parkir dan transaksi kendaraan.
            </p>

        </div>


        <div class="user-box">

            <div class="avatar">
                <?= strtoupper(substr(getNamaUser(), 0, 1)) ?>
            </div>

            <div>

                <div class="user-name">
                    <?= htmlspecialchars(getNamaUser()) ?>
                </div>

                <div class="user-role">
                    Petugas Parkir
                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         WELCOME
    ====================================================== -->

    <div class="welcome">

        <h2>
            Selamat datang,
            <?= htmlspecialchars(getNamaUser()) ?>!
        </h2>

        <p>
            Kelola transaksi kendaraan dan pantau kondisi area parkir
            melalui dashboard petugas.
        </p>

    </div>



    <!-- =====================================================
         STATISTIK
    ====================================================== -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                🚗
            </div>

            <div>

                <div class="stat-number">
                    <?= $totalKendaraan ?>
                </div>

                <div class="stat-label">
                    Total Kendaraan
                </div>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-icon">
                🅿️
            </div>

            <div>

                <div class="stat-number">
                    <?= $kendaraanMasuk ?>
                </div>

                <div class="stat-label">
                    Sedang Parkir
                </div>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-icon">
                ↗
            </div>

            <div>

                <div class="stat-number">
                    <?= $kendaraanKeluar ?>
                </div>

                <div class="stat-label">
                    Kendaraan Keluar
                </div>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-icon">
                ▦
            </div>

            <div>

                <div class="stat-number">
                    <?= $totalArea ?>
                </div>

                <div class="stat-label">
                    Area Parkir
                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         GRAFIK
    ====================================================== -->

    <div class="chart-grid">


        <!-- Grafik transaksi -->

        <div class="chart-card">

            <div class="chart-title">

                <h3>
                    Aktivitas Parkir
                </h3>

                <span>
                    7 HARI TERAKHIR
                </span>

            </div>


            <div class="chart-container">

                <canvas id="transactionChart"></canvas>

            </div>

        </div>



        <!-- Grafik kendaraan -->

        <div class="chart-card">

            <div class="chart-title">

                <h3>
                    Jenis Kendaraan
                </h3>

                <span>
                    TOTAL
                </span>

            </div>


            <div class="chart-container">

                <canvas id="vehicleChart"></canvas>

            </div>

        </div>

    </div>



    <!-- =====================================================
         AREA PARKIR
    ====================================================== -->

    <div class="area-card">

        <h3>
            Kapasitas Area Parkir
        </h3>


        <?php if (!empty($areaParkir)): ?>

            <?php foreach ($areaParkir as $area): ?>

                <?php

                $kapasitas = (int) $area['kapasitas'];

                $terisi = (int) $area['terisi'];

                $persentase = $kapasitas > 0
                    ? round(($terisi / $kapasitas) * 100)
                    : 0;

                if ($persentase > 100) {
                    $persentase = 100;
                }

                ?>

                <div class="area-item">

                    <div class="area-header">

                        <span class="area-name">
                            <?= htmlspecialchars($area['nama_area']) ?>
                        </span>

                        <span class="area-number">
                            <?= $terisi ?> / <?= $kapasitas ?> kendaraan
                        </span>

                    </div>


                    <div class="progress">

                        <div
                            class="progress-bar"
                            style="width: <?= $persentase ?>%;"
                        ></div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p style="color:#999;">
                Belum ada data area parkir.
            </p>

        <?php endif; ?>

    </div>



    <!-- =====================================================
         QUICK MENU
    ====================================================== -->

    <div class="quick-menu">


        <a
            href="transaksi-masuk.php"
            class="quick-item"
        >

            <div class="quick-icon">
                +
            </div>

            <h4>
                Transaksi Masuk
            </h4>

            <p>
                Catat kendaraan masuk
            </p>

        </a>



        <a
            href="transaksi-keluar.php"
            class="quick-item"
        >

            <div class="quick-icon">
                ↗
            </div>

            <h4>
                Transaksi Keluar
            </h4>

            <p>
                Proses kendaraan keluar
            </p>

        </a>



        <a
            href="log.php"
            class="quick-item"
        >

            <div class="quick-icon">
                ≡
            </div>

            <h4>
                Log Aktivitas
            </h4>

            <p>
                Lihat aktivitas petugas
            </p>

        </a>



        <a
            href="../logout.php"
            class="quick-item"
        >

            <div class="quick-icon">
                ←
            </div>

            <h4>
                Keluar Sistem
            </h4>

            <p>
                Logout dari sistem
            </p>

        </a>


    </div>


</div>



<!-- =========================================================
     CHART JAVASCRIPT
========================================================= -->

<script>


/* =========================================================
   DATA PHP → JAVASCRIPT
========================================================= */

const labelHari = <?= json_encode($labelHari) ?>;

const dataMasuk = <?= json_encode($dataMasuk) ?>;

const dataKeluar = <?= json_encode($dataKeluar) ?>;

const motor = <?= $motor ?>;

const mobil = <?= $mobil ?>;

const lainnya = <?= $lainnya ?>;



/* =========================================================
   GRAFIK TRANSAKSI
========================================================= */

const transactionCanvas =
    document.getElementById('transactionChart');


new Chart(transactionCanvas, {

    type: 'line',

    data: {

        labels: labelHari,

        datasets: [

            {
                label: 'Kendaraan Masuk',

                data: dataMasuk,

                borderColor: '#6f4caf',

                backgroundColor:
                    'rgba(111, 76, 175, 0.10)',

                borderWidth: 3,

                tension: 0.4,

                fill: true,

                pointRadius: 4,

                pointHoverRadius: 6
            },

            {
                label: 'Kendaraan Keluar',

                data: dataKeluar,

                borderColor: '#a78bd4',

                backgroundColor:
                    'rgba(167, 139, 212, 0.07)',

                borderWidth: 3,

                tension: 0.4,

                fill: true,

                pointRadius: 4,

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

                position: 'bottom',

                labels: {

                    usePointStyle: true,

                    padding: 18,

                    font: {
                        size: 11
                    }
                }
            }

        },


        scales: {

            y: {

                beginAtZero: true,

                ticks: {

                    precision: 0,

                    font: {
                        size: 10
                    },

                    color: '#9992a2'
                },

                grid: {

                    color:
                        'rgba(100,70,150,0.07)'
                }

            },

            x: {

                ticks: {

                    font: {
                        size: 10
                    },

                    color: '#9992a2'
                },

                grid: {

                    display: false
                }

            }

        }

    }

});



/* =========================================================
   GRAFIK JENIS KENDARAAN
========================================================= */

const vehicleCanvas =
    document.getElementById('vehicleChart');


new Chart(vehicleCanvas, {

    type: 'doughnut',

    data: {

        labels: [
            'Motor',
            'Mobil',
            'Lainnya'
        ],

        datasets: [

            {

                data: [
                    motor,
                    mobil,
                    lainnya
                ],

                backgroundColor: [

                    '#6545a4',
                    '#8865c8',
                    '#c4afe2'

                ],

                borderWidth: 0,

                hoverOffset: 7

            }

        ]

    },


    options: {

        responsive: true,

        maintainAspectRatio: false,

        cutout: '68%',

        plugins: {

            legend: {

                position: 'bottom',

                labels: {

                    usePointStyle: true,

                    padding: 18,

                    font: {
                        size: 11
                    }
                }
            }

        }

    }

});

</script>


</body>

</html>