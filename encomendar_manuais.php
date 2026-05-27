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
                            <h3>Filtrar manuais</h3>

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

                                    <button type="submit" id="btnFiltrar">Filtrar</button>

                                </div>
                            </div>
                        </div>

						<!-- Tabela mostrando os dados -->
						<div class="table-wrapper">
							<table class="alt">
								<thead>
									<tr>
										<th>Id Agrupamento</th>
										<th>Nome Agrupamento</th>
										<th>Ações</th>
									</tr>
								</thead>

								<tbody>
									
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
			<script src="assets/js/gestao.js"></script>
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>
