<?php
// ============================================================
// pages/transparencia.php — Portal de Transparência dinâmico
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================
//
// FUNCIONAMENTO GERAL:
//   1. Todos os filtros (ano, categoria, busca, modal aberto)
//      chegam via $_GET e são processados aqui em PHP.
//   2. O PHP aplica WHERE/LIKE na query conforme necessário.
//   3. O modal da ONG selecionada é aberto via atributo
//      data-bs-toggle no card (Bootstrap nativo, sem JS).
//
// PARÂMETROS GET ESPERADOS:
//   ?ano=2025          → filtra movimentações pelo ano
//   ?categoria=Saúde   → filtra cards por área
//   ?busca=esperança   → filtra cards por nome (LIKE)
//   ?modal=42          → abre automaticamente o modal da ONG 42
// ============================================================

require_once '../includes/db.php';
require_once '../includes/helpers.php';

// ─────────────────────────────────────────────────────────────
// 1. SANITIZAÇÃO DOS PARÂMETROS GET
// ─────────────────────────────────────────────────────────────

// Ano: padrão = ano mais recente com dados; aceita apenas 2020–(ano+1)
$anoPadrao = $pdo->query("SELECT MAX(YEAR(data_mov)) FROM movimentacoes")->fetchColumn();
$anoPadrao = $anoPadrao ? (int) $anoPadrao : (int) date('Y');
$ano = isset($_GET['ano']) && ctype_digit($_GET['ano'])
    ? (int) $_GET['ano']
    : $anoPadrao;

if ($ano < 2020 || $ano > (int) date('Y') + 1) {
    $ano = (int) date('Y');
}

// Filtros de categoria e busca por nome
$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroBusca     = trim($_GET['busca']     ?? '');

// ID da ONG cujo modal deve abrir ao carregar a página (via data-bs-toggle)
$modalAberto = isset($_GET['modal']) && ctype_digit($_GET['modal'])
    ? (int) $_GET['modal']
    : 0;

// ─────────────────────────────────────────────────────────────
// 2. BUSCA DE ONGs (com filtros de categoria e busca aplicados)
// ─────────────────────────────────────────────────────────────

// Monta a query dinâmica conforme filtros presentes
$sql    = "SELECT id, nome, area, cidade, estado, descricao, pix_chave
           FROM   usuarios
           WHERE  ativo = 1";
$params = [];

if ($filtroCategoria !== '') {
    $sql      .= " AND area = ?";
    $params[]  = $filtroCategoria;
}

if ($filtroBusca !== '') {
    $sql      .= " AND nome LIKE ?";
    $params[]  = '%' . $filtroBusca . '%';
}

$sql .= " ORDER BY nome ASC";

$stmtOngs = $pdo->prepare($sql);
$stmtOngs->execute($params);
$ongs = $stmtOngs->fetchAll();

// ─────────────────────────────────────────────────────────────
// 3. TOTAIS ANUAIS POR ONG (receita e despesa)
// ─────────────────────────────────────────────────────────────
// Indexado por [usuario_id][tipo] para acesso O(1) no loop dos cards.

