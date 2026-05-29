<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'), true);

	switch($request["acao"]){
		case "filtrar_manuais":
			echo json_encode(filtrarManuais($conn, $request));
			exit();
	}
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
			ORDER BY manual.id_disciplina ASC, manual.tipo_manual DESC";
	
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
										<h4>Informações da encomenda</h4>

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
										<div class="col-3">
											<button id="btnConfirmarEncomenda" class="primary">Confirmar encomenda</button>
										</div>
										<div class="col-3">
											<button id="btnCancelarEncomenda">Voltar atrás</button>
										</div>
									</div>
								</div>
							</div>
						</div>


						<h2>Encomendar manuais</h2>

						<!-- Filtros dos manuais e tabela -->
						<div class="box">
							<div class="row">
								<div class="col-2">
									<h3>Filtrar manuais</h3>
								</div>

								<div class="col-2">
									<span id="ErroFiltrar"></span>
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
									<input type="number" id="caucaoPaga">
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
