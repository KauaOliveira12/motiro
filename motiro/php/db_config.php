<?php
/**
 * MOTIRÕ — Configuração do Banco de Dados
 * Startup de Inclusão Digital | IFSP Campus Guarulhos
 * * INSTRUÇÕES DE CONFIGURAÇÃO:
 * 1. Crie o banco de dados no phpMyAdmin
 * 2. Atualize as credenciais abaixo conforme seu servidor de hospedagem
 * 3. Execute o SQL para criar as tabelas
 */

// Configurações do Banco de Dados (Ajustadas para o seu cPanel)
define('DB_HOST',   'localhost');
define('DB_NOME',   'computacao_motiro');   // Nome do banco que você criou no cPanel
define('DB_USUARIO','computacao_motiro_admin');  // Usuario do banco no cPanel
define('DB_SENHA',  'motiro1234#');    // Senha do usuario do banco

/**
 * E-mail do Scrum Master (destinatário das mensagens de contato)
 */
define('SCRUM_MASTER_EMAIL', 'kaua.oliveira1@aluno.ifsp.edu.br');
define('NOME_SITE',          'Motirõ — Tecnologia Para Todos');

/**
 * Retorna conexão PDO com o banco de dados
 */
function conectarBD(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NOME . ";charset=utf8mb4";
    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USUARIO, DB_SENHA, $opcoes);
}
