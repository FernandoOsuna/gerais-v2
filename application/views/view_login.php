<?php
$atts=array('data-ajax'=>'false');
$dir='panal'.$panal;
echo form_open($dir,$atts);

$sel=array('id','profesor','contenido','nombre','activo');
$this->db->select($sel);
$this->db->from('curso');
$this->db->where('panal',$panal);
$this->db->limit(1);
$query = $this->db->get();
if ($query->num_rows() > 0){
	$row = $query->row();
	$acins = ($row->activo!='1');
	$id_curso=$row->id;

?>
<div align="right">			
<?php if($acins){ ?>
	<a href="<?php echo site_url('integrantes/registrar/'.$panal.'/create/'.$id_curso) ?>" id='reglink' title='Registarse como nuevo usuario'>Registrarse</a>&nbsp;&nbsp;&nbsp;&nbsp;
			
	<a href="<?php echo site_url('panal'.$panal.'/vincular/') ?>" id='reglink' title='Ya tengo una cuenta RAIS'>Vincular Cuenta</a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php } ?>
</div>
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
		<tr>
		<td></td>
		<td><input type="hidden" name="idc" value="<?php echo $row->id; ?>" /></td>
	</tr>
</table>
<?php echo form_close(); ?>
</p><br />
<div class='jqm-block-content'>
<?php


if ($query->num_rows() > 0){
	echo '<b>'.$row->nombre.'</b>: '.$row->contenido.'<br><b>Prof. '.$row->profesor.'</b>.';
}else{
	echo 'No hay informacion del curso.';
}
?>

<h4>Lista de Compa&ntilde;&iacute;as</h4>
<ul>
<?php
$sel=array('nombre');
$this->db->select($sel);
$this->db->from('compania');
$this->db->where('id_curso',$id_curso);
$this->db->where('semestre',$semestre);
$this->db->order_by('nombre','asc');
$query = $this->db->get();

if ($query->num_rows() > 0){
	foreach ($query->result() as $row){
		echo '<li><b>'.$row->nombre.'</b></li>';
	}
}else{
	echo '<li>Hasta el momento no hay compa&ntilde;&iacute;as registradas en el semestre actual</li>';
}
?>
</ul>


<h4>Lista de productos</h4>

<ul>
<?php
$sel=array('nombre','descripcion');
$this->db->select($sel);
$this->db->from('producto');
$this->db->where('id_curso',$id_curso);
$this->db->order_by('nombre','asc');
$query = $this->db->get();

if ($query->num_rows() > 0){
	foreach ($query->result() as $row){
		echo '<li><b>'.$row->nombre.'</b>:'.$row->descripcion.'</li>';
	}
}else{
	echo '<li>Hasta el momento no hay productos registrados</li>';
}
?>
</ul>

<?php } else{?>
<div align="center">
<table>
	<tr>
	<td></td>
	<td><h1 class='jqm-block-content'> Panal no activo </h1></td>
	<td></td>
	</tr>
	
	
</table>
	
</div>
</div>
<?php }?>
