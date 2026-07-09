	<?php
	// Verificação de sessão em todas as páginas protegidas
	session_start();
	include('db_connect.php');
	?>

	<!DOCTYPE HTML>
	<!--
		Editorial by HTML5 UP
		html5up.net | @ajlkn
		Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
	-->
	<html>
		<head>
			<title>MPP - Encomendas a editora</title>
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
												<h2>Avisos das encomendas</h2>

												<!-- Tabela mostrando os dados -->
												<div class="table-wrapper">
													<table class="alt">
														<thead>
															<tr>
																<th>Id</th>
																<th>Utilizador</th>
																<th>Data aviso</th>
                                                                <th>Ficheiro</th>
															</tr>
														</thead>

														<tbody>
															<?php 
																// Busca os dados à base de dados e constrói a tabela
																$sql = "SELECT * FROM aviso_encomendas
                                                                        JOIN utilizador ON aviso_encomendas.id_utilizador = utilizador.id_utilizador";
																$stmt = $conn->prepare($sql);
																$stmt->execute();
																$resultado = $stmt->get_result();

																if($resultado == false){
																	echo "Erro ao buscar à base de dados: " . $stmt->error;
																	exit();
																}
																
																if($resultado->num_rows == 0){?>
																	<td colspan="4">Sem itens na tabela</td>
																<?php

																}

																$linhas = $resultado->fetch_all(MYSQLI_ASSOC);

																foreach($linhas as $linha){?>
																	<tr>
																		<td><?= $linha['id_aviso_encomendas'] ?></td>
																		<td><?= $linha['username'] ?></td>
																		<td><?= $linha["data_aviso"] ?></td>
                                                                        <td><a href="<?= $linha["doc_aviso"] ?>" download>Download</a></td>
																	</tr>
																<?php }



															?>
														</tbody>
													</table>

													
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
				<script src="assets/js/gestao.js"></script>
				<script src="assets/js/jquery.min.js"></script>
				<script src="assets/js/browser.min.js"></script>
				<script src="assets/js/breakpoints.min.js"></script>
				<script src="assets/js/util.js"></script>
				<script src="assets/js/main.js"></script>

		</body>
	</html>
