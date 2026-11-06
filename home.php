<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';
require_active_account();
if (($_SESSION['user_tipo'] ?? '') === 'representante') {
    require_representante_com_turma();
}

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$userName = $_SESSION['user_name'] ?? 'Estudante';

if (!is_string($userName) || trim($userName) === '') {
    $userName = 'Estudante';
} else {
    $userName = trim($userName);
}

$atividadesPendentes = [];
$proximasAtividades = [];
$eventosCalendario = [];
$erroAtividades = false;
$erroEventos = false;

function homeIconeAtividade(?string $tipo): string
{
    $tipo = mb_strtolower(trim((string) $tipo), 'UTF-8');

    return match ($tipo) {
        'prova', 'avaliação', 'avaliacao', 'teste' => 'fa-file-pen',
        'trabalho', 'projeto' => 'fa-people-group',
        'questionário', 'questionario', 'quiz' => 'fa-list-check',
        'seminário', 'seminario', 'apresentação', 'apresentacao' => 'fa-person-chalkboard',
        'lista', 'exercício', 'exercicio', 'atividade' => 'fa-book-open',
        default => 'fa-book',
    };
}

function homeStatusAtividade(?string $dataLimite): string
{
    if ($dataLimite === null || trim($dataLimite) === '') {
        return 'Sem prazo';
    }

    $timestamp = strtotime($dataLimite);
    if ($timestamp === false) {
        return 'Pendente';
    }

    // Compara apenas a data, para não marcar como atrasada durante o próprio dia.
    return date('Y-m-d', $timestamp) < date('Y-m-d') ? 'Atrasada' : 'Pendente';
}

function homeClasseStatus(string $status): string
{
    return $status === 'Atrasada' ? 'atrasada' : 'pendente';
}

function homeFormatarData(?string $data): string
{
    if ($data === null || trim($data) === '') {
        return 'Sem prazo';
    }

    $timestamp = strtotime($data);
    return $timestamp === false ? 'Data não definida' : date('d/m/Y', $timestamp);
}


try {
    $pdo = db();

    $sqlAtividades = "
        SELECT DISTINCT
            a.id,
            a.titulo,
            a.descricao,
            a.tipo,
            a.data_limite,
            d.nome AS disciplina
        FROM atividades a
        INNER JOIN turma_disciplinas td
            ON td.id = a.turma_disciplina_id
        INNER JOIN disciplinas d
            ON d.id = td.disciplina_id
        INNER JOIN turma_membros tm
            ON tm.turma_id = td.turma_id
        WHERE tm.aluno_id = :aluno_id
          AND a.status = 'publicada'
          AND (
              a.data_limite IS NULL
              OR a.data_limite >= CURDATE()
          )
          AND NOT EXISTS (
              SELECT 1
              FROM entregas ent
              WHERE ent.atividade_id = a.id
                AND ent.aluno_id = :aluno_entrega
          )
        ORDER BY
            CASE WHEN a.data_limite IS NULL THEN 1 ELSE 0 END ASC,
            a.data_limite ASC,
            a.id DESC
    ";

    $stmt = $pdo->prepare($sqlAtividades);

    $stmt->execute([
        ':aluno_id' => $usuarioId,
        ':aluno_entrega' => $usuarioId,
    ]);

    $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Até quatro atividades no resumo principal.
    $atividadesPendentes = array_slice($atividades, 0, 4);

    // Até cinco atividades na lista lateral.
    $proximasAtividades = array_slice($atividades, 0, 5);

} catch (Throwable $erro) {
    error_log(
        'Erro ao carregar atividades da home.php: '
        . $erro->getMessage()
    );

    $erroAtividades = true;
}


// EVENTOS DO CALENDÁRIO

// IDENTIFICAR TURMA DO USUÁRIO
$turmaId = null;

$sqlTurma = "
    SELECT turma_id
    FROM turma_membros
    WHERE aluno_id = :usuario_id
    LIMIT 1
";

$stmtTurma = $pdo->prepare($sqlTurma);
$stmtTurma->execute([
    ':usuario_id' => $_SESSION['user_id']
]);

$turmaId = $stmtTurma->fetchColumn();

$turmaId = $turmaId ? (int) $turmaId : null;

