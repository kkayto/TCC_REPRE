<?php
declare(strict_types=1);
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
    exit('Você não tem permissão para acessar esta turma.');
}

function materialEscapar($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$mensagem = '';
$erro = '';

$tipos = [
    'pdf' => 'PDF',
    'documento' => 'Documento',
    'slide' => 'Apresentação / Slides',
    'imagem' => 'Imagem',
    'link' => 'Link externo',
    'outro' => 'Outro'
];

$stmt = $pdo->prepare("
    SELECT td.id, d.nome
    FROM turma_disciplinas td
    INNER JOIN disciplinas d ON d.id = td.disciplina_id
    WHERE td.turma_id = :turma_id
    ORDER BY d.nome
");

$stmt->execute([':turma_id' => $turmaId]);
$disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();

        $acao = (string) ($_POST['acao'] ?? '');

        if ($acao === 'excluir') {
            $id = filter_var(
                $_POST['material_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id <= 0) {
                throw new RuntimeException('Material inválido.');
            }

            $stmt = $pdo->prepare("
                SELECT arquivo_caminho
                FROM materiais_estudos
                WHERE id = :id AND turma_id = :turma_id
                LIMIT 1
            ");
            $stmt->execute([':id' => $id, ':turma_id' => $turmaId]);
            $caminhoArquivoExcluir = $stmt->fetchColumn();

            $stmt = $pdo->prepare("
                DELETE FROM materiais_estudos
                WHERE id = :id AND turma_id = :turma_id
            ");

            $stmt->execute([
                ':id' => $id,
                ':turma_id' => $turmaId
            ]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException(
                    'Material não encontrado.'
                );
            }

            if (is_string($caminhoArquivoExcluir) && str_starts_with($caminhoArquivoExcluir, 'uploads/materiais/')) {
                $caminhoFisico = __DIR__ . '/' . $caminhoArquivoExcluir;
                if (is_file($caminhoFisico)) {
                    @unlink($caminhoFisico);
                }
            }

            header(
                'Location: materiaisEstudos.php?turma_id='
                . $turmaId . '&sucesso=excluido'
            );
            exit;
        }

        if ($acao !== 'salvar') {
            throw new RuntimeException('Ação inválida.');
        }

        $id = filter_var(
            $_POST['material_id'] ?? 0,
            FILTER_VALIDATE_INT
        ) ?: 0;

        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $tipo = (string) ($_POST['tipo'] ?? 'pdf');
        $link = trim((string) ($_POST['link_url'] ?? ''));

       $disciplinaId = filter_var(
    $_POST['turma_disciplinas_id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($disciplinaId === false || $disciplinaId === null || $disciplinaId <= 0) {
    throw new RuntimeException(
        'Selecione uma disciplina válida antes de cadastrar o material.'
    );
}
        if ($titulo === '' || strlen($titulo) > 150) {
            throw new RuntimeException(
                'Informe um título de até 150 caracteres.'
            );
        }

        if (!array_key_exists($tipo, $tipos)) {
            throw new RuntimeException(
                'Selecione um tipo de material válido.'
            );
        }

        if ($disciplinaId !== null) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM turma_disciplinas
                WHERE id = :id AND turma_id = :turma_id
            ");

            $stmt->execute([
                ':id' => $disciplinaId,
                ':turma_id' => $turmaId
            ]);

            if ((int) $stmt->fetchColumn() === 0) {
                throw new RuntimeException(
                    'Disciplina inválida para esta turma.'
                );
            }
        }

        if ($link !== '') {
            $esquema = strtolower(
                (string) parse_url($link, PHP_URL_SCHEME)
            );

            if (
                !filter_var($link, FILTER_VALIDATE_URL) ||
                !in_array($esquema, ['http', 'https'], true)
            ) {
                throw new RuntimeException(
                    'Informe um link válido iniciado com http:// ou https://.'
                );
            }
        }

        $arquivoNome = null;
        $arquivoCaminho = null;

        if ($id > 0) {
            $stmt = $pdo->prepare("
                SELECT id, arquivo_nome, arquivo_caminho
                FROM materiais_estudos
                WHERE id = :id AND turma_id = :turma_id
                LIMIT 1
            ");

            $stmt->execute([
                ':id' => $id,
                ':turma_id' => $turmaId
            ]);

            $anterior = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anterior) {
                throw new RuntimeException(
                    'Material não encontrado para edição.'
                );
            }

            $arquivoNome = $anterior['arquivo_nome'];
            $arquivoCaminho = $anterior['arquivo_caminho'];
        }

        // se nenhum arquivo novo for enviado, mantém o anterior.
        $arquivoNovoCaminho = null;
        $arquivoNovoNome = null;
        $uploadRecebido = isset($_FILES['arquivo'])
            && (($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);

        if ($uploadRecebido) {
            $arquivo = $_FILES['arquivo'];
            if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new RuntimeException('O envio do arquivo falhou. Tente novamente.');
            }

            $limiteBytes = 10 * 1024 * 1024; // 10 MB
            if ((int) ($arquivo['size'] ?? 0) <= 0 || (int) $arquivo['size'] > $limiteBytes) {
                throw new RuntimeException('O arquivo deve ter tamanho máximo de 10 MB.');
            }

            $nomeOriginal = basename((string) ($arquivo['name'] ?? ''));
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            $extensoesPermitidas = [
                'pdf', 'doc', 'docx', 'odt', 'rtf', 'txt', 'csv',
                'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'odp',
                'jpg', 'jpeg', 'png', 'gif', 'webp'
            ];
            if (!in_array($extensao, $extensoesPermitidas, true)) {
                throw new RuntimeException('Formato não permitido. Envie PDF, documentos Office/LibreOffice, TXT, CSV ou imagens.');
            }

            if (!is_uploaded_file((string) ($arquivo['tmp_name'] ?? ''))) {
                throw new RuntimeException('O arquivo enviado não é válido.');
            }

            $diretorio = __DIR__ . '/uploads/materiais';
            if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
                throw new RuntimeException('Não foi possível preparar a pasta de arquivos no servidor.');
            }

            // Impede listagem da pasta e execução direta de scripts enviados.
            $htaccess = $diretorio . '/.htaccess';
            if (!is_file($htaccess)) {
                @file_put_contents($htaccess, "Options -Indexes\n<FilesMatch \"\\.(php[0-9]?|phtml|phar|cgi|pl|py|sh)$\">\nRequire all denied\n</FilesMatch>\n");
            }

            // Nome aleatório para evitar colisões e não confiar no nome enviado pelo usuário.
            try {
                $nomeArmazenado = bin2hex(random_bytes(16)) . '.' . $extensao;
            } catch (Throwable $ex) {
                $nomeArmazenado = uniqid('material_', true) . '.' . $extensao;
            }

            $destino = $diretorio . '/' . $nomeArmazenado;
            if (!move_uploaded_file((string) $arquivo['tmp_name'], $destino)) {
                throw new RuntimeException('Não foi possível salvar o arquivo no servidor.');
            }

            $arquivoNovoNome = $nomeOriginal;
            $arquivoNovoCaminho = 'uploads/materiais/' . $nomeArmazenado;
            $arquivoNome = $arquivoNovoNome;
            $arquivoCaminho = $arquivoNovoCaminho;
        }

        if ($link === '' && $arquivoCaminho === null) {
            throw new RuntimeException('Envie um arquivo ou informe um link externo para este material.');
        }

        $stmt = $pdo->prepare("
            SELECT id
            FROM materiais_estudos
            WHERE id = :id AND turma_id = :turma_id
            LIMIT 1
        ");

        if ($id > 0) {
            $stmt->execute([
                ':id' => $id,
                ':turma_id' => $turmaId
            ]);

            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('Material não encontrado.');
            }

            $stmt = $pdo->prepare("
                UPDATE materiais_estudos
                SET turma_disciplinas_id = :turma_disciplinas_id,
                    titulo = :titulo,
                    descricao = :descricao,
                    tipo = :tipo,
                    link_url = :link_url,
                    arquivo_nome = :arquivo_nome,
                    arquivo_caminho = :arquivo_caminho
                WHERE id = :id AND turma_id = :turma_id
            ");

            $stmt->execute([
                ':turma_disciplinas_id' => $disciplinaId,
                ':titulo' => $titulo,
                ':descricao' => $descricao !== '' ? $descricao : null,
                ':tipo' => $tipo,
                ':link_url' => $link !== '' ? $link : null,
                ':arquivo_nome' => $arquivoNome,
                ':arquivo_caminho' => $arquivoCaminho,
                ':id' => $id,
                ':turma_id' => $turmaId
            ]);

            if ($arquivoNovoCaminho !== null && !empty($anterior['arquivo_caminho'])
                && str_starts_with((string) $anterior['arquivo_caminho'], 'uploads/materiais/')) {
                $caminhoAntigo = __DIR__ . '/' . $anterior['arquivo_caminho'];
                if (is_file($caminhoAntigo)) {
                    @unlink($caminhoAntigo);
                }
            }

            $sucesso = 'editado';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO materiais_estudos (
                    turma_id,
                    turma_disciplinas_id,
                    publicado_por,
                    titulo,
                    descricao,
                    tipo,
                    link_url,
                    arquivo_nome,
                    arquivo_caminho
                ) VALUES (
                    :turma_id,
                    :turma_disciplinas_id,
                    :publicado_por,
                    :titulo,
                    :descricao,
                    :tipo,
                    :link_url,
                    :arquivo_nome,
                    :arquivo_caminho
                )
            ");

            $stmt->execute([
                ':turma_id' => $turmaId,
                ':turma_disciplinas_id' => $disciplinaId,
                ':publicado_por' => $usuarioId,
                ':titulo' => $titulo,
                ':descricao' => $descricao !== '' ? $descricao : null,
                ':tipo' => $tipo,
                ':link_url' => $link !== '' ? $link : null,
                ':arquivo_nome' => $arquivoNome,
                ':arquivo_caminho' => $arquivoCaminho
            ]);

            $sucesso = 'criado';
        }

        header(
            'Location: materiaisEstudos.php?turma_id='
            . $turmaId . '&sucesso=' . $sucesso
        );
        exit;

    } catch (RuntimeException $ex) {
        $erro = $ex->getMessage();
    } catch (PDOException $ex) {
        error_log('Erro materiaisEstudos: ' . $ex->getMessage());
        $erro = 'Não foi possível salvar. Confira a tabela e a conexão com o banco.';
    }
}

if (isset($_GET['sucesso'])) {
    $mensagem = match ($_GET['sucesso']) {
        'criado' => 'Material publicado com sucesso!',
        'editado' => 'Material atualizado com sucesso!',
        'excluido' => 'Material excluído com sucesso!',
        default => ''
    };
}
$edicao = null;
$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

if ($editarId && $editarId > 0) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM materiais_estudos
        WHERE id = :id AND turma_id = :turma_id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $editarId,
        ':turma_id' => $turmaId
    ]);

    $edicao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$edicao) {
        http_response_code(404);
        exit('Material não encontrado.');
    }
}

