<div class="ui-body ui-body-e ui-corner-all" align="center">
<h3><?php echo mdate('%d/%m/%Y', mysql_to_unix($fecha)); ?> <?php echo $compromiso; ?> <span style='color:#<?php echo color($ejecucion); ?>'><?php echo $ejecucion.'%';?></span></h3>
</div>

<p>
<b><?php echo $nombre; ?></b> - <?php echo $descrip; ?>
</p>

<div data-role='collapsible' data-collapsed='false'><h3>Tareas en proceso</h3>
<p><ul data-role='listview'>
<?php

foreach ($ecomprom as $row){
	if(mdate('%Y%m%d', mysql_to_unix($row->fecha))>=date('Ymd')){
		$color  = '000000';
	}else{
		$color  = color($row->ejecucion);
	}
	$itfecha= mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->tarea;
	$nombre = (empty($row->nombre))? '<b style="color:red">ninguno</b>' : $row->nombre;

	$date_compr = new DateTime($fecha);
	$date_ahora = new DateTime();

	$showEje= ($ejecucion>=100)? 100: $row->ejecucion;
	if($role==2 && $ejecucion<100 && $date_compr>=$date_ahora){
		$link   = site_url('tareas/dataeditmobil/'.$id_producto.'/'.$id_comp.'/modify/'.$row->id);
		$cont   ="<a href='$link' >$itfecha - $nombre - $compro</a>";
	}else{
		$cont="$itfecha - $nombre - $compro";
	}
	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$cont
		Peso:".$row->peso.' - <b style=\'color:#'.$color.'\'>'.$showEje."%</b>
	</li>";
}
?>
</u></p>
</div>
