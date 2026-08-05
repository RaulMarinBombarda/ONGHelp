<?php
// ============================================================
// pages/gestao.php — Gestão financeira e de projetos
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================

session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// ── Protege: só usuário logado ────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id      = (int) $_SESSION['user_id'];
$nome    = $_SESSION['user_nome'];
$erro    = '';
$sucesso = '';

// ── Processa formulários ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ── Nova movimentação ──
    if ($acao === 'add_mov') {
        $tipo     = $_POST['tipo'] ?? '';
        $descricao = trim($_POST['descricao'] ?? '');
        $valor    = valorParaDecimal($_POST['valor'] ?? '0');
        $data_mov = $_POST['data_mov'] ?? '';

        if (!in_array($tipo, ['receita', 'despesa'])) {
            $erro = 'Selecione o tipo (receita ou despesa).';
        } elseif ($descricao === '') {
            $erro = 'Informe a descrição da movimentação.';
        } elseif ($valor <= 0) {
            $erro = 'Informe um valor maior que zero.';
        } elseif ($data_mov === '') {
            $erro = 'Informe a data.';
        } else {
            $pdo->prepare("INSERT INTO movimentacoes (usuario_id, tipo, descricao, valor, data_mov) VALUES (?, ?, ?, ?, ?)")
                ->execute([$id, $tipo, $descricao, $valor, $data_mov]);
            $sucesso = 'Movimentação cadastrada com sucesso!';
        }
    }

    // ── Excluir movimentação ──
    elseif ($acao === 'del_mov') {
        $mov_id = (int) ($_POST['mov_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM movimentacoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$mov_id, $id]);
        $sucesso = $stmt->rowCount() > 0
            ? 'Movimentação excluída.'
            : 'Movimentação não encontrada.';
    }

    // ── Novo projeto ──
    elseif ($acao === 'add_proj') {
        $nomeProj  = trim($_POST['nome'] ?? '');
        $indicador = (int) ($_POST['indicador'] ?? 0);
        $unidade   = trim($_POST['unidade'] ?? '');

        if ($nomeProj === '') {
            $erro = 'Informe o nome do projeto.';
        } elseif ($unidade === '') {
            $erro = 'Informe o que aparece junto do número (ex: escolas parceiras).';
        } else {
            $pdo->prepare("INSERT INTO projetos (usuario_id, nome, indicador, unidade) VALUES (?, ?, ?, ?)")
                ->execute([$id, $nomeProj, $indicador, $unidade]);
            $sucesso = 'Projeto cadastrado com sucesso!';
        }
    }

    // ── Excluir projeto ──
    elseif ($acao === 'del_proj') {
        $proj_id = (int) ($_POST['proj_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM projetos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$proj_id, $id]);
        $sucesso = $stmt->rowCount() > 0
            ? 'Projeto excluído.'
            : 'Projeto não encontrado.';
    }
}

// ── Busca dados existentes ────────────────────────────────
$movimentacoes = $pdo->prepare("
    SELECT * FROM movimentacoes
    WHERE usuario_id = ?
    ORDER BY data_mov DESC, id DESC
    LIMIT 100
");
$movimentacoes->execute([$id]);
$movimentacoes = $movimentacoes->fetchAll();

$projetos = $pdo->prepare("
    SELECT * FROM projetos
    WHERE usuario_id = ?
    ORDER BY nome ASC
");
$projetos->execute([$id]);
$projetos = $projetos->fetchAll();

// ── HTML ──────────────────────────────────────────────────
$page_title = 'Gestão';
$active_nav = 'gestao';
include '../includes/header.php';
?>

<section style="background:var(--cor-fundo); min-height:80vh;">
  <div class="container py-4" style="max-width:1320px;">

    <!-- Cabeçalho -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
      <div>
        <span class="badge-secao">Painel da <?= htmlspecialchars($nome) ?></span>
        <h2 class="fw-bold mt-1 mb-0">Gestão Financeira e Projetos</h2>
        <p class="text-muted" style="font-size:.9rem;">
          Cadastre as receitas e despesas da sua organização e gerencie seus projetos ativos.
        </p>
      </div>
      <a href="transparencia.php" class="btn btn-outline-primary rounded-pill px-3 btn-sm">
        <i class="bi bi-eye me-1"></i> Ver no Portal de Transparência
      </a>
    </div>

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

    <div class="row g-4">

      <!-- ============================================================
           COLUNA ESQUERDA — Movimentações
           ============================================================ -->
      <div class="col-lg-7">

        <!-- Formulário de nova movimentação -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4">
          <h5 class="fw-bold mb-3" style="font-size:1.05rem;">
            <i class="bi bi-cash-stack me-2" style="color:var(--cor-secundaria);"></i>Nova movimentação
          </h5>

          <form method="POST" action="">
            <input type="hidden" name="acao" value="add_mov">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="auth-label" style="font-size:.85rem;">Tipo</label>
                <select name="tipo" class="auth-select" required>
                  <option value="receita">Receita</option>
                  <option value="despesa">Despesa</option>
                </select>
              </div>
              <div class="col-md-5">
                <label class="auth-label" style="font-size:.85rem;">Descrição</label>
                <input type="text" name="descricao" class="auth-input" required
                       placeholder="Ex: Doação via PIX">
              </div>
              <div class="col-md-3">
                <label class="auth-label" style="font-size:.85rem;">Valor (R$)</label>
                <input type="text" name="valor" class="auth-input" required
                       placeholder="1.200,00">
              </div>
              <div class="col-md-6">
                <label class="auth-label" style="font-size:.85rem;">Data</label>
                <input type="date" name="data_mov" class="auth-input" required
                       value="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                  <i class="bi bi-plus-lg me-1"></i> Cadastrar
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Lista de movimentações -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5">
          <h5 class="fw-bold mb-3" style="font-size:1.05rem;">
            <i class="bi bi-list-ul me-2" style="color:var(--cor-primaria);"></i>Movimentações recentes
            <span class="text-muted fw-normal" style="font-size:.8rem;">(<?= count($movimentacoes) ?>)</span>
          </h5>

          <?php if (empty($movimentacoes)): ?>
            <p class="text-muted" style="font-size:.88rem;">
              Nenhuma movimentação cadastrada.
            </p>
          <?php else:
            $totalReceitas = array_sum(array_column(array_filter($movimentacoes, fn($m) => $m['tipo'] === 'receita'), 'valor'));
            $totalDespesas = array_sum(array_column(array_filter($movimentacoes, fn($m) => $m['tipo'] === 'despesa'), 'valor'));
          ?>
          <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; font-size:.82rem;">
            <span><strong style="color:#00915E;">+ <?= fmtDinheiro((float)$totalReceitas) ?></strong> em receitas</span>
            <span><strong style="color:#C0392B;">- <?= fmtDinheiro((float)$totalDespesas) ?></strong> em despesas</span>
          </div>
          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:.88rem;">
              <thead>
                <tr style="border-bottom:2px solid #DCE9F4;">
                  <th style="padding:.5rem .75rem; text-align:left; color:var(--cor-texto-muted); font-weight:600;">Data</th>
                  <th style="padding:.5rem .75rem; text-align:left; color:var(--cor-texto-muted); font-weight:600;">Descrição</th>
                  <th style="padding:.5rem .75rem; text-align:left; color:var(--cor-texto-muted); font-weight:600;">Tipo</th>
                  <th style="padding:.5rem .75rem; text-align:right; color:var(--cor-texto-muted); font-weight:600;">Valor</th>
                  <th style="padding:.5rem .75rem; text-align:center; color:var(--cor-texto-muted); font-weight:600;">Ação</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($movimentacoes as $mov): ?>
                <tr style="border-bottom:1px solid #E8EFF5;">
                  <td style="padding:.6rem .75rem;"><?= fmtData($mov['data_mov']) ?></td>
                  <td style="padding:.6rem .75rem;"><?= htmlspecialchars($mov['descricao']) ?></td>
                  <td style="padding:.6rem .75rem;">
                    <?php if ($mov['tipo'] === 'receita'): ?>
                      <span class="tipo-receita" style="font-size:.8rem;">
                        <i class="bi bi-arrow-down-circle-fill"></i> Receita
                      </span>
                    <?php else: ?>
                      <span class="tipo-despesa" style="font-size:.8rem;">
                        <i class="bi bi-arrow-up-circle-fill"></i> Despesa
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:.6rem .75rem; text-align:right; font-weight:600;
                              color:<?= $mov['tipo'] === 'receita' ? '#00915E' : '#C0392B' ?>;">
                    <?= $mov['tipo'] === 'receita' ? '+' : '-' ?> <?= fmtDinheiro((float)$mov['valor']) ?>
                  </td>
                  <td style="padding:.6rem .75rem; text-align:center;">
                    <form method="POST" action="" style="display:inline;">
                      <input type="hidden" name="acao" value="del_mov">
                      <input type="hidden" name="mov_id" value="<?= $mov['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-excluir"
                              style="color:#C0392B; border:none; background:none; padding:0; font-size:.85rem;">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- ============================================================
           COLUNA DIREITA — Projetos
           ============================================================ -->
      <div class="col-lg-5">

        <!-- Formulário de novo projeto -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4">
          <h5 class="fw-bold mb-3" style="font-size:1.05rem;">
            <i class="bi bi-kanban me-2" style="color:var(--cor-acento);"></i>Novo projeto
          </h5>

          <form method="POST" action="">
            <input type="hidden" name="acao" value="add_proj">
            <div class="row g-3">
              <div class="col-12">
                <label class="auth-label" style="font-size:.85rem;">Nome do projeto</label>
                <input type="text" name="nome" class="auth-input" required
                       placeholder="Ex: Reflorestamento">
              </div>
              <div class="col-md-6">
                <label class="auth-label" style="font-size:.85rem;">Número do resultado</label>
                <input type="number" name="indicador" class="auth-input"
                       value="0" min="0" step="1">
              </div>
              <div class="col-md-6">
                <label class="auth-label" style="font-size:.85rem;">Descrição do número</label>
                <input type="text" name="unidade" class="auth-input" required
                       placeholder="Ex: mudas plantadas">
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                  <i class="bi bi-plus-lg me-1"></i> Cadastrar projeto
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Lista de projetos -->
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5">
          <h5 class="fw-bold mb-3" style="font-size:1.05rem;">
            <i class="bi bi-list-check me-2" style="color:var(--cor-secundaria);"></i>Projetos ativos
          </h5>

          <?php if (empty($projetos)): ?>
            <p class="text-muted" style="font-size:.88rem;">
              Nenhum projeto cadastrado.
            </p>
          <?php else: ?>
            <?php foreach ($projetos as $proj): ?>
            <div style="display:flex; align-items:flex-start; gap:.75rem; padding:.7rem 0;
                        border-bottom:1px solid #E8EFF5;">
              <div style="flex:1;">
                <div style="font-size:1.2rem; font-weight:700; color:var(--cor-primaria);">
                  <?= number_format($proj['indicador'], 0, ',', '.') ?>
                </div>
                <div style="font-size:.85rem; color:var(--cor-texto-muted); margin-bottom:.2rem;">
                  <?= htmlspecialchars($proj['unidade']) ?>
                </div>
                <div style="font-weight:600; font-size:.9rem;">
                  <?= htmlspecialchars($proj['nome']) ?>
                </div>
              </div>
              <form method="POST" action="" style="display:inline;">
                <input type="hidden" name="acao" value="del_proj">
                <input type="hidden" name="proj_id" value="<?= $proj['id'] ?>">
                <button type="submit" class="btn btn-sm btn-excluir"
                        style="color:#C0392B; border:none; background:none; padding:0; font-size:.9rem;">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>

    </div>

  </div>
</section>

<!-- Modal de confirmação para exclusões -->
<div class="modal fade" id="modalConfirmar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirmar</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="font-size:.9rem;">
        Tem certeza que deseja excluir? Esta ação não pode ser desfeita.
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" id="btnConfirmarExclusao">
          <i class="bi bi-trash me-1"></i>Excluir
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var formExcluir = null;
  var modalEl = document.getElementById('modalConfirmar');
  var modal = new bootstrap.Modal(modalEl);

  document.querySelectorAll('.btn-excluir').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      formExcluir = this.closest('form');
      modal.show();
    });
  });

  document.getElementById('btnConfirmarExclusao').addEventListener('click', function () {
    if (formExcluir) {
      modal.hide();
      formExcluir.submit();
    }
  });
});
</script>

<?php include '../includes/footer.php'; ?>
