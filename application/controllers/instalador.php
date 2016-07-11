<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
  * Clase para instalar el sistema.
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class instalador extends CI_Controller {

	function index(){
		ci_redirect('instalador/registroAdmin/create');
	}
	
	function registroAdmin(){
		$this->_creatabla();
		$this->_puedeinstallAdmin();
		$this->load->library('rapyd');

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Instalador :: Registro de Administrador';
		$edit->back_url = site_url('inicio/loginAdmin');

		$edit->source('integrantes');
		$edit->pre_process(array('insert'), array($this, 'pre_registrar_admin_insert'));
		$edit->pre_process(array('update'), array($this, 'pre_false'));
		$edit->pre_process(array('delete'), array($this, 'pre_false'));
		$edit->field('input','nombre','Nombre')->rule('ucwords|trim|required|max_length[50]')->set_group('Datos personales');
		$edit->field('input','apellido','Apellido')->rule('ucwords|trim|required')->set_group('Datos personales');
		//$edit->field('input','cedula','Cédula')->set_attributes(array('maxlength'=>'50'))->rule('numeric|required|unique')->set_group('Datos personales');
		$edit->field('input','telefono','Tel&eacute;fono')->rule('trim|required|phone')->set_group('Datos personales');
		$edit->field('input','correo','Correo Electr&oacute;nico')->rule('trim|required|valid_email|max_length[100]')->set_group('Datos de la Cuenta');
		$edit->field('input','usuario','Usuario')->rule('required|unique|alpha_dash')->set_group('Datos de la Cuenta');
		$edit->field('password','clave1','Contrase&ntilde;a')->rule('required|matches[clave2]')->set_group('Datos de la Cuenta');
		$edit->field('password','clave2','Confirmaci&oacute;n')->rule('required')->set_group('Datos de la Cuenta');

		$edit->buttons('save');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = 'instalador/index';
		$data['home_url']   = 'instalador/index';
		$data['header']     = 'Instalador Panal GeRAIS';
		$data['title']      = 'Instalador Panal GeRAIS';
		$data['footer']     = '';
		$this->load->view('view_ven', $data);
	}
	
	function pre_false($model){
		return false;
	}
	
	function pre_registrar_admin_insert($model){
		$model->set('tipo','S');
		$clave=$_POST['clave1'];
		$model->set('clave',md5($clave));
		return true;
	}

	public function _creatabla(){
		$dbprefix=$this->db->dbprefix;

		if (!$this->db->table_exists("${dbprefix}actividades")){
			$mSQL="CREATE TABLE `${dbprefix}actividades` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_integrante` int(10) unsigned DEFAULT NULL COMMENT 'Apuntador a integrante',
			`fecha` date DEFAULT NULL COMMENT 'Fecha de registro',
			`actividad` text COMMENT 'Descripción de la actividad',
			PRIMARY KEY (`id`),
			KEY `id_integrante` (`id_integrante`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Se guardan las actividades de las compañías'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}aucoevaluacion")){
			$mSQL="CREATE TABLE `${dbprefix}aucoevaluacion` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`fecha_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio de la evaluación',
			`comentario` tinytext COMMENT 'Comentario u observación',
			`plazo` int(5) DEFAULT NULL COMMENT 'Plazo en días que determinan el fin de la evaluacion',
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al curso de la autocoevaluacion',
			`semestre` varchar(50) DEFAULT NULL COMMENT 'Semestre',
			`estampa` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de inserción del registro',
			PRIMARY KEY (`id`),
			KEY `id_curso` (`id_curso`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maestro de las Auto y Co Evaluaciones'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}aucoevaluacion_it")){
			$mSQL="CREATE TABLE `${dbprefix}aucoevaluacion_it` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_aucoevaluacion` int(10) NOT NULL DEFAULT '0' COMMENT 'Apuntador a aucoevaluacion',
			`id_evaluador` int(10) DEFAULT '0' COMMENT 'Apuntador al integrante evaluado',
			`id_evaluado` int(10) DEFAULT '0' COMMENT 'Apuntador al integrante evaluado',
			`resultado` char(1) DEFAULT '0' COMMENT 'Resultado de la evaluación',
			`fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de registro',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Detalle de las Auto y Co Evaluaciones'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}compania")){
			$mSQL="CREATE TABLE `${dbprefix}compania` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_producto` int(10) unsigned DEFAULT '0' COMMENT 'Apuntado al producto',
			`nombre` varchar(150) NOT NULL COMMENT 'Nombre de la compañía',
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al curso de la compania',
			`semestre` varchar(50) DEFAULT NULL COMMENT 'Semestre',
			PRIMARY KEY (`id`),
			KEY `id_curso` (`id_curso`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Registro de las compañias'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}curso")){
			$mSQL="CREATE TABLE `${dbprefix}curso` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`nombre` varchar(50) DEFAULT NULL COMMENT 'Nombre del curso',
			`profesor` varchar(100) DEFAULT NULL COMMENT 'Nombre del profesor',
			`contenido` longtext COMMENT 'Contenido del curso',
			`semestre` varchar(50) DEFAULT NULL COMMENT 'Semestre',
			`activo` char(1) DEFAULT '0' COMMENT 'Estado de las inscripciones del curso',
			`estricto` char(1) DEFAULT '1' COMMENT 'Modo estricto entrega de ISAs',
			`panal` int(10) unsigned DEFAULT '0' COMMENT 'Identificador del panal',
			`eval` char(1) DEFAULT '0' COMMENT 'Estado de la evaluación del docente',
			PRIMARY KEY (`id`),			
			UNIQUE KEY `panal` (`panal`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Detalles de la asignatura'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}integrantes")){
			$mSQL="CREATE TABLE `${dbprefix}integrantes` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_compania` int(10) unsigned DEFAULT NULL COMMENT 'Apuntador a compañía',
			`cargo` char(1) DEFAULT NULL COMMENT 'Cargo que ejerce en la compañía',
			`tipo` char(1) DEFAULT 'A' COMMENT 'Tipo de actor Alumno, Profesor',
			`usuario` varchar(50) DEFAULT NULL COMMENT 'Usuario',
			`clave` varchar(50) DEFAULT NULL COMMENT 'Clave o contraseña',
			`nombre` varchar(100) DEFAULT NULL COMMENT 'Nombre de la persona',
			`apellido` varchar(100) DEFAULT NULL COMMENT 'Apellido de la persona',
			`cedula` varchar(50) DEFAULT NULL COMMENT 'Cédula de la persona',
			`correo` varchar(100) DEFAULT NULL COMMENT 'Correo electrónico',
			`telefono` varchar(100) DEFAULT NULL COMMENT 'Teléfono',
			`cualidades` varchar(100) DEFAULT NULL COMMENT 'Cualidades',
			`hobbies` varchar(100) DEFAULT NULL COMMENT 'Pasatiempos',
			`twitter` varchar(100) DEFAULT NULL COMMENT 'Usuario de twitter',
			`seccion` VARCHAR(5) NULL DEFAULT NULL COMMENT 'Seccion del integrante',
			`id_curso` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Apuntador al curso del integrante',
			PRIMARY KEY (`id`),
			UNIQUE KEY `usuario` (`usuario`),
			KEY `id_compania` (`id_compania`),
			KEY `id_curso` (`id_curso`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Integrantes de las compañías'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}penalizaciones")){
			$mSQL="CREATE TABLE `${dbprefix}penalizaciones` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_compromiso` int(10) unsigned NOT NULL COMMENT 'Apuntador a compromiso',
			`id_integrante` int(10) unsigned NOT NULL COMMENT 'Apuntador a integrante',
			`fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha',
			`fecha_comp` timestamp NULL DEFAULT NULL COMMENT 'Fecha del compromiso',
			`exonerada` char(1) DEFAULT 'N' COMMENT 'Si fue o no exonerada',
			PRIMARY KEY (`id`),
			KEY `id_compromiso` (`id_compromiso`),
			KEY `id_integrante` (`id_integrante`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Penalizaciones por incumplimiento'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}producto")){
			$mSQL="CREATE TABLE `${dbprefix}producto` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`nombre` varchar(100) DEFAULT NULL COMMENT 'Nombre del producto',
			`descripcion` text COMMENT 'Descrición del producto',
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al curso del producto',
			PRIMARY KEY (`id`),
			KEY `id_curso` (`id_curso`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Productos a ser tomados por las compañias'";
			$this->db->simple_query($mSQL);
		}		
	
		if (!$this->db->table_exists("${dbprefix}compromisos")){
			$mSQL="CREATE TABLE `${dbprefix}compromisos` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_compania` int(10) NOT NULL COMMENT 'Apuntador a la compañía',
			`id_producto` int(10) unsigned NOT NULL COMMENT 'Fecha del compromiso',
			`fecha` date NOT NULL COMMENT 'Fecha del compromiso',
			`compromiso` longtext NOT NULL COMMENT 'Descripción del compromiso',
			`ejecucion` int(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Porcentaje de ejecución del compromiso',
			`reasignado` int(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Número de veces que fue reasignado',
			`control` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Indica si es registro de definición o de ejecución',
			`integ` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Apunta al autor del compromiso',
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al curso del compromiso',
			`semestre` varchar(50) DEFAULT NULL COMMENT 'Semestre',
			`estampa` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de inserción del registro',
			PRIMARY KEY (`id`),
			KEY `id_compania` (`id_compania`),
			KEY `id_curso` (`id_curso`),
			FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compromisos o hitos de las companias'";
			$this->db->simple_query($mSQL);
		}		
		
		if (!$this->db->table_exists("${dbprefix}problemas")){
			$mSQL="CREATE TABLE `${dbprefix}problemas` (
			`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_compromiso` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Apuntador a compromisos',
			`fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
			`problema` TEXT NOT NULL COMMENT 'Descripción del problema',
			PRIMARY KEY (`id`),
			FOREIGN KEY (`id_compromiso`) REFERENCES `compromisos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE			
			) COMMENT='Problemas realizando alguna actividad' COLLATE='utf8_general_ci' ENGINE=InnoDB;";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}tareas")){
			$mSQL="CREATE TABLE `${dbprefix}tareas` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_compromiso` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Apuntador al compromiso',
			`id_integrante` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Apuntador al integrante',
			`tarea` LONGTEXT NULL DEFAULT NULL COMMENT 'Descripción de la tarea',
			`fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de la tarea',
			`ejecucion` int(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Porcentaje de ejecución de la tarea',
			`peso` int(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Peso de la tarea',
			`registro` char(1) NOT NULL DEFAULT 'A' COMMENT 'Vale P si fue registrado por un profesor y A si fue registrado por un alumno',
			`control` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Indica si es registro de definición o de ejecución',			
			`integ` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Apunta al autor del compromiso',
			PRIMARY KEY (`id`),
			FOREIGN KEY (`id_compromiso`) REFERENCES `compromisos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
			KEY `id_integrante` (`id_integrante`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sub delegación de los hitos o tareas'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${dbprefix}tarearesol")){
			$mSQL="CREATE TABLE `${dbprefix}tarearesol` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_tarea` int(10) unsigned NOT NULL COMMENT 'Apuntador a la tarea',
			`reasignacion` int(5) unsigned NOT NULL COMMENT 'Número de reasignación a la que pertenece',
			`hizo` text COMMENT 'Que hizo',
			`problema` text COMMENT 'Que problema tuvo',
			`promete` text COMMENT 'Que promete para la próxima semana',
			`fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de inserción del registro',
			PRIMARY KEY (`id`),
			UNIQUE KEY `subreasignado` (`id_tarea`,`reasignacion`),
			FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Resultado de la tarea'";
			$this->db->simple_query($mSQL);
		}
		
		if (!$this->db->table_exists("${dbprefix}integcurso")){
			$mSQL="CREATE TABLE `${dbprefix}integcurso` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_integrante` int(10) unsigned NOT NULL COMMENT 'Apuntador al integrante',
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al curso',			
			`id_compania` int(10) unsigned NOT NULL COMMENT 'Apuntador a la compania',
			`cargo` char(1) DEFAULT NULL COMMENT 'Cargo que ejerce en la compañía',
			`seccion` VARCHAR(5) NULL DEFAULT NULL COMMENT 'Seccion del integrante en el curso',
			`semestre` varchar(50) DEFAULT NULL COMMENT 'Semestre',
			`fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de inserción del registro',
			PRIMARY KEY (`id`),
			KEY `id_integrante` (`id_integrante`),
			FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
			KEY `id_compania` (`id_compania`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabla intermedia que relaciona integrantes y cursos'";
			$this->db->simple_query($mSQL);
		}
				
		if (!$this->db->table_exists("${dbprefix}seccioncurso")){
			$mSQL="CREATE TABLE `${dbprefix}seccioncurso` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al curso',
			`seccion` VARCHAR(5) NULL DEFAULT NULL COMMENT 'Seccion perteneciente a el curso',
			`fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de inserción del registro',
			PRIMARY KEY (`id`),
			FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabla intermedia que relaciona secciones y cursos'";
			$this->db->simple_query($mSQL);
		}
		
		if (!$this->db->table_exists("${dbprefix}evaluacion")){
			$mSQL="CREATE TABLE `${dbprefix}evaluacion` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_evaluado` int(10) unsigned NOT NULL COMMENT 'Apuntador al evaluado',
			`id_evaluador` int(10) unsigned NOT NULL COMMENT 'Apuntador al evaluador',
			`id_curso` int(10) unsigned NOT NULL COMMENT 'Apuntador al evaluador',
			`seccion` VARCHAR(5) NOT NULL COMMENT 'Seccion perteneciente a el curso',
			`semestre` varchar(50) NOT NULL COMMENT 'Semestre',
			`fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de inserción del registro',
			`a` int(5) unsigned NOT NULL COMMENT 'Manera de informar sobre el plan de trabajo de la asignatura',
			`b` int(5) unsigned NOT NULL COMMENT 'Aplicación y desarrollo del plan de trabajo',
			`c` int(5) unsigned NOT NULL COMMENT 'Claridad del programa de la asignatura',
			`d` int(5) unsigned NOT NULL COMMENT 'Uso de medios y materiales didácticos',
			`e` int(5) unsigned NOT NULL COMMENT 'Organización de las clases',
			`f` int(5) unsigned NOT NULL COMMENT 'Cumplimiento de las actividades',
			`g` int(5) unsigned NOT NULL COMMENT 'Puntualidad según el horario pautado',
			`h` int(5) unsigned NOT NULL COMMENT 'Forma de explicar',
			`i` int(5) unsigned NOT NULL COMMENT 'Dinámica y desarrollo de las clases',
			`j` int(5) unsigned NOT NULL COMMENT 'Manera de transmitir interés y motivar a los estudiantes',
			`k` int(5) unsigned NOT NULL COMMENT 'Atención y disposición hacia las consultas fuera de clases',
			`l` int(5) unsigned NOT NULL COMMENT 'Actitud hacia las opiniones, inquietudes y preguntas de los estudiantes',
			`m` int(5) unsigned NOT NULL COMMENT 'Trato hacia los estudiantes',
			`n` int(5) unsigned NOT NULL COMMENT 'Fomenta la participación en clase',
			`o` int(5) unsigned NOT NULL COMMENT 'Interés del profesor por la asignatura',
			`p` int(5) unsigned NOT NULL COMMENT 'Dominio del contenido de la asignatura',
			`q` int(5) unsigned NOT NULL COMMENT 'Vocabulario con el que se expresa',
			`r` int(5) unsigned NOT NULL COMMENT 'Relación de los contenidos con el ejercicio profesional',
			`s` int(5) unsigned NOT NULL COMMENT 'Nivel de profundidad en los contenidos',
			`t` int(5) unsigned NOT NULL COMMENT 'Planificación de las evaluaciones',
			`u` int(5) unsigned NOT NULL COMMENT 'Estrategias de evaluación de la asignatura',
			`v` int(5) unsigned NOT NULL COMMENT 'Claridad en los criterios y objetivos usados en las evaluaciones',
			`w` int(5) unsigned NOT NULL COMMENT 'Manera de transmitir los resultados de las evaluaciones',
			`x` int(5) unsigned NOT NULL COMMENT '¿Cómo cataloga la calidad del trabajo desarrollado por el profesor?',			
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Evaluación del profesor o jefe ejecutivo'";
			$this->db->simple_query($mSQL);
		}
	}
	
	function _puedeinstallAdmin(){
		$cana=0;

		$this->db->from('integrantes');
		$this->db->where('tipo','S');
		$cana += $this->db->count_all_results();
		if($cana != 0){
			die('Acceso prohibido');
		}
	}

}
