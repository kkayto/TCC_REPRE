<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';
require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];
$pdo = db();

$turmaId = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

if (!$turmaId || $turmaId <= 0) {
    http_response_code(400);
    exit('Turma inválida.');
}

/* VERIFICAÇÃO DA TURMA */
$sql = "
    SELECT t.id, t.nome, t.serie, t.turno, t.ano_letivo
    FROM turma_membros tm
    INNER JOIN turmas t ON t.id = tm.turma_id
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
    exit('Você não possui permissão para administrar esta turma.');
}

/* DISCIPLINAS */
$stmt = $pdo->prepare("
    SELECT td.id, d.nome
    FROM turma_disciplinas td
    INNER JOIN disciplinas d
        ON d.id = td.disciplina_id
    WHERE td.turma_id = :turma_id
    ORDER BY d.nome
");
$stmt->execute([':turma_id' => $turmaId]);
$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$erro = '';
$sucesso = '';
$disciplinaSelecionada = '';
$atividadeEdicao = null;
$atividades = [];

$tiposPermitidos = [
    'tarefa', 'trabalho', 'prova',
    'quiz', 'projeto', 'leitura'
];

$statusPermitidos = ['rascunho', 'publicada'];

/* PROCESSAR FORMULÁRIOS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();

        $acao = (string) ($_POST['acao'] ?? 'salvar');

        /* EXCLUIR */
        if ($acao === 'excluir') {
            $atividadeId = filter_input(
                INPUT_POST,
                'atividade_id',
                FILTER_VALIDATE_INT
            );

            if (!$atividadeId || $atividadeId <= 0) {
                throw new RuntimeException('Atividade inválida.');
            }

            // Confirma que a atividade pertence à turma autorizada.
            $stmt = $pdo->prepare("
                SELECT a.id
                FROM atividades a
                INNER JOIN turma_disciplinas td
                    ON td.id = a.turma_disciplina_id
                WHERE a.id = :atividade_id
                  AND td.turma_id = :turma_id
                LIMIT 1
            ");
            $stmt->execute([
                ':atividade_id' => $atividadeId,
                ':turma_id' => $turmaId
            ]);

            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('Atividade não encontrada nesta turma.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                DELETE FROM atividades
                WHERE id = :atividade_id
            ");
            $stmt->execute([':atividade_id' => $atividadeId]);

            $pdo->commit();

            header('Location: criarAtividade.php?turma_id=' . $turmaId . '&excluida=1');
            exit;
        }

        /* CRIAR OU EDITAR */
        $atividadeId = filter_input(INPUT_POST, 'atividade_id', FILTER_VALIDATE_INT);
        $turmaDisciplinaId = filter_input(INPUT_POST, 'turma_disciplina_id', FILTER_VALIDATE_INT);
        $bimestre = trim((string) ($_POST['bimestre'] ?? '1º Bimestre'));
        $disciplinaSelecionada = $atividadeEdicao['turma_disciplina_id'] ?? '';

        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $tipo = (string) ($_POST['tipo'] ?? '');
        $status = (string) ($_POST['status'] ?? 'rascunho');
        $dataLimite = trim((string) ($_POST['data_limite'] ?? ''));
        
        //Não permite uma atividade ser criada antes do dia atual
        $data = DateTime::createFromFormat('!Y-m-d\TH:i', $dataLimite);
if (!$data) {
    $data = DateTime::createFromFormat('!Y-m-d H:i:s', $dataLimite);
}

if (!$data) {
    throw new RuntimeException('A data limite é inválida.');
}

// validação do dia
$dataAtual = new DateTime(); // Pega a data e hora exatas de agora

if ($data < $dataAtual) {
    throw new RuntimeException('A data limite da atividade não pode ser anterior ao dia e horário de hoje.');
}

$dataLimiteSql = $data->format('Y-m-d H:i:s');

        if (!$turmaDisciplinaId) {
            throw new RuntimeException('Selecione a disciplina.');
        }

        if ($titulo === '' || mb_strlen($titulo) > 150) {
            throw new RuntimeException('Informe um título válido de até 150 caracteres.');
        }

        if ($descricao === '') {
            throw new RuntimeException('Digite a descrição da atividade.');
        }

        if (!in_array($tipo, $tiposPermitidos, true)) {
            throw new RuntimeException('Tipo de atividade inválido.');
        }

        if (!in_array($status, $statusPermitidos, true)) {
            throw new RuntimeException('Status de publicação inválido.');
        }

        if ($dataLimite === '') {
            throw new RuntimeException('Informe a data limite.');
        }

        // A disciplina precisa pertencer à turma autorizada.
        $stmt = $pdo->prepare("
            SELECT id
            FROM turma_disciplinas
            WHERE id = :disciplina_id
              AND turma_id = :turma_id
            LIMIT 1
        ");
        $stmt->execute([
            ':disciplina_id' => $turmaDisciplinaId,
            ':turma_id' => $turmaId
        ]);

        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('A disciplina selecionada não pertence a esta turma.');
        }

        $data = DateTime::createFromFormat('!Y-m-d\TH:i', $dataLimite);
        if (!$data) {
            $data = DateTime::createFromFormat('!Y-m-d H:i:s', $dataLimite);
        }

        if (!$data) {
            throw new RuntimeException('A data limite é inválida.');
        }

        $dataLimiteSql = $data->format('Y-m-d H:i:s');

        if ($atividadeId) {
            // Atualizar
            $stmt = $pdo->prepare("
                UPDATE atividades
                SET turma_disciplina_id = :disciplina_id,
                    bimestre = :bimestre,
                    titulo = :titulo,
                    descricao = :descricao,
                    tipo = :tipo,
                    status = :status,
                    data_limite = :data_limite
                WHERE id = :atividade_id
            ");

            $stmt->execute([
                ':disciplina_id' => $turmaDisciplinaId,
                ':bimestre' => $bimestre,
                ':titulo' => $titulo,
                ':descricao' => $descricao,
                ':tipo' => $tipo,
                ':status' => $status,
                ':data_limite' => $dataLimiteSql,
                ':atividade_id' => $atividadeId
            ]);

            $resultado = 'editada';
        } else {
            // Inserir
            $stmt = $pdo->prepare("
                INSERT INTO atividades (
                    turma_disciplina_id, criador_id,
                    bimestre, titulo, descricao, tipo, status, data_limite
                )
                VALUES (
                    :disciplina_id, :criador_id,
                    :bimestre, :titulo, :descricao, :tipo, :status, :data_limite
                )
            ");

            $stmt->execute([
                ':disciplina_id' => $turmaDisciplinaId,
                ':criador_id' => $usuarioId,
                ':bimestre' => $bimestre,
                ':titulo' => $titulo,
                ':descricao' => $descricao,
                ':tipo' => $tipo,
                ':status' => $status,
                ':data_limite' => $dataLimiteSql
            ]);

            $resultado = 'criada';
        }

        header('Location: criarAtividade.php?turma_id=' . $turmaId . '&' . $resultado . '=1');
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Erro em criarAtividade.php: ' . $e->getMessage());
        $erro = $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Não foi possível concluir a operação. Verifique os dados e tente novamente.';
    }
}

/* MENSAGENS DE RETORNO */
if (isset($_GET['criada'])) {
    $sucesso = 'Atividade criada com sucesso.';
} elseif (isset($_GET['editada'])) {
    $sucesso = 'Atividade atualizada com sucesso.';
} elseif (isset($_GET['excluida'])) {
    $sucesso = 'Atividade excluída com sucesso.';
}

/* CARREGAR EDIÇÃO */
$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
if ($editarId && $editarId > 0) {
    $stmt = $pdo->prepare("
        SELECT a.*
        FROM atividades a
        INNER JOIN turma_disciplinas td
            ON td.id = a.turma_disciplina_id
        WHERE a.id = :atividade_id
          AND td.turma_id = :turma_id
        LIMIT 1
    ");
    $stmt->execute([
        ':atividade_id' => $editarId,
        ':turma_id' => $turmaId
    ]);

    $atividadeEdicao = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (!$atividadeEdicao) {
        http_response_code(404);
        exit('Atividade não encontrada nesta turma.');
    }
}

/* LISTAR ATIVIDADES */
$stmt = $pdo->prepare("
    SELECT
        a.id, a.titulo, a.descricao, a.tipo, a.status,
        a.data_limite, a.bimestre, a.turma_disciplina_id,
        d.nome AS disciplina
    FROM atividades a
    INNER JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id
    INNER JOIN disciplinas d
        ON d.id = td.disciplina_id
    WHERE td.turma_id = :turma_id
    ORDER BY a.id DESC
");
$stmt->execute([':turma_id' => $turmaId]);
$atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        Criar atividade - <?= e($turma['nome']) ?>
    </title>

    <link
        rel="stylesheet"
        href="criarAtividade.css"
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
    
    <link rel="stylesheet" href="Styles/root.css">

</head>

<body>

<div class="pagina">

 <div id="headerSet"></div>


  

<main class="conteudo">
    <div class="cabecalho-pagina">
        <h1>Gerenciar atividades</h1>
        <p>Cadastre, edite e acompanhe as atividades da turma.</p>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="mensagem erro">
            <?= e($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['criada'])): ?>
        <div class="mensagem sucesso">
            Atividade cadastrada com sucesso!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['editada'])): ?>
        <div class="mensagem sucesso">
            Atividade atualizada com sucesso!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['excluida'])): ?>
        <div class="mensagem sucesso">
            Atividade excluída com sucesso!
        </div>
    <?php endif; ?>

    <div class="painel-atividades">

        <!-- FORMULÁRIO -->
        <section class="painel formulario-atividade">
            <h2>
                <?= !empty($atividadeEdicao)
                    ? 'Editar atividade'
                    : 'Cadastrar atividade' ?>
            </h2>

