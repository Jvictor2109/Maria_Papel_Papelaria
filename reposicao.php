<?php
// Verificação de sessão em todas as páginas protegidas
session_start();

require_once('db_connect.php');


// Apaga as linhas com mais de 1 ano da BD
$stmt = $conn->prepare("DELETE FROM reposicao WHERE data_criacao < NOW() - INTERVAL 1 YEAR");
$stmt->execute();
if($stmt->affected_rows == -1){
	echo "Erro ao apagar linhas da base de dados: " . $stmt->error;
}


// Verifica se está recebendo um formulário 
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$request = json_decode(file_get_contents('php://input'), true);

	// Verifica o tipo de pedido e faz as ações necessárias
	switch ($request['acao']) {
		case "adicionar":
			if (addItem($conn, $request)) {
				echo json_encode(['resultado' => 'sucesso', 'msg' => 'Item adicionado com sucesso!']);
			} else {
				echo json_encode(['resultado' => 'erro', 'msg' => 'Não foi possível adicionar à base de dados']);
			}
			exit();

		case "listar":
			echo json_encode(mostrarDados($conn));
			exit();

		case "atualizar":
			if (attItem($conn, $request)) {
				echo json_encode(['resultado' => 'sucesso', 'msg' => 'Item atualizado com sucesso!']);
				exit();
			} else {
				echo json_encode(['resultado' => 'erro', 'msg' => 'Erro ao atualizar pedido']);
				exit();
			}
		
		case "editar":
			if(editarItem($conn, $request)){
				echo json_encode(['resultado' => 'sucesso', 'msg' => 'Item editado com sucesso!']);
				exit();
			} else{
				echo json_encode(['resultado' => 'erro', 'msg' => 'Erro ao editar pedido']);
				exit();
			}
	}
}

// Funções
function mostrarDados($conn)
{
	$sql = "SELECT r.*, 
				   u1.nome_utilizador AS criado_por, 
				   u2.nome_utilizador AS pedido_por, 
				   u3.nome_utilizador AS concluido_por		     
			FROM reposicao r
			LEFT JOIN utilizador u1 ON u1.id_utilizador = r.id_criado_por
			LEFT JOIN utilizador u2 ON u2.id_utilizador = r.id_pedido_por
			LEFT JOIN utilizador u3 ON u3.id_utilizador = r.id_concluido_por
			ORDER BY r.urgencia ASC, r.data_criacao DESC";
	$resultado = mysqli_query($conn, $sql);
	$dados = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

	return $dados;
}


function addItem($conn, $request)
{
	// Verifica campos obrigatórios
	$artigo = $request['artigo'];
	if (empty($artigo)) {
		echo json_encode(['resultado' => 'erro', 'msg' => 'Introduza o nome do artigo']);
		exit();
	}

	// Pega o resto dos dados
	$referencia = $request['referencia'];
	$tipo = $request['tipo'];
	$cliente = $request['cliente'];
	$telemovel = $request['telemovel'];
	$urgencia = $request['urgencia'];
	$quantidade = intval($request['quantidade']);
	$observacoes = $request['observacoes'];

	// Informações de data e utilizador
	$user_id = intval($_SESSION['user_id']);
	$data_criado = date("Y-m-d");


	// Adiciona na base de dados
	$sql = "INSERT INTO reposicao (artigo, referencia, tipo, urgencia, nome_cliente, telefone_cliente, data_criacao, observacoes, quantidade, id_criado_por)
			VALUES (?,?,?,?,?,?,?,?,?,?)";

	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssssssssii", $artigo, $referencia, $tipo, $urgencia, $cliente, $telemovel, $data_criado, $observacoes, $quantidade, $user_id);

	if ($stmt->execute()) {
		return True;
	} else {
		echo json_encode(['resultado' => 'erro', 'msg' => $stmt->error]);
		return False;
	}
}


