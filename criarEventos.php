<?php
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

if (!$turmaId || $turmaId <= 0) {
    http_response_code(400);
    exit('Turma inválida.');
}

$pdo = db();

$stmt = $pdo->prepare("
    SELECT t.id, t.nome, t.serie, t.turno, t.ano_letivo
    FROM turma_membros tm
    INNER JOIN turmas t ON t.id = tm.turma_id
    WHERE tm.turma_id = :turma_id
      AND tm.aluno_id = :usuario_id
      AND tm.papel = 'representante'
      AND t.ativa = 1
    LIMIT 1
");

$stmt->execute([
    ':turma_id' => $turmaId,
    ':usuario_id' => $usuarioId
]);

$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) {
    http_response_code(403);
    exit('Você não tem permissão para administrar esta turma.');
}

$eventoEdicao = null;

if (isset($_GET['editar'])) {
    $idEdicao = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

    if (!$idEdicao || $idEdicao <= 0) {
        exit('Evento inválido.');
    }

    $stmt = $pdo->prepare("
        SELECT id, titulo, descricao, tipo, inicio, fim
        FROM eventos
        WHERE id = :evento_id
          AND turma_id = :turma_id
        LIMIT 1
    ");

    $stmt->execute([
        ':evento_id' => $idEdicao,
        ':turma_id' => $turmaId
    ]);

    $eventoEdicao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$eventoEdicao) {
        http_response_code(404);
        exit('Evento não encontrado nesta turma.');
    }
}

$mensagem = '';
$erro = '';

