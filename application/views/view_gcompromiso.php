<?php echo form_open(); ?>
<p>
	<div data-role='collapsible' data-collapsed='true' class="ui-body-e ui-corner-all" >
	<h3>Nuevos Compromisos</h3>	
	<table align='center' width='100%'>
		<tr>
			<td>Fecha *</td>
			<td><input data-role="date" type="text" name="poster" id="poster" value="<?php echo date('d/m/Y',mktime(0, 0, 0, date('m'), date('d')+7, date('Y')));?>" /></td>
		</tr>
		<tr>
			<td>Compromiso *</td>
			<td><textarea name="promete" id="promete" ></textarea></td>
		</tr>
	</table>
	<button value="Guardar" name="mysubmit" type="submit">Guardar</button>
			
	</div>

</p>
<?php echo form_close(); ?>
	
