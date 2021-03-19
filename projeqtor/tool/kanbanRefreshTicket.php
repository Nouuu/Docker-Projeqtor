<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
*
* This file is an add-on to ProjeQtOr, packaged as a plug-in module.
* It is NOT distributed under an open source license.
* It is distributed in a proprietary mode, only to the customer who bought
* corresponding licence.
* The company ProjeQtOr remains owner of all add-ons it delivers.
* Any change to an add-ons without the explicit agreement of the company
* ProjeQtOr is prohibited.
* The diffusion (or any kind if distribution) of an add-on is prohibited.
* Violators will be prosecuted.
*
*** DO NOT REMOVE THIS NOTICE ************************************************/

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
require_once 'kanbanFunction.php';

$idItem = RequestHandler::getId ( 'id' );
$idKanban = RequestHandler::getValue ( 'idKanban' );
$type = RequestHandler::getValue ( 'type' );
$from = RequestHandler::getValue ( 'from' );

$kanB = new Kanban ( $idKanban, true );
$json = $kanB->param;
$type = $kanB->type;

$jsonDecode = json_decode ( $json, true );
$itemClass = $jsonDecode ['typeData'];
$hasVersion=(property_exists($itemClass,'idTargetProductVersion'))?true:false;
$item = new $itemClass ( $idItem );
$line = array ();
$item->targetProductVersion = $kanB->type;
/*
 * foreach($item as $fld=>$val) { $line[strtolower($fld)]=$val; }
 */
$line = ( array ) $item;
$typeName = 'id' . get_class ( $item ) . 'Type';
$line ['name'] = $item->name;
$line ['idtickettype'] = $item->$typeName;
$line ['idstatus'] = $item->idStatus;
$line ['idproject'] = $item->idProject;
// Not for Activity
if (get_class ( $item ) != 'Activity' && get_class ( $item ) != 'Requirement') {
	$line ['idpriority'] = $item->idPriority;
}

$line ['idtargetproductversion'] = ($hasVersion)?$item->idTargetProductVersion:null;

if (property_exists ( $item, 'idActivity' )) {
	$line ['idactivity'] = $item->idActivity;
} else {
	$line ['idactivity'] = null;
}

// if(get_class($item)!= 'Requirement'){}

$line ['description'] = $item->description;
$line ['iduser'] = $item->idUser;

// sortOrder => 300 Status->sortOrder
// Resource->idUser
$line ['targetproductversion'] = $item->targetProductVersion;
// TargetProductVersion -> $type
// var_dump($line['targetproductversion']);

if (property_exists ( $item, 'WorkElement' )) {
	$we = $item->WorkElement;
	$line ['plannedwork'] = $we->plannedWork;
	$line ['realwork'] = $we->realWork;
	$line ['leftwork'] = $we->leftWork;
} else {
	$peName = get_class ( $item ) . 'PlanningElement';
	if (property_exists ( $item, $peName )) {
		$pe = $item->$peName;
		$line ['plannedwork'] = $pe->plannedWork;
		$line ['realwork'] = $pe->realWork;
		$line ['leftwork'] = $pe->leftWork;
	}
}

// $line =
// sortOrder => 300
// name4 => admin
// name5 =>

$add = "";

$from = "";
$mode = "refresh";
kanbanDisplayTicket ( $idItem, $type, $idKanban, $from, $line, $add, $mode );
?>