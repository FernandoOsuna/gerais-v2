<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
  * Clase para visualizar y navegar por los distintos registros en la base de datos
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class dashboard extends CI_Controller {

/**
  * Página principal que se le muestra a los profesores
  *
  * Contiene información sobre las compañías y sus productos
  *
  * @return void
  */
        public function index(){
                $this->load->library('rapyd');

                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                $id_cour = $ut->id_curso();
                if($rt===false) die('Acceso no permitido');

                $menu ='<div data-role="navbar"><ul data-inset="true">';
                $menu.='<li><a href="'.site_url('dashboard/index').'" data-transition="none" class="ui-btn-active">Compa&ntilde;&iacute;as</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/integ').'" data-transition="none">Integrantes</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/produ').'" data-transition="none">Productos</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/autco').'" data-transition="none">Auto/Co Evaluaci&oacute;n</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/foro').'"  data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de compa&ntilde;&iacute;as registradas</h3>";
                $menu.= "<p>Seleccione la compa&ntilde;&iacute;a que desea evaluar</p>";
                $menu.= "<div class='content-primary'>
                        <ul data-role='listview' data-filter='true' data-count-theme='b' data-inset='true'>";

                $sel=array('a.id','a.nombre','b.id AS id_prod','b.nombre AS producto');
                $this->db->select($sel);
                $this->db->from('compania AS a');
                $this->db->join('producto AS b','a.id_producto=b.id','left');
                $this->db->where('a.id_curso',$id_cour);
                $this->db->where('a.semestre',$ut->semestre());
                $this->db->order_by('a.nombre','asc');
                $query = $this->db->get();

                $actis=array();
                if ($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                                //Saca el promedio de avance
                                $sel2=array('AVG(ejecucion) AS prom');
                                $this->db->select($sel2);
                                $this->db->from('compromisos');
                                $this->db->where('id_compania',$row->id);
                                $this->db->where('fecha <= CURDATE()');
                                $this->db->where('id_curso',$id_cour);
                                $this->db->where('id_producto',$row->id_prod);
                                $query2 = $this->db->get();
                                $row2   = $query2->row();
                                $promedio  = round($row2->prom,1);

                                //Saca los compromisos terminados
                                $sel3=array('compromiso','ejecucion','fecha','id','UNIX_TIMESTAMP(fecha) AS uts');
                                $this->db->select($sel3);
                                $this->db->from('compromisos');
                                $this->db->where('id_compania',$row->id);
                                $this->db->where('id_producto',$row->id_prod);
                                $this->db->where('ejecucion <', 100);
                                $this->db->where('id_curso',$id_cour);
                                $this->db->order_by('ejecucion','desc');
                                $this->db->order_by('fecha','asc');
                                $this->db->order_by('id','asc');
                                $query3 = $this->db->get();
                                if ($query3->num_rows() > 0){
                                        $row3=$query3->row(0);          
                                        $comproactual = $row3->compromiso;
                                        $ecomproactual = ': '.round($row3->ejecucion,1).'%';
                                }
                                else{
                                        $comproactual ='No tiene copromisos';
                                        $ecomproactual ='';
                                }

                                $prod = (empty($row->producto))?'No tiene producto asignado':$row->producto;
                                $menu.= '<li><a href="'.site_url('dashboard/gcompania/'.$row->id).'"><h3>'.$row->nombre.'  -  '.$prod.'</h3><p style="color:SteelBlue">'.$comproactual.' '.$ecomproactual.'</p><span class="ui-li-count"> '.$promedio.'%</span></a></li>';
                        }
                }
                $menu.= "</ul></div>";

                $data['content']    = $menu;
                $data['footer']     = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['footer']    .= '<a href="'.site_url('/companias/dataeditmobil/create/').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Compañía</a>';
                $data['footer']    .= '<a href="'.site_url('aucoevaluacion/reporte').'" data-role="button" data-icon="grid" data-direction="reverse" data-ajax="false">Reporte Notas</a>';
                $data['header']     = $this->_curso(1).'::Panel de control';
                $data['title']      = $this->_curso(1);
                $data['logout']     = $this->_curso(2);
                $data['usuario']    = $ut->user('name');
                $data['headerextra']= 'Profesor: '.$ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Lista de integrantes presentada a los profesoes
  *
  * Contiene información sobre todos los integrantes registrados
  *
  * @return void
  */
        public function integ(){

                $this->load->library('rapyd');
                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                $id_cour = $ut->id_curso();
                if($rt===false) die('Acceso no permitido');

                $menu ='<div data-role="navbar"><ul data-inset="true">';
                $menu.='<li><a href="'.site_url('dashboard/index').'" data-transition="none">Compa&ntilde;&iacute;as</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/integ').'" data-transition="none" class="ui-btn-active">Integrantes</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/produ').'" data-transition="none">Productos</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/autco').'" data-transition="none">Auto/Co Evaluaci&oacute;n</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/foro').'" data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de integrantes</h3>";
                $menu.= "<p>Seleccione el integrante que desea gestionar</p>";

                $query = $this->db->query('SELECT * from integrantes where id ='.$id_int);
                if($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                                $i_cargo=$row->cargo;
                        }
                }

                if(empty($i_cargo) || $i_cargo == 0 ){
                        $sel=array('a.id AS id_compa','c.seccion','c.cargo','b.id','a.nombre AS compania','CONCAT_WS(\' \',b.nombre,b.apellido) AS nombre');
                        $ord_by ='b.nombre';
                        $link1 = '<a href="#" data-transition="none" class="ui-btn-active" data-ajax="false" title="Ordenar por nombre">Nombre</a>';
                        $link2 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/1').'" data-transition="none" data-ajax="false" title="Ordenar por apellido">Apellido</a>';
                        $link3 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/2').'" data-transition="none" data-ajax="false" title="Ordenar por cargo">Cargo</a>';
                }
                else if($i_cargo == 1 ){
                        $sel=array('a.id AS id_compa','c.seccion','c.cargo','b.id','a.nombre AS compania','CONCAT_WS(\' \',b.apellido,b.nombre) AS nombre');
                        $ord_by ='b.apellido';
                        $link1 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/0').'" data-transition="none" data-ajax="false" title="Ordenar por nombre">Nombre</a>';
                        $link2 = '<a href="#" data-transition="none" class="ui-btn-active" data-ajax="false" title="Ordenar por apellido">Apellido</a>';
                        $link3 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/2').'" data-transition="none" data-ajax="false" title="Ordenar por cargo">Cargo</a>';
                }
                else if($i_cargo == 2 ){
                        $sel=array('a.id AS id_compa','c.seccion','c.cargo','b.id','a.nombre AS compania','CONCAT_WS(\' \',b.nombre,b.apellido) AS nombre');
                        $ord_by ='b.nombre';
                        $link1 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/0').'" data-transition="none" data-ajax="false" title="Ordenar por nombre">Nombre</a>';
                        $link2 = '<a href="'.site_url('integrantes/cambiarorden/'.$id_int.'/1').'" data-transition="none" data-ajax="false" title="Ordenar por apellido">Apellido</a>';
                        $link3 = '<a href="#" class="ui-btn-active" data-transition="none" data-ajax="false" title="Ordenar por cargo">Cargo</a>';

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
                $this->db->join('integcurso AS c','b.id=c.id_integrante','left');
                $this->db->join('compania AS a','c.id_compania=a.id','left');
                $this->db->where('c.id_curso',$id_cour);
                $this->db->where('c.semestre',$ut->semestre());
                $this->db->order_by('c.seccion' ,'asc');
                $this->db->order_by('a.nombre','asc');
                if($i_cargo < 2 )
                        $this->db->order_by($ord_by ,'asc');
                else if($i_cargo == 2 ){
                        $this->db->_protect_identifiers = FALSE;
                        $this->db->order_by("FIELD(c.cargo,'G','D','P')");
                        $this->db->_protect_identifiers = TRUE;
                }
                $this->db->where('b.tipo','A');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                                if(empty($aux) || empty($aux2) || $aux != $row->compania || $aux2 != $row->seccion){
                                        $menu.="<li data-theme='b'><h3> Compañia: ".$row->compania." - Sección: ".$row->seccion."</h3></li>";
                                        $aux = $row->compania;
                                        $aux2 = $row->seccion;
                                }
                                $comp = (empty($row->id_compa))? 'No tiene compa&ntilde;ia asignada':$row->compania;
                                if($row->cargo == 'G')
                                        $cargo="Gerente";
                                else if($row->cargo == 'D')
                                        $cargo="Director";
                                else if($row->cargo == 'P')
                                        $cargo="Profesional";
                               
                                $menu.= '<li><a href="'.site_url('dashboard/gintegrante/'.$row->id).'">'.$row->nombre.' - '.$cargo.'</a></li>';
                               
                        }
                }
                $menu.= "</ul></div>";

                $sel=array('b.id','CONCAT_WS(\' \',b.nombre,b.apellido) AS nombre');
                $this->db->select($sel);
                $this->db->from('integrantes AS b');
                $this->db->join('integcurso AS c','b.id=c.id_integrante','left');
                $this->db->order_by('b.nombre','asc');
                $this->db->where('b.tipo' ,'P');
                $this->db->where('b.id <>',$id_int);
                $this->db->where('c.id_curso',$id_cour);
                $this->db->where('c.semestre',$ut->semestre());
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                $menu.= "<h3>Lista de profesores</h3>";
                //$menu.= "<p>Seleccione el integrante que desea gestionar</p>";
                $menu.= '<div class="content-primary">
                        <ul data-role="listview" data-filter="true" data-inset="true">';

                        foreach ($query->result() as $row){
                                $menu.= '<li><a href="'.site_url('integrantes/dataedit/modify/'.$row->id).'">'.$row->nombre.'</a></li>';
                        }
                        $menu.= "</ul></div>";
                }

                $data['content'] = $menu;
                $data['footer']  = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['footer'] .= '<a href="'.site_url('/integrantes/dataeditmobil/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Integrante</a>';
                $data['header']  = $this->_curso(1).'::Panel de control';
                $data['title']   = $this->_curso(1);
                $data['logout']  = $this->_curso(2);
                $data['usuario'] = $ut->user('name');
                $data['headerextra']= 'Profesor: '.$ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Lista de produtos que se le muestra a los profesores
  *
  * Contiene información sobre los productos registrados.
  *
  * @return void
  */
        public function produ(){
                $this->load->library('rapyd');

                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                $id_cour = $ut->id_curso();
                if($rt===false) die('Acceso no permitido');

                $menu ='<div data-role="navbar"><ul>';
                $menu.='<li><a href="'.site_url('dashboard/index').'" data-transition="none">Compa&ntilde;&iacute;as</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/integ').'" data-transition="none">Integrantes</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/produ').'" data-transition="none" class="ui-btn-active" >Productos</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/autco').'" data-transition="none">Auto/Co Evaluaci&oacute;n</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/foro').'" data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de productos registrados</h3>";
                $menu.= "<p>Seleccione el producto que desea editar</p>";
                $menu.= "<div class='content-primary'>
                        <ul data-role='listview' data-filter='true' data-inset='true'>";

                $sel=array('b.id','b.nombre');
                $this->db->select($sel);
                $this->db->from('producto AS b');
                $this->db->where('b.id_curso',$id_cour);
                $this->db->order_by('b.nombre','asc');
                $query = $this->db->get();

                $actis=array();
                if ($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                                                               
                                $this->db->from('compromisos');                                
                				$this->db->where('id_compania',0);
                                $this->db->where('control',0);          
                                $this->db->where('id_producto',$row->id);
                                $this->db->where('id_curso',$id_cour);
                                $num_comp = $this->db->count_all_results();
                               
                                $this->db->select(array('GROUP_CONCAT(nombre ORDER BY nombre) AS companias'));
                                $this->db->from('compania');                            
                                $this->db->where('id_producto',$row->id);
                                $this->db->where('id_curso',$id_cour);
                                $this->db->where('semestre',$ut->semestre());
                                $query_c = $this->db->get();
                                if($query_c->num_rows()>0){
                                        $rowc = $query_c->row();
                                        $companias =' '.$rowc->companias;
                                }
                                else{
                                        $companias =' ';
                                }
                                $menu.= '<li><a href="'.site_url('dashboard/producto/'.$row->id).'">'.$row->nombre;
                                $menu.= '<p>'.$num_comp.' Compromisos. '.$companias.'</p></a></li>';
                        }
                }
                $menu.= "</ul></div>";
               

                $data['content'] = $menu;
                $data['footer']  = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['footer'] .= '<a href="'.site_url('/producto/dataeditmobil/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Producto</a>';
                $data['header']  = $this->_curso(1).'::Panel de control';
                $data['title']   = $this->_curso(1);
                $data['logout']  = $this->_curso(2);
                $data['usuario'] = $ut->user('name');
                $data['headerextra']= 'Profesor: '.$ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Especificación de productos
  *
  * Detalles de productos mas la lista de compromisos asociados y agrupados
  *
  * @return void
  * @param int      $id     Clave primaria de registro en la tabla productos.
  */
        public function producto($id){
                $this->load->library('rapyd');
                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $ut= new rpd_auth_library();
                $rt=$ut->logged(1);
                if($rt===false) die('Acceso no permitido');
                $back='dashboard/produ';
                $id_int=$ut->id_int();
                $id_cour = $ut->id_curso();

                $sel=array('a.nombre','a.descripcion');
                $this->db->select($sel);
                $this->db->from('producto AS a');
                $this->db->where('id',$id);
                $this->db->where('id_curso',$id_cour);
                $query = $this->db->get();

                $row = $query->row();

                $menu ='<h2>'.$row->nombre.'</h2>';
                $menu.='<p>'.$row->descripcion.'</p>';

                $sel=array('a.id');
                $this->db->select($sel);
                $this->db->from('compania AS a');
                $this->db->where('id_producto',$id);
                $this->db->where('id_curso',$id_cour);
                $this->db->where('semestre',$ut->semestre());
                $query = $this->db->get();

                if($query->num_rows() > 0){
                        //Guarda la data
                        $submit=$this->input->post('mysubmit');
                        if($submit!==false){
                                $pro     = $this->input->post('promete');
                                $ffech    = $this->input->post('poster');
                                $fech=explode("/",$ffech);
                                                               
                                $body = "Se les informa a los accionistas los nuevos compromisos de la semana: \n";
                                $prometes=explode("\n",$pro);
                                $ffecha  = date('Y-m-d',mktime(0, 0, 0, $fech[1], $fech[0], $fech[2]));
                                foreach($prometes as $promesa){
                                        if(strlen($promesa)>2){
                                                $data = array(
                                                        'id_compania' => 0,
                                                        'compromiso'  => $promesa,
                                                        'id_producto' => $id,
                                                        'fecha'       => $ffecha,
                                                        'control'     => 0,
                                                		'integ'		  => $id_int,
                                                		'id_curso'	  => $id_cour
                                                );
                                                $this->db->insert('compromisos', $data);
                                               
                                                $last_id = mysql_result(mysql_query("SELECT MAX(id) FROM compromisos WHERE integ=$id_int"), 0);
                                                
                                                foreach ($query->result() as $itcomrow){
                                                        $data = array(
                                                                'id_compania' => $itcomrow->id,
                                                                'compromiso'  => $promesa,
                                                                'id_producto' => $id,
                                                                'fecha'       => $ffecha,
                                                                'control'     => $last_id,
                                                        		'integ'		  => $id_int,
                                                        		'id_curso'	  => $id_cour,
                                                        		'semestre'	  => $ut->semestre()
                                                        );
                                                        $this->db->insert('compromisos', $data);
                                                }                              
                                                $body.="$promesa \n";
                                        }
                                }
                           
                        }
                        $menu .= $this->load->view('view_gcompromiso',$conten,true);
                }              
                else{
                        $submit=$this->input->post('mysubmit');
                        if($submit!==false){
                                $pro = $this->input->post('promete');
                                $ffech = $this->input->post('poster');
                                $fech=explode("/",$ffech);
                               
                                $prometes=explode("\n",$pro);
                                $ffecha  = date('Y-m-d',mktime(0, 0, 0, $fech[1], $fech[0], $fech[2]));
                               
                                foreach($prometes as $promesa){
                                        if(strlen($promesa)>2){
                                                $data = array(
                                                'id_compania' => 0,
                                                'compromiso'  => $promesa,
                                                'id_producto' => $id,
                                                'fecha'       => $ffecha,
                                                'control'     => 0,
                                                'integ'		  => $id_int,
                                                'id_curso'    => $id_cour
                                                );
                                                $this->db->insert('compromisos', $data);                                                                                
                                        }
                                }
                        }                      
                        $menu .= $this->load->view('view_gcompromiso',$conten,true);
                }      
               
                $sel=array('id','id_producto','compromiso', 'fecha');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('id_producto',$id);                
                $this->db->where('id_compania',0);
                $this->db->where('control',0);
                $this->db->where('id_curso',$id_cour);
                $this->db->order_by('fecha','asc');
                $this->db->order_by('compromiso','asc');
                $query = $this->db->get();
                if ($query->num_rows() > 0){

                        $menu.= "<h3>Lista de compromisos registrados</h3>";
                        $menu.= "<p>Seleccione el compromiso que desea gestionar</p>";
                        $menu.= "<div class='content-primary'>
                                        <ul data-role='listview' data-split-icon='delete' data-filter='true' data-inset='true'>";

                        foreach ($query->result() as $row){
                                $menu.= '<li><a href="'.site_url('dashboard/compromiso/'.$id.'/'.$row->id).'">'.mdate('%d/%m/%Y', mysql_to_unix($row->fecha)).'  '.$row->compromiso.'</a>
                                <a href="'.site_url('compromisos/dataeditmobil/'.$id.'/delete/'.$row->id).'">Eliminar</a></li>';
                        }
                        $menu.= '</ul></div>';
                }
                else{
                        $menu.= "<h3>No tiene compromisos registrados</h3>";
                }
                $data['content']    = $menu;
                $data['back_url']   = $back;
                $data['header']     = 'Ficha de Producto';
                $data['title']      = 'Ficha de Producto';
                $data['footer']     = '<a href="'.site_url('/producto/dataeditmobil/modify/'.$id).'" data-role="button" data-icon="gear" data-direction="reverse"> Modificar Producto </a>';
                $data['footer']     .= '<a href="'.site_url('/producto/dataeditmobil/delete/'.$id).'" data-role="button" data-icon="delete" data-direction="reverse"> Eliminar</a>';
                $data['headerextra'] = 'Profesor: ';
                $data['headerextra'].= $ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  *     Especificación de compromisos
  *
  * Detalle de todos los compromisos mas la lista de tareas asociadas.
  *
  * @return void
  * @param int      $idprod     Clave primaria de registro en la tabla productos.
  * @param int      $id         Clave primaria de registro en la tabla compromisos.
  */
        public function compromiso($idprod,$id){
                $this->load->library('rapyd');
                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $ut= new rpd_auth_library();
                $rt=$ut->logged(1);
                if($rt===false) die('Acceso no permitido');
                $back='dashboard/producto/'.$idprod;
                $id_int=$ut->id_int();
                $id_cour = $ut->id_curso();

                $sel=array('a.fecha','a.compromiso');
                $this->db->select($sel);
                $this->db->from('compromisos AS a');
                $this->db->where('id',$id);
                $this->db->where('id_curso',$id_cour);
                $query = $this->db->get();
                $row = $query->row();
                $compromiso = $row->compromiso;                
                                
                $this->db->select(array('nombre'));
                $this->db->from('producto');
                $this->db->where('id',$idprod);
                $this->db->where('id_curso',$id_cour);
                $query = $this->db->get();
                $producto= $query->row();

                $menu ='<h2>Compromiso</h2>';
                $menu.='<table align="left" width="550">
                                <tr>
                                        <td>Fecha </td>
                                        <td>'.mdate('%d/%m/%Y', mysql_to_unix($row->fecha)).'</td>
                                </tr>
                                <tr>
                                        <td>Compromiso </td>
                                        <td>'.$compromiso.'</td>
                                </tr>
                                </table><br><br><br>';
               
                $sel=array('id');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('control',$id);
                $this->db->where('id_curso',$id_cour);
                $query = $this->db->get();
               
                //Guarda la data
                $submit=$this->input->post('mysubmit');
                if($submit!==false){
                        $pro     = $this->input->post('promete');
                        $prometes=explode("\n",$pro);
                               
                        foreach($prometes as $promesa){
                                if(strlen($promesa)>2){
                                        $data = array(
                                                'id_compromiso' => $id,
                                                'tarea'     => $promesa,
                                                'peso'           => 1,
                                                'ejecucion'      => 0,
                                                'id_integrante'  => 0,
                                                'registro'       => 'P',
                                                'fecha'                 =>date('Y-m-d',mktime(0, 0, 0, date('m'), date('d')+7, date('Y'))),
                                                'control'        => 0,
                                        		'integ'			 => $id_int
                                                );
                                                $this->db->insert('tareas', $data);
                                                                                               
                                                $last_id = mysql_result(mysql_query("SELECT MAX(id) FROM tareas WHERE integ=$id_int"), 0);
                                                
                                                if($query->num_rows() > 0){
                                                        foreach($query->result() as $row){
                                                                $data = array(
                                                                'id_compromiso' => $row->id,
                                                                'tarea'     => $promesa,
                                                                'peso'           => 1,
                                                                'ejecucion'      => 0,
                                                                'id_integrante'  => 0,
                                                                'registro'       => 'P',
                                                                'fecha'                 =>date('Y-m-d',mktime(0, 0, 0, date('m'), date('d')+7, date('Y'))),
                                                                'control'               => $last_id,
                                                                'integ'			=> $id_int
                                                                );
                                                                $this->db->insert('tareas', $data);
                                                        }                                                      
                                                }                                                                              
                                }
                        }                      
                }
                $menu .= $this->load->view('view_gtarea',$conten,true);
               
                $sel=array('id','id_compromiso','tarea');
                $this->db->select($sel);
                $this->db->from('tareas');
                $this->db->where('id_compromiso',$id);
                $this->db->where('control',0);
                $this->db->order_by('tarea');
                //$this->db->group_by('tarea', 'id_compromiso');
                $query = $this->db->get();
                if ($query->num_rows() > 0){

                        $menu.= "<h3>Lista de tareas registradas</h3>";
                        $menu.= "<p>Seleccione la tarea que desea gestionar</p>";
                        $menu.= "<div class='content-primary'>
                                        <ul data-role='listview' data-split-icon='delete' data-filter='true' data-inset='true'>";
                       
                        foreach ($query->result() as $row){
                                $menu.= '<li><a href="'.site_url('tareas/dataeditmobil/'.$idprod.'/'.$id.'/modify/'.$row->id).'">'.$row->tarea.'</a>
                                <a href="'.site_url('tareas/dataeditmobil/'.$idprod.'/'.$id.'/delete/'.$row->id).'">Eliminar</a></li>';
                        }
                        $menu.= '</ul></div>';
                }
                else{
                        $menu.= "<h3>No tiene tareas registradas</h3>";
                }
                
                $data['content']    = $menu;
                $data['back_url']   = $back;
                $data['header']     = 'Ficha de Compromiso ( '.$producto->nombre.' )';
                $data['title']      = 'Ficha de Compromiso ( '.$producto->nombre.' )';
                $data['footer']     = '<a href="'.site_url('/compromisos/dataeditmobil/'.$idprod.'/modify/'.$id).'" data-role="button" data-icon="gear" data-direction="reverse"> Modificar Compromiso </a>';
                $data['headerextra'] = 'Profesor: ';
                $data['headerextra'].= $ut->user('name');

                $this->load->view('view_ven', $data);
        }




/**
  * Lista de las Auto y Co evaluaciones registradas
  *
  * Contiene información sobre las Auto y Co evaluaciones registradas
  *
  * @return void
  */
        public function autco(){
                $this->load->library('rapyd');
                $this->load->helper('date');

                $ut     = new rpd_auth_library();
                $rt     = $ut->logged(1);
                $id_int = $ut->id_int();
                $id_cour = $ut->id_curso();
                $semestre = $ut->semestre();
                $seccion = $ut->seccion();
                if($rt===false) die('Acceso no permitido');

                $menu ='<div data-role="navbar"><ul>';
                $menu.='<li><a href="'.site_url('dashboard/index').'" data-transition="none">Compa&ntilde;&iacute;as</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/integ').'" data-transition="none">Integrantes</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/produ').'" data-transition="none">Productos</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/autco').'" data-transition="none" class="ui-btn-active">Auto/Co Evaluaci&oacute;n</a></li>';
                $menu.='<li><a href="'.site_url('dashboard/foro').'" data-transition="none">Foro</a></li>';
                $menu.='</ul></div>';

                $menu.= "<h3>Lista de Auto/Co evaluaciones planificadas</h3>";
                $menu.= "<p>Seleccionar para mayor detalle</p>";
                $menu.= "<div class='content-primary'>
                        <ul data-role='listview' data-filter='true' data-inset='true'>";

                $sel=array('a.fecha_inicio','a.comentario','a.plazo','a.id');
                $this->db->select($sel);
                $this->db->from('aucoevaluacion AS a');
                $this->db->where('id_curso',$id_cour);
                $this->db->where('semestre',$ut->semestre());
                $this->db->order_by('a.fecha_inicio','desc');
                $query = $this->db->get();

                $actis=array();
                if ($query->num_rows() > 0){
                        foreach ($query->result() as $row){
                               	$date = date_create($row->fecha_inicio);
                                $fecha= date_format($date, 'd/m/Y');
                                $intev= date_interval_create_from_date_string($row->plazo.' days');
                                date_add($date,$intev);

                                $ffinal = date_format($date, 'd/m/Y');
                                //$menu.= '<li>'.$fecha.' - '.$ffinal.' '.$row->comentario.'</a></li>';
                                $menu.= '<li><a href="'.site_url('aucoevaluacion/dataedit/modify/'.$row->id).'">'.$fecha.' - '.$ffinal.' '.$row->comentario.'</a></li>';
                        }
                }
                $menu.= '</ul></div>';                
                
                $menu.= "<h3>Evaluació del curso</h3>";
                $menu.= "<div data-role='collapsible' data-collapsed='true'>";
                
                $sel=array('AVG(a) AS prom_a','AVG(b) AS prom_b','AVG(c) AS prom_c','AVG(d) AS prom_d','AVG(e) AS prom_e','AVG(f) AS prom_f','AVG(g) AS prom_g',
                'AVG(h) AS prom_h','AVG(i) AS prom_i','AVG(j) AS prom_j','AVG(k) AS prom_k','AVG(l) AS prom_l','AVG(n) AS prom_m', 'AVG(n) AS prom_n','AVG(o) AS prom_o','AVG(p) AS prom_p',
                'AVG(q) AS prom_q','AVG(r) AS prom_r','AVG(s) AS prom_s','AVG(t) AS prom_t','AVG(u) AS prom_u','AVG(v) AS prom_v','AVG(w) AS prom_w','AVG(x) AS prom_x',
                '(AVG(a)+AVG(b)+AVG(c)+AVG(d)+AVG(e)+AVG(f)+AVG(g)+AVG(h)+AVG(i)+AVG(j)+AVG(k)+AVG(l)+AVG(m)+AVG(n)+AVG(o)+AVG(p)+AVG(q)+AVG(r)+AVG(s)+AVG(t)+AVG(u)+AVG(v)+AVG(w)+AVG(x))/25 AS prom_general',
                '(AVG(a)+AVG(b)+AVG(c)+AVG(d)+AVG(e))/5 AS prom1',
                '(AVG(f)+AVG(g)+AVG(h)+AVG(i)+AVG(j)+AVG(k)+AVG(l)+AVG(m)+AVG(n)+AVG(o))/10 AS prom2',
                '(AVG(p)+AVG(q)+AVG(r)+AVG(s))/4 AS prom3',
                '(AVG(t)+AVG(u)+AVG(v)+AVG(w))/4 AS prom4',
                'AVG(x) AS prom5',
                'COUNT(a) AS total_e');
                $this->db->select($sel);
                $this->db->from('evaluacion');
                $this->db->where('id_curso',$id_cour);
                $this->db->where('id_evaluado',$id_int);                
                $this->db->where('semestre',$semestre);
                $query = $this->db->get();                
                
        		if ($query->num_rows() > 0){
                	foreach ($query->result() as $row){
                	                
                    $menu.= '<h3>Semestre '.$semestre.' - Promedio general : '.round($row->prom_general,2).' - '.$this->_rango($row->prom_general).
                    '</h3><p>
                    Evaluados: '.$row->total_e.'<br>
                    <table>
                    <tr><td>Programación y organización del curso</td><td> : '.round($row->prom1,2).'</td></tr>
                    <tr><td>Desempeño y la actitud del profesor	</td><td> : '.round($row->prom2,2).'</td></tr>
                    <tr><td>Manejo y conocimiento de los contenidos</td><td> : '.round($row->prom3,2).'</td></tr>
                    <tr><td>Evaluación de la asignatura	</td><td> : '.round($row->prom4,2).'</td></tr>
                    <tr><td>Calidad del trabajo en gereral</td><td> : '.round($row->prom5,2).'</td></tr>		
                 	</table></p>
                 	<div data-role="collapsible" data-collapsed="true"><h4>Detallada</h4>
                 	<p>
                    <table>
                    <tr><td> Pregunta </td><td> Promedio </td><td> Conclusión </td></tr>
                    <tr><td> Manera de informar sobre el plan de trabajo</td><td> '.round($row->prom_a,2).'</td><td> '.$this->_rango($row->prom_a).'</td></tr>
                    <tr><td> Aplicación y desarrollo del plan de trabajo</td><td> '.round($row->prom_b,2).'</td><td> '.$this->_rango($row->prom_b).'</td></tr>
                    <tr><td> Claridad del programa de la asignatura</td><td> '.round($row->prom_c,2).'</td><td> '.$this->_rango($row->prom_c).'</td></tr>
                    <tr><td> Uso de medios y materiales didácticos</td><td> '.round($row->prom_d,2).'</td><td> '.$this->_rango($row->prom_d).'</td></tr>
                    <tr><td> Organización de las clases</td><td> '.round($row->prom_e,2).'</td><td> '.$this->_rango($row->prom_e).'</td></tr>
                    <tr><td> Cumplimiento de las actividades</td><td> '.round($row->prom_f,2).'</td><td> '.$this->_rango($row->prom_f).'</td></tr>
                    <tr><td> Puntualidad según el horario pautado</td><td> '.round($row->prom_g,2).'</td><td> '.$this->_rango($row->prom_g).'</td></tr>
                    <tr><td> Forma de explicar</td><td> '.round($row->prom_h,2).'</td><td> '.$this->_rango($row->prom_h).'</td></tr>
                    <tr><td> Dinámica y desarrollo de las clases</td><td> '.round($row->prom_i,2).'</td><td> '.$this->_rango($row->prom_i).'</td></tr>
                    <tr><td> Manera de transmitir interés y motivación</td><td> '.round($row->prom_j,2).'</td><td> '.$this->_rango($row->prom_j).'</td></tr>
                    <tr><td> Atención y disposición hacia las consultas fuera de clases</td><td> '.round($row->prom_k,2).'</td><td> '.$this->_rango($row->prom_k).'</td></tr>
                    <tr><td> Actitud hacia las opiniones, inquietudes y preguntas</td><td> '.round($row->prom_l,2).'</td><td> '.$this->_rango($row->prom_l).'</td></tr>
                    <tr><td> Trato hacia los estudiantes</td><td> '.round($row->prom_m,2).'</td><td> '.$this->_rango($row->prom_m).'</td></tr>
                    <tr><td> Fomenta la participación en clase</td><td> '.round($row->prom_n,2).'</td><td> '.$this->_rango($row->prom_n).'</td></tr>
                    <tr><td> Interés del profesor por la asignatura</td><td> '.round($row->prom_o,2).'</td><td> '.$this->_rango($row->prom_o).'</td></tr>
                    <tr><td> Dominio del contenido de la asignatura</td><td> '.round($row->prom_p,2).'</td><td> '.$this->_rango($row->prom_p).'</td></tr>
                    <tr><td> Vocabulario con el que se expresa</td><td> '.round($row->prom_q,2).'</td><td> '.$this->_rango($row->prom_q).'</td></tr>
                    <tr><td> Relación de los contenidos con el ejercicio profesional</td><td> '.round($row->prom_r,2).'</td><td> '.$this->_rango($row->prom_r).'</td></tr>
                    <tr><td> Nivel de profundidad en los contenidos</td><td> '.round($row->prom_s,2).'</td><td> '.$this->_rango($row->prom_s).'</td></tr>
                    <tr><td> Planificación de las evaluaciones</td><td> '.round($row->prom_t,2).'</td><td> '.$this->_rango($row->prom_t).'</td></tr>
                    <tr><td> Estrategias de evaluación de la asignatura</td><td> '.round($row->prom_u ,2).'</td><td> '.$this->_rango($row->prom_u).'</td></tr>
                    <tr><td> Claridad en los criterios y objetivos usados en las evaluaciones</td><td> '.round($row->prom_v,2).'</td><td> '.$this->_rango($row->prom_v).'</td></tr>
                    <tr><td> Manera de transmitir los resultados de las evaluaciones</td><td> '.round($row->prom_w,2).'</td><td> '.$this->_rango($row->prom_w).'</td></tr>
                    <tr><td> Trabajo desarrollado por el profesor</td><td> '.round($row->prom_x,2).'</td><td> '.$this->_rango($row->prom_x).'</td></tr>
		
                 	</table></p>
                 	';
                    }
                }else $menu.='No tiene evaluación';
                
                $menu.= '</div>';

                $data['content'] = $menu;
                $data['footer']  = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
                $data['footer'] .= '<a href="'.site_url('/aucoevaluacion/dataedit/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar</a>';
                $data['header']  = $this->_curso(1).'::Panel de control';
                $data['title']   = $this->_curso(1);
                $data['logout']  = $this->_curso(2);
                $data['usuario'] = $ut->user('name');
                $data['headerextra']= 'Profesor: '.$ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Página que genera el foro del sistema a partir de phpBB3
  *
  * @return void
  */
	public function foro(){
		$this->load->helper('form');
		$this->load->library('rapyd');
		$this->load->library('phpbb');

		$ut     = new rpd_auth_library();
		$rt     = $ut->logged(1);
		$id_int = $ut->id_int();
		if($rt===false) die('Acceso no permitido');

		$menu ='<div data-role="navbar"><ul>';
		$menu.='<li><a href="'.site_url('dashboard/index').'" data-transition="none">Compa&ntilde;&iacute;as</a></li>';
		$menu.='<li><a href="'.site_url('dashboard/integ').'" data-transition="none">Integrantes</a></li>';
		$menu.='<li><a href="'.site_url('dashboard/produ').'" data-transition="none">Productos</a></li>';
		$menu.='<li><a href="'.site_url('dashboard/autco').'" data-transition="none">Auto/Co Evaluaci&oacute;n</a></li>';
		$menu.='<li><a href="'.site_url('dashboard/foro').'" data-transition="none" class="ui-btn-active">Foro</a></li>';
		$menu.='</ul></div>';
		
		$serv = "http://".$_SERVER['SERVER_NAME'] . "/";

		$menu.='<div class="ui-content" align="center">
			    <iframe onload="javascript:resize()" src="'.$serv.'gerais-v2/foro/" height="100%" width="100%" frameborder="0" scrolling="auto" id="iframeforo" name="iframeforo"></iframe>
			</div>';

		$data['content']    = $menu;
		$data['footer']     = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
		$data['header']     = $this->_curso(1).'::Panel de control';
		$data['title']      = $this->_curso(1);
		$data['logout']     = $this->_curso(2);
		$data['usuario']    = $ut->user('name');
		$data['headerextra']= 'Profesor: '.$ut->user('name');


		$this->load->view('view_ven', $data);
	}

/**
  * Método que genera el medidor
  *
  *
  * @param  float $val Valor a graficar, se asume que esta entre 0 y 100.
  * @return string
  */
        public function gauge($val=0){
                $this->load->library('dial_gauge',array('value'=>$val));
                header('Content-Type: image/png');
                $this->dial_gauge->display_png();
        }

/**
  * Método que permite evaluar el desempeño de la compañía
  *
  * @param  int $compania Clave primaria del registro en la tabla compania.
  * @return void
  */
        public function gcompania($compania){
                $this->load->library('rapyd');
                $ut= new rpd_auth_library();
                $rt=$ut->logged(1);
                $id_cour = $ut->id_curso();
                $id_int = $ut->id_int();
                if($rt===false) die('Acceso no permitido');

                $this->db->where('id', $compania);
                $this->db->from('compania');
                if($this->db->count_all_results()==0){
                        ci_redirect('dashboard');
                }

                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $conten=array();

                //Saca los integrates y producto
                $sel=array('a.nombre AS nombre','c.id AS idprod','c.nombre AS producto','c.descripcion','GROUP_CONCAT(b.nombre ORDER BY b.nombre) AS integrantes','GROUP_CONCAT(b.correo) AS correos');
                $this->db->select($sel);
                $this->db->from('compania AS a');
                $this->db->join('integcurso AS d','a.id=d.id_compania','left');
                $this->db->join('integrantes AS b','d.id_integrante=b.id AND tipo=\'A\'','left');
                $this->db->join('producto AS c','a.id_producto=c.id','left');
                $this->db->where('a.id',$compania);
                $this->db->where('a.id_curso',$id_cour);
                $this->db->where('a.semestre',$ut->semestre());
                $query = $this->db->get();
                $row = $query->row();

                $conten['role']       = 1;
                $conten['nombre']     =$row->nombre;
                $conten['integrantes']=$row->integrantes;
                $conten['producto']   =$row->producto;
                $conten['pnombre']    =$row->descripcion;
                $conten['correos']    =$row->correos;
                $idprod=$row->idprod;

                //Guarda la data
                $submit=$this->input->post('mysubmit');
                if($submit!==false){
                        $compros = $this->input->post('cpr');
                        if(!empty($compros)){
                                foreach($compros as $id=>$avance){
                                        if(is_numeric($avance)){
                                                if($avance>100) $avance=100;
                                                $data = array('ejecucion' => $avance);

                                                $this->db->where('id', $id);
                                                $this->db->update('compromisos', $data);
                                        }
                                }
                        }

                        $pro     = $this->input->post('promete');
                        $ffech    = $this->input->post('poster');
                        $fech=explode("/",$ffech);

                        if(!empty($pro)){
                                $dias = ($dias>0)? $dias : 7;
                                $body = "Se les informa a los accionistas los nuevos compromisos de la semana: \n";
                                $prometes=explode("\n",$pro);
                                $ffecha  = date('Y-m-d',mktime(0, 0, 0, $fech[1], $fech[0], $fech[2]));
                                foreach($prometes as $promesa){
                                        if(strlen($promesa)>2){
                                                if($publica=='1' && !empty($idprod)){
                                                        $this->db->select(array('a.id AS id_compa'));
                                                        $this->db->from('compania AS a');
                                                        $this->db->where('id_curso',$id_cour);
                                                        $this->db->where('a.semestre',$ut->semestre());
                                                        $this->db->where('a.id_producto',$idprod);
                                                        $this->db->where('a.id <>',$compania);
                                                        $query = $this->db->get();

                                                        if ($query->num_rows() > 0){
                                                                foreach ($query->result() as $itcomrow){
                                                                        $data = array(
                                                                                'id_compania' => $itcomrow->id_compa,
                                                                                'compromiso'  => $promesa,
                                                                                'ejecucion'   => 0,
                                                                                'id_producto' => (!empty($idprod))? $idprod : 0,
                                                                                'fecha'       => $ffecha,
                                                                        		'id_curso'	  => $id_cour,
                                                                        		'integ'		  => $id_int,
                                                                        		'semestre'	  => $ut->semestre()
                                                                        );
                                                                        $this->db->insert('compromisos', $data);
                                                                }
                                                        }
                                                }
                                                $data = array(
                                                        'id_compania' => $compania,
                                                        'compromiso'  => $promesa,
                                                        'ejecucion'   => 0,
                                                        'id_producto' => (!empty($idprod))? $idprod : 0,
                                                        'fecha'       => $ffecha,
                                                		'id_curso'	  => $id_cour,
                                                		'integ'		  => $id_int,
                                                		'semestre'	  => $ut->semestre()
                                                        );

                                                $this->db->insert('compromisos', $data);
                                                $body.="$promesa \n";

                                        }
                                }
                                //if(!empty($correos)){
                                //      $this->_mail($correos,'GeRAIS:: Compromiso Semanal',$body);
                                //}
                        }
                }

                //Saca el promedio de avance
                $sel=array('AVG(ejecucion) AS prom');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('id_curso',$id_cour);
                $this->db->where('id_compania',$compania);
                $this->db->where('fecha <= CURDATE()');
                $query = $this->db->get();
                $row   = $query->row();
                $conten['prom']  = round($row->prom,1);

                //Saca la salud
                $dbprefix=$this->db->dbprefix;
                $dbcompania=$this->db->escape($compania);
                $mSQL="SELECT VARIANCE(aa.prom) FROM
                (SELECT
                b.id AS id_integrante,
                IF(a.id IS NULL,0,SUM(a.ejecucion*a.peso)/SUM(a.peso)) AS prom
                FROM ${dbprefix}tareas   AS a
                RIGHT JOIN ${dbprefix}integrantes AS b ON a.id_integrante=b.id
                WHERE b.id_compania=$dbcompania
                GROUP BY b.id)
                AS aa";
                $row   = $query->row();
                $conten['salud']  = round(sqrt($row->prom),1);

                //Saca los compromisos faltantes
                $sel=array('compromiso','ejecucion','fecha','id','UNIX_TIMESTAMP(fecha) AS uts');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('id_compania',$compania);
                $this->db->where('id_curso',$id_cour);
                if(!empty($idprod) && $idprod>0)
                        $this->db->where('id_producto',$idprod);
                $this->db->where('ejecucion <'  ,100);
                $this->db->order_by('fecha','asc');
                $this->db->order_by('compromiso','asc');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['ecomprom']=$query->result();
                }else{
                        $conten['ecomprom']=array();
                }

                //Saca los compromisos terminados
                $sel=array('compromiso','ejecucion','fecha','id','UNIX_TIMESTAMP(fecha) AS uts');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('id_compania',$compania);
                $this->db->where('id_curso',$id_cour);
                if(!empty($idprod) && $idprod>0)
                        $this->db->where('id_producto',$idprod);
                $this->db->where('ejecucion'  ,100);
                $this->db->order_by('fecha','desc');
                $this->db->order_by('id');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['comprom']=$query->result();
                }else{
                        $conten['comprom']=array();
                }

                //Saca todos los informes ISA
                $sel=array('d.id AS integrante','e.seccion','b.id AS idcomp','b.tarea','a.id','d.nombre','d.apellido','c.fecha');
                $this->db->select($sel);
                $this->db->from('tarearesol AS a');
                $this->db->join('tareas AS b','a.id_tarea=b.id');
                $this->db->join('compromisos    AS c','b.id_compromiso=c.id'  );
                $this->db->join('integrantes    AS d','b.id_integrante=d.id'   );
                $this->db->join('integcurso    AS e','d.id=e.id_integrante'   );
                $this->db->where('c.id_compania',$compania);
                $this->db->where('c.id_curso',$id_cour);
                $this->db->where('e.semestre',$ut->semestre());
                $this->db->order_by('d.id');
                $this->db->order_by('a.fecha','desc');
                //$this->db->group_by('integrante');
                //$this->db->order_by('a.id');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['isas']=$query->result();
                }else{
                        $conten['isas']=array();
                }

                //Saca los compromisos de otros productos
                if(!empty($idprod) && $idprod>0){
                        $sel=array('a.compromiso','a.ejecucion','a.fecha','a.id'
                        ,'UNIX_TIMESTAMP(a.fecha) AS uts','b.nombre AS prodnom');
                        $this->db->select($sel);
                        $this->db->from('compromisos AS a');
                        $this->db->join('producto AS b','a.id_producto=b.id','left');
                        $this->db->where('a.id_curso',$id_cour);
                        $this->db->where('a.id_compania'   ,$compania);
                        $this->db->where('a.id_producto <>',$idprod);
                        $this->db->order_by('a.fecha','desc');
                        $this->db->order_by('a.id');
                        $query = $this->db->get();

                        if ($query->num_rows() > 0){
                                $conten['antcomprom']=$query->result();
                        }else{
                                $conten['antcomprom']=array();
                        }
                }else{
                        $conten['antcomprom']=array();
                }

                $conten['compania']   = $compania;

                $data['content']  = $this->load->view('view_gcompania',$conten,true);
                $data['back_url'] = 'dashboard/index';
                $data['header']   = 'Gerencia de Compa&ntilde;&iacute;a';
                $data['title']    = 'Gerencia de Compa&ntilde;&iacute;a';
                $data['footer']   = '<a href="'.site_url('companias/dataeditmobil/modify/'.$compania).'" data-role="button" data-icon="gear" data-direction="reverse">Editar Compañía</a>';
                $data['headerextra']= 'Profesor: '.$ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Método que permite evaluar el desempeño particular de un integrante
  *
  * @param  int $id_int Clave primaria del registro en la tabla integrante.
  * @return void
  */
        public function gintegrante($id_int){
                $this->load->library('rapyd');
                $ut= new rpd_auth_library();
                $rt=$ut->logged(1);
                $id_cour = $ut->id_curso();
                if($rt===false) die('Acceso no permitido');

                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $conten=array();

                //Saca los integrates y producto
                $sel=array('a.nombre AS nombre','c.nombre AS producto','c.descripcion','CONCAT_WS(" ",b.nombre,b.apellido) AS integrantes','GROUP_CONCAT(b.correo) AS correos','a.id AS id_compania');
                $this->db->select($sel);
                $this->db->from('compania AS a');
                $this->db->join('integcurso AS d','a.id=d.id_compania');
                $this->db->join('integrantes AS b','d.id_integrante=b.id AND tipo=\'A\'');
                $this->db->join('producto AS c','a.id_producto=c.id','left');
                $this->db->where('d.id_curso',$id_cour);
                $this->db->where('a.semestre',$ut->semestre());
                $this->db->where('b.id',$id_int);
                $query = $this->db->get();
                $row = $query->row();

                $conten['nombre']     =$row->nombre;
                $conten['integrantes']=$row->integrantes;
                $conten['producto']   =$row->producto;
                $conten['pnombre']    =$row->descripcion;
                $conten['correos']    =$row->correos;
                $conten['id_compania']=$row->id_compania;

                //Saca el promedio de avance
                $sel=array('SUM(IF(b.ejecucion=100,100,a.ejecucion)*a.peso)/SUM(a.peso) AS prom');
                $this->db->select($sel);
                $this->db->from('tareas AS a');
                $this->db->join('compromisos AS b','a.id_compromiso=b.id');
                $this->db->where('b.id_curso',$id_cour);
                $this->db->where('a.id_integrante',$id_int);
                $this->db->where('b.fecha <= CURDATE()');
                $query = $this->db->get();
                $row   = $query->row();
                $conten['prom']  = round($row->prom,1);

                //Saca las penalizaciones
                $sel=array('a.fecha','b.compromiso','a.exonerada','a.id');
                $this->db->select($sel);
                $this->db->from('penalizaciones AS a');
                $this->db->join('compromisos    AS b','a.id_compromiso=b.id');
                $this->db->where('b.id_curso',$id_cour);
                $this->db->where('a.id_integrante',$id_int);
                $this->db->order_by('fecha','desc');
                $this->db->order_by('id');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['penaliza']=$query->result();
                }else{
                        $conten['penaliza']=array();
                }

                //Saca las evaluaciones
                $sel=array('resultado','fecha');
                $this->db->select($sel);
                $this->db->from('aucoevaluacion_it');
                $this->db->where('id_evaluado',$id_int);
                $this->db->order_by('fecha','desc');
                $this->db->order_by('id');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['evalu']=$query->result();
                }else{
                        $conten['evalu']=array();
                }

                //Saca los compromisos faltantes
                $sel=array('a.compromiso','b.tarea AS tarea','b.peso',
                                   'a.ejecucion' ,'b.ejecucion AS subejecucion'  ,
                                   'b.fecha AS subfecha','b.id AS subid','c.id AS id_resol',
                                   'a.fecha','a.id','UNIX_TIMESTAMP(a.fecha) AS uts');
                $this->db->select($sel);
                $this->db->from('compromisos    AS a');
                $this->db->join('tareas AS b','b.id_compromiso=a.id');
                $this->db->join('tarearesol AS c','c.id_tarea=b.id AND c.reasignacion=a.reasignado','left');
                $this->db->where('a.id_curso',$id_cour);
                $this->db->where('b.id_integrante', $id_int);
                $this->db->order_by('a.fecha','desc');
                $this->db->order_by('a.id');
                $this->db->group_by('a.compromiso');
                $query = $this->db->get();
                //echo $this->db->last_query();
                if ($query->num_rows() > 0){
                        $conten['ecomprom']=$query->result();
                }else{
                        $conten['ecomprom']=array();
                }

        		//Saca los isa
                $sel=array('a.tarea','b.hizo','b.problema','b.promete','b.fecha','b.id','a.id AS idcomp','c.id_curso', 'c.id_compania');
                $this->db->select($sel);
                $this->db->from('tareas AS a');
                $this->db->join('tarearesol AS b','a.id=b.id_tarea');
                $this->db->join('compromisos AS c','a.id_compromiso=c.id');
                $this->db->where('a.id_integrante',$id_int);
                $this->db->where('c.id_curso',$id_cour);
                $this->db->order_by('fecha');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $conten['isa']=$query->result();
                }else{
                        $conten['isa']=array();
                }

                $conten['role']   = 1;
                $conten['id_int'] = $id_int;
                $data['content']  = $this->load->view('view_gintegrante',$conten,true);
                $data['back_url'] = 'dashboard/integ';
                $data['header']   = 'Gestión de integrantes';
                $data['title']    = 'Gestión de integrantes';
                $data['footer']   = '<a href="'.site_url('/integrantes/dataedit/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Editar Integrante</a>';
                $data['headerextra']= 'Profesor: '.$ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Método que muestra información pertinente a los alumnos
  *
  * Muestra la lista de comrpomisos que debe cumplir el alumno junto
  * con los compromisos asociados a su empresa y la lista de isas registrados
  *
  * @return void
  */
        public function gcompalu(){
                $this->load->library('rapyd');
                $ut= new rpd_auth_library();
                $rt=$ut->logged();
                if($rt===false) die('Acceso no permitido');
                $id_int = $ut->id_int();
                $id_cour = $ut->id_curso();
                $role   = $ut->role();
                $semestre = $ut->semestre();

                $compania=$ut->id_comp();
                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $conten=array();
                $conten['role']=$role;

                //Saca los integrates y producto
                $sel=array('a.nombre AS nombre','c.nombre AS producto','c.descripcion'
                ,'GROUP_CONCAT(b.nombre ORDER BY b.nombre) AS integrantes','GROUP_CONCAT(b.correo) AS correos'
                ,'a.id_producto AS idprod');
                $this->db->select($sel);
                $this->db->from('compania AS a');
                $this->db->join('integcurso AS d','a.id=d.id_compania');
                $this->db->join('integrantes AS b','d.id_integrante=b.id AND tipo=\'A\'');
                $this->db->join('producto AS c','a.id_producto=c.id','left');
                $this->db->where('d.id_curso',$id_cour);
                $this->db->where('d.semestre',$ut->semestre());
                $this->db->where('d.id_compania',$compania);
                $query = $this->db->get();
                $row = $query->row();

                $conten['nombre']     =$row->nombre;
                $conten['integrantes']=$row->integrantes;
                $conten['producto']   =$row->producto;
                $conten['pnombre']    =$row->descripcion;
                $conten['correos']    =$row->correos;
                $idprod = $row->idprod;

                //Saca el promedio de avance
                $sel=array('AVG(ejecucion) AS prom');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('id_curso',$id_cour);
                $this->db->where('id_compania',$compania);
                $this->db->where('fecha <= CURDATE()');
                $query = $this->db->get();
                $row   = $query->row();
                $conten['prom']  = round($row->prom,1);

                //Saca las tareas faltantes propios
                $sel=array('a.compromiso','b.tarea AS tarea',
                                   'a.ejecucion' ,'b.ejecucion AS subejecucion'  ,
                                   'b.fecha AS subfecha','b.id AS subid','c.id AS id_resol',
                                   'a.fecha','a.id','UNIX_TIMESTAMP(a.fecha) AS uts');
                $this->db->select($sel);
                $this->db->from('compromisos    AS a');
                $this->db->join('tareas AS b','b.id_compromiso=a.id');
                $this->db->join('tarearesol AS c','c.id_tarea=b.id AND c.reasignacion=a.reasignado','left');
                $this->db->where('a.id_curso',$id_cour);
                $this->db->where('a.id_compania'  , $compania);
                $this->db->where('a.ejecucion <'  , 100);
                if(!empty($idprod) && $idprod>0)
                        $this->db->where('a.id_producto',$idprod);
                $this->db->where('b.id_integrante', $id_int);
                $this->db->order_by('a.fecha');
                $this->db->order_by('a.id');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['ecomprom']=$query->result();
                }else{
                        $conten['ecomprom']=array();
                }

                //Saca los todos compromisos
                $sel=array('compromiso','ejecucion','fecha','id','UNIX_TIMESTAMP(fecha) AS uts');
                $this->db->select($sel);
                $this->db->from('compromisos');
                $this->db->where('id_curso',$id_cour);
                $this->db->where('id_compania',$compania);
                if(!empty($idprod) && $idprod>0)
                        $this->db->where('id_producto',$idprod);
                $this->db->order_by('fecha');
                $this->db->order_by('id');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $conten['comprom']=$query->result();
                }else{
                        $conten['comprom']=array();
                }

                //Saca los isa
                $sel=array('a.tarea','b.hizo','b.problema','b.promete','b.fecha','b.id','a.id AS idcomp','c.id_curso');
                $this->db->select($sel);
                $this->db->from('tareas AS a');
                $this->db->join('tarearesol AS b','a.id=b.id_tarea');
                $this->db->join('compromisos AS c','a.id_compromiso=c.id');
                $this->db->where('a.id_integrante',$id_int);
                $this->db->where('c.id_curso',$id_cour);
                $this->db->where('c.semestre',$ut->semestre());
                $this->db->order_by('fecha');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $conten['isa']=$query->result();
                }else{
                        $conten['isa']=array();
                }
                $fecha=date('Y-m-d');
                $conten['compania']   = $compania;

                $this->db->select(array('a.id','a.comentario','a.fecha_inicio AS inicio','ADDDATE(a.fecha_inicio,plazo) AS final'));
                $this->db->from('aucoevaluacion AS a');
                $this->db->join('aucoevaluacion_it AS b','b.id_aucoevaluacion=a.id AND b.id_evaluador='.$this->db->escape($id_int),'left');
                $this->db->where('ADDDATE(a.fecha_inicio,plazo) >=',$fecha);
                $this->db->where('a.fecha_inicio <=',$fecha);
                $this->db->where('b.id IS NULL');
                $this->db->where('a.id_curso',$id_cour);
                $this->db->where('a.semestre',$ut->semestre());
                
                $query = $this->db->get();

                if($query->num_rows() > 0){
                        $itrow = $query->row();
                        $conten['auco']  = '<p style="text-align:center;"><b>Desde el '.mdate('%d/%m/%Y', mysql_to_unix($itrow->inicio)).' hasta '.mdate('%d/%m/%Y', mysql_to_unix($itrow->final)).'</b><br />';
                        $conten['auco'] .= '<a href="'.site_url('aucoevaluacion/ejecuta').'" data-role="button" data-icon="star" data-direction="reverse" data-theme="e" title="'.$itrow->comentario.'">Auto/Co Evaluaci&oacute;n - '.$itrow->comentario.'</a></p>';
                }
                
                $this->db->from('evaluacion');	
				$this->db->where('id_curso',$id_cour);
				$this->db->where('id_evaluador',$id_int);
				$this->db->where('semestre',$semestre);
				
				$existe=$this->db->count_all_results();				
				                                
                $this->db->from('curso');
                $this->db->where('id',$id_cour);
                $this->db->where('eval',1);
                $query = $this->db->get();
                
                if($query->num_rows() > 0 && $existe==0){
                	$conten['eval'] = '<a href="'.site_url('curso/evaluacion/create').'" data-role="button" data-icon="star" data-direction="reverse" data-theme="e">Evaluaci&oacute;n del curso</a>';
                }

                //Saca todos los informes ISA restantes que no pertenecen al usuario
                $sel=array('d.id AS integrante','b.id AS idcomp','b.tarea','a.id',
                "IF($id_int=d.id,'Tuyos',d.nombre) AS nombre",
                "IF($id_int=d.id,'',d.apellido) AS apellido",
                'c.fecha');
                $this->db->select($sel);
                $this->db->from('tarearesol AS a');
                $this->db->join('tareas AS b','a.id_tarea=b.id');
                $this->db->join('compromisos    AS c','b.id_compromiso=c.id'  );
                $this->db->join('integrantes    AS d','b.id_integrante=d.id'   );
                $this->db->where('c.id_compania',$compania);
                $this->db->where('c.id_curso',$id_cour);
                $this->db->order_by('d.id');
                $this->db->order_by('a.fecha');
                $query = $this->db->get();
                if ($query->num_rows() > 0){
                        $conten['isas']=$query->result();
                }else{
                        $conten['isas']=array();
                }

                //Saca los compromisos de otros productos
                if(!empty($idprod) && $idprod>0){
                        $sel=array('a.compromiso','a.ejecucion','a.fecha','a.id'
                        ,'UNIX_TIMESTAMP(a.fecha) AS uts','b.nombre AS prodnom');
                        $this->db->select($sel);
                        $this->db->from('compromisos AS a');
                        $this->db->join('producto AS b','a.id_producto=b.id','left');
                        $this->db->where('a.id_curso',$id_cour);
                        $this->db->where('a.id_compania'   ,$compania);
                        $this->db->where('a.id_producto <>',$idprod);
                        $this->db->order_by('a.fecha','desc');
                        $this->db->order_by('a.id');
                        $query = $this->db->get();

                        if ($query->num_rows() > 0){
                                $conten['antcomprom']=$query->result();
                        }else{
                                $conten['antcomprom']=array();
                        }
                }else{
                        $conten['antcomprom']=array();
                }

                $conten['role']       = $role;
                $conten['id_compania']= $ut->id_comp();
                $data['content']  = $this->load->view('view_gcompalu',$conten,true);
                $data['header']   = $this->_curso(1).'::Gerencia de Compa&ntilde;&iacute;a';
                $data['title']    = 'Gerencia de Compa&ntilde;&iacute;a';
                $data['footer']   = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';          
                $data['footer']   .= '<a href="'.site_url('/dashboard/gcompalu_foro/').'" data-role="button" data-icon="grid" data-direction="reverse">Foro</a>';
                $data['home_url'] = '';
                $data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
                $data['headerextra'].= $ut->user('name');

                $data['logout']  = $this->_curso(2);
                $data['usuario'] = $ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Página que genera el foro para usuarios tipo alumno del sistema a partir de phpbb3
  *
  * @return void
  */
 public function gcompalu_foro(){
		$this->load->helper('form');
		$this->load->library('rapyd');
		$this->load->library('phpbb');
		
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		$id_int = $ut->id_int();
		$role   = $ut->role();

		$serv = "http://".$_SERVER['SERVER_NAME'] . "/";

		$menu.='<div class="ui-content" align="center">
			    <iframe onload="javascript:resize()" src="'.$serv.'gerais-v2/foro/" height="100%" width="100%" frameborder="0" scrolling="auto" id="iframeforo" name="iframeforo"></iframe>
			</div>';

		$data['content']    = $menu;
		$data['header']     = $this->_curso(1).'::Panel de control';
		$data['title']      = $this->_curso(1);
		$data['logout']     = $this->_curso(2);
		$data['usuario']    = $ut->user('name');
		$data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
		$data['headerextra'].= $ut->user('name');
		$data['footer']     = '<a href="'.site_url('/integrantes/modif/modify/'.$id_int).'" data-role="button" data-icon="gear" data-direction="reverse">Configurar cuenta</a>';
		$data['footer']     .= '<a href="'.site_url('/dashboard/gcompalu/').'" data-role="button" data-icon="back" data-direction="reverse">Compañia</a>';


		$this->load->view('view_ven', $data);
	}



