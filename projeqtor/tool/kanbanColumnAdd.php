<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once "../tool/projeqtor.php";

if (! array_key_exists('name',$_REQUEST)) {
  throwError('Parameter name not found in REQUEST');
}
$name=$_REQUEST['name'];

if (! array_key_exists('type',$_REQUEST)) {
  throwError('Parameter type not found in REQUEST');
}
$from=$_REQUEST['type'];

if (! array_key_exists('idKanban',$_REQUEST)) {
  throwError('Parameter idKanban not found in REQUEST');
}
$idKanban=$_REQUEST['idKanban'];

if (! array_key_exists('idFrom',$_REQUEST)) {
  throwError('Parameter idFrom not found in REQUEST');
}
$idFrom=$_REQUEST['idFrom'];

$kanban=new Kanban($idKanban);
$json=json_decode($kanban->param,true);
if($idFrom==-1){
  $json['column'][]["from"]=$from;
  end($json['column']);         // move the internal pointer to the end of the array
  $key = key($json['column']);
  $json['column'][$key]["name"]=$name;
}else{
  foreach ($json['column'] as $key=>$line){
    if($line['from']==$idFrom)$json['column'][$key]["name"]=$name;
  }
}
$kanban->param=json_encode($json);
$kanban->save();

?>