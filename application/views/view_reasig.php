<?php
echo $edit;
?>

<div data-role='collapsible' data-collapsed='true' data-theme="b"><h3>Tareas afectadas</h3>
<p><ul data-role='listview'>
<?php

foreach ($ecomprom as $row){
	$color  = color($row->ejecucion);
	//$link   = site_url('tareas/dataeditmobil/'.$id_comp.'/modify/'.$row->id);
	$fecha  = mdate('%d/%m/%Y', mysql_to_unix($row->fecha));
	$compro = $row->tarea;
	$nombre = (empty($row->nombre))? 'ninguno' : $row->nombre;

	//$cont   ="<a href='$link' >$fecha - $nombre - $compro</a>";
	$cont="$fecha - $nombre - $compro";

	echo "<li data-role='fieldcontain' class='ui-field-contain ui-body ui-br ui-li ui-li-static ui-body-c'>
		$cont - Peso $row->peso - <b style='color:#$color'>$row->ejecucion%</b></li>";
}
?>
</u></p>
</div>
