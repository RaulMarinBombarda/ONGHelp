<?php
session_start();
require_once '../includes/db.php';

// Se já estiver logado, redireciona direto
if (isset($_SESSION['user_id'])) {
    header('Location: perfil.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!$email || !$senha) {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_area'] = $user['area'];
            header('Location: perfil.php');
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar | ONGHelp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
</head>
<body>
<div class="auth-layout">

  <!-- ========= ESQUERDA ========= -->
  <div class="auth-left">
    <a class="auth-logo" href="../index.php">
      <div class="auth-logo-icon"><i class="bi bi-heart-fill"></i></div>
      ONGHelp
    </a>

    <div class="auth-left-body">
      <h2>Gestão e transparência para quem transforma o Brasil</h2>
      <p>Mais de 8 ONGs já utilizam o ONGHelp para publicar relatórios, captar doações e conquistar a confiança dos seus apoiadores.</p>

      <div class="auth-feature">
        <div class="auth-feature-dot"></div>
        Perfil público verificado
      </div>
      <div class="auth-feature">
        <div class="auth-feature-dot"></div>
        Portal de transparência financeira
      </div>
      <div class="auth-feature">
        <div class="auth-feature-dot"></div>
        Captação via PIX integrada
      </div>
      <div class="auth-feature">
        <div class="auth-feature-dot"></div>
        Dashboard de gestão de projetos
      </div>
    </div>

    <div class="auth-left-footer">
      &copy; 2026 ONGHelp — Projeto Acadêmico de Engenharia de Software
    </div>
  </div>

  <!-- ========= DIREITA ========= -->
  <div class="auth-right">
    <a href="../index.php" class="auth-back-link">
      <i class="bi bi-arrow-left"></i> Voltar ao site
    </a>
    <div class="auth-form-wrap">

      <div class="auth-tabs">
        <a href="login.php"    class="auth-tab active">Entrar</a>
        <a href="cadastro.php" class="auth-tab">Criar Conta</a>
      </div>

      <h3 class="fw-bold mb-1" style="font-size:1.5rem;">Bem-vindo de volta!</h3>
      <p class="text-muted mb-4" style="font-size:.88rem;">Entre com sua conta para acessar o painel da sua ONG.</p>

      <?php if ($erro): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.875rem;">
          <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">

        <div class="mb-3">
          <label class="auth-label">E-mail</label>
          <input type="email" name="email" class="auth-input"
                 placeholder="sua@ong.org.br"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-1">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="auth-label mb-0">Senha</label>
            <a href="#" style="font-size:.82rem; color:var(--cor-primaria);">Esqueceu a senha?</a>
          </div>
          <div class="auth-input-wrap">
            <input type="password" name="senha" class="auth-input" id="senhaLogin" placeholder="••••••••">
            <button class="auth-eye" type="button" onclick="toggleSenha('senhaLogin','olhoLogin')">
              <i class="bi bi-eye" id="olhoLogin"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="auth-btn mt-4">Entrar na plataforma</button>

      </form>

      <p class="text-center text-muted mt-3 mb-0" style="font-size:.875rem;">
        Ainda não tem conta?
        <a href="cadastro.php" class="fw-semibold" style="color:var(--cor-primaria);">Cadastre sua ONG gratuitamente</a>
      </p>

    </div>
  </div>

</div>

<script>
function toggleSenha(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>