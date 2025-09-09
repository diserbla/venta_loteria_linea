<?php if(!session_id()) {session_start();}
       header('Content-Type: application/json');
	include($_SESSION['bd']."conexion.php");	
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	

	define("FPDF_FONTPATH","font/");	
	include_once ($_SESSION['librerias']."fpdf/fpdf.php"); 	
	
	include($_SESSION['librerias']."excell/PHPExcel.php");
	include($_SESSION['librerias']."excell/PHPExcel/Reader/Excel5.php");
	include($_SESSION['librerias']."excell/PHPExcel/Reader/Excel2007.php");	
	
	include_once($_SESSION['funciones']."funciones.php");  

    $id_usu = $_SESSION['id_usu'];	
	
	$paso     =$_REQUEST['paso'];
	
	if($paso == 'evalua_conexion')
	{
		echo 'merca';
	}			

	if ($paso == 'cboLotero') 
	{
		$sql="select id_usu,nom_usu
			  from usuarios
			  where estado_usu = 'A'
			  and ced_usu in (16271924,94386823,16253952,805012318)
			  order by nom_usu";
		
		$salida="<select id=\"cboLotero\" name=\"cboLotero\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
			
				$salida=$salida. "<option value='0'>Lotero</option>";
				foreach ($db->query($sql) as $row)				
				{
					$salida=$salida."<option value=".$row['id_usu'].">".$row['nom_usu']."</option>";		
				}								  
		$salida=$salida."</select>";
		$arreglo = array ('salida'=>$salida);
		print json_encode($arreglo);			
	}	

	if ($paso == 'cboPaquete_mtto') //lista de valores 
	{
		$tipo_paquete = $_POST['tipo_paquete'];
		$opcion       = $_POST['opcion'];
		$nombre       = $_POST['nombre'];
		
		try 
		{
			$db->beginTransaction(); 
			
			$sql = "select * from maeraspas order by nombre";
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.	

			$db->commit(); 

		} 
		catch(PDOException $e)
		{
			$msg=  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}			
		
		if ($opcion=='paquetes')
		{
			$salida="<select id=\"cboPaquete_mtto\" name=\"cboPaquete_mtto\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
		}

		if ($opcion=='validar_premios')
		{
			$salida="<select id=\"cboPaquete_mtto_pre\" name=\"cboPaquete_mtto_pre\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
		}

		$sw = '0';
		foreach($arre as $row) 		
		{
			$codigo = $row["codigo"];
			if (($codigo == $tipo_paquete ) && ($sw=='0'))
			{
				$salida=$salida. "<option value='$tipo_paquete' selected='selected'>".$nombre."</option>";
				$sw = '1';
			}
			else
			{
				if ($row["nombre"] != 'Otro Proveedor')
				{
					$salida=$salida. "<option value='".$row['codigo']."'>".$row['nombre_corto']."</option>";
				}
			}
		}

		$salida=$salida."</select>";
		$arreglo = array ('salida'=>$salida);
		print json_encode($arreglo);	
	}

	if ($paso == 'cboMaeraspas') 
	{
		$tipo    = $_POST['tipo'];

		if ($tipo=='list_maeraspas')
		{
			$cboMaeraspas = 'cboMaeraspas';	
		}	
		
		if ($tipo=='list_maeraspas_desp')
		{
			$cboMaeraspas = 'cboMaeraspas_desp';
		}

		if ($tipo=='list_maeraspas_inv')
		{
			$cboMaeraspas = 'cboMaeraspas_inv';
		}		
		
		if ($tipo=='list_maeraspas_bodega')
		{
			$cboMaeraspas = 'cboMaeraspas_bodega';
		}				
		
		$sql="select codigo,nombre,valor	
			  from maeraspas
			  where estado = 'A'
			  order by valor";
			  

		//$salida="<select id=\"cboMaeraspas\" name=\"cboMaeraspas\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
		$salida="<select id=\"".$cboMaeraspas."\" name=\"".$cboMaeraspas."\" style=\"background-color: #87CEFA;text-align: center;\" class=\"form-control\">"; 		
				$salida=$salida. "<option value='0'>SELECCIONE</option>";
				
				if ($tipo == 'list_maeraspas_inv')
				{
					$salida=$salida. "<option value='99'>**TODOS**</option>";	
				}
				
				if ($tipo == 'list_maeraspas_bodega')
				{
					$salida=$salida. "<option value='99'>**TODOS**</option>";	
				}								
				
				if ($tipo == 'list_maeraspas_desp')
				{
					$salida=$salida. "<option value='88'>**TODOS** !!PARA UN SOLO PUNTO!!</option>";	
				}				
				
				foreach ($db->query($sql) as $row)				
				{
					if ($row['codigo'] != '99') //otro proveedor (no se utiliza)
					{		
						$valor = number_format($row['valor']);
						$nombre = strtoupper($row['nombre']);
						
						$salida=$salida."<option value=".$row['codigo'].">".$nombre."-".$valor."</option>";								
					}
				}								  
		$salida=$salida."</select>";
	
		$arreglo = array ('salida'=>$salida);
		print json_encode($arreglo);
	
	}

	if ($paso == 'cboPtoventa') //lista de valores con la tabla puntos de venta
	{
		
		$cod_pto = $_SESSION['pto_vta'];
		$tipo    = $_POST['tipo'];
		
		$cboPtoventa = 'cboPtoventa'; //cuando vale esto es porque se estan leyendo los billetes

		if ($tipo=='list_pto_venta4')
		{
			$cboPtoventa = 'cboPtoventa4';	
		}	

		if ($tipo=='list_pto_venta3')
		{
			$cboPtoventa = 'cboPtoventa3';	
		}	
		
		if ($tipo=='list_pto_venta2')
		{
			$cboPtoventa = 'cboPtoventa2';
		}		
		
		if ($tipo=='list_pto_venta1')
		{
			$cboPtoventa = 'cboPtoventa1';	
		}
		
		if ($cod_pto == '99') //oficina ppal
		{
			$condicion_pto = '';
		}	
		else
		{
			$condicion_pto = "and cod_pto = '$cod_pto' ";	
		}
		
		$orden = " order by nombre ";		
		
		
		$sql="select cod_pto,mueble,nom_pto as nombre	
			  from pto_vta
			  where estado = 'A'".
			  $condicion_pto.$orden;
			  
		//echo $sql;
		//exit;

		$salida="<select id=\"".$cboPtoventa."\" name=\"".$cboPtoventa."\" style=\"background-color: #87CEFA;\" class=\"form-control\">"; 
					
				if (($cod_pto == '99') && (($cboPtoventa != 'cboPtoventa') && ($cboPtoventa != 'cboPtoventa2'))) //esta evaluacion la hago para que en la 
				{																								 //lectura o en devolucion la solo me 
					$salida=$salida. "<option value='*t*' >- SOLO CALI -</option>";								 //aparezcan los ptos
					$salida=$salida. "<option value='*-*' >- TODOS LOS PUNTOS -</option>";					
				}
				else
				{
					$salida=$salida. "<option value='0'>PTO VENTA</option>"; //es importante que aparezca esta opcion para que seleccione el pto real
				}
			
				foreach ($db->query($sql) as $row)				
				{
					$salida=$salida."<option value=".$row['cod_pto'].">".$row['nombre']."</option>";
				}								  
		$salida=$salida."</select>";
	
		$arreglo = array ('salida'=>$salida);
		print json_encode($arreglo);
		
	}		

	if($paso=='find_vendedor'){	
		$cod_vendedor = $_POST['cod_vendedor'];	
		$cod_pto      = $_POST['cod_pto'];	
		$error = '';
		
		$sql = 'select id_usu,nom_usu from usuarios where cod_vendedor = :cod_vendedor';
		$stm = $db->prepare($sql);
		$stm->bindParam(":cod_vendedor", $cod_vendedor, PDO::PARAM_INT);
		$stm->execute();					
		$row = $stm->fetch(PDO::FETCH_ASSOC);
		$id_usu	 = $row["id_usu"]; //perfect
		$nom_usu = $row["nom_usu"]; //perfect					
		
		if ($id_usu != '')
		{
			$arreglo = array ('id_usu'=>$id_usu,'nom_usu'=>$nom_usu,'error'=>$error);
		}
		else
		{
			$error = 'Vendedor(a) NO Existe';
			$arreglo = array ('id_usu'=>$id_usu,'nom_usu'=>$nom_usu,'error'=>$error);			
		}
		
		//evaluamos que el pto de venta sino es oficina entonces periferia, de lo contrario no permite el ingreso
		if ($cod_pto != '99')
		{
			$sql     = "select zona from pto_vta where cod_pto = '$cod_pto'";
			$stmt    = $db->query($sql);
			$zona = $stmt->fetchColumn();
			
			//if ($zona != 'P')
			if ($zona == 'ZZ')
			{
				$error = "Punto de Venta no permite el ingreso de Loteria";
				$arreglo = array ('id_usu'=>$id_usu,'nom_usu'=>$nom_usu,'error'=>$error);	
			}
		}		
		
		print json_encode($arreglo);		
	}	


	if ($paso=='valida_premio')
	{
		$codiac      = $_POST['codiac'];
		$cod_pto     = $_POST['cbopto'];		
		$fec_pre_val = $_POST['fec_pre'];		
		$clase       = '';		
		$color       = '';		
		$msg         = '';
		$error       = '';		
		if (!empty($codiac))
		{
			try 
			{
				$db->beginTransaction(); 
				
				$sql = "select mov.*,rp.nro_paquete,rp.tipo_paquete,mae.nombre,usu.nom_usu,pto.mueble 
					   from movraspas mov, raspas_paquetes rp,  maeraspas mae,usuarios usu, pto_vta pto
					   where mov.codraspa = '$codiac'
					   and mov.id_paquete = rp.id_paquete
					   and rp.tipo_paquete = mae.codigo
					   and mov.cod_pto = pto.cod_pto
					   and mov.usr_ult_modif = usu.id_usu
					   ";
					   
				$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
				$stm->execute(); // Se ejecuta la consulta.			
				$arre1 = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
				$stm->closeCursor(); // Se libera el recurso.
				
				foreach($arre1 as $row) 						
				{
					$nro_paquete  = $row['nro_paquete'];	
					$cod_pto_pag  = $row['cod_pto'];	 										
					$id_venta     = $row['id_venta'];	
					$fec_premio   = substr($row['fec_premio'],0,10);	
					$vlr_pre      = number_format($row['vlr_pre']);	
					$estado       = $row['estado'];	
					$id_usu       = $row['usr_ult_modif'];	
					$nom_usu      = $row['nom_usu'];	
					$mueble       = $row['mueble'];	 
					$tipo_paquete = $row['tipo_paquete']; //	 
					$nombre       = $row['nombre'];	      //
				}
				
				$clase   = '';
				$novedad = '0';				
				
				//$color='#FF0000'; //con premio
				$novedad = '0';
				if ($estado != 'PREMIO')
				{
					$id_venta   = 'SIN PREMIO';
					$fec_premio = 'SIN PREMIO';
					//$color='#eee8aa'; //no tiene premio
					$novedad = '1';
				}

				//validamos la fecha de pago
				if ($fec_pre_val != $fec_premio)					
				{
					$clase = 'otras_fechas';
					$novedad = '1';
				}

				//validamos el pto de pago
				if ($cod_pto != $cod_pto_pag)					
				{					
					$clase= 'otros_ptos';
					$novedad = '1';
				}	
				
				$data   = array("codiac" =>$codiac,"nro_paquete"=>$nro_paquete,"pto_vta" =>$mueble,"id_venta" =>$id_venta,
								"fec_premio" =>$fec_premio,"vlr_pre"=>$vlr_pre,"id_usu"=>$id_usu,"nom_usu"=>$nom_usu,
								"color"=>$color,"clase"=>$clase,"novedad"=>$novedad,"tipo_paquete"=>$tipo_paquete,
								"nombre"=>$nombre);
				$arre[] = $data;
				
				$arreglo = array ('error'=>$error,'arreglo'=>$arre);				
				
				
				$db->commit(); 				
			} 
			catch(PDOException $e)
			{
				$error = $e->getMessage();
				$db->rollBack();
				$arreglo = array('error'=>$error);					
			}

			print json_encode($arreglo);	
			
		}
	}				

	if ($paso=='total_premios_pto')
	{
		$cbopto  = $_POST['cbopto'];
		$fec_pre = $_POST['fec_pre'];		
		$msg         = '';
		
		try 
		{
			$db->beginTransaction();

			$sql     = "select count(1) total 
						from movraspas 
						where cod_pto = '$cbopto' and fec_premio::date = '$fec_pre' and estado = 'PREMIO'";

			$stmt    = $db->query($sql);
			$total = $stmt->fetchColumn();
			$total = 'Pagados: '.number_format($total);
		
			$db->commit(); 
		} 		
		catch(PDOException $e)
		{
			$msg = $e->getMessage();
			$db->rollBack();				  			
		}							
		
		$arreglo = array('error'=>$msg,'premios_pagados'=>$total);					
		print json_encode($arreglo);			
	}		

	if($paso=='reporte1')
	{
		$fec_despacho = $_POST['fec_despacho'];
		$tipo         = $_POST['tipo'];
		
		class PDF extends FPDF
		{
			//Cabecera de página
			function Header()
			{
				global $header,$fec_despacho,$tipo;

				$mdate = date('Y-m-d H:i:s');//date("Y-m-d");	
				//Colores, ancho de línea y fuente en negrita
				$this->SetFillColor(240,240,240);
				$this->SetTextColor(0);
				$this->SetDrawColor(0,0,0);
				$this->SetLineWidth(.2);
				$this->SetFont('Arial','',10);

				if (isset($header)) 
				{
					//$pdf->Cell(50,5,'Loteria',1,0,'C',1);
					
					$arre = explode("-", $tipo);
			
					$producto = $arre[0]; 
					$valor    = $arre[1];
					
					$this->SetFont('Arial','',10);					
					$this->Cell(2);				
					$this->Cell(10,5,$mdate);			
					$this->Cell(40);
					$this->SetFont('Arial','B',10);						
					$this->Cell(50,5,'MERCALOTERIAS S.A',0,0,'C');
					$this->SetFont('Arial','',10);						
					$this->Cell(50);														
					$this->Cell(20,5,'Pagina No. '.$this->PageNo(),0,0,'C');			
					$this->Ln();
					$this->SetFont('Arial','B',10);						
					$this->Cell(35);				
					$this->Cell(85,5,"DESPACHO DE RASPA Y LISTO",0,0,'C');	
					//$this->Cell(85,5,"DESPACHO DE ".$producto." - VALOR RASPA: ".$valor,0,0,'C');	
					$this->Ln();									
					$this->Cell(50);														
					$this->Cell(55,5,"FECHA DE ENVIO :".$fec_despacho,0,0,'C');
					$this->Ln(5);					


					//$this->Cell(2);
					$this->Ln(5);									
					$this->SetFont('Arial','',8);	

					//$this->Ln();								
				}	
			}	
			//Pie de página
			function Footer()
			{
				global $db,$id_usu;
				
				$this->SetY(-50);			
				//$this->Cell(3);				
				$this->Cell(50,10,'____________________________ ',0,0,'L');	
				$this->Cell(50);							
				$this->Cell(50,10,'____________________________ ',0,0,'L');				
			
				$this->SetY(-45);			
				$this->Cell(3);				
				$this->Cell(50,10,'Responsable Entrega ',0,0,'C');	
				$this->Cell(50);							
				$this->Cell(50,10,'Reviso Entrega ',0,0,'C');				
				
				$sql     = "select nom_usu from usuarios where id_usu = '$id_usu'";
				$stmt    = $db->query($sql);
				$nom_usu = $stmt->fetchColumn();
				
				$this->SetY(-25);
				$this->SetFont('Arial','',10);			
				$this->Cell(1);				
				$this->Cell(100,10,'Despacho Realizado por: '.$nom_usu,0,0,'L');				

			}				
		}	

		$vector_reg = $_SESSION['arreglo_despacho'];	

		if (!empty($vector_reg)) 
		{ 
			$rep	= 'desp_raspas'.time().'.pdf';
			$action = "../../reports/".$rep;
			$msg    = '';

			$header=
				array(
				'Punto',
				'Paquete',			
				'Rango',			
				'Nombre-Valor',			
				'Cantidad ',			
				'Responsable',
				'Responsable'			
				);

			$pdf=new PDF();
			$pdf->Open();				
			$pdf->AddPage();

			//$w=array(15,18,30,16,60,60);			
			$w=array(15,18,30,40,16,40,40);			

			$x = 0;
			$nom_lot = '';
			$total = 0;
			
			$pdf->SetFont('Arial','B',10);				
			$pdf->SetFillColor(220,220,220);
			for($j=0;$j<count($header);$j++)
			{
				$pdf->Cell($w[$j],5,$header[$j],1,0,'C',1);
			}
			$pdf->SetFont('Arial','',10);				   								
			$pdf->Ln();			
			
			for($i=0; $i<sizeof($vector_reg); $i++)
			{

				$pdf->Cell($w[0],7,$vector_reg[$i]['mueble'],1,0,'C');								
				$pdf->Cell($w[1],7,$vector_reg[$i]['nro_paquete'],1,0,'C');											
				$pdf->Cell($w[2],7,$vector_reg[$i]['rango'],1,0,'C');											
				$pdf->Cell($w[3],7,$vector_reg[$i]['nombre'].'-'.number_format($vector_reg[$i]['vlr_raspa']),1,0,'C');											
				$pdf->Cell($w[4],7,$vector_reg[$i]['cantidad'],1,0,'C');											
				$pdf->Cell($w[5],7,'',1,0,'C');											
				$pdf->Cell($w[6],7,'',1,0,'R');			
				
				$total = $total + $vector_reg[$i]['cantidad'];
				
				$x = 1;
				$pdf->Ln();	

			}

			$pdf->SetFont('Arial','B',10);				   					 
			$total=number_format($total,0,",",".");				
			$pdf->Cell(103,5,"Total",1,0,'L');						    
			$pdf->Cell(16,5,$total,1,0,'C');
			$total = 0;
			
			$pdf->Output($action);	

		}
		else
		{
			$msg = 'Debe realizar primero la consulta';
		}
	
		$arreglo = array('error'=>$msg,'action'=>$action);
		print json_encode($arreglo);
		
		unset ($_SESSION['arreglo_despacho']);
				
		
	}

	if($paso=='consulta1')
	{
		$cod_pto      = $_POST['cboPtoventa'];	
		$rango_fechas = $_POST['rango_fechas'];	
		$tipo_paquete = $_POST['cboMaeraspas'];			
		$error	  = '';		
		
		if (!empty($rango_fechas))
		{
			$arre = explode("-", $rango_fechas);
			
			$fec_ini = $arre[0];
			$fec_fin = $arre[1];

			$condi_fecha = " and mov.fec_despacho::date between '$fec_ini' and '$fec_fin'";

			$date1 = str_replace('/', '-',$fec_ini);	
			$date2 = str_replace('/', '-',$fec_fin);	

			$fecha1= new DateTime($date1);
			$fecha2= new DateTime($date2);
			$diff = $fecha1->diff($fecha2);
			 
			$diferencia_dias = $diff->days+1;

		}			
		
		$condicion_pto = " AND mov.cod_pto = '".$cod_pto."'";				
		switch ($cod_pto) 
		{
			case '*-*':
				$condicion_pto = " AND mov.cod_pto > 0 ";		
				break;
				
			case '*t*':
				$condicion_pto = " and pto.zona not in ('P') and pto.cod_pto not in (18)";			
				break;			
		}

		$condicion_paquete = "";
		if ($tipo_paquete != '88')
		{
			$condicion_paquete = " and rp.tipo_paquete = ".$tipo_paquete;
		}

        $diferencia_dias = '31';
		
		if ($diferencia_dias <= '31')
		{
			try 
			{	
				
				$db->beginTransaction();	
				
				/*
				$sql_ppal = "CREATE TEMP TABLE tempo_mov as ".
						    "SELECT pto.mueble,rp.nro_paquete,mae.nombre_corto as nombre,mov.fec_despacho::date as fec_despacho,rp.vlr_raspa,
									substring(mov.codraspa,13) as codraspa
							 FROM movraspas mov,raspas_paquetes rp ,pto_vta pto, maeraspas mae
						 	 WHERE mov.fec_despacho is not null $condi_fecha 
							 and mov.id_paquete = rp.id_paquete
							 and rp.tipo_paquete = mae.codigo
							 and mov.cod_pto_desp = pto.cod_pto
							".$condicion_paquete.
							$condicion_pto;
				*/
							
				$sql_ppal = "CREATE TEMP TABLE tempo_mov as ".
				"SELECT pto.mueble, rp.nro_paquete, ".
				// --- INICIO: Modificación para el campo nombre ---
				"       CASE ".
				"           WHEN rpe.codigo IS NOT NULL THEN rpe.nombre ". // Si hay match en el JOIN, usa el nombre de la tabla de especiales
				"           ELSE mae.nombre_corto ".                      // Si no, usa el nombre corto de maeraspas
				"       END AS nombre, ".
				// --- FIN: Modificación ---
				"       mov.fec_despacho::date as fec_despacho, rp.vlr_raspa, ".
				"       substring(mov.codraspa,13) as codraspa ".
				"FROM movraspas mov ".
				"JOIN raspas_paquetes rp ON mov.id_paquete = rp.id_paquete ".
				"JOIN pto_vta pto ON mov.cod_pto_desp = pto.cod_pto ".
				"JOIN maeraspas mae ON rp.tipo_paquete = mae.codigo ".
				// --- LEFT JOIN a la tabla de paquetes especiales ---
				"LEFT JOIN public.raspas_paquetes_especiales rpe ON rp.nro_paquete::TEXT = ANY(rpe.paquetes_especiales) ".
				// --- ---
				"WHERE mov.fec_despacho is not null $condi_fecha ".
				"  ".$condicion_paquete.
				"  ".$condicion_pto;
				
				$stmt = $db->query($sql_ppal); 

				$orden 	= " order by mueble,nro_paquete";								
				$grupo 	= " group by mueble,nro_paquete,nombre,fec_despacho,vlr_raspa";				
				
				$sql= "select mueble,nro_paquete,nombre,nombre,fec_despacho,min(codraspa)||'-'||max(codraspa) as rango,
					          vlr_raspa,count(*) as cantidad 
					   from tempo_mov".$grupo.$orden.";"; 
				
				$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
				$stm->execute(); // Se ejecuta la consulta.			
				$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
				$stm->closeCursor(); // Se libera el recurso.
				
				$_SESSION['arreglo_despacho']=$arre;
				
				$contador=0;
				$total = 0;
				
				$mtabla='<table align="center" id="table2" style="width: 50%;">';	
				if (!empty($arre)) 
				{
					$tabla.='
							<table class="" width="90%" cellspacing=0 cellpadding=3 border=1 id="tbl_despachos" align="center">	
								<tr style="background-color:#00bfff;color:#ffffff">
										<td width="5%" align="center">PUNTO</td>
										<td width="5%" align="center">PAQUETE</td>
										<td width="15%" align="center">NOMBRE</td>
										<td width="5%" align="center">RANGO</td>
										<td width="5%" align="center">VALOR</td>
										<td width="5%" align="center">CANTIDAD</td>
								</tr>
							';	

					//$contador=0;
					//$total = 0;
					foreach($arre as $row) 						
					{
						if ($contador % 2) { $fondolinea="#dbeef8"; } else { $fondolinea="#9aceeb"; }
						
						$tabla.='
						<tr bgcolor='.$fondolinea.'> 
							<td style="width: 20px;height: 30px"><div align="center">'.$row['mueble'].'</div></td>
							<td width="5%"><div align="center">'.$row['nro_paquete'].'</div></td>
							<td width="15%"><div align="center">'.$row['nombre'].'</div></td>
							<td width="5%"><div align="center">'.$row['rango'].'</div></td>
							<td width="5%"><div align="right">'.number_format($row['vlr_raspa']).'</div></td>	
							<td width="5%"><div align="right">'.$row['cantidad'].'</div></td>	
						</tr>';	

						$total = $total+$row['cantidad'];
						$contador++;
					}
							
				}

				$tabla.='
				<tr bgcolor="#ff4d74"> 
					<td width="15%"><div align="center">TOTAL ==>></div></td>
					<td width="15%" colspan="5"><div align="right">'.number_format($total).'</div></td>
				</tr>
				';	

				$tabla.='</table>';			
				
				$db->commit();
			} 
			catch(PDOException $e)
			{
				$error =  $e->getMessage().' - '.$sql;
				$db->rollBack();				  			
			}
		}
		else
		{
			$error = 'ERROR: EL MAXIMO NUMERO DE DIAS A PROCESAR ES DE 31, POR FAVOR REVISE';
		}		

		//$error = $sql_ppal;

		$arreglo = array ('salida'=>$tabla,'total'=>$total,'error'=>$error);
		print json_encode($arreglo);			
	}


	if($paso=='consulta2')
	{
		$cod_pto      = $_POST['cboPtoventa'];
		$rango_fechas = $_POST['rango_fechas'];	
		$fec_venta    = $_POST['fec_venta'];	
		$tipo_paquete = $_POST['cboMaeraspas'];	
		$error	      = '';		
		
		$condicion_paquete = " and rp.tipo_paquete = ".$tipo_paquete;

		if ($tipo_paquete == '99')
		{
			$condicion_paquete = '';	
		}
		
		$condicion_pto = " AND mov.cod_pto_desp = '".$cod_pto."'";				
		switch ($cod_pto) 
		{
			case '*-*':
				$condicion_pto = " AND mov.cod_pto_desp > 0 ";		
				break;
				
			case '*t*':
				$condicion_pto = " and pto.zona not in ('P') and pto.cod_pto not in (18)";			
				break;			
		}		
		
		if (!empty($rango_fechas))
		{
			$arre = explode("-", $rango_fechas);
			
			$fec_ini = $arre[0];
			$fec_fin = $arre[1];

			$date1 = str_replace('/', '-',$fec_ini);	
			$date2 = str_replace('/', '-',$fec_fin);	

			$fecha1= new DateTime($date1);
			$fecha2= new DateTime($date2);
			$diff = $fecha1->diff($fecha2);
			 
			$diferencia_dias = $diff->days+1;
		}

		try 
		{
			$db->beginTransaction(); 

			$nro_venta_maxima = fn_venta_maxima($fec_venta);			

			if (empty($nro_venta_maxima))
			{
				$nro_venta_maxima = '0'; //esto podria nunca suceder ya que siempre existen ventas
			}

			$sql = "select rp.nro_paquete,mov.cod_pto_desp,pto.mueble,mov.fec_despacho::date as fec_despacho,
					CASE
						WHEN ((mov.id_venta > 0 and mov.id_venta_raspa < $nro_venta_maxima) or mov.cod_pto_desp = 94 or mov.cod_pto_desp = 95) THEN 'VENDIDO' 
						WHEN (mov.estado = 'DEVOLUCION') THEN 'DEVOLUCION'
						when mov.id_venta_raspa > $nro_venta_maxima then 'DISPONIBLE'
						ELSE 'DISPONIBLE' -- Añadido ELSE						
					END  estado, 
                    case
                        -- Condición para 'VENDIDO'
                        when ((mov.id_venta > 0 and mov.id_venta_raspa < $nro_venta_maxima) or mov.cod_pto_desp IN (94, 95)) then '2'
                        -- Condición para 'DEVOLUCION'
                        when (mov.estado = 'DEVOLUCION') then '5'
                        -- Condición específica para 'DISPONIBLE' (id_venta_raspa > max)
                        when mov.id_venta_raspa > $nro_venta_maxima then '33'
                        -- ELSE para todos los demás casos (que deberían ser 'DISPONIBLE' según el CASE de estado)
                        ELSE '3'
                    end orden,
					count(1) total,
					'' as rango,
					rp.id_paquete,
					CASE -- Aplicando la lógica para el nombre aquí
						WHEN rpe.codigo IS NOT NULL THEN rpe.nombre
						ELSE mae.nombre
					END AS nombre,
					rb.nro_caja,
					usu.nom_usu					
					from 
                   		 movraspas mov
                    JOIN raspas_paquetes rp ON mov.id_paquete = rp.id_paquete
                    JOIN pto_vta pto ON mov.cod_pto_desp = pto.cod_pto
                    JOIN maeraspas mae ON rp.tipo_paquete = mae.codigo
					JOIN raspas_bodega rb ON rp.id_caja = rb.id_caja
                    LEFT JOIN public.raspas_paquetes_especiales rpe ON rp.nro_paquete::TEXT = ANY(rpe.paquetes_especiales) -- JOIN añadido
					LEFT JOIN usuarios usu ON mov.id_lotero = usu.id_usu
					where mov.codraspa is not null
					and mov.id_paquete = rp.id_paquete
					and mov.id_paquete > 0
					and mov.cod_pto_desp = pto.cod_pto 
					and rp.tipo_paquete = mae.codigo
					and mov.fec_despacho is not null
  					";
	 
			$sql.=" AND fec_despacho::date between '".$fec_ini."' and  '".$fec_fin."'".$condicion_paquete.$condicion_pto; 
			
			$sql.="group by 1,2,3,4,5,6,9,10,11,12 union all ";
			
			$sql.="select rp.nro_paquete,mov.cod_pto_desp,pto.mueble,mov.fec_despacho::date as fec_despacho,
					'TODAS' estado,
					'1' orden,
					count(1) total,
					min(substring(mov.codraspa,13))||'-'||max(substring(mov.codraspa,13)) as rango,
					rp.id_paquete,
					CASE -- Aplicando la lógica para el nombre aquí también
						WHEN rpe.codigo IS NOT NULL THEN rpe.nombre
						ELSE mae.nombre
					END AS nombre,
					rb.nro_caja,
					usu.nom_usu
					from 
						movraspas mov
                    JOIN raspas_paquetes rp ON mov.id_paquete = rp.id_paquete
                    JOIN pto_vta pto ON mov.cod_pto_desp = pto.cod_pto
                    JOIN maeraspas mae ON rp.tipo_paquete = mae.codigo
					JOIN raspas_bodega rb ON rp.id_caja = rb.id_caja
                    LEFT JOIN public.raspas_paquetes_especiales rpe ON rp.nro_paquete::TEXT = ANY(rpe.paquetes_especiales) -- JOIN añadido
					LEFT JOIN usuarios usu ON mov.id_lotero = usu.id_usu
				    where mov.codraspa is not null 
					and mov.id_paquete = rp.id_paquete
					and mov.id_paquete > 0
					and mov.cod_pto_desp = pto.cod_pto 
					and rp.tipo_paquete = mae.codigo
					and mov.fec_despacho is not null 
				    ";
			$sql.=" AND fec_despacho::date between '".$fec_ini."' and  '".$fec_fin."'".$condicion_paquete.$condicion_pto; 
			
			$sql.="group by 1,2,3,4,5,6,9,10,11,12 order by 3,1,6";

			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.
				
			$tbl_temp = "create temporary table tbl_raspas(
							cod_pto numeric(5),
							mueble  varchar(30),
							id_paquete varchar(30),
							nro_paquete varchar(30),
							nro_caja varchar(30),
							nombre varchar(50),
							fec_despacho varchar(20),
							rango varchar(20),
							todas numeric(5,0) default 0,
							vendidas numeric(5,0) default 0,
							disponibles numeric(5,0) default 0,
							devolucion numeric(5,0) default 0,
							diferencia numeric(5,0) default 0,
							lotero varchar(50)
						)on commit drop";

			$db->exec($tbl_temp);
			
			$tbl_temp = "create temporary table tbl_raspas_acumulado(
							cod_pto numeric(5),
							mueble  varchar(30),
							todas numeric(5,0) default 0,
							vendidas numeric(5,0) default 0,
							disponibles numeric(5,0) default 0,
							devolucion numeric(5,0) default 0,
							diferencia numeric(5,0) default 0
						)on commit drop";

			$db->exec($tbl_temp);			

			foreach($arre as $row) 					
			{
				$cod_pto      = $row['cod_pto_desp'];
				$mueble 	  = $row['mueble'];
				$id_paquete   = $row['id_paquete'];
				$nro_paquete  = $row['nro_paquete'];
				$nro_caja     = $row['nro_caja'];
				$nombre       = $row['nombre'];
				$fec_despacho = $row['fec_despacho'];
				$rango 		  = $row['rango'];
				$total 		  = $row['total'];
				$orden 		  = $row['orden'];
				$nom_usu	  = $row['nom_usu'];
				
				try { // <-- TRY INTERNO para esta iteración				
				
					if ($orden == '1')
					{
						$sql = "insert into tbl_raspas (cod_pto,mueble,id_paquete,nro_paquete,nro_caja,nombre,fec_despacho,rango,todas,lotero) 
								values ($cod_pto,'$mueble',$id_paquete,$nro_paquete,$nro_caja,'$nombre','$fec_despacho','$rango',$total,'$nom_usu')";						
						$db->exec($sql);

						//esta validacion es debido a que el acumulado es por punto (mueble)
						$exito   = '0';
						$sql = "with updated_rows as (update tbl_raspas_acumulado set todas = todas+$total
									where mueble = '$mueble'
									returning '1' as exito)
									select exito
									from updated_rows																		
									";					

						$stmt= $db->query($sql);						
						
						$exito = $stmt->fetchColumn();
						
						if ($exito == '0') // no existe entonces inserta el registro
						{
							$sql = "insert into tbl_raspas_acumulado (cod_pto,mueble,todas) 
									values ($cod_pto,'$mueble',$total)";						
							$db->exec($sql);
						}
					}
					
					if ($orden == '2')
					{
						$sql = "update tbl_raspas set vendidas = $total 
								where mueble = '$mueble' and nro_paquete = '$nro_paquete' 
								and fec_despacho = '$fec_despacho' and lotero = '$nom_usu'";
						$db->exec($sql);

						$sql = "update tbl_raspas_acumulado set vendidas = vendidas+$total 
								where mueble = '$mueble'";
						$db->exec($sql);
					}				
					
					if (($orden == '3') or ($orden == '33'))
					{
						$sql = "update tbl_raspas set disponibles = disponibles+$total 
								where mueble = '$mueble' and nro_paquete = '$nro_paquete' 
								and fec_despacho = '$fec_despacho' and lotero = '$nom_usu'";
						$db->exec($sql);	

						$sql = "update tbl_raspas_acumulado set disponibles = disponibles+$total 
								where mueble = '$mueble'";
						$db->exec($sql);
					}

				} catch (PDOException $e_inner) { // <-- CATCH INTERNO
					// Error específico de esta iteración
					$error_detalle = "Error en iteración #{$index} (Paquete: {$nro_paquete}, Mueble: {$mueble}): " . $e_inner->getMessage();
					// Opcional: Loguear los datos que causaron el error
					// error_log("Datos: " . json_encode($row));

					// --- CAMBIO CLAVE: Re-lanzar la excepción ---
					// Esto detendrá la ejecución aquí y pasará el control al CATCH PRINCIPAL
					throw new PDOException($error_detalle, (int)$e_inner->getCode(), $e_inner);
					// Ya NO necesitas:
					// $error = $error_detalle;
					// $db->rollBack();
					// break;
				}
			}
			
			//hallamos diferencias
			$sql = "select * from tbl_raspas order by mueble";
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.

			//print_r($arre);
			//exit;
		
			foreach($arre as $row) 					
			{
				$mueble 	  = $row['mueble'];
				$nro_paquete  = $row['nro_paquete'];
				$fec_despacho = $row['fec_despacho'];

				$diferencia = $row['todas'] - ($row['vendidas']+$row['disponibles']+$row['devolucion']);

				$sql = "update tbl_raspas set diferencia = $diferencia
						where mueble = '$mueble' and nro_paquete = '$nro_paquete' 
						and fec_despacho = '$fec_despacho'";
				$db->exec($sql);																								

			}

			//FIN hallamos diferencias
			
			$sql = "select * from tbl_raspas";
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.
			
			$_SESSION['arreglo_detalle']=$arre; 							
			
			//2023-ene-25 Mayra solicito esta opcion consistente en consultar pero sin incluir paquete, fec.despacho y rango, es decir,
			//            solo el acumulado de todos los tiquetes enviados a los puntos
			//hallamos diferencias
			
			$sql = "select * from tbl_raspas_acumulado";
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre_acum = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.						
			
			foreach($arre_acum as $row) 					
			{
				$mueble 	  = $row['mueble'];

				$diferencia = $row['todas'] - ($row['vendidas']+$row['disponibles']+$row['devolucion']);

				$sql = "update tbl_raspas_acumulado set diferencia = $diferencia
						where mueble = '$mueble'";
				$db->exec($sql);																								

			}
			//FIN hallamos diferencias de tbl_raspas_acumulado
			
			$sql = "select * from tbl_raspas_acumulado";
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre_acum = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.	

			$_SESSION['arreglo_acumulado']=$arre_acum; 	
			
			//FIN 2023-ene-25

			$db->commit(); 
		} 
		catch (PDOException $e) { // <-- CATCH PRINCIPAL
			// Captura errores ANTES del bucle o los RE-LANZADOS desde el bucle
			$error = "Error de BD: " . $e->getMessage(); // El mensaje ya será detallado si vino del catch interno
			// La variable $sql aquí podría no ser la que causó el error si vino del bucle
			$db->rollBack();
			// $arre ya debería estar inicializado como []
		}
		
		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);		
	}

	
	if($paso=='consulta3')
	{
		$rango_fechas 		 = $_POST['rango_fechas'];	
		$nro_paquete_validar = $_POST['nro_paquete_validar'];	
		$arre      	 		 = '';	
		$error	  			 = '';	

		$condi_fecha = "";
		if (!empty($rango_fechas))
		{
			$arre = explode("-", $rango_fechas);
			
			$fec_ini = $arre[0];
			$fec_fin = $arre[1];			
			
			$condi_fecha = " and mov.fec_despacho::date between '".$fec_ini."' and  '".$fec_fin."' "; 
		}
		
		$condi_nro_paquete = "";
		if (!empty($nro_paquete_validar ))
		{
			$condi_fecha = ""; //inicializa entonces las fechas
			$condi_nro_paquete = " and rp.nro_paquete = '$nro_paquete_validar' ";
		}
		
		try 
		{
			$db->beginTransaction(); 
			
			$sql = "select distinct rp.id_paquete,rp.nro_paquete,rp.vlr_raspa,
						   mae.nombre_corto nombre,mae.codigo
				    from movraspas mov, raspas_paquetes rp, maeraspas mae
					where mov.id_paquete = rp.id_paquete
					and rp.id_paquete > 0
					and rp.tipo_paquete = mae.codigo".$condi_fecha.$condi_nro_paquete;

			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.
					
			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$msg=  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}	

		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);		
	}	
	
	if($paso=='consulta4')
	{
		$tipo_paquete = $_POST['cboMaeraspas'];	
		$arre      	 		 = '';	
		$error	  			 = '';	

		$condi_paquete = " and rp.tipo_paquete = ".$tipo_paquete;

		if ($tipo_paquete == '99')
		{
			$condi_paquete = '';	
		}		
		
		try 
		{
			$db->beginTransaction(); 
			
			$sql = "select rp.nro_paquete,mae.nombre_corto as nombre,
				   /*min(substring(mov.codraspa,13)) as raspa_inicial, 
				   max(substring(mov.codraspa,13)) as raspa_final,*/
				   min(mov.codraspa) as raspa_inicial, 
				   max(mov.codraspa) as raspa_final,				   
				   rp.vlr_raspa, count(1) as total 
				   from movraspas mov, raspas_paquetes rp, maeraspas mae
				   where mov.id_paquete = rp.id_paquete 
				   and mov.cod_pto = 18
				   and mov.estado = 'RESERVADO'
				   and rp.tipo_paquete = mae.codigo".$condi_paquete."
				   group by 1,2,5
				   order by 1,2";

			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.
			
			$_SESSION['arreglo_bodega']=$arre; 
					
			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$msg=  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}	

		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);		
	}	
	
	
	
	if($paso=='consulta_disponibles')
	{
		$cod_pto    = $_POST['cod_pto'];	
		$id_paquete = $_POST['id_paquete'];
		$error	  = '';		

		try 
		{
			$db->beginTransaction(); 	
			
			$sql = "select codraspa 
					from movraspas
					where id_paquete = $id_paquete 
					and cod_pto = $cod_pto
					and estado = 'DISPONIBLE'
					order by codraspa";

			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.				
		
			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$error =  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}		
		
		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);	
	}
	
	if($paso=='consulta_paquetes_bodega')
	{
		$nro_caja     = $_POST['nro_caja'];
		$paq_ini_bodega = $_POST['paq_ini_bodega'];
		$paq_fin_bodega = $_POST['paq_fin_bodega'];
		$cboMaeraspas = $_POST['cboMaeraspas'];
		$error	  = '';		

		try 
		{
			$db->beginTransaction(); 	
			
			$arre[] = "";				
			for ($x=$paq_ini_bodega;$x<=$paq_fin_bodega; $x++)
			{
				
				$sql = "select rp.id_paquete,cast(rp.nro_paquete as numeric) nro_paquete, rp.id_caja,min(mov.codraspa) codraspa_ini, 
								max(mov.codraspa) codraspa_fin,cast(max(mov.codraspa) as numeric) - cast(min(mov.codraspa) as numeric)+1 cantidad, 
								pto.mueble,mov.estado,mae.nombre_corto||'-'||mae.valor nombre
						from raspas_bodega rb,raspas_paquetes rp,movraspas mov,pto_vta pto, maeraspas mae
						where mov.id_paquete = rp.id_paquete
						and mov.cod_pto_desp = pto.cod_pto
						and rb.id_caja  = rp.id_caja
						and rb.nro_caja = $nro_caja
						and rp.tipo_paquete  = mae.codigo
						/*and cast(rp.nro_paquete as numeric) = $x*/
						and rp.nro_paquete::numeric = $x
						group by rp.id_paquete,rp.id_caja,pto.mueble,mov.estado,mae.nombre_corto,mae.valor
						order by rp.nro_paquete,codraspa_ini";	

				$sw = '0';	
				foreach ($db->query($sql) as $row)
				{				
					$data = array("id_caja"=>$row['id_caja'],"id_paquete"=>$row['id_paquete'],
								  "nro_paquete" =>$row['nro_paquete'],"codraspa_ini" =>$row['codraspa_ini'],
								  "codraspa_fin" =>$row['codraspa_fin'],
								  "cantidad" =>$row['cantidad'],"mueble" =>$row['mueble'],
								  "estado" =>$row['estado'],"nombre" =>$row['nombre']);

					$arre[] = $data;
					
					$sw = '1';	
				}
				
				if ($sw == '0')
				{
					$data = array("id_caja"=>'0',"id_paquete"=>'0',"nro_paquete" =>$x,
								  "codraspa_ini" =>'',"codraspa_fin" =>'',
								  "cantidad" =>'0',"mueble" =>'P-90',"estado" =>'',
								  "nombre" =>'');
								  
					$arre[] = $data;								  
					
				}

			}
		
			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$error =  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}		
		
		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);	
	}	

	if($paso=='consulta_raspa')
	{
		$codiac = $_POST['codiac'];	
		$arre1  = array();			
		$error  = "";	

		try 
		{
			$db->beginTransaction(); 
			
			$sql = "select mov.*,rp.nro_paquete,rp.vlr_raspa,mae.nombre_corto nombre,pto.nom_pto  
					from movraspas mov, raspas_paquetes rp, pto_vta pto ,maeraspas mae 
					where mov.codraspa = '$codiac' 
					and mov.id_paquete = rp.id_paquete
					and mov.cod_pto_desp = pto.cod_pto
					and rp.tipo_paquete = mae.codigo";
					
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.	

			$db->commit(); 

		} 
		catch(PDOException $e)
		{
			$msg=  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}	

		$sw = '0';
		foreach($arre as $row) 		
		{
			//datos de la venta
			
			$nom_usu_vta = "";
			$nom_pto_vta = ""; 	
			$fec_venta   = "";
			
			if ($row['id_venta_raspa'] > 0 )
			{
				$sql = "select usu.nom_usu,pto.nom_pto,vm.fec_venta
					    from ventas_mae vm, usuarios usu, pto_vta pto
						where vm.id_venta = ".$row['id_venta_raspa'].
						" and vm.id_usu = usu.id_usu 
						and vm.cod_pto = pto.cod_pto";	
						
				$stmt        = $db->query($sql);
				$nom_usu_vta = $stmt->fetchColumn(0);					
						
				$stmt        = $db->query($sql);
				$nom_pto_vta = $stmt->fetchColumn(1);

				$stmt        = $db->query($sql);
				$fec_venta   = $stmt->fetchColumn(2);						
			}
			
			$id_venta_premio = '';
			$nom_usu_pre 	 = '';					
			$nom_pto_pre 	 = '';
			$fec_pre     	 = '';
			$vlr_pre     	 = '';
			
			if (($row['estado'] == 'PREMIO') && ($row['id_paquete'] != '0'))
			{
				$sql = "select usu.nom_usu,pto.nom_pto,vm.fec_venta
					    from ventas_mae vm, usuarios usu, pto_vta pto
						where vm.id_venta = ".$row['id_venta'].
						" and vm.id_usu = usu.id_usu 
						and vm.cod_pto = pto.cod_pto";	
						
				$stmt        = $db->query($sql);
				$nom_usu_pre = $stmt->fetchColumn(0);					
						
				$stmt        = $db->query($sql);
				$nom_pto_pre = $stmt->fetchColumn(1);

				$stmt        = $db->query($sql);
				$fec_pre     = $stmt->fetchColumn(2);

				$id_venta_premio = $row['id_venta'];	
				$vlr_pre     	 = $row['vlr_pre'];				
			}
			
			$data = array("fec_despacho"=>substr($row['fec_despacho'],0,16),
						  "nro_paquete"=>$row['nro_paquete'],
						  "nombre"=>strtoupper($row['nombre']),
						  "pto_vta_desp"=>$row['nom_pto'],
						  "vlr_raspa"=>number_format($row['vlr_raspa']),
						  "id_venta_raspa"=>$row['id_venta_raspa'],
						  "nom_usu_vta"=>$nom_usu_vta,
						  "nom_pto_vta"=>$nom_pto_vta,
						  "fec_venta"=>substr($fec_venta,0,16),
						  "id_venta_premio"=>$id_venta_premio,
						  "nom_usu_pre"=>$nom_usu_pre,
						  "nom_pto_pre"=>$nom_pto_pre,
						  "fec_pre"=>substr($fec_pre,0,16),
						  "vlr_pre"=>number_format($vlr_pre)
					);				  
				  
			$arre1[] = $data;
			
			$sw = '1';
		}
		
		if ($sw=='0')
		{
			$error = 'NO SE ENCONTRO REGISTRO';	
		}
		
		$arreglo = array ('arreglo'=>$arre1,'error'=>$error);

		print json_encode($arreglo);
	}


	if($paso=='busca_raspas_inicial_final')
	{
		$nro_paquete = $_POST['nro_paquete'];
		
		$error	  = '';		

		try 
		{
			$db->beginTransaction(); 
			
			$sql = "select min(mov.codraspa) as codiac_ini,max(mov.codraspa) as codiac_fin,mae.codigo, mae.nombre,rb.vlr_raspa
				   from movraspas mov, raspas_paquetes rp,raspas_bodega rb,maeraspas mae
				   where mov.id_paquete = rp.id_paquete
				   and rp.id_caja = rb.id_caja
				   and rp.tipo_paquete = mae.codigo
				   and rp.nro_paquete::numeric = $nro_paquete
				   and mov.estado in ('DISPONIBLE','RESERVADO')
				   group by mae.codigo, mae.nombre,rb.vlr_raspa	";

			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.	

			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$error =  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}		
		
		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);	
	
	}

	if($paso=='busca_datos_caja_bodega')
	{
		$nro_caja     = $_POST['nro_caja'];
		
		$error	  = '';		

		try 
		{
			$db->beginTransaction(); 
			
			$sql = "select rp.*,upper(mae.nombre_corto) nombre 
					from raspas_bodega rp, maeraspas mae
					where rp.nro_caja = $nro_caja
					and rp.tipo_raspa = mae.codigo";
					
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.

			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$error =  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}		
		
		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);				
		
	}

	if($paso=='solo_raspas_disponibles')
	{
		$fec_venta = $_POST['fec_venta'];
		$error	  = '';		

		try 
		{
			$db->beginTransaction(); 
			
			//$nro_venta_maxima = fn_venta_maxima($fec_venta);
			
			$sql = "select pto.mueble,rp.nro_paquete,mae.nombre,count(1) cantidad
				from movraspas mov, raspas_paquetes rp ,pto_vta pto, maeraspas mae
				where mov.codraspa is not null
				and mov.estado = 'DISPONIBLE'
				and mov.id_paquete = rp.id_paquete
				and mov.id_paquete > 0
				and mov.cod_pto_desp = pto.cod_pto 
				and rp.tipo_paquete = mae.codigo
				and mov.fec_despacho is not null
				group by pto.mueble,rp.nro_paquete,mae.nombre
				order by pto.mueble,rp.nro_paquete,mae.nombre
				";	

			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.

			$db->commit(); 
		} 
		catch(PDOException $e)
		{
			$error =  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}		
		
		$arreglo = array ('arreglo'=>$arre,'error'=>$error);

		print json_encode($arreglo);				
	}

	if($paso=='rep_excell_disponibles')
	{
		ini_set('max_execution_time','600');
		ini_set('memory_limit','1024M');	

		$msg          = "";		
		$arre         = $_POST['arreglo'];
		
		$rep    = 'raspas_disponibles_a_'.time().'.xls';
		$action = "../../reports/".$rep;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()
			->setCreator("Depto de Sistemas ERT")
			->setLastModifiedBy("Depto de Sistemas ERT")
			->setTitle("Excel para Contabilidad")
			->setSubject("Excel para Contabilidad")
			->setDescription("Documento generado con SISI")
			->setKeywords("usuarios sistemas")
			->setCategory("reportes");  

		if (!empty($arre)) 
		{	
	
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A2', 'Punto')
				->setCellValue('B2', 'Paquete')
				->setCellValue('C2', 'Tipo')
				->setCellValue('D2', 'Cantidad');
			
			$fecha=strftime( "%Y-%m-%d-%H-%M-%S", time() );
			
			$objPHPExcel->getActiveSheet()->setTitle('DISPONIBLES');

			$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
			$objPHPExcel->getActiveSheet()->getCell('A1')->setValue('RASPAS DISPONIBLES');
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);				

			$w = 3;
			foreach($arre as $row) 		
			{
				$objPHPExcel->getActiveSheet()->SetCellValue("A".$w,$row['mueble']);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$w,$row['paquete'],PHPExcel_Cell_DataType::TYPE_STRING);	
				$objPHPExcel->getActiveSheet()->SetCellValue("C".$w,$row['nombre']);
				$objPHPExcel->getActiveSheet()->SetCellValue("D".$w,$row['cantidad']);			
				$w++;				
			}
	
			$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');			
		
			$objWriter->save($action);		
	
		}
		
		$arreglo = array('error'=>$msg,'action'=>$action);
		print json_encode($arreglo);					
	}

	if($paso=='rep_excell_bodega')
	{
		
		if (isset($_SESSION['arreglo_bodega']))
		{		
			ini_set('max_execution_time','600');
			ini_set('memory_limit','1024M');	

			$msg          = "";		
			
			$rep    = 'raspas_disponibles_bodega_'.time().'.xls';
			$action = "../../reports/".$rep;
			
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()
				->setCreator("Depto de Sistemas ERT")
				->setLastModifiedBy("Depto de Sistemas ERT")
				->setTitle("Excel para Contabilidad")
				->setSubject("Excel para Contabilidad")
				->setDescription("Documento generado con SISI")
				->setKeywords("usuarios sistemas")
				->setCategory("reportes");  

			$arre = $_SESSION['arreglo_bodega'];	

			if (!empty($arre)) 
			{	
		
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', 'Paquete')
					->setCellValue('B2', 'Tipo')
					->setCellValue('C2', 'Valor')
					->setCellValue('D2', 'Raspa Ini.')
					->setCellValue('E2', 'Raspa Fin.')
					->setCellValue('F2', 'Cantidad');
				
				$fecha=strftime( "%Y-%m-%d-%H-%M-%S", time() );
				
				$objPHPExcel->getActiveSheet()->setTitle('DISPONIBLES');

				$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
				$objPHPExcel->getActiveSheet()->getCell('A1')->setValue('RASPAS DISPONIBLES EN BODEGA');
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);				

				$w = 3;
				
				foreach($arre as $row) //AQUI VOY		
				{
					$objPHPExcel->getActiveSheet()->setCellValueExplicit("A".$w,$row['nro_paquete'],PHPExcel_Cell_DataType::TYPE_STRING);	
					$objPHPExcel->getActiveSheet()->SetCellValue("B".$w,$row['nombre']);
					$objPHPExcel->getActiveSheet()->SetCellValue("C".$w,number_format($row['vlr_raspa']));
					$objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$w,$row['raspa_inicial'],PHPExcel_Cell_DataType::TYPE_STRING);	
					$objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$w,$row['raspa_final'],PHPExcel_Cell_DataType::TYPE_STRING);	
					$objPHPExcel->getActiveSheet()->SetCellValue("F".$w,$row['total']);			
					$w++;				
				}
		
				try {
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
					$objWriter->save($action);
				} catch (Exception $e) {
					$msg = "Error al guardar el archivo Excel: " . $e->getMessage();
					$arreglo = array('error' => $msg, 'action' => $action);
					print json_encode($arreglo);
					exit;
				}	
		
			}
			
			$arreglo = array('error'=>$msg,'action'=>$action);
		}
		else
		{
			$msg = "POR FAVOR PRIMERO REALICE LA CONSULTA!!";
			$arreglo = array('error'=>$msg);			
		}
		
		unset ($_SESSION['arreglo_bodega']);	
		
		print json_encode($arreglo);		
	}

	if($paso=='genera_excell_premios')
	{
		ini_set('max_execution_time','600');
		ini_set('memory_limit','1024M');		

		$arre    = $_POST['arreglo'];			
		$fec_pre = $_POST['fec_pre'];	
		$msg     = "";		
		
		$rep    = 'premios_raspa_listos_'.time().'.xls';
		$action = "../../reports/".$rep;

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()
			->setCreator("Depto de Sistemas ERT")
			->setLastModifiedBy("Depto de Sistemas ERT")
			->setTitle("Excel para Contabilidad")
			->setSubject("Excel para Contabilidad")
			->setDescription("Documento generado con SISI")
			->setKeywords("usuarios sistemas")
			->setCategory("reportes");  

		if (!empty($arre)) 
		{	
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'Punto')
				->setCellValue('B1', 'Codigo')
				->setCellValue('C1', 'Paquete')
				->setCellValue('D1', 'Venta')			
				->setCellValue('E1', 'F.Premio')			
				->setCellValue('F1', 'Valor')			
				->setCellValue('G1', 'Usuario');			
			
			$fecha='Premios_RyL_'.strftime( "%Y-%m-%d-%H-%M-%S", time() );
			
			$objPHPExcel->getActiveSheet()->setTitle($fecha);
			
			$styleArray = array(
				'font'	=> array(
					'bold'	=> true,
					'color'	=> array('rgb' =>'FF0000'),
					'size'	=> 8,
					'name'	=> 'Verdana'
				)
			);			
			
			$w = 2;
			foreach($arre as $row) 		
			{
				$objPHPExcel->getActiveSheet()->SetCellValue("A".$w,$row['mueble']);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$w,$row['codraspa'],PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$w,$row['nro_paquete'],PHPExcel_Cell_DataType::TYPE_STRING);			
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$w,$row['id_venta']);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$w,$row['fec_premio']);				
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$w,$row['vlr_pre']);								
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$w,$row['nom_usu']);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);			
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

				if ($row['novedad']=='1') //lo resalta para que lo identifiquen facilmente
				{
					$objPHPExcel->getActiveSheet()->getStyle("A".$w)->applyFromArray($styleArray);	
					$objPHPExcel->getActiveSheet()->getStyle("B".$w)->applyFromArray($styleArray);	
					$objPHPExcel->getActiveSheet()->getStyle("C".$w)->applyFromArray($styleArray);	
					$objPHPExcel->getActiveSheet()->getStyle("D".$w)->applyFromArray($styleArray);	
					$objPHPExcel->getActiveSheet()->getStyle("E".$w)->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getStyle("F".$w)->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getStyle("G".$w)->applyFromArray($styleArray);
				}
				
				$w++;
			}		
		
			$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');			
		
			$objWriter->save($action);			

		}
		$arreglo = array('error'=>$msg,'action'=>$action);
		print json_encode($arreglo);
	}

	if($paso=='genera_excell1')
	{
		ini_set('max_execution_time','600');
		ini_set('memory_limit','1024M');	

		$msg          = "";		
		//$arre         = $_POST['arreglo'];
		$arre		  = $_SESSION['arreglo_detalle'];				
		$fec_venta    = $_POST['fec_venta'];	
		$rango_fechas = $_POST['rango_fechas'];	
		$tipo         = $_POST['tipo'];	
		
		$arre_p = explode("-", $tipo);

		$producto = $arre_p[0]; 
		
		if ($producto == '**TODOS**')
		{
			$producto = 'TODOS LOS PRODUCTOS';
		}
		
		//$producto.= " HASTA FEC.VENTA: '$fec_venta'";
		//$producto = $producto." HASTA FEC.VENTA: '$fec_venta'";
		
		$rep    = 'desp_raspa_listos_'.time().'.xls';
		$action = "../../reports/".$rep;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()
			->setCreator("Depto de Sistemas ERT")
			->setLastModifiedBy("Depto de Sistemas ERT")
			->setTitle("Excel para Contabilidad")
			->setSubject("Excel para Contabilidad")
			->setDescription("Documento generado con SISI")
			->setKeywords("usuarios sistemas")
			->setCategory("reportes");  

		if (!empty($arre)) 
		{
			
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A2', 'Punto')
				->setCellValue('B2', 'Paquete')
				->setCellValue('C2', 'Despacho')
				->setCellValue('D2', 'Nombre sin Caja')
				->setCellValue('E2', 'Nombre')
				->setCellValue('F2', 'Rango')			
				->setCellValue('G2', 'Inicial')
				->setCellValue('H2', 'Venta')
				->setCellValue('I2', 'Disponibles')
				->setCellValue('J2', 'Lotero');
			
			$fecha=strftime( "%Y-%m-%d-%H-%M-%S", time() );
			
			//$objPHPExcel->getActiveSheet()->setTitle('Paquete_F.despacho_Rango');
			$objPHPExcel->getActiveSheet()->setTitle($producto);

			//$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
			$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($producto." HASTA FEC. DE VENTA: $fec_venta");
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);				

			$w = 3;
			foreach($arre as $row) 		
			{
				$objPHPExcel->getActiveSheet()->SetCellValue("A".$w,$row['mueble']);
				$objPHPExcel->getActiveSheet()->SetCellValue("B".$w,$row['nro_paquete']);
				$objPHPExcel->getActiveSheet()->SetCellValue("C".$w,$row['fec_despacho']);			
				$objPHPExcel->getActiveSheet()->SetCellValue("D".$w,$row['nombre']);		
				$objPHPExcel->getActiveSheet()->SetCellValue("E".$w,$row['nombre'].'-'.$row['nro_caja']);		
				$objPHPExcel->getActiveSheet()->SetCellValue("F".$w,$row['rango']);		
				$objPHPExcel->getActiveSheet()->SetCellValue("G".$w,$row['todas']);				
				$objPHPExcel->getActiveSheet()->SetCellValue("H".$w,$row['vendidas']);				
				$objPHPExcel->getActiveSheet()->SetCellValue("I".$w,$row['disponibles']);	
				$objPHPExcel->getActiveSheet()->SetCellValue("J".$w,$row['lotero']);	
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);			
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);	
				$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);	

				$w++;
			}		

			if (isset($_SESSION['arreglo_acumulado']))
			{		
			
				$nueva_hoja = $objPHPExcel->createSheet();

				$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('A2', 'Punto')
					->setCellValue('B2', 'Inicial')
					->setCellValue('C2', 'Venta')				
					->setCellValue('D2', 'Disponibles')
					->setCellValue('E2', 'Devolucion')				
					->setCellValue('F2', 'Diferencia');
				
				$objPHPExcel->getActiveSheet()->setTitle('AcumuladoXPto');		

				$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
				$objPHPExcel->getActiveSheet()->getCell('A1')->setValue('Fechas de Despacho: '.$rango_fechas);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);				
				
				$arre_acum = array();				
				$arre_acum = $_SESSION['arreglo_acumulado'];	
				
				$w = 3;	
				
				foreach($arre_acum as $row) 		
				{	
					$objPHPExcel->getActiveSheet()->SetCellValue("A".$w,$row['mueble']);
					$objPHPExcel->getActiveSheet()->SetCellValue("B".$w,$row['todas']);				
					$objPHPExcel->getActiveSheet()->SetCellValue("C".$w,$row['vendidas']);				
					$objPHPExcel->getActiveSheet()->SetCellValue("D".$w,$row['disponibles']);
					$objPHPExcel->getActiveSheet()->SetCellValue("E".$w,$row['devolucion']);				
					$objPHPExcel->getActiveSheet()->SetCellValue("F".$w,$row['diferencia']);								
					
					$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);			
					$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
					$w++;			
				}

				/****SE COMENTARIO EL 2023-OCT-10, NO SE NECESITA INDICO MAYRA*****/
				/*
				//Pagina No 3
				//venta por dias:
				//$fec_actual = date('d/m/Y');
				$fec_actual = $fec_venta;  //indica hasta que fehca de ventas genera el reporte
				
				$pieces = explode("-", $rango_fechas);
				$fec_ini_desp =  trim($pieces[0]); // piece1
				
				//$sql = "select '2023-01-28' - (timestamp '2023-01-24')::date";
				$sql  = "select '$fec_actual' - (timestamp '$fec_ini_desp')::date";
				$stmt = $db->query($sql);
				$dias = $stmt->fetchColumn();


				//arreglo de titulos
				$arre_titulos = array(); // creo el array	
				array_push($arre_titulos,'Punto','Inicial');	
				
				$arre_fechas_rep = array(); // creo el array		
				$cadena_fechas = "";

				for ($i = 0; $i <= $dias; $i++)
				{
					if ($i == 0)
					{
						$date = date($fec_ini_desp);
						$mod_date = strtotime($date);					
						$fecha = date("Y-m-d",$mod_date);
						$fecha_field = date("M_d",$mod_date).' numeric(5,0) default 0,';
						$fecha_rep   = date("M_d",$mod_date);
					}
					else
					{
						$date = date($fec_ini_desp);
						$mod_date = strtotime($date."+ $i days");
						$fecha 	     = date("Y-m-d",$mod_date);
						$fecha_field.= date('M_d',$mod_date).' numeric(5,0) default 0,';
						$fecha_rep   = date("M_d",$mod_date);			
					}
					
					$cadena_fechas = $fecha_field;		
					array_push($arre_titulos,$fecha);
					
					$fecha_rep = strtolower($fecha_rep);
					array_push($arre_fechas_rep,$fecha_rep);		
				}
				
				array_push($arre_titulos,'Vendidas','Disponibles','Devolucion','Diferencia');	
				//FIN arreglo de titulos	
				
				// Arreglo del alfabeto
				$arre_alfabeto = array();
				for ($i = 65; $i <= 90; $i++) {
					$letra = chr($i);
					array_push($arre_alfabeto, $letra);
				}

				// Si $dias es mayor o igual a 15, agregar combinaciones
				if ($dias >= 15) {
					for ($j = 0; $j < 14; $j++) { // 10 veces (de A a J)
						for ($i = 65; $i <= 90; $i++) {
							$letra = chr($i);
							array_push($arre_alfabeto, chr($j + 65) . $letra);
						}
					}
				}

				//FIN titulos del movimiento de ventas por dia
				
				try 
				{
					$db->beginTransaction(); 					
				
					$w_cnt = count($arre_titulos);				
					
					$nueva_hoja = $objPHPExcel->createSheet();					
					
					for ($i = 0; $i < $w_cnt; ++$i){
						//print_r($arre_titulos[$i]);
						$objPHPExcel->setActiveSheetIndex(2)->setCellValue($arre_alfabeto[$i].'1',$arre_titulos[$i]);						
					}

					$objPHPExcel->getActiveSheet()->setTitle('venta_diaria');
					
					//extraemos los ptos y las fechas de venta con el fin de crear una tabla uniforme con igual numero de fechas, esto con el objetivo
					//de registrar por pto las ventas por fecha
					$tbl_temp = "create temporary table tbl_sorteos(
									cod_pto numeric(2),";
					$tbl_temp.= $cadena_fechas;
					$tbl_temp.= 'comodin varchar(1)';			
					$tbl_temp.=	") on commit drop";
					
					$db->exec($tbl_temp);	
					//FIN tabla de puntos
					
					//insertamos las fechas iguales para todos los puntos
					foreach($arre_acum as $row) 		
					{
						$cod_pto = $row['cod_pto'];

						if (!empty($cod_pto))	
						{
							$sql = "insert into tbl_sorteos (cod_pto) values ('$cod_pto')";	
							$db->exec($sql);	
						}	
					}
					//FIN insertamos las fechas iguales para todos los puntos
					
					$pieces = explode("-", $rango_fechas);
					$fec_ini_desp =  trim($pieces[0]);
					$fec_fin_desp =  trim($pieces[1]); 
					
					$sql = "select mov.cod_pto_desp cod_pto,to_char(vm.fec_venta,'mon_dd') dia, count(1) total
							from movraspas mov,ventas_mae vm 
							where vm.id_venta = mov.id_venta_raspa
							and mov.fec_despacho::date between '$fec_ini_desp' and '$fec_fin_desp'
							and vm.fec_venta::date between '$fec_ini_desp' and '$fec_venta'
							group by 1,2 order by 1,2
							";	
							
						//7854045							
					//ahora actualizamos la tabla tbl_sorteos
					foreach ($db->query($sql) as $row)
					{
						$cod_pto = $row['cod_pto'];					
						$dia     = $row['dia'];	
						$total   = $row['total'];
						
						$sql1 = "update tbl_sorteos set $dia = $total 
								where cod_pto = '$cod_pto' ";
						$db->exec($sql1);
						
					}
					//FIN traemos las ventas y vamos actualizando la tabla por pto de venta								
					
					$w = 2;
					foreach($arre_acum as $row) 		
					{
						$cod_pto = $row['cod_pto'];

						if (!empty($cod_pto))
						{
							$objPHPExcel->getActiveSheet()->SetCellValue("A".$w,$row['mueble']); 
							$objPHPExcel->getActiveSheet()->SetCellValue("B".$w,$row['todas']); 

							//imprime ventas por dia
							$sql     = "select * from tbl_sorteos where cod_pto = $cod_pto ";

							foreach ($db->query($sql) as $row1)
							{

								$dias = count($arre_fechas_rep);
								$letra_alfabeto = 2; //letra F

								for ($i = 0; $i <= $dias; ++$i){
									$dia_vta = $arre_fechas_rep[$i];
									if (!empty($dia_vta))
									{
										$objPHPExcel->getActiveSheet()->setCellValue($arre_alfabeto[$letra_alfabeto].$w,$row1[$dia_vta]);
									}
									$letra_alfabeto++;						
								}
							}
							//FIN imprime por dia				
							
							$letra_alfabeto = $letra_alfabeto-1;
							$objPHPExcel->getActiveSheet()->SetCellValue($arre_alfabeto[$letra_alfabeto].$w,$row['vendidas']);
							$letra_alfabeto++;															
							$objPHPExcel->getActiveSheet()->SetCellValue($arre_alfabeto[$letra_alfabeto].$w,$row['disponibles']);
							$letra_alfabeto++;										
							$objPHPExcel->getActiveSheet()->SetCellValue($arre_alfabeto[$letra_alfabeto].$w,$row['devolucion']);
							$letra_alfabeto++;														
							$objPHPExcel->getActiveSheet()->SetCellValue($arre_alfabeto[$letra_alfabeto].$w,$row['diferencia']);						
							
							$w_cnt = count($arre_alfabeto);
							for ($i = 0; $i < $w_cnt; ++$i){
								$objPHPExcel->getActiveSheet()->getColumnDimension($arre_alfabeto[$i])->setAutoSize(true);					
							}
						}
						$w++;				
					}					
				
					$db->commit(); 					
						
				}		
				catch(PDOException $e)
				{
					$msg=  $e->getMessage().' - '.$sql;
					echo $msg;
					$db->rollBack();				  			
				}					
				*/
				/****FIN     SE COMENTARIO EL 2023-OCT-10, NO SE NECESITA INDICO MAYRA*****/				
			}		
		
			if (isset($_SESSION['arreglo_acumulado'])){	
				unset ($_SESSION['arreglo_acumulado']);	
			}		
			
			if (isset($_SESSION['arreglo_detalle'])){	
				unset ($_SESSION['arreglo_detalle']);	
			}					
		
			$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');			
		
			$objWriter->save($action);	

		}
		$arreglo = array('error'=>$msg,'action'=>$action);
		print json_encode($arreglo);			
	}	

	if ($paso == 'valida_paquete')		
	{
		//$nro_paquete  = $_POST['nro_paquete'];
		$codraspa  = $_POST['codraspa'];
		$tabla        = '';
		
		try 
		{	
			
			$db->beginTransaction();	
			
			//a partir del codraspa ubicamos el nro del paquete:
			$sql = "select nro_paquete
					from movraspas mov, raspas_paquetes rp
					where mov.codraspa = '$codraspa'
					and mov.id_paquete = rp.id_paquete
				   ";
					 
			$sql = $db->prepare($sql);
			$sql->execute();
			$nro_paquete = $sql->fetchColumn(0);
			//fin a partir del codraspa ubicamos el nro del paquete:			
			
			$sql = "select pto.mueble,rp.nro_paquete,mov.fec_despacho::date as fec_despacho,
						  count(case when mov.estado = 'DISPONIBLE' or mov.estado = 'RESERVADO' then 'DISPONIBLE' end) as disponibles,
						  count(case when mov.estado <> 'DISPONIBLE' and mov.estado <> 'RESERVADO' then 'NO_DISPONIBLES' end) as no_disponibles,      
						  rp.vlr_raspa,min(substring(mov.codraspa,13))||'-'||max(substring(mov.codraspa,13)) as rango
					FROM movraspas mov,raspas_paquetes rp ,pto_vta pto
					WHERE mov.id_paquete = rp.id_paquete
					and mov.cod_pto = pto.cod_pto
					and rp.nro_paquete = '$nro_paquete'
					group by pto.mueble,rp.nro_paquete,mov.fec_despacho,rp.vlr_raspa";
					
			
			$stm  = $db->prepare($sql);  // Se crea un objeto PDOStatement.
			$stm->execute(); // Se ejecuta la consulta.			
			$arre = $stm->fetchAll(\PDO::FETCH_ASSOC); // Se recuperan los resultados.
			$stm->closeCursor(); // Se libera el recurso.
			
			$contador=0;
			$total = 0;
			
			if (!empty($arre)) 
			{
				$tabla.='<table class="table table-bordered table-condensed table-striped table-hover tablesorter table1" id="tbl_chequeos">';
				$tabla.='<thead>
							<tr>
								<th>Pto</th>														
								<th>Paquete</th>														
								<th>Rango</th>
								<th>Valor</th>
								<th>Disp</th>
								<th>No.Disp</th>
							</tr>
						</thead>';
						
				$tabla.='<colgroup>
							<col class="col-md-1">
							<col class="col-md-1">
							<col class="col-md-1">
							<col class="col-md-1">
							<col class="col-md-1">												
							<col class="col-md-1">												
						</colgroup>';
				$tabla.='<tbody>';
				
				$no_disponibles = '0';
				foreach($arre as $row) 						
				{
					$tabla.='<tr><td>'.$row['mueble'].'</td><td>'.$row['nro_paquete'].'</td><td>'.$row['rango'].
							'</td><td>'.number_format($row['vlr_raspa']).'</td><td>'.$row['disponibles'].
							'</td><td>'.$row['no_disponibles'];
					$tabla.='</td></tr>';

					$titulo = 'PAQUETE No. '.$row['nro_paquete'].' REGISTRADO EN '.$row['mueble'];
					$no_disponibles = $row['no_disponibles'];
				}

				$tabla.="<tfoot>SI TIENE RASPAS NO_DISPONIBLES <b>NO PERMITE</b> REALIZAR MODIFICACIONES</tfoot>";						
				$tabla.='</tbody>';						
				$tabla.='</table>';		
			}

			$db->commit();
		} 
		catch(PDOException $e)
		{
			$error =  $e->getMessage().' - '.$sql;
			$db->rollBack();				  			
		}		
		
		$arreglo = array ('titulo'=>$titulo,'tabla'=>$tabla,'no_disponibles'=>$no_disponibles);	
		print json_encode($arreglo);		
	}

	if ($paso == 'valida_raspa_inicial')		
	{
		//si el raspa que esta ingresando existe entonces retorna error
		$codraspa = $_POST['codraspa'];			
		$error = "";
		
		try 
		{		
		
			$db->beginTransaction();

			$sql = "select count(1)
					from movraspas 
					where codraspa = '$codraspa'
				   ";
					 
			$sql = $db->prepare($sql);
			$sql->execute();
			$total = $sql->fetchColumn(0);
			
			if ($total != '0')
			{
				$error = "CODIGO DE RASPA YA EXISTE EN LA BASE DE DATOS, POR FAVOR REVISE";	
			}

			$db->commit();
		}
		catch(PDOException $e)
		{
			$db->rollback(); 
			$error =  $e->getMessage().' - '.$sql;
		}
		
		$reg = array ('error'=>$error);	
		print json_encode($reg);			
		
	}

	if ($paso == 'grabar_despacho')		
	{
		$cod_pto      = $_POST['cboPtoventa'];		
		$tipo_paquete = $_POST['cboMaeraspas'];		
		$id_lotero    = $_POST['cboLotero'];		
		$nro_paquete  = $_POST['nro_paquete'];
		$vlr_raspa    = $_POST['vlr_raspa'];		
		$id_usu       = $_POST['id_usu'];				
		$codiac_ini   = $_POST['codiac_ini'];
		$codiac_fin   = $_POST['codiac_fin'];
		
		$error  = "";
		$msg    = "";
		$mensaje= "";		
		
		$codiac_ini_ciclo = substr($codiac_ini,-2);
		$codiac_fin_ciclo = substr($codiac_fin,-2);
		
		$codiac_pedazo = substr($codiac_ini,0,16);

		try 
		{		
		
			$db->beginTransaction();			
			
			/*
			$exito   = '0';

			$sql = "with updated_rows as (update raspas_paquetes set tipo_paquete = $tipo_paquete, usr_ult_modif = '$id_usu',vlr_raspa = $vlr_raspa
						where nro_paquete = '$nro_paquete'
						returning '1' as exito)
						select exito
						from updated_rows																		
						";					

			$stmt= $db->query($sql);						
			
			$exito = $stmt->fetchColumn();
			
			if ($exito == '0') // no existe entonces inserta el registro
			{
				$sql = "insert into raspas_paquetes (nro_paquete,tipo_paquete,vlr_raspa,usr_ult_modif) 
							values ('$nro_paquete',$tipo_paquete,$vlr_raspa,'$id_usu')";
				$db->exec($sql);

				$stmt = $db->query("select currval(pg_get_serial_sequence('raspas_paquetes', 'id_paquete'))");
				$id_paquete = $stmt->fetchColumn();			
			}
			else
			{
				$sql        = "select id_paquete from raspas_paquetes where nro_paquete = '$nro_paquete'";
				$stmt       = $db->query($sql);
				$id_paquete = $stmt->fetchColumn();	
			}
			*/
			
			//rellenamos de ceros el numero del paquete:
			//$m_nro_paquete = intval($nro_paquete);
			
			$m_nro_paquete = str_pad($nro_paquete, 8, '0', STR_PAD_LEFT);
			
			$sql = "update raspas_paquetes set nro_paquete = '$m_nro_paquete' where nro_paquete::numeric = '$nro_paquete'";
			$db->exec($sql);
			//FIN de rellenamos de ceros el numero del paquete:
			
			$codraspa = '';
			$insertados = 0;
			
			for ($x=$codiac_ini_ciclo;$x<=$codiac_fin_ciclo; $x++)
			{
				$codraspa = $codiac_pedazo.str_pad($x,2,'0',STR_PAD_LEFT);	

				if (strlen($codraspa) == 18)
				{

					$exito   = '0';
					
					$sql = "with updated_rows as (update movraspas set usr_ult_modif = '$id_usu',cod_pto = $cod_pto,
																	   cod_pto_desp = $cod_pto,fec_despacho = 'now()',
																	   estado = 'DISPONIBLE',id_lotero = '$id_lotero'
								where codraspa = '$codraspa'
								returning '1' as exito)
								select exito
								from updated_rows																		
								";

					$stmt= $db->query($sql);						
					
					$exito = $stmt->fetchColumn();
					
					/*
					if ($exito == '0') // no existe entonces inserta el registro
					{
						$sql = "insert into movraspas (codraspa,id_paquete,cod_pto,cod_pto_desp,usr_ult_modif,id_lotero)
								values ('$codraspa',$id_paquete,$cod_pto,$cod_pto,'$id_usu','$id_lotero')";
							
						$db->exec($sql);
					}
					*/
					
					$insertados = $insertados + 1;	
				}
			}
			
			$db->commit(); //lo adicione el 2023-jun-08 a las 08:01 am
			
			//lo comentarie el 2023-jun-08 a las 8:00 am

			if ($insertados > 0)			
			{
				//$db->commit();
				$mensaje=$insertados." Raspas grabados exitosamente!!"; 
			}

			//FIN lo comentarie el 2023-jun-08 a las 8:00 am			
		
		}
		catch(PDOException $e)
		{
			$db->rollback(); 
			$error =  $e->getMessage().' - '.$sql;
		}
		
		$reg = array ('msg'=>$mensaje,'insertados'=>$insertados,'error'=>$error);	
		print json_encode($reg);

	}

	if ($paso == 'grabar_paquete')
	{
		$id_paquete  = $_POST['id_paquete'];	
		$nro_paquete = $_POST['nro_paquete'];	
		$cboPaquete  = $_POST['cboPaquete'];
		$error       = "";
		
		try 
		{		
		
			$db->beginTransaction();

			//traemos el valor desde maeraspas
			$sql = "select valor
					from maeraspas
					where codigo = $cboPaquete";				
					
			$sql = $db->prepare($sql);
			$sql->execute();
			$valor = $sql->fetchColumn(0);			
		
			$sql = "update raspas_paquetes 
						set nro_paquete   = '$nro_paquete',
							tipo_paquete  = $cboPaquete,
							vlr_raspa     = $valor,
							usr_ult_modif = '$id_usu'
					where id_paquete = '$id_paquete' ";

			$db->exec($sql);

			$db->commit();			
		
		}
		catch(PDOException $e)
		{
			$db->rollback(); 
			$error =  $e->getMessage().' - '.$sql;
		}
		
		$reg = array ('error'=>$error);	
		print json_encode($reg);		
		
	}


	if ($paso == 'grabar_bodega')
	{
		
		$arreglo_mae  = $_POST['arreglo_mae'];	
		$arreglo_det  = $_POST['arreglo_det'];
		$cod_pto      = '18';
		$accion       = $_POST['accion'];
		$error       = "";
			
		try 
		{		
		
			$db->beginTransaction();

			//inserta o actualiza en la tabla raspas_bodega
			
			$exito   = '0';
			
			foreach($arreglo_mae as $row)
			{
				$nro_caja 	    = $row["nro_caja"];
				$paq_ini_bodega = $row["paq_ini_bodega"];
				$paq_fin_bodega = $row["paq_fin_bodega"];
				$id_usu         = $row["id_usu"];
				$cboMaeraspas   = $row["cboMaeraspas"];
				$vlr_raspa      = $row["vlr_raspa"];
				
				$sql = "with updated_rows as (update raspas_bodega set nro_paquete_inicial = $paq_ini_bodega,
																	   nro_paquete_final = $paq_fin_bodega,
																	   tipo_raspa = $cboMaeraspas,
																	   vlr_raspa = $vlr_raspa,
																	   usr_ult_modif = '$id_usu'
									where nro_caja = $nro_caja
									returning '1' as exito)
									select exito
									from updated_rows
						";
						
				$stmt= $db->query($sql);						
						
				$exito = $stmt->fetchColumn();	

				if ($exito == '0')				
				{
					$sql =  "INSERT INTO raspas_bodega
								(	nro_caja, nro_paquete_inicial, nro_paquete_final, tipo_raspa, vlr_raspa,usr_ult_modif)
							VALUES
								('$nro_caja',$paq_ini_bodega,$paq_fin_bodega,$cboMaeraspas,$vlr_raspa,'$id_usu')";				
					
					$db->exec($sql);
					
					$stmt = $db->query("select currval(pg_get_serial_sequence('raspas_bodega', 'id_caja'))");
					$id_caja = $stmt->fetchColumn();
				}
				else
				{
					$sql     = "select id_caja from raspas_bodega where nro_caja = $nro_caja";
					$stmt    = $db->query($sql);
					$id_caja = $stmt->fetchColumn();
				}

			}

			//FIN inserta o actualiza en la tabla raspas_bodega	

			foreach($arreglo_det as $row)
			{
				$nro_paquete = $row["nro_paquete"];
				$exito   = '0';
				
				$sql = "with updated_rows as (update raspas_paquetes set  tipo_paquete = $cboMaeraspas,
																	      vlr_raspa = $vlr_raspa,
																	      usr_ult_modif = '$id_usu'
									where nro_paquete = '$nro_paquete'
									and id_caja = $id_caja
									returning '1' as exito)
									select exito
									from updated_rows
						";
						
				$stmt= $db->query($sql);						
						
				$exito = $stmt->fetchColumn();	

				if ($exito == '0')				
				{				
					$sql =  "INSERT INTO raspas_paquetes
								(nro_paquete,vlr_raspa,tipo_paquete,id_caja,usr_ult_modif)
							VALUES
								('$nro_paquete',$vlr_raspa,$cboMaeraspas,$id_caja,'$id_usu')";
					
					$db->exec($sql);
					
					$stmt = $db->query("select currval(pg_get_serial_sequence('raspas_paquetes', 'id_paquete'))");
					$id_paquete = $stmt->fetchColumn();	
				}
				else
				{
					$sql     = "select id_paquete from raspas_paquetes where nro_paquete = '$nro_paquete' and id_caja = $id_caja";
					$stmt    = $db->query($sql);
					$id_paquete = $stmt->fetchColumn();					
				}
				
				//aqui insertamos el rango de raspas en movraspas
				
				$codiac_ini_ciclo = substr($row["codraspa_ini"],-2);
				$codiac_fin_ciclo = substr($row["codraspa_fin"],-2);
				
				$codiac_pedazo = substr($row["codraspa_ini"],0,16);				
				
				for ($x=$codiac_ini_ciclo;$x<=$codiac_fin_ciclo; $x++)
				{
					$codraspa = $codiac_pedazo.str_pad($x,2,'0',STR_PAD_LEFT);	

					if (strlen($codraspa) == 18)
					{

						$exito   = '0';
						
						$sql = "with updated_rows as (update movraspas set  id_paquete = $id_paquete,
																			estado = 'RESERVADO',
																			cod_pto = $cod_pto,
																			cod_pto_desp = $cod_pto,
																			fec_despacho = 'now()',
																			usr_ult_modif = '$id_usu'
									where codraspa = '$codraspa'
									returning '1' as exito)
									select exito
									from updated_rows																		
									";					

						$stmt= $db->query($sql);						
						
						$exito = $stmt->fetchColumn();
						
						if ($exito == '0') // no existe entonces inserta el registro
						{
							$sql = "insert into movraspas (codraspa,id_paquete,estado,cod_pto,cod_pto_desp,id_lotero,usr_ult_modif)
									values ('$codraspa',$id_paquete,'RESERVADO',$cod_pto,$cod_pto,'0','$id_usu')";
								
							$db->exec($sql);
						}
						
						$insertados = $insertados + 1;	
					}
				}
				
				//FIN aqui insertamos el rango de raspas en movraspas								
				
			}

			$db->commit();
		
		}
		catch(PDOException $e)
		{
			$db->rollback(); 
			$error =  $e->getMessage().' - '.$sql;
		}
		
		$reg = array ('error'=>$error);	
		print json_encode($reg);		
	}			
				
	if ($paso == 'grabar_premio')
	{
		$codraspa          = $_POST['codraspa'];	
		$tipo_raspa_premio = $_POST['tipo_raspa_premio'];	
		$vlr_pre           = $_POST['vlr_pre'];	
		$error       = "";
		
		try 
		{		
		
			$db->beginTransaction();

			$sql = "update movraspas
						set tipo_raspa_premio   = '$tipo_raspa_premio',
							vlr_pre             = '$vlr_pre',
							usr_ult_modif = '$id_usu'
					where codraspa = '$codraspa' ";

			$db->exec($sql);

			$db->commit();			
		
		}
		catch(PDOException $e)
		{
			$db->rollback(); 
			$error =  $e->getMessage().' - '.$sql;
		}
		
		$reg = array ('error'=>$error);	
		print json_encode($reg);			
	}
	
	function fn_venta_maxima($fec_venta)
	{
		global $db;
		
		$sql = "select max(id_venta) id_venta 
				from ventas_mae
				where fec_venta::date = '$fec_venta'";

		$stmt  = $db->query($sql);
		$fecha = $stmt->fetchColumn();					
				
		return $fecha;
	}
	
	/*
		//2023-nov-24 Scripts utilizados para el cargue de raspas al sistema
		
		--1. Genera el consecutivo, se toma el campo texto y se hace update a la tabla raspas_proceso
		SELECT DISTINCT caja, paquete,
						DENSE_RANK() OVER (ORDER BY paquete) + 2834 AS id_paquete,
						'update raspas_proceso set id_paquete = '||DENSE_RANK() OVER (ORDER BY paquete) + 2834||
						' where paquete = '||''''||paquete||''';' as texto
		FROM raspas_proceso
		where id_caja in (
		  41
		)
		order by paquete
		--and substr(codraspa,17,2) = '00'

		--ORDER BY paquete;

		--2. Inserta en raspas_paquetes
		insert into raspas_paquetes
		SELECT DISTINCT id_paquete,substr(paquete,7,20) paquete,vlr_raspa,
			   usr_ult_modif,now() AS fec_ult_modif,tipo_raspa,id_caja
		FROM raspas_proceso
		where id_caja IN
		(
		  41
		)
		--order by id_caja,paquete
		--3. Inserta en movraspas

		insert into movraspas (codraspa,id_paquete,cod_pto,estado,cod_pto_desp,usr_ult_modif,id_lotero)
		select codraspa,id_paquete,18 cod_pto,'RESERVADO' estado,18 cod_pto_desp,usr_ult_modif,0
		from raspas_proceso
		where id_caja IN
		(
		 41
		)
	*/
	
?>