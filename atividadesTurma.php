<?php
declare(strict_types=1);
require_once __DIR__ . '/Settings/auth.php';
require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];
$turmaId = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

if (!$turmaId || $turmaId <= 0) {
    http_response_code(400);
    exit('Turma inválida.');
}

$pdo = db();
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

$busca = trim((string) ($_GET['busca'] ?? ''));
$statusFiltro = (string) ($_GET['status'] ?? '');

$statusPermitidos = [
    '',
    'rascunho',
    'publicada',
    'encerrada'
];

if (!in_array($statusFiltro, $statusPermitidos, true)) {
    $statusFiltro = '';
}

/* Busca as atividades da turma */
$sql = "
    SELECT
        a.id,
        a.titulo,
        a.descricao,
        a.tipo,
        a.status,
        a.data_limite,
        a.criada_em,
        a.pontos,
        d.nome AS disciplina
    FROM atividades a
    INNER JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id
    INNER JOIN disciplinas d
        ON d.id = td.disciplina_id
    WHERE td.turma_id = :turma_id
";

$params = [
    ':turma_id' => $turmaId
];

if ($busca !== '') {
    $sql .= "
        AND (
            a.titulo LIKE :busca_titulo
            OR a.descricao LIKE :busca_descricao
            OR d.nome LIKE :busca_disciplina
        )
    ";
    
    $termo = '%' . $busca . '%';
    $params[':busca_titulo'] = $termo;
    $params[':busca_descricao'] = $termo;
    $params[':busca_disciplina'] = $termo;
}

if ($statusFiltro !== '') {
    $sql .= " AND a.status = :status";
    $params[':status'] = $statusFiltro;
}

$sql .= "
    ORDER BY
        CASE a.status
            WHEN 'publicada' THEN 1
            WHEN 'rascunho' THEN 2
            WHEN 'encerrada' THEN 3
            ELSE 4
        END,
        a.data_limite IS NULL,
        a.data_limite ASC,
        a.criada_em DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$atividades = $stmt->fetchAll();

$sql = "
    SELECT
        COUNT(*) AS total,
        SUM(a.status = 'publicada') AS publicadas,
        SUM(a.status = 'rascunho') AS rascunhos,
        SUM(a.status = 'encerrada') AS encerradas
    FROM atividades a
    INNER JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id
    WHERE td.turma_id = :turma_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':turma_id' => $turmaId]);
$estatisticas = $stmt->fetch();

$totalAtividades = (int) ($estatisticas['total'] ?? 0);
$totalPublicadas = (int) ($estatisticas['publicadas'] ?? 0);
$totalRascunhos = (int) ($estatisticas['rascunhos'] ?? 0);
$totalEncerradas = (int) ($estatisticas['encerradas'] ?? 0);

function nomeTipo(string $tipo): string
{
    return match ($tipo) {
        'tarefa' => 'Tarefa',
        'trabalho' => 'Trabalho',
        'prova' => 'Prova',
        'quiz' => 'Quiz',
        'projeto' => 'Projeto',
        'leitura' => 'Leitura',
        default => ucfirst($tipo)
    };
}

function nomeStatus(string $status): string
{
    return match ($status) {
        'rascunho' => 'Rascunho',
        'publicada' => 'Publicada',
        'encerrada' => 'Encerrada',
        default => ucfirst($status)
    };
}

