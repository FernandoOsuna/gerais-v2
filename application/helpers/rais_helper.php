<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Rais Helper
 *
 * @package    	helpers
 * @author		Andres Hocevar <aahahocevar@gmail.com>
 */

/**
 * Color
 *
 * Funcion que retorna el color a usar en el hito
 *
 * @access	public
 * @param	float	$i Flotante entre 0 y 100
 * @return	string	Color en RGB
 */
function color($i=0){
	$max=255;
	$min=0;

	$r=($i<50)? $max: ceil(-($i-50)*(($max-$min)/50)  +$max);
	$g=($i<50)? ceil(-$i*(($min-$max)/50)  +$min): $max;
	$b=0;
	$color=str_pad( strtoupper(dechex($r)),2, '0', STR_PAD_LEFT).str_pad( strtoupper(dechex($g)),2, '0', STR_PAD_LEFT).str_pad( strtoupper(dechex($b)),2, '0', STR_PAD_LEFT);
	return $color;
}

/**
 * Salud
 *
 * Funcion que retorna la salud de la compañía
 *
 * @access	public
 * @param	float	$i Flotante
 * @return	int	    Nivel de salud [1,5]
 */
function salud($i=0){
	if($i<=10){
		$rt=5;
	}elseif($i>10 && $i<=20){
		$rt=4;
	}elseif($i>20 && $i<=30){
		$rt=3;
	}elseif($i>30 && $i<=40){
		$rt=2;
	}else{
		$rt=1;
	}
	return $rt;
}
