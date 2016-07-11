<div class="ui-body ui-body-b ui-corner-all" align="center">
<!--<h3 style='color:#<?php echo color($ejecucion); ?>'>-->
<h3>
	<?php echo $companianom; ?> <br />
	<?php echo mdate('%d/%m/%Y', mysql_to_unix($fecha)); ?> <?php echo $compromiso; ?> <?php echo $ejecucion.'%';?>
</h3>
<?php
$date_compr = new DateTime($fecha);
$date_ahora = new DateTime();
$date_ahora->setTime(0, 0);
if($ejecucion<=100){
	echo '<a href="'.site_url("compromisos/reasignar/$id_compania/modify/".$id_comp).'" data-role="button" data-icon="refresh" data-direction="reverse">Posponer</a>';
}
?>

</div>

<p>
<b><?php echo $nombre; ?></b> - <?php echo $descrip; ?>
</p>

<?php if($ejecucion<=100 && $date_compr>=$date_ahora){ ?>
<?php echo form_open(); ?>

<div class="ui-body ui-body-e ui-corner-all" align="center">
<label for="textarea-a">Nuevas Tareas:</label>
<textarea name="promete" id="promete" ></textarea>
<button value="Guardar" name="mysubmit" type="submit">Guardar</button>
</div>
<?php } ?>

<div data-role='collapsible' data-collapsed='true'><h3>Tareas en proceso</h3>
<p><ul data-role='listview' data-split-icon="star">
<?php

foreach ($ecomprom as $row){
	$viewejec=($ejecucion==100)? 100 : $row->ejecucion;
	if(mdate('%Y%m%d', mysql_to_unix($row->fecha))>=date('Ymd')){
		$color  = 'FFFFFF';
	}else{
		$color  = color($viewejec);
	}
	$idform = 'cpr['.$row->id.']';
	$peform = 'pes['.$row->id.']';
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->tarea;
	$nombre = (empty($row->nombre))? 'ninguno' : $row->nombre;

	if(!empty($row->idresol)){
		$ll = site_url('/tarearesol/datashow/'.$id_comp.'/2/'.$row->id.'/show/'.$row->idresol);
		$btn='<a href="'.$ll.'" data-rel="page" data-icon="star">Resultado</a>';
	}else{
		$btn='';
	}

	if($ejecucion<=100 && $date_compr>=$date_ahora){
		$link  = site_url('tareas/dataeditmobil/0/'.$id_comp.'/modify/'.$row->id);
		$cont  = "<a href='$link' style='white-space: normal;' >$fecha - $nombre - $compro</a>";
	}else{
		$cont  = "<a href=# style='white-space: normal;' >$fecha - $nombre - $compro</a>";
	}

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$cont
		$btn
		<input type='label' name='$peform' id='$peform' value='".$row->peso."' data-mini='true' style='text-align:center; width:50px; background-repeat:no-repeat; background-image:url(\"".base_url()."img/scale.png\"); background-position: left center' class='ui-input-text ui-body-c ui-corner-all ui-shadow-inset' />
		<input type='label' name='$idform' id='$idform' value='".$viewejec."' data-mini='true' style='text-align:center; background-color:#$color; width:50px' class='ui-input-text ui-body-c ui-corner-all ui-shadow-inset' />
		
	</li>";
}
?>
</u></p>
</div>
<?php if($ejecucion<=100 && $date_compr>=$date_ahora){ ?>
<?php echo form_close(); ?>
<?php } ?>


<div data-role='collapsible' data-collapsed='true'><h3>Problemas Corporativos</h3>
<p><ul data-role='listview'>
<?php
foreach ($problemas as $row){

	$fecha = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	if($ejecucion<=100 && $date_compr>=$date_ahora){
		$link  = site_url('problemas/dataeditmobil/'.$id_comp.'/modify/'.$row->id);
		$cont  = "<a href='$link' >$fecha - $row->problema</a>";
	}else{
		$cont  = "$fecha - $row->problema";
	}

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$cont
	</li>";
}
?>
</u></p>
</div>
