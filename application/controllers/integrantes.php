<?php
/**
  * Clase para la gestión de integrantes
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class integrantes extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Integrantes';
/**
 *  Dirección url de la clase.
 */
	var $url ='integrantes/';

	function index(){

	}

/**
  * CRUD de integrantes para usuario administrador y profesor  
  *
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla integrantes.
  */
	function dataedit($status,$id=0){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$role   = $ut->role(1);
		$id_int = $ut->id_int();
		$id_cour= $ut->id_curso();
		if($role!=1 && $id_int!=$id) die('Acceso no permitido');
		//$back='dashboard/gintegrante/'.$id;
		$back='dashboard/integ';
		if($ut->tipo()=='S'){
			$back='panel/resp';
		}
		$semestre=$ut->semestre();

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Integrante';
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
				
		$edit->pre_process(array('insert'), array($this, 'pre_edit_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_edit_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_edit_delete'));
		
		$edit->field('input','nombre','Nombre')
			->rule('trim|required');
		
		$edit->field('input','apellido','Apellido')
			->rule('trim|required');		
	
		$edit->field('input','cedula','Cédula')
			->set_attributes(array('maxlength'=>'50'))
			->rule('numeric|required|unique');

		if($ut->tipo()!='S'){		
			$edit->field('dropdown','tipo','Tipo')
				->options(array('A'=>'Alumno', 'P'=>'Profesor'));
		}
		else if($ut->tipo()=='S'){		
			$edit->field('dropdown','tipo','Tipo')
				->options(array('P'=>'Profesor'));
				
			$sel= array('GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre) AS cursos');			
			$this->db->select($sel);
            $this->db->from('curso AS a');
            $this->db->join('integcurso AS b', 'a.id=b.id_curso', 'left');
            $this->db->where('b.id_integrante',$id);
            
            $query=$this->db->get();
            $row=$query->row();
            if($status!='create' && $status!='insert')	
			$edit->field('input','c_datos','Cursos')->set_attributes(array('readonly' =>'readonly', 'value' => $row->cursos));
		}
		if($ut->tipo()!='S'){
			if($status=='create'){			
			$edit->field('dropdown','id_compania','Compa&ntilde;&iacute;a')
				->option('','Ninguna')
				->options("SELECT id,nombre FROM ${dbprefix}compania WHERE id_curso=$id_cour AND semestre='$semestre' ORDER BY nombre");
			
			$this->db->select(array('seccion'));
			$this->db->from('seccioncurso');
			$this->db->where('id_curso',$id_cour);
			$query = $this->db->get();
			
			foreach ($query->result() as $row){
				$opt[$row->seccion]=$row->seccion;
			}		
						
			$edit->field('dropdown','seccion','Sección')->option('','Ninguna')->options($opt)->rule('required');			
		
			$edit->field('dropdown','cargo','Cargo')->options(array('G'=>'Gerente','D'=>'Director','P'=>'Profesional'))->rule('required')->insert_value='P';
			}
			$edit->field('input','cualidades','Cualidades')
				->rule('trim');
		
			$edit->field('input','hobbies','Pasatiempo')
				->rule('trim');
		}
		$edit->field('input','usuario','Usuario')
			->rule('unique|required|max_length[50]');
			
		$edit->field('input','correo','Correo Electr&oacute;nico')
			->rule('trim|required|valid_email');
		
		if($ut->tipo()!='S'){	
			$edit->field('input','telefono','Tel&eacute;fono')
				->rule('trim|required');
			
			$edit->field('input','twitter','Cuenta twitter')
				->rule('trim')
				->set_attributes(array('maxlength'=>'100'))
				->set_group('Datos personales');
		}
		if($status!='modify' && $status!='show'){
			$edit->field('password','clave' ,'Contrase&ntilde;a')->rule('required|min_length[6]|matches[clave2]')->set_group('Datos de la Cuenta');
			$edit->field('password','clave2','Confirmaci&oacute;n')->rule('required')->set_group('Datos de la Cuenta');
		}
		
		if ($ut->tipo()!='S')
			$edit->field('hidden','id_curso','')->insert_value=$id_cour;

		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de integrante';
		$data['title']      = 'Ficha de integrante';
		if($status!='create' && $status!='insert'){
			$data['footer']     = '<a href="'.site_url('integrantes/ccclave/0/modify/'.$id).'" data-role="button" data-icon="gear" data-direction="reverse">Cambiar clave</a>';
			
			if ($ut->tipo()=='S'){
				$data['footer'] .= '<a href="'.site_url('integrantes/vincularprof/'.$id.'/create/').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar curso</a>';
				
				$this->db->from('integcurso');
				$this->db->where('id_integrante',$id);
				if ($this->db->count_all_results() >0)
				$data['footer'] .= '<a href="'.site_url('integrantes/eliminarvinculo/'.$id).'" data-role="button" data-icon="minus" data-direction="reverse">Eliminar curso</a>';
			
			}
			$this->db->select('*');
			$this->db->from('integcurso');		
			$this->db->where('id_integrante',$id);			
			$this->db->where('id_curso',$id_cour);
			$this->db->limit(1);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();				
				$data['footer']    .= '<a href="'.site_url('integrantes/dataeditcurso/'.$id.'/modify/'.$row->id).'" data-role="button" data-icon="gear" data-direction="reverse">Opciones de curso</a>';
			}
		}
		if ($role==1)
			$data['headerextra'] = 'Profesor: ';
		else if ($role==2)
				$data['headerextra'] = 'Alumno Gerente: ';
			else 
				$data['headerextra'] = 'Alumno: ';
			
		$data['headerextra'].= $ut->user('name');
		if($ut->tipo()=='S'){
			$data['home_url']   = 'panel';
			$data['headerextra']= 'Administrador: '.$ut->user('name');
		}		

		$this->load->view('view_ven', $data);
	}
	
/**
  * CRUD de integrantes para usuario administrador y profesor  
  *
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla integrantes.
  */
	function dataedituser($status,$id=0){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$role   = $ut->role(1);
		$id_int = $ut->id_int();
		$id_cour= $ut->id_curso();
		if($role!=1 || $id_int==$id) die('Acceso no permitido');
		$back='panel/users';

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Integrante';
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
				
		$edit->pre_process(array('insert'), array($this, 'pre_false'));
		$edit->pre_process(array('update'), array($this, 'pre_edit_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_edit_delete'));
		
		$edit->field('input','nombre','Nombre')
			->rule('trim|required');
		
		$edit->field('input','apellido','Apellido')
			->rule('trim|required');		
	
		$edit->field('input','cedula','Cédula')
			->set_attributes(array('maxlength'=>'50'))
			->rule('numeric|required|unique');

		$edit->field('input','usuario','Usuario')
			->rule('unique|required|max_length[50]');
			
		$edit->field('input','correo','Correo Electr&oacute;nico')
			->rule('trim|required|valid_email');

		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de integrante';
		$data['title']      = 'Ficha de integrante';
		if($status!='create' && $status!='insert'){
			$data['footer']     = '<a href="'.site_url('integrantes/ccclave/1/modify/'.$id).'" data-role="button" data-icon="gear" data-direction="reverse">Cambiar clave</a>';
		}
		$data['home_url']   = 'panel';
		$data['headerextra']= 'Administrador: '.$ut->user('name');				

		$this->load->view('view_ven', $data);
	}
	
