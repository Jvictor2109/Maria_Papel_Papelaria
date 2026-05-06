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
			<title>MPP - Início</title>
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
												<h2 >Manuais</h2>
												<span id="msgErro"></span>

												<div class="row">
													<div class="col-8">
														<!-- Campo ISBN para começar a adicionar um manual -->
														<label for="isbn">ISBN:</label>
														<input style="width: 40%; margin-bottom: 10px;" type="text" id="isbn" name="isbn">

														<!-- Restantes campos só aparecem ao digitar algo no ISBN -->
														 <div class="form-add-manual row">
															<div class="col-4">
																<label for="nome_manual">Nome do manual:</label>
																<input type="text" required id="nome_manual" name="nome_manual" required>
															</div>

															<div class="col-4">
																<label for="codigo_manual">Código do manual:</label>
																<input type="text" required id="codigo_manual" name="codigo_manual">
															</div>

															<div class="col-4">
																<label for="preco_manual">Preço do manual:</label>
																<input type="text" required id="preco_manual" name="preco_manual" required>
															</div>

															<!-- Vai buscar as editoras à base de dados -->
															<div class="col-4">
																<label for="editora">Editora:</label>
																<select name="editora" id="editora">
																	<option value="" selected>Selecione editora</option>
																	<?php 
																	$sql = "SELECT * FROM editora";
																	$resultado = $conn->query($sql);

																	// Gera uma opção por editora, guardando seu id
																	while($row = $resultado->fetch_assoc()){?>
																		<option value="<?= $row["id_editora"] ?>"><?= $row["nome_editora"] ?></option>
																	<?php }

																	
																	?>
																</select>
															</div>
															
															<!-- Vai buscar as disciplinas à base de dados -->
															<div class="col-4">
																<label for="editora">Disciplina:</label>
																<select name="disciplina" id="disciplina">
																	<option value="" selected>Selecione disciplina</option>
																	<?php 
																	$sql = "SELECT * FROM disciplina";
																	$resultado = $conn->query($sql);

																	// Gera uma opção por disciplina, guardando seu id
																	while($row = $resultado->fetch_assoc()){?>
																		<option value="<?= $row["id_disciplina"] ?>"><?= $row["nome_disciplina"] ?></option>
																	<?php }

																	
																	?>
																</select>
															</div>

															<div class="col-4">
																<label for="tipo_manual">Tipo de manual</label>
																<select name="tipo_manual" id="tipo_manual">
																	<option value="" selected>Selecione o tipo de manual</option>
																	<option value="Manual">Manual</option>
																	<option value="Livro de FIchas">Livro de Fichas</option>
																</select>
															</div>

															<!-- Campos com checkboxes, buscando à base de dados -->
															 <div class="col-12">
																<label for="">Agrupamento:</label>
																
																<?php 
																$sql = "SELECT * FROM agrupamento";
																$resultado = $conn->query($sql);

																while($row = $resultado->fetch_assoc()){?>

																	<input type="checkbox" class="checkbox_agrupamento" id="agrupamento_<?= $row["id_agrupamento"] ?>" value="<?= $row["id_agrupamento"] ?>">
																	<label for="agrupamento_<?= $row["id_agrupamento"] ?>"><?= $row["nome_agrupamento"] ?></label>
																<?php 
																}
																
																?>
															
															 </div>

															 <div class="col-12">
																<label for="">Ano Escolar:</label>
																
																<?php 
																$sql = "SELECT * FROM ano_escolar";
																$resultado = $conn->query($sql);

																while($row = $resultado->fetch_assoc()){?>

																	<input type="checkbox" class="checkbox_ano_escolar" id="ano_escolar_<?= $row["id_ano_escolar"] ?>" value="<?= $row["id_ano_escolar"] ?>">
																	<label for="ano_escolar_<?= $row["id_ano_escolar"] ?>"><?= $row["nome_ano_escolar"] ?></label>
																<?php 
																}
																
																?>
															
															 </div>

															 <div class="col-2">
																<input type="submit" name="guardar_manual" value="Guardar Manual" id="btn_guardar_manual" class="primary">
															 </div>

														 </div>
														
													</div>
												</div>
												

											<?php 
											}
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

				<script src="assets/js/gestao_manual.js"></script>
				<script src="assets/js/gestao.js"></script>
				<script src="assets/js/jquery.min.js"></script>
				<script src="assets/js/browser.min.js"></script>
				<script src="assets/js/breakpoints.min.js"></script>
				<script src="assets/js/util.js"></script>
				<script src="assets/js/main.js"></script>

		</body>
	</html>
