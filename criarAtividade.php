<?php
declare(strict_types=1);

require_once __DIR__ . '/Settings/auth.php';

require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];

$turmaId = filter_input(
    INPUT_GET,
    'turma_id',
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
    SELECT
        td.id,
        d.nome
    FROM turma_disciplinas td
    INNER JOIN disciplinas d
        ON d.id = td.disciplina_id
    WHERE td.turma_id = :turma_id
    ORDER BY d.nome
";

$stmt = db()->prepare($sql);

$stmt->execute([
    ':turma_id' => $turmaId
]);

$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT
        id,
        nome,
        ano,
        data_inicio,
        data_fim
    FROM periodos_letivos
    WHERE ano = :ano
    ORDER BY data_inicio
";

$stmt = db()->prepare($sql);

$stmt->execute([
    ':ano' => $turma['ano_letivo']
]);

$periodos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$erro = '';
$sucesso = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        verify_csrf();

        $turmaDisciplinaId = filter_input(
            INPUT_POST,
            'turma_disciplina_id',
            FILTER_VALIDATE_INT
        );

        $periodoId = filter_input(
            INPUT_POST,
            'periodo_id',
            FILTER_VALIDATE_INT
        );

        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $descricao = trim((string)($_POST['descricao'] ?? ''));
        $tipo = (string)($_POST['tipo'] ?? '');
        $status = (string)($_POST['status'] ?? 'rascunho');

        $emGrupo = isset($_POST['em_grupo']) ? 1 : 0;

        $pontos = filter_input(
            INPUT_POST,
            'pontos',
            FILTER_VALIDATE_FLOAT
        );

        $peso = filter_input(
            INPUT_POST,
            'peso',
            FILTER_VALIDATE_FLOAT
        );

        $permiteAtraso = isset($_POST['permite_atraso']) ? 1 : 0;

        $dataLimite = trim(
            (string)($_POST['data_limite'] ?? '')
        );


        if (!$turmaDisciplinaId) {
            throw new RuntimeException(
                'Selecione uma disciplina.'
            );
        }

        if (!$periodoId) {
            throw new RuntimeException(
                'Selecione um período letivo.'
            );
        }

        if ($titulo === '') {
            throw new RuntimeException(
                'Digite o título da atividade.'
            );
        }

        if (mb_strlen($titulo) > 150) {
            throw new RuntimeException(
                'O título da atividade é muito grande.'
            );
        }

        if ($descricao === '') {
            throw new RuntimeException(
                'Digite uma descrição para a atividade.'
            );
        }


        $tiposPermitidos = [
            'tarefa',
            'trabalho',
            'prova',
            'quiz',
            'projeto',
            'leitura'
        ];

        if (!in_array($tipo, $tiposPermitidos, true)) {
            throw new RuntimeException(
                'Tipo de atividade inválido.'
            );
        }


        $statusPermitidos = [
            'rascunho',
            'publicada'
        ];

        if (!in_array($status, $statusPermitidos, true)) {
            throw new RuntimeException(
                'Status inválido.'
            );
        }


        if ($pontos === false || $pontos === null || $pontos <= 0) {
            throw new RuntimeException(
                'Informe uma quantidade válida de pontos.'
            );
        }


        if ($peso === false || $peso === null || $peso <= 0) {
            throw new RuntimeException(
                'Informe um peso válido.'
            );
        }


        if ($dataLimite === '') {
            throw new RuntimeException(
                'Informe a data limite.'
            );
        }

$sql = "
    SELECT td.id
    FROM turma_disciplinas td
    WHERE td.id = :turma_disciplina_id
      AND td.turma_id = :turma_id
    LIMIT 1
";

        $stmt = db()->prepare($sql);

        $stmt->execute([
            ':turma_disciplina_id' => $turmaDisciplinaId,
            ':turma_id' => $turmaId
        ]);

        if (!$stmt->fetchColumn()) {
            throw new RuntimeException(
                'A disciplina selecionada não pertence a esta turma.'
            );
        }


        $sql = "
            SELECT id
            FROM periodos_letivos
            WHERE id = :periodo_id
              AND ano = :ano
            LIMIT 1
        ";

        $stmt = db()->prepare($sql);

        $stmt->execute([
            ':periodo_id' => $periodoId,
            ':ano' => $turma['ano_letivo']
        ]);

        if (!$stmt->fetchColumn()) {
            throw new RuntimeException(
                'O período letivo selecionado não pertence ao ano da turma.'
            );
        }

        $dataLimiteFormatada = str_replace(
            'T',
            ' ',
            $dataLimite
        );

        $data = DateTime::createFromFormat(
            'Y-m-d H:i',
            $dataLimiteFormatada
        );

        if (!$data) {
            throw new RuntimeException(
                'A data limite informada é inválida.'
            );
        }

        $dataLimiteSql = $data->format(
            'Y-m-d H:i:s'
        );

        $publicadaEm = null;

        if ($status === 'publicada') {
            $publicadaEm = date('Y-m-d H:i:s');
        }

        $sql = "
            INSERT INTO atividades (
                turma_disciplina_id,
                periodo_id,
                criador_id,
                titulo,
                descricao,
                tipo,
                status,
                em_grupo,
                pontos,
                peso,
                permite_atraso,
                publicada_em,
                data_limite
            )
            VALUES (
                :turma_disciplina_id,
                :periodo_id,
                :criador_id,
                :titulo,
                :descricao,
                :tipo,
                :status,
                :em_grupo,
                :pontos,
                :peso,
                :permite_atraso,
                :publicada_em,
                :data_limite
            )
        ";

        $stmt = db()->prepare($sql);
        $stmt->execute([
            ':turma_disciplina_id' => $turmaDisciplinaId,
            ':periodo_id' => $periodoId,
            ':criador_id' => $usuarioId,
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':tipo' => $tipo,
            ':status' => $status,
            ':em_grupo' => $emGrupo,
            ':pontos' => $pontos,
            ':peso' => $peso,
            ':permite_atraso' => $permiteAtraso,
            ':publicada_em' => $publicadaEm,
            ':data_limite' => $dataLimiteSql
        ]);


        $atividadeId = (int) db()->lastInsertId();
        $sql = "
            INSERT INTO log_auditoria (
                usuario_id,
                acao,
                entidade,
                entidade_id,
                ip
            )
            VALUES (
                :usuario_id,
                :acao,
                :entidade,
                :entidade_id,
                :ip
            )
        ";

        $stmt = db()->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':acao' => 'criar',
            ':entidade' => 'atividade',
            ':entidade_id' => $atividadeId,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        header(
            'Location: atividadeCriada.php?turma_id=' .
            $turmaId .
            '&criada=1'
        );
        exit;

    } catch (Throwable $e) {
        $erro = $e->getMessage();
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

            <a href="desempenho.php">
                <i class="fa-solid fa-chart-line"></i>
                Desempenho
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
            href="adminRepresentante.php?id=<?= (int) $turma['id'] ?>"
            class="voltar"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Voltar para a turma
        </a>


        <section class="secao">

            <div class="secao-header">

                <div>

                    <span class="tag">
                        NOVA ATIVIDADE
                    </span>

                    <h1>
                        Criar atividade
                    </h1>

                    <p>
                        <?= e($turma['nome']) ?>
                        ·
                        <?= e($turma['serie'] ?? '') ?>
                    </p>

                </div>

            </div>


            <?php if ($erro !== ''): ?>

                <div class="mensagem erro">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= e($erro) ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="formulario"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >


                <div class="campo">

                    <label for="titulo">
                        Título da atividade
                    </label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="150"
                        required
                        value="<?= e($_POST['titulo'] ?? '') ?>"
                        placeholder="Ex.: Lista de exercícios"
                    >

                </div>


                <div class="campo">

                    <label for="descricao">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="5"
                        required
                        placeholder="Explique o que os alunos devem fazer..."
                    ><?= e($_POST['descricao'] ?? '') ?></textarea>

                </div>


                <div class="form-grid">

                    <div class="campo">

                        <label for="turma_disciplina_id">
                            Disciplina
                        </label>

                        <select
                            id="turma_disciplina_id"
                            name="turma_disciplina_id"
                            required
                        >

                            <option value="">
                                Selecione a disciplina
                            </option>

                            <?php foreach ($disciplinas as $disciplina): ?>

                                <option
                                    value="<?= (int) $disciplina['id'] ?>"
                                    <?= ((int) ($_POST['turma_disciplina_id'] ?? 0) === (int) $disciplina['id']) ? 'selected' : '' ?>
                                >
                                    <?= e($disciplina['nome']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="periodo_id">
                            Período letivo
                        </label>

                        <select
                            id="periodo_id"
                            name="periodo_id"
                            required
                        >

                            <option value="">
                                Selecione o período
                            </option>

                            <?php foreach ($periodos as $periodo): ?>

                                <option
                                    value="<?= (int) $periodo['id'] ?>"
                                    <?= ((int) ($_POST['periodo_id'] ?? 0) === (int) $periodo['id']) ? 'selected' : '' ?>
                                >
                                    <?= e($periodo['nome']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="tipo">
                            Tipo
                        </label>

                        <select
                            id="tipo"
                            name="tipo"
                            required
                        >

                            <option value="">
                                Selecione
                            </option>

                            <option value="tarefa">
                                Tarefa
                            </option>

                            <option value="trabalho">
                                Trabalho
                            </option>

                            <option value="prova">
                                Prova
                            </option>

                            <option value="quiz">
                                Quiz
                            </option>

                            <option value="projeto">
                                Projeto
                            </option>

                            <option value="leitura">
                                Leitura
                            </option>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="data_limite">
                            Data limite
                        </label>

                        <input
                            type="datetime-local"
                            id="data_limite"
                            name="data_limite"
                            required
                            value="<?= e($_POST['data_limite'] ?? '') ?>"
                        >

                    </div>


                    <div class="campo">

                        <label for="pontos">
                            Pontos
                        </label>

                        <input
                            type="number"
                            id="pontos"
                            name="pontos"
                            min="0.01"
                            max="999.99"
                            step="0.01"
                            value="<?= e($_POST['pontos'] ?? '10') ?>"
                            required
                        >

                    </div>


                    <div class="campo">

                        <label for="peso">
                            Peso
                        </label>

                        <input
                            type="number"
                            id="peso"
                            name="peso"
                            min="0.01"
                            max="99.99"
                            step="0.01"
                            value="<?= e($_POST['peso'] ?? '1') ?>"
                            required
                        >

                    </div>

                </div>


                <div class="opcoes">

                    <label class="checkbox">

                        <input
                            type="checkbox"
                            name="em_grupo"
                            value="1"
                        >

                        <span>
                            Atividade em grupo
                        </span>

                    </label>


                    <label class="checkbox">

                        <input
                            type="checkbox"
                            name="permite_atraso"
                            value="1"
                            checked
                        >

                        <span>
                            Permitir entrega atrasada
                        </span>

                    </label>

                </div>


                <div class="campo">

                    <label for="status">
                        Publicação
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option value="rascunho">
                            Salvar como rascunho
                        </option>

                        <option value="publicada">
                            Publicar atividade
                        </option>

                    </select>

                </div>


                <div class="botoes">

                    <a
                        href="adminRepresentante.php?id=<?= (int) $turma['id'] ?>"
                        class="btn-secundario"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn-principal"
                    >
                        <i class="fa-solid fa-check"></i>
                        Criar atividade
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>

</html>