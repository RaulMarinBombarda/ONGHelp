<?php
// ============================================================
// includes/db.php — Conexão PDO com o banco MySQL
// Projeto ONGHelp · Projeto Integrador 2026
// ============================================================
//
// Ajuste $host, $user e $pass conforme seu ambiente.
// Em produção, use variáveis de ambiente ou um arquivo .env
// fora da raiz pública — NUNCA exponha credenciais no Git.
// ============================================================

$host   = 'localhost';
$dbname = 'onghelp';
$user   = 'root';      // altere se necessário
$pass   = '';          // altere se necessário

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            // Lança exceção em qualquer erro SQL (facilita debug)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Retorna arrays associativos por padrão
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Desativa emulação de prepared statements (mais seguro)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Em produção, logue o erro e exiba mensagem genérica ao usuário
    error_log('[ONGHelp] Erro de conexão: ' . $e->getMessage());
    die('<p style="font-family:sans-serif;color:#c0392b;padding:2rem;">
            Não foi possível conectar ao banco de dados. Tente novamente mais tarde.
         </p>');
}
