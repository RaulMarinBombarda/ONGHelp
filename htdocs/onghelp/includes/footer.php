<?php
$base = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'pages') ? '../' : '';
?>

<footer style="background:#0A1929; color:rgba(255,255,255,.75);" class="pt-5 pb-3 mt-0">
  <div class="container">
    <div class="row g-4 mb-4">

      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 fw-bold text-white mb-2">
          <span class="navbar-brand-icon"><i class="bi bi-heart-fill"></i></span>
          ONGHelp
        </div>
        <p class="small">Plataforma de gestão e transparência para ONGs brasileiras.</p>
      </div>

      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-bold mb-3">Plataforma</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= $base ?>pages/transparencia.php" class="footer-link">Portal de Transparência</a></li>
          <li class="mb-2"><a href="<?= $base ?>pages/ongs.php"          class="footer-link">ONGs cadastradas</a></li>
          <li class="mb-2"><a href="<?= $base ?>como-funciona.php"       class="footer-link">Como funciona</a></li>
          <li class="mb-2"><a href="<?= $base ?>doacoes.php"             class="footer-link">Doações</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-bold mb-3">Suporte</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="mailto:contato@gmail.com" class="footer-link">Fale conosco</a></li>
          <li class="mb-2"><a href="<?= $base ?>privacidade.php" class="footer-link">Privacidade</a></li>
          <li class="mb-2"><a href="<?= $base ?>termos.php"     class="footer-link">Termos de uso</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-3">
        <h6 class="text-white fw-bold mb-3">Contato</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="mailto:contato@gmail.com" class="footer-link">contato@gmail.com</a></li>
          <li class="mb-2"><a href="tel:+551699707989"        class="footer-link">(16) 99707-9789</a></li>
          <li>Taquaritinga, SP — Brasil</li>
        </ul>
      </div>

    </div>

    <div class="border-top pt-3 small" style="border-color:rgba(255,255,255,.1)!important; color:rgba(255,255,255,.4);">
      &copy; <?= date('Y') ?> ONGHelp — Projeto Acadêmico
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>