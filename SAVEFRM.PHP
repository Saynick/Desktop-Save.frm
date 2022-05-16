<?php

include "DataBase/conexion.php";
require '../vendor/autoload.php';

date_default_timezone_set('America/Mexico_City');
session_start();

$user = $_SESSION['myusername'];
if (!isset($_SESSION['myusername'])) {
  header("location:index.php");
}//cierre de if

$member = $_POST["member"];
$empresa = $_POST["empresa"];
$nomb = $_POST["nomb"];
$nombf = $_POST["nombf"];
$regimenf = $_POST["regimen"];
$comemp = $_POST["comemp"];
$periodo = $_POST["periodo"];
$rfc1 = $_POST["rfc1"];
$rfc2 = $_POST["rfc2"];
$rfc3 = $_POST["rfc3"];
$dom1 = $_POST["dom1"];
$dom2 = $_POST["dom2"];
$dom3 = $_POST["dom3"];
$dom4 = $_POST["dom4"];
$cp = $_POST["dom5"];
$dom5 = $_POST["dom5"];
$tel1 = $_POST["tel1"];
$tel2 = $_POST["tel2"];
$emai = $_POST["emai"];
$emai2 = $_POST["emai2"];
$emai3 = $_POST["emai3"];
$formapago = $_POST["c_FormaPago"];
$c_MetodoPago = $_POST["c_MetodoPago"];
$resp = $_POST["resp"];
$tres = $_POST["tres"];
$cres = $_POST["cres"];
$vend = $_POST["vendedor"];
$act = $_POST["chkActiva"];
$anticipo = $_POST["anticipo"];


if ($act == "") {
  $act = 0;
} else {
  $act = 1;
}


$cond = $_POST["cond"];
$credito = $_POST["credito"];
if ($credito == "") {
  $credito = 0;
} elseif ($credito != "") {
  $credito = 1;
}
$modo = $_POST["modo"];
$uv = 0;
$conductor = 0;
if ($modo == 3) {
  $uv = 1 && $conductor = 1;
}
if ($modo == 4) {
  $uv = 1 && $conductor = 1;
}
if ($modo == 6) {
  $uv = 1 && $conductor = 1;
}

//echo " modo:".$modo. "uv:".$uv. "conductor:".$conductor; //no sabemos para que lo ocupa

$query_ultimo_id = mysql_query("SELECT MAX(idGroup) FROM `Group`");
$results_query_ultimo_id = mysql_fetch_array($query_ultimo_id);
$grup = $results_query_ultimo_id['MAX(idGroup)'] + 1;
$rfc4 = $rfc1 . $rfc2 . $rfc3;
$pin = rand(1000, 9999);
$vend = $_POST["vendedor"];

$sql = "INSERT INTO `Group` (`idGroup`,`Name`,`NameF`,`RegimenF`,`ComEmp`,`TaxID`,`Address1`,`Address2`,`Address3`,`Address4`,`Address5`,`Telephone1`,`Telephone2`,`Email`,`Email2`,`Email3`,`c_FormaPago`,`CreationDate`,`ResponsibleName`,`ResponsibleTelephone`,`ResponsibleEmail`,`Balance`,`vendedor`,`Status`,`isNeedConductorId`,`user_veh`,`credito`,`empresa_modo`,`anticipo`,`id_periodo`) VALUES ('$grup','$nomb','$nombf','$regimenf','$comemp','$rfc4','$dom1','$dom2','$dom3','$dom4','$dom5','$tel1','$tel2','$emai','$emai2','$emai3','$formapago',NOW(),'$resp','$tres','$cres','0.00','$vend','$act','$conductor','$uv','$credito','$modo','$anticipo','$periodo')";
$respuesta_Group = mysql_query($sql, $conexion);

$mysql_insert_id = '';

if (!$respuesta_Group) {
  die("<br><p class='listaSF' align='left'> No se pudo guardar el registro porque alguno de los datos ingresados es invalido.<br>No es posible generar dos registros con el mismo R.F.C.<br>Asegurese de usar el formato AAAA-MM-DD para ingresar las fechas." . mysql_error() . "</p>");
}else{
  $mysql_insert_id = mysql_insert_id();
}//cierre de else

$tclientes_param = "INSERT INTO gen_cfdi.`tclientes_param` (`idGroup`,`ExentoDesglosaIeps`, `archivos_adjuntos`,`descuento_imp`,`descuento_tipo`,`limite_credito`,`dias_margen`) VALUES ('$mysql_insert_id','$act',0,0,'ninguno',0,0)";
#echo $tclientes_param;
mysql_query($tclientes_param, $conexion) or die("<br><p class='listaSF' align='left'> No se pudo guardar el registro en el tclientes_param. " . mysql_error() . "</p>");

