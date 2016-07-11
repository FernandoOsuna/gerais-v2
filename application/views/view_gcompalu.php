<div align="center">
<?php echo img(site_url('dashboard/gauge/'.$prom.'/gauge.png')); ?>
</div>
<?php
if(isset($auco)){
	echo $auco;
}
?>
<?php
if(isset($eval)){
	echo $eval;
}
?>
<h4><?php echo $nombre ?></h4>
<p><?php echo $integrantes ?></p>
<?php if(!empty($producto)){ ?>
<p><b><?php echo $producto ?></b>: <?php echo $pnombre ?></p>
<?php }elseif($role<=2){ ?>
<p><a href='<?php echo site_url('companias/dataeditmobil/modify/'.$id_compania); ?>' >Seleccionar un Producto</a></p>
<?php }else{ ?>
<p>No tiene producto Asignado.</p>
<?php } ?>

<div data-role='collapsible' data-collapsed='false' data-theme="c" ><h3>Tareas asignadas a ti</h3>
<p><ul data-role='listview'>
<?php
foreach ($ecomprom as $row){
	$color  = color($row->ejecucion);
	if(empty($row->id_resol))
		$link = site_url('tarearesol/dataeditmobil/'.$row->subid.'/create');
	else
		$link = site_url('tarearesol/dataeditmobil/'.$row->subid.'/modify/'.$row->id_resol);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->tarea;
	$subcompro = $row->tarea;
	$subfecha  = mdate('%d/%m/%Y', mysql_to_unix($row->subfecha));
	$subeje    = $row->subejecucion;
	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		<a href='$link' style='white-space: normal;' >$fecha - $compro <b style='color:#$color;'>".$row->ejecucion."%</b></a>
		<p>$subfecha- $subcompro $subeje%</p>
	</li>";
}
?>
</ul></p>
</div>

<div data-role='collapsible' data-collapsed='true'><h3>Compromisos de la Compa&ntilde;&iacute;a</h3>
<p><ul data-role='listview'>

<?php
foreach ($comprom as $row){
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->compromiso;
	$color  = color($row->ejecucion);
	$link   = site_url('dashboard/gsubcompalu/'.$row->id);
	if($role<=2){
		$cont   = "<a href='$link' style='white-space: normal;' >$fecha - $compro</a>";
	}else{
		$cont   = "$fecha - $compro";
	}

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c' style='white-space: normal;'>
		$cont
		<b style='color:#$color;'>".$row->ejecucion."%</b>
	</li>";
}
?>
</ul></p>
</div>

<div data-role='collapsible' data-collapsed='true'><h3>Informe Semanal de Resultados</h3>
<p><ul data-role='listview'>

<?php
foreach ($isa as $row){
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$link   = site_url('/tarearesol/datashow/0/0/'.$row->idcomp.'/show/'.$row->id);
	$compro = $row->tarea;
	$cont   = "<a href='$link' data-rel='page' style='white-space: normal;' >$fecha - $compro</a>";

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$cont
	</li>";
}
?>
</ul></p>
</div>

<div data-role='collapsible' data-collapsed='true'><h3>Reportes ISAs</h3>
<p>
	<div data-role="collapsible-set" data-mini="true" data-theme="c">
<?php
$intid=-1;
foreach($isas AS $isa){
	if($isa->integrante!=$intid){
		if($intid>=0) echo '</div>';
		echo '<div data-role="collapsible" data-collapsed="true" data-mini="true"><h3>'.$isa->nombre.' '.$isa->apellido.'</h3>';
		$intid=$isa->integrante;
	}
	?>
	<p><?php
	$link   = site_url('/tarearesol/datashow/0/0/'.$isa->idcomp.'/modify/'.$isa->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($isa->fecha));
	$compro = $isa->tarea;
	echo  "<a href='$link' data-rel='page' style='white-space: normal;' >$fecha - $compro</a>";
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

	$link   = site_url('dashboard/gsubcompalu/'.$row->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->tarea;
	$prodnom= (empty($row->prodnom))? 'Sin nombre' : $row->prodnom;

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c' style='white-space: normal;'>
		<a href='$link' style='white-space: normal;' >$fecha - $compro</a>
		".$row->ejecucion."% $prodnom
	</li>";
}
?>
</ul></p>
</div>
<?php } ?>