/**
  * CRUD de integrantes desde el ambiente dashboard 
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla integrantes.
  */
	function dataeditmobil($status,$id=0){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$role   = $ut->role(1);
		$id_int = $ut->id_int();
		$id_cour= $ut->id_curso();
		if($role!=1 && $id_int!=$id) die('Acceso no permitido');
		//$back='dashboard/gintegrante/'.$id;
		$back='dashboard/integ';
		if($ut->tipo()=='S'){
			$back='panel/resp';
		}
		$semestre=$ut->semestre();

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Integrante';
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
				
		$edit->pre_process(array('insert'), array($this, 'pre_edit_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_edit_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_edit_delete'));
		
		$edit->field('input','nombre','Nombre')
			->rule('trim|required');
		
		$edit->field('input','apellido','Apellido')
			->rule('trim|required');		
	
		$edit->field('input','cedula','Cédula')
			->set_attributes(array('maxlength'=>'50'))
			->rule('numeric|required|unique');

		if($ut->tipo()!='S'){		
			$edit->field('dropdown','tipo','Tipo')
				->options(array('A'=>'Alumno', 'P'=>'Profesor'));
		}
		else if($ut->tipo()=='S'){		
			$edit->field('dropdown','tipo','Tipo')
				->options(array('P'=>'Profesor'));
				
			$sel= array('GROUP_CONCAT(a.nombre ORDER BY a.nombre) AS cursos');			
			$this->db->select($sel);
            $this->db->from('curso AS a');
            $this->db->join('integcurso AS b', 'a.id=b.id_curso', 'left');
            $this->db->where('b.id_integrante',$id);
            $query=$this->db->get();
            $row=$query->row();
				
			$edit->field('input','c_datos','Cursos')->set_attributes(array('readonly' =>'readonly', 'value' => $row->cursos));
		}
		if($ut->tipo()!='S'){						
			$edit->field('dropdown','id_compania','Compa&ntilde;&iacute;a')
				->option('','Ninguna')
				->options("SELECT id,nombre FROM ${dbprefix}compania WHERE id_curso=$id_cour  AND semestre='$semestre' ORDER BY nombre");
				
			$this->db->select(array('seccion'));
			$this->db->from('seccioncurso');
			$this->db->where('id_curso',$id_cour);
			$query = $this->db->get();
			
			foreach ($query->result() as $row){
				$opt[$row->seccion]=$row->seccion;
			}		
						
			$edit->field('dropdown','seccion','Sección')->option('','Ninguna')->options($opt)->rule('required');
		
			$edit->field('dropdown','cargo','Cargo')->options(array('G'=>'Gerente','D'=>'Director','P'=>'Profesional'))->rule('required')->insert_value='P';
			
			$edit->field('input','cualidades','Cualidades')
				->rule('trim');
		
			$edit->field('input','hobbies','Pasatiempo')
				->rule('trim');
		}
		$edit->field('input','usuario','Usuario')
			->rule('unique|required|max_length[50]');
			
		$edit->field('input','correo','Correo Electr&oacute;nico')
			->rule('trim|required|valid_email');
		
		if($ut->tipo()!='S'){	
			$edit->field('input','telefono','Tel&eacute;fono')
				->rule('trim|required');
			
			$edit->field('input','twitter','Cuenta twitter')
				->rule('trim')
				->set_attributes(array('maxlength'=>'100'))
				->set_group('Datos personales');
		}
		if($status!='modify' && $status!='show'){
			$edit->field('password','clave' ,'Contrase&ntilde;a')->rule('required|min_length[6]|matches[clave2]')->set_group('Datos de la Cuenta');
			$edit->field('password','clave2','Confirmaci&oacute;n')->rule('required')->set_group('Datos de la Cuenta');
		}
		
		if ($ut->tipo()!='S')
			$edit->field('hidden','id_curso','')->insert_value=$id_cour;
			
		//$edit->post_process(array('insert'), array($this, 'post_insert'));

		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de integrante';
		$data['title']      = 'Ficha de integrante';
		if($status!='create'){
			$data['footer']     = '<a href="'.site_url('integrantes/ccclave/0/modify/'.$id).'" data-role="button" data-icon="gear" data-direction="reverse">Cambiar clave</a>';
			
			if ($ut->tipo()=='S'){
				$data['footer'] .= '<a href="'.site_url('integrantes/vincularprof/'.$id.'/create/').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar curso</a>';
				
				$this->db->from('integcurso');
				$this->db->where('id_integrante',$id);
				if ($this->db->count_all_results() >0)
				$data['footer'] .= '<a href="'.site_url('integrantes/eliminarvinculo/'.$id).'" data-role="button" data-icon="minus" data-direction="reverse">Eliminar curso</a>';
			
			}
			$this->db->select('*');
			$this->db->from('integcurso');		
			$this->db->where('id_integrante',$id);			
			$this->db->where('id_curso',$id_cour);
			$this->db->limit(1);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();				
				$data['footer']    .= '<a href="'.site_url('integrantes/dataeditcurso/'.$id.'/modify/'.$row->id).'" data-role="button" data-icon="gear" data-direction="reverse">Opciones de curso</a>';
			}
		}
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');
		if($ut->tipo()=='S'){
			$data['home_url']   = 'panel';
			$data['headerextra']= 'Administrador: '.$ut->user('name');
		}		

		$this->load->view('view_ven', $data);
	}
	
