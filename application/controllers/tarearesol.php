<?php
/**
  * Clase para la gestión de las isas de las tareas
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class tarearesol extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Tareas';
/**
 *  Dirección url de la clase.
 */
	var $url ='tarearesol/';

	function index(){
		ci_redirect($this->url.'filteredgrid');
	}

/**
  * CRUD para gestión de los informes ISA
  *
  *
  * @return void
  * @param int      $id_subcomp Clave primaria de la tarea a la que pertenece
  * @param string   $status     Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id         Clave primaria de registro en la tabla tarearesol.
  */
	function dataeditmobil($id_subcomp,$status,$id=0){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$role = $ut->role();
		
		$back= 'dashboard/gcompalu';
		$home= 'dashboard/gcompalu';

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		//Saca el promedio de avance
		$sel=array('tarea','id_integrante');
		$this->db->select($sel);
		$this->db->from('tareas');
		$this->db->where('id',$id_subcomp);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row   = $query->row();
			$compromiso=$row->tarea;
			$id_integrante=$row->id_integrante;
		}else{
			die('Tarea inválida');
		}

		$edit->label = "Tarea: ".$compromiso;
				
		$edit->back_url = site_url('dashboard/gcompalu');
		
		if ($role==1){
					
			$edit->back_url = site_url('dashboard/gintegrante/'.$id_integrante);
			
			$back= 'dashboard/gintegrante/'.$id_integrante;
			$home= 'dashboard';
		}


		$edit->source('tarearesol');
		$edit->pre_process(array('insert'), array($this, 'pre_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_false'));

		$edit->field('textarea','hizo'    ,'Que hizo?')->rule('required');
		$edit->field('hidden'  ,'id_tarea','')->insert_value=$id_subcomp;

		$edit->buttons('modify','save','undo','back');

		$edit->build();

		//$msj='<p>Solo es permitido un registro de resultados por compromiso, asi que asegurese de colocar la informacion correcta.</p>';
		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['home_url']   = $home;
		$data['header']     = 'Informe final de resultados';
		$data['title']      = 'Informe final de resultados';
		$data['footer']     = '';
		$data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}

/**
  * Visor para los informes ISA
  *
  *
  * @return void
  * @param int      $lback 		Clave primaria de la compania a la que pertenece, usado para retorno.
  * @param int      $id_subcomp Clave primaria de la tarea al que pertenece.
  * @param string   $status     Tipo de acción a ejecutar debe ser show.
  * @param int      $id         Clave primaria de registro en la tabla tarearesol.
  */
	function datashow($lback,$l,$id_subcomp,$status,$id){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$role=$ut->role();
		$id_int = $ut->id_int();
		if($rt===false) die('Acceso no permitido');

		//Saca el promedio de avance

		$sel=array('a.id' ,'a.tarea', 'a.id_integrante', 'b.id','CONCAT_WS(\' \',b.nombre,b.apellido) AS nombre');
		$this->db->select($sel);
		$this->db->from('tareas AS a');		
		$this->db->join('integrantes AS b', 'a.id_integrante=b.id','left');
		$this->db->where('a.id',$id_subcomp);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row   = $query->row();
			$compromiso=$row->tarea;
			$nombre=$row->nombre;
			$id_autor=$row->id_integrante;
		}else{
			die('Tarea inválida');
		}
		$back ='dashboard/gcompalu';
		$home ='dashboard/gcompalu';
		if ($role==1){
			$back ='dashboard/gcompania/'.$lback;
			$home ='dashboard';
			if ($l>0){
				$back ='dashboard/gintegrante/'.$id_autor;
				if ($l>1){
					$back ='dashboard/gsubcompania/'.$lback;					
				}
			}
		}

		//Saca el contenido del compromiso
		$sel=array('id','hizo','id_tarea');
		$this->db->select($sel);
		$this->db->from('tarearesol');
		$this->db->where('id',$id);
		$this->db->where('id_tarea',$id_subcomp);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row   = $query->row();

			$edit = '<h2>Tarea: '.$compromiso.'</h2>';			
			$edit.= '<h3>Autor: '.$nombre.'</h3>';
			$edit.= '<h3>¿Qué Hizo? </h3> <hr>';

			$edit.= '<div>';
			$edit.= $row->hizo;
			$edit.= '</div>';
		}
		else
			$edit = '<h2>Tarea: No tiene</h2>';
			
		$sel=array('id','estricto');
		$this->db->select($sel);
		$this->db->from('curso');
		$this->db->where('id',$ut->id_curso());
		$query = $this->db->get();
		$est = $query->row();

		$data['content']  = $edit;
		$data['back_url'] = $back;
		$data['home_url'] = $home;
		$data['header']   = 'Informe final de resultados';
		$data['title']    = 'Informe final de resultados';
		if ($id_autor==$id_int || $role==1)
			if ($role==1 || $est->estricto!=1)
				$data['footer']   = '<a href="'.site_url('/tarearesol/dataeditmobil/'.$row->id_tarea.'/modify/'.$row->id).'" data-role="button" data-icon="gear" data-direction="reverse">Editar</a>';

		$this->load->view('view_simple_ven', $data);
	}

/**
  * Pre-Proceso
  *
  * Evita acciones no permitidas en el CRUD
  *
  * @return boolean
  * @param object    $model  Modelo de la tabla tarearesol.
  */
	function pre_false($model){
		$model->error_string='Acción no permitida';
		return false;
	}

	function pre_update($model){
		$id=$model->get('id_tarea');

		$sel=array('b.fecha','c.estricto');
		$this->db->select($sel);
		$this->db->from('tareas AS a');
		$this->db->join('compromisos AS b','a.id_compromiso=b.id');
		$this->db->join('curso AS c','b.id_curso=c.id');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row   = $query->row();
			$date_comp = date_create($row->fecha);
			$date_now  = new DateTime();
			$date_comp->add(new DateInterval('PT7H'));
			$hasta = $date_comp->format('d/m/Y h:i:s');
			if($date_now>=$date_comp && $row->estricto == 1){
				$model->error_string = '--El perído de registro ha caducado. ';
				return false;
			}
		}else{
			$model->error_string = 'Tarea inválida';
			return false;
		}
		return true;
	}

/**
  * Pre-Proceso antes de insertar
  *
  * Asigna el numero de reasignaciones en el informe ISA y valida que el
  * compromiso no este vencido
  *
  * @return boolean
  * @param object    $model  Modelo de la tabla tarearesol.
  */
	function pre_insert($model){
		$id=$model->get('id_tarea');

		$sel=array('b.fecha','b.reasignado', 'c.estricto');
		$this->db->select($sel);
		$this->db->from('tareas AS a');
		$this->db->join('compromisos AS b','a.id_compromiso=b.id');
		$this->db->join('curso AS c','b.id_curso=c.id');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row   = $query->row();
			$date_comp = date_create($row->fecha);
			$date_comp->setTime(0, 0);
			$date_now  = new DateTime();
			$date_now->setTime(0, 0);
			$date_comp->add(new DateInterval('PT7H'));
			$hasta = $date_comp->format('d/m/Y h:i:s');
			if($date_now>$date_comp && $row->estricto == 1){
				$model->error_string = '--El perído de registro ha caducado, ya no puede registar el ISA.';
				return false;
			}
			$reasignado = $row->reasignado;
		}else{
			$model->error_string = 'Tarea inválida';
			return false;
		}

		$model->set('reasignacion', $reasignado);
		return true;
	}
}