function editarItem($conn, $request){
	$item_id = intval($request['item_id']);
	$artigo = $request['artigo'];
	$referencia = $request['referencia'];
	$tipo = $request['tipo'];
	$cliente = $request['cliente'];
	$telemovel = $request['telemovel'];
	$quantidade = intval($request['quantidade']);
	$urgencia = $request['urgencia'];
	$observacoes = $request['observacoes'];

	$sql = "UPDATE reposicao
			SET artigo = ?, referencia = ?, tipo = ?, urgencia = ?,
				nome_cliente = ?, telefone_cliente = ?, observacoes = ?, quantidade = ?
			WHERE item_id =?";
	
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssssssii", $artigo, $referencia, $tipo, $urgencia, $cliente,
									$telemovel, $observacoes, $quantidade, $item_id);

	if ($stmt->execute()) {
		return True;
	} else {
		echo json_encode(['resultado' => 'erro', 'msg' => $stmt->error]);
		return False;
	}
}


function attItem($conn, $request)
{
	$estado = $request['estado'];
	$item_id = intval($request['id']);
	$data = date("y-m-d");
	$user_id = intval($_SESSION['user_id']);


	if ($estado == "pedido") {

		$pedido = 1;
		$sql = "UPDATE reposicao
				SET pedido = ?, data_pedido = ?, id_pedido_por = ?
				WHERE item_id = ?";

		$stmt = $conn->prepare($sql);
		$stmt->bind_param("issi", $pedido, $data, $user_id, $item_id);
	} else if ($estado == "concluido") {
		$concluido = 1;
		$sql = "UPDATE reposicao
				SET concluido = ?, data_conclusao = ?, id_concluido_por = ?
				WHERE item_id = ?";

		$stmt = $conn->prepare($sql);
		$stmt->bind_param("issi", $concluido, $data, $user_id, $item_id);
	}

	if ($stmt->execute()) {
		return true;
	} else {
		return false;
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
	<title>MPP - Material a pedir</title>
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
					if (isset($_SESSION['user_id'])) { ?>
						<!-- Formulário para adicionar item -->
						<div id="modal-reposicao" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal" class="modal-close">&times;</span>
								<h3>Adicionar Pedido</h3>

								<div class="row gtr-uniform">
									<div class="col-6 col-12xsmall">
										<label for="artigo">Artigo:</label>
										<input type="text" id="artigo" required>

										<label for="referencia">Referência:</label>
										<input type="text" id="referencia">

										<label for="tipo">Tipo:</label>
										<select name="tipo" id="tipo">
											<option value="papelaria">Papelaria</option>
											<option value="tinteiros">Tinteiros</option>
											<option value="livros">Livros</option>
										</select>

										<label for="quantidade">Quantidade</label>
										<input type="number" minlength="1" value="1" id="quantidade">
									</div>

									<div class="col-6 col-12xsmall">
										<label for="cliente">Cliente:</label>
										<input type="text" id="cliente">

										<label for="telemovel">Telemóvel</label>
										<input type="text" id="telemovel" maxlength="9">

										<label for="urgencia">Urgência:</label>
										<select name="urgencia" id="urgencia">
											<option value="muito urgente">Muito Urgente</option>
											<option value="urgente">Urgente</option>
											<option value="nao urgente">Não Urgente</option>
										</select>

										<label for="obs">Observações</label>
										<textarea name="obs" id="obs"></textarea>
									</div>
								</div>

								<div class="row gtr-uniform" style="margin-top: 1.5em;">
									<div class="col-12">
										<button type="submit" class="primary" onclick="addPedido()">Adicionar artigo</button>
									</div>
								</div>

							</div>
						</div>

						<!-- Modal de Informações -->
						<div id="modal-info" class="modal-overlay" style="display: none;">
							<div class="box modal-content" style="max-width: 500px;">
								<span id="close-modal-info" class="modal-close">&times;</span>
								<h3>Informações do Artigo</h3>
								<div id="info-content">
									<!-- Dados do item são adicionados no javascript -->
								</div>
							</div>
						</div>

						<!-- Modal de Editar Pedido -->
						<div id="modal-editar" class="modal-overlay" style="display: none;">
							<div class="box modal-content">
								<span id="close-modal-editar" class="modal-close">&times;</span>
								<h3>Editar Pedido</h3>

								<div class="row gtr-uniform">
									<div class="col-6 col-12xsmall">
										<input type="hidden" id="edit_id" value="">

										<label for="edit-artigo">Artigo:</label>
										<input type="text" id="edit-artigo" required>

										<label for="edit-referencia">Referência:</label>
										<input type="text" id="edit-referencia">

										<label for="edit-tipo">Tipo:</label>
										<select name="edit-tipo" id="edit-tipo">
											<option value="papelaria">Papelaria</option>
											<option value="tinteiros">Tinteiros</option>
											<option value="livros">Livros</option>
										</select>

										<label for="edit-quantidade">Quantidade</label>
										<input type="number" minlength="1" value="1" id="edit-quantidade">
									</div>

									<div class="col-6 col-12xsmall">
										<label for="edit-cliente">Cliente:</label>
										<input type="text" id="edit-cliente">

										<label for="edit-telemovel">Telemóvel</label>
										<input type="text" id="edit-telemovel" maxlength="9">

										<label for="edit-urgencia">Urgência:</label>
										<select name="edit-urgencia" id="edit-urgencia">
											<option value="muito urgente">Muito Urgente</option>
											<option value="urgente">Urgente</option>
											<option value="nao urgente">Não Urgente</option>
										</select>

										<label for="edit-obs">Observações</label>
										<textarea name="edit-obs" id="edit-obs"></textarea>
									</div>
								</div>

								<div class="row gtr-uniform" style="margin-top: 1.5em;">
									<div class="col-12">
										<button type="submit" class="primary" onclick="editarPedido()">Guardar alterações</button>
									</div>
								</div>

							</div>
						</div>

						<h2>Material a pedir</h3>

						<div style="display: flex; align-items: center; gap: 20px; margin-bottom: 2em;">
							<a href="#" id="btn-add-pedido" class="button primary" style="margin-bottom: 0;">Adicionar artigo em falta</a>
							<h5 id="msg" style="margin: 0; line-height: 1;"></h5>
						</div>


						<div style="display: flex; align-items: center; gap: 20px; margin-bottom: 2em;">
							<button class="secundary btnTab" id="tab_ativo">Pedidos a concluir</button>
							<button class="secundary btnTab" id="tab_historico">Pedidos concluídos</button>
						</div>

						<h3>Filtrar por: </h3>
						<!-- Barra de seleção de filtros -->
						<div class="divFiltros">

							<div class="filtros">
								<div class="filtro-grupo">
									<label for="filtroUrgencia">Urgência:</label>
									<select id="filtroUrgencia">
										<option value="">Todas</option>
										<option value="muito urgente">Muito urgente</option>
										<option value="urgente">Urgente</option>
										<option value="nao urgente">Não Urgente</option>
									</select>
								</div>

								<div class="filtro-grupo">
									<label for="filtroTipo">Tipo:</label>
									<select id="filtroTipo">
										<option value="">Todos</option>
										<option value="papelaria">Papelaria</option>
										<option value="tinteiros">Tinteiros/Tonners</option>
										<option value="livros">Livros</option>
									</select>
								</div>
								
								<div class="filtro-grupo filtro_estado">
									<label for="filtroEstado">Estado:</label>
									<select id="filtroEstado">
										<option value="">Todos</option>
										<option value="por_pedir">Por pedir</option>
										<option value="pedido">Pedido</option>
									</select>
								</div>

								<div class="filtro-grupo filtro_data">                
									<label for="filtro_data_inicio">Data Início:</label>
									<input type="date" id="filtro_data_inicio">
								</div>
								
								<div class="filtro-grupo filtro_data">                
									<label for="filtro_data_fim">Data Fim:</label>
									<input type="date" id="filtro_data_fim">
								</div>

								<button type="submit" id="btnFiltrar">Filtrar</button>
							</div>
						</div>

						<!-- Tabela que mostra os itens -->
						<div class="table-wrapper" style="max-height: 400px; overflow-y: auto;">
							<table>
								<thead>
									<tr>
										<th>Artigo</th>
										<th>Referência</th>
										<th>Tipo</th>
										<th>Urgência</th>
										<th>Obs</th>
										<th>Qntd</th>
										<th>Data criação</th>
										<th style="min-width: 14em; text-align: center;">Ações</th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
			</div>

		<?php } else {
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


	</div>

	<!-- Scripts -->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/browser.min.js"></script>
	<script src="assets/js/breakpoints.min.js"></script>
	<script src="assets/js/util.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="assets/js/reposicao.js"></script>

</body>

</html>