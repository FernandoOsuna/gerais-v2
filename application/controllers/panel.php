<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
  * Clase para visualizar y navegar por los distintos registros en la base de datos
  * 
  * @since 2.0
  *
  * @autor  Fernando Osuna
  * @package controllers
  */
class panel extends CI_Controller {

/**
  * Página principal que se le muestra al administrador del panal
  *
  * Contiene información sobre los cursos
  *
  * @return void
  */
        public function index(){
        	    $this->load->library('rapyd');
                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                if($rt===false || $ut->tipo()!='S') die('Acceso no permitido');

                $menu ='<div data-role="navbar"><ul>';
                $menu.='<li><a href="'.site_url('panel').'" data-transition="none" class="ui-btn-active">Cursos</a></li>';
                $menu.='<li><a href="'.site_url('panel/resp').'" data-transition="none">Responsables</a></li>';
               	$menu.='<li><a href="'.site_url('panel/users').'" data-transition="none">Usuarios</a></li>';
                $menu.='<li><a href="'.site_url('panel/foro').'" data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de Cursos registrados</h3>";
                $menu.= "<p>Seleccione el curso que desea gestionar</p>";
                $menu.= "<div class='content-primary'>
                        <ul data-role='listview' data-filter='true' data-count-theme='b' data-inset='true'>";
                
                $sel=array('id','nombre','profesor','contenido', 'semestre', 'panal');
                $this->db->select($sel);
                $this->db->from('curso');
                $this->db->order_by('panal','asc');
                $query = $this->db->get();
                
                if ($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                        	$menu.= '<li><a href="'.site_url('/curso/dataeditmobil/modify/'.$row->id).'"><h4>Panal '.$row->panal.': '.$row->nombre.', '.$row->profesor.'</h4></a></li>';
                        }
                }

                $menu.= "</ul></div>";

                $data['content']    = $menu;
                $data['footer']     = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['footer']    .= '<a href="'.site_url('/curso/dataeditmobil/create/').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Curso</a>';
                $data['header']     = 'Panel de control';
                $data['title']      = 'Panel de control';
                $data['logout']     = 0;
                $data['usuario']    = $ut->user('name');
                $data['headerextra']= 'Administrador: '.$ut->user('name');

                $this->load->view('view_ven_panel', $data);
        	
        }
        
