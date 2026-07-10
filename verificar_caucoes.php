	<?php
	// Verificação de sessão em todas as páginas protegidas
	session_start();
	include('db_connect.php');

	if($_SERVER['REQUEST_METHOD'] == "POST"){
        $request = json_decode(file_get_contents('php://input'), true);

        $data_caucao = $request["data_caucao"];

        $stmt = $conn->prepare(
            "SELECT SUM(valor_caucao) AS total_caucao FROM encomenda
            WHERE data_encomenda = ?"
        );
        $stmt->bind_param("s", $data_caucao);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        echo json_encode(['total_caucao'=>$row["total_caucao"]]);
        exit();
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
			<title>MPP - Verificar cauções</title>
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
												<h2>Verificar cauções</h2>

                                                <div class="box">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div style="display: flex; gap:8px; align-items:center">
                                                                <strong>Selecione uma data: </stro>
                                                                <input type="date" id="dataCaucao" value="<?= date('Y-m-d') ?>">
                                                                <button class="primary" id="btnCaucao">Verificar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row" style="margin-top: 10px; display: none;" id="resultadoCaucao">
                                                        <div class="col-12">
                                                            <strong>Valor total recebido de caução: <span id="valorTotalCaucao"></span>€</strong>
                                                        </div>
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
                <script>
                    // Ouve o botao de verificar
                    const btnCaucao = document.getElementById('btnCaucao');
                    btnCaucao.addEventListener('click', async ()=>{
                        const data_caucao = document.getElementById('dataCaucao').value;

                        const response = await fetch('verificar_caucoes.php', {
                            method:"post",
                            headers: {'Content-type':'application/json'},
                            body:JSON.stringify({
                                "data_caucao":data_caucao
                            })
                        });

                        const data = await response.json();
                        const total_caucao = data["total_caucao"];

                        document.getElementById('valorTotalCaucao').innerText = total_caucao ?? "0.00";
                        document.getElementById('resultadoCaucao').style.display = "flex"; 
                    });


                </script>
				<script src="assets/js/jquery.min.js"></script>
				<script src="assets/js/browser.min.js"></script>
				<script src="assets/js/breakpoints.min.js"></script>
				<script src="assets/js/util.js"></script>
				<script src="assets/js/main.js"></script>

		</body>
	</html>
