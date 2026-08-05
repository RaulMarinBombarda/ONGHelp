<?php
// ============================================================
// pages/perfil.php — Painel da ONG logada
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================
//
// CORREÇÕES em relação à versão anterior:
//   - Botão "Cancelar" apontava para dashboard.php (não existe).
//     Agora redireciona para a própria página de perfil.
//   - Botão de desativação agora chama desativar.php com tela
//     de confirmação própria (hard delete), sem confirm() JS.
//   - Senha exige mínimo 8 caracteres (era 6).
//   - Campos do formulário exigem o atributo required explícito.
//   - XSS: todos os valores de saída passam por htmlspecialchars().
// ============================================================

session_start();
require_once '../includes/db.php';

// ── Protege: só usuário logado acessa ────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id      = (int) $_SESSION['user_id'];
$erro    = '';
$sucesso = '';

// ── Busca dados atuais do usuário ────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND ativo = 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Se conta não existir mais (excluída por outro meio), desloga
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ── Estatísticas da organização ────────────────────────────
$statsPerfil = $pdo->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) AS total_receitas,
        COALESCE(COUNT(DISTINCT id), 0) AS total_movimentacoes,
        (SELECT COUNT(*) FROM projetos WHERE usuario_id = ?) AS total_projetos
    FROM movimentacoes WHERE usuario_id = ?
");
$statsPerfil->execute([$id, $id]);
$stats = $statsPerfil->fetch();

// ── Processa formulários (POST) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'editar') {
        $nome      = trim($_POST['nome']      ?? '');
        $email     = trim($_POST['email']     ?? '');
        $area      = trim($_POST['area']      ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $pix_chave = trim($_POST['pix_chave'] ?? '');
        $cidade    = trim($_POST['cidade']    ?? '');
        $estado    = trim($_POST['estado']    ?? '');
        if (!$nome || !$email || !$area) {
            $erro = 'Nome, e-mail e área de atuação são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } else {
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $check->execute([$email, $id]);

            if ($check->fetch()) {
                $erro = 'Este e-mail já está sendo usado por outra conta.';
            } else {
                $pdo->prepare("
                    UPDATE usuarios
                    SET nome=?, email=?, area=?, descricao=?, pix_chave=?, cidade=?, estado=?
                    WHERE id=?
                ")->execute([$nome, $email, $area, $descricao, $pix_chave, $cidade ?: null, $estado ?: null, $id]);

                $_SESSION['user_nome'] = $nome;
                $_SESSION['user_area'] = $area;
                $sucesso = 'Dados atualizados com sucesso!';

                $stmt2 = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt2->execute([$id]);
                $user = $stmt2->fetch();
            }
        }
    } elseif ($acao === 'senha') {
        $senha         = trim($_POST['senha']         ?? '');
        $senha_confirm = trim($_POST['senha_confirm'] ?? '');

        if (!$senha) {
            $erro = 'Informe a nova senha.';
        } elseif ($senha !== $senha_confirm) {
            $erro = 'As senhas não coincidem.';
        } elseif (strlen($senha) < 8) {
            $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")->execute([$hash, $id]);
            $sucesso = 'Senha atualizada com sucesso!';
        }
    }
}

$page_title = 'Meu Perfil';
$active_nav = '';
include '../includes/header.php';
?>

