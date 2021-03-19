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

class Absence{
  public $id;
  public $name;
  public $idProject;
  public $idActivity;
  public static $_colorTab=array('#f08080','#87ceeb','#84e184','#ffc266','#ffff66',  
                                 '#ff66ff','#99e6e6','#A0ffA0','#ffeecc','#c68c53');  
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
  
  static function drawActivityDiv($userID, $currentYear){
    global $print;
    $keyDownEventScript=NumberFormatter52::getKeyDownEvent();
    // Insert new lines for admin projects
    Assignment::insertAdministrativeLines($userID);
    //Get administrative project list 
    $proj = new Project();
    $act = new Activity();
    $actTable=$act->getDatabaseTableName();
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $where = "idProject in " . Project::getAdminitrativeProjectList(false, false);    
    $where .= " and (idle = 0 or exists (select 'x' from $workTable where idUser=".Sql::fmtId($userID)." and $workTable.refType='Activity' and $workTable.refId=$actTable.id and $workTable.year='$currentYear' )) ";
    $countExiting=$act->countSqlElementsFromCriteria(null,$where);
    $user=getSessionUser();
    $accessRightRead=securityGetAccessRight('menuActivity', 'read');
    if ($user->id!=$userID and $accessRightRead!='ALL') {
      $profile=$user->getProfile(); // Default profile for user
      $listAccesRightsForImputation=$user->getAllSpecificRightsForProfiles('imputation');
      $listAllowedProfiles=array(); // List will contain all profiles with visibility to Others imputation
      if (isset($listAccesRightsForImputation['PRO'])) {
        $listAllowedProfiles+=$listAccesRightsForImputation['PRO'];
      }
      if (isset($listAccesRightsForImputation['ALL'])) {
        $listAllowedProfiles+=$listAccesRightsForImputation['ALL'];
      }
      $visibleProjects=array();
      foreach ($user->getSpecificAffectedProfiles() as $prj=>$prf) {
        if (in_array($prf, $listAllowedProfiles)) {
          $visibleProjects[$prj]=$prj;
        }
      }
      //$where .=" and idProject in ".transformListIntoInClause($visibleProjects);
    }
    
    $listAct = $act->getSqlElementsFromCriteria(null,false,$where,"idProject asc, id asc");
    $ass = new Assignment();
    //Variable parameter
    $result="";
    $actName = "";
    $actId = "";
    $actProject = "";
    $projName = "";
    $idProject = "";
    $listAss = "";
    $sltActId = "";
    $idle = "";
    $unitAbs = Parameter::getGlobalParameter('imputationUnit');
    $res = new Resource($userID,true);
    $etp = $res->capacity;
    if($unitAbs != 'days'){
      $max = Parameter::getGlobalParameter('dayTime');
    }else{
      $max = 1;
    }
    $max = round($etp * $max,2);
    $assId = "";
    $idColor = 0;
    $tabColor = array();
    $inputActIdValue = '';
    //Activity Table view
    
    $result .='<div id="activityDiv" align="center" style="margin-top:20px; overflow-y:auto; width:100%;">';
    $result .=' <table align="left" style="margin-left:20px; border: 1px solid grey; width: 50%;">';
    $result .='   <tr>';
    $borderRadius = "";
    if(isNewGui()){
      $borderRadius = "border-radius:0 !important;";
    }
    $result .='     <td class="reportHeader" style=" '.$borderRadius.' border-right: 1px solid grey; height:30px;">'.i18n('colProjectName').'</td>';
    $result .='     <td class="reportHeader" style=" '.$borderRadius.'border-right: 1px solid grey; height:30px; width: 10%;">'.i18n('activityId').'</td>';
    $result .='     <td class="reportHeader" style=" '.$borderRadius.'height:30px;">'.i18n('activityName').'</td>';
    $result .='   </tr>';
    
    $listActId="(";
    foreach ($listAct as $id=>$val){
    	foreach ($listAct[$id] as $id2=>$val2){
    	  if ($id2 == 'id') {
    	    $listActId.= $val2 .',';
    	    $actId = htmlEncode($val2);
    	  }
    	  if($id2 == 'name') {
    	    $actName = htmlEncode($val2);
    	  }
    	  if ($id2 == 'idProject') {
    	    $idProject = $val2;
    	    $projName = htmlEncode(SqlList::getNameFromId('Project',$val2));
    	  }
    	  if ($id2 == 'idle') {
    	  	$idle = $val2;
    	  }
    	}
    	if($actId != null and $userID != null ){
    		$where2 = "refType = 'Activity' and refId = ".$actId." and idResource =".$userID;
    		$listAss = $ass->getSqlElementsFromCriteria(null,false,$where2);
    	}else{
    	  continue;
    	}
    	
    	foreach ($listAss as $id3=>$val3){
      	foreach ($listAss[$id3] as $id4=>$val4){
      	  if ($id4 == 'id') {
      	    $assId = htmlEncode($val4);
      	  }
      	}
    	  $actRowId = "actRow".$actId;
    	  //$isSaveUserParam = Parameter::getUserParameter('selectAbsenceActivity');
    	  //$inputIdProject = Parameter::getUserParameter('inputIdProject');
    	  //$inputAssId = Parameter::getUserParameter('inputAssId');
    	  $isSaveUserParam = '';
    	  $inputIdProject = '';
    	  $inputAssId= '';
    	  if(sessionValueExists('selectAbsenceActivity'))$isSaveUserParam = getSessionValue('selectAbsenceActivity');
    	  if(sessionValueExists('inputIdProject'))$inputIdProject = getSessionValue('inputIdProject');
    	  if(sessionValueExists('inputAssId'))$inputAssId = getSessionValue('inputAssId');
//     	  // #4658 - Add control of access rights for user on project
//     	  $profile=getSessionUser()->getProfile($val3->idProject);
//     	  $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'assignmentEdit'));
//     	  $readonlyHabil = false;
//     	  if($habil->rightAccess!=1)$readonlyHabil=true;
//     	  if(!$idle and !$readonlyHabil){
//     	  // #4658 - End
    	  if(!$idle){
    	    if("actRow".$isSaveUserParam == $actRowId){
    	      $result .=' <tr class="absActivityRow dojoxGridRowSelected" id="'.$actRowId.'" align="center" style="height:20px; border: 1px solid grey; cursor:pointer;" onClick="selectActivity('.$actRowId.','.$actId.','.$idProject.','.$assId.')">';
    	    }else{
    	      $result .=' <tr class="absActivityRow dojoxGridRow" id="'.$actRowId.'" align="center" style="height:20px; border: 1px solid grey; cursor:pointer;" onClick="selectActivity('.$actRowId.','.$actId.','.$idProject.','.$assId.')">';
    	    }
    	  }else{
    	  	$workClose = new Work();
    	  	$where3 = " refType = 'Activity' and refId = ".$actId." and idResource =".$userID." and year = '".$currentYear."'";
    	  	$listWorkClose = $workClose->getSqlElementsFromCriteria(null,false,$where3);
    	  	if($listWorkClose){
    	  		$result .=' <tr class="absActivityRow dojoxGridRow" id="'.$actRowId.'" align="center" style="background: #DDDDDD; height:20px; border: 1px solid grey;">';
    	  	}else {
    	  		continue;
    	  	}
    	  }
    	  $result .= '   <input type="hidden" name="inputActId" id="inputActId" value="'.$isSaveUserParam.'"/>';
    	  $result .= '   <input type="hidden" name="inputIdProject" id="inputIdProject" value="'.$inputIdProject.'"/>';
    	  $result .= '   <input type="hidden" name="inputAssId" id="inputAssId" value="'.$inputAssId.'"/>';
    	  $result .='    <td align="left" style="border:1px solid grey;">&nbsp;'.$projName.'</td>';
    	  $result .='    <td align="center" style="border:1px solid grey;">#'.$actId.'</td>';
    	  $result .='    <td align="left" style="border:1px solid grey;">&nbsp;'.formatAbsenceColor($idColor, 15, 'left').$actName.'</td>';
    	  $result .='  </tr>';
    	  $tabColor[$actId]=$idColor;
    	  $idColor++;
    	}
    }
    if(!$idColor){
      $result .='<tr><td colspan="3">'; 
      if ($countExiting>0) {
        $result .='<div style="background:#DDDDDD;font-size:120%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noRightOnAdminActivities').'</div>';
      } else {
        $result .='<div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noActivityOnAdmProjectFound').'</div>';
      }
      $result .='</td></tr>';
    }
    $listActId = substr($listActId, 0, -1);
    $listActId .= ')';
    $result .='</table>';
    if (isset($print) and $print==true) {
      $result .='<table align="left" style="margin-top:20px; margin-left:100px;">';
      $result .='<tr><td class="label">'.i18n('colIdResource').'&nbsp;:&nbsp;</td><td>'.SqlList::getNameFromId('Resource', $userID).'</td></tr>';
      $result .='<tr><td class="label">'.i18n('year').'&nbsp;:&nbsp;</td><td>'.$currentYear.'</td></tr>';
      $result.='</table>';
    } else {
      $result .='<table align="left" style="margin-top:20px; margin-left:100px;">';
      $result .=' <tr>';
      $unitAbs = Parameter::getGlobalParameter('imputationUnit');
      $doublePoint="&nbsp;:";
      if(isNewGui()){
        $doublePoint="&nbsp;";
      }
      $result .='   <td style="margin-top:10px; height:20px;">'.ucfirst(i18n('dailyAbsenceDuration')).$doublePoint;
      $result .='     <div id="absenceInput" name="absenceInput" value="'.$max.'"
                    		  dojoType="dijit.form.NumberTextBox" constraints="{min:0,max:'.$max.'}"  required="true"
                    		      style="width:30px; margin-top:4px; height:20px;">';
      $result .= $keyDownEventScript;
      $result .='     </div> ';
      if(isNewGui()){
        if($unitAbs == 'days'){
          $result .=' &nbsp;'.i18n('day');
        }else{
          $result .=' &nbsp;'.i18n('hours');
        }
      }
      $result .='   </td>';
      if(!isNewGui()){
        if($unitAbs == 'days'){
        	$result .=' <td style="margin-top:30px; height:20px;width:55px">&nbsp;'.i18n('day').'</td>';
        }else{
        	$result .=' <td style="margin-top:30px; height:20px;width:40px">&nbsp;'.i18n('hours').'</td>';
        }
      }
      $result .=' </tr>';
      $result .=' <tr style="height:20px">';
      $result .='   <td colspan="3" style="padding-top:5px;text-align:right">'.ucfirst(i18n('duration')).$doublePoint;
      $classMediumText = "";
      $widthMedium = "55";
      $mediumTextButton='';
      if(isNewGui()){
        $mediumTextButton = 'class="mediumTextButton"';
        $widthMedium = "100";
      }
      $result .='   <span id="absButton_1" style="width:'.$widthMedium.'px; " type="button" dojoType="dijit.form.Button" '.$mediumTextButton.' showlabel="true">'.i18n('buttonFull')
              . '     <script type="dojo/method" event="onClick" >'
              . '        dijit.byId("absenceInput").setAttribute("value" ,'.$max.');'
              . '     </script>'
              . '   </span>&nbsp;';
      $result .='   <span id="absButton_0_5" style="width:'.$widthMedium.'px; " type="button" dojoType="dijit.form.Button" '.$mediumTextButton.' showlabel="true">'.i18n('buttonHalf')
              . '     <script type="dojo/method" event="onClick" >'
              . '       dijit.byId("absenceInput").setAttribute("value" , '.($max/2).');'
              . '     </script>'
              . '    </span>&nbsp;';
      $result .='   <span id="absButton_0" style="width:'.$widthMedium.'px;" type="button" dojoType="dijit.form.Button" '.$mediumTextButton.' showlabel="true">'.i18n('buttonNone')
              . '     <script type="dojo/method" event="onClick" >'
              . '       dijit.byId("absenceInput").setAttribute("value" ,0);'
              . '     </script>'
              . '   </span>';
      $result .='   </td>';
      $result .=' </tr>';
      $result .='</table>';
    }
    $result .='</div>';
    $result .='<div id="warningExceedWork" class="messageWARNING" style="z-index:99;display: none; text-align: center; position:absolute; top:10px;margin-left: 32%; height:20px; width: 35%;vertical-align:middle;font-size:110%">'.i18n('exceedWork').'</div>';
    $result .='<div id="warningExceedWorkWithPlanned" class="messageWARNING" style="z-index:99;display: none; text-align: center; position:absolute; top:10px;margin-left: 32%; height:20px; width: 35%;vertical-align:middle;font-size:110%">'.i18n('asPlannedWork').'</div>';
    $result .='<div id="warningNoActivity" class="messageWARNING" style="z-index:99;display: none; text-align: center; position:absolute; top:10px;margin-left: 32%; height:20px; width: 35%;vertical-align:middle;font-size:110%">'.i18n('noActivitySelected').'</div>';
    $result .='<div id="warningisValiadtedDay" class="messageWARNING" style="z-index:99;display: none; text-align: center; position:fixed; top:90px;left:30%; height:20px; width: 40%;vertical-align:middle;font-size:110%">'.i18n('isValidatedDay').'</div>';
    $result .='</br></br>';
    echo $result;
  }
  
  static function drawCalandarDiv($userID, $currentYear){
    SqlElement::$_cachedQuery['WorkPeriod']=array();
    // Activity calendar view
    $proj = new Project();
    $act = new Activity();
    $actTable=$act->getDatabaseTableName();
    $work=new Work();
    $workTable=$work->getDatabaseTableName();
    $where = "idProject in " . Project::getAdminitrativeProjectList(false, false);    
    $where .= " and (idle = 0 or exists (select 'x' from $workTable where idUser=".Sql::fmtId($userID)." and $workTable.refType='Activity' and $workTable.refId=$actTable.id and $workTable.year='$currentYear' )) ";
    
    $listAct = $act->getSqlElementsFromCriteria(null,false,$where,"idProject asc, id asc");
    $ass = new Assignment();
    $res = new Resource($userID);
    
    $actId = "";
    $actProject = "";
    $idProject = "";
    $listAss = "";
    $assId = "";
    $result="";
    $idColor = 0;
    $tabColor = array();
    $colorTab= self::$_colorTab;
    
    $listActId="(";
    if (count($listAct)>0) {
      foreach ($listAct as $id=>$val){
      	foreach ($listAct[$id] as $id2=>$val2){
      		if ($id2 == 'id') {
      			$listActId.= $val2 .',';
      			$actId = htmlEncode($val2);
      		}
      		if ($id2 == 'idProject') {
      			$idProject = $val2;
      		}
      		if($id2 == 'idle'){
      			$idle = $val2;
      		}
      	}
      	if($actId != null and $userID != null ){
      		$where2 = "refType = 'Activity' and refId = ".$actId." and idResource =".$userID;
      		$listAss = $ass->getSqlElementsFromCriteria(null,false,$where2);
      	}else{
      		continue;
      	}
      	foreach ($listAss as $id3=>$val3){
      		foreach ($listAss[$id3] as $id4=>$val4){
      			if ($id4 == 'id') {
      				$assId = htmlEncode($val4);
      			}
      		}
      		if($idle){
      			$workClose = new Work();
      			$where3 = " refType='Activity' and refId=".$actId." and idResource=".$userID." and year='".$currentYear."'";
      			$listWorkClose = $workClose->getSqlElementsFromCriteria(null,false,$where3); 
      			if(!$listWorkClose){
      				continue;
      			}
      		}
      		$tabColor[$actId]=$idColor;
      		$idColor++;
      	}
      }
      $listActId = substr($listActId, 0, -1);
    } else {
        $listActId .= 0;
    }
    $listActId .= ')';
    
    $today=date('Y-m-d');
    global $bankHolidays,$bankWorkdays;
    $result .= '<div id="absenceCalendar" name="absenceCalendar" align="center">';
    $result .='<table>';
    $result .='<tr><td class="calendarHeader" colspan="32">' .$currentYear. '</td></tr>';
    setlocale(LC_TIME, "en_US");
    setlocale(LC_TIME, "en_US");
    $whereWork = "idProject not in " . Project::getAdminitrativeProjectList(false,false);
    for ($m=1; $m<=12; $m++) {
    	$mx=($m<10)?'0'.$m:''.$m;   	
    	$time=mktime(0, 0, 0, $m, 1, $currentYear);
    	$libMonth=getMonthName($m);
    	$result .= '<tr style="height:30px">';
    	$result .= '<td class="calendar" style="background:#F0F0F0; width: 150px;">' . $libMonth . '</td>';
    	for ($d=1;$d<=date('t',strtotime($currentYear.'-'.$mx.'-01'));$d++) {
    		$dx=($d<10)?'0'.$d:''.$d;
    		$day=$currentYear.'-'.$mx.'-'.$dx;
    		$iDay=strtotime($day);
    		$isOff=isOffDay($day,$res->idCalendarDefinition);
    		$isOpen=isOpenDay($day,$res->idCalendarDefinition);
    		$style='';
    		if ($day==$today) {
    			$style.='font-weight: bold; font-size: 9pt;';
    		}
    		if($isOpen){
    			$style.='background: #FFFFFF; cursor: pointer;';
    		}else{
    			$style.='background: #DDDDDD;';
    		}
    
    		$dateId = date('M', $iDay).mb_substr(date('l',$iDay),0,1,"UTF-8").$d;
    		$dateDay = date('Ymd',$iDay);
    		$workDay = date('Y-m-d', $iDay);
    		//$week=date('Y', $iDay).date('W',$iDay) ;
    		//gautier #4383
    		$week = weekFormat($workDay);
    		$week = substr($week, 0,4).substr($week, 5,7);
    		$month = date('Ym', $iDay);
    		$year = date('Y', $iDay);
    		$result.= '<td id="'.$dateId.'" class="calendar" style="'.$style.'">';
    		$isValidate = false;
    		if($isOpen){
      		//if week is validated
      		$workPeriod = SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', array('idResource'=>$userID,'periodValue'=>$week));
      		if($workPeriod->validated or $workPeriod->submitted){
      	    $isValidate = true;
      		}
    		}
        if ($listActId<>"(0)") {
    		  $onClick = 'onClick="selectAbsenceDay(\''.$dateId.'\',\''.$dateDay.'\',\''.$workDay.'\',\''.$month.'\',\''.$year.'\',\''.$week.'\',\''.$userID.'\',\''.$isValidate.'\')"';
        } else {
          $onClick="";
        }    		
        if($isOpen){
    		  $result.= '<div style="position:relative; width:30px; height:30px;"'.$onClick.'>';
    		}else{
    			$result.= '<div style="position:relative; width:30px; height:30px;">';
    		}
    		$result.= '<div style="width:30px; height:15px;position:absolute; padding-top:10px; text-align:center; vertical-align:middle">';
    		$result.= mb_substr(i18n(date('l',$iDay)),0,1,"UTF-8").$d;
    		$result.= '</div>';
    		//gautier 
    		$unitAbs = Parameter::getGlobalParameter('imputationUnit');
        $res = new Resource($userID,true);
        $max = $res->capacity;
        if (!floatval($max)) $max=1;
    		if($isOpen){ 
    		  $dayWork = new Work();
    		  $whereWorkDay = " idResource=".$userID." AND workDate='".$workDay."' AND ".$whereWork;
    		  $listWork = $dayWork->getSqlElementsFromCriteria(null,null,$whereWorkDay);
    			$listADmWork = self::getWorkAdmActList($listActId, $userID, $workDay);
    			$totalRemplissage = 0;
    			$transHeight = 100;
    			$workHeigth = 0;
    			//Work open day and ADM project
    			foreach ($listADmWork as $admWork){
    				$workV = $admWork->work;
    				if($workV >$max)$workV=$max;
    				$idActWork = $admWork->refId;
    				if($workV){
    				  $workHeigth = $workHeigth + (($workV/$max)*100);
    				  $totalRemplissage += $workHeigth;
    				  if($totalRemplissage > 100){
    				  	$workHeigth = 100-($totalRemplissage-$workHeigth);
    				  }
    				    if($tabColor){
    				    $idColor = $tabColor[$idActWork];
    				    $idColor = $idColor%10;
    				    $background = $colorTab[$idColor];
    				    $result.='<div style="background:'.$background.'; height:'.$workHeigth.'%"> </div>';
    				  }
    				}
    			}
    			//Work open day and not ADM project
    			foreach ($listWork as $workNotAdm){
    				$workVal = $workNotAdm->work;
    				if($workVal > $max) $workVal = $max;
    				$idActWork = $workNotAdm->refId;
    				if($workVal){
    				  if($totalRemplissage < 100){
      					$workHeigth = $workHeigth + (($workVal/$max)*100);
      					$totalRemplissage += $workHeigth;
      					if($totalRemplissage > 100){
      					  $workHeigth = 100-($totalRemplissage-$workHeigth);
      					}
  							$background = '#A0A0A0';
  							$result.='<div style="background:'.$background.'; height:'.$workHeigth.'%"> </div>';
    				  }
    				}
    			}
    			
    			if($isValidate){
    			  $positionGrid=($totalRemplissage<100)?$totalRemplissage:100;
    			  $background = 'repeating-linear-gradient(-45deg,#505050,#505050 2px,transparent 2px,transparent 8px);#00BFFF';
    			    $result.='<div style="position:relative;top:-'.$positionGrid.'%;background:'.$background.'; height:100%"> </div>';
    			}
    			
    			if($transHeight > 0){
    			  $transHeight = $transHeight-$workHeigth;
    			  $result.='<div style="background:transparent; height:'.$transHeight.'%"> </div>';
    			}else {
    			  $transHeight = 0;
    			  $result.='<div style="background:transparent; height:'.$transHeight.'%"> </div>';
    			}
    		}
    		$result.= '</div>';
    		$result.= '</td>';
    	}
    	$result .= '</tr>';
    }
    $result .='</table>';
    $result .='</div>';
    echo $result;
  }
  
  static function getWorkAdmActList($listActId, $userId, $workDate){
  	$work = new Work();
  	$where = "refId in ".$listActId;
    $where .= " and refType = 'Activity' and idResource =".$userId." and workDate='".$workDate."'";
    $listWork = $work->getSqlElementsFromCriteria(null,false,$where);
    return $listWork;
  }
}

function formatAbsenceColor($idColor, $size=20, $float='right') {
  $idColor = $idColor%10;
	$color= Absence::$_colorTab;
	$radius=round($size/2,0);
	$res='<div style="margin-left:2px; border: 1px solid #AAAAAA;background:'.$color[$idColor].';';
	$res.='width:'.($size-2).'px;height:'.($size-2).'px;float:'.$float.';border-radius:'.$radius.'px"';
	$res.='>&nbsp;</div>';
	return $res;
}
?>