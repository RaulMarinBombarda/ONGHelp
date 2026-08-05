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
        $nome     = trim($_POST['nome'] ?? '');
        $indicador = (int)($_POST['indicador'] ?? 0);
        $unidade  = trim($_POST['unidade'] ?? '');

        if (!$nome || !$unidade) {
            $erro = 'Preencha o nome e a unidade do indicador.';
        } else {
            if ($acao === 'adicionar') {
                $pdo->prepare("INSERT INTO projetos (usuario_id, nome, indicador, unidade) VALUES (?, ?, ?, ?)")
                    ->execute([$id, $nome, $indicador, $unidade]);
                $sucesso = 'Projeto cadastrado com sucesso!';
            } else {
                $projId = (int)($_POST['proj_id'] ?? 0);
                $check = $pdo->prepare("SELECT id FROM projetos WHERE id = ? AND usuario_id = ?");
                $check->execute([$projId, $id]);
                if ($check->fetch()) {
                    $pdo->prepare("UPDATE projetos SET nome=?, indicador=?, unidade=? WHERE id=?")
                        ->execute([$nome, $indicador, $unidade, $projId]);
                    $sucesso = 'Projeto atualizado com sucesso!';
                } else {
                    $erro = 'Projeto não encontrado.';
                }
            }
        }
    } elseif ($acao === 'excluir') {
        $projId = (int)($_POST['proj_id'] ?? 0);
        $pdo->prepare("DELETE FROM projetos WHERE id = ? AND usuario_id = ?")->execute([$projId, $id]);
        $sucesso = 'Projeto excluído.';
    }
}

// ── Busca projetos ──
$projs = $pdo->prepare("SELECT * FROM projetos WHERE usuario_id = ? ORDER BY id ASC");
$projs->execute([$id]);
$projetos = $projs->fetchAll();

$page_title = 'Projetos';
$active_nav = 'projetos';
include '../includes/header.php';
?>

<section style="background:var(--cor-fundo); min-height:80vh;">

  <div style="height:140px; background:linear-gradient(135deg,var(--cor-primaria),#0a4b7a);"></div>

  <div class="container pb-5" style="max-width:1320px; margin-top:-60px;">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
      <div>
        <h4 class="fw-bold mb-1 text-white" style="text-shadow:0 1px 4px rgba(0,0,0,.2);">Projetos</h4>
        <p class="mb-0" style="font-size:.85rem; color:rgba(255,255,255,.75);">
          Gerencie os projetos e metas da sua organização.
        </p>
      </div>
      <button class="btn btn-light rounded-pill px-3 btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalProj">
        <i class="bi bi-plus-lg me-1"></i> Novo projeto
      </button>
    </div>

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

    <?php if (empty($projetos)): ?>
      <div class="card border-0 rounded-4 shadow-sm p-5 text-center">
        <div style="font-size:3rem; color:#DCE9F4; margin-bottom:1rem;"><i class="bi bi-kanban"></i></div>
        <h6 class="fw-bold mb-2">Nenhum projeto cadastrado</h6>
        <p class="text-muted mb-3" style="font-size:.875rem;">
          Crie seu primeiro projeto para exibir as metas da sua organização no Portal de Transparência.
        </p>
        <button class="btn btn-primary rounded-pill px-4 btn-sm" data-bs-toggle="modal" data-bs-target="#modalProj">
          <i class="bi bi-plus-lg me-1"></i> Criar projeto
        </button>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($projetos as $proj): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div style="width:44px; height:44px; border-radius:12px; background:#E0F0FF;
                          display:flex; align-items:center; justify-content:center;
                          color:var(--cor-primaria); font-size:1.2rem;">
                <i class="bi bi-kanban-fill"></i>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary px-2 py-0" style="font-size:.75rem;"
                        onclick="editar(<?= $proj['id'] ?>, '<?= htmlspecialchars($proj['nome'], ENT_QUOTES) ?>', <?= $proj['indicador'] ?>, '<?= htmlspecialchars($proj['unidade'], ENT_QUOTES) ?>')">
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Excluir este projeto?');">
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="proj_id" value="<?= $proj['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-0" style="font-size:.75rem;">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
            <h6 class="fw-bold mb-2" style="font-size:.95rem;"><?= htmlspecialchars($proj['nome']) ?></h6>
            <div class="mt-auto">
              <div style="font-size:1.5rem; font-weight:700; color:var(--cor-primaria);">
                <?= number_format($proj['indicador'], 0, ',', '.') ?>
              </div>
              <div style="font-size:.82rem; color:var(--cor-texto-muted);">
                <?= htmlspecialchars($proj['unidade']) ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Modal Adicionar/Editar -->
<div class="modal fade" id="modalProj" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-transp">
      <form method="POST" action="">
        <input type="hidden" name="acao" id="projAcao" value="adicionar">
        <input type="hidden" name="proj_id" id="projId" value="0">
        <div class="modal-header">
          <h5 class="modal-title fw-bold mb-0" id="projTitulo">Novo projeto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="auth-label">Nome do projeto</label>
              <input type="text" name="nome" id="projNome" class="auth-input" required
                     placeholder="Ex: Reforço Escolar">
            </div>
            <div class="col-md-6">
              <label class="auth-label">Indicador (número)</label>
              <input type="number" name="indicador" id="projIndicador" class="auth-input" required
                     placeholder="120" min="0">
            </div>
            <div class="col-md-6">
              <label class="auth-label">Unidade do indicador</label>
              <input type="text" name="unidade" id="projUnidade" class="auth-input" required
                     placeholder="crianças atendidas">
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
function editar(id, nome, indicador, unidade) {
  document.getElementById('projAcao').value = 'editar';
  document.getElementById('projId').value = id;
  document.getElementById('projTitulo').textContent = 'Editar projeto';
  document.getElementById('projNome').value = nome;
  document.getElementById('projIndicador').value = indicador;
  document.getElementById('projUnidade').value = unidade;
  new bootstrap.Modal(document.getElementById('modalProj')).show();
}
document.getElementById('modalProj').addEventListener('hidden.bs.modal', function () {
  if (document.getElementById('projAcao').value === 'editar') {
    document.getElementById('projAcao').value = 'adicionar';
    document.getElementById('projId').value = '0';
    document.getElementById('projTitulo').textContent = 'Novo projeto';
    document.getElementById('projNome').value = '';
    document.getElementById('projIndicador').value = '';
    document.getElementById('projUnidade').value = '';
  }
});
</script>

<?php include '../includes/footer.php'; ?>