<section style="background:var(--cor-fundo); min-height:80vh;">

  <!-- ── CAPA DO PERFIL ── -->
  <div style="height:200px; background:linear-gradient(135deg,var(--cor-primaria),#0a4b7a);"></div>

  <div class="container pb-5" style="max-width:1320px; margin-top:-90px;">

    <!-- ── CARD DO PERFIL ── -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="overflow:hidden;">
      <div class="p-4 p-md-5">
        <div class="row g-4 align-items-end">
          <div class="col-auto">
            <div style="width:100px; height:100px; border-radius:24px;
                        background:linear-gradient(135deg,var(--cor-primaria),#0a4b7a);
                        display:flex; align-items:center; justify-content:center;
                        font-size:2.8rem; color:#fff; box-shadow:0 4px 16px rgba(13,92,143,.25);
                        border:4px solid #fff;">
              <i class="bi bi-building"></i>
            </div>
          </div>
          <div class="col">
            <h2 class="fw-bold mb-1" style="font-size:1.6rem;"><?= htmlspecialchars($user['nome']) ?></h2>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
              <span class="badge rounded-pill"
                    style="background:#E0F0FF; color:var(--cor-primaria); font-weight:600; font-size:.8rem; padding:.35em .9em;">
                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($user['area']) ?>
              </span>
              <span style="font-size:.85rem; color:var(--cor-texto-muted);">
                <i class="bi bi-person-check me-1"></i>Membro desde <?= date('Y', strtotime($user['criado_em'])) ?>
              </span>
              <span style="font-size:.85rem; color:var(--cor-texto-muted);">
                <i class="bi bi-file-text me-1"></i>CNPJ <?= htmlspecialchars($user['cnpj']) ?>
              </span>
            </div>
            <?php if (!empty($user['descricao'])): ?>
              <p style="font-size:.9rem; color:var(--cor-texto-muted); margin-top:.3rem; max-width:600px;">
                <?= htmlspecialchars($user['descricao']) ?>
              </p>
            <?php endif; ?>
          </div>
          <div class="col-auto">
            <a href="transparencia.php?modal=<?= $id ?>" class="btn btn-outline-primary rounded-pill px-3 btn-sm">
              <i class="bi bi-eye me-1"></i> Ver no Portal de Transparência
            </a>
          </div>
        </div>
      </div>

      <!-- Stats row -->
      <div style="border-top:1px solid #DCE9F4; background:#F8FAFC; padding:.8rem 2rem;">
        <div class="row text-center">
          <div class="col-4 py-2" style="border-right:1px solid #DCE9F4;">
            <div style="font-size:1.2rem; font-weight:700; color:var(--cor-primaria);">
              <?= number_format($stats['total_projetos'], 0, ',', '.') ?>
            </div>
            <div style="font-size:.78rem; color:var(--cor-texto-muted);">Projetos ativos</div>
          </div>
          <div class="col-4 py-2" style="border-right:1px solid #DCE9F4;">
            <div style="font-size:1.2rem; font-weight:700; color:var(--cor-secundaria);">
              <?= number_format($stats['total_movimentacoes'], 0, ',', '.') ?>
            </div>
            <div style="font-size:.78rem; color:var(--cor-texto-muted);">Movimentações</div>
          </div>
          <div class="col-4 py-2">
            <div style="font-size:1.2rem; font-weight:700; color:var(--cor-acento);">
              R$ <?= number_format($stats['total_receitas'], 0, ',', '.') ?>
            </div>
            <div style="font-size:.78rem; color:var(--cor-texto-muted);">Total arrecadado</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alertas de feedback -->
    <?php if ($erro): ?>
      <div class="alert alert-danger py-3 px-4 mb-4" style="font-size:.875rem; border-radius:12px;">
        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
      <div class="alert alert-success py-3 px-4 mb-4" style="font-size:.875rem; border-radius:12px;">
        <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($sucesso) ?>
      </div>
    <?php endif; ?>

    <!-- ── GRADE DE CARDS ── -->
    <div class="row g-4">

      <!-- EDITAR DADOS -->
      <div class="col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 h-100">
          <h5 class="fw-bold mb-2" style="font-size:1.1rem;">
            <i class="bi bi-pencil-square me-2" style="color:var(--cor-primaria);"></i>Editar informações
          </h5>
          <p class="text-muted mb-4" style="font-size:.85rem;">
            As informações marcadas com <span style="color:#C0392B;">*</span> são obrigatórias.
          </p>

          <form method="POST" action="">
            <input type="hidden" name="acao" value="editar">
            <div class="row g-4">
              <div class="col-12">
                <label class="auth-label">Nome da organização <span style="color:#C0392B;">*</span></label>
                <input type="text" name="nome" class="auth-input" required
                       value="<?= htmlspecialchars($user['nome']) ?>">
              </div>
              <div class="col-md-6">
                <label class="auth-label">E-mail <span style="color:#C0392B;">*</span></label>
                <input type="email" name="email" class="auth-input" required
                       value="<?= htmlspecialchars($user['email']) ?>">
              </div>
              <div class="col-md-6">
                <label class="auth-label">Área de atuação <span style="color:#C0392B;">*</span></label>
                <select name="area" class="auth-select" required>
                  <?php
                  $areas = ['Educação','Saúde','Animais','Meio Ambiente',
                            'Assistência Social','Cultura','Esporte','Outra'];
                  foreach ($areas as $op):
                    $sel = ($user['area'] === $op) ? 'selected' : '';
                  ?>
                    <option <?= $sel ?>><?= htmlspecialchars($op) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="auth-label">Descrição / Missão</label>
                <textarea name="descricao" class="auth-input" rows="3"
                          placeholder="Conte um pouco sobre a história, o propósito e o impacto da sua organização…"
                          style="resize:vertical;"><?= htmlspecialchars($user['descricao'] ?? '') ?></textarea>
                <small class="text-muted" style="font-size:.78rem;">
                  Aparece no Portal de Transparência. Use para sensibilizar doadores.
                </small>
              </div>
              <div class="col-12">
                <label class="auth-label">Chave PIX</label>
                <input type="text" name="pix_chave" class="auth-input"
                       placeholder="E-mail, CPF, CNPJ ou telefone"
                       value="<?= htmlspecialchars($user['pix_chave'] ?? '') ?>">
                <small class="text-muted" style="font-size:.78rem;">
                  Chave pública para receber doações via PIX no Portal de Transparência.
                </small>
              </div>
              <div class="col-md-6">
                <label class="auth-label">Cidade</label>
                <input type="text" name="cidade" class="auth-input"
                       placeholder="Ex: Taquaritinga"
                       value="<?= htmlspecialchars($user['cidade'] ?? '') ?>">
              </div>
              <div class="col-md-6">
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
                  $estadoAtual = $user['estado'] ?? '';
                  foreach ($estados as $sigla => $nomeEstado):
                    $sel = $estadoAtual === $sigla ? 'selected' : '';
                  ?>
                    <option value="<?= $sigla ?>" <?= $sel ?>><?= $nomeEstado ?> (<?= $sigla ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="auth-label">CNPJ</label>
                <input type="text" class="auth-input"
                       value="<?= htmlspecialchars($user['cnpj']) ?>"
                       disabled style="opacity:.6; cursor:not-allowed;">
                <small class="text-muted" style="font-size:.78rem;">
                  Não pode ser alterado após o cadastro.
                </small>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3" style="border-top:1px solid #DCE9F4;">
              <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-check-lg me-1"></i> Salvar alterações
              </button>
              <a href="perfil.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-x-lg me-1"></i> Descartar
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- COLUNA DIREITA -->
      <div class="col-lg-5 d-flex flex-column gap-4">

        <!-- SEGURANÇA -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5">
          <h6 class="fw-bold mb-3" style="font-size:.95rem;">
            <i class="bi bi-lock me-2" style="color:var(--cor-primaria);"></i>Segurança
          </h6>
          <p class="text-muted mb-3" style="font-size:.82rem;">
            Deixe os campos em branco para manter a senha atual.
          </p>
          <form method="POST" action="">
            <input type="hidden" name="acao" value="senha">
            <div class="row g-3">
              <div class="col-12">
                <label class="auth-label" style="font-size:.82rem;">Nova senha</label>
                <div class="auth-input-wrap">
                  <input type="password" name="senha" class="auth-input"
                         id="novaSenha" placeholder="Mínimo 8 caracteres" style="font-size:.85rem;">
                  <button class="auth-eye" type="button"
                          onclick="toggleSenha('novaSenha','olhoNova')">
                    <i class="bi bi-eye" id="olhoNova"></i>
                  </button>
                </div>
              </div>
              <div class="col-12">
                <label class="auth-label" style="font-size:.82rem;">Confirmar nova senha</label>
                <div class="auth-input-wrap">
                  <input type="password" name="senha_confirm" class="auth-input"
                         id="confirmSenha" placeholder="Repita a nova senha" style="font-size:.85rem;">
                  <button class="auth-eye" type="button"
                          onclick="toggleSenha('confirmSenha','olhoConfirm')">
                    <i class="bi bi-eye" id="olhoConfirm"></i>
                  </button>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3 w-100">
                  <i class="bi bi-check me-1"></i> Atualizar senha
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- ENCERRAMENTO -->
        <div class="card border-0 rounded-4 p-4 p-md-5"
             style="border:1.5px solid #fde8e8 !important; background:#fffafa;">
          <h6 class="fw-bold mb-2" style="font-size:.9rem; color:#C0392B;">
            <i class="bi bi-shield-exclamation me-2"></i>Encerramento da conta
          </h6>
          <p class="text-muted mb-3" style="font-size:.82rem;">
            Todos os dados serão removidos permanentemente: perfil, movimentações e projetos.
            <strong>Ação irreversível.</strong>
          </p>
          <a href="desativar.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 w-100">
            <i class="bi bi-trash me-1"></i> Solicitar exclusão
          </a>
        </div>

      </div>
    </div>

  </div>
</section>

<script>
/** Alterna visibilidade do campo de senha */
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

<?php include '../includes/footer.php'; ?>
