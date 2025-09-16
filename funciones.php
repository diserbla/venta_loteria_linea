<?php if(!session_id()) {session_start();}
       header('Content-Type: application/json');
	include($_SESSION['bd']."conexion.php");	
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	

	require_once '../ventas/funciones.php';
	require_once '../ventas/funciones_lottired.php';

    $id_usu = $_SESSION['id_usu'];	
	
	$paso     =$_REQUEST['paso'];
	
	if($paso == 'evalua_conexion')
	{
		echo 'merca';
	}
?>