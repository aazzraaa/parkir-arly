<?php

require_once __DIR__ . '/../config/auth.php';

requireRole('admin');

$namaUser = getNamaUser();

$currentPage = basename($_SERVER['PHP_SELF']);

?>


<!-- =========================================================
     CSS KHUSUS SIDEBAR
     CSS INI LANGSUNG MENYATU DI FILE sidebar.php
     ========================================================= -->

<style>

    /* =====================================================
       SIDEBAR UTAMA
       ===================================================== */

    .sidebar {
        position: fixed;

        top: 0;
        left: 0;

        width: 285px;
        height: 100vh;

        display: flex;
        flex-direction: column;

        background:
            radial-gradient(
                circle at 90% 0%,
                rgba(139, 92, 246, 0.20),
                transparent 34%
            ),
            linear-gradient(
                180deg,
                #241329 0%,
                #2d1633 48%,
                #1f1124 100%
            );

        color: #ffffff;

        border-right: 1px solid rgba(255, 255, 255, 0.06);

        box-shadow:
            12px 0 40px rgba(31, 14, 36, 0.14);

        z-index: 1000;

        overflow: hidden;
    }


    /* =====================================================
       BRAND / LOGO
       ===================================================== */

    .sidebar-brand {

        height: 92px;

        padding: 0 24px;

        display: flex;
        align-items: center;

        gap: 14px;

        flex-shrink: 0;

        border-bottom:
            1px solid
            rgba(255, 255, 255, 0.07);
    }


    .sidebar-logo {

        width: 47px;
        height: 47px;

        flex: 0 0 47px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 14px;

        background:
            linear-gradient(
                135deg,
                #8b5cf6,
                #6d28d9
            );

        box-shadow:
            0 10px 25px
            rgba(124, 58, 237, 0.30),

            inset 0 1px 0
            rgba(255, 255, 255, 0.22);
    }


    .sidebar-logo svg {

        width: 25px;
        height: 25px;

        fill: none;

        stroke: #ffffff;

        stroke-width: 1.8;

        stroke-linecap: round;
        stroke-linejoin: round;
    }


    .sidebar-brand-info {

        min-width: 0;

        overflow: hidden;
    }


    .sidebar-title {

        color: #ffffff;

        font-size: 17px;

        font-weight: 800;

        line-height: 1.2;

        white-space: nowrap;
    }


    .sidebar-subtitle {

        margin-top: 5px;

        color:
            rgba(255, 255, 255, 0.50);

        font-size: 11px;

        font-weight: 600;

        letter-spacing: 0.5px;

        white-space: nowrap;
    }


    /* =====================================================
       SIDEBAR MENU
       ===================================================== */

    .sidebar-menu {

        flex: 1;

        padding: 22px 15px;

        overflow-y: auto;

        overflow-x: hidden;
    }


    .sidebar-menu::-webkit-scrollbar {
        width: 4px;
    }


    .sidebar-menu::-webkit-scrollbar-track {
        background: transparent;
    }


    .sidebar-menu::-webkit-scrollbar-thumb {

        background:
            rgba(255, 255, 255, 0.12);

        border-radius: 20px;
    }


    .sidebar-menu::-webkit-scrollbar-thumb:hover {

        background:
            rgba(255, 255, 255, 0.20);
    }


    /* =====================================================
       LABEL MENU
       ===================================================== */

    .menu-label {

        padding: 0 13px;

        margin-bottom: 10px;

        color:
            rgba(255, 255, 255, 0.35);

        font-size: 10px;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 1.2px;
    }


    .menu-label.menu-section {

        margin-top: 25px;
    }


    /* =====================================================
       MENU ITEM
       ===================================================== */

    .menu-item {

        position: relative;

        width: 100%;

        min-height: 49px;

        padding: 0 13px;

        margin-bottom: 6px;

        display: flex;
        align-items: center;

        gap: 13px;

        color:
            rgba(255, 255, 255, 0.65);

        border-radius: 13px;

        transition:
            background 0.20s ease,
            color 0.20s ease,
            transform 0.20s ease,
            box-shadow 0.20s ease;
    }


    /* HOVER */

    .menu-item:hover {

        color: #ffffff;

        background:
            rgba(255, 255, 255, 0.075);

        transform:
            translateX(2px);
    }


    /* ACTIVE */

    .menu-item.active {

        color: #ffffff;

        background:
            linear-gradient(
                135deg,
                rgba(139, 92, 246, 0.95),
                rgba(109, 40, 217, 0.85)
            );

        box-shadow:
            0 9px 24px
            rgba(91, 33, 182, 0.25),

            inset 0 1px 0
            rgba(255, 255, 255, 0.13);
    }


    /* GARIS ACTIVE DI KIRI */

    .menu-item.active::before {

        content: "";

        position: absolute;

        left: 0;

        top: 10px;
        bottom: 10px;

        width: 3px;

        background: #ffffff;

        border-radius:
            0 4px 4px 0;
    }


    /* =====================================================
       MENU ICON
       ===================================================== */

    .menu-icon {

        width: 21px;
        height: 21px;

        flex: 0 0 21px;

        display: flex;
        align-items: center;
        justify-content: center;
    }


    .menu-icon svg {

        width: 19px;
        height: 19px;

        fill: none;

        stroke: currentColor;

        stroke-width: 1.8;

        stroke-linecap: round;
        stroke-linejoin: round;
    }


    .menu-text {

        font-size: 13.5px;

        font-weight: 600;

        line-height: 1;

        white-space: nowrap;
    }


    /* =====================================================
       BAGIAN BAWAH SIDEBAR
       ===================================================== */

    .sidebar-bottom {

        flex-shrink: 0;

        padding: 15px;

        border-top:
            1px solid
            rgba(255, 255, 255, 0.07);

        background:
            linear-gradient(
                180deg,
                rgba(0, 0, 0, 0.04),
                rgba(0, 0, 0, 0.13)
            );
    }


    /* =====================================================
       USER MINI
       ===================================================== */

    .user-mini {

        display: flex;
        align-items: center;

        gap: 11px;

        padding: 11px;

        margin-bottom: 9px;

        border-radius: 14px;

        background:
            rgba(255, 255, 255, 0.055);

        border:
            1px solid
            rgba(255, 255, 255, 0.06);
    }


    .user-avatar {

        width: 39px;
        height: 39px;

        flex: 0 0 39px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 12px;

        background:
            linear-gradient(
                135deg,
                #a78bfa,
                #7c3aed
            );

        color: #ffffff;

        font-size: 14px;

        font-weight: 800;

        box-shadow:
            0 6px 16px
            rgba(124, 58, 237, 0.25);
    }


    .user-info {

        min-width: 0;

        overflow: hidden;
    }


    .user-name {

        color: #ffffff;

        font-size: 12.5px;

        font-weight: 700;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }


    .user-role {

        margin-top: 3px;

        color:
            rgba(255, 255, 255, 0.45);

        font-size: 10.5px;

        font-weight: 500;

        white-space: nowrap;
    }


    /* =====================================================
       LOGOUT
       ===================================================== */

    .logout {

        min-height: 45px;

        padding: 0 13px;

        display: flex;
        align-items: center;

        gap: 13px;

        color:
            rgba(255, 255, 255, 0.58);

        border-radius: 12px;

        transition:
            background 0.20s ease,
            color 0.20s ease,
            transform 0.20s ease;
    }


    .logout:hover {

        color: #ffffff;

        background:
            rgba(220, 38, 38, 0.16);

        transform:
            translateX(2px);
    }


    .logout .menu-icon svg {

        stroke: currentColor;
    }


    /* =====================================================
       RESPONSIVE
       ===================================================== */

    @media (max-width: 900px) {

        .sidebar {

            transform:
                translateX(-100%);

            transition:
                transform 0.25s ease;
        }


        .sidebar.open {

            transform:
                translateX(0);
        }
    }


    @media (max-width: 600px) {

        .sidebar {

            width: 280px;
        }


        .sidebar-brand {

            height: 82px;

            padding: 0 20px;
        }


        .sidebar-menu {

            padding:
                18px 13px;
        }


        .sidebar-bottom {

            padding: 13px;
        }
    }

