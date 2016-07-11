<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
  * Clase para iniciar por defecto.
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class Inicio extends CI_Controller {
	var $adjuntos='';

	public function index(){
		$c1=$c2=0;
		$dbprefix=$this->db->dbprefix;

		if ($this->db->table_exists("${dbprefix}curso")){
			$this->db->from('integrantes');
			$this->db->where('tipo','S');
			$c2 = $this->db->count_all_results();
		}
		if($c2 == 0){
			ci_redirect('instalador');
		}
		
        $menu = "<div class='ui-grid-a ui-responsive'>";
                
        $sel=array('id','nombre','profesor','contenido', 'semestre', 'panal');
        $this->db->select($sel);
        $this->db->from('curso');
        $this->db->order_by('panal','asc');
        $query = $this->db->get();
        $altr=1;
        
        if ($query->num_rows() > 0){
        	foreach ($query->result() as $row){
        		if($altr==1){
        			$menu.="<div class='ui-block-a'>
							<div class='jqm-block-content'>";
        			$menu.= '<a href="'.site_url('/panal'.$row->panal.'/').'"><h2>'.$row->nombre.'</h2></a>';
        			$menu.= '<p>Profesor '.$row->profesor;
        			$menu.= $this->info($row->id,$row->semestre);
        			$menu.= $row->panal;
        			$menu.="<br>Semestre ".$row->semestre."</p>";
        			$altr=2;
        		}
        	    else if($altr==2){
        			$menu.="<div class='ui-block-b'>
							<div class='jqm-block-content'>";
        			$menu.= '<a href="'.site_url('/panal'.$row->panal.'/').'"><h2>'.$row->nombre.'</h2></a>';
        			$menu.= '<p>Profesor '.$row->profesor;
        			$menu.= $this->info($row->id,$row->semestre);
        			$menu.= $row->panal;
        			$menu.="<br>Semestre ".$row->semestre."</p>";
        			$altr=1;
        		}
				$menu.="</div>";
				$menu.="</div>";

        	}
        }
        $menu.="<div class='ui-block-a'>
				<div class='jqm-block-content'>";
		$menu.="<a href='#'><h2>Estadísticas Globales</h2></a>";
		$menu.=$this->estadisticas();
		$menu.= '<a href="'.site_url('/inicio/guia_est/').'" >Guía para el estudiante</a>';
        $menu.="</div>";
		$menu.="</div>";
        $menu.= "</div>";

        $data['content']    = $menu;

		$data['header']     = 'Gesti&oacute;n RAIS';
		$data['title']      = 'Gesti&oacute;n RAIS';
		$data['footer']     = '<a href="'.site_url('/inicio/loginAdmin/').'" data-role="button" data-icon="user" data-direction="reverse">Admin</a>';

		$data['onLoadScript']='$.extend(  $.mobile , {
			ajaxFormsEnabled: false
		});';

		$this->load->view('view_panal', $data);
	}	

/**
  * Método que muestra el formulario para autentificar un Administrador.
  *
  * @return  void
  */
	public function loginAdmin(){
		$this->load->helper('form');
		$this->load->library('rapyd');
		$this->load->library('phpbb');

		$ut= new rpd_auth_library();

		$usr = $this->input->post('usr');
		$pwds = $this->input->post('pwd');
		$pwd = md5($this->input->post('pwd'));
		$error='';
		
		$phpbb = new Phpbb();

		if($usr!==false){
			$rt=$ut->login($usr,$pwd,0);
			if($rt){
				$tipo=$ut->tipo();

				if($tipo=='S'){		
	      			$phpbb_vars = array("username" => $usr, "password" => $pwds);
	        		$phpbb_result = $phpbb->user_login($phpbb_vars);
					ci_redirect('panel/');
				}else{
					$ut->logout();
					$error='Usuario o clave no v&aacute;lida';
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
			}
		}
		$conten=array();
		$conten['error']=$error;

		$obj = $this->load->view('view_login_admin',$conten ,true);
		$data['content']    = $obj;
		$data['header']     = 'Gesti&oacute;n RAIS';
		$data['title']      = 'Gesti&oacute;n RAIS';
		$data['footer']     = '';

		$data['onLoadScript']='$.extend(  $.mobile , {
			ajaxFormsEnabled: false
		});';

		$this->load->view('view_ven', $data);
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
		
		$this->index();		
	}

	public function correos(){
		$envio=$this->input->post('btn_envio');
		if($envio!==false){
			$to      = $this->input->post('mensaje');
			$subject = 'Proyecto GeRais';
			$body    = $this->input->post('texto');
			$this->adjuntos='';
			$rt=$this->_mail($to,$subject,$body);
			//$rt=true;
			if($rt){
				$data['msj'] = 'Mensaje enviado';
			}else{
				$data['msj'] = 'Error enviando mensaje';
			}

		}else{
			$data['msj'] = '';
		}

		$this->load->helper('form');
		$sel=array('TRIM(a.correo) AS correo','CONCAT_WS(\' \',a.nombre,a.apellido) AS nombre');
		$this->db->select($sel);
		$this->db->from('integrantes AS a');
		$query = $this->db->get();
		$correos=array();
		foreach ($query->result() as $row){
			$correos[$row->correo]=$row->nombre;
		}

		$data['correos']   = $correos;
		$data['titulo']    = '<h1>Correos</h1>';
		$this->load->view('view_correos',$data);
	}

