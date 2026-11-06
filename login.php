<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

/*Verifica se o usuário já está conectado*/
/* Verifica se o usuário já está conectado */
if (!empty($_SESSION['user_id'])) {

    $tipo = $_SESSION['user_tipo'] ?? '';

    if ($tipo === 'representante') {

        $stmt = db()->prepare("
            SELECT turma_id
            FROM turma_membros
            WHERE aluno_id = :usuario_id
              AND papel = 'representante'
            LIMIT 1
        ");

        $stmt->execute([
            'usuario_id' => (int) $_SESSION['user_id']
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            redirect('criarTurmaSala.php');
            exit;
        }

        redirect('home.php');
        exit;
    }

    if ($tipo === 'aluno') {

        $stmt = db()->prepare("
            SELECT turma_id
            FROM turma_membros
            WHERE aluno_id = :usuario_id
              AND papel = 'aluno'
            LIMIT 1
        ");

        $stmt->execute([
            'usuario_id' => (int) $_SESSION['user_id']
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            redirect('entrarTurma.php');
            exit;
        }

        redirect('home.php');
        exit;
    }

    redirect('home.php');
    exit;
}

$error = '';

/* Processa o formulário de login*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 ||
        strlen($senha) < 1
    ) {
        $error = 'E-mail ou senha inválidos.';

    } else {
        $stmt = db()->prepare(
     'SELECT
        id,
        nome,
        email,
        senha_hash,
        tipo,
        status_conta
     FROM alunos
     WHERE email = :email
     LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       if (
    $user &&
    password_verify($senha, $user['senha_hash'])
) {

    if ($user['status_conta'] === 'pendente') {

        $error = 'Sua conta ainda está aguardando aprovação.';

    } elseif ($user['status_conta'] === 'recusado') {

        $error = 'Sua conta não foi aprovada.';

    } elseif ($user['status_conta'] === 'ativo') {

        /*Inicia a sessão do usuário*/
        session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_tipo'] = $user['tipo'];
                $_SESSION['user_status'] = $user['status_conta'];

                $nomeUsuario = $user['nome'] ?? 'Usuário';

                // Atualiza o último acesso
                $update = db()->prepare(
                    'UPDATE alunos
                     SET ultimo_login = NOW()
                     WHERE id = :id'
                );

                $update->execute([
                    'id' => (int) $user['id']
                ]);

                /* Redirecionamento do representante*/
                if ($user['tipo'] === 'representante') {

                    $stmt = db()->prepare("
                        SELECT turma_id
                        FROM turma_membros
                        WHERE aluno_id = :usuario_id
                          AND papel = 'representante'
                        LIMIT 1
                    ");

                    $stmt->execute([
                        'usuario_id' => (int) $user['id']
                    ]);

                    $turma = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$turma) {
                        redirect('criarTurmaSala.php');
                    }

                    redirect('adminRepresentante.php');
                }
                /* Redirecionamento dos alunos */
if ($user['tipo'] === 'aluno') {
    $stmt = db()->prepare("
        SELECT turma_id
        FROM turma_membros
        WHERE aluno_id = :usuario_id
          AND papel = 'aluno'
        LIMIT 1
    ");

    $stmt->execute([
        'usuario_id' => (int) $user['id']
    ]);

    $turma = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$turma) {
        redirect('entrarTurma.php');
    }

    redirect('home.php');
}

/* Redirecionamento dos demais usuários */
redirect('home.php');
            } else {

                $error = 'Não foi possível acessar esta conta.';
            }

        } else {

            $error = 'E-mail ou senha inválidos.';
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Entrar</title>
    <link rel="stylesheet" href="Styles/login-register.css" />
    <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <div class="bg-books" id="bgBooks" aria-hidden="true"></div>
    <header>
        <div>
            <img src="Images/Logo.png" alt="Logo" height="30px" />
            <!-- <i class="fa-solid fa-graduation-cap" style="color: #584391;"></i> -->
            <a href="index.php">REPRE</a>
        </div>
        <a href="index.php" class="btn back">
            <i class="fa-solid fa-right-from-bracket"></i>
            Voltar
        </a>
    </header>
    <main>
        <div class="card">
            <h2>Faça login para acessar sua conta e turma.</h2>
            <?php if ($error): ?>
                <p
                    role="alert"
                    style="color:#c0392b;">
                    <?= e($error) ?>
                </p>
            <?php endif; ?>
            <form
                action="login.php"
                method="post"
                class="principal-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>">

                <input
                    class="form-input"
                    type="email"
                    name="email"
                    placeholder="Email"
                    maxlength="150"
                    autocomplete="email"
                    required>

                <input
                    class="form-input"
                    type="password"
                    name="senha"
                    placeholder="Senha"
                    autocomplete="current-password"
                    required>

                <button
                    type="submit"
                    class="btn-submit form-input">
                    Entrar
                </button>

            </form>

            <a href="#" class="forgot-password">Esqueci minha senha</a>
            <div class="register-link">
                <span>Não tem uma conta?</span>
                <a href="cadastro.php">Cadastre-se</a>
            </div>
        </div>
    </main>

    <footer>© 2026 Repre · Estude com organização</footer>
    <script src="funcaolivros.js"></script>
</body>

</html>