<?php
/**
 * MOTIRÕ — Processador do Formulário de Contato
 * Recebe POST do formulário, valida, salva no BD e envia e-mail ao Scrum Master
 * 
 * Retorna JSON: { "sucesso": true/false, "mensagem": "..." }
 */

declare(strict_types=1);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Carrega configurações
require_once __DIR__ . '/db_config.php';

// ── Só aceita POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

// ── Funções auxiliares ──
function limpar(string $valor): string {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

function responder(bool $sucesso, string $mensagem, int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Coleta e sanitiza os dados ──
$nome         = limpar($_POST['nome']         ?? '');
$email        = limpar($_POST['email']        ?? '');
$telefone     = limpar($_POST['telefone']     ?? '');
$organizacao  = limpar($_POST['organizacao']  ?? '');
$assunto      = limpar($_POST['assunto']      ?? '');
$mensagem     = limpar($_POST['mensagem']     ?? '');
$concordo     = isset($_POST['concordo']) && $_POST['concordo'] === 'on';
$ipOrigem     = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

// ── Validação server-side ──
$erros = [];

if (strlen($nome) < 3)
    $erros[] = 'O nome deve ter pelo menos 3 caracteres.';

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $erros[] = 'E-mail inválido.';

$assuntosPermitidos = [
    'consultoria','capacitacao','prestacao','engenharia',
    'parceria','voluntariado','imprensa','outro'
];
if (!in_array($assunto, $assuntosPermitidos, true))
    $erros[] = 'Assunto inválido.';

if (strlen($mensagem) < 20)
    $erros[] = 'A mensagem deve ter pelo menos 20 caracteres.';

if (!$concordo)
    $erros[] = 'É necessário concordar com os termos de uso dos dados.';

if (!empty($erros)) {
    responder(false, implode(' | ', $erros), 422);
}

// ── Rate limiting simples (por sessão) ──
session_start();
$agora    = time();
$ultimoEnvio = $_SESSION['ultimo_envio_contato'] ?? 0;
if (($agora - $ultimoEnvio) < 60) {
    responder(false, 'Por favor, aguarde 1 minuto antes de enviar outra mensagem.', 429);
}

// ── Salva no banco de dados ──
try {
    $pdo = conectarBD();
    $sql = "INSERT INTO mensagens_contato
                (nome, email, telefone, organizacao, assunto, mensagem, ip_origem)
            VALUES
                (:nome, :email, :telefone, :organizacao, :assunto, :mensagem, :ip)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'        => $nome,
        ':email'       => $email,
        ':telefone'    => $telefone ?: null,
        ':organizacao' => $organizacao ?: null,
        ':assunto'     => $assunto,
        ':mensagem'    => $mensagem,
        ':ip'          => substr($ipOrigem, 0, 45),
    ]);
    $mensagemId = (int) $pdo->lastInsertId();
} catch (PDOException $e) {
    // Loga o erro sem expor detalhes ao cliente
    error_log('[Motirõ Contato] Erro no banco: ' . $e->getMessage());
    responder(false, 'Erro interno ao salvar a mensagem. Tente novamente mais tarde.', 500);
}

// ── Mapeia assunto para texto legível ──
$assuntoTexto = match($assunto) {
    'consultoria'  => 'Consultoria em Transformação Digital',
    'capacitacao'  => 'Capacitação / Educação Digital',
    'prestacao'    => 'Prestação de Serviço Tecnológico',
    'engenharia'   => 'Projetos de Engenharia / Tecnologia Assistiva',
    'parceria'     => 'Proposta de Parceria',
    'voluntariado' => 'Voluntariado / Colaboração',
    'imprensa'     => 'Imprensa / Comunicação',
    default        => 'Outro Assunto',
};

// ── Envia e-mail ao Scrum Master ──
$emailDestinatario = SCRUM_MASTER_EMAIL;
$emailRemetente    = 'kaua.oliveira1@aluno.igsp.edu.br';
$nomeSite          = NOME_SITE;

$assuntoEmail = "[{$nomeSite}] Nova mensagem: {$assuntoTexto}";