/**
  * Método para proporcionar información de la instancia al panal
  *
  * @return  string
  */
	function info($id,$semestre){
		
		$this->db->from('integrantes AS a');
		$this->db->join('integcurso AS b', 'a.id=b.id_integrante', 'left');			
		$this->db->where('b.id_curso',$id);
		$this->db->where('b.semestre',$semestre);
		$this->db->where('a.tipo','A');
		$integrantes   = $this->db->count_all_results();
		
		$this->db->from('aucoevaluacion');		
		$this->db->where('id_curso',$id);
		$this->db->where('semestre',$semestre);
		$evaluaciones   = $this->db->count_all_results();
		
		$this->db->from('aucoevaluacion_it AS a');
		$this->db->join('aucoevaluacion AS b','b.id=a.id_aucoevaluacion','left');				
		$this->db->where('b.id_curso',$id);
		$this->db->where('b.semestre',$semestre);
		$evaluados   = $this->db->count_all_results();
		
		$this->db->from('compania');		
		$this->db->where('id_curso',$id);
		$this->db->where('semestre',$semestre);
		$companias   = $this->db->count_all_results();
		
		$this->db->from('compromisos');		
		$this->db->where('id_curso',$id);
		$this->db->where('id_compania > 0');
		$this->db->where('semestre',$semestre);
		$compromisos   = $this->db->count_all_results();
		
		$this->db->from('producto');		
		$this->db->where('id_curso',$id);
		$productos   = $this->db->count_all_results();
		
		$this->db->from('tareas AS a');
		$this->db->join('compromisos AS b','b.id=a.id_compromiso','left');
		$this->db->where('b.control > 0');				
		$this->db->where('b.id_curso',$id);
		$this->db->where('b.semestre',$semestre);
		$tareas   = $this->db->count_all_results();

		$this->db->from('tarearesol AS a');
		$this->db->join('tareas AS b','b.id=a.id_tarea','left');
		$this->db->join('compromisos AS c','c.id=b.id_compromiso','left');				
		$this->db->where('c.id_curso',$id);
		$this->db->where('c.semestre',$semestre);
		$isas   = $this->db->count_all_results();
		
		$menu="<br>Integrantes ".$integrantes.", Evaluaciones ".$evaluaciones;
		$menu.="<br>Companias ".$companias.", Productos ".$productos;
		$menu.="<br>Compromisos ".$compromisos.", Tareas ".$tareas;
		$menu.="<br>Informes ISA ".$isas.", Panal ";
		
		return $menu;
	}
	
