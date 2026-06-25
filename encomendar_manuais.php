<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');
require_once("vendor/autoload.php");
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'), true);

	switch($request["acao"]){
		case "filtrar_manuais":
			echo json_encode(filtrarManuais($conn, $request));
			exit();
		
		case "get_id_encomenda":
			echo json_encode(['resultado'=>getIdEncomenda($conn,$request)]);
			exit();
		
		case "adicionar_encomenda":
			adcEncomenda($conn, $request);
			exit();
	}
}


function adcEncomenda(mysqli $conn, array $request){
	$encomenda = $request["encomenda"];

	// Começa uma transação. ou tudo entra na base de dados, ou nada entra
	// Atualiza encomenda, manual, encomenda_manual, e ano_escolar
	$conn->begin_transaction();

	try{
		// Adiciona linha na tabela encomenda
		$stmtEncomenda = $conn->prepare(
			"INSERT INTO encomenda (data_encomenda, nome_aluno_encomenda, nif_encomenda, ee_encomenda,
			telefone_encomenda, email_encomenda, num_encomenda, plast_manuais, plast_livro_fichas, etiquetas,
			obs_etiquetas, obs_encomenda, total_encomenda, valor_caucao, id_utilizador, codigo_mega, id_ano_encomenda)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
		);

		$data_encomenda = date("Y-m-d");
		$nome_aluno_encomenda = $encomenda["nome_aluno"];
		$nif_encomenda = $encomenda["nif"];
		$ee_encomenda = $encomenda["nome_ee"];
		$telefone_encomenda = $encomenda["telemovel"];
		$email_encomenda = $encomenda["email"];
		$num_encomenda = getIdEncomenda($conn, $encomenda);
		$plast_manuais = $encomenda["plast_manuais"];
		$plast_livro_fichas = $encomenda["plast_livro_fichas"];
		$etiquetas = $encomenda["etiqueta"];
		$obs_etiquetas = $encomenda["obs_etiquetas"];
		$obs_encomenda = $encomenda["obs_encomenda"];
		$total_encomenda = floatval($encomenda["total_encomenda"]);
		$caucao_paga = floatval($encomenda["caucao_paga"]);
		$id_utilizador = $_SESSION["user_id"];
		$codigoMega = $encomenda["codigoMega"];
		$id_ano_encomenda = $encomenda["id_ano_escolar"];

		$stmtEncomenda->bind_param("ssssssiiiissddisi", $data_encomenda, $nome_aluno_encomenda, $nif_encomenda, $ee_encomenda,
		$telefone_encomenda, $email_encomenda, $num_encomenda, $plast_manuais, $plast_livro_fichas, $etiquetas, $obs_etiquetas,
		$obs_encomenda, $total_encomenda, $caucao_paga, $id_utilizador, $codigoMega, $id_ano_encomenda);

		$stmtEncomenda->execute();
		
		// Salva o id da encomenda pra usar depois
		$id_encomenda = $conn->insert_id;

		// Adiciona linhas na tabela encomenda_manual
		$stmtEncomendaManual = $conn->prepare(
			"INSERT INTO encomenda_manual (id_encomenda, id_manual, voucher)
			VALUES (?,?,?)"
		);

		// Atualiza linha na tabela manual
		$stmtManual = $conn->prepare(
			"UPDATE manual
			SET quant_manuais_pedir = quant_manuais_pedir + 1, quant_manuais_enc = quant_manuais_enc + 1
			WHERE id_manual = ?"
		);

		foreach($encomenda["manuais"] as $manual){
			if($manual["voucher"] == "Sim"){
				$voucher = 1;
			}
			else{
				$voucher = 0;
			}

			$stmtEncomendaManual->bind_param("iii", $id_encomenda, $manual["id_manual"], $voucher);
			$stmtEncomendaManual->execute();

			$stmtManual->bind_param("i", $manual["id_manual"]);
			$stmtManual->execute();
		}
		

		// Adiciona +1 em encomendas_ano na tabela ano_escolar
		$stmtAnoEscolar = $conn->prepare(
			"UPDATE ano_escolar
			SET encomendas_ano = encomendas_ano + 1
			WHERE id_ano_escolar = ?"
		);

		$stmtAnoEscolar->bind_param("i", $encomenda["id_ano_escolar"]);
		$stmtAnoEscolar->execute();

		// Adiciona observação na encomenda, dizendo que foi registada.
		$observacao_registada = "A encomenda foi registada.";
		$data_registada = date("Y-m-d H:i:s");
		$stmt_registada = $conn->prepare(
			"INSERT INTO observacao_encomenda (id_encomenda, observacao_encomenda, data_observacao, id_utilizador)
			VALUES (?,?,?,?)"
		);
		$stmt_registada->bind_param("issi", $id_encomenda, $observacao_registada, $data_registada, $id_utilizador);
		$stmt_registada->execute();

		
		// Cria o pdf e salva o caminho na base de dados
		$caminho = gerarPdf($conn, $encomenda, $num_encomenda);
		
		$stmtPdf = $conn->prepare(
			"UPDATE encomenda 
			SET doc_encomenda = ?
			WHERE id_encomenda = ?"
		);

		$stmtPdf->bind_param("si", $caminho,$id_encomenda);
		$stmtPdf->execute();
		
		echo json_encode(['resultado'=>'sucesso', 'caminho_pdf'=>$caminho, 'num_encomenda'=>$num_encomenda]);

		// Manda o email
		if($email_encomenda){
			if(!enviar_email($caminho, $email_encomenda, $nome_aluno_encomenda, $num_encomenda)){
				echo json_encode(['resultado'=>'Falha ao enviar email']);
				exit();
			}
		}


		$stmtPdf->close();
		$stmt_registada->close();
		$stmtEncomenda->close();
		$stmtEncomendaManual->close();
		$stmtManual->close();
		$stmtAnoEscolar->close();
		$conn->commit();
	}
	catch(Exception $e){
		$conn->rollback();
		echo json_encode(['resultado'=> 'Não foi possível adicionar a encomenda à base de dados.']);
		return;
	}
}