$busca = trim((string) ($_GET['busca'] ?? ''));

$filtroDisciplina = filter_input(
    INPUT_GET,
    'disciplina',
    FILTER_VALIDATE_INT
);

$sql = "
    SELECT
        m.*,
        d.nome AS disciplina_nome,
        a.nome AS autor_nome
    FROM materiais_estudos m
    LEFT JOIN turma_disciplinas td
        ON td.id = m.turma_disciplinas_id
    LEFT JOIN disciplinas d
        ON d.id = td.disciplina_id
    LEFT JOIN alunos a
        ON a.id = m.publicado_por
    WHERE m.turma_id = :turma_id
";

$params = [':turma_id' => $turmaId];

if ($busca !== '') {
    $sql .= "
        AND (
            m.titulo LIKE :busca_titulo
            OR m.descricao LIKE :busca_descricao
        )
    ";

    $params[':busca_titulo'] = '%' . $busca . '%';
    $params[':busca_descricao'] = '%' . $busca . '%';
}

if ($filtroDisciplina) {
    $sql .= " AND m.turma_disciplinas_id = :turma_disciplinas_id";
    $params[':turma_disciplinas_id'] = $filtroDisciplina;
}

$sql .= " ORDER BY m.criado_em DESC, m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Materiais de estudo - REPRE</title>

    <link rel="stylesheet" href="materiaisEstudos.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <a class="logo" href="adminRepresentante.php">REPRE</a>
    <a class="voltar" href="adminTurma.php?id=<?= (int) $turmaId ?>">
        <i class="fa-solid fa-arrow-left"></i> Voltar para turma
    </a>
