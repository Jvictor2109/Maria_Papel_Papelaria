<?php
// Verificação de sessão em todas as páginas protegidas
session_start();
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request = json_decode(file_get_contents('php://input'), true);
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
                        <h2>Editar encomenda Nº <?= $_GET["id"] ?></h2>

                        <div class="box">
                            <h3>Detalhes da encomenda</h3>

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
                                header("Location: tratar_encomendas.php");
                                exit();
                            }

                            $encomenda = $result->fetch_assoc();
                            ?>

                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>ID: </strong><?= $encomenda["id_encomenda"] ?></li>
                                        <li><strong>Data: </strong><?= date('d/m/Y', strtotime($encomenda["data_encomenda"])) ?></li>
                                        <li><strong>Número da encomenda: </strong><?= $encomenda["num_encomenda"] ?></li>
                                        <li><strong>Utilizador: </strong><?= $encomenda["username"] ?></li>
                                    </ul>
                                </div>

                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>Aluno: </strong> joao</li>
                                        <li><strong>NIF Aluno: </strong> 999999999</li>
                                        <li><strong>E.E. Aluno: </strong> Libna</li>
                                        <li><strong>Telefone: </strong> 999999999</li>
                                    </ul>
                                </div>

                                <br>

                                <div class="col-4 col-12-small">
                                    <ul class="alt">
                                        <li><strong>Plastificar Manuais: </strong> não</li>
                                        <li><strong>Plastificas livro de fichas: </strong> não</li>
                                        <li><strong>Etiquetas: </strong> Sim - tal coisa sjsjsjsjsjsjsjsjsjjsjs</li>
                                        <li><strong>Observações: </strong> tralalelo tralalala 676767</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4 col-12-small">
                                    <p><strong>Total encomenda: </strong> 67€</p>
                                </div>
                                <div class="col-4 col-12-small">
                                    <p><strong>Caução paga:</strong> 5€</p>
                                </div>
                                <div class="col-4 col-12-small">
                                    <p><strong>Doc. Encomenda: </strong> /encomendas/etc</p>
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
    <script src="assets/js/tratar_encomendas.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>