$tipos = [
    'aula' => 'Aula',
    'prova' => 'Prova',
    'entrega' => 'Entrega',
    'reuniao' => 'Reunião',
    'feriado' => 'Feriado',
    'outro' => 'Outro'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    
    $acao = (string) ($_POST['acao'] ?? 'salvar');

    if ($acao === 'excluir') {
        $idExcluir = filter_var(
            $_POST['evento_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$idExcluir || $idExcluir <= 0) {
            exit('Evento inválido.');
        }

        $stmt = $pdo->prepare("
            DELETE FROM eventos
            WHERE id = :evento_id
              AND turma_id = :turma_id
        ");

        $stmt->execute([
            ':evento_id' => $idExcluir,
            ':turma_id' => $turmaId
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            exit('Evento não encontrado.');
        }

        header(
            'Location: criarEventos.php?turma_id='
            . $turmaId . '&sucesso=excluido'
        );
        exit;
    }

    $eventoId = filter_var(
        $_POST['evento_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$eventoId || $eventoId <= 0) {
        $eventoId = null;
    }


    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));
    $tipo = (string) ($_POST['tipo'] ?? 'outro');
    $inicio = trim((string) ($_POST['inicio'] ?? ''));
    $fim = trim((string) ($_POST['fim'] ?? ''));

    if ($titulo === '') {
        $erro = 'Informe o título do evento.';
    } elseif (mb_strlen($titulo) > 150) {
        $erro = 'O título deve ter no máximo 150 caracteres.';
    } elseif (!array_key_exists($tipo, $tipos)) {
        $erro = 'Selecione um tipo de evento válido.';
    } elseif ($inicio === '') {
        $erro = 'Informe a data e o horário de início.';
    } else {
        $inicioObj = DateTime::createFromFormat('Y-m-d\TH:i', $inicio);
        $fimObj = $fim !== ''
            ? DateTime::createFromFormat('Y-m-d\TH:i', $fim)
            : null;

        if (!$inicioObj || $inicioObj->format('Y-m-d\TH:i') !== $inicio) {
            $erro = 'A data de início é inválida.';
        } elseif ($fim !== '' && (
            !$fimObj ||
            $fimObj->format('Y-m-d\TH:i') !== $fim
        )) {
            $erro = 'A data de término é inválida.';
        } elseif ($fimObj && $fimObj < $inicioObj) {
            $erro = 'O término não pode ser anterior ao início.';
        } else {
           
if ($eventoId !== null) {
    $stmt = $pdo->prepare("
        UPDATE eventos
        SET titulo = :titulo,
            descricao = :descricao,
            tipo = :tipo,
            inicio = :inicio,
            fim = :fim
        WHERE id = :evento_id
          AND turma_id = :turma_id
    ");

    $stmt->execute([
        ':titulo' => $titulo,
        ':descricao' => $descricao !== '' ? $descricao : null,
        ':tipo' => $tipo,
        ':inicio' => $inicioObj->format('Y-m-d H:i:s'),
        ':fim' => $fimObj ? $fimObj->format('Y-m-d H:i:s') : null,
        ':evento_id' => $eventoId,
        ':turma_id' => $turmaId
    ]);

    header(
        'Location: criarEventos.php?turma_id='
        . $turmaId . '&sucesso=editado'
    );
    exit;
} else {
    $stmt = $pdo->prepare("
        INSERT INTO eventos
            (turma_id, atividade_id, criador_id, titulo,
             descricao, tipo, inicio, fim)
        VALUES
            (:turma_id, NULL, :criador_id, :titulo,
             :descricao, :tipo, :inicio, :fim)
    ");

    $stmt->execute([
        ':turma_id' => $turmaId,
        ':criador_id' => $usuarioId,
        ':titulo' => $titulo,
        ':descricao' => $descricao !== '' ? $descricao : null,
        ':tipo' => $tipo,
        ':inicio' => $inicioObj->format('Y-m-d H:i:s'),
        ':fim' => $fimObj ? $fimObj->format('Y-m-d H:i:s') : null
    ]);


    header(
        'Location: criarEventos.php?turma_id='
        . $turmaId . '&sucesso=1'
    );
    exit;
}
        }
    }
}



if (isset($_GET['sucesso'])) {
    $mensagem = match ($_GET['sucesso']) {
        '1' => 'Evento cadastrado com sucesso!',
        'editado' => 'Evento atualizado com sucesso!',
        'excluido' => 'Evento excluído com sucesso!',
        default => ''
    };
}

$stmt = $pdo->prepare("
    SELECT id, titulo, descricao, tipo, inicio, fim
    FROM eventos
    WHERE turma_id = :turma_id
    ORDER BY inicio ASC
");

$stmt->execute([':turma_id' => $turmaId]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function dataEvento(?string $data): string
{
    if (!$data) {
        return 'Não informado';
    }

    $obj = DateTime::createFromFormat('Y-m-d H:i:s', $data);

    return $obj ? $obj->format('d/m/Y \à\s H:i') : $data;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos da turma - REPRE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="criarEventos.css">
    </head>
<body>

    <nav class="navbar">
        <a class="logo" href="home.php">REPRE</a>
        <a class="voltar" href="adminRepresentante.php">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>
    </nav>

    <main class="container">
        <header class="cabecalho">
            <h1>Eventos da turma</h1>
            <p>Organize provas, reuniões, aulas e outras datas importantes.</p>
            <span class="turma-tag">
                <i class="fa-solid fa-users"></i>
                &nbsp; <?= e($turma['nome']) ?>
                <?php if (!empty($turma['ano_letivo'])): ?>
                    &nbsp; · &nbsp; <?= e((string) $turma['ano_letivo']) ?>
                <?php endif; ?>
            </span>
        </header>

        <div class="layout">
            <section class="painel">
                <div class="painel-titulo">
                    <div class="painel-icone">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h2>Adicionar evento</h2>
                        <p>Preencha os dados para incluir um evento no calendário.</p>
                    </div>
                </div>

                <?php if ($mensagem !== ''): ?>
                    <div class="alerta alerta-sucesso">
                        <i class="fa-solid fa-circle-check"></i>
                        <?= e($mensagem) ?>
                    </div>
                <?php endif; ?>

                <?php if ($erro !== ''): ?>
                    <div class="alerta alerta-erro">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= e($erro) ?>
                    </div>
                <?php endif; ?>

                <form
    method="POST"
    action="criarEventos.php?turma_id=<?= (int) $turmaId ?><?= $eventoEdicao ? '&editar=' . (int) $eventoEdicao['id'] : '' ?>"
>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <?php if ($eventoEdicao): ?>
        <input type="hidden" name="evento_id" value="<?= (int) $eventoEdicao['id'] ?>">
    <?php endif; ?>
                    <div class="campo">
                        <label for="titulo">Título do evento *</label>
                        <input
                            type="text"
                            id="titulo"
                            name="titulo"
                            maxlength="150"
                            placeholder="Ex.: Reunião da turma"
                            value="<?= e((string) ($_POST['titulo'] ?? $eventoEdicao['titulo'] ?? '')) ?>"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="tipo">Tipo de evento *</label>
                        <select id="tipo" name="tipo" required>
                            <?php foreach ($tipos as $valor => $nome): ?>
                                <option
                                    value="<?= e($valor) ?>"
                                    <?= (($_POST['tipo'] ?? $eventoEdicao['tipo'] ?? 'outro') === $valor) ? 'selected' : '' ?>
                                >
                                    <?= e($nome) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="descricao">Descrição</label>
                        <textarea
                            id="descricao"
                            name="descricao"
                            placeholder="Informações adicionais sobre o evento..."
                        ><?= e((string) ($_POST['descricao'] ?? $eventoEdicao['descricao'] ?? '')) ?></textarea>
                    </div>

                    <div class="duas-colunas">
                        <div class="campo">
                            <label for="inicio">Data e horário de início *</label>
                            <input
                                type="datetime-local"
                                id="inicio"
                                name="inicio"
                                value="<?= e((string) ($_POST['inicio'] ?? ($eventoEdicao ? (new DateTime($eventoEdicao['inicio']))->format('Y-m-d\TH:i') : ''))) ?>"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="fim">Data e horário de término</label>
                            <input
                                type="datetime-local"
                                id="fim"
                                name="fim"
                                value="<?= e((string) ($_POST['fim'] ?? ($eventoEdicao && $eventoEdicao['fim'] ? (new DateTime($eventoEdicao['fim']))->format('Y-m-d\TH:i') : ''))) ?>"
                            >
                        </div>
                    </div>

                  <button class="btn-principal" type="submit">
    <i class="fa-solid fa-floppy-disk"></i>
    <?= $eventoEdicao ? 'Salvar alterações' : 'Cadastrar evento' ?>
</button>
                </form>
            </section>

            
<section class="painel">
    <div class="painel-titulo">
        <div class="painel-icone">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div>
            <h2>Eventos cadastrados</h2>
            <p><?= count($eventos) ?> evento(s) nesta turma.</p>
        </div>
    </div>

    <?php if (empty($eventos)): ?>
        <div class="vazio">
            <i class="fa-regular fa-calendar-xmark"></i>
            <h3>Nenhum evento cadastrado</h3>
            <p>Os eventos criados aparecerão aqui.</p>
        </div>
    <?php else: ?>
        <div class="lista-eventos">
            <?php foreach ($eventos as $evento): ?>
                <?php
                $dataInicio = new DateTime($evento['inicio']);
                $nomeTipo = $tipos[$evento['tipo']] ?? 'Outro';
                ?>

                <article class="evento">
                    <div class="evento-data">
                        <strong><?= $dataInicio->format('d') ?></strong>
                        <span><?= e($dataInicio->format('M')) ?></span>
                    </div>

                    <div class="evento-conteudo">
                        <h3><?= e($evento['titulo']) ?></h3>
                        <span class="tipo"><?= e($nomeTipo) ?></span>

                        <p class="evento-horario">
                            <i class="fa-regular fa-clock"></i>
                            <?= e(dataEvento($evento['inicio'])) ?>

                            <?php if (!empty($evento['fim'])): ?>
                                <br>
                                <i class="fa-solid fa-flag-checkered"></i>
                                Término: <?= e(dataEvento($evento['fim'])) ?>
                            <?php endif; ?>
                        </p>

                        <?php if (!empty($evento['descricao'])): ?>
                            <p class="evento-descricao">
                                <?= nl2br(e($evento['descricao'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="acoes-evento">
                        <a
                            href="criarEventos.php?turma_id=<?= (int) $turmaId ?>&editar=<?= (int) $evento['id'] ?>"
                            class="btn-editar"
                        >
                            <i class="fa-solid fa-pen"></i>
                            Editar
                        </a>

                        <form
                            method="POST"
                            action="criarEventos.php?turma_id=<?= (int) $turmaId ?>"
                            onsubmit="return confirm('Tem certeza de que deseja excluir este evento?');"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e(csrf_token()) ?>"
                            >
                            <input type="hidden" name="acao" value="excluir">
                            <input
                                type="hidden"
                                name="evento_id"
                                value="<?= (int) $evento['id'] ?>"
                            >

                            <button type="submit" class="btn-excluir">
                                <i class="fa-solid fa-trash"></i>
                                Excluir
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

        </div>
    </main>

</body>
</html>