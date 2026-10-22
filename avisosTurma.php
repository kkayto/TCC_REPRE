<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];

$turmaId = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

if (!$turmaId) {
    $turmaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

if (!$turmaId) {
    http_response_code(400);
    exit('Turma inválida.');
}

$pdo = db();

$sql = "
    SELECT
        t.id,
        t.nome,
        t.serie,
        t.turno,
        t.ano_letivo
    FROM turma_membros tm
    INNER JOIN turmas t
        ON t.id = tm.turma_id
    WHERE tm.turma_id = :turma_id
      AND tm.aluno_id = :aluno_id
      AND tm.papel = 'representante'
      AND t.ativa = 1
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':turma_id' => $turmaId,
    ':aluno_id' => $usuarioId
]);

$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) {
    http_response_code(403);
    exit('Você não possui permissão para visualizar os avisos desta turma.');
}

$sql = "
    SELECT
        av.id,
        av.titulo,
        av.conteudo,
        av.fixado,
        av.criado_em,
        d.nome AS disciplina,
        a.nome AS autor
    FROM avisos av
    LEFT JOIN turma_disciplinas td
        ON td.id = av.turma_disciplina_id
    LEFT JOIN disciplinas d
        ON d.id = td.disciplina_id
    INNER JOIN alunos a
        ON a.id = av.autor_id
    WHERE av.turma_id = :turma_id
    ORDER BY
        av.fixado DESC,
        av.criado_em DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':turma_id' => $turmaId
]);

$avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Avisos - <?= e($turma['nome']) ?> - REPRE
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
    <link rel="stylesheet" href="avisosTurma.css">

</head>

<body>

<div class="pagina">

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

    <main class="conteudo">

        <a
            href="adminTurma.php?id=<?= (int) $turmaId ?>"
            class="voltar"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Voltar para gerenciamento
        </a>

        <section class="cabecalho">

            <div>

                <span class="tag">
                    AVISOS DA TURMA
                </span>

                <h1>
                    <?= e($turma['nome']) ?>
                </h1>

                <p>
                    <?= e($turma['serie'] ?? 'Turma') ?>
                    ·
                    <?= e(ucfirst($turma['turno'] ?? '')) ?>
                    ·
                    <?= e((string) $turma['ano_letivo']) ?>
                </p>

            </div>

            <a
                href="criarAviso.php?turma_id=<?= (int) $turmaId ?>"
                class="btn-novo"
            >
                <i class="fa-solid fa-plus"></i>
                Criar aviso
            </a>

        </section>

        <?php if (empty($avisos)): ?>

            <section class="vazio">

                <i class="fa-solid fa-bullhorn"></i>

                <h2>
                    Nenhum aviso criado
                </h2>

                <p>
                    Esta turma ainda não possui avisos publicados.
                </p>

            </section>

        <?php else: ?>

            <section class="lista">

                <?php foreach ($avisos as $aviso): ?>

                    <article
                        class="aviso <?= (int) $aviso['fixado'] === 1 ? 'fixado' : '' ?>"
                    >

                        <div class="aviso-topo">

                            <div>

                                <h2>
                                    <?= e($aviso['titulo']) ?>
                                </h2>

                                <div class="aviso-meta">

                                    <span>
                                        <i class="fa-solid fa-user"></i>
                                        <?= e($aviso['autor']) ?>
                                    </span>

                                    <?php if (!empty($aviso['disciplina'])): ?>

                                        <span>
                                            <i class="fa-solid fa-book"></i>
                                            <?= e($aviso['disciplina']) ?>
                                        </span>

                                    <?php else: ?>

                                        <span>
                                            <i class="fa-solid fa-users"></i>
                                            Aviso geral
                                        </span>

                                    <?php endif; ?>

                                    <span>
                                        <i class="fa-regular fa-clock"></i>
                                        <?= e(
                                            date(
                                                'd/m/Y H:i',
                                                strtotime($aviso['criado_em'])
                                            )
                                        ) ?>
                                    </span>

                                </div>

                            </div>

                            <?php if ((int) $aviso['fixado'] === 1): ?>

                                <span class="fixado-tag">
                                    <i class="fa-solid fa-thumbtack"></i>
                                    Fixado
                                </span>

                            <?php endif; ?>

                        </div>

                        <div class="aviso-conteudo">
                            <?= e($aviso['conteudo']) ?>
                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>

</html>