/**
  * CRUD para los registro de la tabla integcurso
  *
  *
  * @return void
  * @param int      $id_intg Clave primaria de registro en la tabla integrantes.
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla integcurso.
  */
	function dataeditcurso($id_intg,$status,$id){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false || $ut->tipo()=='A') die('Acceso no permitido');
		$role   = $ut->role(1);
		$id_int = $ut->id_int();
		$id_cour= $ut->id_curso();
		//if($role!=1) die('Acceso no permitido');
		if ($id_int!=$id_intg)
			$back='integrantes/dataedit/modify/'.$id_intg;
		if ($id_int==$id_intg)
			$back='integrantes/modif/modify/'.$id_intg;
			
		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		
		$this->db->select(array('nombre'));
		$this->db->from('curso');				
		$this->db->where('id',$id_cour);
		$this->db->limit(1);
		$query = $this->db->get();
		$row= $query->row();
		
		$edit->label = 'Integrante - '.$row->nombre;
		$edit->back_url = site_url($back);

		$edit->source('integcurso');
		$edit->pre_process(array('insert'), array($this, 'pre_false'));
		$edit->pre_process(array('update'), array($this, 'pre_valid_update'));
		
		$edit->field('dropdown','id_compania','Compa&ntilde;&iacute;a')
			->option('','Ninguna')
			->options("SELECT id,nombre FROM ${dbprefix}compania WHERE id_curso=$id_cour ORDER BY nombre")
			->rule('required');
		
		$this->db->select(array('seccion'));
		$this->db->from('seccioncurso');
		$this->db->where('id_curso',$id_cour);
		$query = $this->db->get();
		
		foreach ($query->result() as $row){
			$opt[$row->seccion]=$row->seccion;
		}		
						
		$edit->field('dropdown','seccion','Sección')->options($opt)->rule('required');
		
		$edit->field('dropdown','cargo','Cargo')->options(array('G'=>'Gerente','D'=>'Director','P'=>'Profesional'))->rule('required');
		
		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de integrante';
		$data['title']      = 'Ficha de integrante';

		if ($role==1)
			$data['headerextra'] = 'Profesor: ';
		else if ($role==2)
				$data['headerextra'] = 'Alumno Gerente: ';
			else 
				$data['headerextra'] = 'Alumno: ';
		$data['headerextra'].= $ut->user('name');
		if($ut->tipo()=='S'){
			$data['home_url']   = 'panel';
			$data['headerextra']= 'Administrador: '.$ut->user('name');
		}	
		$this->load->view('view_ven', $data);
	}	
	
/**
  * Edicion de registro de la tabla integcurso al momento de vincular un integrante existente 
  *
  *
  * @return void
  * @param int      $panal   Número de panal del curso, usado para retorno 
  * @param int      $id_cour Clave primaria de registro en la tabla integrantes.
  * @param string   $status  Tipo de acción a ejecutar puede ser create,modify,show,delete.
  */
	function vincular($panal,$id_cour,$status){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$role   = $ut->role();
		$id_int = $ut->id_int();
		
		$back='panal'.$panal;
			
		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;		
				
		$this->db->select(array('nombre','semestre'));
		$this->db->from('curso');				
		$this->db->where('id',$id_cour);
		$this->db->limit(1);
		$query = $this->db->get();
		$row= $query->row();
		
		$semestre=$row->semestre;
		
		$edit->label = 'Registro - '.$row->nombre;
		$edit->back_url = site_url($back);

		$edit->source('integcurso');
		
		$edit->pre_process(array('insert'), array($this, 'pre_vincular_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_registrar_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_registrar_update'));
		
		$edit->field('dropdown','id_compania','Compa&ntilde;&iacute;a')->rule('callback_chcompania')->option('','Crear una nueva')->options("SELECT id,nombre FROM ${dbprefix}compania WHERE id_curso=$id_cour AND semestre='$semestre' ORDER BY nombre");
		$edit->field('input','c_compania','Nombre de la compa&ntilde;&iacute;a: <br>(Solo si selecciono crear una nueva)')
			->rule('callback_chncompania|max_length[200]')
			->set_attributes(array('size'=>'15','maxlength'=>'200','when'=>'create'))
			->when='create';
		
		$this->db->select(array('seccion'));
		$this->db->from('seccioncurso');
		$this->db->where('id_curso',$id_cour);
		$query = $this->db->get();
			
		foreach ($query->result() as $row){
			$opt[$row->seccion]=$row->seccion;
		}		
						
		$edit->field('dropdown','seccion','Sección')->option('','Ninguna')->options($opt)->rule('required');
		
		$edit->field('dropdown','cargo','Cargo')->options(array('G'=>'Gerente','D'=>'Director','P'=>'Profesional'))->rule('required')->insert_value='P';
		
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		$edit->field('hidden','id_integrante','')->insert_value=$id_int;
		$edit->field('hidden','semestre','')->insert_value=$semestre;
		
		$edit->buttons('modify','save','delete');
		$edit->build();

		$data['content']    = $edit;
		//$data['back_url']   = $back;
		$data['header']     = 'Ficha de integrante';
		$data['title']      = 'Ficha de integrante';
		
		$data['home_url']   = 'inicio';
		$data['logout']   = $panal;
		if ($role==1)
			$data['headerextra'] = 'Profesor: ';
		else if ($role==2)
				$data['headerextra'] = 'Alumno Gerente: ';
			else 
				$data['headerextra'] = 'Alumno: ';
		$data['headerextra'].= $ut->user('name');		

		$this->load->view('view_ven', $data);
	}
	
/**
  * Edicion de registro de la tabla integcurso al momento de vincular un integrante existente 
  *
  * @since 2.0
  * @return void
  * @param string   $status  Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id		 Clave primaria de registro en la tabla integrantes.
  * @param int      $idt		 Clave primaria de registro en la tabla integcurso.
  */
	function vincularprof($id,$status,$idt=0){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false || $ut->tipo()!='S') die('Acceso no permitido');
		$role   = $ut->role();
		$id_int = $ut->id_int();
		
		$back='integrantes/dataedit/modify/'.$id;
			
		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		
		$sel=array('nombre','apellido');
		$this->db->select($sel);
		$this->db->from('integrantes');
		$this->db->where('id',$id);
		$query = $this->db->get();
		$row=$query->row();
		
		$edit->label = 'Profesor: '.$row->nombre.' '.$row->apellido;
		$edit->back_url = site_url($back);

		$edit->source('integcurso');
		$edit->pre_process(array('insert'), array($this, 'pre_insert_vinculo'));
		$edit->pre_process(array('delete'), array($this, 'pre_delete_vinculo'));

		$opt=array();
		if ($status=='create' || $status=='insert'){
			$sel=array('id','nombre','panal','semestre');
			$this->db->select($sel);
			$this->db->from('curso');
			$this->db->order_by('nombre');
			$query = $this->db->get();
			
			foreach ($query->result() as $row){
				$opt[$row->id]=$row->nombre;
			}
			
			$sel=array('id_curso');
			$this->db->select($sel);
			$this->db->from('integcurso');
			$this->db->where('id_integrante',$id);
			$query = $this->db->get();
			foreach ($query->result() as $row){
				unset($opt[$row->id_curso]);
			}			
						
			$edit->field('dropdown','id_curso','Curso')->option('','Ninguno')->options($opt)->rule('required');
		}else {
			$sel=array('a.id','a.nombre','a.panal');
			$this->db->select($sel);
			$this->db->from('curso AS a');
			$this->db->join('integcurso AS b', 'a.id=b.id_curso', 'left');
			$this->db->where('b.id_integrante', $id);
			$this->db->order_by('a.nombre');
			$query = $this->db->get();
			
			foreach ($query->result() as $row){
				$opt[$row->id]=$row->nombre;
			}
						
			$edit->field('dropdown','id_curso','Curso')->options($opt)->rule('required');
		}
		$edit->field('hidden','id_integrante','')->insert_value=$id;
		
		$edit->field('hidden','id_compania','')->insert_value = '0';
		
		if ($status=='create')
			$edit->buttons('modify','save','undo','back');
		if ($status=='modify')
			$edit->buttons('modify','back', 'delete');
		if ($status=='delete')
			$edit->buttons('modify','save','undo','back', 'delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de integrante';
		$data['title']      = 'Ficha de integrante';
		
		$data['home_url']   = 'panel';
		$data['headerextra']= 'Administrador: '.$ut->user('name');		

		$this->load->view('view_ven', $data);
	}
	
