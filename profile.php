<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Settings/auth.php';
require_login();
require_once __DIR__ . '/profile.php';


$usuarioId = (int) $_SESSION['user_id'];
$erro = '';
$sucesso = '';

$usuario = [
    'nome' => (string) ($_SESSION['user_name'] ?? 'Estudante'),
    'email' => (string) ($_SESSION['user_email'] ?? ''),
];

$fotoBase64 = (string) ($_SESSION['user_foto'] ?? '');
$imagemPronta = $fotoBase64 !== ''
    ? 'data:image/jpeg;base64,' . $fotoBase64
    : '';

$nomeUsuario = trim($usuario['nome']);
$inicial = function_exists('mb_substr')
    ? mb_substr($nomeUsuario !== '' ? $nomeUsuario : 'E', 0, 1, 'UTF-8')
    : substr($nomeUsuario !== '' ? $nomeUsuario : 'E', 0, 1);

try {
    $pdo = db();

    // Recupera os dados atuais do usuário.
    $stmt = $pdo->prepare(
        'SELECT id, nome, email, foto_url
         FROM alunos
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $usuarioId]);
    $dados = $stmt->fetch();

    if (!$dados) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $usuario['nome'] = (string) $dados['nome'];
    $usuario['email'] = (string) $dados['email'];

    // Recupera a foto salva no banco.
    $foto = $dados['foto_url'] ?? null;

    if (is_resource($foto)) {
        $foto = stream_get_contents($foto);
    }

    if (is_string($foto) && $foto !== '') {
        // Se o campo contém bytes da imagem, converte para Base64.
        $fotoBase64 = base64_encode($foto);
        $_SESSION['user_foto'] = $fotoBase64;
        $imagemPronta = 'data:image/jpeg;base64,' . $fotoBase64;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        if ($nome === '' || strlen($nome) > 150) {
            $erro = 'Informe um nome válido de até 150 caracteres.';
        } elseif (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 150
        ) {
            $erro = 'Informe um endereço de e-mail válido.';
        } else {
            // Verifica se o e-mail já pertence a outro usuário.
            $check = $pdo->prepare(
                'SELECT id FROM alunos
                 WHERE email = :email AND id <> :id
                 LIMIT 1'
            );
            $check->execute([
                'email' => $email,
                'id' => $usuarioId,
            ]);

            if ($check->fetch()) {
                $erro = 'Este e-mail já está sendo utilizado por outra conta.';
            } else {
                $fotoNova = null;

                if (
                    isset($_FILES['nova_foto']) &&
                    $_FILES['nova_foto']['error'] !== UPLOAD_ERR_NO_FILE
                ) {
                    if ($_FILES['nova_foto']['error'] !== UPLOAD_ERR_OK) {
                        $erro = 'Não foi possível enviar a foto.';
                    } elseif ($_FILES['nova_foto']['size'] > 2 * 1024 * 1024) {
                        $erro = 'A foto deve ter no máximo 2 MB.';
                    } else {
                        $tmp = $_FILES['nova_foto']['tmp_name'];
                        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
                        $tiposPermitidos = [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ];

                        if (!in_array($mime, $tiposPermitidos, true)) {
                            $erro = 'Envie uma imagem JPG, PNG ou WEBP.';
                        } else {
                            $conteudo = file_get_contents($tmp);

                            if ($conteudo === false) {
                                $erro = 'Não foi possível ler a foto enviada.';
                            } else {
                                $fotoNova = $conteudo;
                            }
                        }
                    }
                }

                if ($erro === '') {
                    if ($fotoNova !== null) {
                        $update = $pdo->prepare(
                            'UPDATE alunos
                             SET nome = :nome, email = :email, foto_url = :foto
                             WHERE id = :id'
                        );
                        $update->bindValue(':nome', $nome);
                        $update->bindValue(':email', $email);
                        $update->bindValue(':foto', $fotoNova, PDO::PARAM_LOB);
                        $update->bindValue(':id', $usuarioId, PDO::PARAM_INT);
                        $update->execute();

                        $fotoBase64 = base64_encode($fotoNova);
                        $_SESSION['user_foto'] = $fotoBase64;
                        $imagemPronta = 'data:' .
                            (new finfo(FILEINFO_MIME_TYPE))->buffer($fotoNova) .
                            ';base64,' . $fotoBase64;
                    } else {
                        $update = $pdo->prepare(
                            'UPDATE alunos
                             SET nome = :nome, email = :email
                             WHERE id = :id'
                        );
                        $update->execute([
                            'nome' => $nome,
                            'email' => $email,
                            'id' => $usuarioId,
                        ]);
                    }

                    $_SESSION['user_name'] = $nome;
                    $_SESSION['user_email'] = $email;

                    $usuario['nome'] = $nome;
                    $usuario['email'] = $email;

                    $inicial = function_exists('mb_substr')
                        ? mb_substr($nome, 0, 1, 'UTF-8')
                        : substr($nome, 0, 1);

                    $sucesso = 'Perfil atualizado com sucesso.';
                }
            }
        }
    }
} catch (Throwable $e) {
    error_log('[REPRE] Erro no perfil: ' . $e->getMessage());
    http_response_code(500);
    exit('Erro ao carregar ou atualizar o perfil.');
}
?>


