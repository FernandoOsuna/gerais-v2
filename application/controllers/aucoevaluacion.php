<?php
/**
  * Clase para la gestión de las Auto y Co Evaluaciones.
  *
  * @autor  Andres Hocevar <aahahocevar@gmail.com>
  * @autor  Fernando Osuna
  * @package controllers
  */
class aucoevaluacion extends CI_Controller {
/**
 *  Título.
 */
	var $titp='Auto y Co evaluaci&oacute;n';
/**
 *  Dirección url de la clase.
 */
	var $url ='aucoevaluacion/';

	function index(){

	}
/**
  * CRUD para los registro de Auto y Co evaluación
  *
  * @since 1.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla aucoevaluacion.
  */
	function dataedit(){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_cour = $ut->id_curso();
		if($rt===false) die('Acceso no permitido');

		$back='dashboard/autco';

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		//$delete_url = rpd_url_helper::replace('modify' . $edit->cid, 'delete' . $edit->cid);
		//$action = "javascript:window.location.href='" . $delete_url . "'";
		//$edit->button("btn_delete", 'Borrar', $action, "TR");

		$edit->label    = $this->titp;
		$edit->back_url = site_url($back);

		$edit->source('aucoevaluacion');
		$edit->pre_process(array('insert','update'), array($this, 'pre_inserup'));
		$edit->pre_process(array('delete'), array($this, 'pre_delete'));
		$edit->field('date'    ,'fecha_inicio','Fecha de inicio' )->rule('trim|required|chfecha')->insertValue=date('Y/m/d');
		$edit->field('input'   ,'plazo'       ,'Plazo de entrega en d&iacute;as')->rule('numeric|required|in_range[[1,5]]');
		$edit->field('textarea','comentario'  ,'Comentario'      )->rule('trim|required');
		$edit->field('hidden','id_curso','')->insert_value=$id_cour;
		$edit->field('hidden','semestre','')->insert_value=$ut->semestre();

		$edit->field('hidden','estampa','')->insert_value=date('Y-m-d H:i:s');
		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Auto/Co Evaluaci&oacute;n';
		$data['title']      = 'Auto/Co Evaluaci&oacute;n';
		$data['footer']     = '';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');
		$this->load->view('view_ven', $data);
	}

/**
  * CRUD para los registro detalles de las Auto y Co Evaluación
  *
  * @since 1.0
  *
  * @return void
  * @param int      $id_int  Clave primaria de registro en la tabla integrantes.
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla productos.
  */
	function pedataedit($id_int,$status,$id){
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		if($rt===false) die('Acceso no permitido');

		$back='dashboard/gintegrante/'.$id_int;

		$edit = new dataedit_library();
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		$edit->label    = $this->titp;
		$edit->back_url = site_url($back);

		$edit->source('penalizaciones');
		$edit->pre_process(array('insert'), array($this, 'pre_itfalso'));
		$edit->pre_process(array('delete'), array($this, 'pre_itfalso'));
		$edit->field('date'     ,'fecha','Fecha de la pena' )->mode='autohidden';
		$edit->field('dropdown','exonerada' ,'Exonerado')
		->options(array('S'=>'Sí','N'=>'No'))
		->rule('required');

		$edit->buttons('modify','save','undo','back');
		$edit->build();

		$data['content']    = $edit;
		$data['back_url']   = $back;
		$data['header']     = 'Penalizaciones';
		$data['title']      = 'Penalizaciones';
		$data['footer']     = '';
		$data['headerextra'] = 'Profesor: ';
		$data['headerextra'].= $ut->user('name');
		$this->load->view('view_ven', $data);
	}

/**
  * Pre Proceso de borrado de registro
  * Evita el borrado en los CRUD donde se llama a este proceso
  *
  * @since 1.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla productos.
  */
	function pre_itfalso($model){
		$model->error_string = 'No se puede eliminar desde este modulo';
		return false;
	}

/**
  * Pre Proceso de borrado de registro
  * Evita que sea borrada una evaluación que ya fue ejecutada por al menos 1 alumno
  *
  * @since 1.0
  *
  * @return void
  * @param string   $status Tipo de acción a ejecutar puede ser create,modify,show,delete.
  * @param int      $id     Clave primaria de registro en la tabla productos.
  */
	function pre_delete($model){
		$cana=0;
		$this->db->from('aucoevaluacion_it AS a');
		$this->db->where('id_aucoevaluacion',$model->pk['id']);

		$cana+=$this->db->count_all_results();
		if($cana>0){
			$model->error_string = 'No se puede eliminar la evaluaci&oacute;n por que tiene resultados';
			return false;
		}
		return true;
	}

/**
  * Pre Proceso de inserción y actualización
  * Evita que las evaluaciones se solapen
  *
  * @since 1.0
  *
  * @return void
  * @param object   $model Modelo de la tabla aucoevaluacion.
  */
	function pre_inserup($model){
		$inicio= $model->get('fecha_inicio');
		$plazo = $model->get('plazo');
		
		$id_cour = $model->get('id_curso');

		$date  = date_create($inicio);
		$fechai= date_format($date, 'Ymd');
		$intev = date_interval_create_from_date_string($plazo.' days');
		date_add($date,$intev);
		$fechaf= date_format($date, 'Ymd');

		$cana=0;
		$this->db->from('aucoevaluacion AS a');
		$this->db->where('fecha_inicio >=',$fechai);
		$this->db->where('fecha_inicio <=',$fechaf);
		$this->db->where('id_curso', $id_cour);
		if(!empty($model->pk['id'])){
			$this->db->where('a.id <>',$model->pk['id']);
		}
		$cana+=$this->db->count_all_results();

		$this->db->from('aucoevaluacion AS a');
		$this->db->where('ADDDATE( fecha_inicio , plazo ) >=',$fechai);
		$this->db->where('ADDDATE( fecha_inicio , plazo ) <=',$fechaf);
		$this->db->where('id_curso', $id_cour);
		if(!empty($model->pk['id'])){
			$this->db->where('a.id <>',$model->pk['id']);
		}
		$cana+=$this->db->count_all_results();

		if($cana>0){
			$model->error_string = '--La fecha propuesta colisiona con otra evaluaci&oacute;n';
			return false;
		}
		return true;
	}

/**
  * Método para guardar el detalle de las Auto y Co Evaluaciones
  *
  * @since 1.0
  *
  * @return void
  */
	function ejecuta(){
		$this->load->library('rapyd');
		$ut     = new rpd_auth_library();
		$rt     = $ut->logged();
		if($rt===false) die('Acceso no permitido');
		$id_int     = $ut->id_int();
		$id_compania= $ut->id_comp();
		$id_cour = $ut->id_curso();
		$fecha = date('Ymd');

		$this->db->from('aucoevaluacion AS a');
		$this->db->join('aucoevaluacion_it AS b','b.id_aucoevaluacion=a.id AND b.id_evaluador='.$this->db->escape($id_int),'left');
		$this->db->where('ADDDATE( a.fecha_inicio , plazo ) >=',$fecha);
		$this->db->where('a.fecha_inicio <=',$fecha);
		$this->db->where('b.id IS NULL');
		//$this->db->where('a.id_curso',$id_cour);
		$aucana = $this->db->count_all_results();

		if($aucana>0){
			$back = 'dashboard/gcompalu';

			$edit = new dataform_library();
			$edit->validation->set_message('required','Falta la evaluación de %s');

			$edit->label = $this->titp;
			$edit->back_url = site_url($back);

			$sel=array('a.id','a.nombre','a.apellido',"IF($id_int =a.id,0,1) AS ord");
			$this->db->select($sel);
			$this->db->from('integrantes AS a');
			$this->db->join('integcurso AS b','a.id=b.id_integrante', 'left');
			$this->db->where('b.id_compania',$id_compania);
			$this->db->order_by('ord,a.nombre');
			$query = $this->db->get();

			$comprom=array();
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$id = $row->id;
					$nom= $row->nombre.' '.$row->apellido;
					$edit->field('dropdown',"inte$id",$nom)
					->rule('required')
					->options(array(
						'' =>'Seleccionar',
						'1'=>'Pésimo',
						'2'=>'Malo',
						'3'=>'Regular',
						'4'=>'Bueno',
						'5'=>'Excelente'
					));
				}
			}

