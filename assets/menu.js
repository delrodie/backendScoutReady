document.addEventListener("turbo:load", function () {
        const menuToggle = document.querySelector(".brand-bar");
        const menuVertical = document.querySelector(".menu-vertical");
        const mainContent = document.getElementById("mainContent");

        menuToggle.addEventListener("click", function (e) {
            e.preventDefault();
            if (window.innerWidth < 992) {
                menuVertical.classList.toggle("menu-visible");
            } else {
                menuVertical.classList.toggle("menu-hidden");
                mainContent.classList.toggle("expanded");
            }
        });

        const closeBtn = document.querySelector(".close-menu");
        if (closeBtn) {
            closeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                menuVertical.classList.remove("menu-visible");
            });
        }
    });