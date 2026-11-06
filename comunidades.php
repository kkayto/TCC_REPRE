<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_login();

$pdo = db();
$usuarioId = (int) ($_SESSION['user_id'] ?? 0);

$turmaId = null;
$publicacoes = [];
$materiais = [];
$erroCarregamento = false;

$formatarData = static function ($data): string {
    if (empty($data)) {
        return '';
    }

    $timestamp = strtotime((string) $data);

    return $timestamp !== false
        ? date('d/m/Y H:i', $timestamp)
        : '';
};

try {
    // Identifica a turma do usuário.
    $stmtTurma = $pdo->prepare("
        SELECT turma_id
        FROM turma_membros
        WHERE aluno_id = :usuario_id
        LIMIT 1
    ");

    $stmtTurma->execute([
        ':usuario_id' => $usuarioId
    ]);

    $resultadoTurma = $stmtTurma->fetchColumn();

    if ($resultadoTurma !== false) {
        $turmaId = (int) $resultadoTurma;


        $sqlMateriais = "
            SELECT
                m.*,
                a.nome AS autor_nome
            FROM materiais_estudos m
            LEFT JOIN alunos a
                ON a.id = m.publicado_por
            WHERE m.turma_id = :turma_id
            ORDER BY m.criado_em DESC
        ";

        $stmtMateriais = $pdo->prepare($sqlMateriais);
        $stmtMateriais->execute([
            ':turma_id' => $turmaId
        ]);

        $materiais = $stmtMateriais->fetchAll(PDO::FETCH_ASSOC);


        $sqlEventos = "
            SELECT
                ev.id,
                ev.titulo,
                ev.descricao AS conteudo,
                ev.tipo,
                ev.inicio AS data,
                ev.inicio AS inicio,
                ev.fim,
                a.nome AS autor,
                'evento' AS origem,
                NULL AS arquivo_nome,
                NULL AS arquivo_caminho,
                0 AS fixado
            FROM eventos ev
            LEFT JOIN alunos a
                ON a.id = ev.criador_id
            WHERE ev.turma_id = :turma_evento

            UNION ALL

            SELECT
                c.id,
                c.titulo,
                c.conteudo,
                'Comunicado' AS tipo,
                c.criado_em AS data,
                NULL AS inicio,
                NULL AS fim,
                a.nome AS autor,
                'comunicado' AS origem,
                c.arquivo_nome,
                c.arquivo_caminho,
                c.fixado
            FROM comunicados c
            LEFT JOIN alunos a
                ON a.id = c.autor_id
            WHERE c.turma_id = :turma_comunicado
              AND c.canal = 'Eventos'

            ORDER BY data DESC
        ";

        $stmtEventos = $pdo->prepare($sqlEventos);
        $stmtEventos->execute([
            ':turma_evento' => $turmaId,
            ':turma_comunicado' => $turmaId
        ]);

        $publicacoes = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Throwable $e) {
    $erroCarregamento = true;

    echo '<div style="background:#ffe5e5;color:#900;padding:15px;margin:15px;">';
    echo '<strong>Erro encontrado:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '<br><br><strong>Arquivo:</strong> ';
    echo htmlspecialchars($e->getFile());
    echo '<br><strong>Linha:</strong> ';
    echo $e->getLine();
    echo '</div>';
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comunidades | REPRE</title>

    <link rel="stylesheet" href="/Styles/comunidades.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
</head>

<body>

<div class="layout">

    <div id="headerSet"></div>

    <div class="pagina-canais">

        <!-- MENU LATERAL -->
        <aside class="canais-sidebar">

            <div class="canais-titulo">

                <div class="icone-canais">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </div>

                <div>
                    <h2>Canais</h2>
                    <p>Fique por dentro dos recados da escola.</p>
                </div>

            </div>

            <div class="canal ativo" data-canal="eventos">

                <div class="canal-icone gremio-icone">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>

                <div class="canal-texto">
                    <h3>Avisos</h3>
                    <p>Comunicados e avisos da turma.</p>
                </div>

                <i class="fa-solid fa-chevron-right canal-seta"></i>

            </div>

            <div class="canal" data-canal="arquivo">

                <div class="canal-icone gremio-icone">
                    <i class="fa-solid fa-folder-open"></i>
                </div>

                <div class="canal-texto">
                    <h3>Materiais de aula</h3>
                    <p>Materiais e arquivos das aulas.</p>
                </div>

                <i class="fa-solid fa-chevron-right canal-seta"></i>

            </div>

            <div class="aviso-canais">
                <i class="fa-solid fa-eye"></i>

                <p>
                    Este espaço é destinado à visualização
                    dos comunicados e materiais da turma.
                </p>
            </div>

        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="conteudo-canais">
            <section class="conteudo-canal ativo" id="canal-eventos">
                <section class="cabecalho-gremio">
                    <div class="canal-icone gremio-icone">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <div class="gremio-informacoes">
                        <div class="gremio-titulo">
                            <h1>Avisos</h1>
                            <span class="verificado">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </div>
                        <p>Comunicados, avisos da turma.</p>
                    </div>
                    <div class="visualizacao">
                        <i class="fa-solid fa-eye"></i>
                        <span>Apenas visualização</span>
                    </div>
                </section>

                <div class="lista-publicacoes">
                    <?php if ($erroCarregamento): ?>
                        <p>Não foi possível carregar as publicações.</p>
                    <?php elseif ($turmaId === null): ?>
                        <p>Você ainda não está vinculado a uma turma.</p>
                    <?php elseif (empty($publicacoes)): ?>
                        <p>Ainda não existem comunicados ou eventos publicados.</p>
                    <?php else: ?>

                        <?php foreach ($publicacoes as $publicacao): ?>
                            <article class="publicacao">
                                <div class="publicacao-icone gremio-icone">
                                    <?php if ($publicacao['origem'] === 'evento'): ?>
                                        <i class="fa-solid fa-calendar-days"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-bullhorn"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="publicacao-conteudo">
                                    <div class="publicacao-topo">
                                        <strong>
                                            <?= e((string) $publicacao['autor']) ?>
                                        </strong>
                                        <span>
                                            <?= e($formatarData($publicacao['data'])) ?>
                                        </span>
                                    </div>
                                    <h2>
                                        <?= e((string) $publicacao['titulo']) ?>
                                        <?php if ($publicacao['fixado']): ?>
                                            <i class="fa-solid fa-thumbtack"
                                               title="Publicação fixada"></i>
                                        <?php endif; ?>
                                    </h2>
                                    <p>
                                        <?= nl2br(e((string) ($publicacao['conteudo'] ?? ''))) ?>
                                    </p>
                                    <?php if ($publicacao['origem'] === 'evento'): ?>

                                        <div class="publicacao-final">
                                            <p>
                                                <i class="fa-solid fa-clock"></i>
                                                <strong>Início:</strong>
                                                <?= e($formatarData($publicacao['inicio'])) ?>
                                            </p>

                                            <?php if (!empty($publicacao['fim'])): ?>
                                                <p>
                                                    <i class="fa-solid fa-calendar-check"></i>
                                                    <strong>Fim:</strong>
                                                    <?= e($formatarData($publicacao['fim'])) ?>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($publicacao['tipo'])): ?>
                                                <p>
                                                    <i class="fa-solid fa-tag"></i>
                                                    <?= e((string) $publicacao['tipo']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif (!empty($publicacao['arquivo_caminho'])): ?>
                                        <div class="publicacao-final">
                                            <a
                                                href="<?= e((string) $publicacao['arquivo_caminho']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <i class="fa-solid fa-paperclip"></i>
                                                <?= e((string) ($publicacao['arquivo_nome'] ?? 'Visualizar anexo')) ?>
                                            </a>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>

            </section>

            <section class="conteudo-canal" id="canal-arquivo">

                <section class="cabecalho-gremio">

                    <div class="canal-icone gremio-icone">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>

                    <div class="gremio-informacoes">

                        <div class="gremio-titulo">
                            <h1>Materiais de aula</h1>
                        </div>

                        <p>Materiais e arquivos disponibilizados para a turma.</p>

                    </div>

                    <div class="visualizacao">
                        <i class="fa-solid fa-eye"></i>
                        <span>Apenas visualização</span>
                    </div>

                </section>

                <div class="lista-publicacoes">

                    <?php if ($erroCarregamento): ?>

                        <p>Não foi possível carregar os materiais.</p>

                    <?php elseif ($turmaId === null): ?>

                        <p>Você ainda não está vinculado a uma turma.</p>

                    <?php elseif (empty($materiais)): ?>

                        <p>Nenhum material foi disponibilizado ainda.</p>

                    <?php else: ?>

                       <?php foreach ($materiais as $material): ?>

                            <article class="publicacao">

                                <div class="publicacao-icone gremio-icone">
                                    <i class="fa-solid fa-file-lines"></i>
                                </div>

                                <div class="publicacao-conteudo">

                                    <div class="publicacao-topo">

                                        <strong>
                                            <?= e((string) ($material['autor_nome'] ?? 'Escola')) ?>
                                        </strong>

                                        <span>
                                            <?= e($formatarData($material['criado_em'])) ?>
                                        </span>

                                    </div>

                                    <h2>
                                        <i class="fa-solid fa-book"></i>
                                        <?= e((string) $material['titulo']) ?>
                                    </h2>

                                    <p>
                                        <?= nl2br(e((string) ($material['descricao'] ?? ''))) ?>
                                    </p>

                                    <div class="publicacao-final">

                                        <?php if (!empty($material['arquivo_caminho'])): ?>

                                            <a
                                                href="<?= e((string) $material['arquivo_caminho']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <i class="fa-solid fa-paperclip"></i>
                                                <?= e((string) ($material['arquivo_nome'] ?? 'Abrir material')) ?>
                                            </a>

                                        <?php endif; ?>

                                        <?php if (!empty($material['link_url'])): ?>

                                            <p>
                                                <a
                                                    href="<?= e((string) $material['link_url']) ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <i class="fa-solid fa-link"></i>
                                                    Acessar link do material
                                                </a>
                                            </p>

                                        <?php endif; ?>

                                        <?php if (!empty($material['tipo'])): ?>

                                            <p>
                                                <i class="fa-solid fa-tag"></i>
                                                <?= e((string) $material['tipo']) ?>
                                            </p>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </section>

        </main>

    </div>

</div>

<script src="Script/headerSet.js"></script>

<script>
document.querySelectorAll('.canal[data-canal]').forEach(canal => {

    canal.addEventListener('click', function () {

        const nomeCanal = this.dataset.canal;

        // Remove o destaque de todos os canais.
        document.querySelectorAll('.canal[data-canal]').forEach(item => {
            item.classList.remove('ativo');
        });

        // Destaca o canal clicado.
        this.classList.add('ativo');

        // Esconde todos os conteúdos.
        document.querySelectorAll('.conteudo-canal').forEach(conteudo => {
            conteudo.classList.remove('ativo');
        });

        // Mostra somente o conteúdo correspondente.
        const conteudoSelecionado = document.getElementById('canal-' + nomeCanal);

        if (conteudoSelecionado) {
            conteudoSelecionado.classList.add('ativo');
        }

    });

});
</script>

</body>
</html>