			$edit->field('hidden','estampa','')->insert_value=date('Y-m-d H:i:s');

			$edit->buttons('save');
			$edit->build();

			if ($edit->on('success')){
				foreach($_POST as $ind=>$val){
					$fecha=date('Y-m-d');
					$this->db->select(array('id'));
					$this->db->from('aucoevaluacion');
					$this->db->where('ADDDATE( fecha_inicio , plazo ) >=',$fecha);
					$this->db->where('fecha_inicio  <=',$fecha);
					$query = $this->db->get();
					$row = $query->row();
					if ($query->num_rows() > 0){
						$id_aucoevaluacion  =$row->id;

						if(preg_match('/^inte(?P<id>\d+)$/',$ind, $matches)){
							$id   = $matches['id'];
							$data = array(
								'id_aucoevaluacion' => $id_aucoevaluacion,
								'id_evaluador'      => $id_int,
								'id_evaluado'       => $id,
								'resultado'         => $val
							);

							$this->db->insert('aucoevaluacion_it', $data);
						}
					}
				}
				$back = 'dashboard/gcompalu';
				$data['content']    = 'Evaluaci&oacute;n ya fue guardada';
				$data['home_url']   = 'dashboard/gcompalu';
				$data['back_url']   = $back;
				$data['header']     = 'Evaluaci&oacute;n por '.$ut->user('name');
				$data['title']      = 'Evaluaci&oacute;n por '.$ut->user('name');
				$data['footer']     = '';
				$this->load->view('view_ven', $data);
				return;
			}

