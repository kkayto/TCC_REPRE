<?php
require_once __DIR__ . '/../Settings/permissoes.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}


// Resgata o valor limpo da sessão
$fotoBase64 = $_SESSION['user_foto'] ?? '';


$inicial = $_SESSION['user_inicial'] ?? '';

// Se a inicial não foi criada no login por algum motivo, gera ela aqui agora de forma automática
if (empty($inicial)) {
    $nomeUsuario = $_SESSION['user_name'] ?? 'Usuário';
    $inicial = function_exists('mb_substr') 
        ? mb_substr($nomeUsuario, 0, 1, 'UTF-8') 
        : substr($nomeUsuario, 0, 1);
}

// Adiciona o cabeçalho correto apenas se houver uma string convertida
$imagemPronta = '';
if (!empty($fotoBase64)) {
  // Caso a sessão venha puramente em base64, adicionamos o prefixo correto
  if (substr($fotoBase64, 0, 5) === 'data:') {
    $imagemPronta = $fotoBase64;
  } else {
    $imagemPronta = 'data:image/jpeg;base64,' . $fotoBase64;
  }
}
?>

<style>
  *,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    text-decoration: none;
    color: inherit;
}

body {
    padding-top: 90px;
    font-family: 'Montserrat', sans-serif;
    background-color: var(--background);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    max-width: 100vw;
    min-height: 100vh;
}
  header {
    position: fixed;
    top: 0;
    left: 0;
    padding: 15px 10px;
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #eeeeee;
    border-radius: 22px;
    box-shadow: 0 8px 25px rgba(70, 60, 130, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 5px;
    z-index: 1000;
    /* ARRUMAR AQUI */
    width: 100vw;
  }

  .logo {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    gap: 10px;
    font-size: 24px;
  }

  .header-options {
    display: flex;
    align-items: center;
    font-size: 24px;
  }

  .header-options button {
    font-weight: 1000;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
  }

  .representante {
    background: #584d76;
    color: var(--white);
    padding: 5px;
    border-radius: 8px;
    font-weight: 600;
  }

  .bell {
    position: relative;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 21px;
    cursor: pointer;
    transition: 0.25s ease;
  }

  .bell::after {
    content: "";
    position: absolute;
    top: 6px;
    right: 6px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #e74c3c;
    border: 2px solid white;
  }

  .profile {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    box-shadow: 0 3px 10px rgba(109, 66, 219, 0.15);
    transition: 0.25s ease;
    align-self: center;
  }

  .profile:hover {
    transform: scale(1.05);
    border-color: #584391;
  }


  /* FOTO DE PERFIL (QUANDO HÁ FOTO NO BANCO) */
  .header-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--white);
    box-shadow: var(--shadow);
    transition: transform 0.2s ease;
  }

  .header-avatar:hover {
    transform: scale(1.05);
  }

  .header-avatar-default {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-weight: 700;
    background: var(--soft-purple);
    color: var(--main-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border: 2px solid var(--white);
    box-shadow: var(--shadow);
    flex-shrink: 0;
  }

  #navbar {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: auto;

    background: white;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding-top: 20px;
    padding-bottom: 10px;
    transform: translateY(100%);
    /* Manda o menu lá pra baixo, fica invisivel */
    transition: 0.3s ease;

    z-index: 1000;
  }

  .nav-left,
  .nav-right {
    display: flex;
    flex-direction: column;
    gap: 15px;
    text-align: center;
    align-items: center;
  }

  #navbar.opened {
    transform: translateY(0);
  }

  #navbar button {
    border: 1px solid #dddff0;
    padding: 10px 15px;
    border-radius: 50px;
    font-weight: 500;
    background-color: #ded4fc;
    cursor: pointer;
  }

  .nav-closed {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 10px;
    flex: 1;
  }

  .nav-closed a {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 9px 11px;
    border-radius: 10px;
    text-decoration: none;
    color: #333333;
    font-size: 18px;
    font-weight: 800;
    white-space: nowrap;
    transition: all 0.25s ease;
  }

  ::-webkit-scrollbar {
    width: 8px;
  }

  ::-webkit-scrollbar-track {
    background: var(--fundo);
  }

  ::-webkit-scrollbar-thumb {
    background: rgba(109, 66, 219, 0.3);
    border-radius: 20px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: var(--main-purple);
  }

  @media (min-width: 768px) {
    header {
      left: 50%;
      transform: translateX(-50%);
      margin-top: 20px;
      box-shadow: 7px 7px 7px #3333333b;
      width: 94%;
      justify-content: space-between;
    }

    #navbar {
      display: none;
    }

    #navbar.opened {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      /* Faz os links ocuparem toda a largura interna */
      justify-content: flex-start;
      gap: 12px;

      /* Posicionamento Flutuante Exato */
      position: fixed;
      top: 95px;
      /* Distância calibrada para ficar logo abaixo do cabeçalho */
      right: 0;
      /* Alinha dinamicamente com o canto direito do header */
      left: auto;
      transform: none;

      /* Visual "Bonitinho" Premium */
      width: auto;
      height: min-content;
      padding: 18px 14px;
      background-color: var(--white);
      /* O roxo do seu botão de representante */
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
      border: 1px solid var(--border);
      transition: all 0.2s ease;
      border-top: 0px none;
    }

    #navbar.opened::before {
      content: "";
      position: absolute;
      top: -10px;
      /* Joga a ponta para fora da borda superior */
      right: 95px;
      /* Alinha na direção do ícone de hambúrguer */

      /* Cria o triângulo usando as bordas transparentes */
      border-left: 10px solid transparent;
      border-right: 10px solid transparent;
      border-bottom: 10px solid var(--white);
      /* Mesma cor roxa exata do fundo do menu */
    }

    #navbar button {
      display: none;
    }



    #navbar a {
      font-size: 18px;
    }

    .representante {
      width: auto;
      padding: 10px;
      white-space: nowrap;
    }
  }

  @media (min-width: 1200px) {
    header {
      box-shadow: 16px 7px 16px #33333369;
      padding-left: 30px;
      padding-right: 30px;
    }

    body {
      padding-top: 140px;
    }

    #navbar {
      display: flex;
      flex-direction: row;
      position: static;
      width: auto;
      height: auto;
      background: transparent;
      transform: none;
      justify-content: space-between;
      padding: 0px 0px 0px 10px;
      width: 100%;
      padding-left: 20px;
    }
    .nav-left, .nav-right{
      flex-direction: row;
    }
    #menu-btn,
    #navbar button,
    #navbar .profileLink {
      display: none;
    }

    .logo {
      font-size: 28px;
    }

    .logo p {
      display: flex;
    }

    .logo img {
      height: 40px;
    }

    .nav-left {
      gap: 55px;
    }

    #navbar a {
      font-size: 20px;
    }

    .profile {
      height: 40px;
      width: 40px;
    }

    .representante {
      width: min-content;
      padding: 10px;
      white-space: nowrap;
    }
    
  }