/**
  * Método para proporcionar Estadisticas del panal
  *
  * @return  string
  */
	function estadisticas(){
		
		$this->db->select_sum('reasignado','reasignado');
		$this->db->from('compromisos');
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row    = $query->row();
			$reasignado = $row->reasignado;
		}else{
			$reasignado = 0;
		}

		$this->db->select(array('COUNT(*) AS cana'));
		$this->db->from('compromisos');
		$this->db->where('id_compania > 0');
		$this->db->where('ejecucion',100);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row    = $query->row();
			$culmi = $row->cana;
		}else{
			$culmi = 0;
		}

		$this->db->select(array('COUNT(*) AS cana'));
		$this->db->from('tareas');
		$this->db->where('id_integrante > 0');
		$this->db->where('ejecucion',100);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row    = $query->row();
			$sculmi = $row->cana;
		}else{
			$sculmi = 0;
		}

		$this->db->select(array('COUNT(*) AS cana'));
		$this->db->from('tareas');
		$this->db->where('registro','P');
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row    = $query->row();
			$psub = $row->cana;
		}else{
			$psub = 0;
		}
		$this->db->from('integrantes');
		$this->db->where('tipo <>','S');
		$integrantes   = $this->db->count_all_results();
		$evaluaciones  = $this->db->count_all_results('aucoevaluacion');
		$evaluados     = $this->db->count_all_results('aucoevaluacion_it');
		$companias     = $this->db->count_all_results('compania');
		$this->db->from('compromisos');
		$this->db->where('id_compania > 0');
		$compromisos   = $this->db->count_all_results();
		$productos     = $this->db->count_all_results('producto');
		$this->db->from('tareas AS a');
		$this->db->join('compromisos AS b','b.id=a.id_compromiso','left');
		$this->db->where('b.control > 0');
		$tareas   = $this->db->count_all_results();
		$isas          = $this->db->count_all_results('tarearesol');
		$penaliza      = $this->db->count_all_results('penalizaciones');
		
		$menu="<p>Lista de Panales: ".$this->db->count_all_results('curso').", Penalizaciones: ".$penaliza;
		$menu.="<br>Integrantes: ".$integrantes.", Evaluaciones/Evaluados: ".$evaluaciones."/".$evaluados;
		$menu.="<br>Companias: ".$companias.", Productos: ".$productos;
		$menu.="<br>Compromisos: ".$compromisos.", Culminados: ".$culmi;
		$menu.="<br>Compromisos Pospuestos: ".$reasignado;
		$menu.="<br>Tareas: ".$tareas.", Culminadas: ".$sculmi.", P.Profesor: ".$psub;
		$menu.="<br>Informes ISA ".$isas."</p>";
		
		return $menu;
		
	}