<form method="POST" action="criarAtividade.php?turma_id=<?= (int) $turmaId ?>">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= e(csrf_token()) ?>"
    >

    <input type="hidden" name="_enviado" value="1">

    <input
        type="hidden"
        name="turma_id"
        value="<?= (int) $turmaId ?>"
    >

    <?php if (!empty($atividadeEdicao)): ?>
        <input
            type="hidden"
            name="atividade_id"
            value="<?= (int) $atividadeEdicao['id'] ?>"
        >
    <?php endif; ?>

                <div class="campo">
                    <label for="titulo">Título da atividade</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="150"
                        required
                        value="<?= e($_POST['titulo'] ?? $atividadeEdicao['titulo'] ?? '') ?>"
                    >
                </div>

                <div class="campo">
                    <label for="descricao">Descrição</label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="4"
                    ><?= e($_POST['descricao'] ?? $atividadeEdicao['descricao'] ?? '') ?></textarea>
                </div>

                
<div class="campo">
    <label for="turma_disciplina_id">Disciplina</label>

    <select
        id="turma_disciplina_id"
        name="turma_disciplina_id"
        required
    >
        <option value="">Selecione uma disciplina</option>

      <?php if (!empty($atividade['disciplina'])): ?>
    · <?= e($atividade['disciplina']) ?>
