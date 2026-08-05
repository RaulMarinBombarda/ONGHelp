<?php
// ============================================================
// includes/helpers.php — Funções compartilhadas
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================

function fmtDinheiro(float $v, bool $compacto = false): string
{
    if ($compacto) {
        if ($v >= 1_000_000) return 'R$ ' . number_format($v / 1_000_000, 1, ',', '.') . 'M';
        if ($v >= 1_000)     return 'R$ ' . number_format($v / 1_000, 0, ',', '.') . 'k';
        return 'R$ ' . number_format($v, 0, ',', '.');
    }
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function fmtData(string $data): string
{
    if ($data === '' || $data === null) return '—';
    return date('d/m/Y', strtotime($data));
}

function pctUso(float $receita, float $despesa): float
{
    if ($receita <= 0) return 0.0;
    return min(round($despesa / $receita * 100, 1), 100.0);
}

function valorParaDecimal(string $v): float
{
    $v = trim($v);
    $v = str_replace('.', '', $v);
    $v = str_replace(',', '.', $v);
    return (float) $v;
}

function emojiPorArea(string $area): string
{
    $mapa = [
        'Educação'          => '🎓',
        'Saúde'             => '🏥',
        'Animais'           => '🐾',
        'Meio Ambiente'     => '🌱',
        'Assistência Social'=> '❤️',
        'Cultura'           => '🎨',
        'Esporte'           => '⚽',
        'Outra'             => '🏢',
    ];
    return $mapa[$area] ?? '🏢';
}
