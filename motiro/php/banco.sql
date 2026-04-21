-- ============================================================
--  MOTIRÕ — Script de Criação do Banco de Dados
--  Startup de Inclusão Digital | IFSP Campus Guarulhos
--  Execute este script no phpMyAdmin após criar o banco "computacao_motiro"
-- ============================================================

CREATE DATABASE IF NOT EXISTS computacao_motiro
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE computacao_motiro;

-- ------------------------------------------------------------
--  Tabela: mensagens_contato
--  Armazena todas as mensagens enviadas pelo formulário de contato
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mensagens_contato (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(150)     NOT NULL COMMENT 'Nome do remetente',
    email         VARCHAR(255)     NOT NULL COMMENT 'E-mail do remetente',
    telefone      VARCHAR(20)               COMMENT 'Telefone / WhatsApp (opcional)',
    organizacao   VARCHAR(200)              COMMENT 'Empresa ou organização (opcional)',
    assunto       VARCHAR(100)     NOT NULL COMMENT 'Categoria do assunto selecionado',
    mensagem      TEXT             NOT NULL COMMENT 'Conteúdo da mensagem',
    ip_origem     VARCHAR(45)               COMMENT 'IP do remetente (IPv4 ou IPv6)',
    status        ENUM('nova','lida','respondida','arquivada')
                                   NOT NULL DEFAULT 'nova',
    data_envio    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_leitura  DATETIME                  COMMENT 'Quando foi aberta pela equipe',
    PRIMARY KEY (id),
    INDEX idx_email    (email),
    INDEX idx_status   (status),
    INDEX idx_assunto  (assunto),
    INDEX idx_data     (data_envio)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Mensagens recebidas pelo formulário de contato do portal Motirõ';

-- ------------------------------------------------------------
--  View útil para o painel administrativo
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_mensagens_novas AS
SELECT
    id,
    nome,
    email,
    telefone,
    organizacao,
    assunto,
    LEFT(mensagem, 120) AS preview,
    data_envio
FROM mensagens_contato
WHERE status = 'nova'
ORDER BY data_envio DESC;

-- ------------------------------------------------------------
--  (Opcional) Tabela de log de e-mails enviados
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS log_emails (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    mensagem_id     INT UNSIGNED NOT NULL,
    destinatario    VARCHAR(255) NOT NULL,
    status          ENUM('enviado','falhou') NOT NULL DEFAULT 'enviado',
    erro_detalhe    TEXT,
    data_envio      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mensagem_id) REFERENCES mensagens_contato(id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

