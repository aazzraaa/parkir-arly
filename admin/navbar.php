<?php

require_once __DIR__ . '/../config/auth.php';

requireRole('admin');

$namaUser = getNamaUser();

$pageTitle = $pageTitle ?? 'Dashboard';
$pageDescription = $pageDescription ?? 'Ringkasan sistem parkir';

$initial = strtoupper(substr($namaUser, 0, 1));

?>

<header class="topbar">

    <!-- BAGIAN KIRI -->
    <div class="topbar-left">

        <button
            type="button"
            class="sidebar-toggle"
            aria-label="Buka menu"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div class="page-heading">

            <h1>
                <?= htmlspecialchars($pageTitle) ?>
            </h1>

            <p>
                <?= htmlspecialchars($pageDescription) ?>
            </p>

        </div>

    </div>


    <!-- BAGIAN KANAN -->
    <div class="topbar-right">

        <div class="admin-profile">

            <!-- AVATAR -->
            <div class="admin-avatar">
                <?= htmlspecialchars($initial) ?>
            </div>


            <!-- INFORMASI ADMIN -->
            <div class="admin-profile-info">

                <div class="admin-profile-name">
                    <?= htmlspecialchars($namaUser) ?>
                </div>

                <div class="admin-profile-role">
                    Administrator
                </div>

            </div>


            <!-- PANAH -->
            <div class="admin-profile-arrow">

                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>

            </div>

        </div>

    </div>

</header>