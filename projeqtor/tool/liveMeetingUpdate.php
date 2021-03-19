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
$timeForSpeaker=0;
if ( array_key_exists ( 'timeForSpeaker', $_REQUEST )) {
  $timeForSpeaker=explode('|',$_REQUEST['timeForSpeaker']);
}
$idSpeaker=-1;
if ( array_key_exists ( 'idSpeaker', $_REQUEST )) {
  $idSpeaker=explode('|',$_REQUEST['idSpeaker']);
}
$color='';
if ( array_key_exists ( 'color', $_REQUEST )) {
  $color=explode('|',$_REQUEST['color']);
}
$organizator='';
if ( array_key_exists ( 'allOrganizator', $_REQUEST )) {
  $organizator=explode('|',$_REQUEST['allOrganizator']);
}

$noHistory=true;
if ( array_key_exists ( 'noHistory', $_REQUEST )) {
  $noHistory=$_REQUEST['noHistory']==1;
}

$canSpeak='';
if ( array_key_exists ( 'allCanSpeak', $_REQUEST )) {
  $canSpeak=explode('|',$_REQUEST['allCanSpeak']);
}
if (! array_key_exists ( 'idLiveMeeting', $_REQUEST )) {
  throwError ( 'Parameter idLiveMeeting not found in REQUEST' );
}
$idLiveMeeting=$_REQUEST['idLiveMeeting'];
$liveMeeting=new LiveMeeting($idLiveMeeting);
$meeting=new Meeting($liveMeeting->idMeeting);
if(count($meeting->_Assignment)!=0){
  if(count($idSpeaker)!=0 && $idSpeaker[0]!=-1){
    $json=json_decode($liveMeeting->param,true);
    foreach ($idSpeaker as $key=>$line){
      if($line!=""){
        $json[$line]['time']=$timeForSpeaker[$key];
        $json[$line]['color']=$color[$key];
        $json[$line]['organizator']=$organizator[$key];
        $json[$line]['canSpeak']=$canSpeak[$key];
      }
    }
    $liveMeeting->param=json_encode($json);
  }
  if(array_key_exists ( 'liveMeetingTimeOrganizator', $_REQUEST ) && array_key_exists ( 'liveMeetingTime', $_REQUEST )){
    $json=json_decode($liveMeeting->param,true);
    $json['lastTime']=$_REQUEST['liveMeetingTime'];
    $json['lastTimeOrganizator']=$_REQUEST['liveMeetingTimeOrganizator'];
    $liveMeeting->param=json_encode($json);
  }
}
if(isset($_REQUEST['liveMeetingResult']))$liveMeeting->result=$_REQUEST['liveMeetingResult'];
$resSaveLM=$liveMeeting->save();
if (getLastOperationStatus($resSaveLM)=='INVALID') traceLog("liveMeetingUpdate.php => save LiveMeeting:$resSaveLM");

$meeting->result=$liveMeeting->result;
if($noHistory)$meeting->_noHistory=true;
$resSaveM=$meeting->save();
if (getLastOperationStatus($resSaveM)=='INVALID') traceLog("liveMeetingUpdate.php => save Meeting:$resSaveM");
?>