/**
  * Método para enviar correo electrónicos.
  *
  * @return  boolean
  */
	function _mail($to,$subject,$body){
		$this->config->load('notifica');
		if(!@include_once 'Mail.php'){
			$this->error='Problemas al cargar la clase Mail, probablemente sea necesario instalarla desde PEAR, comuniquese con soporte t&eacute;cnico';
			return false;
		}
		if(!@include_once 'Mail/mime.php'){
			$this->error='Problemas al cargar la clase Mail_mime, probablemente sea necesario instalarla desde PEAR, comuniquese con soporte t&eacute;cnico';
			return false;
		}

		$from = $this->config->item('mail_smtp_from');
		$host = $this->config->item('mail_smtp_host');
		$port = $this->config->item('mail_smtp_port');
		$user = $this->config->item('mail_smtp_usr');
		$pass = $this->config->item('mail_smtp_pwd');

		if(is_array($this->adjuntos)){
			$message = new Mail_mime();
			$message->setTXTBody($body);

			foreach($this->adjuntos AS $adj){
				$message->addAttachment($adj);
			}

			$body = $message->get();
			$extraheaders =  array (
				'From'    => $from,
				'To'      => $to,
				'Subject' => $subject
			);
			$headers = $message->headers($extraheaders);

		}else{
			$headers = array (
				'From'    => $from,
				'To'      => $to,
				'Subject' => $subject
			);
			$body.="\n\nEsta es una cuenta de correo no monitoreada. Por favor no responda o reenvíe mensajes a esta cuenta.";
		}

		$parr=array (
			'host'     => $host,
			'port'     => $port,
			'auth'     => true,
			'username' => $user,
			'password' => $pass
		);

		$smtp = Mail::factory('smtp',$parr);
		$mail = $smtp->send($to, $headers, $body);
		if (PEAR::isError($mail)) {
			$this->error=$mail->getMessage();
			return false;
		} else {
			return true;
		}
	}
	
	function guia_est(){
		$back ='inicio';
		$home ='inicio';
		
		$menu = '<h2 class="western">GUÍA PARA EL ESTUDIANTE</h2>
<p class="western"><strong><a name="uno"></a>1. ASPECTOS GENERALES.</strong> </p>
<p class="western" align="JUSTIFY">A continuación se describen los elementos presentados en la pantalla principal de los estudiantes:</p>
<p class="western" style="text-align: center;" align="JUSTIFY">'.image("img1.png").'</p>
<p class="western" style="text-align: center;" align="JUSTIFY"><strong><span style="color: #00000a;"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Figura 1. Pantalla principal de estudiantes.</span></span></span></strong></p>
<p class="western" style="text-align: center;" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Todas las pantallas menos la principal tienen en el encabezado los siguientes botones de navegación:</span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Home</strong></span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"> Para regresar a la pantalla principal.</span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Back</strong></span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"> Para regresar a la pantalla anterior.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Sólo la pantalla principal tiene el siguiente botón </span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Salir </strong></span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"> para cerrar la sesión.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><strong>2. REGISTRARSE</strong>.</p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">No estar registrado antes.</span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Ser estudiante del curso. </span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" style="text-align: center;" align="JUSTIFY">'.image("img2.png").'</p>
<p class="western" align="CENTER"><strong><span style="color: #00000a;"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Figura 2. Sección de registro de estudiantes.</span></span></span></strong></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">En la pantalla anterior presionar el enlace que dice “Registrarse”, de lo contrario si ya posee una cuenta en el sistema, presionar donde dice “Vincular Cuenta”</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Al presionar Registrarse a continuación saldrá la siguiente pantalla:</span></span></p>
<p class="western" style="text-align: center;" align="JUSTIFY">'.image("img3.png").'</p>
<p class="western" align="CENTER"><strong><span style="color: #00000a;"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Figura 3. Pantalla de registro de nuevos usuarios.</span></span></span></strong></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">En caso de que la compañía a la que se desea pertenecer no salga en la lista puede registrarla seleccionando “Crear una nueva” como opción en el campo compañía y escribir el nombre en el campo siguiente.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Una vez completados todos los datos se le da al botón guardar, si hubo algún problema aparecerá de nuevo la misma pantalla con la lista de los problemas al principio de la página, si este es el caso, se deben corregir y volver a intentar guardar, en caso de éxito volverá a la pantalla inicial y ya está listo para iniciar sesión.</span></span></p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Para todas las acciones siguientes se necesita que el usuario haya iniciado una sesión en el sistema.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>3. CAMBIAR DATOS DE LA CUENTA</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Ubicarse en la pantalla principal, al pie de página principal aparece un botón que dice "Configurar", al presionarlo aparece una nueva pantalla con todos los campos posibles para cambiar, una vez completada la operación presionar el botón guardar para finalizar.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><strong>4. CAMBIAR CLAVE</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Ubicarse en la pantalla principal, hacer presionar el botón “Configurar cuenta” ubicado al pie de la página, luego hacer presionar el botón “Cambio de clave” ubicado también al pie de la página, completar los datos que se piden y guardar los datos.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>5. SELECCIONAR UN PRODUCTO</strong></p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">No tener un producto asignado en la compa</span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">ñí</span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">a.</span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Ser alumno gerente.</span></span></p>
<p class="western"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Cuando la compañía no tiene producto asignado, en la pantalla principal, donde debería aparecer el nombre del producto saldrá un enlace que dice “Seleccionar producto”, al presionar sobre dicho enlace le permite al estudiante seleccionar un producto.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><strong>6. GESTION DE TAREAS</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Las tareas son labores que derivan de un compromiso y pueden ser asignados a cualquier integrante de la compañía.</span></span></p>
<p class="western"> </p>
<p class="western"> </p>
<h4 class="western">6.1 GESTIONAR UNA NUEVA TAREA</h4>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Ser alumno gerente.</span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El compromiso no debe estar terminado y debe estar vigente.</span></span></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Desde la pantalla principal, desplegar la lista de “Compromisos de la compañía” en caso de que no lo este, seleccionar el Compromiso al cual se desea detallar, presionar el botón “Agregar</span></span></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Sub-Compromiso”, llenar los campos que solicita y guardar.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><strong>6.2 DELEGAR UNA TAREA</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El sub compromiso no haya sido delegado por el profesor.</span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El sub compromiso no debe estar delegado a alguien.</span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El compromiso del que deriva no debe estar terminado y debe estar vigente.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Desde la pantalla principal, desplegar la lista de “Compromisos de la compañía” en caso de que no lo este, seleccionar el Compromiso el cual contiene el sub compromiso que se desea modificar, hacer presionar en el sub compromiso deseado, Seleccionar al estudiante al cual se le delega y guardar.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>6.3 MODIFICAR UNA TAREA</strong></p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">No es permitido a los alumnos, debe intervenir el profesor.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>7. GESTION DE ISA</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Los ISA son informes que van relacionados con los sub compromisos, deben contener información general de lo que se hizo, los problemas que se tuvieron y la sugerencia de un nuevo compromiso.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>7.1 AGREGAR UN ISA</strong></p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El sub compromiso debe estar asignado al dueño de la sesión.</span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El sub compromiso no debe tener un informe ISA registrado.</span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El compromiso del cual deriva no debe estar vencido o completado.</span></span></p>
<p class="western"> </p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Desde la pantalla principal, desplegar la lista de “Tareas asignados a ti” en caso de que no lo este, seleccionar el sub compromiso al cual se desea registrar el ISA, llenar los tres campos que pide y guardar.</span></span></p>
<p class="western"> </p>
<h4 class="western">7.2 MODIFICAR UN ISA</h4>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Haber registrado el ISA anteriormente.</span></span></p>
<p class="western" align="JUSTIFY">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El compromiso del cual deriva no debe estar vencido o completado. </span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Desde la pantalla principal, desplegar la lista de “Informe Semanal de Resultados” en caso de que no lo este, seleccionar el informe modificar los campos y guardar.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<h4 class="western">7.3 ELIMINAR UN ISA</h4>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Los ISAs no pueden ser eliminados.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>8. EJECUTAR UNA AUTO/CO EVALUACIÓN</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Las auto y co evaluaciones es la manera que tiene el estudiante para evaluar su desempeño y el de sus compañeros de compañía durante el transcurso de tiempo para cual el profesor lo estipule, se guardan con una calificación del 1 al 5 donde equivalen a pésimo, malo, regular, bueno y excelente respectivamente.</span></span></p>
<p class="western"> </p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;"><strong>Requisitos previos:</strong></span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Estar disponible una evaluaci</span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">ó</span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">n.</span></span></p>
<p class="western">● <span style="font-family: Perpetua, serif;"><span style="font-size: medium;">No haber ejecutado la evaluaci</span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">ó</span></span><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">n.</span></span></p>
<p class="western"> </p>
<p class="western"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Nota: la ejecución de la auto y co evaluación no se pueden modificar ni reversar.</span></span></p>
<p class="western"> </p>
<p class="western"><strong>9. FORO</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">El foro es una herramienta fundamental para la comunicación de los miembros fuera del horario habitual de reunión, permite ingresar al contenido disponible según los permisos establecidos para el usuario y el curso al que pertenece. Por defecto se le permite al alumno gestionar los datos de perfil de su cuenta en el foro.</span></span></p>
<p class="western" align="JUSTIFY"> </p>
<p class="western" align="JUSTIFY"><strong>10. VINCULAR CUENTA</strong></p>
<p class="western" align="JUSTIFY"><span style="font-family: Perpetua, serif;"><span style="font-size: medium;">Cuando el alumno ya se ha registrado antes en el sistema, puede registrarse en un curso vinculando su cuenta al curso, lo cual le permite solo llenar los datos necesarios solicitados por el curso y no volver a crear un nuevo usuario y clave.</span></span></p>
<p> </p>';
		
		$data['content']  = $menu;
		$data['back_url'] = $back;
		$data['home_url'] = $home;
		$data['header']   = 'Guía para el estudiante';
		$data['title']    = 'Guía para el estudiante';
		
		$this->load->view('view_simple_ven', $data);		
	}
}
