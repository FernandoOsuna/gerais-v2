<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Correos</title>
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<?php echo style('redmond/jquery-ui-1.8.1.custom.css'); ?>
	<?php echo style('ui.jqgrid.css'); ?>
	<?php echo style('ui.multiselect.css'); ?>
	<?php echo script('jquery.js'); ?>
	<?php echo script('interface.js'); ?>
	<?php echo script('jquery-ui.js'); ?>
	<?php echo script('jquery.layout.js');?>
	<?php echo script('i18n/grid.locale-sp.js'); ?>
	<?php echo script('ui.multiselect.js'); ?>
	<?php echo script('jquery.jqGrid.min.js'); ?>
	<?php echo script('jquery.tablednd.js'); ?>
	<?php echo script('jquery.contextmenu.js'); ?>
	<?php echo script('swfobject.js'); ?>
</head>
<body>
	<h1><?php  echo $titulo; ?></h1>
	<div>
		<?php if(isset($msj)){
			echo '<b>'.$msj.'</b>';
		}?>
		<?php echo form_open(); ?>
		<table>
			<tr>
				<td>Para:</td>
				<td><?php
					$options=$correos;
					echo form_dropdown('mensaje', $options, 'large');
				?></td>
			</tr>
			<tr>
				<td colspan=3><?php
				$data = array(
						'name'        => 'texto',
						'id'          => 'texto',
						'rows'        => '8',
						'cols'        => '80'
				);
				echo form_textarea($data);
				?></td>
			</tr>
			<tr>
				<th colspan=2 style='text-align:right'><?php echo form_submit('btn_envio', 'Enviar'); ?></th>
			</tr>
		</table>
		<?php echo form_close(); ?>
	<div>

</body>
</html>