<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#f0efff" />
  <title>Meu perfil | REPRE</title>
  <link rel="shortcut icon" href="Images/Logo.png" type="image/x-icon" />
  <link rel="stylesheet" href="Styles/root.css" />
  <link rel="stylesheet" href="Styles/profile.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
  <div id="headerSet"></div>

  <main>
    <div class="profile-header">
      <h1>Seu perfil</h1>
      <p>Confira suas informações e gerencie sua conta</p>
    </div>

    <section class="profile-grid">

      <div class="profile-card" aria-labelledby="conta-titulo">

        <div class="card-title">

          <div class="title-icon">
            <i class="fa-regular fa-id-card" aria-hidden="true"></i>
          </div>
          <div>
            <h2>Informações da conta</h2>
            <h3>Atualizar Dados</h3>
          </div>
        </div>

        <p class="card-description">Essas são as informações vinculadas ao seu perfil. Clique e altere o que desejar.</p>

<form
    id="form-avatar"
    class="update-avatar"
    action="profile.php"
    method="POST"
    enctype="multipart/form-data">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= e(csrf_token()) ?>">

          <div class="profile-photo">
            <?php if (!empty($imagemPronta)): ?>
              <img src="<?= $imagemPronta ?>" id="foto-perfil" alt="Foto de Perfil" class="profile-avatar">
            <?php else: ?>
              <div class="profile-avatar">
                <?= $inicial ?>
              </div>
            <?php endif; ?>
            <label for="nova_foto" class="select-img">
              Escolher uma Foto
            </label>
          </div>

          

          <input type="file" id="nova_foto" name="nova_foto" accept="image/*">

<label for="perfil-nome">Nome completo</label>
<div class="field-valor">
    <span class="field-icon">
        <i class="fa-regular fa-user" aria-hidden="true"></i>
    </span>
    <input
        type="text"
        id="perfil-nome"
        name="nome"
        maxlength="150"
        value="<?= e($usuario['nome']) ?>"
        required>
</div>


          <label for="perfil-email">E-mail</label>
<div class="field-valor">
    <span class="field-icon">
        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
    </span>
    <input
        type="email"
        id="perfil-email"
        name="email"
        maxlength="150"
        value="<?= e($usuario['email']) ?>"
        required>
</div>
          <input type="submit" id="btn-submeter-foto" value="Salvar Alterações" />
          <div class="nota-privacidade">
            <i class="fa-solid fa-lock" aria-hidden="true"></i>
            <span>Seus dados são usados para identificar sua conta com segurança.</span>
          </div>
<?php if ($erro !== ''): ?>
    <p role="alert"><?= e($erro) ?></p>
<?php endif; ?>

<?php if ($sucesso !== ''): ?>
    <p role="status"><?= e($sucesso) ?></p>
<?php endif; ?>
        </form>
      </div>

      <div class="profile-card security-card" aria-labelledby="seguranca-titulo">

      <div class="card-title">
        <div class="title-icon">
          <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        </div>
        <div>
          <h2 class="eyebrow">PROTEÇÃO</h2>
          <h3 id="seguranca-titulo">Segurança</h3>
        </div>
      </div>
      <p class="card-description">Mantenha sua conta protegida usando uma senha segura.</p>
      <button type="button" class="btn-card">
        <i class="fa-solid fa-key" aria-hidden="true"></i>
        Alterar senha
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
      </button>
      </div>

    <div class="profile-card exit-card" aria-labelledby="sair-titulo">
      <div class="card-title">
        <div class="title-icon sair-icone"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i></div>
        <div class=""><h2>ATÉ LOGO</h2>
          <h3 id="sair-titulo">Sair da conta</h3>
          
        </div>
      </div>
      <p class="card-description">Encerre sua sessão neste dispositivo. Você poderá entrar novamente quando quiser.</p>
<a href="Functions/logout.php" class="botao-sair">
    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
    Sair da conta
</a>
    </div>
    </section>

    

    


</main>

    <footer class="perfil-rodape"><span>REPRE</span></footer>
  
  <script src="Script/headerSet.js"></script>
  <script src="Script/profile-image.js"></script>
</body>

</html>