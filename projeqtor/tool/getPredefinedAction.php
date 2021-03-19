<?php
/*
 * @author: qCazelles
 */

require_once "../tool/projeqtor.php";

if (! array_key_exists('idPA', $_REQUEST)) {
	throwError('idPA parameter not found in $_REQUEST');
}
$idPA = $_REQUEST['idPA'];

if (empty($idPA)) {
	exit;
}

$obj = new PredefinedAction($idPA);

echo json_encode($obj);