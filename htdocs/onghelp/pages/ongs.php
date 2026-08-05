<?php
$page_title = 'ONGs Cadastradas';
$active_nav = 'ongs';
include '../includes/header.php';
?>

<div class="construcao-wrap">
  <div class="container">
    <div class="construcao-card mx-auto">

      <div class="construcao-icon">
        <i class="bi bi-people-fill"></i>
      </div>

      <span class="badge-secao">Em desenvolvimento</span>
      <h2 class="fw-bold mt-2 mb-2">ONGs Cadastradas</h2>
      <p class="text-muted" style="font-size:.9rem;">
        Esta seção está sendo construída. Em breve você poderá explorar e filtrar
        todas as ONGs verificadas na plataforma.
      </p>

      <ul class="construcao-features">
        <li><i class="bi bi-check-circle-fill"></i> Busca por nome e categoria</li>
        <li><i class="bi bi-check-circle-fill"></i> Perfil público de cada organização</li>
        <li><i class="bi bi-check-circle-fill"></i> Indicadores de impacto e projetos ativos</li>
        <li><i class="bi bi-check-circle-fill"></i> Doação direta via PIX integrado</li>
      </ul>

      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm">Voltar ao início</a>
        <a href="transparencia.php" class="btn btn-primary rounded-pill px-4 btn-sm">Ver transparência</a>
      </div>

    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
