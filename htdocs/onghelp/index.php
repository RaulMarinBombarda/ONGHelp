<?php
require_once 'includes/db.php';

$page_title = 'Home';
$active_nav = 'home';

// ── Stats globais para a seção hero ──────────────────────────
$statsStmt = $pdo->query("
    SELECT
        COUNT(DISTINCT u.id)                                                   AS total_ongs,
        COALESCE(SUM(CASE WHEN m.tipo = 'receita' THEN m.valor END), 0)       AS total_arrecadado,
        COUNT(DISTINCT CASE WHEN m.tipo = 'receita' THEN m.id END)            AS total_doacoes
    FROM  usuarios u
    LEFT  JOIN movimentacoes m ON m.usuario_id = u.id
    WHERE u.ativo = 1
");
$stats = $statsStmt->fetch();

// Formata arrecadado em formato compacto (ex: R$ 47k)
$arrecadado = (float) $stats['total_arrecadado'];
if ($arrecadado >= 1_000_000) {
    $arrecadadoFmt = 'R$ ' . number_format($arrecadado / 1_000_000, 1, ',', '.') . 'M';
} elseif ($arrecadado >= 1_000) {
    $arrecadadoFmt = 'R$ ' . number_format($arrecadado / 1_000, 0, ',', '.') . 'k';
} else {
    $arrecadadoFmt = 'R$ ' . number_format($arrecadado, 2, ',', '.');
}

// ── ONGs em destaque: pega até 4 ONGs mais recentes ──────────
$destaqueStmt = $pdo->query("
    SELECT id, nome, area, cidade, descricao
    FROM   usuarios
    WHERE  ativo = 1
    ORDER  BY criado_em DESC
    LIMIT  4
");
$ongsDestaque = $destaqueStmt->fetchAll();

// ── Imagens de capa por área (fallback por categoria) ─────────
$imgPorArea = [
    'Educação'          => 'assets/img/ong-educacao.jpg',
    'Animais'           => 'assets/img/ong-animais.jpg',
    'Meio Ambiente'     => 'assets/img/ong-meioambiente.jpg',
    'Saúde'             => 'assets/img/ong-saude.jpg',
    'Assistência Social'=> 'assets/img/ong-assistenciasocial.jpg',
    'Cultura'           => 'assets/img/ong-cultura.jpg',
    'Esporte'           => 'assets/img/ong-esporte.jpg',
    'Outra'             => 'assets/img/ong-outra.jpg',
];

include 'includes/header.php';
?>

<?php
// ── Toast de conta excluída (vindo de desativar.php) ─────────
if (isset($_GET['conta']) && $_GET['conta'] === 'excluida'): ?>
<div class="position-fixed top-0 start-50 translate-middle-x mt-3"
     style="z-index:9999; min-width:320px;">
  <div class="alert alert-warning alert-dismissible d-flex align-items-center gap-2 shadow"
       role="alert" style="font-size:.875rem;">
    <i class="bi bi-check-circle-fill text-warning"></i>
    Sua conta foi excluída permanentemente. Lamentamos a sua saída.
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<!-- ======= HERO ======= -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <span class="badge-secao">Plataforma Gratuita para ONGs Brasileiras</span>

        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,4vw,2.8rem);">
          Transparência e gestão para ONGs que<br>
          <span class="hero-titulo"><span>transformam o Brasil</span></span>
        </h1>

        <p class="text-muted mb-4">
          Gerencie projetos, publique relatórios financeiros com total transparência
          e receba doações via PIX — tudo em uma única plataforma.
        </p>

        <div class="d-flex gap-3 flex-wrap mb-3">
          <a href="pages/cadastro.php" class="btn btn-primary rounded-pill px-4">
            Cadastrar gratuitamente
          </a>
          <a href="pages/ongs.php" class="btn btn-outline-primary rounded-pill px-4">
            Ver ONGs
          </a>
        </div>

        <div class="d-flex gap-4">
          <span class="hero-selo"><i class="bi bi-check-circle-fill"></i> Sem taxa de adesão</span>
          <span class="hero-selo"><i class="bi bi-check-circle-fill"></i> Dados seguros (LGPD)</span>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="hero-img">
          <img src="assets/img/hero.jpg" alt="Impacto social">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ======= STATS — dinâmicos do banco ======= -->
<section class="stats">
  <div class="container">
    <div class="row">
      <div class="col-6 col-md-3 stat-item">
        <div class="stat-valor"><?= $stats['total_ongs'] ?>+</div>
        <div class="stat-label">ONGs cadastradas</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="stat-valor"><?= $arrecadadoFmt ?></div>
        <div class="stat-label">Arrecadados</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="stat-valor"><?= $stats['total_doacoes'] ?>+</div>
        <div class="stat-label">Movimentações registradas</div>
      </div>
      <div class="col-6 col-md-3 stat-item" style="border-right:none;">
        <div class="stat-valor">100%</div>
        <div class="stat-label">Transparência pública</div>
      </div>
    </div>
  </div>
</section>

<!-- ======= COMO FUNCIONA ======= -->
<section class="py-5" style="background:#fff;">
  <div class="container py-3">
    <div class="text-center mb-5">
      <span class="badge-secao">Como funciona</span>
      <h2 class="fw-bold">Tudo que sua ONG precisa em um só lugar</h2>
      <p class="text-muted mx-auto" style="max-width:540px;">
        Do cadastro à gestão financeira, o ONGHelp simplifica a operação para
        que você foque no que mais importa: o impacto social.
      </p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card-func">
          <div class="card-func-icon">
            <i class="bi bi-globe2"></i>
          </div>
          <h5 class="fw-bold mb-2">Perfil Público</h5>
          <p class="text-muted small mb-0">
            Crie um perfil completo para sua ONG com projetos, resultados
            e informações de contato acessíveis a qualquer pessoa.
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-func">
          <div class="card-func-icon" style="background:#D6F5EB; color:#00B37E;">
            <i class="bi bi-shield-check"></i>
          </div>
          <h5 class="fw-bold mb-2">Transparência Total</h5>
          <p class="text-muted small mb-0">
            Publique relatórios financeiros, entradas e saídas com gráficos
            claros para garantir a confiança dos doadores.
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-func">
          <div class="card-func-icon" style="background:#FFF0E5; color:#F26522;">
            <i class="bi bi-qr-code"></i>
          </div>
          <h5 class="fw-bold mb-2">Doação via PIX</h5>
          <p class="text-muted small mb-0">
            Receba doações de forma rápida e segura com QR Code PIX
            integrado diretamente na plataforma.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======= ONGs EM DESTAQUE — dinâmicas do banco ======= -->
<section class="py-5" style="background:var(--cor-fundo);">
  <div class="container py-3">
    <div class="text-center mb-5">
      <span class="badge-secao">ONGs em Destaque</span>
      <h2 class="fw-bold">Conheça quem está transformando o Brasil</h2>
      <p class="text-muted mx-auto" style="max-width:540px;">
        Organizações verificadas, transparentes e com impacto real
        nas comunidades brasileiras.
      </p>
    </div>

    <?php if (empty($ongsDestaque)): ?>
      <!-- Estado vazio: ainda não há ONGs cadastradas -->
      <p class="text-center text-muted">Nenhuma ONG cadastrada ainda. Seja a primeira!</p>
    <?php else: ?>
    <div id="carouselOngs" class="carousel slide position-relative px-5" data-bs-ride="carousel">

      <button class="carousel-control-prev" type="button"
              data-bs-target="#carouselOngs" data-bs-slide="prev"
              style="width:40px; left:-10px;">
        <span class="carousel-control-prev-icon"
              style="filter:invert(1) sepia(1) saturate(5) hue-rotate(180deg);"></span>
      </button>
      <button class="carousel-control-next" type="button"
              data-bs-target="#carouselOngs" data-bs-slide="next"
              style="width:40px; right:-10px;">
        <span class="carousel-control-next-icon"
              style="filter:invert(1) sepia(1) saturate(5) hue-rotate(180deg);"></span>
      </button>

      <div class="carousel-inner">
        <?php
        // Agrupa as ONGs de destaque em slides de 2 colunas
        $chunks = array_chunk($ongsDestaque, 2);
        foreach ($chunks as $i => $grupo):
        ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <div class="row g-3">
            <?php foreach ($grupo as $ong):
              $img = $imgPorArea[$ong['area']] ?? 'assets/img/ong-educacao.jpg';
            ?>
            <div class="col-6">
              <div class="card-ong" style="min-height:300px;">
                <img src="<?= $img ?>" alt="<?= htmlspecialchars($ong['nome']) ?>">
                <div class="card-ong-overlay">
                  <span class="card-ong-cat"><?= htmlspecialchars($ong['area']) ?></span>
                  <h3 style="font-size:1rem;"><?= htmlspecialchars($ong['nome']) ?></h3>
                  <p style="font-size:.8rem;">
                    <?= htmlspecialchars($ong['descricao'] ?? $ong['cidade']) ?>
                  </p>
                  <a href="pages/transparencia.php?modal=<?= $ong['id'] ?>" class="card-ong-link" style="font-size:.8rem;">
                    Conhecer ONG <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <br>
    <div class="text-center mt-4">
      <a href="pages/ongs.php" class="btn btn-primary rounded-pill px-4">Ver todas as ONGs</a>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ======= BANNER FINAL ======= -->
<section class="cta-final">
  <div class="container">
    <h2 class="fw-bold">Sua ONG merece o melhor suporte!</h2>
    <p>Cadastre-se gratuitamente e comece a gestão transparente da sua organização hoje mesmo.</p>
    <a href="pages/cadastro.php" class="btn-acento">COMEÇAR AGORA</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