</style>

<header>
  <a href="home.php" class="logo">
    <img src="Images/Logo.png" alt="Logo" height="30" />
    <p>REPRE</p>
  </a>
  <nav id="navbar">
    <div class="nav-left">
      <a href="home.php">Inicio</a>
      <a href="atividades.php">Atividades</a>
      <a href="comunidades.php">Comunidade</a>
    </div>
    <div class="nav-right">
      <?php if (seRepresentante()): ?>
        <a href="adminRepresentante.php" class="representante">
          <i class="fa-solid fa-people-group"></i>
          Área do Representante
        </a>
      <?php endif; ?>
      <button id="close-btn">X</button>
    </div>
  </nav>
  <div class="header-options">
    <button id="menu-btn">☰</button>

    <span class="bell">
      <i class="fa-solid fa-bell"></i>
    </span>

    <a href="profile.php">
      <!-- Se a variável de imagem NÃO estiver vazia e tiver um tamanho mínimo aceitável -->
      <?php if (!empty($imagemPronta)): ?>
          <!-- Se houver imagem carregada -->
          <img src="<?= $imagemPronta ?>" alt="Foto de Perfil" class="header-avatar">
        <?php else: ?>
          <!-- Se não houver imagem, exibe o círculo com a letra inicial tratada -->
          <div class="header-avatar-default">
            <?= $inicial ?>
          </div>
        <?php endif; ?>
    </a>
  </div>
</header>