$totais     = [];
$stmtTotais = $pdo->prepare("
    SELECT usuario_id, tipo, COALESCE(SUM(valor), 0) AS total
    FROM   movimentacoes
    WHERE  YEAR(data_mov) = ?
    GROUP  BY usuario_id, tipo
");
$stmtTotais->execute([$ano]);

foreach ($stmtTotais->fetchAll() as $row) {
    $uid  = $row['usuario_id'];
    $tipo = $row['tipo'];   // 'receita' ou 'despesa'

    if (!isset($totais[$uid])) {
        $totais[$uid] = ['receita' => 0.0, 'despesa' => 0.0];
    }
    $totais[$uid][$tipo] = (float) $row['total'];
}

// ─────────────────────────────────────────────────────────────
// 4. TOTAIS MENSAIS (gráfico de barras no modal)
// ─────────────────────────────────────────────────────────────
// Estrutura: $mensal[usuario_id][tipo][mes_numero] = valor

$mensal     = [];
$stmtMensal = $pdo->prepare("
    SELECT usuario_id, tipo, MONTH(data_mov) AS mes, SUM(valor) AS total
    FROM   movimentacoes
    WHERE  YEAR(data_mov) = ?
    GROUP  BY usuario_id, tipo, mes
    ORDER  BY mes ASC
");
$stmtMensal->execute([$ano]);

foreach ($stmtMensal->fetchAll() as $row) {
    $uid  = $row['usuario_id'];
    $tipo = $row['tipo'];
    $mes  = (int) $row['mes'];
    $mensal[$uid][$tipo][$mes] = (float) $row['total'];
}

// ─────────────────────────────────────────────────────────────
// 5. BATCH QUERIES — busca dados de todas as ONGs de uma vez
//    para eliminar o problema N+1 no loop de cards e modais.
// ─────────────────────────────────────────────────────────────

$ids = array_column($ongs, 'id');

// Projetos de todas as ONGs de uma só vez
$projPorOng = [];
if (!empty($ids)) {
    $projStmt = $pdo->query("
        SELECT *
        FROM   projetos
        WHERE  usuario_id IN (" . implode(',', $ids) . ")
        ORDER  BY usuario_id, nome ASC
    ");
    foreach ($projStmt->fetchAll() as $p) {
        $projPorOng[$p['usuario_id']][] = $p;
    }

    // Últimas 5 movimentações de cada ONG (batch)
    $movStmt = $pdo->query("
        SELECT *
        FROM   movimentacoes
        WHERE  usuario_id IN (" . implode(',', $ids) . ")
        ORDER  BY usuario_id, data_mov DESC, id DESC
    ");
    $movPorOng = [];
    foreach ($movStmt->fetchAll() as $m) {
        $uid = $m['usuario_id'];
        if (!isset($movPorOng[$uid])) $movPorOng[$uid] = [];
        if (count($movPorOng[$uid]) < 5) {
            $movPorOng[$uid][] = $m;
        }
    }
} else {
    $movPorOng = [];
}

// ─────────────────────────────────────────────────────────────
// 6. ESTATÍSTICAS GLOBAIS (hero)
// ─────────────────────────────────────────────────────────────

$statsStmt = $pdo->query("
    SELECT
        COUNT(DISTINCT u.id)                                            AS total_ongs,
        COALESCE(SUM(CASE WHEN m.tipo = 'receita' THEN m.valor END), 0) AS total_receitas
    FROM  usuarios u
    LEFT  JOIN movimentacoes m ON m.usuario_id = u.id
    WHERE u.ativo = 1
");
$stats = $statsStmt->fetch();

// Helpers carregados de includes/helpers.php

/**
 * Monta a URL do formulário de filtro preservando os parâmetros atuais,
 * mas substituindo o que foi alterado.
 */
function urlFiltro(array $override): string
{
    global $ano;
    $base = ['ano' => $_GET['ano'] ?? $ano, 'categoria' => $_GET['categoria'] ?? '', 'busca' => $_GET['busca'] ?? ''];
    $params = array_merge($base, $override);
    // Remove parâmetros vazios para URL limpa
    $params = array_filter($params, fn($v) => $v !== '');
    return '?' . http_build_query($params);
}

// Nomes curtos dos meses em pt-BR
$mesesNome = [
    1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
    5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
    9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
];

// ─────────────────────────────────────────────────────────────
// INÍCIO DO HTML
// ─────────────────────────────────────────────────────────────

$page_title = 'Portal de Transparência';
$active_nav = 'transparencia';
include '../includes/header.php';
?>

<!-- ======= HERO ======= -->
<section class="transp-hero">
  <div class="container">
    <div class="row align-items-center g-4">

      <div class="col-lg-7">
        <span class="badge-secao">🔍 Transparência Ativa</span>
        <h1 class="fw-bold mb-3" style="font-size:clamp(1.8rem,3.5vw,2.4rem);">
          Portal de Transparência<br>
          <span style="color:var(--cor-secundaria); font-style:italic;">das ONGs Cadastradas</span>
        </h1>
        <p class="text-muted mb-0" style="max-width:500px;">
          Consulte receitas, despesas, projetos e balancetes de todas as organizações
          verificadas na plataforma. Transparência é confiança.
        </p>
      </div>

      <!-- Estatísticas do banco -->
      <div class="col-lg-5">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-3 rounded-3" style="background:#fff; border:1px solid #DCE9F4; text-align:center;">
              <div style="font-size:1.6rem; font-weight:700; color:var(--cor-primaria);">
                <?= $stats['total_ongs'] ?>
              </div>
              <div style="font-size:.8rem; color:var(--cor-texto-muted);">ONGs verificadas</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3" style="background:#fff; border:1px solid #DCE9F4; text-align:center;">
              <div style="font-size:1.6rem; font-weight:700; color:var(--cor-secundaria);">100%</div>
              <div style="font-size:.8rem; color:var(--cor-texto-muted);">com prestação de contas</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3" style="background:#fff; border:1px solid #DCE9F4; text-align:center;">
              <div style="font-size:1.6rem; font-weight:700; color:var(--cor-acento);">
                <?= fmtDinheiro((float)$stats['total_receitas'], true) ?>
              </div>
              <div style="font-size:.8rem; color:var(--cor-texto-muted);">arrecadados total</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3" style="background:#fff; border:1px solid #DCE9F4; text-align:center;">
              <div style="font-size:1.6rem; font-weight:700; color:var(--cor-texto);"><?= $ano ?></div>
              <div style="font-size:.8rem; color:var(--cor-texto-muted);">dados atualizados</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ======= CONTEÚDO PRINCIPAL ======= -->
<section class="py-5" style="background:var(--cor-fundo);">
  <div class="container py-3">

    <!-- Aviso informativo -->
    <div class="info-transp">
      <i class="bi bi-shield-check-fill"></i>
      <div>
        <strong>Como funciona o Portal de Transparência?</strong>
        Todas as informações financeiras são inseridas diretamente pelas ONGs em seus painéis
        administrativos. O ONGHelp não audita os dados, mas garante que qualquer movimentação
        registrada seja pública e rastreável.
      </div>
    </div>

    <!-- ── BARRA DE FILTROS ──────────────────────────────────────
         Todos os filtros são submetidos via GET (sem JavaScript).
         Cada <select> e <input> envia o formulário automaticamente
         com um botão submit estilizado como link.
    ──────────────────────────────────────────────────────────── -->
    <form method="get" action="" class="filtro-bar">

      <!-- Filtro por categoria -->
      <div>
        <label for="filtroCategoria">Categoria</label><br>
        <select id="filtroCategoria" name="categoria">
          <option value="">Todas</option>
          <?php
          // Busca categorias distintas de TODAS as ONGs (sem filtro de busca)
          $todasCats = $pdo->query("SELECT DISTINCT area FROM usuarios WHERE ativo = 1 ORDER BY area ASC")->fetchAll();
          foreach ($todasCats as $row):
          ?>
            <option value="<?= htmlspecialchars($row['area']) ?>"
              <?= $filtroCategoria === $row['area'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($row['area']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filtro por ano -->
      <div>
        <label for="filtroPeriodo">Período</label><br>
        <select id="filtroPeriodo" name="ano">
          <?php for ($y = (int)date('Y'); $y >= 2024; $y--): ?>
            <option value="<?= $y ?>" <?= $y === $ano ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <!-- Busca por nome -->
      <div>
        <label for="filtroBusca">Buscar ONG</label><br>
        <input type="text" id="filtroBusca" name="busca"
               placeholder="Nome da organização…"
               value="<?= htmlspecialchars($filtroBusca) ?>"
               style="min-width:200px;">
      </div>

      <!-- Botão de aplicar filtros -->
      <div class="d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
          Filtrar
        </button>
        <?php if ($filtroCategoria || $filtroBusca || $ano !== (int)date('Y')): ?>
          <a href="?" class="btn btn-outline-secondary btn-sm rounded-pill px-3 ms-2">
            Limpar
          </a>
        <?php endif; ?>
      </div>

      <!-- Contador de ONGs visíveis -->
      <div class="ms-auto d-flex align-items-end">
        <span style="font-size:.82rem; color:var(--cor-texto-muted);">
          <?= count($ongs) ?> <?= count($ongs) === 1 ? 'ONG encontrada' : 'ONGs encontradas' ?>
        </span>
      </div>

    </form>

    <!-- ── CARDS DAS ONGs ────────────────────────────────────────
         Cada card abre seu modal Bootstrap usando apenas
         data-bs-toggle e data-bs-target (sem nenhum JS).
         Se ?modal=ID veio na URL, o modal daquela ONG abre
         automaticamente via atributo "autofocus" no próprio modal.
    ──────────────────────────────────────────────────────────── -->
    <div class="row g-3" id="listaOngs">

      <?php foreach ($ongs as $ong):
        $id      = $ong['id'];
        $receita = $totais[$id]['receita'] ?? 0.0;
        $despesa = $totais[$id]['despesa'] ?? 0.0;
        $pct     = pctUso($receita, $despesa);

        // Projetos (batched — evita N+1)
        $projetos = $projPorOng[$id] ?? [];
      ?>
      <div class="col-md-6 col-lg-4">
        <!--
          data-bs-toggle / data-bs-target: Bootstrap abre o modal
          sem precisar de nenhuma função JavaScript.
        -->
        <div class="ong-transp-card"
             data-bs-toggle="modal"
             data-bs-target="#modalOng<?= $id ?>"
             style="cursor:pointer;">

          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="ong-transp-avatar"><?= emojiPorArea($ong['area']) ?></div>
            <div>
              <div class="ong-transp-nome"><?= htmlspecialchars($ong['nome']) ?></div>
              <div class="ong-transp-cat">
                <?= htmlspecialchars($ong['area']) ?> · <?= htmlspecialchars($ong['cidade']) ?><?= $ong['estado'] ? ', ' . htmlspecialchars($ong['estado']) : '' ?>
              </div>
            </div>
          </div>

          <span class="badge-verificado mb-3">
            <i class="bi bi-patch-check-fill"></i> Verificada
          </span>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="mini-stat">
                Receitas <?= $ano ?><br>
                <strong style="color:var(--cor-secundaria);">
                  <?= $receita > 0 ? fmtDinheiro($receita) : '—' ?>
                </strong>
              </div>
            </div>
            <div class="col-6">
              <div class="mini-stat">
                Despesas <?= $ano ?><br>
                <strong style="color:#C0392B;">
                  <?= $despesa > 0 ? fmtDinheiro($despesa) : '—' ?>
                </strong>
              </div>
            </div>
          </div>

          <!-- Barra de uso do orçamento -->
          <div class="mini-stat mb-1">Uso do orçamento (<?= $pct ?>%)</div>
          <div class="barra-transp">
            <div class="barra-transp-fill" style="width:<?= $pct ?>%;"></div>
          </div>

          <div class="mt-3 d-flex justify-content-between align-items-center">
            <span style="font-size:.8rem; color:var(--cor-texto-muted);">
              <?= count($projetos) ?> <?= count($projetos) === 1 ? 'projeto ativo' : 'projetos ativos' ?>
            </span>
            <span style="font-size:.82rem; font-weight:600; color:var(--cor-primaria);">
              Ver relatório <i class="bi bi-arrow-right"></i>
            </span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Card de convite -->
      <div class="col-md-6 col-lg-4">
        <div class="ong-transp-card d-flex flex-column align-items-center justify-content-center text-center"
             style="border-style:dashed; min-height:220px; cursor:default;">
          <div style="width:52px; height:52px; border-radius:12px; background:#E0F0FF;
                      color:var(--cor-primaria); display:flex; align-items:center;
                      justify-content:center; font-size:1.4rem; margin-bottom:1rem;">
            <i class="bi bi-plus-lg"></i>
          </div>
          <div class="fw-bold mb-1" style="color:var(--cor-texto);">Sua ONG aqui</div>
          <p class="text-muted small mb-3">
            Cadastre-se e publique seus relatórios financeiros com transparência.
          </p>
          <a href="cadastro.php" class="btn btn-primary btn-sm rounded-pill px-4">
            Cadastrar gratuitamente
          </a>
        </div>
      </div>

    </div><!-- /row#listaOngs -->
  </div>
</section>

<!-- ============================================================
     MODAIS — um por ONG, gerados pelo PHP
     Abertura via data-bs-toggle no card acima (Bootstrap puro).
     Se ?modal=ID está na URL, o PHP adiciona "show" ao modal
     correspondente para que ele abra ao carregar a página.
============================================================ -->
<?php foreach ($ongs as $ong):
  $id      = $ong['id'];
  $receita = $totais[$id]['receita'] ?? 0.0;
  $despesa = $totais[$id]['despesa'] ?? 0.0;
  $saldo   = $receita - $despesa;

  // Dados via batch queries (evita N+1)
  $movs    = $movPorOng[$id] ?? [];
  $projetos = $projPorOng[$id] ?? [];

  $receitasMensais = $mensal[$id]['receita'] ?? [];
  $maxMensal       = count($receitasMensais) > 0 ? max($receitasMensais) : 1;

  // Abre automaticamente se o ID veio pela URL (?modal=ID)
  $autoShow = ($modalAberto === $id) ? 'show' : '';
?>
<div class="modal fade <?= $autoShow ?>" id="modalOng<?= $id ?>" tabindex="-1"
     <?= $autoShow ? 'style="display:block;" aria-modal="true" role="dialog"' : '' ?>>
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content modal-transp">

      <!-- Cabeçalho -->
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold mb-0"><?= htmlspecialchars($ong['nome']) ?></h5>
          <small style="opacity:.8;">Portal de Transparência · <?= $ano ?></small>
        </div>
        <!--
          Fecha o modal e remove ?modal= da URL para não reabrir
          ao atualizar a página.
        -->
        <a href="<?= htmlspecialchars(urlFiltro([])) ?>"
           class="btn-close" aria-label="Fechar"></a>
      </div>

      <div class="modal-body p-4">

        <?php if (!empty($ong['descricao'])): ?>
        <p class="text-muted mb-4" style="font-size:.88rem;">
          <?= htmlspecialchars($ong['descricao']) ?>
        </p>
        <?php endif; ?>

        <!-- ── Gráfico de receitas mensais ── -->
        <div class="detalhe-secao">
          <h6><i class="bi bi-bar-chart-fill me-1"></i>
            Receitas mensais — <?= $ano ?>
          </h6>

          <?php if (empty($receitasMensais)): ?>
            <p class="text-muted" style="font-size:.85rem;">
              Nenhuma movimentação registrada para <?= $ano ?>.
            </p>
          <?php else: ?>
          <div class="grafico-barra-wrap mb-3">
            <?php foreach ($receitasMensais as $mes => $total):
              $largura = $maxMensal > 0 ? round($total / $maxMensal * 100) : 0;
            ?>
            <div class="grafico-linha">
              <span class="grafico-label"><?= $mesesNome[$mes] ?></span>
              <div class="grafico-barra-outer">
                <div class="grafico-barra-inner verde" style="width:<?= $largura ?>%;"></div>
              </div>
              <span class="grafico-valor"><?= fmtDinheiro($total) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Saldo do período -->
          <div class="saldo-box <?= $saldo >= 0 ? 'saldo-positivo' : 'saldo-negativo' ?>">
            <div>
              <div style="font-size:.8rem; font-weight:600; text-transform:uppercase;
                          letter-spacing:.4px; opacity:.75;">Saldo do período</div>
              <div style="font-size:1.4rem; font-weight:700;">
                <?= ($saldo >= 0 ? '+ ' : '- ') . fmtDinheiro(abs($saldo)) ?>
              </div>
            </div>
            <i class="bi bi-arrow-<?= $saldo >= 0 ? 'up' : 'down' ?>-circle-fill"
               style="font-size:2rem; opacity:.6;"></i>
          </div>
        </div>

        <!-- ── Últimas movimentações ── -->
        <div class="detalhe-secao">
          <h6><i class="bi bi-list-ul me-1"></i> Últimas Movimentações</h6>

          <?php if (empty($movs)): ?>
            <p class="text-muted" style="font-size:.85rem;">Nenhuma movimentação cadastrada.</p>
          <?php else: ?>
          <table class="tabela-mov">
            <thead>
              <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Tipo</th>
                <th style="text-align:right;">Valor</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($movs as $mov): ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($mov['data_mov'])) ?></td>
                <td><?= htmlspecialchars($mov['descricao']) ?></td>
                <td>
                  <?php if ($mov['tipo'] === 'receita'): ?>
                    <span class="tipo-receita">
                      <i class="bi bi-arrow-down-circle-fill"></i> Receita
                    </span>
                  <?php else: ?>
                    <span class="tipo-despesa">
                      <i class="bi bi-arrow-up-circle-fill"></i> Despesa
                    </span>
                  <?php endif; ?>
                </td>
                <td style="text-align:right; font-weight:600;
                            color:<?= $mov['tipo'] === 'receita' ? '#00915E' : '#C0392B' ?>;">
                  <?= ($mov['tipo'] === 'receita' ? '+ ' : '- ') . fmtDinheiro((float)$mov['valor']) ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>

        <!-- ── Projetos ativos ── -->
        <?php if (!empty($projetos)): ?>
        <div class="detalhe-secao">
          <h6><i class="bi bi-kanban-fill me-1"></i> Projetos Ativos</h6>
          <div class="row g-2">
            <?php
            $colSize = count($projetos) <= 2 ? '6' : '4';
            foreach ($projetos as $proj):
            ?>
            <div class="col-md-<?= $colSize ?>">
              <div class="p-3 rounded-3"
                   style="background:#fff; border:1px solid #DCE9F4; text-align:center;">
                <div style="font-size:1.1rem; font-weight:700; color:var(--cor-primaria);">
                  <?= number_format($proj['indicador'], 0, ',', '.') ?>
                </div>
                <div style="font-size:.78rem; color:var(--cor-texto-muted);">
                  <?= htmlspecialchars($proj['unidade']) ?>
                </div>
                <div style="font-size:.82rem; font-weight:600; margin-top:.5rem;">
                  <?= htmlspecialchars($proj['nome']) ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div><!-- /modal-body -->

      <!-- Rodapé do modal -->
      <div class="modal-footer" style="border-top:1px solid #DCE9F4;">
        <small class="text-muted me-auto">
          <i class="bi bi-info-circle me-1"></i>Dados declarados pela própria organização.
        </small>
        <a href="exportar_csv.php?ong=<?= $id ?>&ano=<?= $ano ?>"
           class="btn btn-outline-primary btn-sm rounded-pill px-3">
          <i class="bi bi-download me-1"></i> Exportar CSV
        </a>
        <?php if (!empty($ong['pix_chave'])): ?>
        <a href="doacoes.php?ong=<?= $id ?>"
           class="btn btn-primary btn-sm rounded-pill px-3">
          <i class="bi bi-qr-code me-1"></i> Fazer doação
        </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>