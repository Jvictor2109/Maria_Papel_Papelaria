<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
require_once('db_connect.php');
require_once 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

// Verifica se veio por POST e com qual objetivo
if($_SERVER['REQUEST_METHOD'] == "POST"){

	if($_POST['acao'] == "upload_xlsx"){
		// Verifica se tem um xlsx no pedido -> utilizador carregou manuais por excel
		if(isset($_FILES['xlsx'])){
			// Verifica se realmente é um ficheiro .xlsx
			if($_FILES['xlsx']['type'] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
				echo json_encode(['resultado' => 'erro', 'msg' => 'O Ficheiro adicionado não é uma tabela Excel']);
				exit();
			}
	
			//Ler o ficheiro e extrair os dados
			if ( $xlsx = SimpleXLSX::parse($_FILES['xlsx']['tmp_name']) ) {
				
				$manuais = [];
				/** @var array $coluna */  // Por algum motivo precisa disso pra poder parar de aparecer erro no vscode
				foreach($xlsx->rows() as $linha => $coluna){
					// Pula os cabeçalhos
					if($linha < 2){
						continue;
					}
	
					$manuais[] = [
						"isbn"=>$coluna[0],	
						"nome_manual"=>$coluna[1],
						"codigo_manual"=>$coluna[2],
						"preco_manual"=>$coluna[3]
					];
				}
	
				$_SESSION['manuais'] = $manuais;
	
	
				echo json_encode(['resultado' => 'sucesso']);
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
	else if($_POST['acao'] == "carregar_manuais"){
		$manuais = json_decode($_POST['manuais'],  true);
		if(guardarManuais($conn, $manuais)){
			echo json_encode(['resultado' => 'sucesso', 'msg'=>'Manuais adicionados com sucesso à base de dados']);
			exit();
		}
		else{
			echo json_encode(['resultado' => 'erro', 'msg'=>'Erro ao adicionar manuais à base de dados']);
			exit();
		}
	}

} 
else{
	// TODO: Se vier por GET mas sem os manuais na sessão, deve redirecionar pra pagina da gestao de manuais
	if(!isset($_SESSION['manuais'])){
		header('Location: gestao_manual.php');
		exit();
	}
}

function guardarManuais(mysqli $conn, array $manuais){
	$conn->begin_transaction();

	try{
		$stmtCheck = $conn->prepare(
			"SELECT id_manual FROM manual WHERE isbn_manual = ?"
		);
		$stmtInsert = $conn->prepare(
			"INSERT INTO manual (isbn_manual, nome_manual, preco_manual, cod_manual, id_editora, id_disciplina, tipo_manual)
			VALUES (?,?,?,?,?,?,?)"
		);
		$stmtUpdate = $conn->prepare(
			"UPDATE manual SET nome_manual=?, preco_manual=?, cod_manual=?, id_editora=?, id_disciplina=?, tipo_manual=?
			WHERE isbn_manual=?"
		);

		foreach($manuais as $manual){
			$isbn         = $manual["isbn"];
			$nome_manual  = $manual["nome_manual"];
			$cod_manual   = $manual["codigo_manual"];
			$preco_manual = floatval($manual["preco_manual"]);
			$editora      = intval($manual["editora"]);
			$disciplina   = intval($manual["disciplina"]);
			$tipo_manual  = $manual["tipo_manual"];

			// Verifica se o ISBN já existe
			$stmtCheck->bind_param("s", $isbn);
			$stmtCheck->execute();
			$stmtCheck->store_result();

			if($stmtCheck->num_rows === 0){
				$stmtInsert->bind_param("ssdsiis", $isbn, $nome_manual, $preco_manual, $cod_manual, $editora, $disciplina, $tipo_manual);
				$stmtInsert->execute();
			} else {
				$stmtUpdate->bind_param("sdsiiss", $nome_manual, $preco_manual, $cod_manual, $editora, $disciplina, $tipo_manual, $isbn);
				$stmtUpdate->execute();
			}

			$stmtCheck->free_result();
		}

		$stmtCheck->close();
		$stmtInsert->close();
		$stmtUpdate->close();

		$conn->commit();
		return true;

	}catch(Exception $e){
		$conn->rollback();
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
											<h2>Carregar manuais</h2>

											<h3>Selecione o agrupamento:</h3>
											<?php 
											$sql = "SELECT * FROM agrupamento";
											$resultado = $conn->query($sql);

											while($row = $resultado->fetch_assoc()){?>

												<input type="checkbox" class="checkbox_agrupamento" id="agrupamento_<?= $row["id_agrupamento"] ?>" value="<?= $row["id_agrupamento"] ?>">
												<label for="agrupamento_<?= $row["id_agrupamento"] ?>"><?= $row["nome_agrupamento"] ?></label>
											<?php 
											}
											
											?>

											<h3>Selecione os anos escolares:</h3>
											<?php 
											$sql = "SELECT * FROM ano_escolar";
											$resultado = $conn->query($sql);

											while($row = $resultado->fetch_assoc()){?>

												<input type="checkbox" class="checkbox_ano_escolar" id="ano_escolar_<?= $row["id_ano_escolar"] ?>" value="<?= $row["id_ano_escolar"] ?>">
												<label for="ano_escolar_<?= $row["id_ano_escolar"] ?>"><?= $row["nome_ano_escolar"] ?></label>
											<?php 
											}
											
											?>

											
											<div class="table-wrapper">
												<table class="alt" id="tabela">
													<thead>
														<tr>
															<th>ISBN</th>
															<th>Nome Manual</th>
															<th>Cód. Manual</th>
															<th>Preço Manual</th>
															<th>Editora</th>
															<th>Disciplina</th>
															<th>Tipo Manual</th>
														</tr>
													</thead>
													<tbody>
														<?php 
														foreach($_SESSION['manuais'] as $manual){?>
															<tr>
																<td><?= $manual["isbn"] ?></td>
																<td><?= $manual['nome_manual'] ?></td>
																<td><?= $manual['codigo_manual'] ?></td>
																<td><?= $manual['preco_manual'] ?></td>

																<td>
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
																</td>

																<td>
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
																</td>

																<td>
																	<select name="tipo_manual" id="tipo_manual">
																		<option value="" selected>Selecione o tipo de manual</option>
																		<option value="Manual">Manual</option>
																		<option value="Livro de FIchas">Livro de Fichas</option>
																	</select>
																</td>
															</tr>
														<?php }
														
														?>
													</tbody>
												</table>

												<button id="btnCarregarManuais" class="primary">Carregar manuais</button>
											</div>
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
			<script src="assets/js/carregar_manuais.js"></script>
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
	</body>
</html>
<?php 
// Limpa os dados dos manuais na sesão após construir a tabela
unset($_SESSION['manuais']);
?>