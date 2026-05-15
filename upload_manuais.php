<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
require_once('db_connect.php');
require_once 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

// Verifica se veio por POST e se tem ficheiro no pedido
if($_SERVER['REQUEST_METHOD'] == "POST"){
    // Verifica se tem um xlsx no pedido -> utilizador carregou manuais por excel
	if(isset($_FILES['xlsx'])){
		// Verifica se realmente é um ficheiro .xlsx
		if($_FILES['xlsx']['type'] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
			echo json_encode(['resultado' => 'erro', 'msg' => 'O Ficheiro adicionado não é uma tabela Excel']);
			exit();
		}

        // TODO : Ler o ficheiro e extrair os dados
        // $dados_manuais = 

        if ( $xlsx = SimpleXLSX::parse($_FILES['xlsx']['tmp_name']) ) {
            $_SESSION['xlsx'] = $xlsx->rows();
            echo json_encode(['resultado' => 'sucesso', 'msg' => 'Excel extraido']);
            exit();

        } else {
            echo json_encode(['resultado' => 'erro', 'msg' => SimpleXLSX::parseError()]);
            exit();
        }
	}
    else{
        echo json_encode(['resultado' => 'erro', 'msg' => 'Adicione um ficheiro']);
        exit();
    }
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
		<title>MPP - Expedição VASP</title>
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
									<div class="col-6 col-12-small">
										<div class="box">
											oi
										</div>
									</div>


								</section>
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
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
	</body>
</html>
