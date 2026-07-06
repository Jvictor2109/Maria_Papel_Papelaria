<?php
session_start();
include("db_connect.php");
require_once("vendor/autoload.php");
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$jsonRecebido = file_get_contents('php://input');
	$request = json_decode($jsonRecebido, true);

	
    switch($request["tipo_email"]){
		case "registada":
			$encomenda = $request["encomenda"];
            $caminho = $request["caminho_pdf"];
            $num_encomenda = $encomenda["num_encomenda"];
            $corpo_email = "Segue em anexo o comprovativo da encomenda N$num_encomenda. <br>
						Será contactado(a) novamente por este meio, assim que estiver tudo pronto. <br><br>
						Os melhores cumprimentos, <br>
						Maria Papel Papelaria";
            enviar_email($conn, $encomenda, $corpo_email, $caminho);
			exit();
		
		case "novo_aviso":
			$stmt = $conn->prepare(
				"SELECT * FROM encomenda
				WHERE id_encomenda = ?"
			);
			$stmt->bind_param("i", $request["id_encomenda"]);
			$stmt->execute();
			$result = $stmt->get_result();
			$encomenda = $result->fetch_assoc();

			$num_encomenda = $encomenda["num_encomenda"];
			$corpo_email = "Pode vir levantar a sua encomenda N$num_encomenda \n <br><br>
							Os melhores cumprimentos, <br>
							Maria Papel Papelaria";
			enviar_email($conn, $encomenda, $corpo_email);
			exit();
    }
}

function enviar_email(mysqli $conn, array $encomenda, string $corpo_email, string $caminho = null){
    // Pega as informações necessárias da encomenda
    $nome = $encomenda["nome_aluno_encomenda"];
    $email = $encomenda["email_encomenda"];
    $num_encomenda = $encomenda["num_encomenda"];


	$stmt = $conn->prepare(
	"SELECT nome_ano_letivo FROM ano_letivo
	WHERE ano_letivo_ativo = 1"
	);
	$stmt->execute();
	$result = $stmt->get_result();
	$ano_letivo = $result->fetch_assoc();
	$nome_ano_letivo = $ano_letivo["nome_ano_letivo"];
	$stmt->close();

	try {
		$mail = new PHPMailer(true);

		// Configuração do servidor SMTP
		$mail->isSMTP();
		$mail->Host       = 'smtp.gmail.com';
		$mail->SMTPAuth   = true;
		$mail->Username   = $_ENV["SMTP_USER"];
		$mail->Password   = $_ENV["SMTP_PASS"];
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port       = 587;

		// Remetente e destinatário
		$mail->setFrom($_ENV["SMTP_USER"], 'Maria Papel Papelaria');
		$mail->addAddress($email, $nome);

		// Caminho absoluto do PDF no servidor
        if(isset($caminho)){
            $caminhoPdf = $_SERVER["DOCUMENT_ROOT"] . $caminho;
            $mail->addAttachment($caminhoPdf);
        }

		// Conteúdo do email
		$mail->isHTML(true);
		$mail->Subject = "Manuais Escolares $nome_ano_letivo - Encomenda N$num_encomenda";
		$mail->Body = $corpo_email;

		$mail->send();
		return true;
	} catch (Exception $e) {
		return false;
	}
}

?>