/**
  * Función para eliminar la vinculación de profesores a cursos
  *
  * @since 2.0
  * @return void
  * @param int      $id		 Clave primaria de registro en la tabla integrantes.
  */
	function eliminarvinculo($id){
		
		$this->load->library('rapyd');

        $ut     = new rpd_auth_library();
        $rt     = $ut->logged(1);
        $id_int = $ut->id_int();
        $id_cour = $ut->id_curso();
        if($rt===false || $ut->tipo()!='S') die('Acceso no permitido');
        
        $sel=array('nombre','apellido');
		$this->db->select($sel);
		$this->db->from('integrantes');
		$this->db->where('id',$id);
		$query = $this->db->get();
		$row=$query->row();
		
		$menu = '<h2>Profesor: '.$row->nombre.' '.$row->apellido.'</h2>';
		$sel= array('b.id','a.nombre','b.semestre');			
		$this->db->select($sel);
        $this->db->from('curso AS a');
        $this->db->join('integcurso AS b', 'a.id=b.id_curso', 'left');
        $this->db->where('b.id_integrante',$id);
        $query=$this->db->get();
        
        $menu.= "<h3>Lista de cursos asociados</h3>";
        $menu.= "<p>Seleccione el registro que desea eliminar. <b>Nota:</b> Al eliminar se elimina todo el historial de dicho semestre.</p>";
        $menu.= "<div class='content-primary'>
        <ul data-role='listview' data-inset='true' data-icon='delete'>";
        foreach ($query->result() as $row){
        	  $menu.= '<li><a href="'.site_url('integrantes/vincularprof/'.$id.'/delete/'.$row->id).'" data-transition="none">'.$row->nombre.' '.$row->semestre.'</a></li>';      	
        }
        $menu.= "</ul></div>";
        $data['content']    = $menu;
        $data['header']     = 'Panel de control';
        $data['title']      = 'Panel de control';
        $data['back_url']       = 'integrantes/dataedit/modify/'.$id;
        $data['usuario']    = $ut->user('name');
        
        $data['home_url']       = 'panel';
        $data['headerextra']= 'Administrador: '.$ut->user('name');
        $this->load->view('view_ven_panel', $data);

        $this->load->view('view_ven', $data);
	}

/**
  * Método para que el usuario cambie la clave de si mismo
  *
  * @since 1.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser solo modify.
  * @param int      $id     Clave primaria de registro en la tabla integrantes.
  */
	function cclave($status,$id){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$id_int=$ut->id_int();
		$role = $ut->role();
		if($rt===false || $id_int!=$id) die('Acceso no permitido');

		$back='integrantes/modif/modify/'.$id;

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Integrante';
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
		$edit->pre_process(array('update'), array($this, 'pre_clave'));
		$edit->pre_process(array('insert'), array($this, 'pre_false'));
		$edit->pre_process(array('delete'), array($this, 'pre_false'));

		$edit->field('password','clavea','Contrase&ntilde;a actual')->rule('required')->set_group('Datos de la Cuenta');
		$edit->field('password','clave1','Contrase&ntilde;a nueva')->rule('required|min_length[6]|matches[clave2]')->set_group('Datos de la Cuenta');
		$edit->field('password','clave2','Confirmaci&oacute;n')->rule('required')->set_group('Datos de la Cuenta');

		$edit->buttons('modify','save','undo','back');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['home_url']   = 'dashboard/gcompalu';
		if ($ut->tipo()=='P'){
			$data['home_url']   = 'dashboard';
		}
		else if ($ut->tipo()=='S'){
			$data['home_url']   = 'panel';
		}
		$data['header']     = 'Cambio de clave';
		$data['title']      = 'Cambio de clave';
		$data['footer']     = '';
		if ($role==1)
			$data['headerextra'] = 'Profesor: ';
		else if ($role==2)
				$data['headerextra'] = 'Alumno Gerente: ';
			else 
				$data['headerextra'] = 'Alumno: ';
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}

/**
  * Método para que el profesor cambie el orden de los integrantes usando campo cargo
  *
  * @since 2.0
  *
  * @return void
  * @param int      $id     Clave primaria de registro en la tabla integrantes para el profesor.
  * @param int      $val    Valor de la pestaña de ordenamiento deseada.
  */
	function cambiarorden($id,$val){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$id_int=$ut->id_int();
		$role = $ut->role();
		$tipo = $ut->tipo();
		
		if($rt===false || $id_int!=$id || $role!=1) die('Acceso no permitido');

		$query = $this->db->query('SELECT id, cargo from integrantes where id ='.$id);
		if($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$data = array('cargo' => $val);
				$this->db->where('id', $id);
				$this->db->update('integrantes', $data);
			}
		}
		if ($tipo == 'S')
			$back = 'panel/users';
		else
			$back = 'dashboard/integ';
		ci_redirect($back);		
	}

