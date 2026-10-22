<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];

$turmaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$turmaId || $turmaId <= 0) {
    http_response_code(400);
    exit('Turma inválida.');
}

$sql = "
    SELECT
        t.id,
        t.nome,
        t.serie,
        t.turno,
        t.ano_letivo,
        t.ativa
    FROM turma_membros tm

    INNER JOIN turmas t
        ON t.id = tm.turma_id

    WHERE tm.turma_id = :turma_id
      AND tm.aluno_id = :aluno_id
      AND tm.papel = 'representante'
      AND t.ativa = 1

    LIMIT 1
";

$stmt = db()->prepare($sql);

$stmt->execute([
    ':turma_id' => $turmaId,
    ':aluno_id' => $usuarioId
]);

$turma = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$turma) {
    http_response_code(403);
    exit('Você não possui permissão para administrar esta turma.');
}

$sql = "
    SELECT COUNT(*)
    FROM turma_membros
    WHERE turma_id = :turma_id
      AND papel = 'aluno'
";

$stmt = db()->prepare($sql);

$stmt->execute([
    ':turma_id' => $turmaId
]);

$totalAlunos = (int) $stmt->fetchColumn();

$sql = "
    SELECT
        td.id,
        d.nome,
        a.nome AS professor
    FROM turma_disciplinas td

    INNER JOIN disciplinas d
        ON d.id = td.disciplina_id

    LEFT JOIN alunos a
        ON a.id = td.professor_id

    WHERE td.turma_id = :turma_id

    ORDER BY d.nome
";

$stmt = db()->prepare($sql);

$stmt->execute([
    ':turma_id' => $turmaId
]);

$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT COUNT(*)
    FROM atividades a

    INNER JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id

    WHERE td.turma_id = :turma_id
";

$stmt = db()->prepare($sql);

$stmt->execute([
    ':turma_id' => $turmaId
]);

$totalAtividades = (int) $stmt->fetchColumn();
$totalAvisos = 0;
$totalEventos = 0;

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?= e($turma['nome']) ?> - REPRE
</title>

<link
    rel="stylesheet"
    href="adminTurma.css"
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


</head>

<body>

<div class="pagina">

<!-- NAVBAR -->

<nav class="navbar">

    <a
        href="home.php"
        class="logo"
    >
        REPRE
    </a>

    <div class="nav-links">

        <a href="home.php">

            <i class="fa-solid fa-house"></i>

            Início

        </a>

        <a
            href="adminRepresentante.php"
            class="ativo"
        >

            <i class="fa-solid fa-people-group"></i>

            Representante

        </a>

    </div>

    <div class="nav-direita">

        <a
            href="perfil.php"
            class="perfil-link"
        >

            <i class="fa-solid fa-user"></i>

        </a>

        <a
            href="logout.php"
            class="btn-sair"
        >

            Sair

        </a>

    </div>

</nav>


<!-- CONTEÚDO -->

