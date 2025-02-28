document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".menu-item-has-children > a").forEach(function (link) {
        link.addEventListener("click", function (e) {
            e.preventDefault(); // Prevent link navigation
            let submenu = this.nextElementSibling;

            // Close all other open dropdowns
            document.querySelectorAll(".sub-menu").forEach(function (menu) {
                if (menu !== submenu) {
                    menu.style.maxHeight = null;
                    menu.style.opacity = "0";
                    menu.style.visibility = "hidden";
                }
            });

            // Toggle the clicked dropdown
            if (submenu.style.maxHeight) {
                submenu.style.maxHeight = null; // Collapse
                submenu.style.opacity = "0";
                submenu.style.visibility = "hidden";
            } else {
                submenu.style.maxHeight = submenu.scrollHeight + "px"; // Expand
                submenu.style.opacity = "1";
                submenu.style.visibility = "visible";
            }
        });
    });
});