<?php endif; ?>

        <?php foreach ($disciplinas as $disciplina): ?>
            <option
                value="<?= (int) $disciplina['id'] ?>"
                <?= (string) $disciplinaSelecionada === (string) $disciplina['id']
                    ? 'selected'
                    : '' ?>
            >
                <?= e($disciplina['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

               <div class="campo">
    <label for="bimestre">Bimestre</label>

    <select
        id="bimestre"
        name="bimestre"
        required
    >
        <option value="">Selecione o bimestre</option>

        <?php
        $bimestreSelecionado =
            $_POST['bimestre']
            ?? $atividadeEdicao['bimestre']
            ?? '';

        $bimestresDisponiveis = [
            '1º Bimestre',
            '2º Bimestre',
            '3º Bimestre',
            '4º Bimestre'
        ];
        ?>

        <?php foreach ($bimestresDisponiveis as $b): ?>
            <option
                value="<?= e($b) ?>"
                <?= $bimestreSelecionado === $b ? 'selected' : '' ?>
            >
                <?= e($b) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                <div class="campo">
                    <label for="tipo">Tipo de atividade</label>
                    <select id="tipo" name="tipo" required>
                        <?php
                        $tipos = [
                            'tarefa' => 'Tarefa',
                            'trabalho' => 'Trabalho',
                            'prova' => 'Prova',
                            'quiz' => 'Quiz',
                            'projeto' => 'Projeto',
                            'leitura' => 'Leitura'
                        ];

                        $tipoSelecionado = $_POST['tipo'] ?? $atividadeEdicao['tipo'] ?? 'tarefa';
                        ?>

                        <?php foreach ($tipos as $valor => $rotulo): ?>
                            <option
                                value="<?= e($valor) ?>"
                                <?= $tipoSelecionado === $valor ? 'selected' : '' ?>
                            >
                                <?= e($rotulo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="data_limite">Prazo de entrega</label>
                    <input
                        type="datetime-local"
                        id="data_limite"
                        name="data_limite"
                        required
                        value="<?= e($_POST['data_limite'] ?? (!empty($atividadeEdicao['data_limite']) ? date('Y-m-d\TH:i', strtotime($atividadeEdicao['data_limite'])) : '')) ?>"
                    >
                </div>

                <div class="campo">
                    <label>
                        <input
                            type="checkbox"
                            name="em_grupo"
                            value="1"
                            <?= isset($_POST['em_grupo'])
                                ? 'checked'
                                : (!isset($_POST['_enviado']) && !empty($atividadeEdicao['em_grupo']) ? 'checked' : '') ?>
                        >
                        Atividade em grupo
                    </label>
                </div>

                <div class="campo">
                    <label>
                        <div class="campo">
    <label>
        <input
            type="checkbox"
            name="permite_atraso"
            value="1"
            <?= isset($_POST['_enviado'])
                ? (!empty($_POST['permite_atraso']) ? 'checked' : '')
                : (!empty($_POST['permite_atraso'])
                    || !empty($atividadeEdicao['permite_atraso'])
                    ? 'checked'
                    : '') ?>
        >
        Aceitar entregas atrasadas
    </label>
</div>

                <div class="campo">
                    <label for="status">Status</label>
                    <?php $statusSelecionado = $_POST['status'] ?? $atividadeEdicao['status'] ?? 'rascunho'; ?>

                    <select id="status" name="status" required>
                        <option value="rascunho" <?= $statusSelecionado === 'rascunho' ? 'selected' : '' ?>>
                            Rascunho
                        </option>
                        <option value="publicada" <?= $statusSelecionado === 'publicada' ? 'selected' : '' ?>>
                            Publicada
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn-principal">
                    <?= !empty($atividadeEdicao) ? 'Salvar alterações' : 'Cadastrar atividade' ?>
                </button>

                <?php if (!empty($atividadeEdicao)): ?>
                    <a href="criarAtividade.php?turma_id=<?= (int) $turmaId ?>" class="btn-cancelar">
                        Cancelar edição
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <!-- LISTA DE ATIVIDADES -->
        <section class="painel lista-atividades">
            <h2>Atividades cadastradas</h2>

            <?php if (empty($atividades)): ?>
                <p>Nenhuma atividade cadastrada para esta turma.</p>
            <?php else: ?>
                <?php foreach ($atividades as $atividade): ?>
                    <article class="atividade-item">
                        <div class="atividade-info">
                            <h3><?= e($atividade['titulo']) ?></h3>

                            <p>
                                <?= e($atividade['tipo']) ?>
                                <?php if (!empty($atividade['disciplina_nome'])): ?>
                                    · <?= e($atividade['disciplina_nome']) ?>
                                <?php endif; ?>
                            </p>

                            <p>
                                Prazo:
                                <?= !empty($atividade['data_limite'])
                                    ? e(date('d/m/Y H:i', strtotime($atividade['data_limite'])))
                                    : 'Não informado' ?>
                            </p>

                            <p>Status: <?= e($atividade['status']) ?></p>
                        </div>

                        <div class="atividade-acoes">
                            <a
                                class="btn-editar"
                                href="criarAtividade.php?turma_id=<?= (int) $turmaId ?>&editar=<?= (int) $atividade['id'] ?>"
                            >
                                Editar
                            </a>

                            <form method="POST" action="criarAtividade.php?turma_id=<?= (int) $turmaId ?>" onsubmit="return confirm('Deseja realmente excluir esta atividade?');">
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e(csrf_token()) ?>"
                                >
                                <input
                                    type="hidden"
                                    name="acao"
                                    value="excluir"
                                >
                                <input
                                    type="hidden"
                                    name="atividade_id"
                                    value="<?= (int) $atividade['id'] ?>"
                                >
                                <input
    type="hidden"
    name="turma_id"
    value="<?= (int) $turmaId ?>"
>

                                <button type="submit" class="btn-excluir">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </div>
</main>


</div>

    <script src="Script/headerSet.js"></script>
</body>

</html>