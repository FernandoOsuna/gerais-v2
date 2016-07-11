<?php
/**
  * Vista base para todos los controladores
  *
  * @autor  Fernando Osuna
  * @package views
  */
?><!DOCTYPE html>
<html>
	<head>
	<title>Gestor de Actividades RAIS</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-type" content="text/html; charset=<?php echo $this->config->item('charset'); ?>" >
	<link rel="shortcut icon" type="image/x-icon" href="/gerais/img/favicon.ico">
	<?php echo style('my-custom-theme2.css');   ?>
	<?php echo style('jquery.mobile.icons.min.css');   ?>
	<?php echo style('jquery.mobile.datepicker.css');  ?>
	<?php echo style('jquery.mobile.datepicker.theme.css');  ?>
	<?php echo style('jquery.mobile.structure-1.4.3.css');        ?>
	
	<?php echo script('jquery-1.11.1.min.js');        ?>
	<?php echo script('jquery.mobile-1.4.3.min.js'); ?>
	<?php echo script('jquery.mobile.datepicker.js'); ?>
	<?php echo script('external/jquery-ui/datepicker.js'); ?>
	<?php echo script('tinymce/js/tinymce/tinymce.min.js'); ?>	

	<script>
		$(function(){
			$( ".date-input-css" ).datepicker();
		});
	</script>


</head>
<body>
<script>
	$(document).bind("mobileinit", function(){
		<?php
			if(isset($onLoadScript)){
				echo $onLoadScript;
			}
		?>
	});
</script>
<div data-role="page" data-theme="c">

	<div data-role="header"  data-theme="b">
		<div align="center">
			<h1><?php echo image('logop.png'); ?><span style="font-size: 150%">Panal RAIS</span></h1>
		</div>
	</div>

	<div data-role="content" data-theme="c" data-cache="never" >
		<?php echo (isset($content))? $content :'Contenido'; ?>
	</div>

	<div data-role="footer">
	<h4><?php echo (isset($footer))? $footer :'Sistema GeRAIS'; ?></h4>
	</div>
</div>

</body>
</html>

