<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'), true);

	switch($request["acao"]){
		case "get_encomendas":
			echo json_encode(['encomendas'=>getEncomendas($conn)]);
			exit();
	}
}

function getEncomendas(mysqli $conn){
    $stmt = $conn->prepare(
        "SELECT *, DATEDIFF(NOW(), encomenda.data_encomenda) AS 'datediff' FROM encomenda
        WHERE estado_encomenda = 'registada'"
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
											<h4>Encomendas por tratar: <span id="num_encomendas_tratar"></span></h5>

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
