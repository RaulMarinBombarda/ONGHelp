<?php
// ============================================================
// pages/desativar.php — Desativação de conta (hard delete)
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================
//
// MUDANÇA EM RELAÇÃO À VERSÃO ANTERIOR:
//   A versão original fazia apenas soft-delete (ativo = 0).
//   Esta versão faz hard-delete: apaga o registro de usuarios
//   e, por CASCADE no banco, remove também movimentacoes e
//   projetos vinculados à ONG.
//
// FLUXO:
//   GET  → exibe tela de confirmação com formulário
//   POST → executa o DELETE e encerra a sessão
//
// Exige sessão ativa — redireciona para login caso contrário.
// ============================================================

session_start();
require_once '../includes/db.php';

// ── Protege: só usuário logado acessa ────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id    = (int) $_SESSION['user_id'];
$erro  = '';

// ── POST: executa exclusão permanente ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Dupla verificação: o usuário confirmou no formulário
    if (!isset($_POST['confirmar'])) {
        $erro = 'Por favor, marque a caixa de confirmação para continuar.';
    } else {
        // DELETE em cascata: remove também movimentacoes e projetos
        // (graças às FOREIGN KEY ... ON DELETE CASCADE no SQL)
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);

        // Encerra a sessão por completo
        session_unset();
        session_destroy();

        // Redireciona para home com aviso
        header('Location: ../index.php?conta=excluida');
        exit;
    }
}

// ── GET: busca nome da ONG para exibir na tela ────────────────
$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ? AND ativo = 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Se o usuário não for encontrado (conta já excluída), desloga
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$page_title = 'Excluir Conta';
$active_nav = '';
include '../includes/header.php';
?>

<section class="py-5" style="background:var(--cor-fundo); min-height:80vh;">
  <div class="container py-3" style="max-width:600px;">

    <!-- Ícone e título -->
    <div class="text-center mb-4">
      <div style="width:72px; height:72px; border-radius:18px; background:#FFE5E5;
                  color:#C0392B; font-size:2rem; display:flex; align-items:center;
                  justify-content:center; margin:0 auto 1.25rem;">
        <i class="bi bi-heartbreak-fill"></i>
      </div>
      <h2 class="fw-bold mb-1" style="color:#C0392B;">Que pena ver você ir!</h2>
      <p class="text-muted" style="font-size:.9rem;">
        Pedimos que leia com atenção antes de confirmar.
      </p>
    </div>

    <?php if ($erro): ?>
      <div class="alert alert-danger py-2 px-3 mb-4" style="font-size:.875rem;">
        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <!-- Card de aviso -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4"
         style="border:1.5px solid #fde8e8 !important; background:#fffafa;">

      <p style="font-size:.9rem;">
        Você está solicitando o encerramento da conta de
        <strong><?= htmlspecialchars($user['nome']) ?></strong>.
        Se confirmar, todos os dados abaixo serão <strong>definitivamente removidos</strong>
        — não será possível recuperá-los no futuro:
      </p>

      <ul style="font-size:.875rem; color:var(--cor-texto-muted);">
        <li>O perfil público da sua organização</li>
        <li>O histórico de receitas e despesas registradas</li>
        <li>Os projetos e suas metas cadastradas</li>
      </ul>

      <p style="font-size:.875rem; color:var(--cor-texto-muted);">
        Se ficou com alguma dúvida ou prefere conversar antes de tomar essa decisão,
        fique à vontade para nos procurar. Teremos satisfação em ajudar.
      </p>

      <!-- Formulário de confirmação -->
      <form method="POST" action="">
        <div class="mb-4 d-flex align-items-center gap-2">
          <input type="checkbox" name="confirmar" id="confirmar"
                 style="width:16px; height:16px; accent-color:#C0392B; cursor:pointer;">
          <label for="confirmar" style="font-size:.875rem; color:var(--cor-texto); cursor:pointer;">
            Sim, estou ciente e desejo encerrar a conta definitivamente.
          </label>
        </div>

        <div class="d-flex gap-2 justify-content-center">
          <a href="perfil.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Voltar
          </a>
          <button type="submit" class="btn btn-danger rounded-pill px-4">
            <i class="bi bi-trash me-1"></i> Sim, encerrar minha conta
          </button>
        </div>
      </form>
    </div>

  </div>
</section>

<?php include '../includes/footer.php'; ?>
