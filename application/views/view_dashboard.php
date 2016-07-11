<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="es">
<?php
$atts = array(
    'width'      => '800',
    'height'     => '600',
    'scrollbars' => 'yes',
    'status'     => 'yes',
    'resizable'  => 'yes',
	'screenx'    => "'+((screen.availWidth/2)-400)+'",
	'screeny'    => "'+((screen.availHeight/2)-300)+'"
);

$sel=array('a.id AS compania_id','a.nombre AS compania_nombre','c.nombre AS producto_nombre','GROUP_CONCAT(b.nombre ORDER BY b.nombre) AS integran_nombre');
$this->db->select($sel);
$this->db->from('compania AS a');
$this->db->join('integrantes AS b','a.id=b.id_compania AND tipo=\'A\'');
$this->db->join('producto AS c','a.id_producto=c.id','LEFT');
$this->db->group_by('b.id_compania,a.id_producto');
$this->db->order_by('compania_nombre');
$query = $this->db->get();
$resul = $query->result();
$rresul=array();
foreach($resul as $row){
	$row->compania_nombre=anchor_popup('inicio/compromiso/'.$row->compania_id,$row->compania_nombre, $atts);
	$rresul[]=$row;
}
$jsson = json_encode($rresul);
?>
<head>
	<meta charset="utf-8">
	<title>Manejador RAIS</title>

	<style type="text/css">
	body {
	 background-color: #fff;
	 margin: 40px;
	 font-family: Lucida Grande, Verdana, Sans-serif;
	 font-size: 14px;
	 color: #4F5155;
	}
	a {
	 color: #003399;
	 background-color: transparent;
	 font-weight: normal;
	}
	h1 {
	 color: #444;
	 background-color: transparent;
	 border-bottom: 1px solid #D0D0D0;
	 font-size: 16px;
	 font-weight: bold;
	 margin: 24px 0 2px 0;
	 padding: 5px 0 6px 0;
	}
	code {
	 font-family: Monaco, Verdana, Sans-serif;
	 font-size: 12px;
	 background-color: #f9f9f9;
	 border: 1px solid #D0D0D0;
	 color: #002166;
	 display: block;
	 margin: 14px 0 14px 0;
	 padding: 12px 10px 12px 10px;
	}
	</style>
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
	<script type="text/javascript">
	$(function(){
	jQuery("#list4").jqGrid({
	datatype: "local",
	height: 180,
	colNames:['Compa&ntilde;&iacute;a','Producto', 'Integrantres'],
	colModel:[
		{name:'compania_nombre',index:'compania_nombre', width:160},
		{name:'producto_nombre',index:'producto_nombre', width:190},
		{name:'integran_nombre',index:'integran_nombre', width:200 }
	],
	//multiselect: true,
	caption: "Lista de compa&ntilde;&iacute;as"
	});
	var mydata=<?php echo $jsson; ?>;

	for(var i=0;i<=mydata.length;i++)
		jQuery("#list4").jqGrid('addRowData',i+1,mydata[i]);
	});
	</script>
	</head>
<body>
<table width='100%'>
	<tr>
		<td align='center'><?php echo anchor_popup('aucoevaluacion','Autoevaluaci&oacute;n'   ,$atts) ?></td>
		<td align='center'><?php echo anchor_popup('companias'     ,'Compa&ntilde;&iacute;as' ,$atts) ?></td>
		<td align='center'><?php echo anchor_popup('integrantes'   ,'Integrantes'             ,$atts) ?></td>
		<td align='center'><?php echo anchor_popup('producto'      ,'Productos'               ,$atts) ?></td>
		<td align='center'><?php echo anchor_popup('roadmap'       ,'RoadMap'                 ,$atts) ?></td>
	</tr>
</table>
<h1></h1>
<p></p>

<table align='center'>
	<tr>
		<td><?php echo $calendario; ?></td>
		<td>
			<script type="text/javascript">
			swfobject.embedSWF(
			  "<?php echo base_url(); ?>open-flash-chart.swf", "gpie", "220", "220",
			  "9.0.0", "expressInstall.swf",
			  {"data-file":"<?php echo site_url('/inicio/gpie'); ?>"}
			);
			</script>
			<div id="gpie"></div>
			<!-- <img src='http://localhost/astor/assets/default/images/g2.jpg'> -->
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<script type="text/javascript">
			swfobject.embedSWF(
			  "<?php echo base_url(); ?>open-flash-chart.swf", "gprt", "550", "200",
			  "9.0.0", "expressInstall.swf",
			  {"data-file":"<?php echo site_url('/inicio/gconsul'); ?>"}
			);
			</script>
			<div id="gprt"></div>
		</td>
	</tr>
</table>

<center>
<table id="list4" align='center'></table>
</center>
<center>
<p><?php echo anchor_popup('inicio/correos','Envio de correos',$atts);?></p>
<p><?php echo anchor('inicio/cese','Salir');?></p>
</center>

<p align='center'><br />Pagina generada en {elapsed_time} segundos</p>
</body>
</html>
