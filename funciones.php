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

	if ($paso == 'grabar_venta_ltr') {
		// ---------- 1️⃣  Recuperar variables enviadas ----------
		$id_usu   = isset($_POST['id_usu'])   ? $_POST['id_usu']   : null;
		$pto_vta  = isset($_POST['pto_vta'])  ? $_POST['pto_vta']  : null;
		$cliente  = isset($_POST['cliente'])  ? $_POST['cliente']  : null;   // array / objeto
		$venta    = isset($_POST['venta'])    ? $_POST['venta']    : null;   // array
		$premios  = isset($_POST['premios'])  ? $_POST['premios']  : null;   // array
		$totales  = isset($_POST['totales'])  ? $_POST['totales']  : null;   // array / objeto

		// ---------- 2️⃣  Detectar parámetros faltantes ----------
		$faltantes = array();

		if (empty($id_usu))   $faltantes[] = 'id_usu';
		if (empty($pto_vta))  $faltantes[] = 'pto_vta';
		if (empty($cliente))  $faltantes[] = 'cliente';
		if (empty($totales))  $faltantes[] = 'totales';

		/*
		*  En la lógica original se añadían 'venta' y 'premios' como
		*  obligatorios al mismo tiempo.  Ahora solo se exige que
		*  **al menos uno** de los dos esté presente.
		*/
		if (empty($venta) && empty($premios)) {
			// Ninguno de los dos tiene datos → se indica que falta al menos uno
			$faltantes[] = 'venta o premios';
		}

		// ---------- 3️⃣  Preparar respuesta ----------
		if (!empty($faltantes)) {
			$respuesta = array(
				'error'     => 'Faltan parámetros: ' . implode(', ', $faltantes),
				'mensaje'   => null,
				'recibidos' => null
			);
			echo json_encode($respuesta);
			exit;
		}


		//error_log('Mensaje de depuración', 3, 'C:/mercapos/htdocs/formas/pruebas/debug.log');

		/* -------------------------------------------------------------
		4️⃣  Preparar los totales (todos vienen como strings sin
			símbolos de moneda, ya fueron limpiados en el front‑end)
		------------------------------------------------------------- */
		$tot_venta            = isset($totales['totalVenta'])   ? $totales['totalVenta']   : 0;
		$tot_pagar_cliente    = isset($totales['valorPagar'])   ? $totales['valorPagar']   : 0;
		$tot_premios          = isset($totales['totalPremios']) ? $totales['totalPremios'] : 0;
		$tot_pagar_premios    = $tot_premios;                     // en este caso el mismo valor
		$dinero_efectivo      = isset($totales['efectivo'])   ? $totales['efectivo']   : 0;
		$dinero_cambio        = $dinero_efectivo - $tot_pagar_cliente;
		$id_usu_vendedor      = $id_usu;   // mismo usuario que ejecuta la venta

		/*
		// ---------- 3️⃣  LOG DE DEPURACIÓN ----------
		$msg = ">>> LOG DE DEPURACIÓN\n";
		$msg .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
		$msg .= "id_usu: $id_usu\n";
		$msg .= "pto_vta: $pto_vta\n";
		$msg .= "cliente: " . print_r($cliente, true) . "\n";
		$msg .= "venta: " . print_r($venta, true) . "\n";
		$msg .= "premios: " . print_r($premios, true) . "\n";
		$msg .= "totales: " . print_r($totales, true) . "\n";
		$msg .= "--------------------------\n";

		error_log($msg, 3, 'C:/mercapos/htdocs/formas/pruebas/debug.log');
		*/

		/* -------------------------------------------------------------
		5️⃣  INSERTAR EN LA BASE DE DATOS (transacción)
		------------------------------------------------------------- */
		try {
			$db->beginTransaction();

			/* ---------------------------------------------------------
			5.1  Cabecera de la venta (ventas_mae)
			--------------------------------------------------------- */
			$sqlCabecera = "
				INSERT INTO ventas_mae
					(id_usu, fec_venta, cod_pto, tot_venta, tot_pagar_cliente,
					tot_premios, tot_pagar_premios,
					dinero_efectivo, dinero_cambio, usr_ult_modif, fec_ult_modif)
				VALUES
					(:id_usu, NOW(), :cod_pto, :tot_venta, :tot_pagar_cliente,
					:tot_premios, :tot_pagar_premios,
					:dinero_efectivo, :dinero_cambio, :usr_modif, NOW())
			";

			$stmtCab = $db->prepare($sqlCabecera);
			$stmtCab->bindParam(':id_usu',              $id_usu_vendedor, PDO::PARAM_STR);
			$stmtCab->bindParam(':cod_pto',             $pto_vta,         PDO::PARAM_STR);
			$stmtCab->bindParam(':tot_venta',           $tot_venta,       PDO::PARAM_STR);
			$stmtCab->bindParam(':tot_pagar_cliente',   $tot_pagar_cliente, PDO::PARAM_STR);
			$stmtCab->bindParam(':tot_premios',         $tot_premios,     PDO::PARAM_STR);
			$stmtCab->bindParam(':tot_pagar_premios',   $tot_pagar_premios, PDO::PARAM_STR);
			$stmtCab->bindParam(':dinero_efectivo',     $dinero_efectivo, PDO::PARAM_STR);
			$stmtCab->bindParam(':dinero_cambio',       $dinero_cambio,   PDO::PARAM_STR);
			$stmtCab->bindParam(':usr_modif',           $id_usu_vendedor, PDO::PARAM_STR);
			$stmtCab->execute();

			// Obtener el id de la venta recién insertada
			$stmtId = $db->query("SELECT currval(pg_get_serial_sequence('ventas_mae','id_venta'))");
			$id_venta = $stmtId->fetchColumn();

			// -------------------------------------------------------------
			// 5️⃣  INSERTAR DETALLE DE VENTA (tabla ventas_det)
			// -------------------------------------------------------------
			//  •  Cada registro de $venta corresponde al código 114
			//  •  Cada registro de $premios corresponde al código 116
			//  •  Si hay varios ítems en el mismo arreglo, se **suman** los
			//     valores y se inserta **un solo registro** por código (la PK es
			//     (id_venta, codigo)).
			// -------------------------------------------------------------

			// --- Calcular totales por código ---------------------------------
			$valor_venta   = 0;   // suma de todos los valores de la venta
			$valor_premios = 0;   // suma de todos los valores de los premios

			if (!empty($venta) && is_array($venta)) {
				foreach ($venta as $item) {
					// $item['valor'] viene como string, lo convertimos a número
					$valor_venta += (float)str_replace(',', '.', $item['valor']);
				}
			}

			//error_log($valor_venta, 3, 'C:/mercapos/htdocs/formas/pruebas/debug.log');

			if (!empty($premios) && is_array($premios)) {
				foreach ($premios as $item) {
					$valor_premios += (float)str_replace(',', '.', $item['valor']);
				}
			}

			//error_log($valor_premios, 3, 'C:/mercapos/htdocs/formas/pruebas/debug.log');

			// --- Preparar sentencia INSERT (PDO) ----------------------------
			$sqlDetalle = "
				INSERT INTO ventas_det
					(id_venta, codigo, valor, usr_ult_modif, fec_ult_modif)
				VALUES
					(:id_venta, :codigo, :valor, :usr_modif, NOW())
			";

			/*  La tabla tiene una PK (id_venta, codigo), por lo que sólo
				insertamos una fila por cada código (114 = venta, 116 = premio).   */
			$stmtDet = $db->prepare($sqlDetalle);

			// --- Insertar registro de venta (código 114) --------------------
			if ($valor_venta > 0) {
				$stmtDet->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
				$stmtDet->bindParam(':codigo',   $codigo_venta = 114, PDO::PARAM_INT);
				$stmtDet->bindParam(':valor',    $valor_venta, PDO::PARAM_STR);
				$stmtDet->bindParam(':usr_modif',$id_usu_vendedor, PDO::PARAM_STR);
				$stmtDet->execute();

				// ---------------------------------------------------------
				//  ✅  LLAMAR A fn_venta_terceros() cuando la condición se cumple
				// ---------------------------------------------------------
				fn_venta_terceros(
					$db,
					$id_venta,
					$venta,
					$cliente,
					$pto_vta,
					$id_usu_vendedor
				);
			}

			// --- Insertar registro de premios (código 116) ------------------
			if ($valor_premios > 0) {
				$stmtDet->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
				$stmtDet->bindParam(':codigo',   $codigo_premio = 116, PDO::PARAM_INT);
				$stmtDet->bindParam(':valor',    $valor_premios, PDO::PARAM_STR);
				$stmtDet->bindParam(':usr_modif',$id_usu_vendedor, PDO::PARAM_STR);
				$stmtDet->execute();
			}

			// ---------------------------------------------------------
			//  ✅  CONFIRMAR LA TRANSACCIÓN
			// ---------------------------------------------------------
			$db->commit();          

			$respuesta = array(
				'error'     => null,
				'mensaje'   => 'Venta grabada con éxito. ID de venta: ' . $id_venta,
				'recibidos' => array(
					'id_venta' => $id_venta,
					'cliente'  => $cliente,
					'venta'    => $venta,
					'premios'  => $premios,
					'totales'  => $totales
				)
			);
			
		} catch (Exception $e) {
			// Si algo falla, revertir todo
			$db->rollBack();

			$respuesta = array(
				'error'   => 'Error al grabar la venta: ' . $e->getMessage(),
				'mensaje' => null,
				'recibidos' => null
			);
		}

		/* -------------------------------------------------------------
		6️⃣  Enviar JSON al cliente
		------------------------------------------------------------- */
		echo json_encode($respuesta);
		exit;   // detener la ejecución del script
	}
?>