function enviar_email(string $caminho, string $email, string $nome, string $num_encomenda){
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
		$mail->setFrom($_ENV["SMTP_USER"], 'Maria Papel');
		$mail->addAddress($email, $nome);

		// Caminho absoluto do PDF no servidor
		$caminhoPdf = $_SERVER["DOCUMENT_ROOT"] . $caminho;
		$mail->addAttachment($caminhoPdf);

		// Conteúdo do email
		$mail->isHTML(true);
		$mail->Subject = 'Encomenda N' . $num_encomenda;
		$mail->Body    = "A encomenda Nº$num_encomenda foi realizada com sucesso.";

		$mail->send();
		return true;
	} catch (Exception $e) {
		var_dump($e);
		return false;
	}
}


function gerarPdf(mysqli $conn, array $encomenda, $num_encomenda){
	// Extrai as informações
	$nome_aluno_encomenda = $encomenda["nome_aluno"];
	$nome_aluno_encomenda = $encomenda["nome_aluno"];

	$nif_encomenda = $encomenda["nif"];
	$nif_encomenda = str_split($nif_encomenda, 3);
	$nif_encomenda = implode(' ', $nif_encomenda);

	$ee_encomenda = $encomenda["nome_ee"];

	$telefone_encomenda = $encomenda["telemovel"];
	$telefone_encomenda = str_split($telefone_encomenda, 3);
	$telefone_encomenda = implode(' ', $telefone_encomenda);

	$email_encomenda = $encomenda["email"];
	$plast_manuais = $encomenda["plast_manuais"];
	$plast_livro_fichas = $encomenda["plast_livro_fichas"];
	$etiquetas = $encomenda["etiqueta"];
	$obs_etiquetas = $encomenda["obs_etiquetas"];
	$obs_encomenda = $encomenda["obs_encomenda"];
	$total_encomenda = floatval($encomenda["total_encomenda"]);
	$caucao_paga = floatval($encomenda["caucao_paga"]);
	$id_utilizador = $_SESSION["user_id"];
	$ano = $encomenda["ano"];
	$codigoMega = $encomenda["codigoMega"];
	$agrupamento = $encomenda["agrupamento"];

	$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

	$pdf->SetCreator('Maria Papel'); 
	$pdf->setPrintHeader(false);      
	$pdf->setPrintFooter(false);       
	$pdf->SetMargins(15, 15, 15);      
	$pdf->AddPage();

	// Cabeçalho
	$pdf->SetFont('helvetica', 'B', 14);
	$pdf->Cell(180, 7, 'MARIA PAPEL PAPELARIA', 0, 1, 'C');
	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->Cell(0, 6, 'Encomenda de Manuais Escolares', 0, 1, 'C');

	$stmt = $conn->prepare(
		"SELECT nome_ano_letivo FROM ano_letivo
		WHERE ano_letivo_ativo = 1"
	);
	$stmt->execute();
	$result = $stmt->get_result();
	$ano_letivo = $result->fetch_assoc();
	$nome_ano_letivo = $ano_letivo["nome_ano_letivo"];
	$stmt->close();

	$pdf->Cell(0, 6, "Ano Letivo $nome_ano_letivo", 0, 1, 'C');
	$pdf->Ln(8);

	// Informações do aluno
	$pdf->SetFont('helvetica', '', 10);
	$largura = ($pdf->getPageWidth() - 30) / 2;

	$pdf->Cell($largura, 5, 'Aluno(a): ' . $nome_aluno_encomenda, 0, 0);
	$pdf->Cell($largura, 5, 'Encomenda: ' . $num_encomenda, 0, 1, 'R');

	$pdf->Cell($largura, 5, 'Encarregado de educação: ' . $ee_encomenda, 0, 0);
	$pdf->Cell($largura, 5, 'Ano: ' . $encomenda['ano'], 0, 1, 'R');

	$pdf->Cell($largura, 5, 'Contacto: ' . $telefone_encomenda, 0, 0);
	$pdf->Cell($largura, 5, 'NIF: ' . $nif_encomenda, 0, 1, 'R');

	$pdf->Cell($largura, 5, 'Data: ' . date("d/m/Y"), 0, 0);
	$pdf->Cell($largura, 5, 'Agrupamento: ' . $agrupamento, 0, 1, 'R');

	$pdf->Cell($largura, 5, 'Email: ' . $email_encomenda, 0, 0);

	$pdf->Ln(8);

	// Tabela
	$pdf->SetFont('helvetica', 'B', 9);
	$pdf->SetFillColor(220, 220, 220);

	// Cabeçalho da tabela
	$pdf->Cell(25, 7, 'ISBN',        1, 0, 'L', true);
	$pdf->Cell(78, 7, 'Nome',        1, 0, 'L', true);
	$pdf->Cell(28, 7, 'Disciplina',  1, 0, 'L', true);
	$pdf->Cell(22, 7, 'Tipo',        1, 0, 'C', true);
	$pdf->Cell(16, 7, 'Voucher',     1, 0, 'C', true);
	$pdf->Cell(11,  7, 'Preço',       1, 1, 'R', true);

	// Linhas da tabela
	$pdf->SetFont('helvetica', '', 8);
	foreach ($encomenda["manuais"] as $manual) {
		$pdf->Cell(25, 6, $manual['isbn'], 1, 0, 'L');
		$pdf->Cell(78, 6, $manual['nome'], 1, 0, 'L');
		$pdf->Cell(28, 6, $manual['disciplina'], 1, 0, 'L');
		$pdf->Cell(22, 6, $manual['tipo_manual'], 1, 0, 'C');
		$pdf->Cell(16, 6, $manual['voucher'], 1, 0, 'C');
		$pdf->Cell(11, 6, round(floatval($manual["preco"]), 2), 1, 1, 'R');
	}
	$pdf->Cell(0, 5, 'Aviso: Os preços apresentados poderão ser atualizados em caso de alterações por parte das editoras ou distribuidores.',0,1);
	$pdf->Ln(8);

	// Informações da encomenda
	$pdf->SetFont('helvetica', '', 10);
	
	$pdf->Cell($largura, 5, 'Total da encomenda: ' . $total_encomenda, 0, 0);
	// Plastificar Manuais
	if ($plast_manuais) {
		$pdf->Cell($largura, 5, 'Plastificar Manuais: Sim', 0, 1, 'R');
	} else {
		$pdf->Cell($largura, 5, 'Plastificar Manuais: Não', 0, 1, 'R');
	}

	$pdf->Cell($largura, 5, 'Valor pago: ' . $caucao_paga, 0, 0);
	// Plastificar Livros de Fichas
	if ($plast_livro_fichas) {
		$pdf->Cell($largura, 5, 'Plastificar livros de fichas: Sim', 0, 1, 'R');
	} else {
		$pdf->Cell($largura, 5, 'Plastificar livros de fichas: Não', 0, 1, 'R');
	}

	$pdf->Cell($largura, 5, 'Falta pagar: ' . ($total_encomenda-$caucao_paga), 0, 0);
	// Etiquetas
	if ($etiquetas) {
		$pdf->Cell($largura, 5, 'Etiquetas: Sim - ' . $obs_etiquetas, 0, 1, 'R');
	} else {
		$pdf->Cell($largura, 5, 'Etiquetas: Não', 0, 1, 'R');
	}
	$pdf->Cell(0, 5, 'Observações: ' . $obs_encomenda, 0, 1);
	$pdf->Cell(0, 5, 'Código MEGA: ' . $codigoMega, 0, 1);

	// Rodapé
	$stmtUtilizador = $conn->prepare("SELECT username FROM utilizador WHERE id_utilizador = ?");
	$stmtUtilizador->bind_param("i", $id_utilizador);
	$stmtUtilizador->execute();
	$result = $stmtUtilizador->get_result();
	$row = $result->fetch_assoc();
	$utilizador = $row["username"];

	$pdf->setY(-40);
	$pdf->SetFont('helvetica', '', 8);
	$pdf->Cell(0, 5, 'Ao efetuar a encomenda, autoriza o tratamento dos dados pessoais fornecidos para efeitos de gestão e faturação da mesma, nos termos do RGPD.',0,1, 'C');
	$texto_rodape = 'Documento gerado no dia ' . date("d/m/Y") . ' às ' . date("H:i:s") . ' | Utilizador: ' . $utilizador;
	$pdf->Cell(180, 5, $texto_rodape, 0, 1, 'C');

	$pasta_relativa = '/MPP_3/encomendas/' . $ano . '/';
	$arquivo = 'encomenda_' . $num_encomenda . '.pdf';

	$pasta_absoluta = __DIR__ . '/encomendas/' . $ano . '/';

	if (!is_dir($pasta_absoluta)) {
		mkdir($pasta_absoluta, 0755, true);
	}

	// Salva o PDF no disco com caminho absoluto
	$pdf->Output($pasta_absoluta . $arquivo, 'F');
	$stmtUtilizador->close();
	// Retorna o caminho pra ser salvo na base de dados
	return $pasta_relativa . $arquivo;
}


