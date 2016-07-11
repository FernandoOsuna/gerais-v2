<?php

class rpd_auth_library {

	public $namespace = '';
	public $cookie    = true;
	public $table     = 'integrantes';
	public $username  = 'usuario';
	public $password  = 'clave';	
	public $course	  = 'id_curso';
	private $key      = '';
	private $data     = array();
	public $duration  = 0; //0 : singola sessione, time()+60*60*24*30 : un mese

	public function __construct($config = array()){
		foreach($config as $property=>$value){
			$this->$property = $value;
		}
		include(RAPYD_ROOT.'../application/config/database.php');
		include(RAPYD_ROOT.'../application/config/config.php');
		$this->key = $config['encryption_key'];
		$ees=sha1($db['default']['dbprefix'].$db['default']['database'].$db['default']['username'].$db['default']['dbdriver'].$config['encryption_key']);
		$this->namespace=$ees;
	}

	// --------------------------------------------------------------------
	function encrypt($data) {
		$result = '';
		for($i=0; $i<strlen($data); $i++) {
			$char = substr($data, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}
		return base64_encode($result);
	}

	function decrypt($data) {
		$result = '';
		$data = base64_decode($data);
		for($i=0; $i<strlen($data); $i++) {
			$char = substr($data, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
		}
		return $result;
	}

	/**
	 * prova a validare un login con user/password,  setta una variabile di sessione, opzionalmete salva un cookie
	 * da usare nella pagina di login.
	 */
	function login($username, $password, $course){
		//$password_hash = md5($password);
		rpd::connect();
		//rpd::$db->db_debug = true;
		if ($course>0){
			
			rpd::$db->from($this->table);
			rpd::$db->where($this->username, $username);
			rpd::$db->where($this->password, $password);
			//rpd::$db->where('role_id<=2');
			rpd::$db->get();
	
			$user = rpd::$db->row_object();
			
			rpd::$db->from('curso');		
			rpd::$db->where('id', $course);
			rpd::$db->get();
	
			$sc = rpd::$db->row_object();
			
			if ($sc===false) return false;
			
			rpd::$db->from('integcurso');		
			rpd::$db->where('id_curso', $course);
			rpd::$db->where('id_integrante', $user->id);
			rpd::$db->where('semestre', $sc->semestre);
			//rpd::$db->where('role_id<=2');
			rpd::$db->get();
	
			$datos = rpd::$db->row_object();
			
			if ($user===false || $datos===false) return false;		

			//1 profesor
			//2 alumno gerente
			//3 alumno director
			//4 alumno profesional
			if($user->tipo=='P' || $user->tipo=='S'){
				$role=1;
				$sec=1;
			}else{
				if($datos->cargo=='G'){
					$role=2;
				}elseif($datos->cargo=='D'){
					$role=3;
				}else{
					$role=4;
				}
				$sec=$datos->seccion;
			}
			
			
			
			
			$user_session = array (
				'user_name'  => $username,
				'password'   => $password,
				'name'       => $user->nombre.' '.$user->apellido,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'role_id'    => $role,
				'id_comp'    => $datos->id_compania,
				'id_int'     => $user->id,
				'correo'	 => $user->correo,
				'tipo'		 => $user->tipo,
				'id_curso'	 => $datos->id_curso,
				'semestre'	 => $datos->semestre,
				'seccion'	 => $sec
			);
			
		}
		else {
			rpd::$db->from($this->table);
			rpd::$db->where($this->username, $username);
			rpd::$db->where($this->password, $password);		
			rpd::$db->where('tipo', 'S');
			//rpd::$db->where('role_id<=2');
			rpd::$db->get();
	
			$user = rpd::$db->row_object();
			
			//var_dump($user);
			if ($user===false) return false;
		
			//1 profesor
			//2 alumno gerente
			//3 alumno director
			//4 alumno profesional
			
			$role=1;
			$sec=1;			
			
			$user_session = array (
				'user_name'  => $username,
				'password'   => $password,
				'name'       => $user->nombre.' '.$user->apellido,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'role_id'    => $role,
				'id_comp'    => $user->id_compania,
				'id_int'     => $user->id,
				'correo'	 => $user->correo,
				'tipo'		 => $user->tipo,
				'id_curso'	 => $user->id_curso,
				'seccion'	 => $sec
			);			
		}

		$_SESSION[$this->namespace] = $this->encrypt(serialize($user_session));

		if($this->cookie) setcookie($this->namespace, $this->encrypt(serialize($user_session)), $this->duration, '/', '', 0);
		return true;
	}
	
	/**
	 * prova a validare un login con user/password
	 * 
	 */
	function vincular($username, $password){
		//$password_hash = md5($password);
		rpd::connect();
		//rpd::$db->db_debug = true;
		rpd::$db->from($this->table);
		rpd::$db->where($this->username, $username);
		rpd::$db->where($this->password, $password);
		//rpd::$db->where('role_id<=2');
		rpd::$db->get();

		$user = rpd::$db->row_object();
		//var_dump($user);
		if ($user===false) return false;

		//1 profesor
		//2 alumno gerente
		//3 alumno director
		//4 alumno profesional
		if($user->tipo=='P' || $user->tipo=='S'){
			$role=1;
		}else{
				$role=4;
			}
		
		$user_session = array (
			'user_name'  => $username,
			'password'   => $password,
			'name'       => $user->nombre.' '.$user->apellido,
			'ip_address' => $_SERVER['REMOTE_ADDR'],
			'role_id'    => $role,
			'id_comp'    => 0,
			'id_int'     => $user->id,
			'correo'	 => $user->correo,
			'tipo'		 => $user->tipo,
			'id_curso'	 => 0
		);
		$_SESSION[$this->namespace] = $this->encrypt(serialize($user_session));

		if($this->cookie) setcookie($this->namespace, $this->encrypt(serialize($user_session)), $this->duration, '/', '', 0);
		return true;
	}
	
	/**
	 * prova a validare un login con user/password
	 * 
	 */
	function validar($username, $password, $course){		
					
		rpd::$db->from($this->table);
		rpd::$db->where($this->username, $username);
		rpd::$db->where($this->password, $password);
		//rpd::$db->where('role_id<=2');
		rpd::$db->get();
	
		$user = rpd::$db->row_object();
		
		rpd::$db->from('curso');		
		rpd::$db->where('id', $course);
		rpd::$db->get();
	
		$curso = rpd::$db->row_object();
			
		rpd::$db->from('integcurso');		
		rpd::$db->where('id_curso', $course);
		rpd::$db->where('id_integrante', $user->id);
		rpd::$db->where('semestre', $curso->semestre);
		//rpd::$db->where('role_id<=2');
		rpd::$db->get();
	
		$datos = rpd::$db->row_object();
			
		if ($datos===false) return true;
		
		
		return false;
	}

	/**
	 * se il cookie esiste ed è valido, o comunque esiste una sessione utente, restituisce i dati.
	 */
	function logged($rol=0){
		if ($this->cookie AND !isset($_SESSION[$this->namespace])){
			if(!isset($_COOKIE[$this->namespace])) return false;
			$cookie = $_COOKIE[$this->namespace];
			$_SESSION[$this->namespace] = stripslashes($cookie);
		}
		$user = unserialize($this->decrypt($_SESSION[$this->namespace]));
		if($rol==0){
			if(!isset($user['ip_address']) || $user['ip_address']!=$_SERVER['REMOTE_ADDR'])
				return false;
			else
				return $user;
		}else{
			if(!isset($user['ip_address']) || $user['ip_address']!=$_SERVER['REMOTE_ADDR'] || $user['role_id']!=$rol)
				return false;
			else
				return $user;
		}
	}
	
	function chngsession($semestre){
		if ($this->cookie AND !isset($_SESSION[$this->namespace])){
			if(!isset($_COOKIE[$this->namespace])) return false;
			$cookie = $_COOKIE[$this->namespace];
			$_SESSION[$this->namespace] = stripslashes($cookie);
		}
		$user = unserialize($this->decrypt($_SESSION[$this->namespace]));
		
		$user_session = array (
			'user_name'  => $user['user_name'],
			'password'   => $user['password'],
			'name'       => $user['name'],
			'ip_address' => $user['ip_address'],
			'role_id'    => $user['role_id'],
			'id_comp'    => $user['id_comp'],
			'id_int'     => $user['id_int'],
			'correo'	 => $user['correo'],
			'tipo'		 => $user['tipo'],
			'id_curso'	 => $user['id_curso'],
			'semestre'	 => $semestre
		);
		$this->logout();	
		

		$_SESSION[$this->namespace] = $this->encrypt(serialize($user_session));

		if($this->cookie) setcookie($this->namespace, $this->encrypt(serialize($user_session)), $this->duration, '/', '', 0);
		return true;		
	}

	function id_comp(){
		if(isset($_SESSION[$this->namespace])){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['id_comp'];
		}else{
			return 0;
		}
	}

	function id_int(){
		if(isset($_SESSION[$this->namespace])){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['id_int'];
		}else{
			return 0;
		}
	}

	function correo(){
		if(isset($_SESSION[$this->namespace])){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['correo'];
		}else{
			return 0;
		}
	}

	function name(){
		if(isset($_SESSION[$this->namespace])){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['name'];
		}else{
			return 0;
		}
	}
	
	function user_name(){
		if(isset($_SESSION[$this->namespace])){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['user_name'];
		}else{
			return 0;
		}
	}
	
	function password(){
		if(isset($_SESSION[$this->namespace])){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['password'];
		}else{
			return 0;
		}
	}


	/**
	 * verifica che esista una sessione utente alrimenti ridireziona su una pagina di default
	 */
	function logged_or($location='index.php',$id_rol=0){
		if ($this->logged()){
			if($id_rol==0){
				return true;
			}else{
				$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
				$rol=$data['role_id'];
			}
		}

		session_write_close();
		header('Location: '.$location);
		exit;
	}


	/**
	 * restituisce l'array o una sottochiave dei dati utente
	 */
	function user($key=null){
		$user = $this->logged();
		if (!isset($key)){
			return $user;
		}elseif(isset($user[$key])){
			return $user[$key];
		}
		return false;
	}

	function role(){
		if ($this->logged()){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['role_id'];
		}else{
			return 0;
		}
	}
	
	function tipo(){
		if ($this->logged()){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['tipo'];
		}else{
			return 0;
		}
	}
	
	function id_curso(){
		if ($this->logged()){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['id_curso'];
		}else{
			return 0;
		}
	}
	
	function semestre(){
		if ($this->logged()){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['semestre'];
		}else{
			return 0;
		}
	}
	
	function seccion(){
		if ($this->logged()){
			$data=unserialize($this->decrypt($_SESSION[$this->namespace]));
			return $data['seccion'];
		}else{
			return 0;
		}
	}

	/**
	 * rimuove cookie e sessione, da usare al logout
	 */
	function logout(){
		setcookie($this->namespace, '', 0, '/', '', 0);
		$_SESSION[$this->namespace]='';
		session_destroy();
	}
}
