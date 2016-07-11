<?php
/**
  * Clase para la gestión de problemas a nivel de compañías
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @package controllers
  */
class problemas extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Problemas Corporativos';
/**
 *  Dirección url de la clase.
 */
	var $url ='problemas/';

	function index(){
	}

/**
  * CRUD para los registro de problemas corporativos
  *
  *
  * @return void
  * @param int   $id_comp Clave primaria de compromisos.
  */
	function dataeditmobil($id_comp){
		$this->load->library('rapyd');

		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_int=$ut->id_int();
		if($rt===false) die('Acceso no permitido');
		$back = 'dashboard/gsubcompania/'.$id_comp;

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		$edit->label = $this->titp;
		$edit->back_url = site_url($back);

		$edit->source('problemas');
		$edit->field('hidden','id_compromiso','Compromiso')->rule('trim')->set_insert_value($id_comp);
		$edit->field('input','problema','Descripción del problema')->rule('trim|required');

		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Problemas corporativos';
		$data['title']      = 'Problemas corporativos';
		$data['footer']     = '';

		$this->load->view('view_ven', $data);
	}
}
