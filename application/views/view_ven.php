<?php
/**
  * Vista base para todos los controladores
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
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
		
	<script type="text/javascript">
		function resize() {
		height = $("#iframeforo").contents().find("body").outerHeight();
		
		$("#iframeforo").attr("height", height);
		}
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
<div data-role="page">

	<div data-role="header" data-theme="b">
		<?php
		$url = $this->uri->uri_string();
		$eurl= explode('/',$url);
		$lurl= array_pop($eurl);

		if(!empty($lurl)){
		if($lurl!='login' && $lurl!='logout' && $lurl!='gcompalu' && $lurl!='gcompalu_foro' && $lurl!='panel' && $lurl!='resp' && $lurl!='loginAdmin' && $lurl!='curso'){ ?>
			<?php $url=(isset($home_url))? site_url($home_url) : site_url('dashboard'); ?>
			<a href="<?php echo $url; ?>" data-icon="home" data-iconpos="notext" data-direction="reverse">Inicio</a>
		<?php }
		else if($lurl=='panel' || $lurl=='resp'){ ?>
			<?php $url=(isset($home_url))? site_url($home_url) : site_url('panel'); ?>
			<a href="<?php echo $url; ?>" data-icon="home" data-iconpos="notext" data-direction="reverse">Inicio</a>
		<?php }
		else if($lurl=='loginAdmin'){ ?>
			<?php $url=(isset($home_url))? site_url($home_url) : site_url('inicio'); ?>
			<a href="<?php echo $url; ?>" data-icon="home" data-iconpos="notext" data-direction="reverse">Inicio</a>
		<?php }
		else if($lurl=='gcompalu_foro'){ ?>
			<?php $url=(isset($home_url))? site_url($home_url) : site_url('dashboard/gcompalu'); ?>
			<a href="<?php echo $url; ?>" data-icon="home" data-iconpos="notext" data-direction="reverse">Inicio</a>
		<?php }
		}
		?>
		<h1><?php echo (isset($header))? $header :'GeRAIS'; ?></h1>
		<?php if(isset($back_url)){ ?>
			<a href="<?php echo site_url($back_url); ?>" data-icon="back" data-iconpos="notext" data-direction="reverse">Regresar</a>
		<?php } ?>
		<?php if(isset($logout)){ ?>
			<?php if($logout==0){ ?>
				<a href="<?php echo site_url('inicio/logout/'.$logout) ?>" data-theme="f" data-icon="minus">Salir</a>
			<?php } 
			else if($logout>0){ ?>
			<a href="<?php echo site_url('panal'.$logout.'/logout') ?>" data-theme="f" data-icon="minus">Salir</a>
		<?php } 
			}?>
		<?php if(isset($headerextra)){ ?>
		<p style="clear: both; font-size: 85%; margin-bottom: 8px;"><?php echo $headerextra; ?></p>
		<?php } ?>
	</div>

	<div data-role="content" data-theme="c" data-cache="never" >
		<?php echo (isset($content))? $content :'Contenido'; ?>
	</div>

	<div data-role="footer" data-theme="c">
		<h4><?php echo (isset($footer))? $footer :'Sistema GeRAIS'; ?></h4>
	</div>
</div>

</body>
</html>

