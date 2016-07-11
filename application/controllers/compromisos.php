<?php
/**
  * Clase para la gestión de compromisos
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class compromisos extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Compromiso';
/**
 *  Dirección url de la clase.
 */
	var $url ='compromisos/';

	function index(){
	}

/**
  * CRUD para gestionar los compromisos.
  *
  * @since 1.0
  *
  * @return void
  * @param int      $lback 		Clave primaria de registro en la tabla productos (usado solo para el link de regreso).
  * @param string   $status 	Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id_comp    Clave primaria de registro en la tabla compromisos.
  */
	function dataedit($status,$id_comp){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_cour = $ut->id_curso();
		if($rt===false) die('Acceso no permitido');

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		//$delete_url = rpd_url_helper::replace('modify' . $edit->cid, 'delete' . $edit->cid);
		//$action = "javascript:window.location.href='" . $delete_url . "'";
		//$edit->button("btn_delete", 'Borrar', $action, "TR");
		$this->db->select(array('a.nombre', 'b.compromiso'));
        $this->db->from('producto AS a');
        $this->db->join('compromisos AS b', 'a.id=b.id_producto', 'left');
        $this->db->where('b.id',$id_comp);
        $query = $this->db->get();
        $producto= $query->row();

		$edit->label = $this->titp;

		$back='/dashboard/gsubcompania/'.$id_comp;
		
		$edit->back_url = site_url($back);

		$edit->source('compromisos');
		$edit->pre_process(array('insert'), array($this, 'pre_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_delete'));
		$edit->field('date','fecha','Fecha')->rule('trim|required');
		$edit->field('input','compromiso','Compromiso')->rule('trim|required');
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		
		$edit->buttons('modify','save','back','undo','delete');

		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de Compromiso ( '.$producto->nombre.' )';
		$data['title']      = 'Ficha de Compromiso ( '.$producto->nombre.' )';
		$data['footer']     = '';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}
	
/**
  * CRUD para gestionar los compromisos.
  *
  * @since 1.0
  *
  * @return void
  * @param int      $lback 		Clave primaria de registro en la tabla productos (usado solo para el link de regreso).
  * @param string   $status 	Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id_comp    Clave primaria de registro en la tabla compromisos.
  */
	function dataeditmobil($lback,$status,$id_comp){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_cour = $ut->id_curso();
		if($rt===false) die('Acceso no permitido');

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		//$delete_url = rpd_url_helper::replace('modify' . $edit->cid, 'delete' . $edit->cid);
		//$action = "javascript:window.location.href='" . $delete_url . "'";
		//$edit->button("btn_delete", 'Borrar', $action, "TR");
		$this->db->select(array('a.nombre', 'b.compromiso'));
        $this->db->from('producto AS a');
        $this->db->join('compromisos AS b', 'a.id=b.id_producto', 'left');
        $this->db->where('b.id',$id_comp);
        $query = $this->db->get();
        $producto= $query->row();

		$edit->label = $this->titp;

		$back='dashboard/producto/'.$lback;		
		
		$edit->back_url = site_url($back);

		$edit->source('compromisos');
		$edit->pre_process(array('insert'), array($this, 'pre_insert'));
		$edit->pre_process(array('update'), array($this, 'post_update'));
		$edit->pre_process(array('delete'), array($this, 'post_delete'));
		$edit->field('date','fecha','Fecha')->rule('trim|required');
		$edit->field('input','compromiso','Compromiso')->rule('trim|required');
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		
		$edit->buttons('modify','save','back','undo','delete');

		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de Compromiso ( '.$producto->nombre.' )';
		$data['title']      = 'Ficha de Compromiso ( '.$producto->nombre.' )';
		$data['footer']     = '';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}

