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
require_once('_securityCheck.php');
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

class ImputationValidation{
	public $id;
	public $idUser;
	public $idProject;

	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id=NULL, $withoutDependentObjects=false) {

	}
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {}
	
	static function drawUserWorkList($idUser, $idTeam, $startDay, $endDay){
	  $showSubmitted = RequestHandler::getValue('showSubmitWork');
	  $showValidated = RequestHandler::getValue('showValidatedWork');
	  $user=getCurrentUserId();
	  $noData = true;
	  $critDraw = "";
	  $result="";
	  $currentDay = date('Y-m-d');
	  $proj = new Project();
	  $listAdmProj = $proj->getAdminitrativeProjectList(true);
	  $userVisbileResourceList = getListForSpecificRights('imputation');
	  if(trim($idUser) != ''){
	    unset($userVisbileResourceList);
	    foreach (getUserVisibleResourcesList(true) as $id=>$name){
	      if($id == $idUser){
	        $userVisbileResourceList[$id]=$name;
	      }
	    }
	    if(trim($idTeam) != ''){
	      $res = new Resource($idUser,true);
	      if($res->idTeam != $idTeam){
	        $noResource=true;
	      }
	    }
	  }
	  if(!isset($noResource)){
  	  if($idUser == '' and trim($idTeam) != ''){
  	    unset($userVisbileResourceList);
  	    foreach (getUserVisibleResourcesList(true) as $id=>$name){
  	      $res = new Resource($id, true);
  	      if($res->idTeam == $idTeam){
  	        $userVisbileResourceList[$id]=$name;
  	      }
  	    }
  	  }
  	  
//   	  $critWhere = "";
//   	  if($showSubmitted != ''){
//   	    $critWhere .= " and submitted=".$showSubmitted;
//   	  }
//   	  if($showValidated != ''){
//   	  	$critWhere .= " and validated=".$showValidated;
//   	  }
	  }
	  
	  //Header
	  $result .='<div id="imputationValidationDiv" align="center" style="margin-top:20px;margin-bottom:20px; overflow-y:auto; width:100%;">';
	  $result .='<table width="98%" style="margin-left:20px;margin-right:20px;border: 1px solid grey;">';
	  $result .='   <tr class="reportHeader">';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:16%;text-align:center;vertical-align:center;">'.i18n('Resource').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colWeek').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:8%;text-align:center;vertical-align:center;">'.i18n('expectedWork').'</td>';
	  $result .='     <td style="width:20%;border: 1px solid grey;border-right: 1px solid white;">';
	  $result .='      <table width="100%"><tr><td colspan="3" style="height:30px;text-align:center;vertical-align:center;">'.i18n('inputWork').'</td></tr>';
	  $result .='      <tr><td style="border-top: 1px solid white;border-right: 1px solid white;width:33%;height:30px;text-align:center;vertical-align:center;">'.i18n('operationalWork').'</td>';
	  $result .='      <td style="border-top: 1px solid white;border-right: 1px solid white;width:33%;height:30px;text-align:center;vertical-align:center;">'.i18n('administrativeWork').'</td>';
	  $result .='      <td style="border-top: 1px solid white;border-right: 1px solid white;width:33%;height:30px;text-align:center;vertical-align:center;">'.i18n('sum').'</td></tr></table>';
	  $result .='     </td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:20%;text-align:center;vertical-align:center;">'.i18n('ImputationSubmit').'</td>';
	  $result .='     <td colspan="2" style="border: 1px solid grey;height:60px;width:26%;text-align:center;vertical-align:center;">';
      $result .='       <table width="100%"><tr><td width="62%">'.i18n('menuImputationValidation').'</td>';
      $result .='       <td width="30%">';
      $result .='       <span class="mediumTextButton" id="buttonValidationAll" style="width:98% !important;" type="button" dojoType="dijit.form.Button" showlabel="true">'.i18n('validateWorkPeriod')
            . '         <script type="dojo/method" event="onClick" >'
        		. '           validateAllSelection();'
    				. '         </script>'
  				  . '       </span></td>';
	  $result.='        <td width="8%" style="padding-left:5px;padding-right:5px;">
	                     <div title="'.i18n('selectionAll').'" dojoType="dijit.form.CheckBox" type="checkbox" 
	                     class="whiteCheck" id="selectAll" name="selectAll" onChange="imputationValidationSelection()"></div>
                      </td></table>
                    </td>';
	  $result .='   </tr>';
	  if(!isset($noResource)){
// 	  $weekArray = array();
// 	  $weekList = '';
// 	  if($startWeek !='' and $endWeek !=''){
//   	  while ($startWeek<=$endWeek){
//   	    $startWeek=addDaysToDate($startWeek, 1);
//   	    $weekArray[$startWeek]="'".date('YW', strtotime($startWeek))."'";
//   	  }
//   	  $weekArray = array_flip($weekArray);
//   	  $weekList = transformListIntoInClause($weekArray);
// 	  }
    if (!trim($startDay)) {
      $h=new History();
      $minDay=$h->getMinValueFromCriteria('operationDate', null, "1=1", true);
      if ($minDay) $startDay=substr($minDay,0,10);
      else $startDay="2015-01-01";
    }
    $startWeek=($startDay)?date('YW', strtotime($startDay)):'';
    $endWeek=($endDay)?date('YW', strtotime($endDay)):'';
	  $idCheckBox = 0;
	  foreach ($userVisbileResourceList as $idResource=>$name){
	  	$periodValue = new WorkPeriod();
	  	$where = "idResource=".$idResource;
	  	//if ($critWhere) $where .= $critWhere;
	  	if ($startWeek){
	  	 	$where .= " and periodValue >= '".$startWeek."'";
	  	} if ($endWeek) {
	  	  $where .= " and periodValue <= '".$endWeek."'";
	  	}
	  	$where .= " Order by periodValue";
	  	$periodValueList = $periodValue->getSqlElementsFromCriteria(null,null,$where);
	  	$periodValueListOutOfScope=array();
	  	//if( ! $periodValueList)continue;
	  	$periodValueListSorted=array();
	  	foreach ($periodValueList as $week) {
	  	  if ($showSubmitted==='1' and $week->submitted!=1) $periodValueListOutOfScope[$week->periodValue]=$week;
	  	  else if ($showSubmitted==='0' and $week->submitted!=0) $periodValueListOutOfScope[$week->periodValue]=$week;
	  	  else if ($showValidated==='1' and $week->validated!=1) $periodValueListOutOfScope[$week->periodValue]=$week;
	  	  else if ($showValidated==='0' and $week->validated!=0) $periodValueListOutOfScope[$week->periodValue]=$week;
	  	  else $periodValueListSorted[$week->periodValue]=$week;
	  	}
	  	if ($showSubmitted!='1' and  $showValidated!='1') {
	  	  $lastDayToCheck=($endDay)?$endDay:date('Y-m-d');
	  	  $testDay=$startDay;
	  	  $res=new Resource($idResource);
	  	  if ($res->idle) { 
	  	    if ($res->endDate) {
	  	      if ($res->endDate<$lastDayToCheck) {
	  	        $lastDayToCheck=$res->endDate;
	  	      }
	  	    } else {
	  	      $lastDayToCheck='1970-01-01';
	  	    }
	  	  }
	  	  if ($res->startDate and $res->startDate>$testDay) { 
	  	    $testDay=$res->startDate;
	  	  } else {
	  	    if (count($periodValueList)>0) {
	  	      $first=reset($periodValueList);
	  	      $dayOfFirst=date('Y-m-d', firstDayofWeek(substr($first->periodValue, 4, 2),substr($first->periodValue, 0, 4)));
	  	      if ($dayOfFirst>$testDay) {
	  	        $testDay=$dayOfFirst;
	  	      }
	  	    } else {
	  	      $aff=new Affectation();
	  	      $cptAff=$aff->countSqlElementsFromCriteria(array('idResource'=>$idResource));
	  	      if ($cptAff==0) {
	  	        $lastDayToCheck='1970-01-01';
	  	      }
	  	    }
	  	  }
	  	  
	  	  while ($testDay<=$lastDayToCheck) {
  	  	  $testWeek=getWeekNumberFromDate($testDay);
  	  	  if (! isset($periodValueListSorted[$testWeek]) and ! isset($periodValueListOutOfScope[$testWeek])) {
  	  	    $wp=new WorkPeriod();
  	  	    $wp->idResource=$idResource;
  	  	    $wp->periodRange='week';
  	  	    $wp->periodValue=$testWeek;
  	  	    $wp->submitted=0;
  	  	    $wp->validated=0;
  	  	    $periodValueListSorted[$testWeek]=$wp;
  	  	  }
  	  	  $testDay=addDaysToDate($testDay, 7);
	  	  }  
	  	}
	  	ksort($periodValueListSorted);
	  	$res = new Resource($idResource,true);
	  	$idCalendar = $res->idCalendarDefinition;
	  	$countWeek = 0;
	  	foreach ($periodValueListSorted as $week){
	  	  $idCheckBox++;
	  	  $uniqueId=($week->id)?$week->id:$week->periodValue."_".$week->idResource;
	  	  $noData = false;
  			$firstDay = date('Y-m-d', firstDayofWeek(substr($week->periodValue, 4, 2),substr($week->periodValue, 0, 4)));
  			$lastDay = lastDayofWeek(substr($week->periodValue, 4, 2),substr($week->periodValue, 0, 4));
  			
  			$firstWeekDay = $firstDay;
  			$lastWeekDay = $lastDay;
  			$expected = 0;
  			$weekDayArray =array();
  			while ($firstWeekDay<=$lastWeekDay){
  			  if(isOpenDay($firstWeekDay, $idCalendar)){
  			    $expected += round($res->getCapacityPeriod($firstWeekDay),2);
  			    $weekDayArray[$week->idResource][$week->periodValue][$firstWeekDay]=round($res->getCapacityPeriod($firstWeekDay),2);
  			  }
  				$firstWeekDay=addDaysToDate($firstWeekDay, 1);
  			}
  			$work = new Work();
  			$crit = array('idResource'=>$idResource, 'week'=>$week->periodValue);
  			$critWorkList = $work->getSqlElementsFromCriteria($crit);
  			$inputWork = 0;
  			$inputAdm = 0;
  			$outCapacity = false;
  			
  			if($critWorkList and isset($weekDayArray[$res->id][$week->periodValue])){
  			  foreach ($weekDayArray[$res->id][$week->periodValue] as $workDay=>$capacityValue){
  			    $workByDay = 0;
  			    foreach ($critWorkList as $critWork){
  			      if($critWork->workDate == $workDay){
  			    		$workByDay += $critWork->work;
    			    	if (isset($listAdmProj[$critWork->idProject])) {
    			    		$inputAdm += $critWork->work;
    			    	}else{
    			    		$inputWork += $critWork->work;
    			    	}
  			      }
  			    }
  			    if($workByDay != $capacityValue){
  			    	$outCapacity = true;
  			    }
  			  }
  			}
  			$inputTotal = $inputWork + $inputAdm;
			  $expected = Work::displayImputationWithUnit($expected);
				$inputWork = Work::displayImputationWithUnit($inputWork);
				$inputAdm = Work::displayImputationWithUnit($inputAdm);
  			$inputTotal = Work::displayImputationWithUnit($inputTotal);
  			$backgroundColor = "background-color:#a3d179;";
  			if($outCapacity){
  			  if($inputTotal == $expected){
  			    $backgroundColor = "background-color:#ffb366;";
  			  }else{
  			    $backgroundColor = "background-color:#ff7777;";
  			  }
  			}else{
  			  if($inputTotal != $expected){
  			  	$backgroundColor = "background-color:#ff7777;";
  			  }
  			}
  			$weekValue = substr($week->periodValue, 0, 4).'-'.substr($week->periodValue, 4, 2);
  			$goto="showWait();saveDataToSession('userName',$idResource,false, function() {
  			       saveDataToSession('yearSpinner',".intval(substr($week->periodValue, 0, 4)).",false, function() {
		           saveDataToSession('weekSpinner',".intval(substr($week->periodValue, 4, 2)).",false, function() {
		           saveDataToSession('dateSelector','$firstDay',false, function() {
	             loadContent('../view/imputationMain.php','centerDiv');}); }); }); });";
  			
  			//List body
				$result .='   <tr>';
  			if($countWeek == 0){
  				$result .='     <td rowSpan="'.count($periodValueListSorted).'" style="vertical-align:top;padding-top:5px;border: 1px solid grey;height:30px;width:16%;text-align:left;vertical-align:center;background:white;">';
  				$result .='     <table style="width:100%;">';
  				$result .='       <tr><td width="40%">'.formatUserThumb($idResource, $name, null, 22, 'right').'</td>';
  				$result .='       <td width="60%" float="left">&nbsp'.$name.'</td></tr>';
  				$result .='     </table></td>';
  			}else{
  				//$result .='     <td style="border-left: 1px solid grey;border-right: 1px solid grey;height:30px;width:16%;background-color:transparent;"></td>';
  			}
  			$displayWeek=$weekValue.'&nbsp;<span style="font-size:80%;font-style:italic;">('.htmlFormatDate($firstDay).' - '.htmlFormatDate($lastDay).')</span>';
  			$result .='     <td onClick="'.$goto.'" style="cursor:pointer;border: 1px solid grey;height:30px;width:10%;text-align:center;vertical-align:center;background:white;">'.$displayWeek.'</td>';
  			$result .='     <td onClick="'.$goto.'" style="cursor:pointer;border: 1px solid grey;height:30px;width:8%;text-align:center;vertical-align:center;background:white;">'.$expected.'</td>';
  			$result .='     <td onClick="'.$goto.'" style="cursor:pointer;width:23%;border: 1px solid grey;">';
  			$result .='      <table style="width:100%;height:35px">';
  			$result .='        <tr><td style="background:white;border-right: 1px solid grey;width:33%;height:30px;text-align:center;vertical-align:center;">'.$inputWork.'</td>';
  			$result .='        <td style="background:white;border-right: 1px solid grey;width:33%;height:30px;text-align:center;vertical-align:center;">'.$inputAdm.'</td>';
  			$result .='        <td style="background:white;'.$backgroundColor.'width:33%;height:30px;text-align:center;vertical-align:center;">'.$inputTotal.'</td></tr>';
  			$result .='      </table>';
  			$result .='     </td>';
  			$result .='   <td style="border: 1px solid grey;height:30px;width:20%;text-align:left;vertical-align:center;background:white;">';
  			$result .='   <div id="submittedDiv'.$uniqueId.'" name="submittedDiv'.$uniqueId.'" width="100%" dojoType="dijit.layout.ContentPane" region="center">';
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
  				$result .='     </td></tr></table></div></td>';
  			}else{
  				$result .='     <table width="100%"><tr><td style="height:30px;">'.formatIcon('Unsubmitted', 32, i18n('unsubmittedWork'),false,true).'</td>';
  				$result .='     <td style="height:30px;width:90%;">'.i18n('unsubmittedWork').'</td></tr></table></div></td>';
  			}
  			$result .='   <td style="border: 1px solid grey;height:30px;width:26%;text-align:left;vertical-align:center;background:white;">';
  			$result .='   <div id="validatedDiv'.$uniqueId.'" name="validatedDiv'.$uniqueId.'" width="100%" dojoType="dijit.layout.ContentPane" region="center">';
  			$result .='     <table width="100%"><tr>';
  			if($week->validated){
  				$locker = SqlList::getNameFromId('Affectable', $week->idLocker);
  				$result .='     <td style="height:30px;">'.formatIcon('Submitted', 32, i18n('validatedLineWorkPeriod', array($locker, htmlFormatDate($week->validatedDate))),false,true).'</td>';
  				$result .='     <td style="width:73%;padding-left:5px;height:30px;">'.i18n('validatedLineWorkPeriod', array($locker, htmlFormatDate($week->validatedDate))).'</td>';
  				$result .='     <td style="width:27%;padding-right:8px;">';
  				$result .='      <span id="buttonCancelValidation'.$week->id.'" style="width:100% !important; " class="mediumTextButton" type="button" dojoType="dijit.form.Button" showlabel="true">'.i18n('buttonCancel')
  				        . '       <script type="dojo/method" event="onClick" >'
  				        . '        saveDataToSession("idCheckBox", '.$idCheckBox.', false);' 
				          . '        saveImputationValidation('.$week->id.', "cancelValidation");'   
  								. '       </script>'
									. '     </span>';
  			} else {
  			  $result .='     <td style="height:30px;">'.formatIcon('Unsubmitted', 32, i18n('unvalidatedWorkPeriod'),false,true).'</td>';
  				$result .='     <td style="width:73%;padding-left:5px;height:30px;">'.i18n('unvalidatedWorkPeriod').'</td>';
  				$result .='     <td style="width:27%;padding-right:8px;">';
  				$result .='      <span class="mediumTextButton" id="buttonValidation'.$uniqueId.'" style="width:100% !important;" type="button" dojoType="dijit.form.Button" showlabel="true">'.i18n('validateWorkPeriod')
  				        . '       <script type="dojo/method" event="onClick" >'
				          . '        saveImputationValidation("'.$uniqueId.'", "validateWork");'
		              . '        saveDataToSession("idCheckBox", "'.$uniqueId.'", false);' 
  								. '       </script>'
									. '     </span>';
//   			} else {
//   			  $result .='     <td style="height:30px;">'.formatIcon('Unsubmitted', 32, i18n('unvalidatedWorkPeriod')).'</td>';
//   			  $result .='     <td style="width:73%;padding-left:5px;height:30px;">'.i18n('unvalidatedWorkPeriod').'</td>';
//   			  $result .='     <td style="width:27%;padding-right:8px;height:30px;">';
//   			  $result .='      ';
  			}
  			$result .='     </td>';
  			$result .='     <td style="padding-right:5px;"><div class="validCheckBox" type="checkbox" dojoType="dijit.form.CheckBox" '.(($week->id)?'name="validCheckBox'.$idCheckBox.'"':'').' id="validCheckBox'.$idCheckBox.'"></div></td>';
  			$result .='     </tr></table>';
  			$result .='     <input type="hidden" id="validatedLine'.$idCheckBox.'" name="'.$uniqueId.'" value="'.$week->validated.'"/>';
  			$result .='  </div></td>';
  			$result .='   </tr>';
  			
  			$countWeek++;
	  	}
	  }
	  $result .='<input type="hidden" id="countLine" name="countLine" value="'.$idCheckBox.'"/>';
	  }
	  if($noData==true or isset($noResource)){
	    noData :
	  	$result .='<tr><td colspan="6">';
	  	$result .='<div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noDataFound').'</div>';
	  	$result .='</td></tr>';
	  }
    $result .='</table>';
	  $result .='</div>';
	  echo $result;
	}
}