$users_empresas = "INSERT INTO `users_empresas` (`user`,`username`,`password`,`Name`,`seguimiento`,`type`) VALUES ('$grup','$grup',MD5('$pin'),'$nomb','$pin','$modo')";
mysql_query($users_empresas, $conexion) or die("<br><p class='listaSF' align='left'> No se pudo guardar el registro de usuario y contrasena para esta empresa. " . mysql_error() . "</p>");

$vend = $_POST["vendedor"];
$vendedores_h = "INSERT INTO `vendedores_h`  (`id_vendedor`, `grupo`, `fecha_ini`) VALUES ('$vend','$grup',NOW())";
mysql_query($vendedores_h, $conexion) or die("<br><p class='listaSF' align='left'> No se pudo guardar el registro en el historico de vendedores. " . mysql_error() . "</p>");

//esta graba la txn en la tabla txn_user_admin
$txn_user_admin = "INSERT INTO `txn_user_admin` (`tx_type`,`n_group`,`id_vendedor`,`user`) VALUES ('13','$vend','$grup','$user')";
mysql_query($txn_user_admin, $conexion) or die("<br><p class='listaSF' align='left'> No se pudo guardar el registro del log porque alguno de los datos ingresados es invalido. " . mysql_error() . "</p>");
echo "<br><br><br><br><br><p class='listaSF' align='center'> Los datos han sido guardados exitosamente.<br><br> La nueva empresa tiene el numero <strong> " . $grup . " </strong><br>La contrasena se ha enviado al correo electronico del responsable de la empresa</strong></p>";

//Inicia envio de correo via PHPMailer
$tip = 0;
if ($tip == 0) {
  $tip = "Administrador";
} elseif ($tipo == 1) {
  $tip = "Operador";
} elseif ($tip == 2) {
  $tip = "Monitor";
}

$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = 'mail.transfergas.com.mx';
$mail->Port = 465;
$mail->SMTPSecure = 'ssl';
$mail->SMTPAuth = true;
$mail->Username = 'notificaciones1@transfergas.com.mx';
$mail->Password = 'tra@not01';
$mail->setFrom('notificaciones1@transfergas.com.mx', 'Transfer Gas');
$mail->addReplyTo('notificaciones1@transfergas.com.mx', 'Transfer Gas');
$mail->AddAddress($cres);
$mail->AddBCC('alejandro.mendoza@transfergas.com.mx');
$mail->AddBCC('eduardo.lopez@transfergas.com.mx');

$mail->Subject = 'Transfer Gas - Acceso a Plataforma';

$mail->Body = ' <html lang="en"> <meta charset="UTF-8"> <div style="width: 400px; font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
  <div align="center">
  <h1> &iexcl;Bienvenido a Transfer Gas!</h1>
  <p><strong>' . $nomb . '</p>

  <h4>Un nuevo acceso a plataforma ha sido creado exitosamente para:</h4>
  <p>Nombre:<strong> ' . $nomb . '</p>
  <p>Usuario tipo:  ' . $tip . ' </p> <br>
  <p>Accesos: </p>
  <table width="100" align="center" border="0">
  <tr>
  <td align="right">Empresa: </td>
  <td><strong>' . $grup . '</td>
  </tr>
  <tr>
  <td align="right">Usuario: </td>
  <td> <strong>' . $grup . '</td>
  </tr>
  <tr>
  <td align="right" >Contrase&ntilde;a:</td>
  <td> <strong>' . $pin . '</td>
  </tr>
  </table>

  </br>
  <p><a href="https://www.transfergas.com.mx/flotillas/empresas/index_2.php" target="_blank" style ="background-color: #73BC2E; color: white; text-decoration: none; padding: 14px 25px; ">Ingrese a Transfer Gas</a></p> <br>
  </br>
  <p>Si considera que ha recibido este mensaje por error por favor notifique a Transfer Gas al tel: 01-800-TFERGAS (833-7427).</p>
  </div>
  </div></html>';
//Replace the plain text body with one created manually
$mail->AltBody = 'Transfer Gas S.A. de C.V.';
//Attach an image file
//Mel dice no: $mail->addAttachment('images/phpmailer_mini.png');
//send the message, check for errors
if (!$mail->send()) {
  echo "<br><br><p class='listaSF' align='left'>Error en envio de correo electronico: " . $mail->ErrorInfo;
} else {
  echo "<br><p class='listaSF' align='center'>Notificacion de correo electronico enviada exitosamente a: " . $cres . " </a></p>";
  echo '<script>setTimeout(function() { window.location.href = "grupos.php"; }, 10000);</script>';
}
