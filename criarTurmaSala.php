<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_role(['representante']);

$pdo = db();
$representanteId = (int) $_SESSION['user_id'];
$usuarioId = (int) $_SESSION['user_id'];

$erro = '';
$nome = '';
$serie = '';
$turno = '';
$anoLetivo = (int) date('Y');

$seriesDisponiveis = [
    '1° ano',
    '2° ano',
    '3° ano'
];

$turnosDisponiveis = [
    'manha' => 'Matutino',
    'tarde' => 'Vespertino',
    'noite' => 'Noturno'
];

try {
    $stmt = $pdo->prepare("
        SELECT tm.turma_id
        FROM turma_membros tm
        WHERE tm.aluno_id = :usuario_id
        AND tm.papel = 'representante'
        LIMIT 1
    ");
    
    $stmt->execute(['usuario_id' => $usuarioId]);
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Location: home.php');
        exit;
    }
    
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Não foi possível verificar sua turma. Tente novamente mais tarde.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $serie = trim((string) ($_POST['serie'] ?? ''));
    $turno = trim((string) ($_POST['turno'] ?? ''));
    
    $anoLetivoInput = filter_var(
        $_POST['ano_letivo'] ?? null,
        FILTER_VALIDATE_INT
    );
    
    if ($anoLetivoInput !== false) {
        $anoLetivo = $anoLetivoInput;
    }
    
    try {
        if (function_exists('verify_csrf')) {
            verify_csrf();
        }

        if ($nome === '' || mb_strlen($nome) > 100) {
            throw new RuntimeException('Informe um nome para a turma com até 100 caracteres.');
        }

        if (!in_array($serie, $seriesDisponiveis, true)) {
            throw new RuntimeException('Selecione uma série válida.');
        }

        $turnosValidos = ['manha', 'tarde', 'noite'];

        if (!in_array($turno, $turnosValidos, true)) {
            throw new RuntimeException('Selecione um turno válido.');
        }

        if (
            $anoLetivoInput === false ||
            $anoLetivo < 2020 ||
            $anoLetivo > 2100
        ) {
            throw new RuntimeException('Informe um ano letivo válido.');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT turma_id
            FROM turma_membros
            WHERE aluno_id = :usuario_id
              AND papel = 'representante'
            LIMIT 1
        ");

        $stmt->execute(['usuario_id' => $usuarioId]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->rollBack();
            header('Location: home.php');
            exit;
        }

        $codigoConvite = gerarCodigoConvite($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO turmas
                (nome, serie, turno, ano_letivo, ativa, codigo_convite)
            VALUES
                (:nome, :serie, :turno, :ano_letivo, 1, :codigo_convite)
        ");

        $sucessoTurma = $stmt->execute([
            'nome'           => $nome,
            'serie'          => $serie,
            'turno'          => $turno,
            'ano_letivo'     => $anoLetivo,
            'codigo_convite' => $codigoConvite
        ]);
        
        if (!$sucessoTurma) {
            $erroBanco = $stmt->errorInfo();
            throw new Exception("Erro ao inserir turma no banco: " . ($erroBanco[2] ?? 'Desconhecido'));
        }

        $turmaId = (int) $pdo->lastInsertId();

        if ($turmaId <= 0) {
            throw new Exception("Erro crítico: ID da turma gerado é inválido.");
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO turma_membros
                (turma_id, aluno_id, papel)
            VALUES
                (:turma_id, :usuario_id, 'representante')
        ");

        $stmt->execute([
            'turma_id'   => $turmaId,
            'usuario_id' => $usuarioId
        ]);

        $disciplinasIniciais = [1, 2, 3];
        $stmtDisc = $pdo->prepare("
            INSERT INTO turma_disciplinas (turma_id, disciplina_id, professor_id) 
            VALUES (:turma_id, :disciplina_id, :professor_id)
        ");

        foreach ($disciplinasIniciais as $disciplinaId) {
            $stmtDisc->execute([
                ':turma_id'      => $turmaId,
                ':disciplina_id' => $disciplinaId,
                ':professor_id'  => null
            ]);
        }

        $pdo->commit();

        header('Location: home.php');
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e instanceof RuntimeException) {
            $erro = $e->getMessage();
        } else {
            error_log($e->getMessage());
            $erro = 'Não foi possível criar a turma. Erro: ' . $e->getMessage();
        }
    }
}

if (!function_exists('e')) {
    function e($valor): string
    {
        return htmlspecialchars(
            (string) $valor,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

$token = function_exists('csrf_token') ? csrf_token() : '';

function gerarCodigoConvite(PDO $pdo): string
{
    do {
        $codigo = strtoupper(bin2hex(random_bytes(4)));

        $stmt = $pdo->prepare("
            SELECT codigo_convite
            FROM turmas
            WHERE codigo_convite = :codigo
            LIMIT 1
        ");

        $stmt->execute(['codigo' => $codigo]);

        $existe = $stmt->fetchColumn() !== false;

    } while ($existe);

    return $codigo;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar minha turma | REPRE</title>

    <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="Styles/root.css">
    <link rel="stylesheet" href="Styles/criarTurmaSala.css">

</head>

<body>

 <!-- DECORAÇÃO DO FUNDO -->

    <div class="decoracao-superior"></div>
    

    <!-- CABEÇALHO -->

    <header class="topbar">

        <a href="home.php" class="logo">
           
        </a>

    </header>
      <!-- MARCA-D1GUA -->
                    <div class="marca-dagua">
      <img src="/Images/Logo.png" alt="Logo do REPRE">
</div>

    <!-- CONTEÚDO PRINCIPAL DA PG -->

    <main class="pagina">
     <!-- APRESENTAÇÃO -->

        <section class="apresentacao">

            <span class="etiqueta">
                <i class="fa-solid fa-sparkles"></i>
                Vamos começar!
            </span>

            <h1>
                Crie sua turma e organize sua rotina escolar.
            </h1>

            <p>
                Antes de acessar seu painel, precisamos cadastrar sua turma.
                Depois disso, você poderá organizar atividades, prazos e
                compromissos escolares no REPRE.
            </p>


            <!-- BENEFÍCIOS -->

            <div class="beneficios">

                <div class="beneficio-item">

                    <span class="beneficio-icone">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <div>
                        <strong>Sua turma em um só lugar</strong>
                        <p>Centralize as informações da sua classe.</p>
                    </div>

                </div>


                <div class="beneficio-item">

                    <span class="beneficio-icone">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>

                    <div>
                        <strong>Mais organização</strong>
                        <p>Acompanhe atividades e datas importantes.</p>
                    </div>

                </div>


                <div class="beneficio-item">

                    <span class="beneficio-icone">
                        <i class="fa-solid fa-rocket"></i>
                    </span>

                    <div>
                        <strong>Pronto para começar</strong>
                        <p>Conclua o cadastro e acesse sua página inicial.</p>
                    </div>

                </div>

            </div>

        </section>

     <section class="formulario-card">

            <div class="formulario-cabecalho">


                <div class="cabecalho-icone">
                    <img src="Images/Logo.png" alt="Logo REPRE">
                </div>

                <h2>Dados da sua turma</h2>

                <p>
                    Preencha as informações abaixo para continuar.
                </p>

            </div>
        <?php if ($erro !== ''): ?>
            <div class="mensagem-erro" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <form id="formTurma" method="POST" action="criarTurmaSala.php">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

                 <!-- NOME DA TURMA -->

                <div class="campo">

                    <label for="nome">
                        Nome da turma
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-people-group"></i>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            placeholder="Ex.: 3º DS ou 2º A"
                            maxlength="100"
                            required
                        >

                    </div>

                    <small>
                        Digite o nome usado pela sua escola.
                    </small>

                </div>

             <div class="campo">

                    <label for="serie">
                        Etapa de ensino
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-graduation-cap"></i>

                        <select id="serie" name="serie" required>

                            <option value="" selected disabled>
                                Selecione a etapa
                            </option>
                        <?php foreach ($seriesDisponiveis as $opcao): ?>
                            <option
                                value="<?= e($opcao) ?>"
                                <?= $serie === $opcao ? 'selected' : '' ?>
                            >
                                <?= e($opcao) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

           <!-- TURNO -->

                <div class="campo">

                    <label for="turno">
                        Turno
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-regular fa-clock"></i>

                        <select id="turno" name="turno" required>

                            <option value="" selected disabled>
                                Selecione o turno
                            </option>
                        <option value="manha" <?= $turno === 'manha' ? 'selected' : '' ?>>Matutino</option>
                        <option value="tarde" <?= $turno === 'tarde' ? 'selected' : '' ?>>Vespertino</option>
                        <option value="noite" <?= $turno === 'noite' ? 'selected' : '' ?>>Noturno</option>
                    </select>
                </div>
            </div>

            <!-- ANO LETIVO -->

                <div class="campo">

                    <label for="ano_letivo">
                        Ano letivo
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-regular fa-calendar"></i>

                    <input
                        type="number"
                        id="ano_letivo"
                        name="ano_letivo"
                        min="2020"
                        max="2100"
                        value="<?= e((string) $anoLetivo) ?>"
                        required
                    >
                </div>
            </div>

           <!-- BOTÃO -->

                <button type="submit" class="botao-criar">

                    <span>Criar turma e continuar</span>

                    <i class="fa-solid fa-arrow-right"></i>

                </button>


                <!-- NOTA-->

                <p class="nota-formulario">

                    <i class="fa-solid fa-lock"></i>

                    Seus dados serão salvos com segurança.
                
                </p>

            </form>

        </section>

    </main>

<!-- RODAPÉ -->

    <footer>
        REPRE · Organização escolar mais simples.
    </footer>

</body>
</html>
