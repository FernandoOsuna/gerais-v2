<?php
class welcome_controller extends rpd {


	function index()
	{
		echo $this->view('welcome');
	}

	function test()
	{
		echo 'test';
	}

}
?>