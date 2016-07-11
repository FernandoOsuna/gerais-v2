<div align="center">
<?php echo img(site_url('dashboard/gauge/'.$prom.'/gauge.png')); ?>
</div>
<h2><?php echo $integrantes ?></h2>
<?php
$clink = site_url('dashboard/gcompania/'.$id_compania);

echo "<p><a href='$clink' style='text-decoration:none'> $nombre </p></a>"
?>
<?php if(!empty($producto)){ ?>
<p><b><?php echo $producto ?></b>: <?php echo $pnombre ?></p>
<?php }else{ ?>
<p>No tiene producto Asignado.</p>
<?php } ?>

<div data-role='collapsible' data-collapsed='true'><h3>Lista de Penalizaciones</h3>
<p><ul data-role='listview'>
<?php
foreach ($penaliza as $row){

	$link   = site_url('aucoevaluacion/pedataedit/'.$id_int.'/modify/'.$row->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->compromiso;
	$color  = ($row->exonerada=='N')? 'red': 'green';
	$exon   = ($row->exonerada=='N')? '': 'Exonerada';

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		<a href='$link' ><b style='color:$color;'>$fecha - $compro $exon</b></a>
	</li>";
}
?>
</ul></p>
</div>

<div data-role='collapsible' data-collapsed='true'><h3>Resultados de las Evaluaciones</h3>
<p><ul data-role='listview'>
<?php
foreach ($evalu as $row){
	$fecha = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$resul = $row->resultado;

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$fecha - Calificación: $resul
	</li>";
}
?>
</ul></p>
</div>
<div data-role='collapsible' data-collapsed='true'><h3>Tareas</h3>
<p><ul data-role='listview'>
<?php
foreach ($ecomprom as $row){
	if(mdate('%Y%m%d', mysql_to_unix($row->fecha))>=date('Ymd')){
		$color  = 'FFFFFF';
	}else{
		$color  = color($row->ejecucion);
	}
	$idform = 'cpr['.$row->id.']';
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->compromiso.' - '.$row->tarea;

	if(empty($row->id_resol)){
		$btn='';
	}else{
		$ll = site_url('/tarearesol/datashow/'.$row->subid.'/show/'.$row->id_resol);
		$btn='<a href="'.$ll.'" data-rel="dialog" data-icon="star" data-role="button" data-direction="reverse">Ver ISA</a>';
	}
	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		<a href='#' style='white-space: normal;'><input type='label' readonly='readonly' name='$idform' id='$idform' value='".$row->ejecucion."' data-mini='true' style='text-align:center; background-color:#$color; width:40px' class='ui-input-text ui-body-c ui-corner-all ui-shadow-inset' />
		$fecha - $compro Peso ".$row->peso."</a>
		$btn
	</li>";
}
?>
</ul></p>
</div>

<div data-role='collapsible' data-collapsed='true'><h3>Informe de Resultados ISA</h3>
<p><ul data-role='listview'>
<?php
foreach ($isa as $row){
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$link   = site_url('/tarearesol/datashow/'.$row->id_compania.'/1/'.$row->idcomp.'/show/'.$row->id);
	$compro = $row->tarea;
	$cont   = "<a href='$link' data-rel='page' style='white-space: normal;'>$fecha - $compro</a>";

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$cont
	</li>";
}
?>
</ul></p>
</div>
