<?php
/**
  * Clase para la gestión de productos.
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class producto extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Producto';
/**
 *  Dirección url de la clase.
 */
	var $url ='producto/';

	function index(){
	}

/**
  * CRUD para los registro de productos
  *
  * @since 1.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla productos.
  */
	function dataeditmobil($status,$id=0){
		$this->load->library('rapyd');
		$this->load->library('calendar');
		$this->load->helper('date');
		$this->load->helper('url');
		$this->load->helper('html');
		$this->load->helper('form');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_cour = $ut->id_curso();
		if($rt===false) die('Acceso no permitido');
		
		$back='dashboard/produ';

		$edit = new dataedit_library();
		$edit->label    = $this->titp;
		$edit->back_url = site_url($back);

		$edit->source('producto');
		$edit->pre_process(array('delete'), array($this, 'pre_delete'));
		$edit->field('input'   ,'nombre','Nombre')->rule('trim|required');
		$edit->field('textarea','descripcion','Descripción')->rule('required');
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;

		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		if(!empty($id))	$back.='cto/'.$id;

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de Producto';
		$data['title']      = 'Ficha de Producto';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');

		$this->load->view('view_ven', $data);
	}

/**
  * Pre-proceso antes de borrar.
  *
  * Chequea que el producto no este asignado a alguna compañía.
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla producto.
  */
	function pre_delete($model){
		$id=$model->pk['id'];
		$cana=0;
		$this->db->where('id_producto', $id);
		$this->db->from('compania');
		$cana+=$this->db->count_all_results();

		$this->db->where('id_producto', $id);
		$this->db->from('compromisos');
		$cana+=$this->db->count_all_results();

		if($cana==0){
			return true;
		}else{
			$model->error_string = 'No se puede eliminar porque existe al menos una compañia que lo tiene asignado';
			return false;
		}
	}
}