<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
  * Clase para inicio del panal 6.
  *
  * @autor  Fernando Osuna
  * @since 2.0
  * @package controllers
  */
class panal6 extends CI_Controller {

	public function index(){
		$this->load->helper('form');
		$this->load->library('rapyd');
		$this->load->library('phpbb');

		$ut= new rpd_auth_library();

		$usr = $this->input->post('usr');
		$pwds = $this->input->post('pwd');
		$pwd = md5($this->input->post('pwd'));
		$idc = $this->input->post('idc');
		$error='';
		$panal=6;

		if($usr!==false){
			$rt=$ut->login($usr,$pwd,$idc);
			if($rt){
				$rol=$ut->role();
				$this->db->from('integrantes AS a');
				$this->db->from('integcurso AS b', 'a.id=b.id_integrante', 'left');
				$this->db->join('integcurso AS c','b.id_compania=c.id_compania', 'left');
				$this->db->where('a.usuario',$usr);
				$this->db->where('b.id_compania IS NOT NULL');
				$this->db->where('b.id_compania <>','');
				$this->db->where('b.id_curso',$idc);
				$this->db->where('b.semestre',$ut->semestre());
				
				$cant = $this->db->count_all_results();
				if($cant==1 && $rol>2){
					$this->db->select(array('id'));
					$this->db->from('integrantes');
					$this->db->where('usuario', $usr);
					$query = $this->db->get();
					$row = $query->row();
					
					$this->db->where('id_integrante', $row->id);
					$this->db->where('id_curso', $idc);
					$this->db->update('integcurso', array('cargo' => 'G'));
					$ut->login($usr,$pwd,$idc);
				}

				$phpbb = new Phpbb();
 				$phpbb_vars = array("username" => $usr, "password" => $pwds);
				$phpbb_result = $phpbb->user_login($phpbb_vars);
 				if($rol==1){
					ci_redirect('dashboard/');
				}else{
					ci_redirect('dashboard/gcompalu/');
				}
			}else{
				$ut->logout();
				
				$this->db->where('usuario',$usr);
				$this->db->from('integrantes');
				$query = $this->db->get();
				if($query->num_rows()>0)
					$row=$query->row();
				
				if($row->clave == $pwds){
					$actualizar_datos = array('clave'=>$pwd);
					$this->db->where('id',$row->id);
					$this->db->where('usuario',$usr);
					$this->db->update('integrantes',$actualizar_datos);
					$this->loginAdmin();
					//$error='Ocurrio un problema! Por Favor intente de nuevo.';
				}
				else
				{
					$error='Usuario o clave no v&aacute;lida';
				}
				if($ut->validar($usr,$pwd, $id_cour))
					$error='No disponible temporalmente!';
			}
		}

		$conten=array();
		$conten['error']=$error;
		$conten['panal']=$panal;
		
		$this->db->select(array('id','semestre'));
		$this->db->from('curso');
		$this->db->where('panal',$panal);
		$this->db->limit(1);
		$query = $this->db->get();
	
		if ($query->num_rows() > 0){
			$row = $query->row();
			$conten['semestre']=$row->semestre;
		}	

		$obj = $this->load->view('view_login',$conten ,true);
		$data['content']    = $obj;
		$data['header']     = 'Gesti&oacute;n RAIS';
		$data['title']      = 'Gesti&oacute;n RAIS';
		$data['footer']     = '';

		$data['onLoadScript']='$.extend(  $.mobile , {
			ajaxFormsEnabled: false
		});';

		$this->load->view('view_ven_login', $data);
		
		
	}
	
		
/**
  * Método para el cierre de sessión de usuario.
  * Destruye la sesión de rapyd y la de phpBB3
  *
  * @return  void
  */
	public function logout(){
		$this->load->library('rapyd');
		$this->load->library('phpbb');
		$phpbb = new Phpbb();
		
		$phpbb_result = $phpbb->user_logout();

		$ut= new rpd_auth_library();
		$ut->logout();
		
		//$this->index();
		ci_redirect('panal6');
		
	}
	
/**
  * Método que permite vincular un nuevo curso a una cuenta RAIS existente.
  *
  * @return  void
  */
	public function vincular(){
		$this->load->helper('form');
		$this->load->library('rapyd');

		$ut= new rpd_auth_library();

		$usr = $this->input->post('usr');
		$pwds = $this->input->post('pwd');
		$pwd = md5($this->input->post('pwd'));
		$error='';
		$panal=6;

		$valid_tipo='A';

			$this->db->select(array('id','activo'));
			$this->db->from('curso');
			$this->db->where('panal',6);
			$this->db->limit(1);
			$query = $this->db->get();
	
			if ($query->num_rows() > 0){
				$row = $query->row();
				$id_cour = $row->id;
				$acins = $row->activo;
			}
			if($acins) die('Acceso no autorizado.');
	
			if($usr!==false){
						
				$this->db->select(array('tipo'));
				$this->db->from('integrantes');
				$this->db->where('usuario',$usr);
				$this->db->limit(1);
				$query = $this->db->get();
				if ($query->num_rows() > 0){
					$row = $query->row();
					$valid_tipo = $row->tipo;
				}
		
				if ($valid_tipo=='A'){
				
					if ($ut->vincular($usr,$pwd)){						
						if($ut->validar($usr,$pwd, $id_cour)){
							$rol=$ut->role();
							$tipo=$ut->tipo();
			                
			                ci_redirect('integrantes/vincular/'.$panal.'/'.$id_cour.'/'.$ut->id_int().'/create/');	                
							
						} else{
							$ut->logout();
							$error='Ya tiene este curso asociado a su cuenta';
						}
					} else {
						$ut->logout();
						$error='Usuario o clave no v&aacute;lida';
					}
				}else{
					$error='Usuarios tipo "Profesor" no se pueden asociar por este medio, contacte al administrador.';
				}
			}
		
		$conten=array();
		$conten['error']=$error;
		$conten['panal']=$panal;

		$obj = $this->load->view('view_login_vincular',$conten ,true);
		$data['content']    = $obj;
		$data['header']     = 'Gesti&oacute;n RAIS';
		$data['title']      = 'Gesti&oacute;n RAIS';
		$data['footer']     = '';

		$data['onLoadScript']='$.extend(  $.mobile , {
			ajaxFormsEnabled: false
		});';

		$this->load->view('view_ven_login', $data);
	}
}
