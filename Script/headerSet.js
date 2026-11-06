
fetch("Components/headerSet.php")
    .then(response => {
        if (!response.ok) {
            throw new Error("Não foi possível carregar o cabeçalho.");
        }

        return response.text();
    })
    .then(data => {
        const headerContainer = document.getElementById("headerSet");

        if (!headerContainer) {
            console.error("Elemento #headerSet não encontrado.");
            return;
        }

        // Insere o cabeçalho primeiro
        headerContainer.innerHTML = data;

        // Localiza os elementos DEPOIS da inserção
        const menuBtn = document.getElementById("menu-btn");
        const closeBtn = document.getElementById("close-btn");
        const navbar = document.getElementById("navbar");

        const notificationBtn = document.getElementById("notificationBtn");
        const notificationPopup = document.getElementById("notificationPopup");

        // MENU
        if (navbar) {
            if (menuBtn) {
                menuBtn.addEventListener("click", event => {
                    event.stopPropagation();
                    navbar.classList.toggle("opened");
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener("click", () => {
                    navbar.classList.remove("opened");
                });
            }

            document.addEventListener("click", event => {
                if (
                    navbar.classList.contains("opened") &&
                    !navbar.contains(event.target) &&
                    menuBtn &&
                    !menuBtn.contains(event.target)
                ) {
                    navbar.classList.remove("opened");
                }
            });
        }

        // NOTIFICAÇÕES
        if (notificationBtn && notificationPopup) {

            notificationBtn.addEventListener("click", event => {
                event.stopPropagation();

                notificationPopup.classList.toggle("active");

                console.log(
                    "Popup aberto:",
                    notificationPopup.classList.contains("active")
                );
            });

            notificationPopup.addEventListener("click", event => {
                event.stopPropagation();
            });

            document.addEventListener("click", () => {
                notificationPopup.classList.remove("active");
            });

        } else {
            console.error("Sininho ou popup não encontrado!");
        }

        console.log("Cabeçalho carregado e eventos inicializados!");
    })
    .catch(error => {
        console.error("Erro ao carregar o cabeçalho:", error);
    });