function formatarData(?string $data): string
{
    if (!$data) {
        return 'Sem prazo definido';
    }

    $timestamp = strtotime($data);

    if ($timestamp === false) {
        return 'Data indisponível';
    }

    return date('d/m/Y \à\s H:i', $timestamp);
}

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

    <title>Atividades da turma - REPRE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
    <link rel="stylesheet" href="atividadesTurma.css">
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

    <a
        href="adminTurma.php?id=<?= (int) $turmaId ?>"
        class="voltar"
    >
        <i class="fa-solid fa-arrow-left"></i>
        Voltar para a turma
    </a>

    <section class="cabecalho">
        <div>
            <span class="tag">GERENCIAMENTO DA TURMA</span>

            <h1>Atividades da turma</h1>

            <p class="subtitulo">
                <?= e($turma['nome']) ?>
                · <?= e($turma['serie'] ?? 'Turma') ?>
                · <?= e(formatarTurno($turma['turno'] ?? null)) ?>
                · <?= e((string) $turma['ano_letivo']) ?>
            </p>
        </div>

        <div class="resumo">
            <div class="resumo-icone">
                <i class="fa-solid fa-list-check"></i>
            </div>

            <div>
                <span>
                    <?= ($busca !== '' || $statusFiltro !== '')
                        ? 'Resultados'
                        : 'Total de atividades'
                    ?>
                </span>

                <strong><?= count($atividades) ?></strong>
            </div>
        </div>
    </section>

    <section class="estatisticas">

        <div class="estatistica">
            <div class="estatistica-icone icone-publicada">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div>
                <span>Publicadas</span>
                <strong><?= $totalPublicadas ?></strong>
            </div>
        </div>

        <div class="estatistica">
            <div class="estatistica-icone icone-rascunho">
                <i class="fa-solid fa-file-pen"></i>
            </div>

            <div>
                <span>Rascunhos</span>
                <strong><?= $totalRascunhos ?></strong>
            </div>
        </div>

        <div class="estatistica">
            <div class="estatistica-icone icone-encerrada">
                <i class="fa-solid fa-flag-checkered"></i>
            </div>

            <div>
                <span>Encerradas</span>
                <strong><?= $totalEncerradas ?></strong>
            </div>
        </div>

    </section>

    <section class="painel">

        <div class="painel-topo">
            <div>
                <h2>Lista de atividades</h2>
                <p>Pesquise atividades e filtre por situação.</p>
            </div>
        </div>

        <form method="GET" action="atividadesTurma.php" class="filtros">

            <input
                type="hidden"
                name="turma_id"
                value="<?= (int) $turmaId ?>"
            >

            <input
                type="search"
                name="busca"
                class="campo-busca"
                placeholder="Buscar título ou disciplina..."
                value="<?= e($busca) ?>"
            >

            <select name="status" class="campo-status">
                <option value="">Todos os status</option>

                <option
                    value="publicada"
                    <?= $statusFiltro === 'publicada' ? 'selected' : '' ?>
                >
                    Publicadas
                </option>

                <option
                    value="rascunho"
                    <?= $statusFiltro === 'rascunho' ? 'selected' : '' ?>
                >
                    Rascunhos
                </option>

                <option
                    value="encerrada"
                    <?= $statusFiltro === 'encerrada' ? 'selected' : '' ?>
                >
                    Encerradas
                </option>
            </select>

            <button type="submit" class="btn-buscar">
                <i class="fa-solid fa-magnifying-glass"></i>
                Filtrar
            </button>

            <?php if ($busca !== '' || $statusFiltro !== ''): ?>
                <a
                    href="atividadesTurma.php?turma_id=<?= (int) $turmaId ?>"
                    class="btn-limpar"
                >
                    Limpar filtros
                </a>
            <?php endif; ?>

        </form>

        <?php if (empty($atividades)): ?>

            <div class="vazio">
                <i class="fa-solid fa-clipboard-list"></i>

                <h3>
                    <?= ($busca !== '' || $statusFiltro !== '')
                        ? 'Nenhuma atividade encontrada'
                        : 'Nenhuma atividade cadastrada'
                    ?>
                </h3>

                <p>
                    <?= ($busca !== '' || $statusFiltro !== '')
                        ? 'Tente alterar a pesquisa ou os filtros selecionados.'
                        : 'Esta turma ainda não possui atividades cadastradas.'
                    ?>
                </p>
            </div>

        <?php else: ?>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Atividade</th>
                            <th>Disciplina</th>
                            <th>Tipo</th>
                            <th>Prazo de entrega</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($atividades as $atividade): ?>

                            <tr>
                                <td>
                                    <div class="atividade-titulo">
                                        <?= e($atividade['titulo']) ?>
                                    </div>

                                    <?php if (!empty($atividade['descricao'])): ?>
                                        <div class="atividade-descricao">
                                            <?= e(mb_strimwidth(
                                                $atividade['descricao'],
                                                0,
                                                100,
                                                '...',
                                                'UTF-8'
                                            )) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="disciplina">
                                        <i class="fa-solid fa-book-open"></i>
                                        <?= e($atividade['disciplina']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="tipo">
                                        <?= e(nomeTipo($atividade['tipo'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="prazo <?= empty($atividade['data_limite']) ? 'prazo-sem' : '' ?>">
                                        <?php if (!empty($atividade['data_limite'])): ?>
                                            <i class="fa-regular fa-calendar"></i>
                                        <?php endif; ?>

                                        <?= e(formatarData($atividade['data_limite'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status status-<?= e($atividade['status']) ?>">
                                        <?= e(nomeStatus($atividade['status'])) ?>
                                    </span>
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