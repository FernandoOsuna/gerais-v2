<?php
/**
  * Clase para la gestión de tareas
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class tareas extends CI_Controller {
	var $titp='Tarea';
	var $url ='tareas/';

	function index(){
		ci_redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->load->library('rapyd');

		$filter = new datafilter_library();
		$filter->label = 'Tareas';
		$filter->db->select('*');
		$filter->db->from('tareas');

		$filter->field('input','nombre'  ,'Nombre'  )->attributes(array('style' => 'width:170px'));

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new datagrid_library();
		$grid->label = 'Lista de tareas';
		$grid->per_page = 40;
		$grid->cid = '';
		$grid->source($filter);

		$grid->column('tarea','tarea',true)->url($this->url.'/dataedit/show/{id}');

		$grid->add_button(array('url'=>$this->url.'dataedit/create'));
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->head().script('jquery.js');
		$data['title']   = '';
		$this->load->view('view_ven', $data);
	}

/**
  * CRUD para la gestión de las Tareas
  *
  *
  * @return void
  * @param int      $lback 	 Clave primaria de registro en la tabla productos (usado solo para el link de regreso).
  * @param int      $id_comp Clave primaria del compromiso al que pertenece.
  * @param string   $status  Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id      Clave primaria de registro en la tabla tareas.
  */
	function dataeditmobil($lback,$id_comp,$status){

		$this->load->library('rapyd');

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$role = $ut->role();
		$id_int = $ut->id_int();

		if($role == 1){
			if($lback == 0)
				$back='dashboard/gsubcompania/'.$id_comp;
			else if($lback > 0)
				$back='dashboard/compromiso/'.$lback.'/'.$id_comp;
		}else{
			//Chequea que un gerente no pueda gestionar el compromiso de otra compañía
			$sel=array('id_compania');
			$this->db->select($sel);
			$this->db->from('compromisos');
			$this->db->where('id',$id_comp);
			$query = $this->db->get();

			if ($query->num_rows() > 0){
				$rrow = $query->row();
				$compania=$rrow->id_compania;
				$usr_compania=$ut->id_comp();
				if($usr_compania!=$compania) die('Acceso no permitido');
			}else{
				die('Compromiso invalido');
			}

			$back='dashboard/gsubcompalu/'.$id_comp;
			$data['home_url']   = 'dashboard/gcompalu';
		}

		$this->db->where('id', $id_comp);
		$this->db->or_where('control', $id_comp);
		$this->db->where('ejecucion <', 100);
		$this->db->where('fecha >= CURDATE()');
		$this->db->from('compromisos');
		if($this->db->count_all_results()==0){
			//echo $this->db->last_query();
			//die();
			ci_redirect($back);
		}
		$sel =array('a.nombre', 'b.compromiso');
		$this->db->select($sel);
		$this->db->from('producto AS a');
		$this->db->join('compromisos AS b','a.id=b.id_producto','left');
		$this->db->where('b.id',$id_comp);
		$query = $this->db->get();
		$datos=$query->row();

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		//$delete_url = rpd_url_helper::replace('modify' . $edit->cid, 'delete' . $edit->cid);
		//$action = "javascript:window.location.href='" . $delete_url . "'";
		//$edit->button("btn_delete", 'Borrar', $action, "TR");

		$edit->label = $this->titp.' > '.$datos->nombre;
		$edit->back_url = site_url($back);

		$sel=array('a.id',"CONCAT_WS(' ',a.nombre,a.apellido) AS nom");
		$this->db->select($sel);
		$this->db->from('compromisos AS b');
		$this->db->join('compania AS c'   ,'b.id_compania=c.id');		
		$this->db->join('integcurso AS d','c.id=d.id_compania');
		$this->db->join('integrantes AS a','d.id_integrante=a.id');
		$this->db->where('a.tipo','A');
		$this->db->where('b.id',$id_comp);
		$this->db->order_by('a.nombre');
		$query = $this->db->get();

		$opt=array();
		foreach ($query->result() as $row){
			$opt[$row->id]=$row->nom;
		}

		$edit->source('tareas');
		$edit->pre_process(array('delete'), array($this, 'pre_delete'));
		$edit->pre_process(array('update'), array($this, 'pre_update'));

		$edit->field('input','tarea','Tarea')->rule('trim|required');

		if($role ==1 && $lback == 0){
			$edit->field('dropdown','id_integrante','Asignado a')->option('','Seleccionar')->options($opt)->rule='required';
		}
		if($role ==2 ){
			$edit->field('dropdown','id_integrante','Asignado a')->option('','Seleccionar')->options($opt)->rule='required';
		}
		$edit->field('input','peso','Peso')->rule('in_range[1,100]|required');
		$edit->field('hidden','id_compromiso','Compromiso')->insert_value=$id_comp;
		$edit->field('hidden','integ','')->insert_value=$id_int;
		if($role==1 && $lback>0){
			$edit->post_process(array('delete'), array($this, 'post_delete'));
			$edit->post_process(array('update'), array($this, 'post_update'));
		}

		$edit->buttons('modify','save','back','delete');
		$edit->build();
		
		
		$data['content']    = $edit;

		//$query = $this->db->query('SELECT nombre FROM compania JOIN compromisos ON compromisos.id_compania=compania.id JOIN tareas ON tareas.id_compromiso=compromisos.id WHERE tareas.control=1');

		
		//$button = '<a href="'.site_url('tareas/asignar/'.$id.'/'.$lback.'/'.$id_comp).'" data-role="button" data-transition="none">Asignar Responsables</a>';


		$data['back_url']   = $back;
		$data['header']     = 'Gerencia de Compañía ( '.$datos->nombre.' )';
		$data['title']      = 'Ficha de Compañías';
		$data['footer']     = '';
		$data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}

	
