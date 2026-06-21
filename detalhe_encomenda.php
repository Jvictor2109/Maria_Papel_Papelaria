<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'),true);
	$data = date("Y-m-d H:i:s");

	$stmt = $conn->prepare(
		"INSERT INTO observacao_encomenda (id_encomenda, observacao_encomenda, data_observacao, id_utilizador)
		VALUES (?,?,?,?)"
	);

	$stmt->bind_param("issi", $request["id_encomenda"], $request["obs"], $data, $_SESSION["user_id"]);
	if($stmt->execute()){
		echo json_encode(['resultado'=>'Observação adicionada com sucesso']);
		}
	else{
		echo json_encode(['resultado'=>'Falha ao adicionar observação']);
	}

	exit();
}
?>

<!DOCTYPE HTML>
<html>

<head>
    <title>MPP - Editar Encomenda</title>
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
                        <h2>Editar encomenda ID: <?= $_GET["id"] ?></h2>

                        <!-- Detalhes da encomenda -->
                        <?php 
                            $stmt = $conn->prepare(
                                "SELECT * FROM encomenda
                                JOIN utilizador ON utilizador.id_utilizador = encomenda.id_utilizador
                                WHERE id_encomenda = ?"
                            );

                            $stmt->bind_param("i", $_GET["id"]);

                            $stmt->execute();

                            $result = $stmt->get_result();

                            if($result->num_rows != 1){
                                $stmt->close();
                                header("Location: tratar_encomendas.php");
                                exit();
                            }

                            $encomenda = $result->fetch_assoc();
                            $stmt->close();
                        ?>
                        <div class="box">
                            <h3>Detalhes da encomenda</h3>

                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>Data: </strong><?= date('d/m/Y', strtotime($encomenda["data_encomenda"])) ?></li>
                                        <li><strong>Número da encomenda: </strong><?= $encomenda["num_encomenda"] ?></li>
                                        <li><strong>Utilizador: </strong><?= $encomenda["username"] ?></li>
                                        <li><strong>Código MEGA: </strong><?= $encomenda["codigo_mega"] ?></li>
                                    </ul>
                                </div>

                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>Aluno: </strong><?= $encomenda["nome_aluno_encomenda"] ?></li>
                                        <li><strong>NIF Aluno: </strong><?= $encomenda["nif_encomenda"] ?></li>
                                        <li><strong>E.E. Aluno: </strong><?= $encomenda["ee_encomenda"] ?></li>
                                        <li><strong>Telefone: </strong><?= $encomenda["telefone_encomenda"] ?></li>
                                    </ul>
                                </div>

                                <br>

                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li>
                                            <strong>Plastificar Manuais: </strong>
                                            <?php 
                                            if($encomenda["plast_manuais"] == 1){
                                                echo "Sim";
                                            }
                                            else{
                                                echo "Não";
                                            }
                                            ?>
                                        </li>
                                        <li>
                                            <strong>Plastificas livro de fichas: </strong>
                                            <?php 
                                            if($encomenda["plast_livro_fichas"] == 1){
                                                echo "Sim";
                                            }
                                            else{
                                                echo "Não";
                                            }
                                            ?>
                                        </li>
                                        <li>
                                            <strong>Etiquetas: </strong> 
                                            <?php 
                                            if($encomenda["etiquetas"] == 1){
                                                echo "Sim - " . $encomenda["obs_etiquetas"];
                                            }
                                            else{
                                                echo "Não";
                                            }
                                            ?>
                                        </li>
                                        <li><strong>Observações: </strong><?= $encomenda["obs_encomenda"] ?></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <p><strong>Total encomenda: </strong><?= $encomenda["total_encomenda"] ?>€</p>
                                </div>
                                <div class="col-4 col-12-small">
                                    <p><strong>Caução paga: </strong><?= $encomenda["valor_caucao"] ?>€</p>
                                </div>
                                <div class="col-4 col-12-small">
                                    <p><strong>Doc. Encomenda: </strong><a href="<?= $encomenda["doc_encomenda"] ?>" target="_blank">Ver documento</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- Manuais da encomenda -->
                        <?php 
                            $stmt = $conn->prepare(
                                "SELECT encomenda_manual.*, manual.*, disciplina.nome_disciplina, utilizador.username FROM encomenda_manual
                                JOIN manual ON manual.id_manual = encomenda_manual.id_manual
                                JOIN disciplina ON disciplina.id_disciplina = manual.id_disciplina
                                LEFT JOIN utilizador ON utilizador.id_utilizador = encomenda_manual.id_separado
                                WHERE id_encomenda = ?"
                            );

                            $stmt->bind_param("i", $_GET["id"]);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $manuais = $result->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <div class="box">
                            <h3>Manuais da encomenda</h3>

                            <div class="table-wrapper">
                                <table class="alt">
                                    <thead>
                                        <tr>
                                            <th>ID manual</th>
											<th>ISBN</th>
											<th>Nome</th>
                                            <th>Preço</th>
                                            <th>Código</th>
                                            <th>Utiliza voucher</th>
                                            <th>Manual separado</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    <?php 
                                        foreach($manuais as $manual){?>
                                            <tr>
												<td><?= $manual["id_manual"] ?></td>
                                                <td><?= $manual["isbn_manual"] ?></td>
                                                <td><?= $manual["nome_manual"] ?></td>
                                                <td><?= $manual["preco_manual"] ?></td>
                                                <td><?= $manual["cod_manual"] ?></td>
												<td>
                                                    <?php 
                                                    if($manual["voucher"] == 1){?>
                                                        <span>Sim</span>
                                                        <?php 
                                                    }
                                                    else{?>
														<span>Não</span>
                                                    <?php
                                                    }
                                                    ?>
												</td>
                                                <td>
                                                    <?php 
                                                    if($manual["manual_separado"] == 1){?>
                                                        <span>Sim</span>
                                                        <?php 
                                                    }
                                                    else{?>
														<span>Não</span>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

						<!-- Observações -->
						<div class="box">
							<h3>Observações adicionais</h2>

							<h4>Adicionar observação</h4>
							<div class="row aln-middle">
								<div class="col-4">
									<textarea name="obs" id="obs" data-id_encomenda="<?= $_GET["id"] ?>"></textarea>
								</div>
								<div class="col-4">
									<button id="btnObs" >Adicionar observação</button>
								</div>
								<div class="col-12">
									<p id="msgErro"></p>
								</div>
							</div>
							
							<div class="row" style="margin-top: 10px;">
								<div class="col-12">
								<?php 
									$stmt = $conn->prepare(
										"SELECT * FROM observacao_encomenda
										JOIN utilizador ON observacao_encomenda.id_utilizador = utilizador.id_utilizador
										WHERE id_encomenda = ?"
									);
									$stmt->bind_param("i", $_GET["id"]);
									$stmt->execute();
									$result = $stmt->get_result();
									$rows = $result->fetch_all(MYSQLI_ASSOC);

									foreach($rows as $obs){?>
										<dl>
											<dt><?= $obs["username"] ?> : <?= $obs["data_observacao"] ?></dt>
											<dd><?= $obs["observacao_encomenda"] ?></dd>
										</dl>
									<?php
									}
								?>
								</div>
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
	<script src="assets/js/detalhe_encomenda.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>