/**
  * Método para que el profesor cambie la clave de los alumnos
  *
  * @since 1.0
  *
  * @return void
  * @param int      $l   	Valor de retorno.
  * @param string   $status Tipo de acción a ejecutar puede ser solo modify.
  * @param int      $id     Clave primaria de registro en la tabla integrantes.
  */
	function ccclave($l,$status,$id){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_int=$ut->id_int();
		if($rt===false) die('Acceso no permitido');
		if ($l==1)
			$back='integrantes/dataedituser/modify/'.$id;
		else
			$back='integrantes/dataedit/modify/'.$id;

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Integrante';
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
		$edit->pre_process(array('update'), array($this, 'pre_cod_clave'));
		$edit->pre_process(array('insert'), array($this, 'pre_false'));
		$edit->pre_process(array('delete'), array($this, 'pre_false'));

		$edit->field('password','clave1' ,'Contrase&ntilde;a')->rule('required|min_length[6]|matches[clave2]')->set_group('Datos de la Cuenta');
		$edit->field('password','clave2','Confirmaci&oacute;n')->rule('required')->set_group('Datos de la Cuenta');

		$edit->buttons('modify','save','undo','back');

		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['home_url']   = 'dashboard';
		$data['header']     = 'Cambio de clave';
		$data['title']      = 'Cambio de clave';
		$data['footer']     = '';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');
		if($ut->tipo()=='S'){
			$data['home_url']   = 'panel';
			$data['headerextra']= 'Administrador: '.$ut->user('name');
		}
		$this->load->view('view_ven', $data);
	}
	
/**
  * Pre proceso antes de vincular a un profesor a un curso
  *
  * @since 2.0
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integcurso.
  */
	function pre_insert_vinculo($model){
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) return false;
		$id_int=$ut->id_int();
		$id=$model->pk['id'];
		$id_curso=$model->get('id_curso');
		
		$sel=array('id','nombre','semestre');
		$this->db->select($sel);
		$this->db->from('curso');
		$this->db->where('id',$id_curso);
		$query = $this->db->get();
		if ($query->num_rows()>0){
			$row=$query->row();
			$model->set('semestre',$row->semestre);
		}
		else{
			return false;
		}				

		return true;
	}
	
/**
  * Pre proceso antes de borrar a un profesor de la tabla integcurso.
  * Borra la trayectoria de ese semestre.
  *
  * @since 2.0
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integcurso.
  */
	function pre_delete_vinculo($model){
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) return false;
		$id_int=$ut->id_int();
		$id=$model->pk['id'];
		$id_curso=$model->get('id_curso');
		$semestre=$model->get('semestre'); 
		
		$this->db->where('id_curso',$id_curso);
		$this->db->where('semestre',$semestre);
		$this->db->delete('compromisos');
		
		$this->db->where('id_curso',$id_curso);
		$this->db->where('semestre',$semestre);
		$this->db->delete('aucoevaluacion');
		
		$this->db->where('id_curso',$id_curso);
		$this->db->where('semestre',$semestre);
		$this->db->delete('compania');
		
		$this->db->where('id_curso',$id_curso);
		$this->db->where('semestre',$semestre);
		$this->db->delete('integcurso');
		
		return true;
	}

/**
  * Pre proceso antes de borrar un integrante
  * Borra toda la trayectoria del estudiante
  *
  * @since 1.0
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integrantes.
  */
	function pre_edit_delete($model){
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) return false;
		$id_int=$ut->id_int();
		$id=$model->pk['id'];
		if($id_int==$id){
			$model->error_string='No se puede borrar usted mismo';
			return false;
		}
				
		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		
		$user=$model->get('usuario');
		$phpbb_vars = array("username" => $user);

		$phpbb_result = $phpbb->user_delete($phpbb_vars);

		//Borra la trayectoria
		$this->db->where('id_integrante',$id);
		$this->db->delete('tareas');

		$this->db->where('id_evaluador',$id);
		$this->db->or_where('id_evaluado',$id);
		$this->db->delete('aucoevaluacion_it');

		$this->db->where('id_integrante',$id);
		$this->db->delete('actividades');
		
		$this->db->where('id_integrante',$id);
		$this->db->delete('penalizaciones');
		
		$this->db->where('id_integrante',$id);
		$this->db->delete('integcurso');

		//$cana=0;
		//$this->db->where('id_integrante',$id);
		//$this->db->from('tareas');
		//$cana += $this->db->count_all_results();

		//$this->db->where('id_evaluador',$id);
		//$this->db->or_where('id_evaluado',$id);
		//$this->db->from('aucoevaluacion_it');
		//$cana += $this->db->count_all_results();

		//$this->db->where('id_integrante',$id);
		//$this->db->from('actividades');
		//$cana += $this->db->count_all_results();
		//if($cana>0){
		//	$model->error_string='No se puede borra el integrante porque tiene trayectoria.';
		//	return false;
		//}
		return true;
	}

/**
  * Pre proceso antes de insertar un integrante
  * Aplica la funcion MD5 a la clave
  *
  * @since 1.0
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integrantes.
  */
	function pre_edit_insert($model){
		$this->load->library('phpbb');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$phpbb = new Phpbb();

		$user=$model->get('usuario');
		$pwd=$model->get('clave');
		$correo=$model->get('correo');
		$tipo=$model->get('tipo');		
				
		if($tipo!='S'){		
		    $phpbb_vars = array("username" => $user, "user_password" => $pwd, "user_email" => $correo, "group_id" => "2");
		    $phpbb_result = $phpbb->user_add($phpbb_vars);
	    }	
		
        $clave=md5($model->get('clave'));
		$model->set('clave',$clave);
		$this->pre_edit_update($model);		
						
		$cargo       = $model->get('cargo');
		$id_compania = $model->get('id_compania');
		$seccion     = $model->get('seccion');
		//$id_integ    = $model->pk['id'];
		$id_curso	 = $model->get('id_curso');
		
		if ($model->get('tipo')=='P')
			$id_compania=0;
		if ($ut->tipo()!='S'){
			$myTable = "integrantes";
			$this->db->trans_start();
			$result = mysql_query("SHOW TABLE STATUS");
			while ($row = mysql_fetch_assoc($result)) {
			  if ($row['Name'] == $myTable) {
			    $id_integ = $row['Auto_increment'];
			    break;
			  }
			}		
			
			$datos = array('id_integrante' => $id_integ,
							'id_compania'  => $id_compania,
							'id_curso'	   => $id_curso,
							'seccion'	   => $seccion,
							'cargo'		   => $cargo);
			$this->db->insert('integcurso', $datos);
			$this->db->trans_complete();
		}
		return true;
	}

/**
  * Pre proceso antes de modificar un integrante
  * Si el cargo es de gerente o director destituye
  * al integrante que tenia el cargo anterior
  *
  * @since 1.0
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integrantes.
  */
	function pre_edit_update($model){
		
		$cargo       = $model->get('cargo');
		$id_compania = $model->get('id_compania');
		$tipo        = $model->get('tipo');
		$id          = $model->pk['id'];		
		
		if($tipo=='A'){
			if($cargo=='G' || $cargo=='D'){
				$data = array( 'cargo' => 'P' );

				$this->db->where('id_compania' , $id_compania);
				$this->db->where('cargo'       , $cargo);
				$this->db->where('id_integrante <>'       , $id);
				$this->db->update('integcurso', $data);
			}
		}				
		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		$user=$model->get('usuario');
		$correo=$model->get('correo');
						
		$phpbb_vars = array("user_id" => $id, "username" => $user, "user_email" => $correo);
		$phpbb_result = $phpbb->user_update($phpbb_vars);
		
		return true;
	}

