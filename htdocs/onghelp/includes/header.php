<?php
$page_title = $page_title ?? 'ONGHelp';
$active_nav = $active_nav ?? '';
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'pages') ? '../' : '';

// Inicia sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> | ONGHelp</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- CSS próprio (só o que Bootstrap não cobre) -->
  <link rel="stylesheet" href="<?= $base ?>assets/css/global.css">
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top" style="background:#fff; border-bottom:1px solid #DCE9F4; box-shadow:0 2px 12px rgba(13,92,143,.06);">
  <div class="container">

    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $base ?>index.php">
      <span class="navbar-brand-icon"><i class="bi bi-heart-fill"></i></span>
      ONGHelp
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link nav-link-custom <?= $active_nav==='home' ? 'nav-link-active' : '' ?>" href="<?= $base ?>index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-custom <?= $active_nav==='ongs' ? 'nav-link-active' : '' ?>" href="<?= $base ?>pages/ongs.php">ONGs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-custom <?= $active_nav==='transparencia' ? 'nav-link-active' : '' ?>" href="<?= $base ?>pages/transparencia.php">Transparência</a>
        </li>
        <?php if ($logado): ?>
        <li class="nav-item">
          <a class="nav-link nav-link-custom <?= $active_nav==='gestao' ? 'nav-link-active' : '' ?>" href="<?= $base ?>pages/gestao.php">Gestão</a>
        </li>
        <?php endif; ?>
      </ul>
      <div class="d-flex gap-2 mt-3 mt-lg-0">
        <?php if ($logado): ?>
          <a href="<?= $base ?>pages/perfil.php"
             class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_nome']) ?>
          </a>
          <a href="#" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnSair">
            <i class="bi bi-box-arrow-right me-1"></i> Sair
          </a>
        <?php else: ?>
          <a href="<?= $base ?>pages/login.php"    class="btn btn-outline-primary btn-sm rounded-pill px-3">Entrar</a>
          <a href="<?= $base ?>pages/cadastro.php" class="btn btn-primary btn-sm rounded-pill px-3">Cadastrar</a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</nav>

<!-- Modal confirmar saída -->
<div class="modal fade" id="modalSair" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header border-0 pb-0 justify-content-center">
        <h6 class="modal-title fw-bold"><i class="bi bi-box-arrow-right text-primary me-2"></i>Sair</h6>
      </div>
      <div class="modal-body text-center" style="font-size:.9rem;">
        Deseja realmente sair da sua conta?
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <a href="<?= $base ?>pages/logout.php" class="btn btn-primary btn-sm rounded-pill px-3">
          <i class="bi bi-box-arrow-right me-1"></i>Sair
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var btnSair = document.getElementById('btnSair');
  if (btnSair) {
    btnSair.addEventListener('click', function (e) {
      e.preventDefault();
      var modal = new bootstrap.Modal(document.getElementById('modalSair'));
      modal.show();
    });
  }
});
</script>