/**
  * Reasignación de compromisos.
  *
  * @since 1.0
  *
  * @return void
  *
  * @param int       $id_emp   Clave primaria de registro en la tabla empresa (usado solo para el link de regreso).
  * @param string    $status   Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int       $id_comp  Clave primaria de registro en la tabla compromisos.
  */
	function reasignar($id_emp,$status,$id_comp){
		$this->load->helper('date');
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		if($rt===false) die('Acceso no permitido');

		$back='/dashboard/gcompania/'.$id_emp;

		$conten=array();
		//Saca los sub-hitos con sus responsables
		$sel=array('a.tarea','a.ejecucion','a.fecha','a.id','a.peso'
		,'UNIX_TIMESTAMP(a.fecha) AS uts','CONCAT_WS(\' \',nombre,apellido) AS nombre');
		$this->db->select($sel);
		$this->db->from('tareas AS a');
		$this->db->join('integrantes AS b','a.id_integrante=b.id','LEFT');
		$this->db->where('a.id_compromiso',$id_comp);
		$this->db->where('a.ejecucion <',100);
		$this->db->order_by('a.fecha','desc');
		$this->db->order_by('a.id');
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$conten['ecomprom']=$query->result();
		}else{
			$conten['ecomprom']=array();
		}

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		$edit->label = 'Reasignación de compromiso';
		$edit->back_url = site_url($back);

		$edit->source('compromisos');
		$edit->pre_process(array('insert'), array($this, 'pre_reasig_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_reasig_update'));
		$edit->pre_process(array('delete'), array($this, 'pre_reasig_insert'));
		$edit->field('date'    ,'fecha','Fecha')->rule('trim|required');
		$edit->field('input'   ,'compromiso','Compromiso')->mode='autohide';
		$edit->field('checkbox','penalizar','Penalizar')
			->set_attributes(array('checked'=>true,'data-role'=>"flipswitch", 'data-on-text'=>"Si", 'data-off-text'=>"No", 'data-wrapper-class'=>"custom-size-flipswitch"));
		$edit->field('checkboxgroup','','')
			->set_extra('Nota: Por cada tarea que no tengan asignado un responsable sera penalizado todo el grupo.');
			

		$edit->buttons('modify','save','back','undo');

		$edit->build();
		
		$this->db->select(array('a.nombre', 'b.compromiso'));
        $this->db->from('producto AS a');
        $this->db->join('compromisos AS b', 'a.id=b.id_producto', 'left');
        $this->db->where('b.id',$id_comp);
        $query = $this->db->get();
        $producto= $query->row();
		
		$conten['edit']    = $edit;

		$data['content']   = $this->load->view('view_reasig',$conten,true);
		$data['back_url']  = $back;
		$data['header']    = 'Reasignaci&oacute;n de Compromiso ( '.$producto->nombre.' )';
		$data['title']     = 'Reasignaci&oacute;n de Compromiso ( '.$producto->nombre.' )';
		$data['footer']    = '';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}

