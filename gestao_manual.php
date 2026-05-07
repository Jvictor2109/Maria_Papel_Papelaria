<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	// Recebe a request do Javascript e lida conforme o tipo de ação
	$request = json_decode(file_get_contents('php://input'), true);

	switch($request["acao"]){
		case "adicionar":
			if(addManual($conn, $request)){
			echo json_encode(['resultado' => 'sucesso', 'msg' => 'Manual adicionado com sucesso!']);
			}
			else{
				echo json_encode(['resultado' => 'erro', 'msg' => 'Não foi possível adicionar à base de dados']);
			};
			exit();
		
		case "checar_isbn":
			echo json_encode(['resultado' => checarIsbn($conn, $request)]);
			exit();

		case "listar_manuais":
			echo json_encode(listarManuais($conn));
			exit();

		case "checar_anos_escolares":
			echo json_encode(checarAnosEscolares($conn, $request));
			exit();
	}

	
}


function checarAnosEscolares(mysqli $conn, array $request){
	$id_manual = $request['id_manual'];

	$sql = "SELECT nome_ano_escolar FROM ano_escolar 
			JOIN manual_ano_escolar ON manual_ano_escolar.id_ano_escolar=ano_escolar.id_ano_escolar
			WHERE id_manual = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $id_manual);
	$stmt->execute();
	$resultado = $stmt->get_result();
	$anos = [];
	while($row = $resultado->fetch_assoc()){
		$anos[] = $row['nome_ano_escolar'];
	}
	return ['resultado'=>$anos];
}


function listarManuais(mysqli $conn){
	$sql = "SELECT * FROM manual JOIN editora ON manual.id_editora=editora.id_editora";
	$resultado = mysqli_query($conn, $sql);
	$dados = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

	return $dados;
}


function addManual(mysqli $conn, array $request){
	// Extrai os dados da request
	$isbn = $request["isbn"];
	$nome_manual = $request["nome_manual"];
	$cod_manual = $request["cod_manual"];
	$preco_manual = floatval($request["preco_manual"]);
	$editora = intval($request["editora"]);
	$disciplina = intval($request["disciplina"]);
	$tipo_manual = $request["tipo_manual"];
	$agrupamentos = $request["agrupamentos"];
	$anos_escolares = $request["anos_escolares"];

	// Começa o processo de inserção de dados
	// Ou tudo é inserido corretamente, ou nada é inserido
	$conn->begin_transaction();

	try{
		// Insere o manual
		$sql = "INSERT INTO manual (isbn_manual, nome_manual, preco_manual, cod_manual, id_editora, id_disciplina, tipo_manual)
		VALUES (?,?,?,?,?,?,?)";

		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ssdsiis", $isbn,$nome_manual,$preco_manual,$cod_manual, $editora,$disciplina,$tipo_manual);
		$stmt->execute();
		$id_manual = $conn->insert_id;

		$stmt->close();

		// Insere as relações de agrupamento
		$sql = "INSERT INTO manual_agrupamento (id_manual, id_agrupamento) VALUES (?,?)";
		$stmt = $conn->prepare($sql);
		foreach($agrupamentos as $id_agrupamento){
			$stmt->bind_param("ii", $id_manual, $id_agrupamento);
			$stmt->execute();

		}
		$stmt->close();
		
		// Insere as relações de anos escolares
		$sql = "INSERT INTO manual_ano_escolar (id_manual, id_ano_escolar) VALUES (?,?)";
		$stmt = $conn->prepare($sql);
		foreach($anos_escolares as $id_ano_escolar){
			$stmt->bind_param("ii", $id_manual, $id_ano_escolar);
			$stmt->execute();
		}
		$stmt->close();

		$conn->commit();
		return true;

	}catch(Exception $e){
		$conn->rollback();
		return false;
	}	
}


function checarIsbn(mysqli $conn, array $request){
	$isbn = $request["isbn"];

	$sql = "SELECT * FROM manual WHERE isbn_manual = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $isbn);
	$stmt->execute();
	$resultado = $stmt->get_result();
	if($resultado->num_rows > 0){
		return true;
	}
	else{
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
														<input pattern="[0-9]*" style="width: 40%; margin-bottom: 10px;" type="text" id="isbn" name="isbn" minlength="10" maxlength="13">

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

												<div class="row">
													<div class="col-12">
														<!-- Tabela que mostra os itens -->
														<div class="table-wrapper" style="max-height: 400px; overflow-y: auto;">
															<table>
																<thead>
																	<tr>
																		<th>ID</th>
																		<th>ISBN</th>
																		<th>Nome</th>
																		<th>Preço</th>
																		<th>Código</th>
																		<th>Editora</th>
																		<th>Ano Escolar</th>
																		<th>   </th>
																	</tr>
																</thead>
																<tbody>

																</tbody>
															</table>
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
