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
						<h2>Encomendar manuais</h2>
                        
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
	
									<tbody>
										
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

							<div class="row aln-middle" style="margin-top:30px;">
								<div class="col-3">
									<input type="checkbox" id="plastificarManuais">
									<label for="plastificarManuais"><strong>Plastificar Manuais</strong></label>
								</div>
								<div class="col-3">
									<input type="checkbox" id="plastificarLivroDeFichas">
									<label for="plastificarLivroDeFichas"><strong>Plastificar Livro de fichas</strong></label>
								</div>
							</div>

							<div class="row aln-middle" style="margin-top: 10px;">
								<div class="col-3">
									<input type="checkbox" id="checkEtiquetas">
									<label for="checkEtiquetas"><strong>Etiquetas</strong></label>
									<textarea id="etiquetas"></textarea>
								</div>
								<div class="col-3">
									<input type="checkbox" id="checkObservacoes">
									<label for="checkObservacoes"><strong>Observações</strong></label>
									<textarea id="observacoes"></textarea>
								</div>
							</div>
                        </div> <!-- box -->



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
			function editar(id){
				// Popula o modal
				const nome_agrupamento = document.getElementById(`nome_agrupamento_${id}`).innerText;

				document.getElementById('edit_id_agrupamento').value = id;
				document.getElementById('edit_nome_agrupamento').value = nome_agrupamento;

				const modal = document.getElementById('modal-gestao');
				modal.style.display = 'flex';
			}
		</script>
        <script src="assets/js/encomendar_manuais.js"></script>
			<script src="assets/js/gestao.js"></script>
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>
