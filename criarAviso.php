<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';
$pdo = db();
require_login();
require_active_account();

$usuarioId = (int) $_SESSION['user_id'];
$tipoUsuario = $_SESSION['user_tipo'] ?? '';
$tiposPermitidos = ['representante', 'gremio', 'atletica'];

if (!in_array($tipoUsuario, $tiposPermitidos, true)) {
    http_response_code(403);
    exit('Você não possui permissão para criar avisos.');
}
$turmas = [];

if ($tipoUsuario === 'representante') {

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
        WHERE tm.aluno_id = :aluno_id
          AND tm.papel = 'representante'
          AND t.ativa = 1
        ORDER BY t.ano_letivo DESC, t.nome
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':aluno_id' => $usuarioId
    ]);

    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($tipoUsuario === 'gremio' || $tipoUsuario === 'atletica') {

    $turmas = [];
}

if (empty($turmas)) {
    http_response_code(403);
    exit('Você não possui nenhuma turma autorizada para publicar avisos.');
}

$turmaId = filter_input(
    INPUT_GET,
    'turma_id',
    FILTER_VALIDATE_INT
);

if (!$turmaId && isset($_POST['turma_id'])) {
    $turmaId = filter_var(
        $_POST['turma_id'],
        FILTER_VALIDATE_INT
    );
}

$turmaSelecionada = null;

foreach ($turmas as $turma) {
    if ((int) $turma['id'] === (int) $turmaId) {
        $turmaSelecionada = $turma;
        break;
    }
}

if (!$turmaSelecionada) {
    $turmaId = null;
}
$disciplinas = [];

if ($turmaId !== null) {

    $sql = "
        SELECT
            td.id,
            d.nome,
            d.cor_hex
        FROM turma_disciplinas td
        INNER JOIN disciplinas d
            ON d.id = td.disciplina_id
        WHERE td.turma_id = :turma_id
        ORDER BY d.nome
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':turma_id' => $turmaId
    ]);

    $disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $turmaIdPost = filter_input(
        INPUT_POST,
        'turma_id',
        FILTER_VALIDATE_INT
    );

    $disciplinaId = filter_input(
        INPUT_POST,
        'turma_disciplina_id',
        FILTER_VALIDATE_INT
    );

    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $conteudo = trim((string) ($_POST['conteudo'] ?? ''));
    $fixado = isset($_POST['fixado']) ? 1 : 0;
    $turmaAutorizada = false;

    foreach ($turmas as $turma) {
        if ((int) $turma['id'] === (int) $turmaIdPost) {
            $turmaAutorizada = true;
            break;
        }
    }

    if (!$turmaAutorizada) {
        $erro = 'Você não possui permissão para publicar avisos nesta turma.';
    } elseif ($titulo === '') {
        $erro = 'Informe o título do aviso.';
    } elseif (mb_strlen($titulo) > 150) {
        $erro = 'O título pode ter no máximo 150 caracteres.';
    } elseif ($conteudo === '') {
        $erro = 'Informe o conteúdo do aviso.';
    } elseif (mb_strlen($conteudo) > 10000) {
        $erro = 'O aviso é muito grande.';
    } else {

        if ($disciplinaId) {

            $sql = "
                SELECT id
                FROM turma_disciplinas
                WHERE id = :disciplina_id
                  AND turma_id = :turma_id
                LIMIT 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':disciplina_id' => $disciplinaId,
                ':turma_id' => $turmaIdPost
            ]);

            if (!$stmt->fetchColumn()) {
                $erro = 'A disciplina selecionada não pertence a esta turma.';
            }
        }

        if ($erro === '') {

            try {

                $sql = "
                    INSERT INTO avisos (
                        turma_id,
                        turma_disciplina_id,
                        autor_id,
                        titulo,
                        conteudo,
                        fixado
                    ) VALUES (
                        :turma_id,
                        :turma_disciplina_id,
                        :autor_id,
                        :titulo,
                        :conteudo,
                        :fixado
                    )
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':turma_id' => $turmaIdPost,
                    ':turma_disciplina_id' => $disciplinaId ?: null,
                    ':autor_id' => $usuarioId,
                    ':titulo' => $titulo,
                    ':conteudo' => $conteudo,
                    ':fixado' => $fixado
                ]);

                redirect(
                    'adminTurma.php?id=' .
                    (int) $turmaIdPost .
                    '&aviso=criado'
                );

            } catch (PDOException $e) {

                error_log(
                    'Erro ao criar aviso: ' . $e->getMessage()
                );

                $erro = 'Não foi possível criar o aviso. Tente novamente.';
            }
        }
    }
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

    <title>Criar aviso - REPRE</title>
    <link rel="stylesheet" href="criarAviso.css">
</head>

<body>

<div class="container">

    <div class="card">

        <h1>Criar aviso</h1>

        <p class="subtitulo">
            Publique um aviso para os alunos da turma.
        </p>

        <?php if ($erro !== ''): ?>

            <div class="erro">
                <?= e($erro) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <div class="campo">

                <label for="turma_id">
                    Turma
                </label>

                <select
                    name="turma_id"
                    id="turma_id"
                    required
                    onchange="trocarTurma(this.value)"
                >

                    <option value="">
                        Selecione uma turma
                    </option>

                    <?php foreach ($turmas as $turma): ?>

                        <option
                            value="<?= (int) $turma['id'] ?>"
                            <?= $turmaId === (int) $turma['id'] ? 'selected' : '' ?>
                        >
                            <?= e($turma['nome']) ?>
                            -
                            <?= e($turma['serie'] ?? '') ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="info">

                Você está publicando este aviso como
                <strong><?= e($tipoUsuario) ?></strong>.

            </div>

            <div class="campo">

                <label for="turma_disciplina_id">
                    Disciplina
                </label>

                <select
                    name="turma_disciplina_id"
                    id="turma_disciplina_id"
                >

                    <option value="">
                        Aviso geral da turma
                    </option>

                    <?php foreach ($disciplinas as $disciplina): ?>

                        <option
                            value="<?= (int) $disciplina['id'] ?>"
                        >
                            <?= e($disciplina['nome']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="campo">

                <label for="titulo">
                    Título
                </label>

                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    maxlength="150"
                    required
                    placeholder="Ex.: Reunião da turma"
                >

            </div>

            <div class="campo">

                <label for="conteudo">
                    Aviso
                </label>

                <textarea
                    id="conteudo"
                    name="conteudo"
                    maxlength="10000"
                    required
                    placeholder="Digite aqui o conteúdo do aviso..."
                ></textarea>

            </div>

            <div class="campo checkbox">

                <input
                    type="checkbox"
                    id="fixado"
                    name="fixado"
                    value="1"
                >

                <label for="fixado">
                    Fixar este aviso no topo
                </label>

            </div>

            <div class="botoes">

                <a
                    href="adminRepresentante.php?id=<?= (int) ($turmaId ?? 0) ?>"
                    class="btn btn-voltar"
                >
                    Voltar
                </a>

                <button
                    type="submit"
                    class="btn btn-salvar"
                >
                    Publicar aviso
                </button>

            </div>

        </form>

    </div>

</div>

<script>

function trocarTurma(turmaId) {

    if (!turmaId) {
        window.location.href = 'criarAviso.php';
        return;
    }

    window.location.href =
        'criarAviso.php?turma_id=' +
        encodeURIComponent(turmaId);
}

</script>

</body>

</html>