/**
  * Método para evaluar la microgerencia por parte del profesor
  *
  * Permite darle porcentajes de avances y pesos a las tareas registradas,
  * adicionalmente permite registrar nuevas tareas y delegarlos a un integrante
  *
  *
  * @param  int $compromiso Clave primaria del registro en la tabla compromiso.
  * @return void
  */
        public function gsubcompania($compromiso){
                $this->load->library('rapyd');
                $ut= new rpd_auth_library();
                $rt=$ut->logged(1);
                if($rt===false) die('Acceso no permitido');
                $role   = $ut->role();
                $id_cour = $ut->id_curso();

                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $conten=array();
                $conten['role']=$role;

                //Guarda la data
                $submit=$this->input->post('mysubmit');
                if($submit!==false){
                        $compros=$this->input->post('cpr');
                        $pesos  =$this->input->post('pes');
                        if(!empty($compros)){
                                foreach($compros as $id=>$avance){
                                        if(is_numeric($avance)){
                                                $data = array('ejecucion' => $avance);
                                                if(is_numeric($pesos[$id]) && $pesos[$id]>=1 && $pesos[$id]<=100) $data['peso'] = $pesos[$id];
                                                if($avance>100) $avance=100;

                                                $this->db->where('id', $id);
                                                $this->db->update('tareas', $data);
                                        }
                                }
                        }

                        $pro=$this->input->post('promete');
                        if(!empty($pro)){
                                $body="Se les informa a los accionistas los nuevos compromisos de la semana: \n";
                                $prometes=explode("\n",$pro);
                                foreach($prometes as $promesa){
                                        if(strlen($promesa)>2){
                                                $data = array(
                                                        'id_compromiso' => $compromiso,
                                                        'tarea'     => $promesa,
                                                        'peso'           => 1,
                                                        'ejecucion'      => 0,
                                                        'id_integrante'  => 0,
                                                        'registro'       => 'P',
                                                        'fecha'=>date('Y-m-d',mktime(0, 0, 0, date('m'), date('d')+7, date('Y')))
                                                        );

                                                $this->db->insert('tareas', $data);
                                                $body.="$promesa \n";
                                        }
                                }
                                //if(!empty($correos)){
                                //      $this->_mail($correos,'GeRAIS:: Compromiso Semanal',$body);
                                //}
                        }
                }

                $sel=array('a.fecha','a.compromiso','a.id_compania'
                        ,'a.ejecucion','c.nombre','c.descripcion','c.id','b.nombre AS companianom');
                $this->db->select($sel);
                $this->db->from('compromisos AS a');
                $this->db->join('compania    AS b','a.id_compania=b.id');
                $this->db->join('producto    AS c','b.id_producto=c.id','left');
                $this->db->where('a.id',$compromiso);
                $this->db->where('a.id_curso',$id_cour);
                $this->db->where('b.semestre',$ut->semestre());
                $query = $this->db->get();
                $row = $query->row();

                $conten['fecha']      = $row->fecha      ;
                $conten['compromiso'] = $row->compromiso ;
                $conten['ejecucion']  = $row->ejecucion  ;
                $conten['nombre']     = $row->nombre     ;
                $conten['descrip']    = $row->descripcion;
                $conten['id_comp']    = $compromiso      ;
                $conten['companianom']= $row->companianom;
                $id_compania          = $row->id_compania;
                $id_producto          = $row->id         ;
                $conten['id_compania']= $id_compania;

                //Saca los tareas
                $sel=array('a.tarea','a.ejecucion','a.fecha','a.id','a.peso','MAX(c.id) AS idresol'
                ,'UNIX_TIMESTAMP(a.fecha) AS uts','CONCAT_WS(\' \',nombre,apellido) AS nombre');
                $this->db->select($sel);
                $this->db->from('tareas AS a');
                $this->db->join('integrantes    AS b','a.id_integrante   =b.id','left');
                $this->db->join('tarearesol AS c','c.id_tarea=a.id','left');
                //$this->db->where('b.id_curso',$id_cour);
                $this->db->where('a.id_compromiso',$compromiso);
                $this->db->order_by('a.fecha','asc');
                $this->db->order_by('a.id','asc');
                $this->db->group_by('a.tarea');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $conten['ecomprom']=$query->result();
                }else{
                        $conten['ecomprom']=array();
                }

                //Saca los problemas
                $sel=array('a.fecha','a.id','a.problema');
                $this->db->select($sel);
                $this->db->from('problemas AS a');
                $this->db->where('a.id_compromiso',$compromiso);
                $this->db->order_by('a.fecha','asc');
                $this->db->order_by('a.id');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $conten['problemas']=$query->result();
                }else{
                        $conten['problemas']=array();
                }

                $data['content']  = $this->load->view('view_gsubcompania',$conten,true);
                $data['back_url'] = 'dashboard/gcompania/'.$id_compania;
                $data['header']   = 'Sub-Gerencia de Compa&ntilde;&iacute;a';
                $data['title']    = 'Sub-Gerencia de Compa&ntilde;&iacute;a';
                $data['footer']   = '<a href="'.site_url('compromisos/dataedit/modify/'.$compromiso).'" data-role="button" data-icon="gear" data-direction="reverse">Editar compromiso</a>';
                $data['footer']  .= '<a href="'.site_url('problemas/dataeditmobil/'.$compromiso.'/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Problema</a>';
                $data['headerextra'] = 'Profesor: ';
                $data['headerextra'].= $ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Método para sub-delegar los compromisos por parte del alumno
  *
  * Permite darle pesos a las tareas registradas,
  * adicionalmente permite registrar nuevas tareas y delegarlos a un integrante
  *
  *
  * @param  int $compromiso Clave primaria del registro en la tabla compromiso.
  * @return void
  */
        public function gsubcompalu($compromiso){
                $this->load->library('rapyd');
                $ut= new rpd_auth_library();
                $rt=$ut->logged();
                $id_cour = $ut->id_curso();

                if($rt===false) die('Acceso no permitido');
                $role  = $ut->role();
                if($role>2)     die('Acceso no permitido');

                $this->load->library('calendar');
                $this->load->helper('date');
                $this->load->helper('url');
                $this->load->helper('html');
                $this->load->helper('form');
                $conten=array();
                $conten['role']=$role;

                $sel=array('a.fecha','a.compromiso','a.id_compania','a.ejecucion','c.nombre','c.descripcion','c.id');
                $this->db->select($sel);
                $this->db->from('compromisos AS a');
                $this->db->join('compania    AS b','a.id_compania=b.id');
                $this->db->join('producto    AS c','b.id_producto=c.id','left');
                
                $this->db->where('a.id',$compromiso);
                $query = $this->db->get();
                $row = $query->row();

                $conten['fecha']      = $row->fecha      ;
                $conten['compromiso'] = $row->compromiso ;
                $conten['ejecucion']  = $row->ejecucion  ;
                $conten['nombre']     = $row->nombre     ;
                $conten['descrip']    = $row->descripcion;
                $conten['id_comp']    = $compromiso;
                $id_compania          = $row->id_compania;
                $id_producto          = $row->id;

                //Saca las tareas
                $sel=array('a.tarea','a.ejecucion','a.fecha','a.id','a.peso'
                ,'UNIX_TIMESTAMP(a.fecha) AS uts','CONCAT_WS(\' \',nombre,apellido) AS nombre');
                $this->db->select($sel);
                $this->db->from('tareas AS a');
                $this->db->join('integrantes AS b','a.id_integrante=b.id','LEFT');
                $this->db->where('a.id_compromiso',$compromiso);
                
                $this->db->order_by('a.fecha','asc');
                $this->db->order_by('a.id','asc');
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $conten['ecomprom']=$query->result();
                }else{
                        $conten['ecomprom']=array();
                }

                $conten['id_compania']= $ut->id_comp();
                $conten['id_producto']= $id_producto;
                $data['content']    = $this->load->view('view_gsubcompalu',$conten,true);
                $data['back_url']   = 'dashboard/gcompalu';
                $data['home_url']   = 'dashboard/gcompalu';
                $data['header']     = 'Sub-Gerencia de Compa&ntilde;&iacute;a';
                $data['title']      = 'Sub-Gerencia de Compa&ntilde;&iacute;a';

                $date_compr = new DateTime($conten['fecha']);
                $date_ahora = new DateTime();

                if($conten['ejecucion']<100 && $date_compr>=$date_ahora){
                        $data['footer']   = '<a href="'.site_url('tareas/dataeditmobil/'.$id_producto.'/'.$compromiso.'/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar Tarea</a>';
                }else{
                        $data['footer']   = '';
                }
                $data['headerextra'] = ($role==1)? 'Profesor: ': (($role==2)? 'Alumno Gerente: ':'Alumno: ');
                $data['headerextra'].= $ut->user('name');

                $this->load->view('view_ven', $data);
        }

/**
  * Método para optener el curso o el panal
  *
  * @since 2.0
  * @return string
  * @param $num 		Parámetro de entrada para definir si retornar el nombre o el número de panal
  */
        function _curso($num){
        	    $this->load->library('rapyd');
                $ut= new rpd_auth_library();
                $rt=$ut->logged();
                $id_cour = $ut->id_curso();
        	
                $sel=array('nombre','panal');
                $this->db->select($sel);
                $this->db->from('curso');
                $this->db->where('id',$id_cour);
                $this->db->limit(1);
                $query = $this->db->get();

                if ($query->num_rows() > 0){
                        $row    = $query->row();
                        $nombre = $row->nombre;
                        $panal = $row->panal;
                }else{
                        $nombre = 'No hay información del curso';
                        $panal='0';
                }
                if($num==1){
               		return $nombre;
                }
                if($num==2){
                	return $panal;
                }
        }
        
 /**
  * Método para optener el rango de las evaluaciones segun puntaje
  *
  * @since 2.0
  * @return string
  * @param $float $num 	Parámetro de entrada para definir si retornar el nombre o el número de panal
  */
        function _rango($num){
        	if ($num >= 4.5)
        		return 'Optimo';
        	else if ($num < 4.5 && $num >= 3.5)
        		return 'Bueno';        		
        	else if ($num < 3.5 && $num >=2.5)
        		return 'Suficiente';
        	else if ($num < 2.5)
        		return 'Insuficiente';
        	else return 'Pésimo';
        	
        }
        
}

