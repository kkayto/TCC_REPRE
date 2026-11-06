<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

require_active_account();

if (($_SESSION['user_tipo'] ?? '') !== 'aluno') {
    redirect('home.php');
}

$pdo = db();
$alunoId = (int) $_SESSION['user_id'];

$erro = '';
$codigo = '';
$turmaEncontrada = null;

// Se já pertence a uma turma, não precisa entrar novamente.
$stmt = $pdo->prepare("
    SELECT turma_id
    FROM turma_membros
    WHERE aluno_id = :aluno_id
      AND papel = 'aluno'
    LIMIT 1
");

$stmt->execute(['aluno_id' => $alunoId]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    redirect('home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();

        $codigo = strtoupper(trim(
            (string) ($_POST['codigo_convite'] ?? '')
        ));

        if ($codigo === '' || strlen($codigo) > 12) {
            throw new RuntimeException(
                'Informe um código de convite válido.'
            );
        }

        $stmt = $pdo->prepare("
            SELECT id, nome, serie, turno, ano_letivo
            FROM turmas
            WHERE codigo_convite = :codigo
              AND ativa = 1
            LIMIT 1
        ");

        $stmt->execute(['codigo' => $codigo]);

        $turma = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$turma) {
            throw new RuntimeException(
                'Código não encontrado. Confira o código fornecido pelo representante.'
            );
        }

        $turmaId = (int) $turma['id'];

        $pdo->beginTransaction();

        // Evita vincular o aluno duas vezes à mesma turma.
        $stmt = $pdo->prepare("
            SELECT turma_id
            FROM turma_membros
            WHERE aluno_id = :aluno_id
              AND papel = 'aluno'
            LIMIT 1
        ");

        $stmt->execute(['aluno_id' => $alunoId]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->rollBack();
            redirect('home.php');
        }

        // Entrada imediata, sem solicitação ou aprovação.
        $stmt = $pdo->prepare("
            INSERT INTO turma_membros
                (turma_id, aluno_id, papel)
            VALUES
                (:turma_id, :aluno_id, 'aluno')
        ");

        $stmt->execute([
            'turma_id' => $turmaId,
            'aluno_id' => $alunoId
        ]);

      $pdo->commit();
redirect('home.php');
exit;

    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erro = $e->getMessage();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        $erro = 'Não foi possível entrar na turma. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Entrar em uma turma | REPRE</title>

    <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="entraTurma.css">
</head>

<body>
<!-- Elementos decorativos -->
    <div class="decoracao decoracao-superior"></div>
    <div class="decoracao decoracao-inferior"></div>

    <!-- marca-d'água -->
    <div class="marca-dagua">
        <img src="Images/Logo.png" alt="">
    </div>

    <main class="pagina-turma">


   <div class="turma-card">

            <div class="turma-icon">
                <img src="Images/Logo.png" alt="Logo REPRE">
            </div>

            <header class="turma-header">

                <span class="subtitulo">BEM-VINDO AO REPRE</span>

                <h1>Entre na sua turma</h1>

                <p class="descricao">
                    Sua jornada escolar fica mais organizada
                    quando você está conectado à sua turma.
                </p>

            </header>
            <!--Formulário -->

        <?php if ($erro !== ''): ?>
            <div class="mensagem-erro" role="alert">
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="entrarTurma.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>">
            
           <div class="campo">
                    <label for="codigo_convite">
                        Código de convite
                    </label>


                <div class="input-container">

                        <i class="fa-solid fa-ticket"></i>

                        <input type="text"
                    id="codigo_convite"
                    name="codigo_convite"
                    placeholder="Ex.: A7F29B4C"
                    maxlength="12"
                    autocomplete="off"
                    value="<?= e($codigo) ?>"
                    required>
            </div>

          <span class="campo-ajuda">
                        Utilize o código fornecido pelo representante.
                    </span>

                </div>

                <button type="submit" class="botao-entrar">
                    <span>Entrar na turma</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

            </form>

       <!-- msg de aviso -->
            <div class="aviso">

                <div class="aviso-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div class="aviso-texto">
                    <strong>Seu acesso está protegido</strong>

                    <p>
                        Seu perfil será vinculado à turma
                        correspondente ao código informado.
                    </p>
                </div>

            </div>
            <!-- msg final -->

 <div class="assinatura">
                <span>REPRE</span>
                <i class="fa-solid fa-graduation-cap"></i>
            </div>

        </div>

    </main>

</body>

</html>