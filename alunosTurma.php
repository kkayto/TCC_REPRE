<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];
$turmaId = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

if (!$turmaId || $turmaId <= 0) {
    http_response_code(400);
    exit('Turma inválida.');
}

$pdo = db();

/* Verifica se o representante pertence à turma */
$sql = "
    SELECT t.id, t.nome, t.serie, t.turno, t.ano_letivo
    FROM turma_membros tm
    INNER JOIN turmas t ON t.id = tm.turma_id
    WHERE tm.turma_id = :turma_id
      AND tm.aluno_id = :usuario_id
      AND tm.papel = 'representante'
      AND t.ativa = 1
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':turma_id' => $turmaId,
    ':usuario_id' => $usuarioId
]);

$turma = $stmt->fetch();

if (!$turma) {
    http_response_code(403);
    exit('Você não possui permissão para visualizar esta turma.');
}

/* Campo de pesquisa */
$busca = trim((string) ($_GET['busca'] ?? ''));

/* Lista os alunos vinculados à turma */
$sql = "
    SELECT
        a.id,
        a.nome,
        a.email,
        a.matricula,
        tm.papel,
        tm.entrou_em
    FROM turma_membros tm
    INNER JOIN alunos a ON a.id = tm.aluno_id
    WHERE tm.turma_id = :turma_id
      AND a.ativo = 1
      AND a.status_conta = 'ativo'
      AND (
          tm.papel = 'aluno'
          OR tm.papel = 'representante'
      )
";

$params = [':turma_id' => $turmaId];

if ($busca !== '') {
    $sql .= "
        AND (
            a.nome LIKE :nome
            OR a.email LIKE :email
            OR a.matricula LIKE :matricula
        )
    ";

    $termo = '%' . $busca . '%';

    $params[':nome'] = $termo;
    $params[':email'] = $termo;
    $params[':matricula'] = $termo;
}

$sql .= "
    ORDER BY
        CASE WHEN tm.papel = 'representante' THEN 0 ELSE 1 END,
        a.nome ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alunos = $stmt->fetchAll();

$totalAlunos = count($alunos);

function formatarTurno(?string $turno): string
{
    return match ($turno) {
        'manha' => 'Manhã',
        'tarde' => 'Tarde',
        'noite' => 'Noite',
        default => 'Não informado'
    };
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Alunos da turma - REPRE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="alunosTurma.css">
</head>

<body>

<nav class="navbar">
    <a href="home.php" class="logo">REPRE</a>

    <div class="nav-links">
        <a href="home.php">
            <i class="fa-solid fa-house"></i>
            Início
        </a>

        <a href="adminRepresentante.php">
            <i class="fa-solid fa-people-group"></i>
            Representante
        </a>
    </div>

    <div class="nav-direita">
        <a href="perfil.php" class="perfil-link" aria-label="Perfil">
            <i class="fa-solid fa-user"></i>
        </a>

        <a href="logout.php" class="btn-sair">Sair</a>
    </div>
</nav>

<main class="conteudo">

    <a href="adminTurma.php?id=<?= (int) $turmaId ?>" class="voltar">
        <i class="fa-solid fa-arrow-left"></i>
        Voltar para a turma
    </a>

    <section class="cabecalho">
        <div>
            <span class="tag">GERENCIAMENTO DA TURMA</span>

            <h1>Alunos da turma</h1>

            <p class="subtitulo">
                <?= e($turma['nome']) ?>
                · <?= e($turma['serie'] ?? 'Turma') ?>
                · <?= e(formatarTurno($turma['turno'] ?? null)) ?>
                · <?= e((string) $turma['ano_letivo']) ?>
            </p>
        </div>

        <div class="resumo">
            <div class="resumo-icone">
                <i class="fa-solid fa-users"></i>
            </div>

            <div>
                <span><?= $busca !== '' ? 'Resultados' : 'Alunos cadastrados' ?></span>
                <strong><?= $totalAlunos ?></strong>
            </div>
        </div>
    </section>

    <section class="painel">

        <div class="painel-topo">
            <div>
                <h2>Lista de alunos</h2>
                <p>Pesquise por nome, e-mail ou matrícula.</p>
            </div>

            <form method="GET" action="alunosTurma.php" class="busca">
                <input
                    type="hidden"
                    name="turma_id"
                    value="<?= (int) $turmaId ?>"
                >

                <input
                    type="search"
                    name="busca"
                    class="campo-busca"
                    placeholder="Pesquisar aluno..."
                    value="<?= e($busca) ?>"
                >

                <button type="submit" class="btn-buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Buscar
                </button>
            </form>
        </div>

        <?php if (empty($alunos)): ?>

            <div class="vazio">
                <i class="fa-solid fa-user-group"></i>

                <h3>
                    <?= $busca !== '' ? 'Nenhum resultado encontrado' : 'Nenhum aluno cadastrado' ?>
                </h3>

                <p>
                    <?= $busca !== ''
                        ? 'Tente pesquisar usando outro nome, e-mail ou matrícula.'
                        : 'Esta turma ainda não possui alunos ativos vinculados.'
                    ?>
                </p>

                <?php if ($busca !== ''): ?>
                    <a
                        href="alunosTurma.php?turma_id=<?= (int) $turmaId ?>"
                        class="limpar"
                    >
                        Limpar pesquisa
                    </a>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Matrícula</th>
                            <th>E-mail</th>
                            <th>Função na turma</th>
                            <th>Vinculado em</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td>
                                    <div class="aluno-nome">
                                        <div class="avatar">
                                            <?= e(mb_strtoupper(
                                                mb_substr($aluno['nome'], 0, 1, 'UTF-8'),
                                                'UTF-8'
                                            )) ?>
                                        </div>

                                        <?= e($aluno['nome']) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= e($aluno['matricula'] ?: 'Não informada') ?>
                                </td>

                                <td class="email">
                                    <?= e($aluno['email']) ?>
                                </td>

                                <td>
                                    <span class="papel <?= $aluno['papel'] === 'aluno' ? 'aluno' : '' ?>">
                                        <?= $aluno['papel'] === 'representante'
                                            ? 'Representante'
                                            : 'Aluno'
                                        ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($aluno['entrou_em'])
                                        ? e(date('d/m/Y', strtotime($aluno['entrou_em'])))
                                        : '—'
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>