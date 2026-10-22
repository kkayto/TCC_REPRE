<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

$nome = $_SESSION['user_name']?? '';

require_role(['representante']);

$turmaId = filter_input(
    INPUT_GET,
    'turma_id',
    FILTER_VALIDATE_INT
);

if (!$turmaId || $turmaId <= 0) {
    http_response_code(400);
    exit('Turma inválida.');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividade criada - REPRE</title>
    <link rel="stylesheet" href="atividadeCriada.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <div class="pagina">
        <nav class="navbar"> <a href="home.php" class="logo"> REPRE </a>
            <div class="nav-links"> <a href="home.php"> <i class="fa-solid fa-house"></i> Início </a> <a
                    href="adminRepresentante.php" class="ativo"> <i class="fa-solid fa-people-group"></i> Representante
                </a> </div>
            <div class="nav-direita"> <a href="perfil.php" class="perfil-link"> <i class="fa-solid fa-user"></i> </a> <a
                    href="logout.php" class="btn-sair"> Sair </a> </div>
        </nav>
        <main class="conteudo">
            <section class="secao confirmacao">
                <div class="confirmacao-icone"> <i class="fa-solid fa-check"></i> </div> <span class="tag"> ATIVIDADE
                    CRIADA </span>
                <h1> Atividade criada com sucesso! </h1>
                <p> A atividade foi cadastrada corretamente e já está disponível no sistema. </p>
                <p class="saudacao"> Tudo certo,
                    <?= e($nome) ?>!
                </p>
                <div class="botoes"> <a href="adminRepresentante.php" class="btn-principal"> <i
                            class="fa-solid fa-people-group"></i> Voltar para área do representante </a> <a
                        href="home.php" class="btn-secundario"> <i class="fa-solid fa-house"></i> Ir para o início </a>
                </div>
            </section>
        </main>
    </div>
</body>

</html>