<?php
/**
  * Clase para la gestión de compañías.
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class companias extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Compa&ntilde;ías';
/**
 *  Dirección url de la clase.
 */
	var $url ='companias/';

	function index(){
		return;
	}

/**
  * CRUD para los registro de compañías.
  *
  * @since 1.0	
  *
  * @return void
  * @param string    $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int       $id     Clave primaria de registro en la tabla compania.
  */
	function dataeditmobil($status,$id=0){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$id_int=$ut->id_int();
		$id_cour = $ut->id_curso();
		if($rt===false) die('Acceso no permitido');
		$role  = $ut->role();
		if($role>2)     die('Acceso no permitido');

		if($role==1){
			if($id==0){
				$back='dashboard/index';
			}else{
				$back='dashboard/gcompania/'.$id;
			}
		}else{
			$back='dashboard/gcompalu';
			$data['home_url']   = $back;
		}

		$dbprefix=$this->db->dbprefix;

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		$edit->label = $this->titp;
		$edit->back_url = site_url($back);

		$edit->source('compania');
		$edit->pre_process(array('delete'), array($this, 'pre_delete'));
		$edit->pre_process(array('update'), array($this, 'pre_update'));
		$edit->field('input','nombre','Nombre')
			->rule('trim|required|unique|max_length[200]')
			->set_attributes(array('maxlength'=>'200'));
		
		$edit->field('dropdown','id_producto','Producto')
			->option('','Ninguno')
			->options("SELECT id,nombre FROM ${dbprefix}producto WHERE id_curso=$id_cour ORDER BY nombre");
		
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		$edit->field('hidden','semestre','')->insert_value=$ut->semestre();
		
		$edit->post_process(array('insert'), array($this, 'post_insert'));
		
		$edit->buttons('modify','save','undo','back');
		if($role==1) $edit->buttons('delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Gerencia de Compa&ntilde;&iacute;a';
		$data['title']      = 'Ficha de Compa&ntilde;&iacute;as';
		$data['footer']     = '';
		$data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}	

/**
  * Pre-proceso antes de actualizar.
  *
  * Relaciona la compañía con los compromisos en caso de no tenerla
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compania.
  */
	function pre_update($model){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$id_int=$ut->id_int();
		$id_cour = $ut->id_curso();
		
		$id_compania=$model->pk['id'];

		$sel=array('a.id_producto');
		$this->db->select($sel);
		$this->db->from('compania AS a');
		$this->db->where('a.id',$id_compania);
		$query = $this->db->get();
		$row   = $query->row();

		$ant_prod = $row->id_producto;
		$act_prod = $model->get('id_producto');
		if((!empty($act_prod)) && $act_prod>0 && $ant_prod!=$act_prod){
			$data=array('id_producto'=>$act_prod);
			$this->db->where('id_compania' , $id_compania);
			$this->db->where('id_producto' , 0);
			$this->db->update('compromisos', $data);
		}
		
		$this->db->from('compromisos');
		$this->db->where('id_producto',$act_prod);
		$this->db->where('id_compania',$id_compania);
		$apc   = $this->db->count_all_results();
		
		if(($ant_prod!=$act_prod && $apc==0) || (empty($ant_prod) && $apc==0)){
			//Elimina los compromisos asociados al producto que se va a actualizar
			//Elimina las tareas de dichos compromisos 
			/* Se comenta para mantener la trayectoria de productos anteriores
			$sel=array('id','compromiso', 'fecha');
			$this->db->select($sel);
			$this->db->from('compromisos');
			$this->db->where('id_producto' , $ant_prod);
			$this->db->where('id_compania' , $id_compania);
			$query = $this->db->get();
	
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$data = array(
					'id_compania' => $id_compania,
					'compromiso'  => $row->compromiso,
					'id_producto' => $ant_prod,
					'fecha'		  => $row->fecha
					);
					$this->db->delete('compromisos', $data);
					
					$data = array('id_compromiso' => $row->id);
					$this->db->where('control > 0');
					$this->db->delete('tareas', $data);
				}
			}*/
	
			//Refleja la creación de los compromisos existentes para el nuevo producto
			//Refleja la creación de tareas existentes a dichos compromisos
			$selt=array('id', 'id_compromiso', 'tarea', 'peso','control');
			$this->db->select($selt);
			$this->db->from('tareas');
			$this->db->where('control' , 0);
			$qtareas = $this->db->get();		
			
			$sel=array('id', 'id_producto', 'compromiso', 'fecha');
			$this->db->select($sel);
			$this->db->from('compromisos');
			$this->db->where('id_producto' , $act_prod);
			$this->db->where('id_compania' , 0);
			$this->db->where('control' , 0);
			$this->db->where('id_curso' , $id_cour);
			$query = $this->db->get();
	
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$data = array(
					'id_compania' => $id_compania,
					'compromiso'  => $row->compromiso,
					'id_producto' => $act_prod,
					'fecha'       => $row->fecha,
					'control'     => $row->id,
					'integ'		  => $id_int,
					'id_curso'	  => $id_cour,
					'semestre'	  => $ut->semestre()
					);
					$this->db->insert('compromisos', $data);
					
					/*$myTable = "compromisos";
					$result = mysql_query("SHOW TABLE STATUS");
					while ($ro = mysql_fetch_assoc($result)) {
					  if ($ro['Name'] == $myTable) {
					    $last_id = $ro['Auto_increment'] - 1 ;
					    break;
					  }
					}*/
					$last_id = mysql_result(mysql_query("SELECT MAX(id) FROM compromisos WHERE integ=$id_int"), 0);
					
					foreach ($qtareas->result() as $rowt){
						if($rowt->id_compromiso == $row->id){
							$data = array(
								'id_compromiso' => $last_id,
								'tarea'     	=> $rowt->tarea,
								'peso'          => $rowt->peso,
								'ejecucion'     => 0,
								'id_integrante' => 0,
								'registro'      => 'P',
								'fecha'			=>date('Y-m-d',mktime(0, 0, 0, date('m'), date('d')+7, date('Y'))),
								'control'		=> $rowt->id,
								'integ'		  => $id_int
								);
								$this->db->insert('tareas', $data);
						}
					}
				}
			}
		}		
		return true;
	}

