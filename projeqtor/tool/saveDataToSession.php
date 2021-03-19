<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/** ============================================================================
 * Save some information to session (remotely).
 */
require_once "../tool/projeqtor.php";
if (isset($_REQUEST['idData'])) {
  $id=$_REQUEST['idData'];
} else if (isset($_REQUEST['id'])) {
  $id=$_REQUEST['id'];
} else {
  $id=null;
  errorLog("Invalid id : value not set".(($_SERVER['REQUEST_URI'])?' for query='.$_SERVER['REQUEST_URI']:'')); 
}
$ValidSessionIDs = '(param(ConfirmQuit|(Top)?IconSize)|lang|hideMenu'
    .'|browserLocale(DateFormat|DecimalPoint|ThousandSeparator)?|currentLocale'
    .'|defaultProject|disconnect|(switched|multiple)Mode|project(Selector(DisplayMode|ShowIdle)?)?'
    .'|screen(Width|Height)?|showWorkHistory|theme|hideInService'
    .'|isCheckedProjectStructureProject|isCheckedOtherProjectStructureProject|'
    .'|defaultProject|(print|pdf)InNewWindow|accordionPane(Top|Bottom)'
    .'|contentPane(Left(DivWidth|BottomDivHeight)|Top(DetailDivHeight(.*)?|(Portfolio|Resource)?PlanningDivHeight))'
    .'|contentPaneRightDetailDivWidth(.*)|kanban(.*)|privacyNotes(.*)'
    .'|contentPaneBottomLiveMeeting|contentPaneTopLiveMeeting|kanbanname|kanbanresponsible|kanbanstatus|kanbantargetProductVersion)|ckeditorHeight(.*)?'
    .'|skipEmptyDay|globalParameter(.*)|displayByStatusList(.*)'
    .'|newGuiTheme(.*)';
if (preg_match('/^'.$ValidSessionIDs.'$/', trim($id)) != True){
  if (array_key_exists($id, Parameter::getParamtersList('userParameter'))) {
    // OK, it is a user parameter
  } else if (array_key_exists($id, Parameter::getParamtersList('globalParameter'))) {
    // OK, it is a global parameter
  } else {
    errorLog("save Data to session : Invalid id value - [$id]"); // all column names are valid session id values - need to make a full list
  	// TODO (security) : when list is complete and no more error logged, change to traceHack 
  }
}
if ($id=='disconnect') {
  // can retreive (for debigging purpose) in $_REQUEST['origin'] the source of disconnection : disconnect or quit
  if (isset($_REQUEST['cleanCookieHash']) and $_REQUEST['cleanCookieHash']=='true' and getSessionUser()->id ) {
		 $user=new User(getSessionUser()->id);
		 $user->cleanCookieHash();
	}
	if (getSessionUser()->id) Audit::finishSession();
  exit;
}

$value=$_REQUEST['value'];
setSessionValue($id, $value);

if ($id=='browserLocaleDateFormat') {
	//setSessionValue('browserLocaleDateFormatJs', str_replace(array('D','Y'), array('d','y'), $value));
	setSessionValue('browserLocaleDateFormatJs', str_replace(array('D','Y'), array('d','y'), $value));
}
//gautier
 if ($id=='browserLocaleTimeFormat') {
   setSessionValue('browserLocaleTimeFormatJs', $value);
 }

//$userParamArray=getSessionValue('userParamatersArray');
if (sessionValueExists('userParamatersArray')) {
  setSessionTableValue('userParamatersArray', $id, $value);
}

if (isset($_REQUEST['saveUserParam']) && $_REQUEST['saveUserParam']=='true') {
	Parameter::storeUserParameter($id, $value);
}
?>