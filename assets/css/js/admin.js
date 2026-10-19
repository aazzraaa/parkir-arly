/* =========================================================
   ADMIN JAVASCRIPT
   Sistem Parkir
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       LUCIDE ICON
       ===================================================== */

    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }


    /* =====================================================
       SIDEBAR MENU
       ===================================================== */

    const menuItems = document.querySelectorAll(".menu-item");

    menuItems.forEach(function (item) {

        item.addEventListener("click", function () {

            menuItems.forEach(function (menu) {
                menu.classList.remove("clicked");
            });

            item.classList.add("clicked");

        });

    });


    /* =====================================================
       HOVER EFFECT
       ===================================================== */

    const cards = document.querySelectorAll(
        ".stat-card, .dashboard-card, .quick-card"
    );

    cards.forEach(function (card) {

        card.addEventListener("mouseenter", function () {
            card.classList.add("is-hovered");
        });

        card.addEventListener("mouseleave", function () {
            card.classList.remove("is-hovered");
        });

    });


    /* =====================================================
       CONFIRM LOGOUT
       ===================================================== */

    const logoutLinks = document.querySelectorAll(".logout");

    logoutLinks.forEach(function (logout) {

        logout.addEventListener("click", function (event) {

            const yakin = confirm(
                "Apakah Anda yakin ingin keluar dari sistem?"
            );

            if (!yakin) {
                event.preventDefault();
            }

        });

    });

});