/**
  * Pre-proceso antes de borrar.
  *
  * Chequea que la compañía a borrar no tenga integrantes asociados
  * y/o compromisos
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compania.
  */
	function pre_delete($model){
		$id=$model->pk['id'];
		$cana=0;
		$this->db->from('integcurso');
		$this->db->where('id_compania',$model->pk['id']);
		$cana+= $this->db->count_all_results();

		$this->db->from('compromisos');
		$this->db->where('id_compania',$model->pk['id']);
		$cana+= $this->db->count_all_results();

		if($cana>0){
			$model->error_string = 'No se puede eliminar una compañía con trayectoria.';
			return false;
		}else{
			return true;
		}
	}	
	
/**
  * Post-proceso despues de insertar.
  *
  * Relaciona la compañía con los compromisos creados para el producto
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla compania.
  */
	function post_insert($model){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		$id_int=$ut->id_int();
		$id_cour = $ut->id_curso();
		
		$id_compania=$model->pk['id'];
		$id_prod = $model->get('id_producto');

		$myTable = "compania";
		$this->db->trans_start();
		$result = mysql_query("SHOW TABLE STATUS");
		while ($row = mysql_fetch_assoc($result)) {
		  if ($row['Name'] == $myTable) {
		    $next_insert_id = $row['Auto_increment'];
		    break;
		  }
		}
		
		//Refleja la creación de los compromisos existentes para el producto
		//Refleja la creación de tareas existentes a dichos compromisos
		
		$sel=array('id', 'id_compromiso', 'tarea', 'peso','control');
		$this->db->select($sel);
		$this->db->from('tareas');
		$this->db->where('control' , 0);
		$qtareas = $this->db->get();
		
		$sel=array('id', 'compromiso', 'fecha');
		$this->db->select($sel);
		$this->db->from('compromisos');
		$this->db->where('id_producto' , $id_prod);
		$this->db->where('id_compania' , 0);
		$query = $this->db->get();
	
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$data = array(
				'id_compania' => $next_insert_id,
				'compromiso'  => $row->compromiso,
				'id_producto' => $id_prod,
				'fecha'       => $row->fecha,
				'control'     => $row->id,
				'integ'			=> $id_int,
				'id_curso'		=> $id_cour,
				'semestre'	  => $ut->semestre()
				);
				$this->db->insert('compromisos', $data);
				
				/*$myTable = "compromisos";
				$result = mysql_query("SHOW TABLE STATUS");
				while ($ro = mysql_fetch_assoc($result)) {
				  if ($ro['Name'] == $myTable) {
				    $last_id = $ro['Auto_increment'] - 1 ;
				    break;
				  }
				}*/
				$last_id = mysql_result(mysql_query("SELECT MAX(id) FROM compromisos WHERE integ=$id_int"), 0);
				
				foreach ($qtareas->result() as $rowt){
					if($rowt->id_compromiso == $row->id){
						$data = array(
							'id_compromiso' => $last_id,
							'tarea'     	=> $rowt->tarea,
							'peso'          => $rowt->peso,
							'ejecucion'     => 0,
							'id_integrante' => 0,
							'registro'      => 'P',
							'fecha'			=>date('Y-m-d',mktime(0, 0, 0, date('m'), date('d')+7, date('Y'))),
							'control'		=> $rowt->id,
							'integ'			=> $id_int
							);
							$this->db->insert('tareas', $data);
					}
				}
			}
		}
		$this->db->trans_complete();
		return true;		

	}

}
