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

$param = RequestHandler::getValue('critField');
$obj = new $param;

$JsonData = '';
$JsonData.= '{"identifier":"id","label": "name"' ;
$JsonData.= ',"items":[';

if (property_exists($obj, 'idStatus')) {
	$JsonData .= '{"id":"Status" , "name":"'. i18n("colIdStatus") .'"},';
}
if (property_exists($obj, 'idActivity')) {
  $JsonData .= '{"id":"Activity" , "name":"'. i18n("colPlanningActivity") .'"},';
}
if (property_exists($obj, 'idTargetProductVersion')){
  $JsonData .= '{"id":"TargetProductVersion" , "name":"'. i18n("colIdTargetProductVersion") .'"}';
}
$JsonData.= ' ]}';
echo  $JsonData;
?>