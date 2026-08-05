<?php
require_once '../includes/db.php';

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome']      ?? '');
    $cnpj      = trim($_POST['cnpj']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $senha     = trim($_POST['senha']     ?? '');
    $area      = trim($_POST['area']      ?? '');
    $pix_chave = trim($_POST['pix_chave'] ?? '');
    $cidade    = trim($_POST['cidade']    ?? '');
    $estado    = trim($_POST['estado']    ?? '');
    $termos    = isset($_POST['termos']);

    if (!$nome || !$cnpj || !$email || !$senha || !$area) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif (!$termos) {
        $erro = 'Você precisa aceitar os Termos de Uso e a Política de Privacidade.';
    } else {
        // Verifica se e-mail ou CNPJ já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR cnpj = ?");
        $stmt->execute([$email, $cnpj]);

        if ($stmt->fetch()) {
            $erro = 'E-mail ou CNPJ já cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare("INSERT INTO usuarios (nome, cnpj, email, senha, area, pix_chave, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$nome, $cnpj, $email, $hash, $area, $pix_chave ?: null, $cidade ?: null, $estado ?: null]);
            $sucesso = 'Conta criada com sucesso! Você já pode fazer login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Conta | ONGHelp</title>
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
        <a href="login.php"    class="auth-tab">Entrar</a>
        <a href="cadastro.php" class="auth-tab active">Criar Conta</a>
      </div>

      <h3 class="fw-bold mb-1" style="font-size:1.5rem;">Cadastre sua ONG!</h3>
      <p class="text-muted mb-4" style="font-size:.88rem;">Preencha os dados abaixo para criar sua conta gratuitamente.</p>

      <?php if ($erro): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.875rem;">
          <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <?php if ($sucesso): ?>
        <div class="alert alert-success py-2 px-3 mb-3" style="font-size:.875rem;">
          <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($sucesso) ?>
          <br><a href="login.php" class="fw-semibold" style="color:inherit;">Clique aqui para entrar →</a>
        </div>
      <?php endif; ?>

      <form method="POST" action="">

        <div class="mb-3">
          <label class="auth-label">Nome da ONG</label>
          <input type="text" name="nome" class="auth-input"
                 placeholder="Ex: Instituto Esperança"
                 value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="auth-label">CNPJ</label>
          <input type="text" name="cnpj" class="auth-input"
                 placeholder="00.000.000/0001-00"
                 value="<?= htmlspecialchars($_POST['cnpj'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="auth-label">E-mail</label>
          <input type="email" name="email" class="auth-input"
                 placeholder="sua@ong.org.br"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="auth-label">Senha</label>
          <div class="auth-input-wrap">
            <input type="password" name="senha" class="auth-input" id="senhaCad" placeholder="••••••••">
            <button class="auth-eye" type="button" onclick="toggleSenha('senhaCad','olhoCad')">
              <i class="bi bi-eye" id="olhoCad"></i>
            </button>
          </div>
        </div>

        <div class="mb-3">
          <label class="auth-label">Área de atuação</label>
          <select name="area" class="auth-select">
            <option value="" disabled <?= empty($_POST['area']) ? 'selected' : '' ?>>Selecione uma área</option>
            <?php
            $areas = ['Educação','Saúde','Animais','Meio Ambiente','Assistência Social','Cultura','Esporte','Outra'];
            foreach ($areas as $op):
              $sel = (($_POST['area'] ?? '') === $op) ? 'selected' : '';
            ?>
              <option <?= $sel ?>><?= $op ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="auth-label">Chave PIX para doações</label>
          <input type="text" name="pix_chave" class="auth-input"
                 placeholder="E-mail, CPF, CNPJ ou telefone"
                 value="<?= htmlspecialchars($_POST['pix_chave'] ?? '') ?>">
          <small class="text-muted" style="font-size:.78rem;">
            Sua chave aparecerá no portal de transparência para receber doações.
          </small>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-7">
            <label class="auth-label">Cidade</label>
            <input type="text" name="cidade" class="auth-input"
                   placeholder="Ex: Taquaritinga"
                   value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>">
          </div>
          <div class="col-md-5">
            <label class="auth-label">Estado</label>
            <select name="estado" class="auth-select">
              <option value="">Selecione</option>
              <?php
              $estados = [
                'AC'=>'Acre','AL'=>'Alagoas','AP'=>'Amapá','AM'=>'Amazonas',
                'BA'=>'Bahia','CE'=>'Ceará','DF'=>'Distrito Federal',
                'ES'=>'Espírito Santo','GO'=>'Goiás','MA'=>'Maranhão',
                'MT'=>'Mato Grosso','MS'=>'Mato Grosso do Sul','MG'=>'Minas Gerais',
                'PA'=>'Pará','PB'=>'Paraíba','PR'=>'Paraná','PE'=>'Pernambuco',
                'PI'=>'Piauí','RJ'=>'Rio de Janeiro','RN'=>'Rio Grande do Norte',
                'RS'=>'Rio Grande do Sul','RO'=>'Rondônia','RR'=>'Roraima',
                'SC'=>'Santa Catarina','SP'=>'São Paulo','SE'=>'Sergipe',
                'TO'=>'Tocantins',
              ];
              foreach ($estados as $sigla => $nomeEstado):
                $sel = ($_POST['estado'] ?? '') === $sigla ? 'selected' : '';
              ?>
                <option value="<?= $sigla ?>" <?= $sel ?>><?= $nomeEstado ?> (<?= $sigla ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mb-3 d-flex align-items-center gap-2">
          <input type="checkbox" name="termos" id="termos"
                 style="width:16px; height:16px; accent-color:var(--cor-primaria); cursor:pointer;"
                 <?= isset($_POST['termos']) ? 'checked' : '' ?>>
          <label for="termos" class="auth-check-label mb-0">
            Concordo com os <a href="termos.php">Termos de Uso</a> e a <a href="privacidade.php">Política de Privacidade</a> do ONGHelp.
          </label>
        </div>

        <button type="submit" class="auth-btn">Criar conta gratuitamente</button>

      </form>

      <p class="text-center text-muted mt-3 mb-0" style="font-size:.875rem;">
        Já tem uma conta?
        <a href="login.php" class="fw-semibold" style="color:var(--cor-primaria);">Entrar</a>
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