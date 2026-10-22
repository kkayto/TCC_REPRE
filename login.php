<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect('home.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $senha = (string)($_POST['senha'] ?? '');

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
                status_conta,
                foto_url,
                ativo
             FROM alunos
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch();

        if (
            $user &&
            (int)$user['ativo'] === 1 &&
            password_verify($senha, $user['senha_hash'])
        ) {

            if ($user['status_conta'] === 'pendente') {
                $error = 'Sua conta ainda está aguardando aprovação.';
            } elseif ($user['status_conta'] === 'recusado') {
                $error = 'Sua conta não foi aprovada.';
            } elseif ($user['status_conta'] === 'ativo') {

                session_regenerate_id(true);

                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_tipo'] = $user['tipo'];
                $_SESSION['user_status'] = $user['status_conta'];
                $nomeUsuario = $user['nome'] ?? 'Usuário';
                
                //-------Foto Vazia---------
                $_SESSION['user_inicial'] = function_exists('mb_substr')
                    ? mb_substr($nomeUsuario, 0, 1, 'UTF-8')
                    : substr($nomeUsuario, 0, 1);

                if (!empty($user['foto_url'])) {
                    // Se o PDO trouxer como Resource, lê os bytes. Se trouxer como String, mantém.
                    $dadosFoto = is_resource($user['foto_url']) ? stream_get_contents($user['foto_url']) : $user['foto_url'];
                    $_SESSION['user_foto'] = base64_encode($dadosFoto);
                } else {
                    $_SESSION['user_foto'] = '';
                }
                $update = db()->prepare(
                    'UPDATE alunos
                     SET ultimo_login = NOW()
                     WHERE id = :id'
                );

                $update->execute([
                    'id' => $user['id']
                ]);
                if ($user['tipo'] === 'representante') {
                    redirect('adminRepresentante.php');
                }

                redirect('home.php');
            }
        }
        if ($error === '') {
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
                <a href="register.php">Cadastre-se</a>
            </div>
        </div>
    </main>

    <footer>© 2026 Repre · Estude com organização</footer>
    <script src="funcaolivros.js"></script>
</body>

</html>