			$msj='<p><b>Nota</b>: Tenga en cuenta que las evaluaciones después de guardadas no podrán cambiarse.</p>';
			$data['content']    = $msj.$edit;
			$data['back_url']   = $back;
			$data['home_url']   = 'dashboard/gcompalu';
			$data['header']     = 'Evaluaci&oacute;n por '.$ut->user('name');
			$data['title']      = 'Evaluaci&oacute;n por '.$ut->user('name');
			$data['footer']     = '';
			$this->load->view('view_ven', $data);
		}else{
			$back = 'dashboard/gcompalu';
			$data['content']    = 'Evaluaci&oacute;n ya fue guardada';
			$data['home_url']   = 'dashboard/gcompalu';
			$data['back_url']   = $back;
			$data['header']     = 'Evaluaci&oacute;n por '.$ut->user('name');
			$data['title']      = 'Evaluaci&oacute;n por '.$ut->user('name');
			$data['footer']     = '';
			$this->load->view('view_ven', $data);
		}
	}

/**
  * Reporte de notas en xls
  *
  * @since 1.0
  *
  * @return void
  */
	function reporte() {
		$this->load->library('rapyd');
		$ut= new rpd_auth_library();
		$rt=$ut->logged(1);
		$id_cour = $ut->id_curso();
		$semestre=$ut->semestre();
		if($rt===false) die('Acceso no permitido');

		$sel=array('nombre','profesor','semestre');
		$this->db->select($sel);
		$this->db->from('curso AS a');
		$this->db->where('a.id',$id_cour);
		$this->db->limit(1);
		$query = $this->db->get();
		$rrow = $query->row();

		$curso = $rrow->nombre;
		$profe = $rrow->profesor;
		$semes = $rrow->semestre;

		$fnombre='notas.xls';
		$fname = tempnam('/tmp',$fnombre);

		$this->load->library('workbook', array('fname'=>$fname));
		$wb = & $this->workbook ;
		$ws = & $wb->addworksheet('01');

		// ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',8);
		$ws->set_column('B:B',20);
		$ws->set_column('C:C',35);
		$ws->set_column('D:H',15);
		$ws->set_column('I:I',35);

		// FORMATOS
		$h       =& $wb->addformat(array( "bold" => 1, "size" => 14, "align" => 'left'));
		$h0      =& $wb->addformat(array( "bold" => 1, "size" => 10, "align" => 'left'));
		$codesc  =& $wb->addformat(array( "bold" => 0, "size" => 8 , "align" => 'left', "fg_color" => 26  ));
		$codesc->set_border(1);
		$numcer  =& $wb->addformat(array( "bold" => 0, "size" => 8 , "align" => 'right', "fg_color" => 26  ));
		$numcer->set_border(1);
		$numpri  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 8 , "fg_color" => 44 ));
		$numpri->set_border(1);
		$numseg  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 8 , "fg_color" => 42 ));
		$numseg->set_border(1);
		$numter  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 8 , "fg_color" => 41 ));
		$numter->set_border(1);
		$numcua  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 8 , "fg_color" => 41 ));
		$numcua->set_border(1);
		$numqui  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 8 , "fg_color" => 45 ));
		$numqui->set_border(1);

		$titulo  =& $wb->addformat(array( "bold" => 1, "size" => 8, "merge" => 1, "fg_color" => 'silver', 'align'=>'vcenter' ));
		$titulo->set_text_wrap();
		$titulo->set_text_h_align(2);
		$titulo->set_border(1);
		$titulo->set_merge();


		$cuerpo  =& $wb->addformat(array( 'size' => 9 ));

		$Tnumero =& $wb->addformat(array( 'num_format' => '#,##0.00' , 'size' => 9, 'bold' => 1, 'fg_color' => 'silver' ));
		$Rnumero =& $wb->addformat(array( 'num_format' => '#,##0.00' , 'size' => 9, 'bold' => 1, 'align'    => 'right' ));

		// COMIENZA A ESCRIBIR
		$ws->write(1, 1, 'Notas GeRAIS de fecha '.date('d/m/Y H:i:s') , $h );
		$ws->write(2, 1, utf8_decode($profe.' :: '.$curso.' :: '.$semes), $h0 );

		// TITULOS
		$mm=4;
		$ws->write_string( $mm, 0, utf8_decode('Sección'), $titulo );
		$ws->write_string( $mm, 1, utf8_decode('Cédula'), $titulo );
		$ws->write_string( $mm, 2, 'Nombre'         , $titulo );
		$ws->write_string( $mm, 3, 'Nota Producto'  , $titulo );
		$ws->write_string( $mm, 4, 'Nota Individual', $titulo );
		$ws->write_string( $mm, 5, utf8_decode('Co-Evaluación')  , $titulo );
		$ws->write_string( $mm, 6, utf8_decode('Auto-Evaluación'), $titulo );
		$ws->write_string( $mm, 7, 'Penalizaciones'         , $titulo );
		$ws->write_string( $mm, 8, utf8_decode('Compañía')  , $titulo );

		$mm=$mm+1;
		$dd=$mm+1;

		$dbprefix=$this->db->dbprefix;
		$mSQL = "SELECT a.apellido,a.nombre,a.id,c.id_compania,a.cedula, c.seccion, c.semestre, b.nombre AS cnombre
		FROM ${dbprefix}integrantes AS a
		LEFT JOIN ${dbprefix}integcurso AS c ON a.id=c.id_integrante 
		LEFT JOIN ${dbprefix}compania AS b ON c.id_compania=b.id
		WHERE a.tipo='A' AND c.id_curso=$id_cour AND c.semestre='$semestre'
		ORDER BY c.seccion ASC, a.apellido ASC";

		$mc=$this->db->query($mSQL);
		if($mc->num_rows() > 0){
			foreach( $mc->result() as $row ) {
				$nombre = $row->apellido.', '.$row->nombre;

				//Saca las penalizaciones
				$dbid_int = $this->db->escape($row->id);
				$mSQL     = "SELECT COUNT(*) AS pena FROM ${dbprefix}penalizaciones AS a JOIN compromisos AS b ON b.id=a.id_compromiso WHERE a.id_integrante=$dbid_int AND b.semestre='$semestre' AND a.exonerada='N'" ;
				$query    = $this->db->query($mSQL);
				$rrow     = $query->row();
				$penaliza = $rrow->pena  ;

				//Saca el promedio de la compañía
				$dbid_com = $this->db->escape($row->id_compania);
				$mSQL     = "SELECT AVG(ejecucion) AS prom FROM ${dbprefix}compromisos WHERE id_compania=$dbid_com";
				$query    = $this->db->query($mSQL);
				$rrow     = $query->row();
				$producto = $rrow->prom  ;


				//Saca el promedio individual
				$mSQL     = "SELECT SUM(IF(b.ejecucion=100,100,a.ejecucion)*a.peso)/SUM(a.peso) AS prom
				FROM ${dbprefix}tareas AS a
				JOIN ${dbprefix}compromisos AS b ON a.id_compromiso=b.id
				WHERE id_integrante=$dbid_int AND semestre='$semestre'";
				$query    = $this->db->query($mSQL);
				$rrow     = $query->row();
				$individuo= $rrow->prom  ;

				//Saca el resultado de la co evaluacion
				$mSQL     = "SELECT AVG(resultado) AS prom FROM ${dbprefix}aucoevaluacion_it AS a JOIN aucoevaluacion AS b ON b.id=a.id_aucoevaluacion WHERE id_evaluado=$dbid_int AND id_evaluador<>$dbid_int AND semestre='$semestre'";
				$query    = $this->db->query($mSQL);
				$rrow     = $query->row();
				$coeval = $rrow->prom  ;

				//Saca el resultado de la auto evaluacion
				$mSQL     = "SELECT AVG(resultado) AS prom FROM ${dbprefix}aucoevaluacion_it AS a JOIN aucoevaluacion AS b ON b.id=a.id_aucoevaluacion WHERE id_evaluado=$dbid_int AND id_evaluador=$dbid_int AND semestre='$semestre'";
				$query    = $this->db->query($mSQL);
				$rrow     = $query->row();
				$aueval   = $rrow->prom  ;

				$ws->write_string( $mm,  0,$row->seccion              , $codesc);
				$ws->write_string( $mm,  1,$row->cedula               , $codesc);
				$ws->write_string( $mm,  2,utf8_decode($nombre)       , $codesc);
				$ws->write_number( $mm,  3,round(20*$producto/100 ,2) , $numpri);
				$ws->write_number( $mm,  4,round(20*$individuo/100,2) , $numseg);
				$ws->write_number( $mm,  5,round(20*$coeval/5 ,2)     , $numter);
				$ws->write_number( $mm,  6,round(20*$aueval/5 ,2)     , $numter);
				$ws->write_number( $mm,  7,round($penaliza ,2)        , $numcua);
				$ws->write_string( $mm,  8,utf8_decode($row->cnombre) , $codesc);
				$mm++;
			}
		}

		$wb->close();
		header("Content-type: application/x-msexcel; name=\"$fnombre\"");
		header("Content-Disposition: inline; filename=\"$fnombre\"");
		$fh=fopen($fname,'rb');
		fpassthru($fh);
		unlink($fname);
	}
}