<main class="conteudo">

    <!-- VOLTAR -->

    <a
        href="adminRepresentante.php"
        class="voltar"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Voltar para minhas turmas

    </a>


    <!-- CABEÇALHO DA TURMA -->

    <section class="cabecalho-turma">

        <div class="cabecalho-esquerda">

            <div class="turma-icone-grande">

                <i class="fa-solid fa-users"></i>

            </div>

            <div>

                <span class="tag">
                    ADMINISTRAÇÃO DA TURMA
                </span>

                <h1>
                    <?= e($turma['nome']) ?>
                </h1>

                <p>

                    <?= e($turma['serie'] ?? 'Turma') ?>

                    <?php if (!empty($turma['turno'])): ?>

                        · <?= e(ucfirst($turma['turno'])) ?>

                    <?php endif; ?>

                    · <?= e((string) $turma['ano_letivo']) ?>

                </p>

            </div>

        </div>

        <span class="status">

            <i class="fa-solid fa-circle"></i>

            Ativa

        </span>

    </section>


    <!-- INFORMAÇÕES -->

    <section class="informacoes-grid">

        <div class="info-card">

            <div class="info-icone">

                <i class="fa-solid fa-users"></i>

            </div>

            <div>

                <span>
                    Alunos
                </span>

                <strong>
                    <?= (int) $totalAlunos ?>
                </strong>

            </div>

        </div>


        <div class="info-card">

            <div class="info-icone">

                <i class="fa-solid fa-book"></i>

            </div>

            <div>

                <span>
                    Disciplinas
                </span>

                <strong>
                    <?= count($disciplinas) ?>
                </strong>

            </div>

        </div>


        <div class="info-card">

            <div class="info-icone">

                <i class="fa-solid fa-list-check"></i>

            </div>

            <div>

                <span>
                    Atividades
                </span>

                <strong>
                    <?= (int) $totalAtividades ?>
                </strong>

            </div>

        </div>


        <div class="info-card">

            <div class="info-icone">

                <i class="fa-solid fa-calendar"></i>

            </div>

            <div>

                <span>
                    Ano letivo
                </span>

                <strong>
                    <?= e((string) $turma['ano_letivo']) ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- AÇÕES -->

    <section class="secao">

        <div class="secao-header">

            <div>

                <h2>
                    Gerenciar turma
                </h2>

                <p>
                    Escolha uma das opções abaixo.
                </p>

            </div>

        </div>


        <div class="acoes-grid">

            <!-- ALUNOS -->

            <a
                href="alunosTurma.php?turma_id=<?= (int) $turmaId ?>"
                class="acao-card"
            >

                <div class="acao-icone roxo">

                    <i class="fa-solid fa-users"></i>

                </div>

                <div>

                    <h3>
                        Alunos
                    </h3>

                    <p>
                        Visualize os alunos da turma.
                    </p>

                </div>

                <i class="fa-solid fa-chevron-right seta"></i>

            </a>


            <!-- DISCIPLINAS -->

<a
    href="diciplinasTurma.php?id=<?= (int) $turmaId ?>"
    class="acao-card"
>
    <div class="acao-icone roxo">

        <i class="fa-solid fa-book"></i>

    </div>

    <div>

        <h3>
            Disciplinas
        </h3>

        <p>
            Veja as disciplinas da turma.
        </p>

    </div>

    <i class="fa-solid fa-chevron-right seta"></i>

</a>


            <!-- ATIVIDADES -->

            <a
                href="atividadesTurma.php?turma_id=<?= (int) $turmaId ?>"
                class="acao-card"
            >

                <div class="acao-icone verde">

                    <i class="fa-solid fa-list-check"></i>

                </div>

                <div>

                    <h3>
                        Atividades
                    </h3>

                    <p>
                        Gerencie as atividades da turma.
                    </p>

                </div>

                <i class="fa-solid fa-chevron-right seta"></i>

            </a>


            <!-- AVISOS -->

           <a
    href="avisosTurma.php?id=<?= (int) $turmaId ?>"
    class="acao-card"
>
    <div class="acao-icone azul">
        <i class="fa-solid fa-bullhorn"></i>
    </div>

    <div>
        <h3>
            Avisos
        </h3>

        <p>
            Publique avisos para a turma.
        </p>
    </div>

    <i class="fa-solid fa-chevron-right seta"></i>
</a>


            <!-- EVENTOS -->

            <a
                    href="eventosTurma.php?id=<?= (int) $turmaId ?>"
                class="acao-card"
            >

                <div class="acao-icone roxo">

                    <i class="fa-solid fa-calendar"></i>

                </div>

                <div>

                    <h3>
                        Eventos
                    </h3>

                    <p>
                        Gerencie eventos da turma.
                    </p>

                </div>

                <i class="fa-solid fa-chevron-right seta"></i>

            </a>


            <!-- MATERIAIS DE ESTUDO -->
<a href="materiaisEstudos.php?id=<?= (int) $turmaId ?>"class="acao-card">
    <div class="acao-icone roxo">
        <i class="fa-solid fa-book-open"></i>
    </div>

    <div>
        <h3>Materiais de estudo</h3>
        <p>Compartilhe materiais e recursos de aprendizagem.</p>
    </div>

    <i class="fa-solid fa-chevron-right seta"></i>
</a>
        </div>

    </section>


</main>

</div>

</body>

</html>
