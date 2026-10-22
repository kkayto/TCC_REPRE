<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';
require_login();
require_once __DIR__ . '/atividades.php';

$alunoId = (int) ($_SESSION['user_id'] ?? 0);

if ($alunoId <= 0) {
    redirect('login.php');
    exit;
}

$erroAtividades = '';
$atividades = [];
$materias = [];

$totalPendentes = 0;
$totalAtrasadas = 0;
$totalConcluidas = 0;

try {
    $pdo = db();

    $sql = "
        SELECT
            a.id,
            a.titulo,
            a.descricao,
            a.tipo,
            a.data_limite,
            d.nome AS disciplina,
            d.cor_hex,
            e.status AS status_entrega,
            e.enviado_em
        FROM atividades a
        INNER JOIN turma_disciplinas td
            ON td.id = a.turma_disciplina_id
        INNER JOIN disciplinas d
            ON d.id = td.disciplina_id
        INNER JOIN turma_membros tm
            ON tm.turma_id = td.turma_id
           AND tm.aluno_id = :aluno_id
        LEFT JOIN entregas e
            ON e.atividade_id = a.id
           AND e.aluno_id = :aluno_id_entrega
        WHERE a.status = 'publicada'
        ORDER BY
            a.data_limite IS NULL ASC,
            a.data_limite ASC,
            a.id DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'aluno_id' => $alunoId,
        'aluno_id_entrega' => $alunoId
    ]);

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $atividade) {
        $statusEntrega = $atividade['status_entrega'] ?? 'pendente';
        $dataLimite = $atividade['data_limite'];

        if (in_array($statusEntrega, ['entregue', 'corrigida'], true)) {
            $status = 'concluida';
        } elseif (
            $statusEntrega === 'atrasada' ||
            (
                in_array($statusEntrega, ['pendente', 'devolvida'], true) &&
                $dataLimite !== null &&
                strtotime($dataLimite) < time()
            )
        ) {
            $status = 'atrasada';
        } else {
            $status = 'pendente';
        }

        $atividade['status_calculado'] = $status;

        if ($status === 'concluida') {
            $totalConcluidas++;
        } elseif ($status === 'atrasada') {
            $totalAtrasadas++;
        } else {
            $totalPendentes++;
        }

        $materias[$atividade['disciplina']] = $atividade['disciplina'];
        $atividades[] = $atividade;
    }

    natcasesort($materias);

} catch (Throwable $e) {
    error_log('[REPRE] Erro ao carregar atividades: ' . $e->getMessage());

    $erroAtividades = 'Não foi possível carregar suas atividades. Tente novamente mais tarde.';
}

function formatarDataAtividade(?string $data): string
{
    if (!$data) {
        return 'Sem prazo definido';
    }

    $timestamp = strtotime($data);

    return $timestamp === false
        ? 'Data indisponível'
        : date('d/m/Y', $timestamp);
}

