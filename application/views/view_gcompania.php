<div align="center">
<?php echo img(site_url('dashboard/gauge/'.$prom.'/gauge.png')); ?>
<p>
<?php
$salud = salud($salud);
for($i=1;$i<6;$i++){
	if($i<=$salud){
		echo image('pimiento.png',$salud);
	}else{
		echo image('pimiento_nb.png',$salud);
	}
}
?></p>
</div>
<h2><?php echo $nombre ?></h2>
<p><?php echo $integrantes ?></p>

<?php if(!empty($producto)){ ?>
<p><b><?php echo $producto ?></b>: <?php echo $pnombre ?></p>
<?php }elseif($role<=2){ ?>
<p><a href='<?php echo site_url('companias/dataeditmobil/modify/'.$compania); ?>' >Asignar un Producto</a></p>
<?php }else{ ?>
<p>No tiene producto Asignado.</p>
<?php } ?>

<?php echo form_open(); ?>


<div data-role='collapsible' data-collapsed='true'><h3>Compromisos en proceso</h3>
<p>
	<ul data-role='listview' data-split-icon="refresh">
<?php

foreach ($ecomprom as $row){
	if(mdate('%Y%m%d', mysql_to_unix($row->fecha))>=date('Ymd')){
		$color  = 'FFFFFF';
	}else{
		$color  = color($row->ejecucion);
	}
	$idform = 'cpr['.$row->id.']';
	$link   = site_url('dashboard/gsubcompania/'.$row->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->compromiso;

	$reasig = '<a href="'.site_url("compromisos/reasignar/$compania/modify/".$row->id).'">Posponer</a>';

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		<a href='$link' style='white-space: normal;' >$fecha - $compro</a>
		$reasig<input type='label' name='$idform' id='$idform' value='".$row->ejecucion."' data-mini='true' style='text-align:center; background-color:#$color; width:40px; border:0px; margin-left:20px; margin-top:0px; margin-down:0px;' class='ui-input-text ui-body-c ui-corner-all' />
		
	</li>";
}
?>
</ul>
</p>

</div>

<div data-role='collapsible' data-collapsed='true'><h3>Compromisos completados</h3>
<p><ul data-role='listview'>

<?php
foreach ($comprom as $row){
	$color  = color($row->ejecucion);
	$idform = 'cpr['.$row->id.']';

	$link   = site_url('dashboard/gsubcompania/'.$row->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->compromiso;

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		<a href='$link' style='white-space: normal;' >$fecha - $compro</a>
		<input type='label' name='$idform' id='$idform' value='".$row->ejecucion."' data-mini='true' style='text-align:center; background-color:#$color; width:40px; border:0px; margin-left:20px; margin-top:0px; margin-down:0px;' class='ui-input-text ui-body-c ui-corner-all' />
		
	</li>";
}
?>
</ul></p>
</div>

<?php if(!empty($producto)){ ?>
<div data-role='collapsible' data-collapsed='true' title='Agregar solo en esta Compañia'>
<h3>Agregar Compromiso</h3>	
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
<?php }?>
<div data-role='collapsible' data-collapsed='true'><h3>Reportes ISA</h3>
<p>
	<div data-role="collapsible-set" data-mini="true" data-theme="c">
<?php
$intid=-1;
foreach($isas AS $isa){
	if($isa->integrante!=$intid){
		if($intid>=0) echo '</div>';
		echo '<div data-role="collapsible" data-collapsed="true" data-mini="true"><h3>'.$isa->nombre.' '.$isa->apellido.' - Sección '.$isa->seccion.'</h3>';
		$intid=$isa->integrante;
	}
	?>
	<p><?php
	$link   = site_url('/tarearesol/datashow/'.$compania.'/0/'.$isa->idcomp.'/show/'.$isa->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($isa->fecha));
	$compro = $isa->tarea;
	echo  "<a href='$link' data-rel='page' >$fecha - $compro</a>";
	?></p>

<?php }
if($intid>0) echo '</div>' ?>
</div>
</p>
</div>

<?php if(count($antcomprom)>0){ ?>
<div data-role='collapsible' data-collapsed='true'><h3>Historial de otros productos</h3>
<p><ul data-role='listview'>

<?php
foreach ($antcomprom as $row){
	$color  = color($row->ejecucion);
	$idform = 'cpr['.$row->id.']';

	$link   = site_url('dashboard/gsubcompania/'.$row->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->compromiso;
	$prodnom= (empty($row->prodnom))? 'Sin nombre' : $row->prodnom;

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		<a href='$link' style='white-space: normal;' >$fecha - $compro</a>
		<input type='label' name='$idform' id='$idform' value='".$row->ejecucion."' data-mini='true' style='text-align:center; background-color:#$color; width:40px' class='ui-input-text ui-body-c ui-corner-all ui-shadow-inset' /> $prodnom
	</li>";
}
?>
</ul></p>
</div>
<?php } ?>
<?php echo form_close(); ?>