/**
  * Método que permite el registro de integrantes
  *
  * @since 1.0
  *
  * @return void
  * @param int      $panal  Numero de panal, usado para retorno.
  * @param string   $status Tipo de acción a ejecutar puede ser solo modify.
  * @param int      $id     Clave primaria del curso que se asignará al integrante.
  */
	function registrar($panal,$status,$id=0){
		$sel=array('activo','panal','semestre');
		$this->db->select($sel);
		$this->db->from('curso');		
		$this->db->where('panal',$panal);
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row = $query->row();
			$dir = $row->panal;
			$semestre = $row->semestre;
			$acins = $row->activo;
		}
		if($acins) die('Acceso no autorizado.');

		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Registro de integrantes';
		
		$back ='panal'.$panal;
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
		$edit->pre_process(array('insert'), array($this, 'pre_registrar_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_registrar_update'));
		$edit->field('input','nombre','Nombre')
			->rule('ucwords|trim|required')
			->set_attributes(array('maxlength'=>'100'))
			->set_group('Datos personales');
		$edit->field('input','apellido','Apellido')
			->set_attributes(array('maxlength'=>'100'))
			->rule('ucwords|trim|required')
			->set_group('Datos personales');
		$edit->field('input','cedula','Cédula')
			->set_attributes(array('maxlength'=>'50'))
			->rule('numeric|required|unique')
			->set_group('Datos personales');
		$edit->field('input','cualidades','Cualidades o virtudes')
			->set_attributes(array('maxlength'=>'100'))
			->rule('trim')
			->set_group('Datos personales');
		$edit->field('input','hobbies','Pasatiempo')
			->set_attributes(array('maxlength'=>'100'))
			->rule('trim')
			->set_group('Datos personales');
		$edit->field('input','telefono','Tel&eacute;fono')
			->rule('trim|required|phone')
			->set_attributes(array('maxlength'=>'100'))
			->set_group('Datos personales');
		$edit->field('input','twitter','Cuenta twitter')
			->rule('trim')
			->set_attributes(array('maxlength'=>'100'))
			->set_group('Datos personales');
		$edit->field('dropdown','id_compania','Compa&ntilde;&iacute;a')->rule('callback_chcompania')->option('','Crear una nueva')->options("SELECT id,nombre FROM ${dbprefix}compania WHERE id_curso=$id AND semestre='$semestre' ORDER BY nombre")->set_group('Datos corporativos');
		$edit->field('input','c_compania','Nombre de la compa&ntilde;&iacute;a: <br>(Solo si selecciono crear una nueva)')
			->rule('callback_chncompania|max_length[200]')
			->set_group('Datos corporativos')
			->set_attributes(array('size'=>'15','maxlength'=>'200','when'=>'create'))
			->when='create';
		$this->db->select(array('seccion'));
		$this->db->from('seccioncurso');
		$this->db->where('id_curso',$id);
		$query = $this->db->get();
			
		foreach ($query->result() as $row){
			$opt[$row->seccion]=$row->seccion;
		}		
						
		$edit->field('dropdown','seccion','Sección')->option('','Ninguna')->options($opt)->rule('required');
		$edit->field('dropdown','cargo','Cargo')->options(array('G'=>'Gerente','D'=>'Director','P'=>'Profesional'))->set_group('Datos corporativos')->insert_value='P';
		$edit->field('input','correo','Correo Electr&oacute;nico')
			->rule('trim|required|valid_email|max_length[100]')
			->set_attributes(array('maxlength'=>'100'))
			->set_group('Datos de la Cuenta');
		$edit->field('input','usuario','Usuario')
			->rule('required|unique|alpha_dash|max_length[50]')
			->set_attributes(array('maxlength'=>'50'))
			->set_group('Datos de la Cuenta');
		if($status!='show'){
			$edit->field('password','clave1','Contrase&ntilde;a')->rule('required|min_length[6]|matches[clave2]')->set_group('Datos de la Cuenta');
			$edit->field('password','clave2','Confirmaci&oacute;n')->rule('required')->set_group('Datos de la Cuenta');
		}
		//$edit->field('captcha'  ,'verifica','');
		$edit->field('hidden','id_curso','')->insert_value=$id;
		$edit->field('hidden','semestre','')->insert_value=$semestre;

		$edit->buttons('save','undo','back');

		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['home_url']   = $back;
		$data['header']     = 'Gerencia de Compa&ntilde;&iacute;a';
		$data['title']      = 'Ficha de Compa&ntilde;&iacute;as';
		$data['footer']     = '';
		$this->load->view('view_ven', $data);
	}

/**
  * Valida el nombre de una compañía
  *
  * @since 1.0
  *
  * @return boolean
  * @param string    $str  Nombre de la compañía.
  */
	public function chncompania($str){
		if(empty($str)) return true;
		$this->db->where('nombre', trim($str));
		$this->db->from('compania');
		$cana=$this->db->count_all_results();

		if ($cana > 0) {
			//echo 'Ya existe una compa&ntilde;ia con el nombre propuesto, por favor elija otro.';
			return false;
		} else {
			return true;
		}
	}

/**
  * Valida la elección de una compañía
  *
  * @since 1.0
  *
  * @return boolean
  * @param string    $str  Clave primaria de la compañía.
  */
	public function chcompania($str){
		if(empty($str)){
			$ccomp=isset($_POST['c_compania'])? $_POST['c_compania'] : '';
			if(empty($ccomp)){
				//echo 'Si no elije una compa&ntilde;ia debe proponer una.';
				return false;
			}
		}
		return true;
	}

/**
  * Pre proceso para prohibir acciones indebidas
  *
  * @since 1.0
  *
  * @return boolean
  * @param object     $model  Modelo de la tabla integrantes.
  */
	function pre_registrar_update($model){
		$model->error_string = 'Acción prohibida';
		return false;
	}

