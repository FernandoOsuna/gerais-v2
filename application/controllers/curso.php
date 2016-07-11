<?php
/**
  * Clase para la gestión de información del curso.
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class curso extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Informacion del curso';
/**
 *  Dirección url de la clase.
 */
	var $url ='curso/';

	function index(){
	}

/**
  * CRUD para los registro de cursos
  *
  * @since 1.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla curso.
  */
	function dataeditmobil($status,$id=0){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		if($rt===false) die('Acceso no permitido');
		$id_int=$ut->id_int();
		$back='integrantes/modif/modify/'.$id_int;
		
		if($ut->tipo()=='S'){
			$back='panel';
		}

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label    = $this->titp;
		$edit->back_url = site_url($back);

		$edit->source('curso');
		$edit->pre_process(array('delete'), array($this, 'pre_false'));
		$edit->pre_process(array('insert'), array($this, 'pre_false'));
		$edit->pre_process(array('update'), array($this, 'pre_update'));
		$edit->field('input'   ,'nombre'   ,'Nombre del curso')->rule('trim|required');
		if($ut->tipo()=='S'){
			$act='';
			$actual='Ninguno';
			if(!empty($id)){
				$sel=array('id','nombre','panal');
				$this->db->select($sel);
				$this->db->from('curso');
				$this->db->where('id', $id);
				$query = $this->db->get();
				$row=$query->row();				
				$act = $row->panal;
				$actual = $row->panal;
			}
			$sel=array('id','nombre','panal');
			$this->db->select($sel);
			$this->db->from('curso');
			$this->db->order_by('panal', 'desc');
			$query = $this->db->get();
	
			$opt=array('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16');
			
			foreach ($query->result() as $row){
				unset($opt[$row->panal]);
			}
			unset($opt[0]);
			$edit->field('dropdown','panal','Panal Nro')->rule('numeric|required|unique|in_range[[1,16]]')->option($act,$actual)->options($opt);		
		}
		$edit->field('input'   ,'semestre' ,'Semestre')->rule('required');
		$edit->field('input'   ,'profesor' ,'Profesor')->rule('required');
		$edit->field('textarea','contenido','Contenido')->rule('required');
		
		$sel= array('GROUP_CONCAT(a.seccion ORDER BY a.seccion) AS secciones');			
		$this->db->select($sel);
        $this->db->from('seccioncurso AS a');
        $this->db->where('a.id_curso',$id);
        $query=$this->db->get();
        $row=$query->row();
		if (empty($row->secciones))
			$edit->field('input','c_datos','Secciones')->set_attributes(array('readonly' =>'readonly', 'value' => 'Debe agregar las secciones a el curso'));
		else 
			$edit->field('input','c_datos','Secciones')->set_attributes(array('readonly' =>'readonly', 'value' => $row->secciones));
		
		$edit->field('checkbox','activo'   ,'Desactivar inscripciones')
					->set_attributes(array('data-role'=>"flipswitch", 'data-on-text'=>"Si", 'data-off-text'=>"No", 'data-wrapper-class'=>"custom-size-flipswitch"));
					
		$edit->field('checkbox','estricto' ,'Modo estricto ISA')
					->set_attributes(array('data-role'=>"flipswitch", 'data-on-text'=>"Si", 'data-off-text'=>"No", 'data-wrapper-class'=>"custom-size-flipswitch"));
					
		$edit->field('checkbox','eval','Evaluar Profesor')
					->set_attributes(array('data-role'=>"flipswitch", 'data-on-text'=>"Si", 'data-off-text'=>"No", 'data-wrapper-class'=>"custom-size-flipswitch"));
								
		$edit->buttons('modify','save','undo','back');

		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = $this->titp;
		$data['title']      = $this->titp;
		
		if ($status!='create' && $status!='insert')
			$data['footer'].= '<a href="'.site_url('curso/seccion/'.$id.'/create').'" data-role="button" data-icon="plus" data-direction="reverse">Agregar sección</a>';
		
		$this->db->from('seccioncurso');
		$this->db->where('id_curso',$id);
		if ($this->db->count_all_results() >0)
			$data['footer'] .= '<a href="'.site_url('curso/eliminarseccion/'.$id).'" data-role="button" data-icon="minus" data-direction="reverse">Eliminar sección</a>';
					
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');
		if($ut->tipo()=='S'){
			$data['header']     = 'Configuración de cuenta';
			$data['title']      = 'Configuración de cuenta';
			$data['headerextra'] = 'Administrador: '.$ut->user('name');
		}

		$this->load->view('view_ven_panel', $data);
	}
	