/**
  * Función para actualizar los responsables.
  *
  * Despliega la lista de integrantes por compañias para poder asignarlos.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla tareas.
  */
	function asignar($lback,$id_comp,$id){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if(!$rt) return false;

		$role = $ut->role();
		
		$menu= "<div class='content-primary'>";
		
		$sel=array('a.id','a.nombre', 'c.id as id_tarea');
		$this->db->select($sel);
		$this->db->from('companias AS a');		
		$this->db->join('compromisos AS b','b.id_compania=a.id','left');
		$this->db->join('tareas AS c','c.id_compromiso=b.id','left');
		$this->db->where('c.control',$id);
		$query = $this->db->get();
		
		
		$menu.= "</div>";

		$data['content'] = $menu;
		$data['title']   = '';
		$this->load->view('view_ven', $data);
		
	}

/**
  * Pre-proceso antes de actualizar.
  *
  * Evita que se cambie la descripción, el integrante responsable de una tarea y el peso
  * propuesto por el profesor.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla tareas.
  */
	function pre_update($model){
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if(!$rt) return false;

		$role = $ut->role();
		$registro = $model->get('registro');
		if($role!=1 && $registro!='A'){
			$id=$model->pk['id'];
			$this->db->select(array('tarea','id_integrante','peso'));
			$this->db->where('id', $id);
			$this->db->from('tareas');
			$query = $this->db->get();
			$rrow = $query->row();
			if($rrow->id_integrante==0){
				$model->set('id_integrante',$model->get('id_integrante'));
			}
			if($model->get('tarea')!=$rrow->tarea || $model->get('peso')!=$rrow->peso ){
				$model->error_string = '--No se pueden modificar peso, responsable o descripción asignada por Profesor.';
				return false;
			}
				

			//$model->set('compromiso',$rrow->compromiso);
			//$model->set('peso'      ,$rrow->peso);
		}
		else{
			if($role!=1 && $registro!='P'){
				$id=$model->pk['id'];
				$this->db->select(array('tarea','id_integrante','peso'));
				$this->db->where('id', $id);
				$this->db->from('tareas');
				$query = $this->db->get();
				$rrow = $query->row();
				//Evita que se edite el responsable del compromiso sea o no asignado por profesor
				if(!empty($row->id_integrante) || $row->id_integrante==0){
					//$model->set('id_integrante',$rrow->id_integrante);
				}
			}			
		}
		return true;
	}

/**
  * Pre-proceso antes de borrar.
  *
  * Chequea que la tarea no tenga isa y evita
  * que un alumno borre una tarea propuesto por un profesor
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla tareas.
  */
	function pre_delete($model){
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if(!$rt) return false;

		$role = $ut->role();
		if($role!=1){
			$registro = $model->get('registro');

			if($registro!='A'){
				$model->error_string = 'Esta tarea fue propuesto por el profesor, no puede ser borrado por alumnos.';
				return false;
			}
		}

		//Chequea que no tenga informes ISA
		$id=$model->pk['id'];
		$this->db->where('id_tarea', $id);
		$this->db->from('tarearesol');
		if($this->db->count_all_results()==0){
			return true;
		}else{
			$model->error_string = 'No se puede eliminar porque tiene Informes ISA';
			return false;
		}
	}	
	
/**
  * Post-proceso despues de actualizar desde el ambiente productos.
  *
  * Actualiza todos las tareas que comparten el mismo compromiso padre.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function post_update($model){
		$id_control=$model->get('id');
		$peso=$model->get('peso');
		$tarea=$model->get('tarea');

		$data = array(
					'peso' => $peso,
					'tarea'  => $tarea
					);

		$this->db->where('control',$id_control);
		$this->db->update('tareas', $data);

		return true;
	}
	
/**
  * Post-proceso despues de borrar desde el ambiente productos.
  *
  * Borra todos las tareas que comparten el mismo compromiso padre.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla tarea.
  */
	function post_delete($model){
		$id_control=$model->get('id');
		$peso=$model->get('peso');
		$tarea=$model->get('tarea');

		$this->db->where('control',$id_control);
		$this->db->where('tarea',$tarea);
				
		$this->db->delete('tareas');

		return true;
	}
	

}
