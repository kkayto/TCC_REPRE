fetch("Components/headerSet.php")
    .then(response => response.text())
    .then(data => {
        document.getElementById("headerSet").innerHTML = data;

        //------------------------------------------------

        const menuBtn = document.getElementById("menu-btn");
        const closeBtn = document.getElementById("close-btn");
        const navbar = document.getElementById("navbar");

        menuBtn.addEventListener("click", () => {
            navbar.classList.toggle("opened");
        });

        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                navbar.classList.remove("opened");
            });
        }

        // Fecha ao clicar fora do menu
        window.addEventListener("click", function(event) {
            if (!navbar.classList.contains("opened")) {
                return;
            }

            if (
                !navbar.contains(event.target) &&
                !menuBtn.contains(event.target)
            ) {
                navbar.classList.remove("opened");
            }
        }, true);
        
        //------------------------------------

    });
