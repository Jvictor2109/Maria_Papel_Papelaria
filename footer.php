<?php
// Nada antes da tag de abertura PHP
?>
<footer id="footer">
	<p class="copyright">&copy; Maria Papel Papelaria 2026. All rights reserved. Design: <a href="https://html5up.net">HTML5 UP</a>.</p>
</footer>
<?php
if(isset($_SESSION["user_id"])){?>
<script src="assets/js/timeout_sessao.js"></script>
<?php }
?>