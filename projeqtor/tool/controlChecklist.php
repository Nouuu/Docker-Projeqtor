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

/** ===========================================================================
 * Save a checklistdefinition line : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

$checklistDefinitionId=trim($_REQUEST['checklistDefinitionId']); // validated to be numeric value in SqlElement base constructor

$checklistObjectClass=$_REQUEST['checklistObjectClass'];
Security::checkValidClass($checklistObjectClass);

$checklistObjectId=trim($_REQUEST['checklistObjectId']);
Security::checkValidId($checklistObjectId);

$checklistDefinition=new ChecklistDefinition($checklistDefinitionId);
$done=(RequestHandler::isCodeSet('done'))?RequestHandler::getValue('done'):'false';
$statusLine='';
foreach($checklistDefinition->_ChecklistDefinitionLine as $line) {
  $required=(RequestHandler::isCodeSet('isRequired_'.$line->id))?RequestHandler::getValue('isRequired_'.$line->id):0;
	$checkedCpt=0;
	for ($i=1; $i<=5; $i++) {
		$checkName="check_".$line->id."_".$i;
		$valueName="value0".$i;
		if (isset($_REQUEST[$checkName])) {
			$checkedCpt++;
		} 
	}
	if($checkedCpt==0 and $required==1 and $done=='on'){
	  $statusObj= new Status(RequestHandler::getValue('idStatus'));
      $result.='<br/>' . i18n('errorRequiredLine',array($line->name,$statusObj->name));
	  $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
	}
}


?>