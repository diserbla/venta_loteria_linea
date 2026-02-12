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
   		echo 'conexión OK';
    	exit;	
	}

	if ($paso == 'validar_cliente_existe')
	{
		$cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
		$error = '';
		$existe = false;

		if ($cedula === '') {
			$error = 'Debe enviar la cédula del cliente.';
		} else {
			try {
				$sql = "SELECT 1 FROM clientes WHERE cedula = :cedula LIMIT 1";
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
				$stmt->execute();
				$existe = (bool)$stmt->fetchColumn();
			} catch (PDOException $e) {
				$error = 'Error al validar cliente: ' . $e->getMessage();
			}
		}

		echo json_encode(array(
			'error' => $error,
			'existe' => $existe
		));
		exit;
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

	if ($paso == 'grabar_venta_ltr') 
	{
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
					'id_usu'   => $id_usu,
					'pto_vta'  => $pto_vta
					//'cliente'  => $cliente,
					//'venta'    => $venta,
					//'premios'  => $premios,
					//'totales'  => $totales
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

	if ($paso == 'reimprimir_venta') 
	{
		$id_venta = isset($_POST['id_venta']) ? $_POST['id_venta'] : null;

		$error = '';
		$mensaje = '';

		if (empty($id_venta)) {
			$error = 'ID de venta no proporcionado';
		} else {
			try {
				/* -------------------------------------------------
				La función ahora devuelve:
				- arre_totales
				- cliente
				- recibidos
				------------------------------------------------- */
				$data = fn_reimprimir_venta($db, $id_venta);
				//$mensaje = 'Reimpresión solicitada para la venta ID: ' . $id_venta;
			} catch (Exception $e) {
				$error = 'Error al reimprimir la venta: ' . $e->getMessage();
			}		
		}

		$respuesta = array(
			'error'   => $error,
        	// ← enviamos los tres arreglos al cliente
        	'data'    => $data
		);

		echo json_encode($respuesta);
		exit;
	}	

	if ($paso == 'cboPtoventa') //lista de valores con la tabla puntos de venta
	{
		
		$cod_pto      = $_SESSION['pto_vta'];
		$cboPtoventa  = $_POST['cbo_id'];
		$tipo         = $_POST['tipo'];
		
		if ($cod_pto == '99') //oficina ppal
		{
			$condicion_pto = '';
		}	
		else
		{
			$condicion_pto = "and cod_pto = '$cod_pto' ";	
		}
		
		$orden = " order by nombre ";		
		
		$sql="select cod_pto,mueble as nombre	
			  from pto_vta
			  where estado = 'A'".
			  $condicion_pto.$orden;
			  
		//echo $sql;
		//exit;

		$salida="<select id=\"".$cboPtoventa."\" name=\"".$cboPtoventa."\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
					
				if (($cod_pto == '99') && (($cboPtoventa != 'cboPtoventa') && ($cboPtoventa != 'cboPtoventa8'))) //esta evaluacion la hago para que en la 
				{																								 //lectura o en devolucion la solo me 
					//$salida=$salida. "<option value='*t*' >- SOLO CALI -</option>";								 //aparezcan los ptos
					$salida=$salida. "<option value='*-*' >- TODOS LOS PUNTOS -</option>";					
				}
				else
				{
					//$salida=$salida. "<option value='0'>PTO VENTA</option>"; //es importante que aparezca esta opcion para que seleccione el pto real
				}
			
				foreach ($db->query($sql) as $row)				
				{
					$salida=$salida."<option value=".$row['cod_pto'].">".$row['nombre']."</option>";
				}								  
		$salida=$salida."</select>";
	
		$arreglo = array ('salida'=>$salida);
		print json_encode($arreglo);
		
	}

	if ($paso == 'cboLoterias') //lista de valores 
	{
		$sql="select cod_lot,nombre_corto as nombre	
			  from maelote where cod_est_lot = 'A'
			  order by nombre_corto";
		
		$salida="<select id=\"cboLoterias\" name=\"cboLoterias\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
				$salida=$salida. "<option value='99'>Todas</option>";
				foreach ($db->query($sql) as $row)				
				{
					$salida=$salida."<option value=".$row['cod_lot'].">".$row['nombre'].'-'.$row['cod_lot']."</option>";		
				}								  
		$salida=$salida."</select>";
		$arreglo = array ('salida'=>$salida);
		print json_encode($arreglo);			
	}

	if ($paso == 'consulta_ppal') {	

		$cboPtoventa  		  = $_POST['cboPtoventa'];
		$cboLoterias  		  = $_POST['cboLoterias'];
		$rango_fechas 		  = $_POST['rango_fechas'];	
		$rango_fechas_sorteo  = $_POST['rango_fechas_sorteo'];	
		$rango_fechas_val_pre = $_POST['rango_fechas_val_pre'];	
		$cedula       		  = $_POST['cedula']; 
		$num_sor       		  = $_POST['num_sor']; 
		
		$error = "";
		$arre  = "";

		$condi_fecha = "";
		if (!empty($rango_fechas))
		{
			$arre = explode("-", $rango_fechas);
			
			$fec_ini = $arre[0];
			$fec_fin = $arre[1];

			$condi_fecha = " and vm.fec_venta::date between '$fec_ini' and '$fec_fin' ";

			$date1 = strtotime(str_replace('/', '-', $fec_ini));
			$date2 = strtotime(str_replace('/', '-', $fec_fin));
			
			if ($date1 !== false && $date2 !== false) {
				$diferencia_dias = floor(($date2 - $date1) / (60 * 60 * 24)) + 1;
			} else {
				// Manejar el caso en que las fechas no se puedan interpretar correctamente
			}
		}

		$condi_fecha_sorteo = "";
		if (!empty($rango_fechas_sorteo))
		{
			$arre = explode("-", $rango_fechas_sorteo);
			
			$fec_ini = $arre[0];
			$fec_fin = $arre[1];

			$condi_fecha_sorteo = " and mov.fec_sor_str::date between '$fec_ini' and '$fec_fin' ";

			$date1 = strtotime(str_replace('/', '-', $fec_ini));
			$date2 = strtotime(str_replace('/', '-', $fec_fin));
			
			//con esta condicion genera todo el movimiento de rango de fechas seleccionadas de los sorteos
			$condi_fecha = "";
			$diferencia_dias = 1;
		}

		$condi_fecha_val_premio = "";
		if (!empty($rango_fechas_val_pre))
		{
			$arre = explode("-", $rango_fechas_val_pre);
			
			$fec_ini = $arre[0];
			$fec_fin = $arre[1];

			$condi_fecha_val_premio = " and mov.fec_ult_modif::date between '$fec_ini' and '$fec_fin' ";

			$date1 = strtotime(str_replace('/', '-', $fec_ini));
			$date2 = strtotime(str_replace('/', '-', $fec_fin));
			
			//con esta condicion genera todo el movimiento de rango de fechas seleccionadas de los sorteos
			$condi_fecha = "";
			$diferencia_dias = 1;
		}		

		if ((!empty($diferencia_dias) && $diferencia_dias <= 31) || (strlen($condi_fecha_sorteo) > 0) || (strlen($num_sor) > 0) || 
			(strlen($cedula) > 0) || (strlen($condi_fecha_val_premio)))			
		{
			try {
				$db->beginTransaction();

                //select de acumulado VENTAS
                $sql_total ="SELECT 
                                mae.nombre_corto AS loteria,
                                mov.num_sor,
                                mov.fec_sor_str AS fec_sor,
                                sum(mov.fracciones) AS fracciones,
                                SUM(mov.vlr_fracciones) AS vlr_venta
                            FROM movlottired mov
                            INNER JOIN ventas_mae vm 
                                ON mov.id_venta = vm.id_venta
                            LEFT JOIN maelote mae 
                                ON mov.cod_lot = mae.cod_lot
                            WHERE vm.id_venta > 0 " . $condi_fecha_sorteo . "
                            GROUP BY 
                                mae.nombre_corto, 
                                mov.num_sor, 
                                mov.fec_sor_str
                            ORDER BY 
                                mae.nombre_corto";

                //select de acumulado PREMIOS
                $sql_premios ="SELECT 
                                mae.nombre_corto AS loteria,
                                mov.num_sor,
                                mov.fec_sor_str AS fec_sor,
                                sum(mov.fracciones) AS fracciones,
                                SUM(mov.vlr_premio) AS vlr_venta
                            FROM movlottired mov
                            INNER JOIN ventas_mae vm 
                                ON mov.id_venta_premio = vm.id_venta
                            LEFT JOIN maelote mae 
                                ON mov.cod_lot = mae.cod_lot
                            WHERE vm.id_venta > 0 " . $condi_fecha_sorteo . "
                            GROUP BY 
                                mae.nombre_corto, 
                                mov.num_sor, 
                                mov.fec_sor_str
                            ORDER BY 
                                mae.nombre_corto";

                // -----------------------------------------------------------------
                // Ejecutar los SELECT solo cuando $condi_fecha_sorteo no está vacío
                // -----------------------------------------------------------------
                if (!empty($condi_fecha_sorteo)) {
                    // Ejecutar SELECT de VENTAS
                    $stm = $db->prepare($sql_total);
                    $stm->execute();
                    $arre_totales = $stm->fetchAll(\PDO::FETCH_ASSOC);
                    $stm->closeCursor();

                    // Ejecutar SELECT de PREMIOS
                    $stm = $db->prepare($sql_premios);
                    $stm->execute();
                    $arre_premios = $stm->fetchAll(\PDO::FETCH_ASSOC);
                    $stm->closeCursor();

                    /*
                    A continuación busca en $arre_totales los registros de $arre_premios,
                    la llave es loteria y num_sor, si encuentra el registro entonces debe
                    adicionar dos columnas, fracciones_premio y vlr_premios en $arre_totales
                    */
                    foreach ($arre_totales as &$total) {
                        // Inicializar las columnas adicionales con valores por defecto
                        $total['fracciones_premio'] = 0;
                        $total['vlr_premios'] = 0;

                        // Buscar en $arre_premios
                        foreach ($arre_premios as $premio) {
                            if ($total['loteria'] === $premio['loteria'] && $total['num_sor'] === $premio['num_sor']) {
                                // Si se encuentra, actualizar las columnas
                                $total['fracciones_premio'] = $premio['fracciones'];
                                $total['vlr_premios'] = $premio['vlr_venta'];
                                break; // Romper el bucle ya que la coincidencia se encontró
                            }
                        }
                    }
                    unset($total); // Romper la referencia al último elemento
                } else {
                    // Si no hay condición de fecha, inicializar variables vacías
                    $arre_totales = array();
                    $arre_premios = array();
				}

				//select detalle
				$sql = "SELECT 
							/*mov.*, */
							pto1.mueble as pto_venta,
                            CASE 
                                WHEN mov.id_venta != 0 THEN mov.id_venta
                                ELSE mov.id_venta_premio
                            END AS id_venta,
                            mov.id_reserva,
							mov.barcode,
                            to_char(vm.fec_venta, 'YYYY-MM-DD HH24:MI') AS fec_venta,
							mae.nombre_corto as loteria,
							mov.fec_sor_str as fec_sor,
							mov.num_sor,
							mov.num_bil,
							mov.num_ser,
							mov.fracciones,
							mov.vlr_fracciones,
							mov.id_venta_premio,
							pto2.mueble as pto_premio,
							mov.nom_pre,
							mov.vlr_premio,
                            mov.tipo,
                            mov.cedula,
							cli.nombres||' '||cli.apellidos as cliente,                            
							usu1.nom_usu as vendedor,
							usu2.nom_usu as nom_usu_reimp,
							to_char(mov.fec_reimpresion, 'YYYY-MM-DD HH24:MI') AS fec_reimpresion,
							mov.valida_premio,
							mov.fec_ult_modif
						FROM movlottired mov
						INNER JOIN ventas_mae vm ON mov.id_venta = vm.id_venta OR mov.id_venta_premio = vm.id_venta
						LEFT JOIN pto_vta pto1 ON mov.cod_pto = pto1.cod_pto
						LEFT JOIN pto_vta pto2 ON mov.cod_pto_premio = pto2.cod_pto
						LEFT JOIN usuarios usu1 ON vm.id_usu = usu1.id_usu
						LEFT JOIN usuarios usu2 ON mov.id_usu_reimpresion = usu2.id_usu
						LEFT JOIN clientes cli ON mov.cedula = cli.cedula
						LEFT JOIN maelote mae ON mov.cod_lot = mae.cod_lot                      
						/*WHERE vm.id_venta > 0 .$condi_fecha;*/										
						WHERE vm.id_venta > 0" .$condi_fecha.$condi_fecha_sorteo.$condi_fecha_val_premio;				

				if ($cboPtoventa != '99') {
					$sql .= " AND mov.cod_pto = '$cboPtoventa' ";
				}
				
				if ($cboLoterias != '99') {
					$sql .= " AND mov.cod_lot = '$cboLoterias' ";
				}				
				
				if (!empty($cedula)) {
					$sql .= " AND mov.cedula = '$cedula' ";
				}
				
				if (!empty($num_sor)) {
					$sql .= " AND mov.num_sor = '$num_sor' ";
				}				
				
				// Agregar la condición para que al menos una de las dos comparaciones sea verdadera
				$sql .= " AND (vm.id_usu = usu1.id_usu OR mov.id_usu_reimpresion IS NULL OR mov.id_usu_reimpresion = usu1.id_usu) ";
				
				$sql .= " ORDER BY vm.cod_pto, vm.id_venta desc, mov.tipo desc, mae.nombre_corto";

				$stm = $db->prepare($sql);
				$stm->execute();
				$arre = $stm->fetchAll(\PDO::FETCH_ASSOC);
				$stm->closeCursor();

				// Eliminar registros duplicados basados en el campo 'barcode'
				$uniqueBarcodes = array();
				$result = array();

				foreach ($arre as $row) {
					if (!in_array($row['barcode'], $uniqueBarcodes)) {
						$uniqueBarcodes[] = $row['barcode'];
						$result[] = $row;
					}
				}

				$arre = $result; // Ahora $arre solo contiene registros únicos				

				//********************** IMPRESION DE LA TABLA **********************/
				$productos = '
				<div class="pager"> 
					<img src="' . $_SESSION["java"] . 'css/images/first.png" class="first" style="width: 32px; height: 32px;"/> 
					<img src="' . $_SESSION["java"] . 'css/images/prev.png" class="prev" style="width: 32px; height: 32px;"/> 
					<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
					<img src="' . $_SESSION["java"] . 'css/images/next.png" class="next" style="width: 32px; height: 32px;"/> 											
					<img src="' . $_SESSION["java"] . 'css/images/last.png" class="last" style="width: 32px; height: 32px;"/> 											
					<select class="pagesize" title="Select page size"> 
						<option selected="selected" value="10">10</option> 
						<option value="20">20</option> 
						<option value="30">30</option> 
						<option value="40">40</option> 
					</select>
					<select class="gotoPage" title="Select page number"></select>
				</div>
				';

				$productos.= "<table class='table table-bordered table-condensed table-striped table-hover tablesorter' id='tbl_productos'>";

				$productos.="<thead><tr>
								<th class='first-name filter-select' data-placeholder='Todos'>Punto</th>
								<th>Venta</th>
								<th class='first-name filter-select' data-placeholder='Todas'>Fecha</th>
								<th class='first-name filter-select' data-placeholder='Todos'>Tipo</th>
								<th>Cedula</th>
								<th class='first-name filter-select' data-placeholder='Todas'>Loteria</th>
								<th class='first-name filter-select' data-placeholder='Todas'>Sorteo</th>
								<th class='first-name filter-select' data-placeholder='Todas'>FecSor</th>
								<th>Billete</th>
								<th>Serie</th>
								<th class='sorter-false filter-select' data-placeholder='Todas'>Fracs.</th>	
								<th class='sorter-false filter-select' data-placeholder='Todas'>ValPrem</th>								
								<th class='sorter-false filter-false'>Consultar</th>";
				
				$detalle = "<table id='tbl_detalle' cellspacing='0' cellpadding='0'>";

				$detalle .= "<tr>";			
				$detalle .= "<th><b>Pto. Vta</b></th>
								<th><b>Nro.Venta</b></th>
								<th><b>Fecha Venta</b></th>
								<th><b>Cedula</b></th>
								<th><b>Cliente</b></th>
								<th><b>Tipo</b></th>
								<th><b>Loteria</b></th>
								<th><b>Fec. Sorteo</b></th>
								<th><b>Sorteo</b></th>
								<th><b>Billete</b></th>
								<th><b>Serie</b></th>
								<th><b>No. Fracs</b></th>
								<th><b>Valor</b></th>
								<th><b>Asesora</b></th>
								<th><b>Pto Premio</b></th>
								<th><b>Vta Premio</b></th>
								<th><b>Fec. Premio</b></th>
								<th><b>Nom. Premio</b></th>
								<th><b>Valor Premio</b></th>
								<th><b>Reimprimio</b></th>
								<th><b>Fec. Reimp.</b></th>
								<th><b>Valido Premio</b></th>
								<th><b>Fec. Validacion</b></th>
								<th><b>Cod.Barras</b></th>
								<!--<th><b>Premio en Lottired</b></th>
								<th><b>Valor en Lottired</b></th>-->
								
								";
				$detalle .= "</tr>";			

				$productos.="</tr>
							</thead>
							<tfoot>
							</tfoot>
							<colgroup>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-2'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>
								<col class='col-md-1'>";
				
				$productos.= "</colgroup>							
							<tbody>
							";
							
				//$autorizacion = fn_genera_autorizacion();

				foreach($arre as $row1) 												
				{

					//evaluamos que el premio corresponda al rango de fechas seleccionadas
					$w_cnt = '0';
					$fec_premio = '';
					if ($row1['id_venta_premio'] > 0)
					{
						$id_venta        = $row1['id_venta'];
						
						$sql     = "select to_char(vm.fec_venta, 'YYYY-MM-DD HH24:MI') AS fec_premio
									from ventas_mae  vm where vm.id_venta = $id_venta";
						$stmt    = $db->query($sql);
						$fec_venta = $stmt->fetchColumn();	

						if (!empty($fec_venta))
						{
							$row1['fec_venta'] = $fec_venta;
							//$row1['vlr_fracciones'] = '0';
						}
						
						$id_venta_premio   = $row1['id_venta_premio'];
						$row1['pto_venta'] = $row1['pto_premio'];
						
						$sql     = "select to_char(vm.fec_venta, 'YYYY-MM-DD HH24:MI') AS fec_premio
									from ventas_mae  vm where vm.id_venta = $id_venta_premio".$condi_fecha;
						$stmt    = $db->query($sql);
						$fec_premio = $stmt->fetchColumn();
						
						//si se cumple esta condicion es porque tiene premio pero no esta dentro del rango de fechas
						if (empty($fec_premio))
						{
							$row1['pto_premio'] = '';
							$row1['id_venta_premio'] = '0';
							$row1['nom_pre'] = '';
							$row1['vlr_premio'] = '0';
							$row1['tipo'] = 'VENTA';
						}
						else
						{	//2025-ene-17: le comentarie mas pensando en que si tiene valor se puede revisar mas facil
							//             el acumulado por sorteos y el detalle
							//$row1['vlr_fracciones'] = '0';	//la venta siempre corresponde a una fecha anterior
						}
					}
					//FIN //evaluamos que el premio corresponda al rango de fechas seleccionadas
				
					$productos .= "<tr>"; // Corregido: Se eliminó la comilla simple extra y se usó <
					$productos .= "<td class='cls_pto_venta'>" . $row1['pto_venta'] . "</td>";
					$productos .= "<td class='cls_id_venta'>" . $row1['id_venta'] . "</td>";
					$productos .= "<td class='cls_fec_venta_corta'>" . substr($row1['fec_venta'], 0, 10) . "</td>";
					$productos .= "<td class='cls_tipo'>" . $row1['tipo'] . "</td>";
					$productos .= "<td class='cls_cedula'>" . $row1['cedula'] . "</td>";
					//$productos .= "<td class='cls_loteria'>" . $row1['loteria'] . "</td>";
					$productos .= "<td class='cls_loteria'>" . ($row1['loteria'] == 'CUNDINAMARCA' ? 'C/MARCA' : htmlspecialchars($row1['loteria'], ENT_QUOTES, 'UTF-8')) . "</td>";
					$productos .= "<td class='cls_num_sor'>" . $row1['num_sor'] . "</td>";
					$productos .= "<td class='cls_fec_sor'>" . $row1['fec_sor'] . "</td>";
					$productos .= "<td class='cls_num_bil'>" . $row1['num_bil'] . "</td>";
					$productos .= "<td class='cls_num_ser'>" . $row1['num_ser'] . "</td>";
					$productos .= "<td class='cls_fracciones'>" . $row1['fracciones'] . "</td>";

					$productos .= "<td class='cls_valida_premio'>" .
									htmlspecialchars($row1['valida_premio'], ENT_QUOTES, 'UTF-8') . // Muestra el valor
								  "</td>"; // Fin de la celda modificada

					$productos .= "<td class='cls_vlr_fracciones' style='display: none;'>" . $row1['vlr_fracciones'] . "</td>";
					// $productos .= "<!--<td class='cls_fec_sor' style='display: none;'>".$row1['fec_sor']."</td>-->";
					// $productos .= "<!--fecha de venta completa-->";
					$productos .= "<td class='cls_fec_venta' style='display: none;'>" . $row1['fec_venta'] . "</td>";
					// $productos .= "<!--fin de fecha de venta completa-->";
					$productos .= "<td class='cls_id_venta_premio' style='display: none;'>" . $row1['id_venta_premio'] . "</td>";
					$productos .= "<td class='cls_fec_premio' style='display: none;'>" . $fec_premio . "</td>"; // Asumiendo que $fec_premio está definida
					$productos .= "<td class='cls_nom_pre' style='display: none;'>" . $row1['nom_pre'] . "</td>";
					$productos .= "<td class='cls_vlr_premio' style='display: none;'>" . $row1['vlr_premio'] . "</td>";
					$productos .= "<td class='cls_cliente' style='display: none;'>" . $row1['cliente'] . "</td>";
					$productos .= "<td class='cls_vendedor' style='display: none;'>" . $row1['vendedor'] . "</td>";
					$productos .= "<td class='cls_nom_usu_reimp' style='display: none;'>" . $row1['nom_usu_reimp'] . "</td>";
					$productos .= "<td class='cls_fec_reimpresion' style='display: none;'>" . $row1['fec_reimpresion'] . "</td>";
					// $productos .= "<!--<td class='cls_valida_premio' style='display: none;'>".$row1['valida_premio']."</td>-->";
					$productos .= "<td class='cls_id_reserva' style='display: none;'>" . $row1['id_reserva'] . "</td>";
					$productos .= "<td class='cls_barcode' style='display: none;'>" . $row1['barcode'] . "</td>";
					$productos .= "<td align='center'>";
					$productos .= "<a type='button' class='consulta_vta btn btn-info fa fa-pencil-square-o'></a>";
					$productos .= "</td>";
					$productos .= "</tr>"; // Corregido: Se usó </tr>					

					$nombre_premio_ltr = null; // O asignar un valor por defecto
					$vlr_premio_ltr    = '0';
							
					//if ($row1['barcode'] != '999'){
					if ($row1['barcode'] == '999'){
						$resultado_validacion = fn_valida_premio_ltr($row1['barcode'], $autorizacion);
						$datos_validacion_ltr = $resultado_validacion['arreglo'];
						
						// Inicializar las variables antes del bucle
						$nombre_premio_ltr = null; // O un valor por defecto como "Múltiples Premios" si se encuentran varios
						$vlr_premio_ltr = 0;       // Inicializar la suma en 0

						// Verificar si $datos_validacion_ltr es un array y no está vacío
						if (is_array($datos_validacion_ltr) && !empty($datos_validacion_ltr)) {

							//$error = 'arreglo: '.print_r($datos_validacion_ltr,true);
							
							// Iterar sobre cada elemento (premio) en el array
							foreach ($datos_validacion_ltr as $premio) {

								// --- Condición SOLO para la SUMA ---
								if (
									isset($premio['status']) &&
									//$premio['status'] == '02' && // Comprobar el estado del premio actual
									$premio['status'] != '01' && // Comprobar el estado del premio actual
									isset($premio['prizeNetValue']) &&
									is_numeric($premio['prizeNetValue']) // Asegurarse de que sea un número
								) {
									// Sumar el valor del premio actual al total
									$vlr_premio_ltr += (float)$premio['prizeNetValue'];
								}

								// --- Lógica separada para obtener el nombre (si existe Y el status es '02') ---
								// Guarda el nombre del *primer* premio válido encontrado que tenga nombre Y status '02'.
								if ($nombre_premio_ltr === null && // Solo si aún no hemos guardado un nombre
									isset($premio['nombre_premio']) && // Si el nombre existe en este premio
									isset($premio['status']) && // Si el status existe (buena práctica)
									$premio['status'] != '01' // Y si el status es '02'
								   ) {
										 $nombre_premio_ltr = $premio['nombre_premio'];
								}

							}
						}						
					}
					$detalle.= "<tr>
									<td>".$row1['pto_venta']."</td>
									<td>".$row1['id_venta']."</td>
									<td>".$row1['fec_venta']."</td>
									<td>".$row1['cedula']."</td>
									<td>".$row1['cliente']."</td>
									<td>".$row1['tipo']."</td>
									<td>".$row1['loteria']."</td>
									<td>".$row1['fec_sor']."</td>
									<td>".$row1['num_sor']."</td>
									<td>".$row1['num_bil']."</td>
									<td>".$row1['num_ser']."</td>
									<td data-type='Number' style='text-align: right;'>".$row1['fracciones']."</td>
									<td data-type='Number' style='text-align: right;'>".$row1['vlr_fracciones']."</td>
									<td>".$row1['vendedor']."</td>
									<td>".$row1['pto_premio']."</td>
									<td>".$row1['id_venta_premio']."</td>
									<td>".$fec_premio."</td>
									<td>".$row1['nom_pre']."</td>
									<td data-type='Number'>".$row1['vlr_premio']."</td>
									<td>".$row1['nom_usu_reimp']."</td>
									<td>".$row1['fec_reimpresion']."</td>
									<td>".$row1['valida_premio']."</td>
									<td>".$row1['fec_ult_modif']."</td>
									<td>".$row1['barcode']."</td>
									<!--<td>".$nombre_premio_ltr."</td>
									<td data-type='Number' style='text-align: right;'>".$vlr_premio_ltr."</td> -->
								</tr>";										


				}

				$detalle .= "</table>";
				$productos.= "</tbody></table>";
				
				//2025-ene-16: VENTA: aquí se genera la tabla de totales por lotería y sorteos:
				$totales = ''; // Valor por defecto cuando no hay registros

				$totales = "<table id='tbl_totales_venta' cellspacing='0' cellpadding='0'>";

				if (!empty($arre_totales)) {

					$totales .= "<tr>";            
					$totales .= "<th data-style='CenteredBold' data-style='Bold'><b>Lotería</b></th>
								<th data-style='CenteredBold' data-style='Bold'><b>Sorteo</b></th>                
								<th data-style='CenteredBold' data-style='Bold'><b>Fec.Sorteo</b></th>                
								<th data-style='CenteredBold' data-style='Bold'><b>Fra_Ventas</b></th>                
								<th data-style='CenteredBold' data-style='Bold'><b>Vrl.Venta</b></th>
								<th data-style='CenteredBold' data-style='Bold'><b>Fra_Premios</b></th>                
								<th data-style='CenteredBold' data-style='Bold'><b>Vlr.Premios</b></th>";                                             
					$totales .= "</tr>";

					$loteria_anterior = null; // Para rastrear el cambio de lotería
					$total_fracciones = 0;
					$total_venta = 0;
					$total_fracciones_premios = 0;
					$total_venta_premios = 0;

					$gran_total_fracciones = 0;
					$gran_total_venta = 0;
					$gran_total_fracciones_premios = 0;
					$gran_total_venta_premios = 0;

					foreach ($arre_totales as $row1) {                
						if ($loteria_anterior !== null && $loteria_anterior !== $row1['loteria']) {
							// Imprimir los totales acumulados de la lotería anterior
							$totales .= "<tr style='font-weight: bold; background-color: #f0f0f0;'>
											<td data-style='Bold'>Total ===></td>
											<td></td><td></td>                                        
											<td data-style='Bold' data-type='Number'>{$total_fracciones}</td>
											<td data-style='Bold' data-type='Number'>{$total_venta}</td>
											<td data-style='Bold' data-type='Number'>{$total_fracciones_premios}</td>
											<td data-style='Bold' data-type='Number'>{$total_venta_premios}</td>
										</tr>";
							// Reiniciar los acumuladores
							$total_fracciones = 0;
							$total_venta = 0;
							$total_fracciones_premios = 0;
							$total_venta_premios = 0;
						}

						// Acumular los valores de la lotería actual
						$total_fracciones += $row1['fracciones'];
						$total_venta += $row1['vlr_venta'];
						$total_fracciones_premios += $row1['fracciones_premio'];
						$total_venta_premios += $row1['vlr_premios'];

						// Acumular los totales generales
						$gran_total_fracciones += $row1['fracciones'];
						$gran_total_venta += $row1['vlr_venta'];
						$gran_total_fracciones_premios += $row1['fracciones_premio'];
						$gran_total_venta_premios += $row1['vlr_premios'];

						// Imprimir la fila actual
						$totales .= "<tr>
										<td>".$row1['loteria']."</td>
										<td>".$row1['num_sor']."</td>
										<td>".$row1['fec_sor']."</td>
										<td data-type='Number'>".$row1['fracciones']."</td>
										<td data-type='Number'>".$row1['vlr_venta']."</td>
										<td data-type='Number'>".$row1['fracciones_premio']."</td>
										<td data-type='Number'>".$row1['vlr_premios']."</td>
									</tr>";

						$loteria_anterior = $row1['loteria'];
					}

					// Imprimir los totales de la última lotería
					if ($loteria_anterior !== null) {
						$totales .= "<tr style='font-weight: bold; background-color: #f0f0f0;'>
										<td data-style='Bold'>Total ===></td>
										<td></td><td></td>
										<td data-style='Bold' data-type='Number'>{$total_fracciones}</td>
										<td data-style='Bold' data-type='Number'>{$total_venta}</td>
										<td data-style='Bold' data-type='Number'>{$total_fracciones_premios}</td>
										<td data-style='Bold' data-type='Number'>{$total_venta_premios}</td>
									</tr>";
					}

					// Imprimir los totales generales al final
					$totales .= "<tr style='font-weight: bold; background-color: #d0d0d0;'>
									<td data-style='Bold'>Gran Total</td>
									<td></td><td></td>                                
									<td data-style='Bold' data-type='Number'>{$gran_total_fracciones}</td>
									<td data-style='Bold' data-type='Number'>{$gran_total_venta}</td>
									<td data-style='Bold' data-type='Number'>{$gran_total_fracciones_premios}</td>
									<td data-style='Bold' data-type='Number'>{$gran_total_venta_premios}</td>
								</tr>";

					$totales .= "<tr></tr>";

					$totales .= "<tr>
									<td></td>
									<td colspan='6' data-style='Bold'>Rango de Sorteos: {$fec_ini} al {$fec_fin}</td>
								</tr>";
				} else {
					$totales .= "<tr>
									<td colspan='6' data-style='Bold'>Rango de Fechas de Sorteos no fue seleccionado</td>
								</tr>";
				}
				//FIN 2025-ene-16

				 $totales .= "</table>";

				//***********************************FIN IMPRESION DE LA TABLA **********************************/
				$db->commit(); 

			} 
			catch(PDOException $e) 
			{
				$error = $e->getMessage();
				$db->rollBack();
			}	
		}
		else
		{
			$error = 'ERROR: EL MAXIMO NUMERO DE DIAS A PROCESAR ES DE 31, POR FAVOR REVISE';
		}		
		

		$arreglo = array('error' => $error,'productos'=>$productos,'detalle'=>$detalle,'totales'=>$totales);
		echo json_encode($arreglo);	
		exit;

	}

	/**
	 * Reimprime una venta y devuelve los datos de la venta + los totales.
	 *
	 * @param PDO $db          Conexión PDO ya configurada.
	 * @param int $id_venta    ID de la venta a reimprimir.
	 *
	 * @return array           ['venta'=>..., 'arre_totales'=>...]
	 *
	 * @throws Exception       Si la venta no se encuentra.
	 */
	function fn_reimprimir_venta($db, $id_venta) {
		/* -------------------------------------------------------------
		1️⃣  Obtener la fila completa de la tabla ventas_mae
		------------------------------------------------------------- */
		$sql = "SELECT * FROM ventas_mae WHERE id_venta = :id_venta";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
		$stmt->execute();
		$venta = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$venta) {
			throw new Exception("Venta con ID $id_venta no encontrada.");
		}

		/* -------------------------------------------------------------
		2️⃣  Construir el arreglo arre_totales
			- totalVenta   = tot_venta (o 0 si tot_premios > tot_venta)
			- totalPremios = tot_premios
			- valorPagar   = totalVenta - totalPremios (no negativo)
			- efectivo     = dinero_efectivo
		------------------------------------------------------------- */
		$tot_venta   = (float) $venta['tot_venta'];
		$tot_premios = (float) $venta['tot_premios'];
		$efectivo    = (float) $venta['dinero_efectivo'];

		// Si los premios superan la venta, la venta se considera 0
		$totalVenta = ($tot_premios > $tot_venta) ? 0 : $tot_venta;

		// Valor a pagar (no negativo)
		$valorPagar = $totalVenta - $tot_premios;
		if ($valorPagar < 0) {
			$valorPagar = 0;
		}

		$totales = array(
			'totalVenta'   => $totalVenta,
			'totalPremios' => $tot_premios,
			'valorPagar'   => $valorPagar,
			'efectivo'     => $efectivo
		);

		/* -------------------------------------------------------------
		2.2️⃣  **Primer paso** – Obtener la cédula del cliente
		------------------------------------------------------------- */
		$sqlCedula = "
			SELECT cedula
			FROM movlottired
			WHERE id_venta = :id_venta or id_venta_premio = :id_venta
			LIMIT 1
		";
		$stmtCed = $db->prepare($sqlCedula);
		$stmtCed->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
		$stmtCed->execute();
		$rowCed = $stmtCed->fetch(PDO::FETCH_ASSOC);

		if (!$rowCed) {
			throw new Exception("No se encontró registro en movlottired para la venta $id_venta.");
		}

		$cedula = $rowCed['cedula'];

		/* -------------------------------------------------------------
		2.3️⃣  Obtener los datos del cliente a partir de la cédula
		------------------------------------------------------------- */
		$sqlCliente = "
			SELECT nombres, apellidos, celular, direccion, correo
			FROM clientes
			WHERE cedula = :cedula
			LIMIT 1
		";
		$stmtCli = $db->prepare($sqlCliente);
		$stmtCli->bindParam(':cedula', $cedula, PDO::PARAM_STR);
		$stmtCli->execute();
		$rowCli = $stmtCli->fetch(PDO::FETCH_ASSOC);

		if (!$rowCli) {
			// Si no hay registro en la tabla clientes, devolvemos valores vacíos
			$cliente = array(
				'cedula'    => $cedula,
				'nombres'   => '',
				'apellidos' => '',
				'celular'   => '',
				'direccion' => '',
				'correo'     => ''
			);
		} else {
			$cliente = array(
				'cedula'    => $cedula,
				'nombres'   => $rowCli['nombres'],
				'apellidos' => $rowCli['apellidos'],
				'celular'   => $rowCli['celular'],
				'direccion' => $rowCli['direccion'],
				'correo'     => $rowCli['correo']
			);
		}

		/* -------------------------------------------------------------
		2.5️⃣  **Nuevo** – Arreglo “recibidos” con datos de ventas_mae
		------------------------------------------------------------- */
		$recibidos = array(
			'id_venta' => $venta['id_venta'],
			'id_usu'   => $venta['id_usu'],
			'pto_vta'  => $venta['cod_pto']   // campo cod_pto corresponde al punto de venta
		);

		/* -------------------------------------------------------------
		3️⃣  Devolver ambos arreglos en un único array asociativo
		------------------------------------------------------------- */
		return array(
			'totales'      => $totales,
			'cliente'      => $cliente,
			'recibidos'    => $recibidos
		);
	}
?>