try {
    if (!empty($turmaId)) {

        $sqlEventos = "
            SELECT
                ev.id,
                ev.titulo,
                ev.descricao,
                ev.tipo,
                ev.inicio,
                ev.fim
            FROM eventos ev
            WHERE ev.turma_id = :turma_id
            ORDER BY ev.inicio ASC
        ";

        $stmtEventos = $pdo->prepare($sqlEventos);

        $stmtEventos->execute([
            ':turma_id' => $turmaId
        ]);

        $eventosCalendario = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $erroEventos = true;
    error_log('Erro ao carregar eventos: ' . $e->getMessage());
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repre</title>
    <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon">
    <link rel="stylesheet" href="Styles/home.css">
    <link rel="stylesheet" href="Styles/root.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <div id="headerSet"></div>

    <main>
        <section class="column-1">
            <section class="welcome">
                <div class="welcome-title">
                    <h1>Bem-vindo(a), <?= e($userName) ?>!</h1>
                    <p>Confira suas atividades abaixo</p>
                </div>
                <div class="onlyPc-quote">“A alfabetização, portanto, é toda a pedagogia: aprender a ler é aprender a
                    dizer a sua palavra. E a sua palavra humana imita a palavra divina: é criadora.” - Paulo Freire
                </div>
            </section>

            <section class="wrapper-activities">
                <div class="activities-header">
                    <div class="activities-title">
                        <h2>Atividades Pendentes</h2>
                    </div>
                </div>

                <div id="cards-container">

                    <?php if ($erroAtividades): ?>

                        <p class="next-message next-error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            Não foi possível carregar as próximas atividades.
                        </p>

                    <?php elseif (empty($proximasAtividades)): ?>

                        <p class="next-message next-empty">
                            <i class="fa-regular fa-circle-check"></i>
                            Você não possui atividades pendentes no momento.
                        </p>

                    <?php else: ?>

                        <?php foreach ($proximasAtividades as $atividade): ?>

                            <?php
                            $titulo = $atividade['titulo'] ?? 'Atividade sem título';
                            $disciplina = $atividade['disciplina'] ?? 'Disciplina não informada';
                            $dataLimite = $atividade['data_limite'] ?? null;

                            $dataFormatada = 'Sem prazo definido';

                            if (!empty($dataLimite)) {
                                $timestamp = strtotime((string) $dataLimite);

                                if ($timestamp !== false) {
                                    $dataFormatada = date('d/m/Y', $timestamp);
                                }
                            }

                            $estaAtrasada = false;

                            if (!empty($dataLimite)) {
                                $timestamp = strtotime((string) $dataLimite);

                                if ($timestamp !== false) {
                                    $estaAtrasada =
                                        date('d-m-Y', $timestamp) < date('d-m-Y');
                                }
                            }
                            ?>

                            <article class="card-activities">
                                <div class="card-title-date">
                                    <div class="card-activities-title">
                                        <p>
                                            <?= e($disciplina) ?>
                                        </p>
                                        <?= e($titulo) ?>
                                    </div>

                                    <div class="card-activities-info">
                                        <?php $statusAtividade = 'Pendente'; ?>

                                        <span class="card-activities-status">
                                            <?= e($statusAtividade) ?>
                                        </span>

                                        <div class="card-activities-date">
                                            <i class="fa-regular fa-calendar"></i>

                                            <?php if (!empty($dataLimite)): ?>
                                                <time datetime="<?= e((string) $dataLimite) ?>">
                                                    <?= e($dataFormatada) ?>
                                                </time>
                                            <?php else: ?>
                                                <span><?= e($dataFormatada) ?></span>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a href="atividades.php" class="see-all-activities">
                    Ver todas as atividades
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </section>
        </section>

        <section class="calendar-section">
            <div class="next-events">
                <h2>Seus Próximos Eventos</h2>
                <div class="calendar">
                    <div class="calendar-header">
                        <button type="button" id="prev" aria-label="Mês anterior">❮</button>
                        <h2 id="month"></h2>
                        <button type="button" id="next" aria-label="Próximo mês">❯</button>
                    </div>
                    <div class="weekdays">
                        <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
                    </div>
                    <div id="calendarDays" class="days"></div>
                </div>
                <div id="eventInfo">Selecione uma data</div>
                <?php if ($erroEventos): ?>
                    <p class="home-message">Não foi possível carregar os eventos do calendário.</p>
                <?php endif; ?>
            </div>

            <div class="wrapper-next">
                <h2 class="title-next">Próximas atividades</h2>
                <div id="upcoming-activities-container">
                    <?php if ($erroAtividades): ?>
                        <p>Não foi possível carregar as próximas atividades.</p>
                    <?php elseif (empty($proximasAtividades)): ?>
                        <p>Nenhuma atividade pendente encontrada.</p>
                    <?php else: ?>
                        <?php foreach ($proximasAtividades as $atividade): ?>
                            <article class="card-next">
                                <h3><?= e($atividade['disciplina'] ?? 'Disciplina') ?></h3>
                                <h4><?= e($atividade['titulo'] ?? 'Atividade sem título') ?></h4>
                                <?php if (!empty($atividade['data_limite'])): ?>
                                    <span class="data-evento">
                                        <time datetime="<?= e($atividade['data_limite']) ?>">
                                                <?= e(homeFormatarData((string) $atividade['data_limite'])) ?>
                                        </time>
                                    </span>
                                <?php else: ?>
                                    <p>Sem data de entrega definida</p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        © Todos os direitos reservados à New World.
        <div class="quote">“A alfabetização, portanto, é toda a pedagogia: aprender a ler é aprender a dizer a sua
            palavra. E a sua palavra humana imita a palavra divina: é criadora.” - Paulo Freire</div>
    </footer>

   <script>
    window.repreEventos = <?= json_encode(
        $eventosCalendario,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ) ?>;
</script>
    <script src="Script/home.js"></script>
    <script src="Script/headerSet.js"></script>
</body>

</html>