function filtrarManuais(mysqli $conn, array $request){
	// Constrói o WHERE da query
	$condicoes = [];
	$parametros = [];
	$tipos_dados = "";

	if(!empty($request['agrupamento'])){
		$condicoes[] = "id_agrupamento = ?";
		$parametros[] = $request['agrupamento'];
		$tipos_dados .= "i";
	}
		
	if(!empty($request["ano_escolar"])){
		$condicoes[] = "id_ano_escolar = ?";
		$parametros[] = $request['ano_escolar'];
		$tipos_dados .= "i";
	}

	if(!empty($request["tipo_manual"])){
		$condicoes[] = "tipo_manual = ?";
		$parametros[] = $request['tipo_manual'];
		$tipos_dados .= "s";
	}

	if(count($condicoes) == 0){
		$where = "";
	}
	else{
		$where = 'WHERE ' . implode(' AND ', $condicoes);
	}

	$sql = "SELECT DISTINCT manual.*, nome_disciplina FROM manual
			JOIN manual_agrupamento ON manual.id_manual=manual_agrupamento.id_manual
			JOIN manual_ano_escolar ON manual.id_manual=manual_ano_escolar.id_manual
			JOIN disciplina	 ON manual.id_disciplina=disciplina.id_disciplina 
			$where
			ORDER BY manual.id_disciplina ASC, manual.tipo_manual ASC";
	
	$stmt = $conn->prepare($sql);

	if(!$stmt){
		die($conn->error);
	}

	if($tipos_dados){
		$stmt->bind_param($tipos_dados, ...$parametros);
	}

	$stmt->execute();
	$resultado = $stmt->get_result();
	return $resultado->fetch_all(MYSQLI_ASSOC);
}