/**
  * Pre proceso para validar o insertar una nueva compañía al momento de registrarse
  *
  * @since 1.0
  *
  * @return boolean
  * @param object     $model  Modelo de la tabla integrantes.
  */
	function pre_registrar_insert($model){
		$model->set('tipo','A');
		$id_compania= $model->get('id_compania');
		$c_compania = $_POST['c_compania'];
		$id_curso=$model->get('id_curso');
		
		$sel=array('semestre');
		$this->db->select($sel);
		$this->db->from('curso');		
		$this->db->where('id',$id_curso);
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row = $query->row();
			$semestre = $row->semestre;
		}
		if(!$this->chncompania($c_compania)){
			$model->error_string = 'Ya existe una compañía con el nombre propuesto, por favor elija otro.';
			return false;
		}

		if(!$this->chcompania($id_compania)){
			$model->error_string = 'Si no elije una compa&ntilde;ia debe proponer una.';
			return false;
		}

		if(empty($id_compania)){
			if(!empty($c_compania)){
				$data = array('nombre'   => trim($c_compania),
							  'id_curso' => $id_curso ,
							  'semestre' => $semestre);
				$this->db->insert('compania', $data);
				$id_compania=$this->db->insert_id();
				$model->set('id_compania',$id_compania);
			}
		}
		$id_compania = $model->get('id_compania');
		if(!empty($id_compania)){
			$cargo       = $model->get('cargo');
			if($cargo=='D' || $cargo=='G'){
				$this->db->where('id_compania', $id_compania);
				$this->db->where('cargo'      , $cargo);
				$this->db->where('id_curso'      , $id_curso);
				$this->db->from('integcurso');
				if($this->db->count_all_results()>0){
					$model->error_string = 'Ya existe un integrante con ese cargo.';
					return false;
				}
			}
		}
		$clave=$_POST['clave1'];
		$model->set('clave',md5($clave));
		$model->rm('c_compania');
		
		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		$user=$model->get('usuario');
		$correo=$model->get('correo');
		$phpbb_vars = array("username" => $user, "user_password" => $clave, "user_email" => $correo, "group_id" => "2");
		$phpbb_result = $phpbb->user_add($phpbb_vars);
		
		$cargo       = $model->get('cargo');
		$seccion     = $model->get('seccion');
		
		if ($model->get('tipo')=='S' || $model->get('tipo')=='P')
			$id_compania=0;
			
		$myTable = "integrantes";
		$this->db->trans_start();
		$result = mysql_query("SHOW TABLE STATUS");
		while ($row = mysql_fetch_assoc($result)) {
		  if ($row['Name'] == $myTable) {
		    $id_integ = $row['Auto_increment'];
		    break;
		  }
		}
				
		$datos = array('id_integrante' => $id_integ,
						'id_compania'  => $id_compania,
						'id_curso'	   => $id_curso,
						'seccion'	   => $seccion,
						'cargo'		   => $cargo,
						'semestre'	   => $semestre );
		$this->db->insert('integcurso', $datos);
		
		$this->db->trans_complete();
		return true;
	}	
	
/**
  * Pre-proceso de la tabla integcurso
  * Valida que se seleccione una compañia o el nombre en caso de proponer una
  * Valida que el cargo de Gerente o director en la compañia no lo tenga otro integrante
  * 
  * @return boolean
  * @param object     $model  Modelo de la tabla integcurso.
  */
	function pre_vincular_insert($model){
		
		$id_compania= $model->get('id_compania');
		$c_compania = $_POST['c_compania'];
		$semestre = $model->get('semestre');
		$id_curso=$model->get('id_curso');
		if(!$this->chncompania($c_compania)){
			$model->error_string = 'Ya existe una compañía con el nombre propuesto, por favor elija otro.';
			return false;
		}

		if(!$this->chcompania($id_compania)){
			$model->error_string = 'Si no elije una compa&ntilde;ia debes proponer una.';
			return false;
		}

		if(empty($id_compania)){
			if(!empty($c_compania)){
				$data = array('nombre'   => trim($c_compania),
							  'id_curso' => $id_curso,
							  'semestre' => $semestre);
				$this->db->insert('compania', $data);
				$id_compania=$this->db->insert_id();
				$model->set('id_compania',$id_compania);
			}
		}
		$id_compania = $model->get('id_compania');
		if(!empty($id_compania)){
			$cargo       = $model->get('cargo');
			if($cargo=='D' || $cargo=='G'){
				$this->db->where('id_compania', $id_compania);
				$this->db->where('cargo'      , $cargo);
				$this->db->where('id_curso'      , $id_curso);
				$this->db->from('integcurso');
				if($this->db->count_all_results()>0){
					$model->error_string = 'Ya existe un integrante con ese cargo.';
					return false;
				}
			}
		}
		return true;
	}	
		
/**
  * Pre-proceso de la tabla integcurso
  * Valida que el cargo de Gerente o director en la compañia no lo tenga otro integrante
  *
  * @return boolean
  * @param object     $model  Modelo de la tabla integcurso.
  */
	function pre_valid_update($model){
		
		$id_compania= $model->get('id_compania');
		$id_curso=$model->get('id_curso');
		if(!$this->chncompania($c_compania)){
			$model->error_string = 'Ya existe una compañía con el nombre propuesto, por favor elija otro.';
			return false;
		}

		if(!empty($id_compania)){
			$cargo       = $model->get('cargo');
			if($cargo=='D' || $cargo=='G'){
				$this->db->where('id_compania', $id_compania);
				$this->db->where('cargo'      , $cargo);
				$this->db->where('id_curso'      , $id_curso);
				$this->db->from('integcurso');
				if($this->db->count_all_results()>0){
					$model->error_string = 'Ya existe un integrante con ese cargo.';
					return false;
				}
			}
		}
		return true;
	}

