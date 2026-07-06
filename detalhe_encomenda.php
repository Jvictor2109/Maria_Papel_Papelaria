<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$request = json_decode(file_get_contents('php://input'),true);
	$data = date("Y-m-d H:i:s");

    switch($request["acao"]){
        case "add_obs":
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
        
        case "entregar_encomenda":
            entregar_encomenda($conn, $request);
            exit();
        case "cancelar_encomenda":
            cancelar_encomenda($conn, $request);
            exit();
    }
}

function entregar_encomenda(mysqli $conn, array $request){
    $id_encomenda = $request["id_encomenda"];
    $data = date("Y-m-d");

    $conn->begin_transaction();
    try{
        // Marcar a encomenda como entregue
        $stmt_entregue = $conn->prepare(
            "UPDATE encomenda 
            SET estado_encomenda = 'entregue', id_entregue = ?, data_entregue = ?
            WHERE id_encomenda = ?"
        );
        $stmt_entregue->bind_param("isi", $_SESSION["user_id"], $data, $id_encomenda);
        $stmt_entregue->execute();
    
        // Adicionar observação
        $stmt_obs = $conn->prepare(
            "INSERT INTO observacao_encomenda (id_encomenda, observacao_encomenda, data_observacao, id_utilizador)
            VALUES (?,?,?,?)"
        );
        $data_obs = date("Y-m-d H:i:s");
        $obs = "MPP3: A encomenda passou ao estado de entregue.";
        $stmt_obs->bind_param("issi", $id_encomenda, $obs, $data_obs, $_SESSION["user_id"]);
        $stmt_obs->execute();

        $stmt_obs->close();
        $stmt_entregue->close();
        $conn->commit();
    }
    catch(Exception $e){
        $conn->rollback();
        echo json_encode(['resultado'=>'erro', 'msg'=>$e]);
        return;
    }

    echo json_encode(['resultado'=>'sucesso', 'msg'=>'Encomenda entregue com sucesso!']);
    return;
}
function cancelar_encomenda(mysqli $conn, array $request){
    $id_encomenda = $request["id_encomenda"];
    $data = date("Y-m-d");

    $conn->begin_transaction();
    try{
        // Marcar a encomenda como canelada
        $stmt_entregue = $conn->prepare(
            "UPDATE encomenda 
            SET estado_encomenda = 'cancelada', id_cancelado = ?, data_cancelado = ?
            WHERE id_encomenda = ?"
        );
        $stmt_entregue->bind_param("isi", $_SESSION["user_id"], $data, $id_encomenda);
        $stmt_entregue->execute();
    
        // Adicionar observação
        $stmt_obs = $conn->prepare(
            "INSERT INTO observacao_encomenda (id_encomenda, observacao_encomenda, data_observacao, id_utilizador)
            VALUES (?,?,?,?)"
        );
        $data_obs = date("Y-m-d H:i:s");
        $obs = "MPP3: A encomenda passou ao estado de cancelada.";
        $stmt_obs->bind_param("issi", $id_encomenda, $obs, $data_obs, $_SESSION["user_id"]);
        $stmt_obs->execute();

        // Subtrair os manuais a encomendar
        $stmt_manuais = $conn->prepare(
            "SELECT id_manual FROM encomenda_manual
            WHERE id_encomenda = ?"
        );
        $stmt_manuais->bind_param("i", $id_encomenda);
        $stmt_manuais->execute();
        $result = $stmt_manuais->get_result();
        $manuais = $result->fetch_all(MYSQLI_ASSOC);

        $stmt_quant_pedir = $conn->prepare(
            "UPDATE manual
            SET quant_manuais_pedir = quant_manuais_pedir - 1, quant_manuais_enc = quant_manuais_enc - 1
            WHERE id_manual = ?"
        );
        foreach($manuais as $manual){
            $stmt_quant_pedir->bind_param("i",$manual["id_manual"]);
            $stmt_quant_pedir->execute();
        }

        $stmt_quant_pedir->close();
        $stmt_obs->close();
        $stmt_manuais->close();
        $stmt_entregue->close();
        $conn->commit();
    }
    catch(Exception $e){
        $conn->rollback();
        echo json_encode(['resultado'=>'erro', 'msg'=>$e]);
        return;
    }

    echo json_encode(['resultado'=>'sucesso', 'msg'=>'Encomenda cancelada com sucesso!']);
    return;
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
                        <h2>Encomenda ID: <?= $_GET["id"] ?></h2>

                        <!-- Detalhes da encomenda -->
                        <div class="box">
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

                            <div class="row aln-middle" style="margin-top: 10px;">
                                <div class="col-4 col-12-small">
                                    <?php 
                                    $cores_estados = [
                                        'registada'=>'darkred',
                                        'pedida'=>'orange',
                                        'concluida'=>'goldenrod',
                                        'entregue'=>'green',
                                        'cancelada'=>'red'
                                    ]
                                    ?>
                                    <h4><strong>Estado da encomenda: <span style="color:<?= $cores_estados[$encomenda['estado_encomenda']] ?>"><?= $encomenda["estado_encomenda"] ?></span></strong></h4>
                                </div>
                                <div class="col-4 col-12-small" style="margin-bottom: 10px;">
                                    <?php
                                    $estado_encomenda = $encomenda["estado_encomenda"];
                                    if($estado_encomenda == "concluida" && $estado_encomenda != "cancelada") {?>
                                        <button class="primary small" id="btnEntregar">Marcar como entregue</button>

                                    <?php }
                                     ?>
                                </div>
                                <div class="col-4">
                                    <?php
                                    $estado_encomenda = $encomenda["estado_encomenda"];
                                    if($estado_encomenda != "entregue" && $estado_encomenda != "cancelada") {?>
                                        <button class="secondary small" id="btnCancelar">Cancelar encomenda</button>

                                    <?php }
                                     ?>
                                </div>
                            </div>

                            <!-- Informação acerca do aviso da encomenda -->
                            <div class="row aln-middle" style="margin-top: 10px;">
                                <?php 
                                if($encomenda["avisado"] == 1){?>
                                <div class="col-4 col-12-small">
                                    <p><strong>Encomenda avisada: Sim - <?= $encomenda["data_aviso"] ?></strong></p>
                                </div>
                                <div class="col-4 co-12-small">
                                    <?php 
                                    if($encomenda["estado_encomenda"] == "concluida" && !empty($encomenda["email_encomenda"])){?>
                                        <button class="primary small" data-id_encomenda="<?= $encomenda["id_encomenda"] ?>" id="btnNovoAviso">Novo aviso</button>
                                    <?php }
                                    ?>
                                </div>

                                <?php 
                                }
                                else{?>
                                    <h4><strong>Encomenda avisada: Não</strong></h4>
                                <?php }
                                
                                ?>
                            </div>
                        </div>

                        <!-- Manuais da encomenda -->
                        <div class="box">
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
                                            <th>Data separação</th>
                                            <th>Utilizador</th>
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
                                                        if(!isset($manual["data_separacao"])){
                                                            echo "-";
                                                        }
                                                        else{
                                                            echo date('d/m/Y', strtotime($manual["data_separacao"])); 
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        if(!isset($manual["id_separado"])){
                                                            echo "-";
                                                        }
                                                        else{
                                                            echo $manual["username"]; 
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

                            <!-- Somente quando tiver registada/pedida que pode se fazer observações -->
                            <?php 
                            $estado_encomenda = $encomenda["estado_encomenda"];
                            if($estado_encomenda != 'entregue' && $estado_encomenda != 'cancelada'){?>
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
                            <?php }
                            ?>

							
							<div class="row" style="margin-top: 10px;">
								<div class="col-12">
								<?php 
									$stmt = $conn->prepare(
										"SELECT * FROM observacao_encomenda
										JOIN utilizador ON observacao_encomenda.id_utilizador = utilizador.id_utilizador
										WHERE id_encomenda = ?
                                        ORDER BY data_observacao DESC"
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

                        <!-- Modal confirmar encomenda -->
                        <div id="modalEntregar" class="modal-overlay" style="display: none;">
                            <div class="box modal-content">
                                <span id="close-modal" class="modal-close">&times;</span>
                                <h3>Entregar encomenda</h3>

                                <div>
                                    <p>Marcar a encomenda como entregue?</p>
                                    <button id="confirmarEntregar" class="primary" data-id_encomenda="<?= $encomenda["id_encomenda"] ?>">Sim</button>
                                    <button id="fecharConfirmar">Não</button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal cancelar encomenda -->
                        <div id="modalCancelar" class="modal-overlay" style="display: none;">
                            <div class="box modal-content">
                                <span id="close-modal" class="modal-close">&times;</span>
                                <h3>Cancelar encomenda</h3>

                                <div>
                                    <p>Tem a certeza de que quer cancelar a encomenda??</p>
                                    <button id="confirmarCancelar" class="primary" data-id_encomenda="<?= $encomenda["id_encomenda"] ?>">Sim</button>
                                    <button id="fecharCancelar">Não</button>
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