$corpoEmail = "
<!DOCTYPE html>
<html lang='pt-BR'>
<head><meta charset='UTF-8'><title>Nova Mensagem de Contato</title></head>
<body style='font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;'>
<div style='max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.1);'>

  <div style='background:#1B5E20; padding:28px 32px;'>
    <h1 style='color:#ffffff; margin:0; font-size:1.4rem;'>MOTIRÕ</h1>
    <p style='color:#A5D6A7; margin:4px 0 0; font-size:0.85rem; letter-spacing:0.1em;'>NOVA MENSAGEM DO PORTAL</p>
  </div>

  <div style='padding:32px;'>
    <h2 style='color:#1B5E20; margin-top:0;'>Nova mensagem recebida</h2>
    <p style='color:#555;'>Uma nova mensagem foi enviada pelo formulário de contato do portal Motirõ (ID #{$mensagemId}).</p>

    <table style='width:100%; border-collapse:collapse; margin-top:20px;'>
      <tr style='background:#E8F5E9;'>
        <td style='padding:12px 16px; font-weight:bold; color:#1B5E20; width:140px; border-bottom:1px solid #C8E6C9;'>Nome</td>
        <td style='padding:12px 16px; color:#333; border-bottom:1px solid #C8E6C9;'>" . htmlspecialchars($nome) . "</td>
      </tr>
      <tr>
        <td style='padding:12px 16px; font-weight:bold; color:#1B5E20; border-bottom:1px solid #C8E6C9;'>E-mail</td>
        <td style='padding:12px 16px; color:#333; border-bottom:1px solid #C8E6C9;'><a href='mailto:" . htmlspecialchars($email) . "' style='color:#2E7D32;'>" . htmlspecialchars($email) . "</a></td>
      </tr>
      <tr style='background:#E8F5E9;'>
        <td style='padding:12px 16px; font-weight:bold; color:#1B5E20; border-bottom:1px solid #C8E6C9;'>Telefone</td>
        <td style='padding:12px 16px; color:#333; border-bottom:1px solid #C8E6C9;'>" . ($telefone ?: '<em style="color:#999">Não informado</em>') . "</td>
      </tr>
      <tr>
        <td style='padding:12px 16px; font-weight:bold; color:#1B5E20; border-bottom:1px solid #C8E6C9;'>Organização</td>
        <td style='padding:12px 16px; color:#333; border-bottom:1px solid #C8E6C9;'>" . ($organizacao ?: '<em style="color:#999">Não informada</em>') . "</td>
      </tr>
      <tr style='background:#E8F5E9;'>
        <td style='padding:12px 16px; font-weight:bold; color:#1B5E20; border-bottom:1px solid #C8E6C9;'>Assunto</td>
        <td style='padding:12px 16px; color:#333; border-bottom:1px solid #C8E6C9;'><strong>{$assuntoTexto}</strong></td>
      </tr>
    </table>

    <div style='background:#F1F8F2; border-left:4px solid #2E7D32; border-radius:8px; padding:20px; margin-top:24px;'>
      <h4 style='color:#1B5E20; margin-top:0;'>Mensagem:</h4>
      <p style='color:#333; line-height:1.8; margin:0; white-space:pre-wrap;'>" . htmlspecialchars($mensagem) . "</p>
    </div>

    <div style='margin-top:24px; padding:16px; background:#fff3e0; border-radius:8px; border:1px solid #ffe0b2;'>
      <small style='color:#e65100;'>⚠ Responda diretamente para: <strong>" . htmlspecialchars($email) . "</strong></small>
    </div>
  </div>

  <div style='background:#E8F5E9; padding:16px 32px; text-align:center;'>
    <small style='color:#5A7A5C;'>
      Esta é uma mensagem automática do sistema Motirõ.<br>
      ID da mensagem: #{$mensagemId} • IP: {$ipOrigem} • " . date('d/m/Y H:i:s') . "
    </small>
  </div>
</div>
</body>
</html>
";

$cabecalhosEmail = implode("\r\n", [
    "MIME-Version: 1.0",
    "Content-Type: text/html; charset=UTF-8",
    "From: {$nomeSite} <{$emailRemetente}>",
    "Reply-To: {$nome} <{$email}>",
    "X-Mailer: PHP/" . PHP_VERSION,
    "X-Priority: 1",
]);

$emailEnviado = mail($emailDestinatario, $assuntoEmail, $corpoEmail, $cabecalhosEmail);

// ── Loga resultado do e-mail ──
try {
    $sqlLog = "INSERT INTO log_emails (mensagem_id, destinatario, status, erro_detalhe)
               VALUES (:id, :dest, :status, :erro)";
    $stmtLog = $pdo->prepare($sqlLog);
    $stmtLog->execute([
        ':id'    => $mensagemId,
        ':dest'  => $emailDestinatario,
        ':status'=> $emailEnviado ? 'enviado' : 'falhou',
        ':erro'  => $emailEnviado ? null : 'mail() retornou false',
    ]);
} catch (PDOException $e) {
    error_log('[Motirõ Contato] Erro ao logar e-mail: ' . $e->getMessage());
}

// ── Atualiza sessão para rate limit ──
$_SESSION['ultimo_envio_contato'] = $agora;

// ── Resposta final ──
responder(
    true,
    'Mensagem enviada com sucesso! Nossa equipe responderá em breve.'
);
