<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Tareas RAIS</title>
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<?php echo style('page.css'); ?>
	<?php echo style('jgauge.css'); ?>
	<!--[if IE]><?php echo script('excanvas.min.js'); ?><![endif]-->
	<?php echo script('jquery.js'); ?>
	<?php //echo script('jquery-1.4.2.min.js'); ?>
	<?php echo script('plugins/jQueryRotate.min.js'); ?>
	<?php echo script('plugins/jgauge-0.3.0.a3.min.js'); ?>
	<?php echo script('plugins/jquery.numeric.pack.js'); ?>
	<?php echo script('plugins/jquery.floatnumber.js'); ?>
</head>
<body>
<script type="text/javascript">

	$(document).ready(function(){

	});


</script>
	<h1><?php  echo $titulo; ?></h1>
	<h2><?php  echo $nombre; ?></h2>

	<div>
		<?php echo form_open(); ?>
		<table>
			<tr>
				<td colspan=5>
					<h3><?php echo $producto ?><h3>
					<h4>Accionistas: <?php echo $integrantes; ?></h4>
					<p><?php echo $pnombre; ?></p>
				</td>
			</tr>
			<tr>
				<td colspan=5 bgcolor='yellow' align='center'>
					<h1><?php echo $compronom; ?> <b><?php echo $comproeje; ?>%</b></h1>
				</td>
			</tr>
				<tr>
						<th>Fecha</th>
						<th>Compromiso</th>
						<th>Asignado a</th>
						<th>Peso</th>
						<th>% Avance</th>
				</tr>
		<?php
		foreach($compros AS $compro){
				echo '<tr bgcolor="#'.$compro['color'].'">';
				echo '<td>'.mdate('%d/%m/%Y %i:%s', mysql_to_unix($compro['fecha']));

				echo '</td>';
				echo '<td>';
				echo $compro['compromiso'];
				echo '</td>';
				echo '<td align=\'right\'>'.form_dropdown('inte['.$compro['id'].']', $inte, $compro['id_integrante']).'</td>';
				$data = array(
					'name'        => 'peso['.$compro['id'].']',
					'id'          => 'peso['.$compro['id'].']',
					'value'       => $compro['peso'],
					'maxlength'   => '3',
					'size'        => '5',
					'autocomplete'=> 'off',
					'style'       => 'text-align:right',
				);
				echo '<td align=\'right\'>'.form_input($data).'</td>';
				$data = array(
					'name'        => 'cpr['.$compro['id'].']',
					'id'          => 'cpr['.$compro['id'].']',
					'value'       => $compro['ejecucion'],
					'maxlength'   => '3',
					'size'        => '5',
					'autocomplete'=> 'off',
					'style'       => 'text-align:right',
				);
				echo '<td align=\'right\'>'.form_input($data).'%</td>';
				echo '</tr>';
		}
		?>

			<tr>
				<th colspan=5><h2>Crear Tarea</h2></th>
			</tr>
			<tr>
				<td colspan=5><?php
				$data = array(
						'name'        => 'promete',
						'id'          => 'promete',
						'rows'        => '8',
						'cols'        => '100'
				);
				echo form_textarea($data);
				?></td>
			</tr>
			<tr>
				<td><?php
				$js = "onClick=\"window.location.href='".site_url('inicio/compromiso/'.$id_comp)."'\"";
				echo form_button('btn_back', 'Regresar', $js);
				?></td>
				<th colspan=4 style='text-align:right'><?php echo form_submit('mysubmit', 'Guardar'); ?></th>
			</tr>
		</table>
		<?php echo form_close(); ?>
	<div>
</body>
</html>
