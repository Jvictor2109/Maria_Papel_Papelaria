<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');
require_once("vendor/autoload.php");


if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'), true);

	switch($request["acao"]){
		case "get_encomendas":
			echo json_encode(['encomendas'=>getEncomendas($conn)]);
			exit();
		
		case "avisar":
			// Pegar todas as encomendas concluidas
			$result = $conn->query(
				"SELECT * FROM encomenda
				WHERE estado_encomenda = 'concluida'
				AND avisado = 0
				ORDER BY num_encomenda ASC"
			);
			$encomendas = $result->fetch_all(MYSQLI_ASSOC);

			// Gera pdf e encerra a conexao com a base de dados
			gerar_pdf_aviso($conn, $encomendas);
			header('Connection: close');
			ob_end_flush();
			flush();

			// Manda os emails
			require_once("enviar_email.php");

			foreach($encomendas as $encomenda){
				if(!empty($encomenda["email_encomenda"])){
					$num_encomenda = $encomenda["num_encomenda"];
					$corpo_email = "Pode vir levantar a sua encomenda N$num_encomenda \n <br>
									Os melhores cumprimentos, <br>
									Maria Papel Papelaria";
					enviar_email($conn, $encomenda, $corpo_email);
				}
			}

			// Marca as encomendas como avisadas
			$data_aviso = date("Y-m-d H:i:s");

			$stmt = $conn->prepare(
				"UPDATE encomenda
				SET avisado = 1, id_avisado = ?, data_aviso = ?
				WHERE estado_encomenda = 'concluida'"
			);
			$stmt->bind_param("is", $_SESSION["user_id"], $data_aviso);
			$stmt->execute();
			$stmt->close();

			exit();
	}
}


function gerar_pdf_aviso(mysqli $conn, array $encomendas){
	// Pega o ano letivo	
	$result = $conn->query(
		"SELECT nome_ano_letivo FROM ano_letivo
		WHERE ano_letivo_ativo = 1"
	);
	$ano_letivo = $result->fetch_assoc();
	$nome_ano_letivo = $ano_letivo["nome_ano_letivo"];


	// Gera o pdf
	$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

	$pdf->SetCreator('Maria Papel'); 
	$pdf->setPrintHeader(false);      
	$pdf->setPrintFooter(false);       
	$pdf->SetMargins(15, 15, 15);      
	$pdf->AddPage();

	$pdf->SetFont('helvetica', 'B', 14);
	$pdf->Cell(180, 7, 'MARIA PAPEL PAPELARIA', 0, 1, 'C');
	$pdf->Cell(0, 6, "Ano Letivo $nome_ano_letivo", 0, 1, 'C');
	$pdf->Ln(8);

	$pdf->Cell(0, 6, "Encomendas a avisar", 0, 1, 'C');
	$pdf->Ln(8);

	// Tabela
	$pdf->SetFont('helvetica', 'B', 9);
	$pdf->SetFillColor(220, 220, 220);

	// Cabeçalho da tabela
	$pdf->Cell(25, 7, 'Nº Encomenda', 1, 0, 'C', true);
	$pdf->Cell(78, 7, 'Telemóvel', 1, 1, 'C', true);
	
	$pdf->SetFont('helvetica', '', 8);
	
	foreach($encomendas as $encomenda){
		if(!empty($encomenda["telefone_encomenda"])){
			$pdf->Cell(25, 7, $encomenda["num_encomenda"], 1, 0, 'C');
			$pdf->Cell(78, 7, $encomenda["telefone_encomenda"], 1, 1, 'C');
		}
	}

	$stmtUtilizador = $conn->prepare("SELECT username FROM utilizador WHERE id_utilizador = ?");
	$stmtUtilizador->bind_param("i", $_SESSION["user_id"]);
	$stmtUtilizador->execute();
	$result = $stmtUtilizador->get_result();
	$row = $result->fetch_assoc();
	$utilizador = $row["username"];
	$stmtUtilizador->close();

	$pdf->setY(-30);
	$pdf->SetFont('helvetica', '', 8);
	$texto_rodape = 'Documento gerado no dia ' . date("d/m/Y") . ' às ' . date("H:i:s") . ' | Utilizador: ' . $utilizador;
	$pdf->Cell(180, 5, $texto_rodape, 0, 1, 'C');

	// Envia para o frontend como string
	$pdf_pronto = $pdf->Output('encomendas_a_avisar.pdf', 'S');

	header('Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename="encomendas_a_avisar.pdf"');
	header('Content-Length: ' . strlen($pdf_pronto));

	echo $pdf_pronto;
}


function getEncomendas(mysqli $conn){
	$stmt = $conn->prepare(
		"SELECT *, DATEDIFF(NOW(), encomenda.data_encomenda) AS 'datediff' FROM encomenda
		WHERE estado_encomenda <> 'entregue'"
	);

	$stmt->execute();
	$result = $stmt->get_result();
	$encomendas = $result->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
	return $encomendas;
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
		<title>MPP - Tratar Encomendas</title>
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
										<h2>Tratar encomendas</h2>

										<div class="box">
											
											<div class="row" style="margin-bottom: 10px;">
												<div class="col-12">
													<button class="primary" id="btn_a_tratar" style="margin-bottom: 10px;">Encomendas a tratar</button>
													<button class="primary" id="btn_tratadas" style="margin-bottom: 10px;">Encomendas tratadas</button>
													<button class="secondary" id="btnAvisar" style="display: none;">Avisar encomendas concluídas</button>
												</div>
											</div>
											


											<!-- Filtro de ano escolar -->
											<h4>Filtrar por ano escolar</h4>
											<div class="row">
												<div class="col-12">
												<?php 
													$stmt = $conn->prepare(
														"SELECT * FROM ano_escolar"
													);
													$stmt->execute();
													$result = $stmt->get_result();
													$anos = $result->fetch_all(MYSQLI_ASSOC);

													foreach($anos as $ano){?>
														<input type="checkbox" class="filtroAno" id="<?= $ano["id_ano_escolar"] ?>" value="<?= $ano["id_ano_escolar"] ?>">
														<label for="<?= $ano["id_ano_escolar"] ?>"><?= $ano["nome_ano_escolar"] ?></label>
														
														<?php
													}
												?>
												</div>
											</div>
											
											<h4>Encomendas: <span id="num_encomendas_tratar"></span></h5>

											<!-- Tabela -->
											<div class="table-wrapper">
												<table class="alt">
													<thead>
														<tr>
															<th>ID</th>
															<th>Número da encomenda</th>
															<th>Data da encomenda</th>
															<th>Dias em espera</th>
															<th> </th>
														</tr>
													</thead>

													<tbody>

													</tbody>
												</table>
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
		 <script src="assets/js/tratar_encomendas.js"></script>
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/browser.min.js"></script>
		<script src="assets/js/breakpoints.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>
	</body>
</html>
