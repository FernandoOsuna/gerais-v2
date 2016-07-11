<?php
$atts=array('data-ajax'=>'false');
echo form_open('inicio/loginAdmin',$atts);
?>
<table align='center'>
	<tr>
		<td rowspan=4><?php echo image('login.png'); ?></td>
		<td colspan=2><?php echo $error; ?></td>
	</tr>
	<tr>
		<td>Usuario:</td>
		<td><input type="text" name="usr" value="" size="10" autocomplete='off' /></td>
	</tr>
	<tr>
		<td>Clave:</td>
		<td><input type="password" name="pwd" value="" size="10" /></td>
	</tr>
	<tr>
		<td colspan=2 style='text-align:right'>
			<input type="submit" value="Entrar" />
		</td>
	</tr>
</table>
<?php echo form_close(); ?>
</p><br />