/**
  * Lista de profesores responsables de los cursos
  *
  * @return void
  */
        public function resp(){
        	    $this->load->library('rapyd');
                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                if($rt===false || $ut->tipo()!='S') die('Acceso no permitido');

                $menu ='<div data-role="navbar"><ul>';
                $menu.='<li><a href="'.site_url('panel').'" data-transition="none" >Cursos</a></li>';
                $menu.='<li><a href="'.site_url('panel/resp').'" data-transition="none" class="ui-btn-active">Responsables</a></li>';
                $menu.='<li><a href="'.site_url('panel/users').'" data-transition="none">Usuarios</a></li>';
                $menu.='<li><a href="'.site_url('panel/foro').'" data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de Responsables de los cursos</h3>";
                $menu.= "<p>Seleccione el registro que desea gestionar</p>";
                $menu.= "<div class='content-primary'>
                        <ul data-role='listview' data-filter='true' data-count-theme='b' data-inset='true'>";
                
                $sel=array('a.id AS id_int','CONCAT_WS(\' \',a.nombre,a.apellido) AS nombre', 'a.id_curso AS id_curso','b.panal','c.semestre', 'b.id AS curso');
                $this->db->select($sel);
                $this->db->from('integrantes AS a');
                $this->db->join('integcurso AS c','a.id=c.id_integrante','left');
                $this->db->join('curso AS b', 'c.id_curso=b.id', 'left');
                $this->db->where('a.tipo','P');
                $this->db->order_by('a.nombre','asc');
                $this->db->group_by('nombre');
                $query = $this->db->get();
                $npanal =' ';
                
                if ($query->num_rows() > 0){
                	foreach ($query->result() as $row){
						$sel= array('GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre) AS cursos');			
						$this->db->select($sel);
			            $this->db->from('curso AS a');
			            $this->db->join('integcurso AS b', 'a.id=b.id_curso', 'left');
			            $this->db->where('b.id_integrante',$row->id_int);
			            
			            $qquery=$this->db->get();
			            $rrow=$qquery->row();
                		
                    $menu.= '<li><a href="'.site_url('/integrantes/dataedit/modify/'.$row->id_int).'"><h3>'.$row->nombre.' </h3><p>'.$rrow->cursos.'</p></a></li>';
                        	
                    }
                }

                $menu.= "</ul></div>";

                $data['content']    = $menu;
                $data['footer']     = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['footer']    .= '<a href="'.site_url('/integrantes/dataedit/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Responsable</a>';
                $data['header']     = 'Panel de control';
                $data['title']      = 'Panel de control';
                $data['logout']     = 0;
                $data['usuario']    = $ut->user('name');
                $data['headerextra']= 'Administrador: '.$ut->user('name');

                $this->load->view('view_ven_panel', $data);
        	
        }
        

/**
  * Lista de integrantes
  *
  * Contiene información sobre todos los integrantes registrados
  *
  * @return void
  */
        public function users(){

                $this->load->library('rapyd');
                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                if($rt===false || $ut->tipo()!='S') die('Acceso no permitido');
                
                $menu ='<div data-role="navbar"><ul>';
                $menu.='<li><a href="'.site_url('panel').'" data-transition="none" >Cursos</a></li>';
                $menu.='<li><a href="'.site_url('panel/resp').'" data-transition="none">Responsables</a></li>';
                $menu.='<li><a href="'.site_url('panel/users').'" data-transition="none" class="ui-btn-active">Usuarios</a></li>';
                $menu.='<li><a href="'.site_url('panel/foro').'" data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de Usuarios</h3>";
                $menu.= "<p>Seleccione el usuario que desea gestionar</p>";

                $query = $this->db->query('SELECT * from integrantes where id ='.$id_int);
                if($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                                $i_cargo=$row->cargo;
                        }
                }

                if(empty($i_cargo) || $i_cargo == 0 ){
                        $sel=array('b.id','b.tipo','CONCAT_WS(\' \',b.nombre,b.apellido) AS nombre');
                        $ord_by ='b.nombre';
                        $link1 = '<a href="#" data-transition="none" class="ui-btn-active" data-ajax="false" title="Ordenar por nombre">Nombre</a>';
                        $link2 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/1').'" data-transition="none" data-ajax="false" title="Ordenar por apellido">Apellido</a>';
                        $link3 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/2').'" data-transition="none" data-ajax="false" title="Ordenar por Tipo">Tipo</a>';
                }
                else if($i_cargo == 1 ){
                        $sel=array('b.id','b.tipo','CONCAT_WS(\' \',b.apellido,b.nombre) AS nombre');
                        $ord_by ='b.apellido';
                        $link1 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/0').'" data-transition="none" data-ajax="false" title="Ordenar por nombre">Nombre</a>';
                        $link2 = '<a href="#" data-transition="none" class="ui-btn-active" data-ajax="false" title="Ordenar por apellido">Apellido</a>';
                        $link3 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/2').'" data-transition="none" data-ajax="false" title="Ordenar por Tipo">Tipo</a>';
                }
                else if($i_cargo == 2 ){
                        $sel=array('b.id','b.tipo','CONCAT_WS(\' \',b.nombre,b.apellido) AS nombre');
                        $ord_by ='b.tipo';
                        $link1 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/0').'" data-transition="none" data-ajax="false" title="Ordenar por nombre">Nombre</a>';
                        $link2 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/1').'" data-transition="none" data-ajax="false" title="Ordenar por apellido">Apellido</a>';
                        $link3 = '<a href="#" class="ui-btn-active" data-transition="none" data-ajax="false" title="Ordenar por tipo">Tipo</a>';

                }

                $menu.='<div data-role="navbar" style=" width:300px;"><ul><li>';
                $menu.=$link1;
                $menu.='</li><li>';
                $menu.=$link2;
                $menu.='</li><li>';
                $menu.=$link3;
                $menu.='</li></ul></div>
                                <div class="content-primary">
                                <ul data-role="listview" data-filter="true" data-inset="true">';

                $this->db->select($sel);
                $this->db->from('integrantes AS b');
                $this->db->where('b.tipo <>','S');
                $this->db->order_by($ord_by ,'asc');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                                if($row->tipo == 'A')
                                        $cargo="Alumno";
                                else if($row->tipo == 'P')
                                        $cargo="Profesor";                               
                                $menu.= '<li><a href="'.site_url('/integrantes/dataedituser/modify/'.$row->id).'">'.$row->nombre.' - '.$cargo.'</a></li>';
                               
                        }
                }
                $menu.= "</ul></div>";                

                $data['content'] = $menu;
                $data['footer']  = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['header']     = 'Panel de control';
                $data['title']      = 'Panel de control';
                $data['logout']     = 0;
                $data['usuario']    = $ut->user('name');
                $data['headerextra']= 'Administrador: '.$ut->user('name');

                $this->load->view('view_ven_panel', $data);
        }
        

/**
  * Página que genera el foro del sistema a el administrador a partir de phpBB3
  *
  * @return void
  */
	public function foro(){
       	$this->load->library('rapyd');
        $ut     = new rpd_auth_library();
        $rt     = $ut->logged(1);
        $id_int = $ut->id_int();
        if($rt===false || $ut->tipo()!='S') die('Acceso no permitido');

        $menu ='<div data-role="navbar"><ul>';
        $menu.='<li><a href="'.site_url('panel').'" data-transition="none" >Cursos</a></li>';
        $menu.='<li><a href="'.site_url('panel/resp').'" data-transition="none">Responsables</a></li>';
        $menu.='<li><a href="'.site_url('panel/users').'" data-transition="none">Usuarios</a></li>';
        $menu.='<li><a href="'.site_url('panel/foro').'" data-transition="none" class="ui-btn-active">Foro</a></li>';
        $menu.='</ul></div>';
		

		$menu.='<div class="ui-content" align="center">
			    <iframe onload="javascript:resize()" src="http://localhost/gerais-v2/foro/" height="100%" width="100%" frameborder="0" scrolling="auto" id="iframeforo" name="iframeforo"></iframe>
			</div>';

        $data['content']    = $menu;
        $data['footer']     = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
        $data['header']     = 'Panel de control';
        $data['title']      = 'Panel de control';
        $data['logout']     = 0;
        $data['usuario']    = $ut->user('name');
        $data['headerextra']= 'Administrador: '.$ut->user('name');

        $this->load->view('view_ven_panel', $data);
	}
        
}