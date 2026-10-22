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

/* Verifica se o representante pertence à turma */
$stmt = $pdo->prepare("
    SELECT t.id, t.nome, t.ano_letivo
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
    exit('Você não tem permissão para acessar os eventos desta turma.');
}

/* Busca os eventos da turma */
$stmt = $pdo->prepare("
    SELECT id, titulo, descricao, tipo, inicio, fim
    FROM eventos
    WHERE turma_id = :turma_id
    ORDER BY inicio ASC
");

$stmt->execute([':turma_id' => $turmaId]);

$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tipos = [
    'aula' => 'Aula',
    'prova' => 'Prova',
    'entrega' => 'Entrega',
    'reuniao' => 'Reunião',
    'feriado' => 'Feriado',
    'outro' => 'Outro'
];

function escaparEvento(?string $valor): string
{
    return htmlspecialchars(
        $valor ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatarDataEvento(?string $data): string
{
    if (!$data) {
        return 'Não informado';
    }

    try {
        return (new DateTime($data))->format('d/m/Y \à\s H:i');
    } catch (Exception $e) {
        return 'Data indisponível';
    }
}

/* Separa eventos futuros e passados */
$agora = new DateTime();
$proximos = 0;
$passados = 0;

foreach ($eventos as $evento) {
    $dataEvento = new DateTime($evento['inicio']);

    if ($dataEvento >= $agora) {
        $proximos++;
    } else {
        $passados++;
    }
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
    <link rel="stylesheet" href="eventosTurma.css">
</head>

<body>

    <nav class="navbar">
        <a class="logo" href="home.php">REPRE</a>

        <a
            class="btn-voltar"
            href="adminTurma.php?id=<?= (int) $turmaId ?>"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Voltar à turma
        </a>
    </nav>

    <main class="container">

        <header class="cabecalho">
            <div class="cabecalho-icone">
                <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div class="cabecalho-texto">
                <span class="subtitulo">ORGANIZAÇÃO DA TURMA</span>

                <h1>Eventos</h1>

                <p>
                    Consulte provas, entregas, reuniões, aulas e outras
                    datas importantes da sua turma.
                </p>

                <span class="turma-tag">
                    <i class="fa-solid fa-users"></i>
                    <?= escaparEvento($turma['nome']) ?>

                    <?php if (!empty($turma['ano_letivo'])): ?>
                        · <?= escaparEvento((string) $turma['ano_letivo']) ?>
                    <?php endif; ?>
                </span>
            </div>
        </header>

        <section class="resumo">

            <article class="resumo-card">
                <div class="resumo-icone roxo">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

                <div>
                    <p>Total de eventos</p>
                    <h2><?= count($eventos) ?></h2>
                </div>
            </article>

            <article class="resumo-card">
                <div class="resumo-icone verde">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>
                    <p>Próximos eventos</p>
                    <h2><?= $proximos ?></h2>
                </div>
            </article>

            <article class="resumo-card">
                <div class="resumo-icone cinza">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>

                <div>
                    <p>Eventos anteriores</p>
                    <h2><?= $passados ?></h2>
                </div>
            </article>

        </section>

        <section class="painel">

            <div class="painel-cabecalho">
                <div>
                    <h2>Agenda da turma</h2>
                    <p>
                        <?= count($eventos) ?>
                        evento(s) encontrado(s).
                    </p>
                </div>

                <div class="agenda-icone">
                    <i class="fa-solid fa-list-ul"></i>
                </div>
            </div>

            <?php if (empty($eventos)): ?>

                <div class="estado-vazio">
                    <div class="vazio-icone">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>

                    <h3>Nenhum evento cadastrado</h3>

                    <p>
                        Quando forem cadastrados eventos para esta turma,
                        eles aparecerão aqui.
                    </p>
                </div>

            <?php else: ?>

                <div class="lista-eventos">

                    <?php foreach ($eventos as $evento): ?>

                        <?php
                        $dataInicio = new DateTime($evento['inicio']);
                        $nomeTipo = $tipos[$evento['tipo']] ?? 'Outro';

                        $dataPassada = $dataInicio < $agora;
                        ?>

                        <article class="evento">

                            <div class="evento-data">
                                <strong>
                                    <?= $dataInicio->format('d') ?>
                                </strong>

                                <span>
                                    <?= escaparEvento(
                                        $dataInicio->format('M')
                                    ) ?>
                                </span>
                            </div>

                            <div class="evento-conteudo">

                                <div class="evento-titulo-linha">
                                    <h3>
                                        <?= escaparEvento($evento['titulo']) ?>
                                    </h3>

                                    <span class="tipo tipo-<?= escaparEvento($evento['tipo']) ?>">
                                        <?= escaparEvento($nomeTipo) ?>
                                    </span>
                                </div>

                                <p class="evento-horario">
                                    <i class="fa-regular fa-clock"></i>
                                    <?= escaparEvento(
                                        formatarDataEvento($evento['inicio'])
                                    ) ?>
                                </p>

                                <?php if (!empty($evento['fim'])): ?>
                                    <p class="evento-horario">
                                        <i class="fa-solid fa-flag-checkered"></i>
                                        Término:
                                        <?= escaparEvento(
                                            formatarDataEvento($evento['fim'])
                                        ) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($evento['descricao'])): ?>
                                    <p class="evento-descricao">
                                        <?= nl2br(
                                            escaparEvento($evento['descricao'])
                                        ) ?>
                                    </p>
                                <?php endif; ?>

                                <span class="status-evento <?= $dataPassada ? 'passado' : 'futuro' ?>">
                                    <i class="fa-solid <?= $dataPassada ? 'fa-check-circle' : 'fa-hourglass-half' ?>"></i>

                                    <?= $dataPassada ? 'Data anterior' : 'Evento futuro' ?>
                                </span>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </main>

</body>
</html>