/**
  * Edicion de registro de la tabla seccioncurso para agregar nuevas secciones
  *
  *
  * @return void
  */
	function seccion($id_cour,$status){
		$this->load->library('rapyd');
		$dbprefix=$this->db->dbprefix;

		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		
		$role   = $ut->role();
		if($rt===false || $role!=1) die('Acceso no permitido');
		$id_int = $ut->id_int();
		
		$back='curso/dataeditmobil/modify/'.$id_cour;
			
		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;		
				
		$this->db->select(array('nombre'));
		$this->db->from('curso');				
		$this->db->where('id',$id_cour);
		$this->db->limit(1);
		$query = $this->db->get();
		$row= $query->row();
		
		$edit->label = $row->nombre.' - Sección';
		$edit->back_url = site_url($back);

		$edit->source('seccioncurso');
		
		$sel= array('GROUP_CONCAT(a.seccion ORDER BY a.seccion) AS secciones');			
		$this->db->select($sel);
        $this->db->from('seccioncurso AS a');
        $this->db->where('a.id_curso',$id_cour);
        $query=$this->db->get();
        $row=$query->row();
		if (empty($row->secciones))
			$edit->field('input','c_datos','Actual')->set_attributes(array('readonly' =>'readonly', 'value' => 'No tiene'));
		else 
			$edit->field('input','c_datos','Actual')->set_attributes(array('readonly' =>'readonly', 'value' => $row->secciones));
		
		$edit->field('input','seccion','Nueva sección')
			->set_attributes(array('maxlength'=>'5'))
			->rule('required|max_length[5]');
			
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		
		$edit->buttons('modify','save','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Ficha de curso';
		$data['title']      = 'Ficha de curso';
		
		$data['home_url']   = 'inicio';
		if ($role==1)
			$data['headerextra'] = 'Profesor: ';
		else if ($role==2)
				$data['headerextra'] = 'Alumno Gerente: ';
			else 
				$data['headerextra'] = 'Alumno: ';
		$data['headerextra'].= $ut->user('name');		

		$this->load->view('view_ven', $data);
	}
	
	function eliminarseccion($id){
		
		$this->load->library('rapyd');

        $ut     = new rpd_auth_library();
        $rt     = $ut->logged(1);
        $id_int = $ut->id_int();
        $id_cour = $ut->id_curso();
        if($rt===false) die('Acceso no permitido');
        
        $sel=array('nombre');
		$this->db->select($sel);
		$this->db->from('curso');
		$this->db->where('id',$id);
		$query = $this->db->get();
		$row=$query->row();
		
		$menu = '<h2>'.$row->nombre.' - Sección</h2>';
		$sel= array('id','seccion');			
		$this->db->select($sel);
        $this->db->from('seccioncurso');
        $this->db->where('id_curso',$id);
        $query=$this->db->get();
        
        $menu.= "<h3>Lista de secciones asociadas</h3>";
        $menu.= "<p>Seleccione la que desea eliminar</p>";
        $menu.= "<div class='content-primary'>
        <ul data-role='listview' data-inset='true' data-icon='delete'>";
        foreach ($query->result() as $row){
        	  $menu.= '<li><a href="'.site_url('curso/seccion/'.$id.'/delete/'.$row->id).'" data-transition="none">'.$row->seccion.'</a></li>';      	
        }
        $menu.= "</ul></div>";
        $data['content']    = $menu;
        $data['header']     = 'Panel de control';
        $data['title']      = 'Panel de control';
        $data['back_url']       = 'curso/dataeditmobil/modify/'.$id;
        $data['usuario']    = $ut->user('name');
        
        $data['home_url']       = 'panel';
        $data['headerextra']= 'Administrador: '.$ut->user('name');
        $this->load->view('view_ven_panel', $data);

        $this->load->view('view_ven', $data);
	}

/**
  * Pre proceso.
  *
  * evita que el registro sea borrado o insertado sin ser administrador
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla curso.
  */
	function pre_false($model){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		if($rt===false) die('Acceso no permitido');
		$id_int=$ut->id_int();
		if($ut->tipo()=='S'){
			return true;
		}
		else{
			$model->error_string = 'Accion prohibida';
			return false;
		}

	}
	
/**
  * Pre proceso.
  *
  * Evita que la ejecución de acciones prohibidas 
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla curso.
  */
	function pre_eval_false($model){		
		$model->error_string = 'Accion prohibida';
		return false;		
	}
	
/**
  * Pre proceso.
  *
  * Al modificar el campo semestre del registro se actualiza la session y se agrega el nuevo campo correspondiente en la tabla integcurso
  *
  * @return boolean
  *
  * @param object    $model Modelo de la tabla curso.
  */
	function pre_update($model){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		if($rt===false) die('Acceso no permitido');
		$id_int=$ut->id_int();
		if ($ut->tipo()=='S'){
			$this->db->select(array('a.id'));
			$this->db->from('integrantes AS a');
			$this->db->join('integcurso AS b','a.id=b.id_integrante', 'left');
			$this->db->where('a.tipo','P');
			$query1 = $this->db->get();
			if ($query1->num_rows()>0){
				$row   = $query1->row();
				$id_int=$row->id;
			}
		}
		
		$id =$model->pk['id'];
		$sem = $model->get('semestre');
		
		$this->db->from('curso');
		$this->db->where('id',$id);
		$query1 = $this->db->get();
		if ($query1->num_rows()>0)
			$row   = $query1->row();
		
		$this->db->from('integcurso');
		$this->db->where('id_curso',$id);
		$this->db->where('id_integrante',$id_int);
		$this->db->where('semestre',$sem);
		$query = $this->db->get();
		
		if ($query->num_rows()>0){
			if ($sem!=$row->semestre){
				$ut->chngsession($sem);
				if ($ut->tipo()=='P'){				
									
					$sel=array('id','DATE_FORMAT(fecha, \'%Y%m%d\') AS fecha', 'DATE_FORMAT(estampa, \'%Y%m%d\') AS estampa');
					$this->db->select($sel);
					$this->db->from('compromisos');
					$this->db->where('control',0);
					$this->db->where('id_compania',0);
					$this->db->where('id_curso',$id);
					$query = $this->db->get();
					$fecha_actual = date('Ymd',mktime(0, 0, 0, date('m'), date('d'), date('Y')));
					if ($query->num_rows() > 0)
						foreach ($query->result() as $row){
							$fecha_ant = $row->fecha;
							$fecha_ins = $row->estampa;
							if ($fecha_actual>$fecha_ant){
								
								$dif=$this->diferenciaDias($fecha_ins,$fecha_ant);
								$data = array(
												'fecha'   =>date('Ymd',mktime(0, 0, 0, date('m'), date('d')+$dif, date('Y'))),
												'estampa' =>date('Ymd',mktime(0, 0, 0, date('m'), date('d'), date('Y')))
												);
								
								$this->db->where('id',$row->id);
								$this->db->update('compromisos',$data);
							}
						}
				}
			}
			return true;
		}
		else{
			$data = array(
		      	'id_integrante' => $id_int,
				'id_curso' => $id,
				'id_compania' => 0,
				'semestre' => $sem 
		       );
			$this->db->insert('integcurso', $data);
			
			
			if ($sem!=$row->semestre){
				$ut->chngsession($sem);
				if ($ut->tipo()=='P'){				
									
					$sel=array('id','DATE_FORMAT(fecha, \'%Y%m%d\') AS fecha', 'DATE_FORMAT(estampa, \'%Y%m%d\') AS estampa');
					$this->db->select($sel);
					$this->db->from('compromisos');
					$this->db->where('control',0);
					$this->db->where('id_compania',0);
					$this->db->where('id_curso',$id);
					$query = $this->db->get();
					$fecha_actual = date('Ymd',mktime(0, 0, 0, date('m'), date('d'), date('Y')));
					if ($query->num_rows() > 0)
						foreach ($query->result() as $row){
							$fecha_ant = $row->fecha;
							$fecha_ins = $row->estampa;
							if ($fecha_actual>$fecha_ant){
								
								$dif=$this->diferenciaDias($fecha_ins,$fecha_ant);
								$data = array(
												'fecha' =>date('Ymd',mktime(0, 0, 0, date('m'), date('d')+$dif, date('Y'))),
												'estampa' =>date('Ymd',mktime(0, 0, 0, date('m'), date('d'), date('Y')))
												);
								
								$this->db->where('id',$row->id);
								$this->db->update('compromisos',$data);
							}
						}
				}	
			}

			
			return true;				
		}	        			

	}
	
/**
  * Método que permite calcular la diferencia de dias entre dos fechas
  *
  * @since 2.0
  *
  * @return int
  * @param date   	$inicio 	Fecha inicial.
  * @param date     $fin     	Fecha final.
  */
function diferenciaDias($inicio, $fin)
{
        $inicio = strtotime($inicio);
        $fin = strtotime($fin);
        $dif = $fin - $inicio;
        $diasFalt = (( ( $dif / 60 ) / 60 ) / 24);
        return ceil($diasFalt);
}
	
/**
  * Método que permite el registro de evaluaciones
  *
  * @since 2.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser solo modify.
  * @param int      $id     Clave primaria de registro en la tabla evaluación.
  */
	function evaluacion($status,$id=0){

		$this->load->library('rapyd');
		
		$ut= new rpd_auth_library();
		$rt=$ut->logged();
		if($rt===false) die('Acceso no permitido');
		
		$id_int = $ut->id_int();
		$id_cour = $ut->id_curso();
		$rol = $ut->role();
		$sec = $ut->seccion();
		
		$sel=array('a.id','a.activo','a.semestre', 'a.eval','a.nombre AS nom_curso','c.id AS id_eval','CONCAT_WS(\' \',c.nombre,c.apellido) AS nombre');
		$this->db->select($sel);
		$this->db->from('curso AS a');
		$this->db->join('integcurso AS b', 'a.id=b.id_curso','left');
		$this->db->join('integrantes AS c', 'b.id_integrante=c.id','left');	
		$this->db->where('a.id',$id_cour);
		$this->db->where('c.tipo','P');
		$this->db->order_by('b.fecha');
		$query = $this->db->get();
		$row = $query->row();
		
		$semestre = $row->semestre;
		$nombre = $row->nombre;
		$curso = $row->nom_curso;
		
		$this->db->from('evaluacion');	
		$this->db->where('id_curso',$id_cour);
		$this->db->where('id_evaluador',$id_int);
		$this->db->where('semestre',$semestre);
		
		$existe=$this->db->count_all_results();		
		if($existe>0) die('Ya realizo esta evaluaci&oacute;n.');		
		
		if($row->eval==0) die('Acceso no permitido.');		

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->label = 'Evaluación del curso '.$curso.'<p><small>Profesor '.$nombre.'</small></p>';
		
		$back ='dashboard/gcompalu';
		$edit->back_url = site_url($back);

		$edit->source('evaluacion');
		$edit->pre_process(array('update'), array($this, 'pre_eval_false'));
		$edit->pre_process(array('delete'), array($this, 'pre_eval_false'));
		
		$opt = array();
		$opt[1]='Pésimo';
		$opt[2]='Insuficiente';
		$opt[3]='Suficiente';
		$opt[4]='Bueno';
		$opt[5]='Optimo';
		
		$edit->field('hidden','id_evaluado','')->insert_value=$row->id_eval;
		$edit->field('hidden','id_evaluador','')->insert_value=$id_int;
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		$edit->field('hidden','seccion','')->insert_value=$sec;
		$edit->field('hidden','semestre','')->insert_value=$semestre;

		$edit->field('checkboxgroup','a1','')
			->set_extra('<h3>De la programación y organización del curso realizado por parte del profesor</h3>');			
								
		$edit->field('dropdown','a','1.- Manera de informar sobre el plan de trabajo de la asignatura')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','b','2.- Aplicación y desarrollo del plan de trabajo')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','c','3.- Claridad del programa de la asignatura')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','d','4.- Uso de medios y materiales didácticos')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','e','5.- Organización de las clases')
			->option('','Seleccione')->options($opt)
			->rule('required');
			
		$edit->field('checkboxgroup','a2','')
			->set_extra('<h3>Del desempeño y la actitud del profesor</h3>');
		$edit->field('dropdown','f','6.- Cumplimiento de las actividades')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','g','7.- Puntualidad según el horario pautado')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','h','8.- Forma de explicar')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','i','9.- Dinámica y desarrollo de las clases')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','j','10.- Manera de transmitir interés y motivar a los estudiantes')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','k','12.- Atención y disposición hacia las consultas fuera de clases')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','l','13.- Actitud hacia las opiniones, inquietudes y preguntas de los estudiantes')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','m','14.- Trato hacia los estudiantes')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','n','15.- Fomenta la participación en clase')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','o','16.- Interés del profesor por la asignatura')
			->option('','Seleccione')->options($opt)
			->rule('required');
			
		$edit->field('checkboxgroup','a3','')
			->set_extra('<h3>Del manejo y conocimiento de los contenidos</h3>');
		$edit->field('dropdown','p','17.- Dominio del contenido de la asignatura')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','q','18.- Vocabulario con el que se expresa')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','r','19.- Relación de los contenidos con el ejercicio profesional')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','s','20.- Nivel de profundidad en los contenidos')
			->option('','Seleccione')->options($opt)
			->rule('required');
			
		$edit->field('checkboxgroup','a4','')
			->set_extra('<h3>De la evaluación de la asignatura</h3>');
		$edit->field('dropdown','t','21.- Planificación de las evaluaciones')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','u','22.- Estrategias de evaluación de la asignatura')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','v','23.- Claridad en los criterios y objetivos usados en las evaluaciones')
			->option('','Seleccione')->options($opt)
			->rule('required');
		$edit->field('dropdown','w','24.- Manera de transmitir los resultados de las evaluaciones')
			->option('','Seleccione')->options($opt)
			->rule('required');
			
		$edit->field('checkboxgroup','a5','')
			->set_extra('<h3>De lo general</h3>');
		$edit->field('dropdown','x','25.- ¿Cómo cataloga la calidad del trabajo desarrollado por el profesor?')
			->option('','Seleccione')->options($opt)
			->rule('required');

		$edit->buttons('modify','save','undo','back','delete');
			
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['home_url']   = $back;
		$data['header']     = 'Evaluación del curso';
		$data['title']      = 'Evaluación del curso';
		$data['footer']     = '';
		$this->load->view('view_ven', $data);
	}

}
