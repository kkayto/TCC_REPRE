<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_role(['aluno', 'representante']);

$pdo = db();
$usuarioId = (int) ($_SESSION['user_id'] ?? 0);

$atividadeId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$atividadeId || $atividadeId <= 0) {
    http_response_code(400);
    exit('Atividade inválida.');
}

/*
 * Busca os detalhes e verifica se o aluno
 * pertence à turma da atividade.
 */
$sql = "
    SELECT
        a.id,
        a.titulo,
        a.descricao,
        a.tipo,
        a.status,
        a.em_grupo,
        a.data_limite,
        a.bimestre,
        d.nome AS disciplina,
        t.nome AS turma,
        t.serie,
        t.turno,
        t.ano_letivo
    FROM atividades a
    INNER JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id
    INNER JOIN disciplinas d
        ON d.id = td.disciplina_id
    INNER JOIN turmas t
        ON t.id = td.turma_id
    INNER JOIN turma_membros tm
        ON tm.turma_id = t.id
    WHERE a.id = :atividade_id
      AND tm.aluno_id = :aluno_id
      AND a.status = 'publicada'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':atividade_id' => $atividadeId,
    ':aluno_id' => $usuarioId,
]);

$atividade = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$atividade) {
    http_response_code(404);
    exit('Atividade não encontrada ou indisponível para você.');
}

/* Formata o prazo */
$dataLimite = null;
$estaAtrasada = false;

if (!empty($atividade['data_limite'])) {
    try {
        $dataLimite = new DateTime(
            $atividade['data_limite']
        );

        $agora = new DateTime();

        $estaAtrasada = $dataLimite < $agora;
    } catch (Exception $e) {
        $dataLimite = null;
    }
}

function formatarDataDetalhe(?string $data): string
{
    if (empty($data)) {
        return 'Não informado';
    }

    $timestamp = strtotime($data);

    return $timestamp !== false
        ? date('d/m/Y', $timestamp)
        : 'Não informado';
}

// Verifica se o aluno já concluiu a atividade 
$stmtEntrega = $pdo->prepare("
    SELECT 1
    FROM entregas
    WHERE atividade_id = :atividade_id
      AND aluno_id = :aluno_id
    LIMIT 1
");

$stmtEntrega->execute([
    ':atividade_id' => $atividadeId,
    ':aluno_id' => $usuarioId
]);

$atividadeConcluida = (bool) $stmtEntrega->fetchColumn();

// Processa a conclusão 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['concluir'])) {

    verify_csrf();

    if (!$atividadeConcluida) {
        $stmtConcluir = $pdo->prepare("
            INSERT INTO entregas (atividade_id, aluno_id, enviado_em)
            VALUES (:atividade_id, :aluno_id, NOW())
        ");

        $stmtConcluir->execute([
            ':atividade_id' => $atividadeId,
            ':aluno_id' => $usuarioId
        ]);
    }

    redirect('detalhesAtividade.php?id=' . $atividadeId);
}

function formatarDataHoraDetalhe(?string $data): string
{
    if (empty($data)) {
        return 'Não definido';
    }

    $timestamp = strtotime($data);

    return $timestamp !== false
        ? date('d/m/Y \à\s H:i', $timestamp)
        : 'Não definido';
}

$turmaDescricao = trim(
    (string) ($atividade['serie'] ?? '') . ' ' .
    (string) ($atividade['turma'] ?? '')
);

if ($turmaDescricao === '') {
    $turmaDescricao = $atividade['turma'] ?? 'Turma';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($atividade['titulo']) ?> - REPRE
    </title>

    <link rel="stylesheet" href="detalhesAtividade.css">

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
    <link rel="stylesheet" href="Styles/root.css">
</head>

<body>
<div class="pagina">
    <div id="headerSet"></div>

    <main class="conteudo">

        <div class="cabecalho">
            <span class="etiqueta">
                <i class="fa-solid fa-book-open"></i>
                Detalhes da atividade
            </span>

            <h1><?= e($atividade['titulo']) ?></h1>

            <p class="subtitulo">
                <?= e($atividade['disciplina']) ?>
                <?php if ($turmaDescricao !== ''): ?>
                    · <?= e($turmaDescricao) ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="layout-detalhes">

            <section class="painel descricao-painel">
                <h2>
                    <i class="fa-regular fa-file-lines"></i>
                    Instruções da atividade
                </h2>

                <div class="descricao">
                    <?php if (trim((string) $atividade['descricao']) !== ''): ?>
                        <?= nl2br(e($atividade['descricao'])) ?>
                    <?php else: ?>
                        <p>O professor ou representante não adicionou uma descrição.</p>
                    <?php endif; ?>
                </div>

                <div class="aviso-prazo <?= $estaAtrasada ? 'atrasada' : 'no-prazo' ?>">
                    <i class="fa-regular fa-calendar"></i>

                    <div>
                        <strong>
                            <?= $estaAtrasada ? 'Prazo encerrado' : 'Prazo de entrega' ?>
                        </strong>

                        <p>
                            <?= e(formatarDataHoraDetalhe(
                                $atividade['data_limite']
                            )) ?>
                        </p>
                    </div>
                </div>
            </section>

            <aside class="painel informacoes-painel">
                <h2>Informações</h2>

                <div class="informacao">
                    <span>Tipo</span>
                    <strong><?= e(ucfirst($atividade['tipo'])) ?></strong>
                </div>

                <div class="informacao">
                    <span>Disciplina</span>
                    <strong><?= e($atividade['disciplina']) ?></strong>
                </div>

                <div class="informacao">
                    <span>Turma</span>
                    <strong><?= e($turmaDescricao) ?></strong>
                </div>

                <?php if (!empty($atividade['bimestre'])): ?>
                    <div class="informacao">
                        <span>Bimestre</span>
                        <strong><?= e((string) $atividade['bimestre']) ?></strong>
                    </div>
                <?php endif; ?>
            
                <div class="informacao">
                    <span>Formato</span>
                    <strong>
                        <?= !empty($atividade['em_grupo'])
                            ? 'Em grupo'
                            : 'Individual' ?>
                    </strong>
                </div>
            </aside>

        </div>

       <div class="rodape-acoes">

    <?php if ($atividadeConcluida): ?>

        <button class="btn-concluida" disabled>
            <i class="fa-solid fa-circle-check"></i>
            Atividade concluída
        </button>

    <?php else: ?>

        <form method="POST">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <button type="submit" name="concluir" class="btn-concluir">
                <i class="fa-solid fa-check"></i>
                Concluir atividade
            </button>
        </form>

    <?php endif; ?>

    <a href="atividades.php" class="btn-voltar">
        <i class="fa-solid fa-list-check"></i>
        Voltar à lista de atividades
    </a>

</div>
    </main>
</div>
    <script src="Script/headerSet.js"></script>
</body>
</html>