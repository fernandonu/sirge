<?php
require 'disenio/header.php';
?>
<script>
$(document).ready(function(){
	$('p').hide();
	$('form input:submit').button();
	$('body').css({'background-color' : 'white'});
	
	$('form').submit(function(event){
		event.preventDefault();
		$.ajax({
			type : 'POST' ,
			url  : $(this).attr('action') ,
			data : $(this).serialize() ,
			success : function (data) {
				if (data == 'error') {
					$('p').html('Error en ingreso!').show();
				} else {
					window.location.href = 'index.php';
				}
			}
		});
	});
	
	$('.input-login').focus(function(){
			$(this).val('');
			$(this).css({
				'color' : '#000000',
				'font-style' : 'normal'
			})
		});
		
	$('.input-login').blur(function(){
		if ($(this).val().length == 0) {
			$(this).css({
				'color' : '#CCC',
				'font-style' : 'italic'
			});
			$(this).val('Usuario');
		};
	});
	
	$('.login-principal').hide();
	$('.login-principal').fadeIn("slow");
	
	
});
</script>
		<div class="login-principal">
			<div class="login-header-sumar"><img src="img/header_pdf.png" /></div>
			<div class="login-logo-sumar"><img src="img/sumar-1.png" /></div>
			<div class="login-form">
				<form action="sistema/login.php" method="post" autocomplete="off">
					<input class="input-login" id="usr" type="text" name="usuario" value="Usuario" />
					<br />
					<input class="input-login" id="pwd" type="password" name="password" value="Usuario" />
					<br />
					<input class="submit-login" type="submit" value="Ingresar" />
				</form>
				<p></p>
			</div>
		</div>
	</body>
</html>