/**
  * Metodo para gestionar la cuenta propia de usuario
  *
  * @since 1.0
  *
  * @return boolean
  * @param object     $model  Modelo de la tabla integrantes.
  */
	function modif($status,$id){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt    = $ut->logged();
		$id_int= $ut->id_int();
		$id_cour= $ut->id_curso();
		if($rt===false || $id_int!=$id) die('Acceso no permitido');
		$role = $ut->role();
		if($role == 1 && $ut->tipo()=='S'){
			$back='panel';
			$data['home_url']   = $back;
		}
		if($role == 1 && $ut->tipo()=='P'){
			$back='dashboard';
			$data['home_url']   = $back;
		}
		if($role != 1 && $ut->tipo()=='A'){
			$back='dashboard/gcompalu';
			$data['home_url']   = $back;
		}

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->validation->set_message('clavesan','Clave anterior inv&aacute;lida');
		$edit->label = 'Integrante';
		if($ut->tipo()=='S'){
			$edit->label = '';
		}
		$edit->back_url = site_url($back);

		$edit->source('integrantes');
		$edit->pre_process(array('insert'), array($this, 'pre_false'));
		$edit->pre_process(array('delete'), array($this, 'pre_false'));

		$edit->field('input','nombre','Nombre')->rule('ucwords|trim|required')->set_group('Datos personales');
		$edit->field('input','apellido','Apellido')->rule('ucwords|trim|required')->set_group('Datos personales');
		/*if($ut->tipo()!='P' && $ut->tipo()!='S'){
			$edit->field('input','seccion','Sección')
				->set_attributes(array('maxlength'=>'5'))
				->rule('required')
				->set_group('Datos personales');
		}*/
		
		if($ut->tipo()!='S'){
			$edit->field('input','cedula','Cédula')
				->set_attributes(array('maxlength'=>'50'))
				->rule('numeric|required|unique')
				->set_group('Datos personales');
	
			$edit->field('input','cualidades','Cualidades o virtudes')->rule('trim')->set_group('Datos personales');
			$edit->field('input','hobbies','Pasatiempo')->rule('trim')->set_group('Datos personales');
			$edit->field('input','twitter','Cuenta twitter')->rule('trim')->set_group('Datos personales');
		}
		$edit->field('input','telefono','Tel&eacute;fono')->rule('trim|required')->set_group('Datos personales');
		$edit->field('input','correo','Correo Electr&oacute;nico')->rule('trim|required|valid_email')->set_group('Datos de la Cuenta');
		$edit->field('input','usuario','Usuario')->rule('required|unique|alpha_dash|max_length[50]')->set_group('Datos de la Cuenta')->mode='autohide';

		$edit->post_process(array('update'), array($this, 'post_update'));
		
		$edit->buttons('save','undo','modify','back');

		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Gerencia de Compa&ntilde;&iacute;a';
		$data['title']      = 'Ficha de Compa&ntilde;&iacute;as';
		$data['footer']     = '<a href="'.site_url('integrantes/cclave/modify/'.$id).'" data-role="button" data-icon="gear" data-direction="reverse">Cambiar clave</a>';
		if($ut->tipo()=='P'){
			$data['footer'].= '<a href="'.site_url('curso/dataeditmobil/modify/'.$id_cour).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar Curso</a>';
		}
		else if($ut->tipo()=='S'){							
			$this->db->select('*');
			$this->db->from('integcurso');		
			$this->db->where('id_integrante',$id);			
			$this->db->where('id_curso',$id_cour);
			$this->db->limit(1);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();				
				$data['footer']    .= '<a href="'.site_url('integrantes/dataeditcurso/'.$id.'/modify/'.$row->id).'" data-role="button" data-icon="gear" data-direction="reverse">Opciones de curso</a>';
			}
		}
		$data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
		$data['headerextra'].= $ut->user('name');
		if($ut->tipo()=='S'){
			$data['header']     = 'Configuración de cuenta';
			$data['title']      = 'Configuración de cuenta';
			$data['headerextra'] = 'Administrador: '.$ut->user('name');
		}

		$this->load->view('view_ven', $data);
	}

/**
  * Pre proceso para evitar acciones prohibidas
  *
  * @since 1.0
  *
  * @return boolean
  * @param string   $clave Clave anterior.
  * @param object   $model Modelo de la tabla integrantes.
  */
	function pre_false($model){
		$model->error_string = 'Acción prohibida';
		return false;
	}

/**
  * Pre-proceso que encripta la clave antes de guardarla
  * sin validación de la clave anterior
  *
  * @since 1.0
  *
  * @return boolean
  * @param object     $model  Modelo de la tabla integrantes.
  */
	function pre_cod_clave($model){
		$clave=$_POST['clave1'];

		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		$user=$model->get('usuario');
		$pwd=$clave;
		
		$phpbb_vars = array("username" => $user, "password" => $pwd);
		$phpbb_result = $phpbb->user_change_password($phpbb_vars);		
		
		$model->set('clave',md5($clave));
		return true;
	}

/**
  * Pre-proceso que encripta la clave antes de guardarla
  * con validación de la clave anterior
  *
  * @since 1.0
  *
  * @return boolean
  * @param object     $model  Modelo de la tabla integrantes.
  */
	function pre_clave($model){
		$clave = $_POST['clavea'];		
				
		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		$user=$model->get('usuario');
		
		if($this->chclavesan($clave,$model)){
			$clave=$_POST['clave1'];
			$pwd=$clave;
			$model->set('clave',md5($clave));
			
			$phpbb_vars = array("username" => $user, "password" => $pwd);
			$phpbb_result = $phpbb->user_change_password($phpbb_vars);
			
			return true;
		}else{

			$model->error_string = '--La Clave anterior no coincide con la registrada.';
			return false;
		}
	}

/**
  * Validación de la clave
  * Chequea que la clave anterior sea correcta
  *
  * @since 1.0
  *
  * @return boolean
  * @param string   $clave Clave anterior.
  * @param object   $model Modelo de la tabla integrantes.
  */
	function chclavesan($clave,&$model){
		$sel=array('clave');
		$this->db->select($sel);
		$this->db->from('integrantes');
		$this->db->where('id',$model->pk['id']);
		$query = $this->db->get();
		$row   = $query->row();
		echo $row->clave.'=='.md5($clave);
		if($row->clave==md5($clave)){
			return true;
		}
		return false;
	}
	
/**
  * Post proceso despues de actualizar
  * Actualiza los datos en el foro
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integrantes.
  */
	function post_update($model){
		
		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		
		$user=$model->get('usuario');
		$correo=$model->get('correo');
		$phpbb_vars = array("username" => $user, "user_email" => $correo);
		$phpbb_result = $phpbb->user_update($phpbb_vars);
		
		return true;
	}
	
/**
  * Post proceso despues de insertar
  * Asigna los valores correspondientes en la tabla integcurso
  *
  * @return boolean
  * @param object   $model Modelo de la tabla integrantes.
  */
	function post_insert($model){
				
		$cargo       = $model->get('cargo');
		$id_compania = $model->get('id_compania');
		$seccion     = $model->get('seccion');
		//$id_integ    = $model->pk['id'];
		$id_curso	 = $model->get('id_curso');
		
		if ($model->get('tipo')=='S' || $model->get('tipo')=='P')
			$id_compania=0;
			
		$myTable = "integrantes";
		$this->db->trans_start();
		$result = mysql_query("SHOW TABLE STATUS");
		while ($row = mysql_fetch_assoc($result)) {
		  if ($row['Name'] == $myTable) {
		    $id_integ = $row['Auto_increment'];
		    break;
		  }
		}		
		$datos = array('id_integrante' => $id_integ,
						'id_compania'  => $id_compania,
						'id_curso'	   => $id_curso,
						'seccion'	   => $seccion,
						'cargo'		   => $cargo);
		$this->db->insert('integcurso', $datos);
		$this->db->trans_complete();
		
		return true;
	}


}
