<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id      = (int) $_SESSION['user_id'];
$erro    = '';
$sucesso = '';

$userSt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ? AND ativo = 1");
$userSt->execute([$id]);
$user = $userSt->fetch();
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

// ── Processa formulários ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar' || $acao === 'editar') {
        $tipo      = $_POST['tipo'] ?? '';
        $descricao = trim($_POST['descricao'] ?? '');
        $valor     = str_replace([',', '.'], ['', '.'], $_POST['valor'] ?? '0');
        $data_mov  = $_POST['data_mov'] ?? '';

        if (!$tipo || !$descricao || !$valor || !$data_mov) {
            $erro = 'Preencha todos os campos.';
        } elseif (!in_array($tipo, ['receita', 'despesa'])) {
            $erro = 'Tipo inválido.';
        } elseif ((float)$valor <= 0) {
            $erro = 'O valor deve ser maior que zero.';
        } else {
            if ($acao === 'adicionar') {
                $pdo->prepare("INSERT INTO movimentacoes (usuario_id, tipo, descricao, valor, data_mov) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$id, $tipo, $descricao, (float)$valor, $data_mov]);
                $sucesso = 'Movimentação cadastrada com sucesso!';
            } else {
                $movId = (int)($_POST['mov_id'] ?? 0);
                $check = $pdo->prepare("SELECT id FROM movimentacoes WHERE id = ? AND usuario_id = ?");
                $check->execute([$movId, $id]);
                if ($check->fetch()) {
                    $pdo->prepare("UPDATE movimentacoes SET tipo=?, descricao=?, valor=?, data_mov=? WHERE id=?")
                        ->execute([$tipo, $descricao, (float)$valor, $data_mov, $movId]);
                    $sucesso = 'Movimentação atualizada com sucesso!';
                } else {
                    $erro = 'Movimentação não encontrada.';
                }
            }
        }
    } elseif ($acao === 'excluir') {
        $movId = (int)($_POST['mov_id'] ?? 0);
        $pdo->prepare("DELETE FROM movimentacoes WHERE id = ? AND usuario_id = ?")->execute([$movId, $id]);
        $sucesso = 'Movimentação excluída.';
    }
}

// ── Busca movimentações ──
$movs = $pdo->prepare("SELECT * FROM movimentacoes WHERE usuario_id = ? ORDER BY data_mov DESC, id DESC");
$movs->execute([$id]);
$movimentacoes = $movs->fetchAll();

