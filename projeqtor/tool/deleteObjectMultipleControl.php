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
 * Delete multiple objects
 */

require_once "../tool/projeqtor.php";

// Get the object class from request
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('objectClass parameter not found in REQUEST');
}
$className=$_REQUEST['objectClass'];
Security::checkValidClass($className);

if (! array_key_exists('selection',$_REQUEST)) {
  throwError('selection parameter not found in REQUEST');
}
$selection=trim($_REQUEST['selection']);
if ($selection=='NaN') $selection='';
$selectList=explode(';',$selection);

if (! trim($selection) or count($selectList)==0) {
	 $summary='<div class=\'messageWARNING\' >'.i18n('messageNoData',array(i18n($className)),ENT_QUOTES,'UTF-8').'</div >';
	 echo '<input type="hidden" id="summaryResult" value="'.$summary.'" />'.$summary;
	 exit;
}
$cptOk=0;
$cptError=0;
$cptWarning=0;
$cptNoChange=0;
$first=true;
foreach ($selectList as $id) {
	if (!trim($id)) { continue;}
	Security::checkValidId($id);

	$item=new $className($id);
	if (property_exists($item, 'locked') and $item->locked) {
		Sql::rollbackTransaction();
    $cptWarning++;
    echo '<span class="messageWARNING" >' .i18n($className). " #" . htmlEncode($item->id) . ' '.i18n('colLocked'). '</span>';
		continue;
	}
  $control=$item->deleteControl();
	if ( ($control=='OK' or strpos($control,'id="confirmControl" value="delete"')>0 )
	and property_exists($className, $className.'PlanningElement')) {
	  $pe=$className.'PlanningElement';
	  $controlPe=$item->$pe->deleteControl();
	  if ($controlPe!='OK') {
	    $control=$controlPe;
	  }
	}
	
	if ($control!="OK") {
	  // errors on control => don't save, display error message
	  if ( strpos($control,'id="confirmControl" value="delete"')>0 ) {
	    $returnValue='<b>' . i18n('messageConfirmationNeeded') . '</b><br/>' . $control;
	    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="CONFIRM" />';
	    if($first){
	      echo '<div style="height:250px;overflow:auto;border:1px solid #999;background-color:#f0f0f0;padding:10px;">';
	      echo str_replace(i18n("confirmControlDelete"), i18n("confirmControlDelete").":<br/><br/><b>".$item->name."</b>",$returnValue); 
	    }else{
	      echo str_replace(i18n("confirmControlDelete"), "<b>".$item->name."</b>", str_replace("<b>".i18n("messageConfirmationNeeded")."</b>", "", $returnValue));
	    }
	    $first=false;
	  }
	}
}	    
if($first){
  echo '</div>';
}
?>