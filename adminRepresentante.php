<?php

declare(strict_types=1);

require_once __DIR__ . '/Settings/auth.php';
require_once 'Settings/permissoes.php';

require_role(['representante']);

$usuarioId = (int) $_SESSION['user_id'];

// ==================== TURMAS ====================
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

$stmt = db()->prepare($sql);
$stmt->execute([':aluno_id' => $usuarioId]);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==================== ATIVIDADES ====================
$sqlAtividades = "
    SELECT COUNT(*)
    FROM atividades a
    INNER JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id
    INNER JOIN turma_membros tm
        ON tm.turma_id = td.turma_id
    WHERE tm.aluno_id = :aluno_id
      AND tm.papel = 'representante'
";

$stmt = db()->prepare($sqlAtividades);
$stmt->execute([':aluno_id' => $usuarioId]);
$atividades = (int) $stmt->fetchColumn();


// ==================== AVISOS ====================
$sqlAvisos = "
    SELECT COUNT(*)
    FROM avisos a
    LEFT JOIN turma_disciplinas td
        ON td.id = a.turma_disciplina_id
    INNER JOIN turma_membros tm
        ON tm.turma_id = COALESCE(td.turma_id, a.turma_id)
    WHERE tm.aluno_id = :aluno_id
      AND tm.papel = 'representante'
";

$stmt = db()->prepare($sqlAvisos);
$stmt->execute([':aluno_id' => $usuarioId]);
$avisos = (int) $stmt->fetchColumn();


// ==================== EVENTOS ====================
$sqlEventos = "
    SELECT COUNT(*)
    FROM eventos e
    INNER JOIN turma_membros tm
        ON tm.turma_id = e.turma_id
    WHERE tm.aluno_id = :aluno_id
      AND tm.papel = 'representante'
";

$stmt = db()->prepare($sqlEventos);
$stmt->execute([':aluno_id' => $usuarioId]);
$eventos = (int) $stmt->fetchColumn();

?>

<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Repre</title>
    <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="Styles/adminRepresentante.css" />
    <link rel="stylesheet" href="Styles/root.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <div id="headerSet"></div>
    <!-- <a href="Functions/logout.php" class="btn-sair">
        Sair
    </a> -->
    <main>
        <!-- CABEÇALHO -->
        <section class="hello">
            <h1>
                Olá, <?= e($_SESSION['user_name'] ?? 'Representante') ?>!
            </h1>

            <p>
                Gerencie as informações e atividades das turmas que você representa.
            </p>
        </section>

        <!-- RESUMO -->
        <section id="summaryScroll">
            <a class="card-summary" href="activities.php">
                <div class="icon blue">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <span>Atividades</span>
                    <strong>
                        <?= $atividades ?>
                    </strong>
                </div>
            </a>
            <a class="card-summary" href="communities.php">
                <div class="icon green">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div>
                    <span>Avisos</span>
                    <strong>
                        <?= $avisos ?>
                    </strong>
                </div>
            </a>
            <a class="card-summary" href="home.php">
                <div class="icon purple">
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <div>
                    <span>Eventos</span>
                    <strong>
                        <?= $eventos ?>
                    </strong>
                </div>
            </a>
        </section>


        <!-- TURMAS -->
        <section class="sections">

            <?php if (empty($turmas)): ?>
                <div class="empty">
                    <div class="empty-icon">
                        <i class="fa-solid fa-users-slash"></i></i>
                    </div>
                    <h3>Nenhuma turma encontrada</h3>
                    <p>Você ainda não foi definido como representante de nenhuma turma.</p>
                </div>

            <?php else: ?>
                <div class="class">
                    <?php foreach ($turmas as $turma): ?>
                        <div class="class-top">
                            <!-- <div class="class-icon">
                                <i class="fa-solid fa-users"></i>
                            </div> -->
                            <div class="sections-header">
                                <h2>Minha turma</h2>
                            </div>
                            <span class="status">
                                Ativa
                            </span>

                        </div>


                        <h3>
                            <?= e($turma['nome']) ?>
                        </h3>


                        <p class="grade">
                            <?= e($turma['serie'] ?? 'Turma') ?>
                        </p>


                        <div class="class-info">

                            <span>

                                <i class="fa-regular fa-calendar"></i>

                                <?= e((string) $turma['ano_letivo']) ?>

                            </span>


                            <span>

                                <i class="fa-regular fa-clock"></i>

                                <?= e(ucfirst($turma['turno'])) ?>

                            </span>

                        </div>


                        <a
                            href="adminTurma.php?id=<?= (int) $turma['id'] ?>"
                            class="btn-manage">

                            Gerenciar turma

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>


                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>


        <!-- AÇÕES -->
        <section class="sections">

            <?php if (!empty($turmas)): ?>

                <?php
                $turmaRapida = $turmas[0];
                ?>
                <div class="sections-header">
                    <h2 class="actions">
                        Ações rápidas
                    </h2>

                    <!--<p>
                        Ferramentas disponíveis para o representante.
                </p>-->
                </div>

                <div class="actions-grid">


                    <!-- CRIAR ATIVIDADE -->
                    <a
                        href="criarAtividade.php?turma_id=<?= (int) $turmaRapida['id'] ?>"
                        class="action-card">

                        <div class="action-icon purple">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <div>
                            <h3>Criar atividade</h3>
                            <p>
                                Adicione uma atividade para a turma
                                <?= e($turmaRapida['nome']) ?>.
                            </p>

                        </div>


                        <i class="fa-solid fa-chevron-right seta"></i>

                    </a>


                    <!-- CRIAR AVISO -->
                    <a
                        href="criarAviso.php?turma_id=<?= (int) $turmaRapida['id'] ?>"
                        class="action-card">

                        <div class="action-icon blue">

                            <i class="fa-solid fa-bullhorn"></i>

                        </div>


                        <div>

                            <h3>
                                Criar aviso
                            </h3>

                            <p>
                                Publique um aviso para os alunos.
                            </p>

                        </div>


                        <i class="fa-solid fa-chevron-right seta"></i>

                    </a>


                    <!-- ADICIONAR EVENTO -->
                    <a
                       href="criarEventos.php?turma_id=<?= (int) $turmaRapida['id'] ?>"
                        class="action-card">

                        <div class="action-icon green">

                            <i class="fa-solid fa-calendar-plus"></i>

                        </div>


                        <div>

                            <h3>
                                Adicionar evento
                            </h3>

                            <p>
                                Adicione um evento ao calendário da turma.
                            </p>

                        </div>


                        <i class="fa-solid fa-chevron-right seta"></i>

                    </a>

                </div>


            <?php else: ?>

                <div class="empty">

                    <div class="empty-icon">

                        <i class="fa-solid fa-lock"></i>

                    </div>

                    <h3>
                        Nenhuma turma disponível
                    </h3>

                    <p>
                        As ações rápidas estarão disponíveis quando você estiver
                        vinculado a uma turma.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

    <footer class="footer">© Todos os direitos reservados à New World.
        <div class="quote">
            “A alfabetização, portanto, é toda a pedagogia: aprender a ler é aprender
            a dizer a sua palavra. E a sua palavra humana imita a palavra divina: é
            criadora.”- Paulo Freire
        </div>
    </footer>
    <script src="Script/adminRepresentante.js"></script>
    <script src="Script/headerSet.js"></script>
</body>

</html>