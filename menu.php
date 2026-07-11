<?php
?>


<!-- Login -->
<!-- Seção de Login/Logout no header -->
<section id="search" class="alt">
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Se o utilizador ESTÁ autenticado -->
        <div style="text-align: center; padding: 10px; background: #f5f6f7; border-radius: 5px;">
            <p style="margin: 0 0 5px 0; font-weight: bold;">
                 <?php echo htmlspecialchars($_SESSION['nome_utilizador'] ?? 'Utilizador'); ?>
            </p>
            <p style="margin: 0 0 10px 0; font-size: 0.8em; color: #666;">
                <?php echo isset($_SESSION['e_administrador']) && $_SESSION['e_administrador'] == 1 ? '🔑 Administrador' : '👤 Utilizador'; ?>
            </p>
            <a href="logout.php" class="button primary" >Logout</a>
        </div>
    <?php else: ?>
        <!-- Se o utilizador NÃO ESTÁ autenticado -->
        <a href="login.php" class="button primary">Login</a>
    <?php endif; ?>
</section>


<!-- Menu -->
<nav id="menu">
    <header class="major">
        <h2>Menu</h2>
    </header>
    <ul>
        <li><a href="index.php">Início</a></li>
            
        <li><a href="expedicao_vasp.php">Expedições VASP</a></li>
                <?php
                    if(isset($_SESSION['user_id'])){
                        echo '<li><a href="reposicao.php">Material a pedir</a></li>';
                        echo '
                            <li>
                                <span class="opener">Gestão Encomendas</span>
                                <ul>
                                    <li><a href="encomendar_manuais.php">Encomendar manuais escolares</a></li>
                                    <li><a href="tratar_encomendas.php">Encomendas a tratar</a></li>
                                    <li><a href="pesquisar_encomendas.php">Pesquisar encomendas</a></li>
                                    <li><a href="verificar_caucoes.php">Verificar cauções</a></li>
                                    <li><a href="avisos_encomendas.php">Aviso encomendas</a></li>
                                </ul>
                            </li>
                        ';

                    }

                    if (isset($_SESSION['e_administrador']) && $_SESSION['e_administrador'] === 1) {

                        echo '
                            <li>
                                <span class="opener">Gestão Manuais escolares</span>
                                <ul>
                                    <li><a href="gestao_disciplina.php">Disciplina</a></li>
                                    <li><a href="gestao_editora.php">Editora</a></li>
                                    <li><a href="gestao_agrupamento.php">Agrupamento</a></li>
                                    <li><a href="gestao_ano_escolar.php">Ano Escolar</a></li>
                                    <li><a href="gestao_ano_letivo.php">Ano letivo</a></li>
                                    <li><a href="gestao_manual.php">Manual</a></li>
                                    <li><a href="manuais_a_encomendar.php">Manuais a encomendar</a></li>
                                    <li><a href="encomendas_editora.php">Encomendas editora</a></li>
                                </ul>
                            </li>
                        ';

                        echo '<li>
                            <span class="opener">Painel de Controlo</span>
                            <ul>
                                <li><a href="gestao_utilizadores.php">Utilizador</a></li>
                            </ul>
                        </li>';

                    }
                ?>
    </ul>
</nav>
<?php
// Nada depois do menu
?>