// ── Totais ──
$totais = $pdo->prepare("
    SELECT tipo, COALESCE(SUM(valor), 0) AS total FROM movimentacoes WHERE usuario_id = ? GROUP BY tipo
");
$totais->execute([$id]);
$saldos = ['receita' => 0, 'despesa' => 0];
foreach ($totais->fetchAll() as $r) {
    $saldos[$r['tipo']] = (float)$r['total'];
}
$saldo = $saldos['receita'] - $saldos['despesa'];

$page_title = 'Movimentações';
$active_nav = 'movimentacoes';
include '../includes/header.php';
?>

<section style="background:var(--cor-fundo); min-height:80vh;">

  <div style="height:140px; background:linear-gradient(135deg,var(--cor-primaria),#0a4b7a);"></div>

  <div class="container pb-5" style="max-width:1320px; margin-top:-60px;">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
      <div>
        <h4 class="fw-bold mb-1 text-white" style="text-shadow:0 1px 4px rgba(0,0,0,.2);">Movimentações Financeiras</h4>
        <p class="mb-0" style="font-size:.85rem; color:rgba(255,255,255,.75);">
          Registre receitas e despesas da sua organização.
        </p>
      </div>
      <button class="btn btn-light rounded-pill px-3 btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalMov">
        <i class="bi bi-plus-lg me-1"></i> Nova movimentação
      </button>
    </div>

    <!-- Alertas -->
    <?php if ($erro): ?>
      <div class="alert alert-danger py-3 px-4 mb-4" style="border-radius:12px; font-size:.875rem;">
        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
      <div class="alert alert-success py-3 px-4 mb-4" style="border-radius:12px; font-size:.875rem;">
        <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($sucesso) ?>
      </div>
    <?php endif; ?>

    <!-- Cards de saldo -->
    <div class="row g-3 mb-4">
      <div class="col-sm-4">
        <div class="card border-0 rounded-4 shadow-sm p-3 text-center">
          <div style="font-size:.78rem; color:var(--cor-texto-muted); text-transform:uppercase; letter-spacing:.4px;">Receitas</div>
          <div style="font-size:1.3rem; font-weight:700; color:var(--cor-secundaria);">
            R$ <?= number_format($saldos['receita'], 2, ',', '.') ?>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card border-0 rounded-4 shadow-sm p-3 text-center">
          <div style="font-size:.78rem; color:var(--cor-texto-muted); text-transform:uppercase; letter-spacing:.4px;">Despesas</div>
          <div style="font-size:1.3rem; font-weight:700; color:#C0392B;">
            R$ <?= number_format($saldos['despesa'], 2, ',', '.') ?>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card border-0 rounded-4 shadow-sm p-3 text-center">
          <div style="font-size:.78rem; color:var(--cor-texto-muted); text-transform:uppercase; letter-spacing:.4px;">Saldo</div>
          <div style="font-size:1.3rem; font-weight:700; color:<?= $saldo >= 0 ? 'var(--cor-secundaria)' : '#C0392B' ?>;">
            <?= $saldo >= 0 ? '+ ' : '- ' ?>R$ <?= number_format(abs($saldo), 2, ',', '.') ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela -->
    <div class="card border-0 rounded-4 shadow-sm">
      <div class="p-4 pb-0">
        <h6 class="fw-bold mb-0" style="font-size:.95rem;">
          <i class="bi bi-list-ul me-2" style="color:var(--cor-primaria);"></i>Histórico de movimentações
        </h6>
      </div>
      <div class="p-0">
        <?php if (empty($movimentacoes)): ?>
          <div class="p-4 text-center text-muted" style="font-size:.9rem;">
            Nenhuma movimentação cadastrada ainda.
          </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="tabela-mov" style="min-width:600px;">
            <thead>
              <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Tipo</th>
                <th style="text-align:right;">Valor</th>
                <th style="text-align:center; width:80px;">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($movimentacoes as $mov): ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($mov['data_mov'])) ?></td>
                <td><?= htmlspecialchars($mov['descricao']) ?></td>
                <td>
                  <?php if ($mov['tipo'] === 'receita'): ?>
                    <span class="tipo-receita"><i class="bi bi-arrow-down-circle-fill"></i> Receita</span>
                  <?php else: ?>
                    <span class="tipo-despesa"><i class="bi bi-arrow-up-circle-fill"></i> Despesa</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:right; font-weight:600; color:<?= $mov['tipo'] === 'receita' ? '#00915E' : '#C0392B' ?>;">
                  <?= ($mov['tipo'] === 'receita' ? '+ ' : '- ') ?>R$ <?= number_format((float)$mov['valor'], 2, ',', '.') ?>
                </td>
                <td style="text-align:center;">
                  <button class="btn btn-sm btn-outline-primary px-2 py-0 me-1" style="font-size:.78rem;"
                          onclick="editar(<?= $mov['id'] ?>, '<?= $mov['tipo'] ?>', '<?= htmlspecialchars($mov['descricao'], ENT_QUOTES) ?>', '<?= number_format((float)$mov['valor'], 2, '.', '') ?>', '<?= $mov['data_mov'] ?>')">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Excluir esta movimentação?');">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="mov_id" value="<?= $mov['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-0" style="font-size:.78rem;">
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

  </div>
</section>

<!-- Modal Adicionar/Editar -->
<div class="modal fade" id="modalMov" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-transp">
      <form method="POST" action="">
        <input type="hidden" name="acao" id="movAcao" value="adicionar">
        <input type="hidden" name="mov_id" id="movId" value="0">
        <div class="modal-header">
          <h5 class="modal-title fw-bold mb-0" id="movTitulo">Nova movimentação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="auth-label">Tipo</label>
              <select name="tipo" id="movTipo" class="auth-select" required>
                <option value="receita">Receita</option>
                <option value="despesa">Despesa</option>
              </select>
            </div>
            <div class="col-12">
              <label class="auth-label">Descrição</label>
              <input type="text" name="descricao" id="movDesc" class="auth-input" required
                     placeholder="Ex: Doação via PIX — campanha mensal">
            </div>
            <div class="col-md-6">
              <label class="auth-label">Valor (R$)</label>
              <input type="text" name="valor" id="movValor" class="auth-input" required
                     placeholder="0,00" inputmode="decimal">
            </div>
            <div class="col-md-6">
              <label class="auth-label">Data</label>
              <input type="date" name="data_mov" id="movData" class="auth-input" required>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #DCE9F4;">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-check-lg me-1"></i> Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editar(id, tipo, descricao, valor, data) {
  document.getElementById('movAcao').value = 'editar';
  document.getElementById('movId').value = id;
  document.getElementById('movTitulo').textContent = 'Editar movimentação';
  document.getElementById('movTipo').value = tipo;
  document.getElementById('movDesc').value = descricao;
  document.getElementById('movValor').value = valor.replace('.', ',');
  document.getElementById('movData').value = data;
  new bootstrap.Modal(document.getElementById('modalMov')).show();
}
// Reset do modal ao fechar
document.getElementById('modalMov').addEventListener('hidden.bs.modal', function () {
  if (document.getElementById('movAcao').value === 'editar') {
    document.getElementById('movAcao').value = 'adicionar';
    document.getElementById('movId').value = '0';
    document.getElementById('movTitulo').textContent = 'Nova movimentação';
    document.getElementById('movValor').value = '';
    document.getElementById('movDesc').value = '';
    document.getElementById('movData').value = '';
    document.getElementById('movTipo').value = 'receita';
  }
});
</script>

<?php include '../includes/footer.php'; ?>