function iconeAtividade(string $tipo): string
{
    $icones = [
        'tarefa' => 'fa-list-check',
        'trabalho' => 'fa-book',
        'prova' => 'fa-file-pen',
        'quiz' => 'fa-circle-question',
        'projeto' => 'fa-lightbulb',
        'leitura' => 'fa-book-open'
    ];

    return $icones[$tipo] ?? 'fa-book';
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPRE - Minhas Atividades</title>
    <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/Styles/atividades.css">
  <link rel="stylesheet" href="Styles/root.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap"
        rel="stylesheet"
    >
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
</head>

<body class="pagina-atividades">
    <div id="headerSet"></div>

        <main class="content">
            <section class="page-welcome">
                <div class="welcome-text">
                    <h1>
                        Minhas Atividades
                    </h1>
                    <p>
                        Organize, acompanhe e entregue suas tarefas.
                    </p>
                </div>

                <div class="citacao">
                    “Disciplina hoje,
                    constrói as conquistas de amanhã.”

                </div>
            </section>


            <section class="summary-section">
                <h2 class="section-title">
                    Resumo de suas Atividades
                </h2>

                
<div class="summary-inner">

    <div class="summary-card yellow">
        <span class="summary-card-num">
            <i class="fa-solid fa-hourglass-half"></i>
            <?= $totalPendentes ?>
        </span>
        <span class="summary-card-label">Pendentes</span>
    </div>

    <div class="summary-card red">
        <span class="summary-card-num">
            <i class="fa-solid fa-clock"></i>
            <?= $totalAtrasadas ?>
        </span>
        <span class="summary-card-label">Atrasadas</span>
    </div>

    <div class="summary-card green">
        <span class="summary-card-num">
            <i class="fa-regular fa-circle-check"></i>
            <?= $totalConcluidas ?>
        </span>
        <span class="summary-card-label">Concluídas</span>
    </div>
</div>

            </section>


            <section class="activities-section">
                <div class="activities-header">
                    <div class="activities-title">
                        <i class="fa-solid fa-list-check"></i>
                        <h2>
                            Lista de Atividades
                        </h2>
                    </div>

                    <span class="activities-counter">
                        7 tarefas
                    </span>
                </div>


                <div class="filters">
                    
<select id="filter-subjects">
    <option value="all">Todas as matérias</option>

    <?php foreach ($materias as $materia): ?>
        <option value="<?= e($materia) ?>">
            <?= e($materia) ?>
        </option>
    <?php endforeach; ?>
</select>

                    <select id="order-activities">
                        <option value="date">
                            Ordenar por: Data de entrega
                        </option>

                        <option value="subject">
                            Ordenar por: Matéria
                        </option>

                        <option value="status">
                            Ordenar por: Status
                        </option>
                    </select>

                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            id="search-activity"
                            type="search"
                            placeholder="Buscar atividade..."
                        >
                    </div>
                </div>


<div id="cards-container">

    <?php if ($erroAtividades !== ''): ?>

        <p role="alert"><?= e($erroAtividades) ?></p>

    <?php elseif (empty($atividades)): ?>

        <p>Você ainda não possui atividades publicadas para sua turma.</p>

    <?php else: ?>

        <?php foreach ($atividades as $atividade): ?>

            <?php
            $status = $atividade['status_calculado'];

            $classesStatus = [
                'pendente' => 'pending',
                'atrasada' => 'late',
                'concluida' => 'completed'
            ];

            $rotulosStatus = [
                'pendente' => 'Pendente',
                'atrasada' => 'Atrasada',
                'concluida' => 'Concluída'
            ];
            ?>

            <article
                class="activity-card"
                data-materia="<?= e($atividade['disciplina']) ?>"
                data-data="<?= e($atividade['data_limite'] ?? '') ?>"
                data-status="<?= e($rotulosStatus[$status]) ?>"
            >

                <div class="activity-icon purple">
                    <i class="fa-solid <?= e(iconeAtividade($atividade['tipo'])) ?>"></i>
                </div>

                <div class="activity-info">
                    <span class="activity-subject">
                        <?= e($atividade['disciplina']) ?>
                    </span>

                    <h3><?= e($atividade['titulo']) ?></h3>
                </div>

                <div class="activity-date">
                    <i class="fa-regular fa-calendar"></i>
                    <?= e(formatarDataAtividade($atividade['data_limite'])) ?>
                </div>

                <span class="activity-status <?= e($classesStatus[$status]) ?>">
                    <?= e($rotulosStatus[$status]) ?>
                </span>

                <button
                    type="button"
                    class="activity-more"
                    aria-label="Opções da atividade"
                    title="Opções da atividade"
                >
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>

            </article>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<p id="no-results" hidden>
    Nenhuma atividade encontrada.
</p>


            </section>


        </main>



        

        <footer class="footer">

            © Todos os direitos reservados à New World.

        </footer>


    </div>
    
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filtro = document.getElementById('filter-subjects');
    const ordenacao = document.getElementById('order-activities');
    const busca = document.getElementById('search-activity');
    const container = document.getElementById('cards-container');
    const mensagemVazia = document.getElementById('no-results');
    const contador = document.querySelector('.activities-counter');

    if (!filtro || !ordenacao || !busca || !container) {
        console.error('Não foi possível localizar os controles de atividades.');
        return;
    }

    // Captura todos os cartões existentes na página.
    const cartoes = Array.from(
        container.querySelectorAll('.activity-card')
    );

    // Padroniza texto: ignora maiúsculas, acentos e espaços extras.
    function normalizar(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toLocaleLowerCase('pt-BR');
    }

    function obterData(cartao) {
        const data = cartao.dataset.data || '';

        if (!data) {
            return '9999-12-31';
        }

        // Mantém datas SQL no formato AAAA-MM-DD.
        const correspondencia = data.match(/^(\d{4}-\d{2}-\d{2})/);

        if (correspondencia) {
            return correspondencia[1];
        }

        // Também aceita datas no formato DD/MM/AAAA.
        const brasileira = data.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

        if (brasileira) {
            return `${brasileira[3]}-${brasileira[2]}-${brasileira[1]}`;
        }

        return '9999-12-31';
    }

    function atualizarAtividades() {
        const materiaEscolhida = normalizar(filtro.value);
        const termo = normalizar(busca.value);

        // Ordena todos os cartões antes de exibi-los.
        const cartoesOrdenados = [...cartoes];

        cartoesOrdenados.sort(function (a, b) {
            if (ordenacao.value === 'subject') {
                return normalizar(a.dataset.materia).localeCompare(
                    normalizar(b.dataset.materia),
                    'pt-BR'
                );
            }

            if (ordenacao.value === 'status') {
                return normalizar(a.dataset.status).localeCompare(
                    normalizar(b.dataset.status),
                    'pt-BR'
                );
            }

            return obterData(a).localeCompare(obterData(b));
        });

        let visiveis = 0;

        cartoesOrdenados.forEach(function (cartao) {
            const materia = normalizar(cartao.dataset.materia);
            const textoCartao = normalizar(cartao.textContent);

            const correspondeMateria =
                materiaEscolhida === 'all' ||
                materia === materiaEscolhida;

            const correspondeBusca =
                termo === '' || textoCartao.includes(termo);

            const mostrar = correspondeMateria && correspondeBusca;

            // Usa também display para evitar conflitos com o CSS.
            cartao.hidden = !mostrar;
            cartao.style.display = mostrar ? '' : 'none';

            // Reorganiza os cartões conforme a ordenação selecionada.
            container.appendChild(cartao);

            if (mostrar) {
                visiveis++;
            }
        });

        if (mensagemVazia) {
            mensagemVazia.hidden = visiveis > 0;
            mensagemVazia.style.display =
                visiveis > 0 ? 'none' : 'block';

            mensagemVazia.textContent = cartoes.length === 0
                ? 'Nenhuma atividade cadastrada.'
                : 'Nenhuma atividade corresponde aos filtros selecionados.';
        }

        if (contador) {
            contador.textContent =
                visiveis + (visiveis === 1 ? ' tarefa' : ' tarefas');
        }
    }

    filtro.addEventListener('change', atualizarAtividades);
    ordenacao.addEventListener('change', atualizarAtividades);
    busca.addEventListener('input', atualizarAtividades);

    atualizarAtividades();
});
</script>

  <script src="Script/headerSet.js"></script>
</body>

</html>

