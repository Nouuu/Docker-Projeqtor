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

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
require_once "../tool/liveMeetingFunc.php";
if (! array_key_exists ( 'idMeeting', $_REQUEST )) {
  throwError ( 'Parameter idMeeting not found in REQUEST' );
}
$idMeeting = $_REQUEST ['idMeeting'];

$idObj = -1;
if ( array_key_exists ( 'idObj', $_REQUEST )) {
  $idObj = $_REQUEST ['idObj'];
}

$needDelete=false;
if($idObj!=-1 && strpos($idObj, "-")){
  $needDelete=true;
  $idObj=explode("-", $idObj);
  $idObj=$idObj[0];
}

$typeObj=-1;
if ( array_key_exists ( 'typeObj', $_REQUEST )) {
  $typeObj = $_REQUEST ['typeObj'];
}

$meeting = new Meeting ( $idMeeting );
if (! $meeting->id) {
  throwError ( 'Parameter idMeeting not found in DBBASE' );
}
$find=false;
if($typeObj!=-1)foreach ($meeting->_Link as $line){
  if($line->ref1Type==$typeObj && $line->ref1Id==$idObj){
    $find=true;
    if($needDelete)$line->delete();
  }
  if($line->ref2Type==$typeObj && $line->ref2Id==$idObj){
    $find=true;
    if($needDelete)$line->delete();
  }
}

if(!$find && !$needDelete && $idObj>0 && $typeObj!=-1){
  $lnk=new Link();
  $lnk->ref1Id=$idObj;
  $lnk->ref1Type=$typeObj;
  $lnk->ref2Id=$idMeeting;
  $lnk->ref2Type="Meeting";
  $lnk->creationDate=date("Y-m-d");
  $result=$lnk->save();
}
$meeting = new Meeting ( $idMeeting );
generateBottom($meeting);
?>