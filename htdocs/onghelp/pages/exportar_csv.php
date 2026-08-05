<?php
// ============================================================
// pages/exportar_csv.php — Exporta movimentações da ONG em CSV
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================
//
// Parâmetros GET:
//   ong  → ID da ONG (inteiro, obrigatório)
//   ano  → Ano a filtrar (inteiro, opcional; padrão: ano atual)
//
// Retorna um arquivo CSV para download.
// Não exibe nenhum HTML — apenas headers + dados.
// ============================================================

require_once '../includes/db.php';

// ── Valida parâmetros ────────────────────────────────────────
$ongId = isset($_GET['ong']) && ctype_digit($_GET['ong']) ? (int) $_GET['ong'] : 0;
$ano   = isset($_GET['ano']) && ctype_digit($_GET['ano']) ? (int) $_GET['ano'] : (int) date('Y');

if ($ongId <= 0) {
    http_response_code(400);
    exit('Parâmetro inválido.');
}

// ── Verifica se a ONG existe e está ativa ────────────────────
$stmtOng = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ? AND ativo = 1");
$stmtOng->execute([$ongId]);
$ong = $stmtOng->fetch();

if (!$ong) {
    http_response_code(404);
    exit('ONG não encontrada.');
}

// ── Busca movimentações do ano solicitado ────────────────────
$stmt = $pdo->prepare("
    SELECT data_mov, descricao, tipo, valor
    FROM   movimentacoes
    WHERE  usuario_id = ?
      AND  YEAR(data_mov) = ?
    ORDER  BY data_mov DESC, id DESC
");
$stmt->execute([$ongId, $ano]);
$movs = $stmt->fetchAll();

// ── Monta nome do arquivo seguro ─────────────────────────────
// Remove caracteres especiais do nome da ONG para o filename
$nomeSeguro = preg_replace('/[^a-z0-9]+/i', '-', $ong['nome']);
$nomeSeguro = trim($nomeSeguro, '-');
$filename   = "transparencia-{$nomeSeguro}-{$ano}.csv";

// ── Envia headers de download ─────────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

// BOM UTF-8 para o Excel abrir acentuação corretamente
echo "\xEF\xBB\xBF";

// ── Escreve CSV ──────────────────────────────────────────────
$out = fopen('php://output', 'w');

// Cabeçalho
fputcsv($out, ['Data', 'Descrição', 'Tipo', 'Valor (R$)'], ';');

foreach ($movs as $mov) {
    fputcsv($out, [
        date('d/m/Y', strtotime($mov['data_mov'])),
        $mov['descricao'],
        ucfirst($mov['tipo']),
        number_format((float) $mov['valor'], 2, ',', '.'),
    ], ';');
}

fclose($out);
exit;
