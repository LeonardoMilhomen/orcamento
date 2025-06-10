<?php
// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Inclui o autoload do Composer (essencial)
// Certifique-se de que o caminho para 'vendor/autoload.php' está correto
require 'vendor/autoload.php';

// Apenas executa se a requisição for do tipo POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Acesso inválido.']);
    exit;
}

// ---- DADOS DO SEU E-MAIL (JÁ CONFIGURADOS) ----
$seuEmailGmail = 'jessicanogueira.contatoo@gmail.com';     // E-MAIL GMAIL AUTENTICADOR E DESTINATÁRIO
$suaSenhaDeApp = 'chave';            // SENHA DE APP (espaços removidos)
$emailDeDestino = 'jessicanogueira.contatoo@gmail.com';    // E-MAIL QUE RECEBERÁ AS PROPOSTAS
$nomeDeDestino = 'Jessica Nogueira';              // NOME DE QUEM RECEBERÁ

// Coleta e sanitiza os dados do formulário
$nome = htmlspecialchars($_POST['name'] ?? 'Não informado');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$whatsapp = htmlspecialchars($_POST['whatsapp'] ?? 'Não informado');
$tipoProduto = htmlspecialchars($_POST['product-type'] ?? 'Não informado');
$nomeEmpresa = htmlspecialchars($_POST['company-name'] ?? 'Não informado');
$siteEmpresa = htmlspecialchars($_POST['company-site'] ?? 'Não informado');
$instagramEmpresa = htmlspecialchars($_POST['company-instagram'] ?? 'Não informado');
$tipoParceria = htmlspecialchars($_POST['partnership-type'] ?? 'Não informado');
$descricao = htmlspecialchars($_POST['description'] ?? 'Não informado');

// Cria uma nova instância do PHPMailer
$mail = new PHPMailer(true);

try {
    // Configurações do servidor SMTP do Google
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $seuEmailGmail;
    $mail->Password   = $suaSenhaDeApp;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Destinatários
    $mail->setFrom($seuEmailGmail, 'Site Propostas'); // Remetente (sempre seu e-mail)
    $mail->addAddress($emailDeDestino, $nomeDeDestino);    // E-mail que vai receber a proposta
    $mail->addReplyTo($email, $nome);                      // Para poder responder diretamente ao cliente

    // Constrói o corpo do e-mail em HTML
    $corpoEmail = "<h1>Nova Proposta de Parceria</h1>";
    $corpoEmail .= "<p><strong>Nome:</strong> {$nome}</p>";
    $corpoEmail .= "<p><strong>E-mail:</strong> {$email}</p>";
    $corpoEmail .= "<p><strong>WhatsApp:</strong> {$whatsapp}</p>";
    $corpoEmail .= "<p><strong>Nome da Empresa:</strong> {$nomeEmpresa}</p>";
    $corpoEmail .= "<p><strong>Instagram:</strong> {$instagramEmpresa}</p>";
    $corpoEmail .= "<p><strong>Site:</strong> {$siteEmpresa}</p>";
    $corpoEmail .= "<p><strong>Tipo de Produto:</strong> {$tipoProduto}</p>";
    $corpoEmail .= "<p><strong>Tipo de Parceria:</strong> {$tipoParceria}</p>";
    
    // Adiciona as informações da cotação, se existirem
    if (isset($_POST['valor_cotado'])) {
        $dataPub = htmlspecialchars($_POST['data_publicacao']);
        $tipoCont = htmlspecialchars($_POST['tipo_conteudo']);
        $valorCotado = htmlspecialchars($_POST['valor_cotado']);
        $corpoEmail .= "<hr><h3>Informações da Cotação</h3>";
        $corpoEmail .= "<p><strong>Publicação Solicitada:</strong> {$tipoCont} para o dia {$dataPub}</p>";
        $corpoEmail .= "<p><strong>Valor Cotado:</strong> R$ {$valorCotado}</p>";
    }

    $corpoEmail .= "<hr><h3>Descrição da Proposta</h3>";
    $corpoEmail .= "<p>" . nl2br($descricao) . "</p>"; // nl2br para manter as quebras de linha

    // Conteúdo do E-mail
    $mail->isHTML(true);
    $mail->Subject = 'Nova Proposta de Parceria de: ' . $nome;
    $mail->Body    = $corpoEmail;
    $mail->AltBody = strip_tags($corpoEmail); // Versão em texto plano

    $mail->send();
    // Retorna uma resposta de sucesso em JSON
    echo json_encode(['status' => 'success', 'message' => 'Proposta enviada com sucesso!']);

} catch (Exception $e) {
    // Em caso de erro, retorna uma resposta de erro em JSON
    http_response_code(500); // Define o código de erro HTTP
    echo json_encode(['status' => 'error', 'message' => "A mensagem não pôde ser enviada. Erro: {$mail->ErrorInfo}"]);
}
?>