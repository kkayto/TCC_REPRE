<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';

$error = '';
$oldName = '';
$oldEmail = '';
$oldTipo = '';

if (!empty($_SESSION['user_id'])) {
    redirect('home.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $oldName = trim((string) ($_POST['name'] ?? ''));
    $oldEmail = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['password'] ?? '');
    $oldTipo = (string) ($_POST['tipo'] ?? '');

    try {

        // Verifica o token de segurança
        verify_csrf();

        // Tipos de conta aceitos
        $tiposPermitidos = [
            'aluno',
            'representante'
        ];

        // Validação dos dados
        if (!in_array($oldTipo, $tiposPermitidos, true)) {

            $error = 'Selecione um tipo de conta válido.';

        } elseif (
            $oldName === '' ||
            (
                function_exists('mb_strlen')
                    ? mb_strlen($oldName, 'UTF-8')
                    : strlen($oldName)
            ) > 120
        ) {

            $error = 'Informe um nome válido com até 120 caracteres.';

        } elseif (
            !filter_var($oldEmail, FILTER_VALIDATE_EMAIL) ||
            strlen($oldEmail) > 150
        ) {

            $error = 'Informe um e-mail válido.';

        } elseif (
            strlen($senha) < 8 ||
            strlen($senha) > 72
        ) {
            $error = 'A senha deve ter entre 8 e 72 bytes.';
        } else {
            $pdo = db();
            $check = $pdo->prepare(
                'SELECT id
                 FROM alunos
                 WHERE email = :email
                 LIMIT 1'
            );

            $check->execute([
                'email' => $oldEmail
            ]);
            if ($check->fetch(PDO::FETCH_ASSOC)) {
                $error = 'Este e-mail já está cadastrado.';
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                if ($hash === false) {
                    throw new RuntimeException(
                        'Não foi possível gerar o hash da senha.'
                    );
                }
                $statusConta = ($oldTipo === 'aluno')
                    ? 'ativo'
                    : 'pendente';
                
                $stmt = $pdo->prepare(
                    'INSERT INTO alunos (
                        nome,
                        email,
                        senha_hash,
                        tipo,
                        status_conta
                    ) VALUES (
                        :nome,
                        :email,
                        :senha_hash,
                        :tipo,
                        :status_conta
                    )'
                );

                $stmt->execute([
                    'nome' => $oldName,
                    'email' => $oldEmail,
                    'senha_hash' => $hash,
                    'tipo' => $oldTipo,
                    'status_conta' => $statusConta
                ]);
                redirect('login.php');
                exit;
            }
        }

    } catch (PDOException $e) {

        error_log(
            '[REPRE] Erro PDO no cadastro: ' .
            $e->getMessage()
        );

        if ($e->getCode() === '23000') {
            $error = 'Este e-mail já está cadastrado ou os dados violam uma restrição do banco.';
        } else {
            $error = 'Não foi possível concluir o cadastro. Verifique a configuração do banco de dados.';
        }

    } catch (Throwable $e) {

        error_log(
            '[REPRE] Erro no cadastro: ' .
            get_class($e) . ': ' . $e->getMessage()
        );

        $error = 'Ocorreu um erro ao realizar o cadastro. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar Conta | REPRE</title>

    <link rel="shortcut icon" href="Images/Logo.png" type="image/png">
    <link rel="stylesheet" href="Styles/login-register.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

    <div class="bg-books" id="bgBooks" aria-hidden="true"></div>

    <header>
        <div>
            <img src="Images/Logo.png" alt="Logo REPRE" height="30">
            <a href="index.php">REPRE</a>
        </div>

        <a href="index.php" class="btn back">
            <i class="fa-solid fa-right-from-bracket"></i>
            Voltar
        </a>
    </header>

    <main>
        <div class="card">

            <h2>Crie sua conta para acessar sua turma.</h2>

            <?php if ($error !== ''): ?>
                <p
                    role="alert"
                    aria-live="polite"
                    style="color: #c0392b;">
                    <?= e($error) ?>
                </p>
            <?php endif; ?>

            <form
                action="cadastro.php"
                method="post"
                class="principal-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>">

                <label for="tipo">Tipo de conta</label>

                <select
                    name="tipo"
                    id="tipo"
                    class="form-input"
                    required>

                    <option value="">Selecione seu perfil</option>

                    <option
                        value="aluno"
                        <?= $oldTipo === 'aluno' ? 'selected' : '' ?>>
                        Aluno
                    </option>

                    <option
                        value="representante"
                        <?= $oldTipo === 'representante' ? 'selected' : '' ?>>
                        Representante
                    </option>

                </select>

                <input
                    class="form-input"
                    type="text"
                    name="name"
                    placeholder="Nome completo"
                    maxlength="120"
                    autocomplete="name"
                    value="<?= e($oldName) ?>"
                    required>

                <input
                    class="form-input"
                    type="email"
                    name="email"
                    placeholder="E-mail"
                    maxlength="150"
                    autocomplete="email"
                    value="<?= e($oldEmail) ?>"
                    required>

<div class="campo-senha">
    <input
        class="form-input"
        type="password"
        id="password"
        name="password"
        placeholder="Senha (mín. 8 caracteres)"
        minlength="8"
        maxlength="72"
        autocomplete="new-password"
        required
    >

    <button
        type="button"
        class="alternar-senha"
        id="alternarSenha"
        aria-label="Mostrar senha"
        aria-pressed="false"
    >
        <i class="fa-solid fa-eye" aria-hidden="true"></i>
    </button>
</div>


                <button
                    type="submit"
                    class="form-input btn-submit">
                    Cadastrar
                </button>

            </form>

            <div class="register-link">
                <span>Já tem uma conta?</span>
                <a href="login.php">Entrar</a>
            </div>

        </div>
    </main>

    <footer>
        © 2026 REPRE · Estude com organização
    </footer>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const campoSenha = document.getElementById('password');
    const botaoSenha = document.getElementById('alternarSenha');

    if (!campoSenha || !botaoSenha) {
        console.error('Campo de senha ou botão do olhinho não encontrado.');
        return;
    }

    const iconeSenha = botaoSenha.querySelector('i');

    botaoSenha.addEventListener('click', function () {
        const mostrar = campoSenha.type === 'password';

        campoSenha.type = mostrar ? 'text' : 'password';

        if (iconeSenha) {
            iconeSenha.classList.toggle('fa-eye', !mostrar);
            iconeSenha.classList.toggle('fa-eye-slash', mostrar);
        }

        botaoSenha.setAttribute(
            'aria-label',
            mostrar ? 'Ocultar senha' : 'Mostrar senha'
        );

        botaoSenha.setAttribute('aria-pressed', String(mostrar));
    });
});
</script>

    <script src="funcaolivros.js"></script>

</body>
</html>
```
