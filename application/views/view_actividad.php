<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Actividades RAIS</title>
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<?php echo script('ckeditor.js'); ?>
	<?php echo script('sample.js'); ?>
	<?php echo style('sample.css'); ?>

</head>
<body>
	<h1 class="samples"><?php  echo $titulo; ?></h1>

	<?php if(count($comprom)>0){ ?>
	<h2>Compromisos no culminados</h2>
	<?php echo anchor('aucoevaluacion/ejecuta','Autoevaluaci&oacute;n') ?>
	<table align='center'>
		<tr>
			<th>Fecha</th>
			<th>Compromiso</th>
			<th>Ejecuci&oacute;n</th>
		</tr>
		<?php foreach($comprom as $com){ ?>
		<tr>
			<td><?php echo date('d/m/Y H:i:s',mysql_to_unix($com['fecha']));      ?></td>
			<td><?php echo $com['compromiso']; ?></td>
			<td><?php echo anchor('tareas/dataedit/'.$com['id'].'/create','Delegar'); ?></td>
			<td><?php
			if(!empty($com['sub'])){
				$ul=explode(',',$com['sub']);
				$ulshow=array();
				foreach($ul as $val){
					$sub=explode(':',$val);

					$usuario = $sub[0];
					$nombre  = $sub[1];
					$acti    = $sub[2];

					if($usuario==$integrante){
						$pivot=anchor('tarearesol/filteredgrid/'.$com['id'],$nombre ).':'.$acti;
					}else{
						$pivot=$nombre.':'.$acti;
					}

					$ulshow[]=$pivot;
				}
				echo ul($ulshow);

			}else{
				echo ul(array('Todos'));
			}
			?></td>
			<td align='right'><?php echo $com['ejecucion'];  ?>%</td>
		</tr>
		<?php } ?>
	</table>
	<?php } ?>

	<form action="<?php echo site_url('inicio/guardaacti'); ?>" method="post">
		<p>
			<label for="editor1">
				<h2>ISA realizada:</h2>
			</label>
			<textarea class="ckeditor" cols="80" id="actividad" name="actividad" rows="10"></textarea>
		</p>
	</form>
	<div id="footer">
		<hr />
		<p id="copy">
			Astor
		</p>
	</div>
	<p><?php echo anchor('inicio/cese','Salir');?></p>
	<div>
		<?php
		foreach($actis AS $acti){
			echo '<p>'.$acti.'</p>';
		}
		?>
	<div>
</body>
</html>