</nav>

<main class="container">

    <header class="cabecalho">
        <div class="cabecalho-icone">
            <i class="fa-solid fa-book-open"></i>
        </div>
        <div>
            <p class="sobretitulo">BIBLIOTECA DA TURMA</p>
            <h1>Materiais de estudo</h1>
            <p>Compartilhe resumos, documentos e links úteis para a turma.</p>
            <span class="turma-tag">
                <i class="fa-solid fa-users"></i>
                <?= materialEscapar($turma['nome']) ?>
                · <?= materialEscapar($turma['ano_letivo']) ?>
            </span>
        </div>
    </header>

    <?php if ($mensagem !== ''): ?>
        <div class="alerta sucesso">
            <i class="fa-solid fa-circle-check"></i>
            <?= materialEscapar($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if ($erro !== ''): ?>
        <div class="alerta erro">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= materialEscapar($erro) ?>
        </div>
    <?php endif; ?>

    <div class="layout">

        <section class="painel" id="formulario">
            <div class="painel-titulo">
                <div class="painel-icone">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div>
                    <h2><?= $edicao ? 'Editar material' : 'Adicionar material' ?></h2>
                    <p>Adicione documentos, apresentações, imagens ou links para a turma.</p>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data" class="formulario">
                <input type="hidden" name="csrf_token" value="<?= materialEscapar(csrf_token()) ?>">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="material_id" value="<?= (int) ($edicao['id'] ?? 0) ?>">

                <label for="titulo">Título *</label>
                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    maxlength="150"
                    required
                    placeholder="Ex.: Resumo de funções"
                    value="<?= materialEscapar($edicao['titulo'] ?? '') ?>"
                >

                <label for="descricao">Descrição</label>
                <textarea
                    id="descricao"
                    name="descricao"
                    rows="3"
                    placeholder="Explique brevemente o conteúdo..."
                ><?= materialEscapar($edicao['descricao'] ?? '') ?></textarea>

                <label for="turma_disciplinas_id">Disciplina *</label>
                <select id="turma_disciplinas_id" name="turma_disciplinas_id" required>
                    <option value="">Selecione uma disciplina</option>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <option
                            value="<?= (int) $disciplina['id'] ?>"
                            <?= (string) ($edicao['turma_disciplinas_id'] ?? '') === (string) $disciplina['id'] ? 'selected' : '' ?>
                        >
                            <?= materialEscapar($disciplina['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="tipo">Tipo de material *</label>
                <select id="tipo" name="tipo" required>
                    <?php foreach ($tipos as $valor => $rotulo): ?>
                        <option
                            value="<?= materialEscapar($valor) ?>"
                            <?= ($edicao['tipo'] ?? 'link') === $valor ? 'selected' : '' ?>
                        >
                            <?= materialEscapar($rotulo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="link_url">Link externo</label>
                <input
                    type="url"
                    id="link_url"
                    name="link_url"
                    placeholder="https://..."
                    value="<?= materialEscapar($edicao['link_url'] ?? '') ?>"
                >

                <label for="arquivo">Arquivo (opcional)</label>
                <input
                    type="file"
                    id="arquivo"
                    name="arquivo"
                    accept=".pdf,.doc,.docx,.odt,.rtf,.txt,.csv,.xls,.xlsx,.ods,.ppt,.pptx,.odp,.jpg,.jpeg,.png,.gif,.webp"
                >
                <p class="ajuda">
                    <i class="fa-solid fa-circle-info"></i>
                    Máximo de 10 MB. Aceita PDF, Word, Excel, PowerPoint, documentos de texto e imagens.
                    Se estiver editando, deixe em branco para manter o arquivo atual.
                </p>
                <?php if (!empty($edicao['arquivo_nome']) && !empty($edicao['arquivo_caminho'])): ?>
                    <p class="arquivo-atual">
                        <i class="fa-solid fa-paperclip"></i>
                        Arquivo atual: <?= materialEscapar($edicao['arquivo_nome']) ?>
                    </p>
                <?php endif; ?>

                <button class="btn-principal" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= $edicao ? 'Salvar alterações' : 'Publicar material' ?>
                </button>

                <?php if ($edicao): ?>
                    <a class="btn-secundario" href="materiaisEstudos.php?turma_id=<?= (int) $turmaId ?>">
                        Cancelar edição
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <section class="painel painel-lista">
            <div class="lista-topo">
                <div>
                    <p class="sobretitulo">CONTEÚDO COMPARTILHADO</p>
                    <h2>Materiais da turma</h2>
                    <p>Pesquise e encontre os recursos de estudo.</p>
                </div>
                <span class="contador"><?= count($materiais) ?> material(is)</span>
            </div>

            <form method="get" class="filtros">
                <input type="hidden" name="turma_id" value="<?= (int) $turmaId ?>">

                <input
                    type="search"
                    name="busca"
                    placeholder="Buscar material..."
                    value="<?= materialEscapar($busca) ?>"
                >

                <select name="disciplina" aria-label="Filtrar por disciplina">
                    <option value="">Todas as disciplinas</option>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <option
                            value="<?= (int) $disciplina['id'] ?>"
                            <?= (string) $filtroDisciplina === (string) $disciplina['id'] ? 'selected' : '' ?>
                        >
                            <?= materialEscapar($disciplina['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn-filtrar" type="submit">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
            </form>

            <?php if (!$materiais): ?>
                <div class="vazio">
                    <i class="fa-regular fa-folder-open"></i>
                    <h3>Nenhum material encontrado</h3>
                    <p>Cadastre o primeiro material ou tente outra pesquisa.</p>
                </div>
            <?php else: ?>
                <div class="materiais-grid">
                    <?php foreach ($materiais as $material): ?>
                        <article class="material-card">

                            <div class="material-icone">
                                <?php
                                $icone = match ($material['tipo']) {
                                    'pdf' => 'fa-file-pdf',
                                    'documento' => 'fa-file-word',
                                    'slide' => 'fa-file-powerpoint',
                                    'imagem' => 'fa-file-image',
                                    'link' => 'fa-link',
                                    default => 'fa-file-lines'
                                };
                                ?>
                                <i class="fa-solid <?= $icone ?>"></i>
                            </div>

                            <span class="tipo-tag">
                                <?= materialEscapar($tipos[$material['tipo']] ?? 'Material') ?>
                            </span>

                            <h3><?= materialEscapar($material['titulo']) ?></h3>

                            <p class="descricao">
                                <?= materialEscapar($material['descricao'] ?: 'Sem descrição.') ?>
                            </p>

                            <div class="material-meta">
                                <span>
                                    <i class="fa-solid fa-book"></i>
                                    <?= materialEscapar($material['disciplina_nome'] ?? 'Todas as disciplinas') ?>
                                </span>
                                <span>
                                    <i class="fa-regular fa-user"></i>
                                    <?= materialEscapar($material['autor_nome'] ?? 'Representante') ?>
                                </span>
                                <span>
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= !empty($material['criado_em']) ? date('d/m/Y', strtotime($material['criado_em'])) : '—' ?>
                                </span>
                            </div>

                            <div class="material-acoes">
                                <?php if (!empty($material['link_url'])): ?>
                                    <a
                                        class="btn-abrir"
                                        href="<?= materialEscapar($material['link_url']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        Acessar link
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($material['arquivo_caminho']) && !empty($material['arquivo_nome'])): ?>
                                    <a
                                        class="btn-abrir"
                                        href="<?= materialEscapar($material['arquivo_caminho']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i class="fa-solid fa-download"></i>
                                        Abrir documento
                                    </a>
                                <?php endif; ?>

                                <a
                                    class="acao-btn"
                                    href="materiaisEstudos.php?turma_id=<?= (int) $turmaId ?>&editar=<?= (int) $material['id'] ?>#formulario"
                                    title="Editar material"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <form
                                    method="post"
                                    onsubmit="return confirm('Deseja realmente excluir este material?');"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= materialEscapar(csrf_token()) ?>">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="material_id" value="<?= (int) $material['id'] ?>">

                                    <button class="acao-btn excluir" type="submit" title="Excluir material">
                                        <i class="fa-solid fa-trash"></i>
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