function getIdEncomenda(mysqli $conn, array $request){
	$id_ano_escolar = intval($request["id_ano_escolar"]);

	$sql = "SELECT encomendas_ano FROM ano_escolar
			WHERE id_ano_escolar = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $id_ano_escolar);
	$stmt->execute();
	$resultado = $stmt->get_result();
	$resultado = $resultado->fetch_assoc();
	return $resultado["encomendas_ano"] +1;
}

?>

<!DOCTYPE HTML>
<!--
	Editorial by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>MPP - Encomendar Manuais</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
	</head>
	<body class="is-preload">

		<!-- Wrapper -->
		<div id="wrapper">

			<!-- Main -->
			<div id="main">
				<div class="inner">

					<!-- Header -->
					<?php include('header.php'); ?>
					<!-- Banner -->
					<section id="search" class="alt">
					<?php
					// Verificar se já está autenticado
					if (isset($_SESSION['user_id'])) {?>

                        <!-- Modal de confirmação de encomenda -->
						<div id="modal-confirmar" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal-confirmar" class="modal-close">&times;</span>

								<h3>Confirmar encomenda</h3>

								<div id="confirmar-content">
									<!-- Informações da encomenda -->
									<div class="box">
										<h4>Encomenda Nº<span id="idEncomenda"></span></h4>

										<div class="row">
											<div class="col-6">
												<strong>Agrupamento: </strong>
												<span id="confirmar_agrupamento"></span>
											</div>

											<div class="col-3">
												<strong>Ano escolar: </strong>
												<span id="confirmar_ano"></span>
											</div>
										</div>
									</div>

									<!-- Tabela de manuais selecionados e especificações da encomenda-->
									<div class="box">
										<h4>Manuais selecionados</h4>
										
										<div class="table-wrapper">
											<table class="alt">
												<thead>
													<tr>
														<th>ISBN</th>
														<th>Nome</th>
														<th>Preço</th>
														<th>Voucher</th>
													</tr>
												</thead>
												<tbody id="tabela-confirmar">

												</tbody>
											</table>
										</div>

										<div class="row">
											<div class="col-3">
												<strong>Total encomenda: </strong><span id="confirmarTotalEncomenda"></span>
											</div>
											<div class="col-3">
												<strong>Caução paga: </strong><span id="confirmarCaucaoPaga"></span>
											</div>
										</div>

										<div class="row">
											<div class="col-3">
												<strong>Plastificar Manuais: </strong><span id="confirmarPlastManuais"></span>
											</div>
											<div class="col-4">
												<strong>Plastificar Livro de fichas: </strong><span id="confirmarPlastLivroFichas"></span>
											</div>
										</div>

										<div class="row">
											<div class="col-12">
												<strong>Etiquetas: </strong><span id="confirmarEtiquetas"></span>
											</div>
										</div>
										<div class="row">
											<div class="col-12">
												<strong>Observações: </strong><span id="confirmarObs"></span>
											</div>
										</div>

										<div class="row">
											<div class="col-12">
												<strong>Código MEGA: </strong>
												<span id="confirmarCodigoMega"></span>
											</div>
										</div>

									</div>

									<!-- Informações de contato -->
									<div class="box">
										<h4>Informações do Aluno e encarregado de educação</h4>

										<div class="row">
											<div class="col-6">
												<strong>Nome do aluno: </strong><span id="confirmarAluno"></span>
											</div>
											<div class="col-6">
												<strong>NIF: </strong><span id="confirmarNIF"></span>
											</div>
										</div>

										<div class="row">
											<div class="col-12">
												<strong>Nome do encarregado de educação: </strong><span id="confirmarEnc"></span>
											</div>
										</div>

										<div class="row">
											<div class="col-6">
												<strong>Email: </strong><span id="confirmarEmail"></span>
											</div>
											<div class="col-6">
												<strong>Telemóvel: </strong><span id="confirmarTelemovel"></span>
											</div>
										</div>
									</div>

									<!-- Botões de ação -->
									<div class="row">
										<div class="col-4 col-12-small" style="margin-bottom: 10px;">
											<button id="btnConfirmarEncomenda" class="primary">Confirmar encomenda</button>
										</div>
										<div class="col-4 col-12-small">
											<button id="btnCancelarEncomenda">Voltar atrás</button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Modal de sucesso -->
						<div id="modal-sucesso" class="modal-overlay" style="display: none;">
							<div class="box modal-content" style="max-width: 500px;">
								<h3>Alerta</h3>
								
								<p><strong>A encomenda Nº<span id="num_encomenda_sucesso"></span> foi criada com sucesso.</strong></p>
								<p><strong>Link para o documento pdf:</strong> <a id="caminho_pdf_sucesso" target="_blank"></a></p> 
								

								<button class="primary" onclick="location.reload()">Concluído</button>
							</div>
						</div>


						<h2>Encomendar manuais</h2>

						<!-- Filtros dos manuais e tabela -->
						<div class="box">
							<div class="row">
								<div class="col-2">
									<h3>Filtrar manuais</h3>
								</div>

								<div class="col-6">
									<span id="erroFiltrar"></span>
								</div>
							</div>
							

							<div class="divFiltros">
								<div class="filtros">

									<div class="filtro-grupo">
										<label for="filtroAgrupamento">Agrupamento: </label>
										<select id="filtroAgrupamento">
											<option value="" selected>Selecionar agrupamento</option>
											<?php 
											$sql = "SELECT * FROM agrupamento";
											$resultado = $conn->query($sql);

											while($row = $resultado->fetch_assoc()){?>
												<option value="<?= $row["id_agrupamento"] ?>"><?= $row["nome_agrupamento"] ?></option>
											<?php }
											?>
										</select>
									</div>
									
									<div class="filtro-grupo">
										<label for="filtroAnoEscolar">Ano Escolar: </label>
										<select id="filtroAnoEscolar">
											<option value="" selected>Selecionar ano escolar</option>
											<?php 
											$sql = "SELECT * FROM ano_escolar";
											$resultado = $conn->query($sql);

											while($row = $resultado->fetch_assoc()){?>
												<option value="<?= $row["id_ano_escolar"] ?>"><?= $row["nome_ano_escolar"] ?></option>
											<?php }
											?>
										</select>
									</div>

									<div class="filtro-grupo">
										<label for="filtroTipoManual">Tipo de manual: </label>
										<select id="filtroTipoManual">
											<option value="" selected>Selecionar tipo de manual</option>
											<option value="Manual">Manual</option>
											<option value="Livro de Fichas">Livro de fichas</option>
										</select>
									</div>

									<button type="submit" id="btnFiltrar" class="small">Filtrar</button>
								</div>
							</div>

							<!-- Tabela mostrando os manuais -->
							<div class="table-wrapper">
								<table class="alt">
									<thead>
										<tr>
											<th>ISBN</th>
											<th>Nome</th>
											<th>Preço</th>
											<th>Disciplina</th>
											<th>Tipo de manual</th>
											<th>
												Selecionar
												<input type="checkbox" id="selecionarAll">
												<label for="selecionarAll"></label>
											</th>
											<th>
												Voucher
												<input type="checkbox" id="voucherAll">
												<label for="voucherAll"></label>
											</th>
										</tr>
									</thead>
	
									<tbody id="tabela-filtro">
										
									</tbody>
								</table>
							</div>


							<div class="row aln-middle">
								<div class="col-3">
									<strong>Total da encomenda: <span id="totalEncomenda">0</span>€</strong> 
								</div>

								<div class="col-2">
									<strong>Valor da caução: <span id="valorCaucao">0</span>€</strong>
								</div>

								<div class="col-3">
									<strong>Caução paga: </strong>
									<input type="number" id="caucaoPaga" default=0>
								</div>
							</div>
						</div>

						<!-- Opções da encomenda e contatos -->
						<div class="row" style="display: flex; align-items: stretch;">
							<!-- Opções da encomenda -->
							<div class="col-6" style="display: flex; flex-direction: column;">
								<div class="box" style="height: 100%;">
									<div class="row aln-middle">
										<div class="col-6">
											<input type="checkbox" id="plastificarManuais">
											<label for="plastificarManuais"><strong>Plastificar Manuais</strong></label>
										</div>
										<div class="col-6">
											<input type="checkbox" id="plastificarLivroDeFichas">
											<label for="plastificarLivroDeFichas"><strong>Plastificar Livro de fichas</strong></label>
										</div>
									</div>
		
									<div class="row aln-middle" style="margin-top: 10px;">
										<div class="col-6">
											<input type="checkbox" id="checkEtiquetas">
											<label for="checkEtiquetas"><strong>Etiquetas</strong></label>
											<textarea id="etiquetas"></textarea>
										</div>
										<div class="col-6">
											<label for="observacoes"><strong>Observações</strong></label>
											<textarea id="observacoes"></textarea>
										</div>
									</div>

									<div class="row-aln-middle" style="margin-top:10px;">
										<div class="col-12">
											<strong>Código MEGA: </strong>
											<input type="text" id="codigoMega">
										</div>
									</div>
								</div>
							</div>

							<!-- Contactos -->
							<div class="col-6" style="display: flex; flex-direction: column;">
								<div class="box" style="height: 100%;">
									<!-- Nome do aluno e NIF -->
									<div class="row" style="margin: 10px 0;">
										<div class="col-8 input-contato" >
											<label for="nomeAluno">Nome do aluno: </label>
											<input type="text" id="nomeAluno" >
										</div>
										<div class="col-4 input-contato">
											<label for="nif">NIF: </label>
											<input type="text" id="nif"  pattern="[0-9]*" maxlength="9">
										</div>
									</div>

									<!-- Nome do Encarregado de educação -->
									<div class="row" style="margin: 10px 0;">
										<div class="col-12 input-contato">
											<label for="nomeEnc">Nome do Encarregado de educação: </label>
											<input type="text" id="nomeEnc">
										</div>
									</div>

									<!-- Email e telefone -->
									<div class="row" style="margin: 10px 0;">
										<div class="col-6 input-contato" >
											<label for="email">Email: </label>
											<input type="email" id="email" >
										</div>
										<div class="col-6 input-contato">
											<label for="telemovel">Telemóvel: </label>
											<input type="text" id="telemovel"  pattern="[0-9]*" maxlength="9">
										</div>
									</div>
								</div>
							</div>
						</div> 

						<div class="row aln-middle">
							<div class="col-2 ">
								<button id="btnEncomendar">Encomendar</button>
							</div>
							<div class="col-6">
								<strong id="errorMsg"></strong>
							</div>
						</div>



					<?php }
						else{
							echo <<<HTML
								<section id="banner">
									<div class="col-6 col-12-small">
										<div class="box">
											<h2>Informações do Sistema</h2>
											<p>Este é o sistema de gestão da Maria Papel Papelaria. Para aceder às funcionalidades administrativas, é necessário autenticar-se com as suas credenciais.</p>
											<p>Se ainda não possui uma conta, contacte o administrador do sistema para obter acesso.</p>
										</div>
									</div>
								</section>
								HTML;
								
								}

					?>


				</div>
			</div>

			<!-- Sidebar -->
			<div id="sidebar">
				<div class="inner">

					<!-- Menu -->
							<?php include('menu.php'); ?>

					<!-- Footer -->
						<?php include('footer.php'); ?>

				</div>
			</div>

		</div>

		<!-- Scripts -->
        <script src="assets/js/encomendar_manuais.js"></script>
			<script src="assets/js/gestao.js"></script>
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>
