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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refreshSubmitValidateDiv.php'); 

$idWorkPeriod = getSessionValue('idWorkPeriod');
$buttonAction = getSessionValue('buttonAction');
$idCheckBox = getSessionValue('idCheckBox');

if($buttonAction and $idWorkPeriod){
  $week = WorkPeriod::getWorkPeriod($idWorkPeriod);
  $result = "";
  if($buttonAction == 'validateWork' or $buttonAction == 'cancelValidation'){
  	$result .='     <table width="100%"><tr>';
  	if($week->validated){
  		$locker = SqlList::getNameFromId('Resource', $week->idLocker);
  		$result .='     <td style="height:30px;">'.formatIcon('Submitted', 32, i18n('validatedLineWorkPeriod', array($locker, htmlFormatDate($week->validatedDate))),false,true).'</td>';
  		$result .='     <td style="width:73%;padding-left:5px;height:30px;">'.i18n('validatedLineWorkPeriod', array($locker, htmlFormatDate($week->validatedDate))).'</td>';
  		$result .='     <td style="width:27%;padding-right:8px;">';
  		$result .='      <span id="buttonCancelValidation'.$idWorkPeriod.'" style="width:100% !important;" class="mediumTextButton" type="button" dojoType="dijit.form.Button" showlabel="true">'.i18n('buttonCancel')
  		. '       <script type="dojo/method" event="onClick" >'
  				. '        saveImputationValidation("'.$idWorkPeriod.'", "cancelValidation");'
  				    . '        saveDataToSession("idCheckBox", '.$idCheckBox.', false);' 
  				    
  						. '       </script>'
  								. '     </span>';
  	}else{
  		$result .='     <td style="height:30px;">'.formatIcon('Unsubmitted', 32, i18n('unvalidatedWorkPeriod'),false,true).'</td>';
  		$result .='     <td style="width:73%;padding-left:5px;height:30px;">'.i18n('unvalidatedWorkPeriod').'</td>';
  		$result .='     <td style="width:27%;padding-right:8px;">';
  		$result .='      <span class="mediumTextButton" id="buttonValidation'.$idWorkPeriod.'" style="width:100% !important; " type="button" dojoType="dijit.form.Button" showlabel="true">'.i18n('validateWorkPeriod')
  		. '       <script type="dojo/method" event="onClick" >'
  				. '        saveImputationValidation("'.$idWorkPeriod.'", "validateWork");'
  				    . '        saveDataToSession("idCheckBox", '.$idCheckBox.', false);' 
  						. '       </script>'
  								. '     </span>';
  	}
  	$result .='     </td>';
  	$result .='     <td style="padding-right:5px;"><div class="validCheckBox" type="checkbox" dojoType="dijit.form.CheckBox" name="validCheckBox'.$idCheckBox.'" id="validCheckBox'.$idCheckBox.'"></div></td>';
		$result .='     </tr></table>';
		$result .='     <input type="hidden" id="validatedLine'.$idCheckBox.'" name="'.$week->id.'" value="'.$week->validated.'"/>';
  }else{
  	if($week->submitted){
  		$result .='     <table width="100%"><tr><td style="height:30px;">'.formatIcon('Submitted', 32, i18n('submittedWork', array($name, htmlFormatDate($week->submittedDate))),false,true).'</td>';
  		$result .='     <td style="width:73%;padding-left:5px;height:30px;">'.i18n('submittedWork', array($name, htmlFormatDate($week->submittedDate))).'</td>';
  		$result .='     <td style="width:27%;height:30px;padding-right:8px;">';
  		$result .='      <span id="buttonCancel'.$week->id.'" style="width:100px; " type="button" dojoType="dijit.form.Button" showlabel="true">'.i18n('buttonCancel')
  		. '       <script type="dojo/method" event="onClick" >'
  				. '        saveImputationValidation('.$week->id.', "cancelSubmit");'
  				    . '        saveDataToSession("idCheckBox", '.$idCheckBox.', false);' 
  						. '       </script>'
  								. '     </span>';
  		$result .='     </td></tr></table>';
  	}else{
  		$result .='     <table width="100%"><tr><td style="height:30px;">'.formatIcon('Unsubmitted', 32, i18n('unsubmittedWork'),false,true).'</td>';
  		$result .='     <td style="height:30px;width:90%;">'.i18n('unsubmittedWork').'&nbsp'.htmlFormatDate($week->submittedDate).'</td></tr></table>';
  	}
  }
  echo $result;
}
?>
