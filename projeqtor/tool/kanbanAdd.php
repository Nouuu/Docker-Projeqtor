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

if (! array_key_exists('kanbanReffList',$_REQUEST)) {
  throwError('Parameter kanbanReffList not found in REQUEST');
}
$typeData=$_REQUEST['kanbanReffList'];

if (! array_key_exists('type',$_REQUEST)) {
  throwError('Parameter type not found in REQUEST');
}
$type=$_REQUEST['type'];

if (! array_key_exists('shared',$_REQUEST)) {
  throwError('Parameter shared not found in REQUEST');
}
$shared=$_REQUEST['shared'];
$kanban=new Kanban();
$kanban->isShared=$shared!='false' ? 1 : 0;
$kanban->name=$name;
$kanban->type=$type;
$kanban->idUser=getSessionUser()->id;

if($type=='Status'){
  $objStatus = new Status();
  $listStatus=$objStatus->getSqlElementsFromCriteria(null,false);
  $listStatusWithOrder=array();
  foreach ($listStatus as $line){
    $listStatusWithOrder[$line->sortOrder.'-'.$line->id]=$line;
  }
  ksort($listStatusWithOrder);
  foreach ($listStatusWithOrder as $line){
    $kanban->param='{"column":[{"from":"'.$line->id.'","name":"Backlog","cantDelete":true}],"typeData":"'.$typeData.'"}';
    break;
  }
}else{
  $kanban->param='{"column":[{"from":"n","name":"'.ucfirst(i18n('undefinedValue')).'","cantDelete":true}],"typeData":"'.$typeData.'"}';
}
$kanban->save();
echo $kanban->id;
?>