</style>


<!-- =========================================================
     SIDEBAR HTML
     ========================================================= -->

<aside class="sidebar">


    <!-- =====================================================
         LOGO & BRAND
         ===================================================== -->

    <div class="sidebar-brand">

        <div class="sidebar-logo">

            <!-- Parking SVG -->
            <svg viewBox="0 0 24 24" aria-hidden="true">

                <rect
                    x="3"
                    y="3"
                    width="18"
                    height="18"
                    rx="3"
                ></rect>

                <path
                    d="M9 17V7h4a3 3 0 0 1 0 6H9"
                ></path>

            </svg>

        </div>


        <div class="sidebar-brand-info">

            <div class="sidebar-title">
                Sistem Parkir
            </div>

            <div class="sidebar-subtitle">
                Admin Panel
            </div>

        </div>

    </div>


    <!-- =====================================================
         MENU
         ===================================================== -->

    <nav class="sidebar-menu">


        <div class="menu-label">
            Menu Utama
        </div>


        <!-- =================================================
             DASHBOARD
             ================================================= -->

        <a
            href="dashboard.php"
            class="menu-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <rect
                        x="3"
                        y="3"
                        width="7"
                        height="7"
                        rx="1"
                    ></rect>

                    <rect
                        x="14"
                        y="3"
                        width="7"
                        height="7"
                        rx="1"
                    ></rect>

                    <rect
                        x="3"
                        y="14"
                        width="7"
                        height="7"
                        rx="1"
                    ></rect>

                    <rect
                        x="14"
                        y="14"
                        width="7"
                        height="7"
                        rx="1"
                    ></rect>

                </svg>

            </span>


            <span class="menu-text">
                Dashboard
            </span>

        </a>


        <!-- =================================================
             DATA KENDARAAN
             ================================================= -->

        <a
            href="kendaraan.php"
            class="menu-item <?= $currentPage === 'kendaraan.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <path d="M5 17h14"></path>

                    <path
                        d="M6 17l1.5-6h9L18 17"
                    ></path>

                    <path
                        d="M8 11l1.5-3h5L16 11"
                    ></path>

                    <circle
                        cx="8"
                        cy="17"
                        r="1.5"
                    ></circle>

                    <circle
                        cx="16"
                        cy="17"
                        r="1.5"
                    ></circle>

                </svg>

            </span>


            <span class="menu-text">
                Data Kendaraan
            </span>

        </a>


        <!-- =================================================
             TARIF PARKIR
             ================================================= -->

        <a
            href="tarif.php"
            class="menu-item <?= $currentPage === 'tarif.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <circle
                        cx="12"
                        cy="12"
                        r="8"
                    ></circle>

                    <path
                        d="M12 8v8"
                    ></path>

                    <path
                        d="M15 10.5c0-1-1.1-1.5-3-1.5s-3 .5-3 1.5 1.1 1.5 3 1.5 3 .5 3 1.5-1.1 1.5-3 1.5-3-.5-3-1.5"
                    ></path>

                </svg>

            </span>


            <span class="menu-text">
                Tarif Parkir
            </span>

        </a>


        <!-- =================================================
             AREA PARKIR
             ================================================= -->

        <a
            href="area.php"
            class="menu-item <?= $currentPage === 'area.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <rect
                        x="3"
                        y="3"
                        width="18"
                        height="18"
                        rx="3"
                    ></rect>

                    <path
                        d="M8 17V7h4a3 3 0 0 1 0 6H8"
                    ></path>

                </svg>

            </span>


            <span class="menu-text">
                Area Parkir
            </span>

        </a>


        <!-- =================================================
             TRANSAKSI
             ================================================= -->

        <div class="menu-label menu-section">
            Transaksi
        </div>


        <!-- =================================================
             TRANSAKSI PARKIR
             ================================================= -->

        <a
            href="transaksi.php"
            class="menu-item <?= $currentPage === 'transaksi.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <path d="M7 7h13"></path>

                    <path
                        d="m16 4 4 3-4 3"
                    ></path>

                    <path d="M17 17H4"></path>

                    <path
                        d="m8 14-4 3 4 3"
                    ></path>

                </svg>

            </span>


            <span class="menu-text">
                Transaksi Parkir
            </span>

        </a>


        <!-- =================================================
             DATA USER
             ================================================= -->

        <a
            href="user.php"
            class="menu-item <?= $currentPage === 'user.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <circle
                        cx="9"
                        cy="8"
                        r="3"
                    ></circle>

                    <path
                        d="M3.5 19c.7-3 2.5-5 5.5-5s4.8 2 5.5 5"
                    ></path>

                    <path
                        d="M16 11a3 3 0 1 0 0-6"
                    ></path>

                    <path
                        d="M16 14c2.5 0 4 1.8 4.5 4"
                    ></path>

                </svg>

            </span>


            <span class="menu-text">
                Data User
            </span>

        </a>


        <!-- =================================================
             LOG AKTIVITAS
             ================================================= -->

        <a
            href="log.php"
            class="menu-item <?= $currentPage === 'log.php' ? 'active' : '' ?>"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <path
                        d="M3 12a9 9 0 1 0 3-6.7"
                    ></path>

                    <path
                        d="M3 4v6h6"
                    ></path>

                    <path
                        d="M12 7v5l3 2"
                    ></path>

                </svg>

            </span>


            <span class="menu-text">
                Log Aktivitas
            </span>

        </a>

    </nav>


    <!-- =====================================================
         USER BOTTOM
         ===================================================== -->

    <div class="sidebar-bottom">


        <div class="user-mini">


            <div class="user-avatar">

                <?= htmlspecialchars(
                    strtoupper(
                        substr($namaUser, 0, 1)
                    )
                ) ?>

            </div>


            <div class="user-info">

                <div class="user-name">

                    <?= htmlspecialchars($namaUser) ?>

                </div>


                <div class="user-role">

                    Administrator

                </div>

            </div>

        </div>


        <!-- =================================================
             LOGOUT
             ================================================= -->

        <a
            href="../logout.php"
            class="logout"
        >

            <span class="menu-icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >

                    <path
                        d="M10 17l5-5-5-5"
                    ></path>

                    <path
                        d="M15 12H3"
                    ></path>

                    <path
                        d="M21 19V5a2 2 0 0 0-2-2h-6"
                    ></path>

                </svg>

            </span>


            <span class="menu-text">
                Keluar
            </span>

        </a>

    </div>

</aside>