/**
  * Pre-proceso antes de borrar.
  *
  * Chequea que el compromiso no este sub-delegado
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function pre_delete($model){
		$id=$model->pk['id'];
		$this->db->where('id_compromiso', $id);
		$this->db->from('tareas');
		if($this->db->count_all_results()==0){
			return true;
		}else{
			$model->error_string = 'No se puede eliminar porque tiene tareas';
			return false;
		}
	}

/**
  * Pre-proceso antes de insertar.
  *
  * Asigna el id de producto en caso de tenerlo
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function pre_insert($model){
		$id_compania=$model->get('id_compania');

		$sel=array('a.id_producto');
		$this->db->select($sel);
		$this->db->from('compania AS a');
		$this->db->where('a.id',$id_compania);
		$query = $this->db->get();
		$row = $query->row();

		$producto = $row->id_producto;
		if(empty($producto) && $producto>0 ){
			$model->set('id_producto',$producto);
		}else{
			$model->set('id_producto',0);
		}

		return true;
	}

/**
  * Pre-proceso antes de insertar.
  *
  * Evita que un registro sea ingresado por el método de reasignación
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function pre_reasig_insert($model){
		return false;
	}


/**
  * Pre-proceso antes de modificar o actualizar.
  *
  * Chequea que el compromiso a reasignar no este completado y que la fecha
  * no sea anterior al la fecha actual o anterior al la que tenia anteriormente
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function pre_reasig_update($model){
		$id =$model->pk['id'];
		$eje=$model->get('ejecucion');
		if($eje>=100){
			$model->error_string = '--No se puede reasignar un compromiso cuya ejecuci&oacute;n es el 100%.';
			return false;
		}

		$sel=array('DATE_FORMAT(fecha, \'%Y%m%d\') AS fecha','reasignado');
		$this->db->select($sel);
		$this->db->from('compromisos');
		$this->db->where('id',$id);
		$query = $this->db->get();
		$row   = $query->row();
		$reasig= $row->reasignado;
		$fecha_ant = $row->fecha;
		$fecha_act = str_replace('-','',$model->get('fecha'));
		$fecha_act = substr($fecha_act,0,8);
		if($fecha_ant>=$fecha_act){
			$model->error_string = '--El compromiso se debe asignar a una fecha posterior a la que ya tenia';
			return false;
		}
		$model->set('reasignado',$reasig+1);

		if(date('Ymd')>$fecha_act){
			$model->error_string = '--El compromiso se debe asignar a una fecha posterior a la actual';
			return false;
		}

		if(isset($_POST['penalizar']) && $_POST['penalizar']==1){

			$sel=array('c.fecha','c.id','b.id AS id_int');
			$this->db->select($sel);
			$this->db->from('compromisos    AS c');
			$this->db->join('tareas AS a','a.id_compromiso=c.id');
			$this->db->join('integrantes AS b','a.id_integrante=b.id','LEFT');
			$this->db->where('a.id_compromiso',$id);
			$this->db->where('a.ejecucion <',100);
			$this->db->order_by('a.fecha','desc');
			$this->db->order_by('a.id');
			$query = $this->db->get();

			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					if(!empty($row->id_int)){
						$data = array(
							'id_compromiso'=> $id,
							'id_integrante' => $row->id_int,
							'fecha_comp'    => $row->fecha,
							'exonerada'     => 'N'
						);

						$this->db->insert('penalizaciones', $data);
					}else{
						//Si no tiene asignado penaliza todos
						$sel=array('b.id');
						$this->db->select($sel);
						$this->db->from('compromisos AS c');
						$this->db->join('integcurso AS a','c.id_compania=a.id_compania', 'left');
						$this->db->join('integrantes AS b','a.id_integrante=b.id', 'left');
						$this->db->where('c.id',$id);

						$qquery = $this->db->get();
						foreach ($qquery->result() as $rrow){
								$data = array(
								'id_compromiso'=> $id,
								'id_integrante' => $rrow->id,
								'fecha_comp'    => $row->fecha,
								'exonerada'     => 'N'
							);
							$this->db->insert('penalizaciones', $data);
						}
					}
				}
			}else{
				//Si no tiene tareas tambien penaliza todos
				$sel=array('b.id');
				$this->db->select($sel);
				$this->db->from('compromisos AS c');
				$this->db->join('integcurso AS a','c.id_compania=a.id_compania', 'left');
				$this->db->join('integrantes AS b','a.id_integrante=b.id', 'left');
				$this->db->where('c.id',$id);

				$qquery = $this->db->get();
				foreach ($qquery->result() as $rrow){
						$data = array(
						'id_compromiso'=> $id,
						'id_integrante' => $rrow->id,
						'fecha_comp'    => $row->fecha,
						'exonerada'     => 'N'
					);
					$this->db->insert('penalizaciones', $data);
				}
			}
		}
	}
	
/**
  * Pre-proceso antes de modificar o actualizar.
  *
  * Chequea que el compromiso a reasignar no este completado y que la fecha
  * no sea anterior al la fecha actual o anterior al la que tenia anteriormente
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function pre_update($model){
		$id =$model->pk['id'];
		$eje=$model->get('ejecucion');
		if($eje>=100){
			$model->error_string = '--No se puede reasignar un compromiso cuya ejecuci&oacute;n es el 100%.';
			return false;
		}

		$sel=array('DATE_FORMAT(fecha, \'%Y%m%d\') AS fecha','reasignado');
		$this->db->select($sel);
		$this->db->from('compromisos');
		$this->db->where('id',$id);
		$query = $this->db->get();
		$row   = $query->row();
		$reasig= $row->reasignado;
		$fecha_ant = $row->fecha;
		$fecha_act = str_replace('-','',$model->get('fecha'));
		$fecha_act = substr($fecha_act,0,8);
		if($fecha_ant==$fecha_act){
			
		}
		else if($fecha_ant>=$fecha_act){
			$model->error_string = '--El compromiso se debe asignar a una fecha posterior a la que ya tenia';
			return false;
		}
		$model->set('reasignado',$reasig+1);

		if($fecha_ant==$fecha_act){
			
		}
		else if(date('Ymd')>$fecha_act){
			$model->error_string = '--El compromiso se debe asignar a una fecha posterior a la actual';
			return false;
		}
		
		return true;

	}



/**
  * Post-proceso despues de actualizar desde el ambiente productos.
  *
  * Actualiza todos los compromisos que comparten dicho producto.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function post_update($model){
		$id =$model->pk['id'];
		$eje=$model->get('ejecucion');
		if($eje>=100){
			$model->error_string = '--No se puede reasignar un compromiso cuya ejecuci&oacute;n es el 100%.';
			return false;
		}

		$sel=array('DATE_FORMAT(fecha, \'%Y%m%d\') AS fecha','reasignado');
		$this->db->select($sel);
		$this->db->from('compromisos');
		$this->db->where('id',$id);
		$query = $this->db->get();
		$row   = $query->row();
		$reasig= $row->reasignado;
		$fecha_ant = $row->fecha;
		$fecha_act = str_replace('-','',$model->get('fecha'));
		$fecha_act = substr($fecha_act,0,8);
		if($fecha_ant==$fecha_act){
			
		}
		else if($fecha_ant>=$fecha_act){
			$model->error_string = '--El compromiso se debe asignar a una fecha posterior a la que ya tenia';
			return false;
		}
		$model->set('reasignado',$reasig+1);

		if($fecha_ant==$fecha_act){
			
		}
		else if(date('Ymd')>$fecha_act){
			$model->error_string = '--El compromiso se debe asignar a una fecha posterior a la actual';
			return false;
		}
			
		$id_control=$model->get('id');
		$id_producto=$model->get('id_producto');
		$fecha=$model->get('fecha');
		$compromiso=$model->get('compromiso');

		$data = array(
					'fecha' => $fecha,
					'compromiso'  => $compromiso
					);

		$this->db->where('id_producto',$id_producto);
		$this->db->where('id_compania > 0');
		$this->db->where('control',$id_control);
		$this->db->update('compromisos', $data);
		
		return true;

		return true;
	}


/**
  * Post-proceso despues de borrar un compromiso desde el ambiente productos.
  *
  * Borra en grupo el compromiso para las compañias con dicho producto.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compromiso.
  */
	function post_delete($model){
		$id_control=$model->pk['id'];
		$id_producto=$model->get('id_producto');	
		
		$data = array(
					'id_producto' => $id_producto,
					'control'	  => $id_control
					);
		$this->db->delete('compromisos', $data);		

		return true;
	}

}