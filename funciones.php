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

	if($paso=='valida_premio')
	{
		$barcode = $_POST['barcode'];
		$id_usu  = $_POST['id_usu'];
		$error   = "";

		if (empty($barcode)) {
			$error = "Barcode no proporcionado";
		} else {
			// Validar formato del barcode (debe ser numérico y tener longitud adecuada)
			if (!is_numeric($barcode)) {
				$error = "El barcode debe contener solo números";
			} elseif (strlen($barcode) < 11) {
				$error = "El barcode debe tener al menos 8 caracteres";
			} else {
				try {
					$db->beginTransaction();

					// Actualizar el campo valida_premio de 'N' a 'S'
					$sql = "UPDATE movlottired
							SET valida_premio = 'S',
								usr_ult_modif = :id_usu
							WHERE barcode = :barcode
							";

					$stmt = $db->prepare($sql);
					$stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
					$stmt->bindParam(':id_usu', $id_usu, PDO::PARAM_STR);
					$stmt->execute();

					/*
					// Verificar si se actualizó algún registro
					if ($stmt->rowCount() == 0) {
						$error = "Premio no encontrado o ya fue validado previamente";
					}
						*/

					$db->commit();
				} catch(PDOException $e) {
					$error = "Error al validar premio: " . $e->getMessage();
					$db->rollBack();
				}
			}
		}

		$arreglo = array('error' => $error, 'barcode' => $barcode);
		print json_encode($arreglo);
	}
?>

