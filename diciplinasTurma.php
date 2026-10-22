<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_role(['representante']);

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);

$turmaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$turmaId || $turmaId < 1) {
    http_response_code(400);
    exit('Turma inválida.');
}

try {
    $pdo = db();

    // Verifica se o usuário é representante da turma.
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
        exit('Você não possui permissão para acessar esta turma.');
    }

    // Busca as disciplinas vinculadas à turma.
    $sql = "
        SELECT
            td.id,
            d.nome AS disciplina,
            d.cor_hex AS cor,
            a.nome AS professor
        FROM turma_disciplinas td
        INNER JOIN disciplinas d
            ON d.id = td.disciplina_id
        LEFT JOIN alunos a
            ON a.id = td.professor_id
        WHERE td.turma_id = :turma_id
        ORDER BY d.nome ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':turma_id' => $turmaId
    ]);

    $disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $erro) {
    error_log(
        'Erro em disciplinasTurma.php: '
        . $erro->getMessage()
        . ' na linha '
        . $erro->getLine()
    );

    http_response_code(500);

    echo 'Erro ao carregar as disciplinas: '
        . htmlspecialchars(
            $erro->getMessage(),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    exit;
}
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
        Disciplinas - <?= e($turma['nome']) ?>
    </title>

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

    <link
        rel="stylesheet"
        href="adminTurma.css"
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


        <a
            href="adminTurma.php?id=<?= (int) $turmaId ?>"
            class="voltar"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Voltar para gerenciamento da turma

        </a>


        <div class="cabecalho-pagina">

            <div>

                <h1>
                    Disciplinas
                </h1>

                <p>

                    Disciplinas da turma
                    <strong>
                        <?= e($turma['nome']) ?>
                    </strong>

                </p>

            </div>


            <div class="contador-disciplinas">

                <i class="fa-solid fa-book"></i>

                <?= count($disciplinas) ?>

                <?= count($disciplinas) === 1
                    ? 'disciplina'
                    : 'disciplinas'
                ?>

            </div>

        </div>


        <?php if (empty($disciplinas)): ?>


            <div class="sem-disciplinas">

                <i class="fa-solid fa-book-open"></i>

                <h3>
                    Nenhuma disciplina encontrada
                </h3>

                <p>
                    Esta turma ainda não possui disciplinas cadastradas.
                </p>

            </div>


        <?php else: ?>


            <div class="disciplinas-container">


                <?php foreach ($disciplinas as $disciplina): ?>


                    <article class="disciplina-card">


                        <div class="disciplina-icone">

                            <i class="fa-solid fa-book-open"></i>

                        </div>


                        <h3>

                            <?= e(
                                $disciplina['disciplina']
                            ) ?>

                        </h3>


                        <div class="professor">

                            <i class="fa-solid fa-user-tie"></i>


                            <?php if (
                                !empty($disciplina['professor'])
                            ): ?>

                                <?= e(
                                    $disciplina['professor']
                                ) ?>

                            <?php else: ?>

                                Professor não informado

                            <?php endif; ?>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </main>

</div>

</body>

</html>