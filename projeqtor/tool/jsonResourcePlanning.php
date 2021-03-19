<?PHP
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
 * Get the list of objects, in Json format, to display the grid list
 */
require_once "../tool/projeqtor.php";
require_once "../tool/jsonFunctions.php";
scriptLog('   ->/tool/jsonResourcePlanning.php');
//SqlElement::$_cachedQuery['Project']=array(); // Do not manage project cahce here : lead to error and is no use as some caching is done here.
SqlElement::$_cachedQuery['Ticket']=array();
SqlElement::$_cachedQuery['Activity']=array();
SqlElement::$_cachedQuery['Resource']=array();
SqlElement::$_cachedQuery['PlanningElement']=array();
$objectClass='PlanningElement';
$columnsDescription=Parameter::getPlanningColumnDescription();
$obj=new $objectClass();
$table=$obj->getDatabaseTableName();
$displayResource=Parameter::getUserParameter('displayResourcePlan');
$seeAllResource=false;
$right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'resourcePlanning'));
if ($right) { 
  $list=new ListYesNo($right->rightAccess);
  if ($list->code=='YES') {
    $seeAllResource=true;
  }
}
if (!$seeAllResource) {
  $prfLst=$user->getAllProfiles();
  foreach ($prfLst as $prf) {
    $right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$prf, 'scope'=>'resourcePlanning'));
    if ($right) {
      $list=new ListYesNo($right->rightAccess);
      if ($list->code=='YES') {
        $seeAllResource=true;
        break;
      }
    }
  }
}

$print=false;
if ( array_key_exists('print',$_REQUEST) ) {
	$print=true;
	include_once('../tool/formatter.php');
}
$saveDates=false;
if ( array_key_exists('listSaveDates',$_REQUEST) ) {
	$saveDates=true;
}
$startDate="";
$endDate="";
if (array_key_exists('startDatePlanView',$_REQUEST) and array_key_exists('endDatePlanView',$_REQUEST)) {
	$startDate= trim($_REQUEST['startDatePlanView']);
	Security::checkValidDateTime($startDate);
	$endDate= trim($_REQUEST['endDatePlanView']);
	Security::checkValidDateTime($endDate);
	$user=getSessionUser();
	$paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningStartDate'));
	$paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningEndDate'));
	if ($saveDates) {
		$paramStart->parameterValue=$startDate;
		$paramStart->save();
		$paramEnd->parameterValue=$endDate;
		$paramEnd->save();
	} else {
		if ($paramStart->id) {
			$paramStart->delete();
		}
		if ($paramEnd->id) {
			$paramEnd->delete();
		}
	}
}

$selectResource=null;
if (array_key_exists('selectResourceName',$_REQUEST)) {
  $selectResource=trim($_REQUEST['selectResourceName']);
}
if(!$selectResource and sessionValueExists('selectResourceName')){ 
  $selectResource =trim(getSessionValue('selectResourceName'));
}
$selectTeam=null;
if (array_key_exists('teamName',$_REQUEST)) {
  $selectTeam=trim($_REQUEST['teamName']);
}
if(!$selectTeam and sessionValueExists('teamName')){
  $selectTeam =trim(getSessionValue('teamName'));
}
$selectOrganization=null;
if (array_key_exists('organizationName',$_REQUEST)) {
  $selectOrganization=trim($_REQUEST['organizationName']);
}
if(!$selectOrganization and sessionValueExists('organizationName')){
  $selectOrganization =trim(getSessionValue('organizationName'));
}
// Header
if ( array_key_exists('report',$_REQUEST) ) {
	$headerParameters="";
	if (array_key_exists('startDate',$_REQUEST) and trim($_REQUEST['startDate'])!="") {
		Security::checkValidDateTime(trim($_REQUEST['startDate']));
		$headerParameters.= i18n("colStartDate") . ' : ' . dateFormatter($_REQUEST['startDate']) . '<br/>';
	}
	if (array_key_exists('endDate',$_REQUEST) and trim($_REQUEST['endDate'])!="") {
		Security::checkValidDateTime(trim($_REQUEST['endDate']));
		$headerParameters.= i18n("colEndDate") . ' : ' . dateFormatter($_REQUEST['endDate']) . '<br/>';
	}
	if (array_key_exists('format',$_REQUEST)) {
		Security::checkValidDatePeriodScale(trim($_REQUEST['format']));
		$headerParameters.= i18n("colFormat") . ' : ' . i18n($_REQUEST['format']) . '<br/>';
	}
	if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
		$headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $_REQUEST['idProject'])) . '<br/>';
	}
	include "../report/header.php";
}
if (! isset($outMode)) { $outMode=""; }
$showIdleProjects=(sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;
  $showIdle=true;
  if (array_key_exists('idle',$_REQUEST)) {
    $showIdle=true;
  }

$accessRightRead=securityGetAccessRight('menuActivity', 'read');
$proj=(sessionValueExists('project'))?getSessionValue('project'):null;
if(strpos($proj, ",")){
	$proj="*";
}
if ( ! ( $accessRightRead!='ALL' or ($proj and $proj!='*'))
and ( ! array_key_exists('idProject',$_REQUEST) or trim($_REQUEST['idProject'])=="")) {
	$listProj=explode(',',getVisibleProjectsList(! $showIdleProjects));
	// #720
	//if (count($listProj)-1 > Parameter::getGlobalParameter('maxProjectsToDisplay')) {
	//	echo i18n('selectProjectToPlan');
	//	return;
	//}
}
$querySelect = '';
$queryFrom='';
$queryWhere='';
$queryOrderBy='';
$idTab=0;
if (! array_key_exists('idle',$_REQUEST) ) {
	$queryWhere= $table . ".idle=0 ";
}
$showProject=(isset($saveShowProject) and $saveShowProject==1)?true:false;
if ( array_key_exists('showProject',$_REQUEST) ) {
  $showProject=true;
}

$showNullAssignment=(isset($saveShowNullAssignment) and $saveShowNullAssignment==1)?true:false;
if ( array_key_exists('listShowNullAssignment',$_REQUEST) ) {
  $showNullAssignment=true;
}

 if (! $showNullAssignment) {
   $queryWhere.= ($queryWhere=='')?'':' and ';
   $queryWhere.=' ass.plannedWork>0 ';
 }
// else $queryWhere.=' ass.plannedWork>0 ';
 
if (!$seeAllResource) {
   $queryWhere.= ($queryWhere=='')?'':' and ';
   $listUsers=array($user->id=>$user->name);
   $rta=new ResourceTeamAffectation();
   $listRta=$rta->getSqlElementsFromCriteria(array('idResource'=>$user->id));
   foreach ($listRta as $rta) {
     $listUsers[$rta->idResourceTeam]='pool';
   }
   $queryWhere.=' ass.idResource in '.transformListIntoInClause($listUsers);
}
$queryWhere.= ($queryWhere=='')?'':' and ';
$queryWhere.=getAccesRestrictionClause('Activity',$table,$showIdleProjects);
if ( array_key_exists('report',$_REQUEST) ) {
	if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
		$queryWhere.= ($queryWhere=='')?'':' and ';
		$queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects, $_REQUEST['idProject']) ;
	}
} else {
	$queryWhere.= ($queryWhere=='')?'':' and ';
	$queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects) ;
}

// Remove administrative projects :
$queryWhere.= ($queryWhere=='')?'':' and ';
$queryWhere.=  $table . ".idProject not in " . Project::getAdminitrativeProjectList() ;
$ass=new Assignment();
$res=new Resource();
$querySelect .= "pe.idProject as idProj, pe.id idPe, pe.wbs wbs, pe.wbsSortable wbsSortable, pe.priority priority, pe.idplanningmode idplanningmode, pe.validatedenddate, pe.notplannedwork  , pe.plannedenddate as peplannedend, pe.plannedstartdate as peplannedstart, pe.color as color, ass.* , usr.fullName as name, pe.refName refName";
$querySelect .= ", pe.topRefType as topreftype, pe.toprefid as toprefid, pe.topid as topid ";
$queryFrom .= $table . ' pe, ' . $ass->getDatabaseTableName() . ' ass, ' . $res->getDatabaseTableName() . ' usr';
$queryWhere= ' pe.refType=ass.refType and pe.RefId=ass.refId and usr.id=ass.idResource and ' . str_replace($table, 'pe', $queryWhere);
$queryOrderBy .= ' name, pe.wbsSortable ';

$list=Plugin::getEventScripts('query','ResourcePlanning');
$objectClass='ResourcePlanning';
foreach ($list as $script) {
  require $script; // execute code
}

$table=array();
$specific="imputation";
$includePool=true;
ob_start();
include("../tool/drawResourceListForSpecificAccess.php");
if (ob_get_length()){
  ob_clean();  // Important : clean possible extra char before returning data;
}
$allowedResource=$table;
if ($selectTeam) {
  $team=new Team($selectTeam,true);
  $teamMembers=$team->getMembers();
} else {
  $teamMembers=null;
}
if ($selectOrganization) {
  $orga=new Organization($selectOrganization,true);
  $orgaMembers=$orga->getMembers();
} else {
  $orgaMembers=null;
}

foreach ($allowedResource as $resId=>$resName) {
  if ($selectResource and $selectResource!=$resId) {
    unset($allowedResource[$resId]);
  } 
  if ($selectTeam and is_array($teamMembers)) {
	  if (!isset($teamMembers[$resId])) {
	    unset($allowedResource[$resId]);
	  }
  }
  if ($selectOrganization and is_array($orgaMembers)) {
    if (!isset($orgaMembers[$resId])) {
      unset($allowedResource[$resId]);
    }
  }
}

$rta=new ResourceTeamAffectation();
$today=date('Y-m-d');

// ADD RESOURCES OF SELECTED POOL
foreach ($allowedResource as $resId=>$resName) {
  $rtaList=$rta->getSqlElementsFromCriteria(array('idResourceTeam'=>$resId));
  foreach ($rtaList as $rta) {
    if ($rta->idle) continue;
    if ($rta->endDate==null or $rta->endDate>=$today) {
      if (!isset($allowedResource[$rta->idResource])) $allowedResource[$rta->idResource]=SqlList::getNameFromId('ResourceAll', $rta->idResource);
    }
  }
}

// ADD POOLS OF SELECTED RESOURCES
foreach ($allowedResource as $resId=>$resName) {
  $rtaList=$rta->getSqlElementsFromCriteria(array('idResource'=>$resId));
  foreach ($rtaList as $rta) {
    if ($rta->idle) continue;
    if ($rta->endDate==null or $rta->endDate>=$today) {
      if (!isset($allowedResource[$rta->idResourceTeam])) $allowedResource[$rta->idResourceTeam]=SqlList::getNameFromId('ResourceAll', $rta->idResourceTeam);
    }
  }
}



$queryWhere.="and ass.idResource in ".transformListIntoInClause($allowedResource);
// constitute query and execute
// florent 4391
$queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
$arrayFilter=jsonGetFilterArray('ResourcePlanning', false);
$act=new Activity();
$pe=new PlanningElement();
$peTable=$pe->getDatabaseTableName();
$actTable=$act->getDatabaseTableName();
$querySelectAct="pet.refId as id , pet.wbsSortable as wbs ";
$queryFromAct=" $peTable as pet left join $actTable on (pet.refType='Activity' and pet.refId=$actTable.id)";
$queryWhereAct="1=1 ";
$queryOrderByAct="$actTable.id asc";
$cpt=0;
$applyFilter=false;
$arrayRestrictWbs=array();
if (count($arrayFilter)>0  ) {
  $applyFilter=true;
  jsonBuildWhereCriteria($querySelectAct,$queryFromAct,$queryWhereAct,$queryOrderByAct,$cpt,$arrayFilter,$act);
  $queryAct='select ' . $querySelectAct
  . ' from ' . $queryFromAct
  . ' where ' . $queryWhereAct
  . ' order by ' . $queryOrderByAct;
  $resultAct=Sql::query($queryAct);
  while ($line = Sql::fetchLine($resultAct)) {
    $wbsExplode=explode('.',$line['wbs']);
    $wbsParent="";
    foreach ($wbsExplode as $wbsTemp) {
      $wbsParent=$wbsParent.(($wbsParent)?'.':'').$wbsTemp;
      if (!isset($arrayRestrictWbs[$wbsParent])) {
        $arrayRestrictWbs[$wbsParent]=$line['id'];
      } 
    }
  }
  ksort($arrayRestrictWbs);
}

$query='select ' . $querySelect
. ' from ' . $queryFrom
. ' where ' . $queryWhere
. ' order by ' . $queryOrderBy;
$result=Sql::query($query);

if (isset($debugJsonQuery) and $debugJsonQuery) { // Trace in configured to
  debugTraceLog("jsonResourcePlanning: ".$query); // Trace query
  debugTraceLog("  => error (if any) = ".Sql::$lastQueryErrorCode.' - '.Sql::$lastQueryErrorMessage);
  debugTraceLog("  => number of lines returned = ".Sql::$lastQueryNbRows);
}

$arrayPeAss=array();
$arrayResource=array();
$arrayProject=array();
$nbRows=0;
// return result in json format
$d=new Dependency();
if (Sql::$lastQueryNbRows == 0) {
	echo i18n('noDataToDisplay');
} else {
	$collapsedList=Collapsed::getCollaspedList();
	$list=array();
	$idResource="";
	$idProject="";
	//$sumValidated=0;
	$sumAssigned=0;
	$sumReal=0;
	$sumLeft=0;
	$sumPlanned=0;
	$sumProjAssigned=0;
  $sumProjReal=0;
  $sumProjLeft=0;
  $sumProjPlanned=0;
  $keyProj="";
  $idProj='';
  $keyRes="";
  $idRes='';
  $cptLine=0; 
	while ($line = Sql::fetchLine($result)) {
		$line=array_change_key_case($line,CASE_LOWER);
		//florent 4391
		if ($applyFilter and (!isset($arrayRestrictWbs[$line['wbssortable']]))) continue;
		if (! isset($allowedResource[$line['idresource']])) continue;
		$cptLine++;
		if ($line['idresource']!=$idResource) {
			$idResource=$line['idresource'];
			$arrayResource[$idResource]=array();;
			$resAr=array();
			$resAr["refname"]=$line['name'];
			$res=new ResourceAll($idResource,true);
			if ($res->isResourceTeam) {
			  $resAr["reftype"]='ResourceTeam';
			} else {
			  $resAr["reftype"]='Resource';
			}
			$resAr["refid"]=$idResource;
			$resAr["elementary"]='0';
			$idRes=$idResource*1000000;
			$resAr["id"]=$idRes;
			$resAr["idle"]='0';
			$resAr["wbs"]='';
			$resAr["wbssortable"]='';
			$resAr["realstartdate"]='';
			$resAr["realenddate"]='';
			$resAr["plannedstartdate"]='';
			$resAr["plannedenddate"]='';
			$resAr["idresource"]=$idResource;
			$resAr["progress"]=0;
			$resAr["topid"]=0;
			$resAr["leftwork"]=0;
			$resAr["priority"]="";
			$resAr["planningmode"]="";
			$keyRes='Resource#'.$idResource;
			$list[$keyRes]=$resAr;
			//$sumValidated=0;
		  $sumAssigned=0;
		  $sumReal=0;
		  $sumLeft=0;
		  $sumPlanned=0;
		  $idProject="";
		}
	  if ($showProject and $line['idproj']!=$idProject) {
      $idProject=$line['idproj'];
      if (array_key_exists($idProject, $arrayProject)) {
      	$prj=$arrayProject[$idProject];
      } else {
        $prj=new Project($idProject,false);
        $arrayProject[$idProject]=$prj;
      }
      $resPr=array();
      $resPr["refname"]=$prj->name;
      $resPr["reftype"]='Project';
      $resPr["refid"]=$idProject;
      $resPr["elementary"]='0';
      $idProj=$idRes+$idProject;
      $resPr["id"]=$idProj;
      $resPr["idle"]='0';
      $resPr["wbs"]=$prj->ProjectPlanningElement->wbs;
      $resPr["wbssortable"]=$prj->ProjectPlanningElement->wbsSortable;
      $resPr["realstartdate"]='';
      $resPr["realenddate"]='';
      $resPr["plannedstartdate"]='';
      $resPr["plannedenddate"]='';
      $resPr["idresource"]=$idResource;
      $resPr["progress"]=0;
      $resPr["topid"]=$idRes;
      $resPr["leftwork"]=0;
      $resPr["priority"]="";
      $resPr["planningmode"]="";
      $keyProj=$keyRes.'_Project#'.$idProject;
      $list[$keyProj]=$resPr;
      //$sumValidated=0;
      $sumProjAssigned=0;
      $sumProjReal=0;
      $sumProjLeft=0;
      $sumProjPlanned=0;
    }
    $line["elementary"]='1';
    if (!isset($line["id"])) $line["id"]=$line["idpe"];
		if ($line['reftype']=='Meeting' and $line['topreftype']=='PeriodicMeeting') {
		  // Do not change topRefType and topRefId;
		} else {
		  if ($line['reftype']=='PeriodicMeeting') {
		    $line["elementary"]='0'; // Will contain meetings
		    $line["id"]=$line["idpe"];
		  } 
		  $line["topreftype"]=($showProject)?'Project':'Resource';
		  $line["toprefid"]=($showProject)?$idProject:$idResource;
		}
		$line["validatedworkdisplay"]='';
		$line["assignedworkdisplay"]=Work::displayWorkWithUnit($line["assignedwork"]);
		$line["realworkdisplay"]=Work::displayWorkWithUnit($line["realwork"]);
		$line["leftworkdisplay"]=Work::displayWorkWithUnit($line["leftwork"]);
		$line["plannedworkdisplay"]=Work::displayWorkWithUnit($line["plannedwork"]);
		if (floatval($line['plannedwork'])==0 and trim($line['plannedstartdate'])=='' and trim($line['peplannedstart'])!='') { $line['plannedstartdate']=$line['peplannedstart'];}
		if (floatval($line['plannedwork'])==0 and trim($line['plannedenddate'])=='' and trim($line['peplannedend'])!='') { $line['plannedenddate']=$line['peplannedend'];}
		if ($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1) {
		  $ref=$line['reftype'];
		  $type='id'.$ref.'Type';
		  $item=new $ref($line['refid'],true);
      $line["status"]=(property_exists($item,'idStatus'))?SqlList::getNameFromId('Status',$item->idStatus)."#split#".SqlList::getFieldFromId('Status',$item->idStatus,'color'):null;
      $line["type"]=(property_exists($item,$type))?SqlList::getNameFromId('Type',$item->$type):null;
		}
		if ($line['reftype']=='Meeting' and $line['topreftype']=='PeriodicMeeting') {
		  // topid from query
		} else {
		  $line["topid"]=($showProject)?$idProj:$idRes;
		}
		if ($line["leftwork"]>0) {
			//$line['realenddate']='';
		}
		if (trim($line["realstartdate"]) and !trim($line["plannedstartdate"])) {
			$line['plannedstartdate']=$line['realstartdate'];
		}
		$line['progress']=($line["plannedwork"]>0)?round($line["realwork"]/$line["plannedwork"],2):'';
		$line['planningmode']=SqlList::getNameFromId('PlanningMode', $line['idplanningmode']);
		$list[]=$line;
		//$sumValidated=0;
    $sumAssigned+=$line["assignedwork"];
    $sumReal+=$line["realwork"];
    $sumLeft+=$line["leftwork"];
		$sumPlanned+=$line["plannedwork"];
		if (! $list[$keyRes]["realstartdate"] or $line['realstartdate'] < $list[$keyRes]["realstartdate"]) {
			if ($line['realstartdate'] and $line['realstartdate']<$line['plannedstartdate']) {
			  $list[$keyRes]["realstartdate"]=$line['realstartdate'];
			}
		}
		if (! $list[$keyRes]["realenddate"] or $line['realenddate'] > $list[$keyRes]["realenddate"]) {
			if ($line['realenddate'] and $line['realenddate']>$line['plannedenddate']) {
			  $list[$keyRes]["realenddate"]=$line['realenddate'];
			}
		}
		if (! $list[$keyRes]["plannedstartdate"] or $line['plannedstartdate'] < $list[$keyRes]["plannedstartdate"]) {
      if ($line['plannedstartdate'] ) {
			  $list[$keyRes]["plannedstartdate"]=$line['plannedstartdate'];
      }
		}
		if (! $list[$keyRes]["plannedenddate"] or $line['plannedenddate'] > $list[$keyRes]["plannedenddate"]) {
			if ($line['plannedenddate']) {
			  $list[$keyRes]["plannedenddate"]=$line['plannedenddate'];
			  if ($list[$keyRes]["plannedenddate"]>$list[$keyRes]["realenddate"]) {
			  	$list[$keyRes]["realenddate"]="";
			  }
			}
		}
		$list[$keyRes]["assignedwork"]=$sumAssigned;
		$list[$keyRes]["realwork"]=$sumReal;
		$list[$keyRes]["leftwork"]=$sumLeft;
		$list[$keyRes]["plannedwork"]=$sumPlanned;
		$list[$keyRes]["validatedworkdisplay"]='';
		$list[$keyRes]["assignedworkdisplay"]=Work::displayWorkWithUnit($sumAssigned);
		$list[$keyRes]["realworkdisplay"]=Work::displayWorkWithUnit($sumReal);
		$list[$keyRes]["leftworkdisplay"]=Work::displayWorkWithUnit($sumLeft);
		$list[$keyRes]["plannedworkdisplay"]=Work::displayWorkWithUnit($sumPlanned);
		$list[$keyRes]["progress"]=($sumPlanned>0)?round($sumReal/$sumPlanned,2):0;
		$list[$keyRes]["status"]="";
		$list[$keyRes]["type"]="";
		if ($showProject) {
			$sumProjAssigned+=$line["assignedwork"];
	    $sumProjReal+=$line["realwork"];
	    $sumProjLeft+=$line["leftwork"];
	    $sumProjPlanned+=$line["plannedwork"];
	    $list[$keyProj]["assignedwork"]=$sumProjAssigned;
	    $list[$keyProj]["realwork"]=$sumProjReal;
	    $list[$keyProj]["leftwork"]=$sumProjLeft;
	    $list[$keyProj]["plannedwork"]=$sumProjPlanned;
	    $list[$keyProj]["assignedworkdisplay"]=Work::displayWorkWithUnit($sumProjAssigned);
	    $list[$keyProj]["realworkdisplay"]=Work::displayWorkWithUnit($sumProjReal);
	    $list[$keyProj]["leftworkdisplay"]=Work::displayWorkWithUnit($sumProjLeft);
	    $list[$keyProj]["plannedworkdisplay"]=Work::displayWorkWithUnit($sumProjPlanned);
	    $list[$keyProj]["progress"]=($sumProjPlanned)?round($sumProjReal/$sumProjPlanned,2):0;
	    if ($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1 or $columnsDescription['Priority']['show']==1) {
	      $item=new Project($line['idproject'],false);
	      $list[$keyProj]["status"]=SqlList::getNameFromId('Status',$item->idStatus)."#split#".SqlList::getFieldFromId('Status',$item->idStatus,'color');
	      $list[$keyProj]["type"]=SqlList::getNameFromId('Type',$item->idProjectType);
	      //$list[$keyProj]["priority"]=SqlList::getNameFromId('Priority',$item->ProjectPlanningElement->priority);
	      $list[$keyProj]["priority"]=$item->ProjectPlanningElement->priority;
	    }
			if (! $list[$keyProj]["realstartdate"] or $line['realstartdate'] < $list[$keyProj]["realstartdate"]) {
	      if ($line['realstartdate'] and $line['realstartdate']<$line['plannedstartdate']) {
	        $list[$keyProj]["realstartdate"]=$line['realstartdate'];
	      }
	    }
	    if (! $list[$keyProj]["realenddate"] or $line['realenddate'] > $list[$keyProj]["realenddate"]) {
	      if ($line['realenddate'] and $line['realenddate']>$line['plannedenddate']) {
	        $list[$keyProj]["realenddate"]=$line['realenddate'];
	      }
	    }
	    if (! $list[$keyProj]["plannedstartdate"] or $line['plannedstartdate'] < $list[$keyProj]["plannedstartdate"]) {
	      if ($line['plannedstartdate'] ) {
	        $list[$keyProj]["plannedstartdate"]=$line['plannedstartdate'];
	      }
	    }
	    if (! $list[$keyProj]["plannedenddate"] or $line['plannedenddate'] > $list[$keyProj]["plannedenddate"]) {
	      if ($line['plannedenddate']) {
	        $list[$keyProj]["plannedenddate"]=$line['plannedenddate'];
	        if ($list[$keyProj]["plannedenddate"]>$list[$keyProj]["realenddate"]) {
	          $list[$keyProj]["realenddate"]="";
	        }
	      }
	    }
		}
		if (! isset($arrayPeAss[$line['idpe']])) {
			$arrayPeAss[$line['idpe']]=array();
		}
		$arrayPeAss[$line['idpe']][$line['id']]=$line['id'];
		$arrayResource[$idResource][$line['id']]=$line['id'];
	}
	if ($print) {
		if ( array_key_exists('report',$_REQUEST) ) {
			$test=array();
			if (Sql::$lastQueryNbRows > 0) $test[]="OK";
			if (checkNoData($test))  exit;
		}
		if ($outMode=='mpp') {
			exportGantt($list);
		} else {
			displayGantt($list);
		}
	} else {
		echo '{"identifier":"id",' ;
    echo ' "items":[';
		$idResource="";
		foreach ($list as $line) {
			if ($line['idresource']!=$idResource) {
				$idResource=$line['idresource'];
			}
			$line['resource']=$idResource;
			echo (++$nbRows>1)?',':'';
			echo  '{';
			$nbFields=0;
			$idPe="";
			if (trim($line['plannedenddate'])=='' and trim($line['realenddate'])!='') $line['plannedenddate']=$line['realenddate'];
			if (trim($line['plannedstartdate'])=='' and trim($line['realstartdate'])!='') $line['plannedstartdate']=$line['realstartdate'];
			foreach ($line as $id => $val) {
				if ($val==null) {$val=" ";}
				if ($val=="") {$val=" ";}
				echo (++$nbFields>1)?',':'';
				//echo '"' . htmlEncode($id) . '":"' . htmlEncodeJson(htmlEncode($val)) . '"';
				if ($id=='refname' or $id=='resource' or $id=='planningmode' or substr($id,-7)!='display') { 
          $val=htmlEncode(htmlEncodeJson($val));
        } else {
          $val=htmlEncodeJson($val);
        }
        echo '"' . htmlEncode($id) . '":"' . $val . '"';
				if ($id=='idPe') {$idPe=$val;}
			}
			//add expanded status
			if (($line['reftype']=='Resource' or $line['reftype']=='ResourceTeam' or $line['reftype']=='Project') and array_key_exists('Planning_'.$line['reftype'].'_'.$line['refid'], $collapsedList)) {
				echo ',"collapsed":"1"';
			} else {
				echo ',"collapsed":"0"';
			}
			$crit=array('successorId'=>$idPe);
			$listPred="";
			$depList=$d->getSqlElementsFromCriteria($crit,false);
			foreach ($depList as $dep) {
				if ( isset($arrayPeAss[$dep->predecessorId])) {
					foreach($arrayPeAss[$dep->predecessorId] as $assId) {
						// Restrict to activities of save resource
						if (array_key_exists($assId,$arrayResource[$idResource])) {
							$listPred.=($listPred!="")?',':'';
							$listPred.=$assId;
						}
					}
				}
			}
			echo ', "depend":"' . '"'; // No dependency to display on Resource Planning
			echo ', "color":"'.((isset($line['color']))?trim($line['color'],'#'):'').'"';
			echo '}';
		}
		echo ' ] }';
	}
}

function displayGantt($list) {
	global $outMode,$showProject;
	$showWbs=false;
	if (array_key_exists('showWBS',$_REQUEST) ) {
		$showWbs=true;
	}
  $showWork=false;
  if ( array_key_exists('showWork',$_REQUEST) ) {
    $showWork=true;
  }
	// calculations
	$startDate=date('Y-m-d');
	if (array_key_exists('startDate',$_REQUEST)) {
		$startDate=Security::checkValidDateTime($_REQUEST['startDate']);
	}

	$endDate='';
	if (array_key_exists('endDate',$_REQUEST)) {
		$endDate=Security::checkValidDateTime($_REQUEST['endDate']);
	}
	$format='day';
	if (array_key_exists('format',$_REQUEST)) {
		$format=Security::checkValidPeriodScale($_REQUEST['format']);
	}
	if($format == 'day') {
		$colWidth = 18;
		$colUnit = 1;
		$topUnit=7;
	} else if($format == 'week') {
		$colWidth = 50;
		$colUnit = 7;
		$topUnit=7;
	} else if($format == 'month') {
		$colWidth = 60;
		$colUnit = 30;
		$topUnit=30;
  } else if($format == 'quarter') {
    $colWidth = 30;
    $colUnit = 30;
    $topUnit=90;
	}
	$maxDate = '';
	$minDate = '';
	if (count($list) > 0) {
		$resultArray=array();
		foreach ($list as $line) {
			$pStart="";
			$pStart=(trim($line['plannedstartdate'])!="")?$line['plannedstartdate']:$pStart;
			$pStart=(trim($line['realstartdate'])!="")?$line['realstartdate']:$pStart;
			if (trim($line['plannedstartdate'])!=""
			and trim($line['realstartdate'])!=""
			and $line['plannedstartdate']<$line['realstartdate'] ) {
				$pStart=$line['plannedstartdate'];
			}
			$pEnd="";
			$pEnd=(trim($line['plannedenddate'])!="")?$line['plannedenddate']:$line['realenddate'];
			$pEnd=(trim($line['plannedenddate'])=="" and trim($line['realenddate'])!="")?$line['realenddate']:$pEnd;
			if ($line['reftype']=='Milestone') {
				$pStart=$pEnd;
			}
			$line['pstart']=$pStart;
			$line['pend']=$pEnd;
			$line['prealend']=$line['realenddate'];
			$line['pplanstart']=$line['plannedstartdate'];
			$resultArray[]=$line;
			if ($maxDate=='' or $maxDate<$pEnd) {$maxDate=$pEnd;}
			if ($minDate=='' or ($minDate>$pStart and trim($pStart))) {$minDate=$pStart;}

		}
		if ($minDate<$startDate) {
			$minDate=$startDate;
		}
		if ($endDate and $maxDate>$endDate) {
			$maxDate=$endDate;
		}
		if ($format=='day' or $format=='week') {
			//$minDate=addDaysToDate($minDate,-1);
			$minDate=date('Y-m-d',firstDayofWeek(weekNumber($minDate),substr($minDate,0,4)));
			//$maxDate=addDaysToDate($maxDate,+1);
			$maxDate=date('Y-m-d',firstDayofWeek(weekNumber($maxDate),substr($maxDate,0,4)));
			$maxDate=addDaysToDate($maxDate,+6);
		} else if ($format=='month') {
			//$minDate=addDaysToDate($minDate,-1);
			$minDate=substr($minDate,0,8).'01';
			//$maxDate=addDaysToDate($maxDate,+1);
			$maxDate=addMonthsToDate($maxDate,+1);
			$maxDate=substr($maxDate,0,8).'01';
			$maxDate=addDaysToDate($maxDate,-1);
    } else if ($format=='quarter') {
      $arrayMin=array("01-01"=>"01-01","02-01"=>"01-01","03-01"=>"01-01",
                      "04-01"=>"04-01","05-01"=>"04-01","06-01"=>"04-01",
                      "07-01"=>"07-01","08-01"=>"07-01","09-01"=>"07-01",
                      "10-01"=>"10-01","11-01"=>"10-01","12-01"=>"10-01");
      $arrayMax=array("01-31"=>"03-31","02-28"=>"03-31","02-29"=>"03-31","03-31"=>"03-01",
                      "04-30"=>"06-30","05-31"=>"06-30","06-30"=>"06-30",
                      "07-31"=>"09-30","08-31"=>"09-30","09-30"=>"09-30",
                      "10-31"=>"12-31","11-30"=>"12-31","12-31"=>"12-31");
      //$minDate=addDaysToDate($minDate,-1);
      $minDate=substr($minDate,0,8).'01';
      $minDate=substr($minDate,0,5).$arrayMin[substr($minDate,5)];
      //$maxDate=addDaysToDate($maxDate,+1);
      $maxDate=addMonthsToDate($maxDate,+1);
      $maxDate=substr($maxDate,0,8).'01';
      $maxDate=addDaysToDate($maxDate,-1);
      $maxDate=substr($maxDate,0,5).$arrayMax[substr($maxDate,5)];
		}
		$numDays = (dayDiffDates($minDate, $maxDate) +1);
		$numUnits = round($numDays / $colUnit);
		$topUnits = round($numDays / $topUnit);
		$days=array();
		$openDays=array();
		$day=$minDate;
		for ($i=0;$i<$numDays; $i++) {
			$days[$i]=$day;
			$openDays[$i]=isOpenDay($day,'1');
			$day=addDaysToDate($day,1);
		}
		//echo "mindate:$minDate maxdate:$maxDate numDays:$numDays numUnits:$numUnits topUnits:$topUnits" ;
		// Header
	  $sortArray=Parameter::getPlanningColumnOrder();
    $cptSort=0;
    foreach ($sortArray as $name) { if (substr($name,0,6)!='Hidden' and $name!='ValidatedWork' and $name!='Resource') $cptSort++; }
		//echo '<table dojoType="dojo.dnd.Source" id="wishlistNode" class="container ganttTable" style="border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
    echo '<div style="overflow:auto;">';
		echo '<table style="-webkit-print-color-adjust: exact;font-size:80%; border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
		echo '<tr style="height: 20px;"><td colspan="' . (2+$cptSort) . '">&nbsp;</td>';
		$day=$minDate;
		for ($i=0;$i<$topUnits;$i++) {
			$span=$topUnit;
			$title="";
			if ($format=='month') {
				$title=substr($day,0,4);
				$span=numberOfDaysOfMonth($day);
			} else if($format=='week') {
				$title=substr($day,2,2) . " #" . weekNumber($day);
			} else if ($format=='day') {
				$tDate = explode("-", $day);
				$date= mktime(0, 0, 0, $tDate[1], $tDate[2]+1, $tDate[0]);
				$title=substr($day,0,4) . " #" . weekNumber($day);
				$title.=' (' . substr(i18n(date('F', $date)),0,4) . ')';
      } else if ($format=='quarter') {
        $arrayQuarter=array("01"=>"1","02"=>"1","03"=>"1",
                      "04"=>"2","05"=>"2","06"=>"2",
                      "07"=>"3","08"=>"3","09"=>"3",
                      "10"=>"4","11"=>"4","12"=>"4");
        $title="Q";
        $title.=$arrayQuarter[substr($day,5,2)];
        $title.=" ".substr($day,0,4);
         $span=3*numberOfDaysOfMonth($day);			
      }
			echo '<td class="reportTableHeader" colspan="' . $span . '">';
			echo $title;
			echo '</td>';
			if ($format=='month') {
				$day=addMonthsToDate($day,1);
      } else if ($format=='quarter') {
          $day=addMonthsToDate($day,3);
			} else {
				$day=addDaysToDate($day,$topUnit);
			}
		}
		echo '</tr>';
		echo '<TR style="height: 20px;">';
		echo '  <TD class="reportTableHeader" style="width:15px; border-right:0px;"></TD>';
		echo '  <TD class="reportTableHeader" style="width:150px; border-left:0px; text-align: left;">' . i18n('colTask') . '</TD>';
    foreach ($sortArray as $col) {
        //if ($col=='ValidatedWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colValidated') . '</TD>' ;
        if ($col=='AssignedWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colAssigned') . '</TD>' ;
        if ($col=='RealWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colReal') . '</TD>' ;
        if ($col=='LeftWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colLeft') . '</TD>' ;
        if ($col=='PlannedWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colReassessed') . '</TD>' ;
        if ($col=='Duration') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colDuration') . '</TD>' ;
        if ($col=='Progress') echo '  <TD class="reportTableHeader" style="width:30px">'  . i18n('colPct') . '</TD>' ;
        if ($col=='StartDate') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colStart') . '</TD>' ;
        if ($col=='EndDate') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colEnd') . '</TD>' ;
        if ($col=='Priority') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colPriority') . '</TD>' ;
        if ($col=='IdPlanningMode') echo '  <TD class="reportTableHeader" style="width:80px">'  . i18n('colIdPlanningMode') . '</TD>' ;
        //if ($col=='Resource') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colResource') . '</TD>' ;
    }
		$weekendColor="#cfcfcf";
		$day=$minDate;
		for ($i=0;$i<$numUnits;$i++) {
			$color="";
			$span=$colUnit;
			if ($format=='month') {
				$tDate = explode("-", $day);
				$date= mktime(0, 0, 0, $tDate[1], $tDate[2]+1, $tDate[0]);
				$title=i18n(date('F', $date));
				$span=numberOfDaysOfMonth($day);
			} else if($format=='week') {
				$title=substr(htmlFormatDate($day),0,5);
			} else if ($format=='day') {
				$color=($openDays[$i]==1)?'':'background-color:' . $weekendColor . ';';
				$title=substr($days[$i],-2);
      } else if ($format=='quarter') {
        $tDate = explode("-", $day);
        $date= mktime(0, 0, 0, $tDate[1], $tDate[2]+1, $tDate[0]);
        $title=substr($day,5,2);
        $span=numberOfDaysOfMonth($day);
			}
			echo '<td class="reportTableColumnHeader" colspan="' . $span . '" style="width:' . $colWidth . 'px;magin:0px;padding:0px;' . $color . '">';
			echo $title . '</td>';
			if ($format=='month') {
				$day=addMonthsToDate($day,1);
      } else if ($format=='quarter') {
        $day=addMonthsToDate($day,1);
			} else {
				$day=addDaysToDate($day,$topUnit);
			}
		}
		echo '</TR>';

		// lines
		$width=round($colWidth/$colUnit) . "px;";
		$collapsedList=Collapsed::getCollaspedList();
		$levelCollpased=0;
		$collapsed=false;
		foreach ($resultArray as $line) {
			$pEnd=$line['pend'];
			$pStart=$line['pstart'];
			$pRealEnd=$line['prealend'];
			$pPlanStart=$line['pplanstart'];
			$realWork=$line['realwork'];
			$plannedWork=$line['plannedwork'];
			$progress=$line['progress'];
			// pGroup : is the tack a group one ?
			$pGroup=($line['reftype']=='Resource' or $line['reftype']=='ResourceTeam' or $line['reftype']=='Project')?1:0;
			$scope='Planning_'.$line['reftype'].'_'.$line['refid'];
			$compStyle="";
			$bgColor="";
			if( $pGroup) {
				$rowType = "group";
				$compStyle="font-weight: bold; background: #E8E8E8;";
				$bgColor="background: #E8E8E8;";
			} else if( $line['reftype']=='Milestone'){
				$rowType  = "mile";
			} else {
				$rowType  = "row";
			}
			$wbs=$line['wbssortable'];
			if ($line['reftype']=='Resource' or $line['reftype']=='ResourceTeam') {
				$level=1;
			} else if ($line['reftype']=='Project') {
				$level=2;
			} else if ($showProject) {
				$level=3;
			} else {
				$level=2;
			}
			if ($collapsed and $collapsedLevel<$level) {
				continue;
			}
			if ($pGroup) {
        $collapsed=false;
        if (array_key_exists($scope, $collapsedList)) {
          $collapsed=true;
          $collapsedLevel=$level;
        }
      }
      if ($collapsed and ! $pGroup) {
        continue;
      }
			$tab="";
			for ($i=1;$i<$level;$i++) {
				$tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
			}
			$pName=($showWbs)?$line['wbs']." ":"";
			$pName.=htmlEncode($line['refname']);
			$duration=($rowType=='mile' or $pStart=="" or $pEnd=="")?'-':workDayDiffDates($pStart, $pEnd) . "&nbsp;" . i18n("shortDay");
			//echo '<TR class="dojoDndItem ganttTask' . $rowType . '" style="margin: 0px; padding: 0px;">';

			echo '<TR style="height:18px;' ;
			echo '">';
			echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '">'.formatIcon($line['reftype'], 16).'</TD>';
			echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '"><span class="nobr">' . $tab ;
		  echo '<span style="width: 16px;height:100%;vertical-align:middle;">';
			if ($pGroup) {
        if ($collapsed) {
          echo '<img style="width:12px" src="../view/css/images/plus.gif" />';
        } else {
          echo '<img style="width:12px" src="../view/css/images/minus.gif" />';
        }
      } else {
        echo '<img style="width:12px" src="../view/css/images/none.gif" />';
      }
      echo '</span>&nbsp;';
      echo $pName . '</span></TD>';
		  foreach ($sortArray as $col) {
          //if ($col=='ValidatedWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . Work::displayWorkWithUnit($line["validatedwork"])  . '</TD>' ;
          if ($col=='AssignedWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["assignedwork"])  . '</TD>' ;
          if ($col=='RealWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["realwork"])  . '</TD>' ;
          if ($col=='LeftWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["leftwork"])  . '</TD>' ;
          if ($col=='PlannedWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["plannedwork"])  . '</TD>' ;
          if ($col=='Duration') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . $duration  . '</TD>' ;
          if ($col=='Progress') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . percentFormatter(round($progress*100)) . '</TD>' ;
          if ($col=='StartDate') echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . (($pStart)?dateFormatter($pStart):'-') . '</TD>' ;
          if ($col=='EndDate') echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . (($pEnd)?dateFormatter($pEnd):'-') . '</TD>' ;
          if ($col=='Priority') echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . $line["priority"] . '</TD>' ;
          if ($col=='IdPlanningMode') echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . $line["planningmode"] . '</TD>' ;
          //if ($col=='Resource') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . $line["resource"]  . '</TD>' ;
      }
      if ($pGroup) {
				$pColor='#505050;';
				$pBackground='background-color:#505050;';
			} else {
					$pColor="#50BB50";
					$pBackground='background-color:#50BB50;';
			}
			$dispCaption=false;
			for ($i=0;$i<$numDays;$i++) {
				$color=$bgColor;
				$noBorder="border-left: 0px;";
				if ($format=='month') {
					$fontSize='90%';
					if ( $i<($numDays-1) and substr($days[($i+1)],-2)!='01' ) {
						$noBorder="border-left: 0px;border-right: 0px;";
					}
        } else  if ($format=='quarter') {
          $fontSize='10%';
          if ( $i<($numDays-1) and substr($days[($i+1)],-2)!='01' ) {
             $noBorder="border-left: 0px;border-right: 0px;";
          }
				} else if($format=='week') {
					$fontSize='90%';
					if ( ( ($i+1) % $colUnit)!=0) {
						$noBorder="border-left: 0px;border-right: 0px;";
					}
				} else if ($format=='day') {
					$fontSize='150%';
					$color=($openDays[$i]==1)?$bgColor:'background-color:' . $weekendColor . ';';
				}
				$height=($pGroup)?'8':'12';
				if ($days[$i]>=$pStart and $days[$i]<=$pEnd) {
					if ($rowType=="mile") {
						echo '<td class="reportTableData" style="font-size: ' . $fontSize . ';' . $color . $noBorder . ';color:' . $pColor . ';">';
						if($progress < 100) {
							echo '&loz;' ;
						} else {
							echo '&diams;' ;
						}
					} else {
						$subHeight=round((18-$height)/2);
						echo '<td class="reportTableData" style="width:' . $width .';padding:0px;' . $color . '; vertical-align: middle;' . $noBorder . '">';
						if ($pGroup and ($days[$i]==$pStart or $days[$i]==$pEnd) and $outMode!='pdf') {
							echo '<div class="ganttTaskgroupBarExtInvisible" style="float:left; height:4px"></div>';
						}
						echo '<table width="100%" >';
						$pBgColor=$pBackground;
						$pHeight=$height;
						$border="";
						if (! $pGroup) {
							if ($days[$i]<=$pRealEnd) {
								$pBgColor="background: #999999;";
							} else if ($days[$i]<$pPlanStart) {
								$pBgColor="";
								$border='border-bottom: 2px solid ' . $pColor . ';';
								$pHeight=$height-2;
							}
						}
						echo '<tr height="' . $pHeight . 'px"><td style="' . $border . ' width:100%; ' . $pBgColor . 'height:' .  $pHeight . 'px;"></td></tr>';
						echo '</table>';
            if ($pGroup and $days[$i]==$pStart and $outMode!='pdf') {
						  echo '<div class="ganttTaskgroupBarExt" style="float:left; height:4px"></div>'
                . '<div class="ganttTaskgroupBarExt" style="float:left; height:3px"></div>'
                . '<div class="ganttTaskgroupBarExt" style="float:left; height:2px"></div>'
                . '<div class="ganttTaskgroupBarExt" style="float:left; height:1px"></div>';
					  }
					  if ($pGroup and $days[$i]==$pEnd and $outMode!='pdf') {
              echo '<div class="ganttTaskgroupBarExt" style="float:right; height:4px"></div>'
                . '<div class="ganttTaskgroupBarExt" style="float:right; height:3px"></div>'
                . '<div class="ganttTaskgroupBarExt" style="float:right; height:2px"></div>'
                . '<div class="ganttTaskgroupBarExt" style="float:right; height:1px"></div>';
            }
						$dispCaption=($showWork)?true:false;
					}
				} else {
					echo '<td class="reportTableData" width="' . $width .'" style="width: ' . $width . $color . $noBorder . '">';
				  if ($days[$i]>$pEnd and $dispCaption) {
              echo '<div style="position: relative; top: 0px; height: 12px;">';
              echo '<div style="position: absolute; top: -1px; left: 1px; height:12px; width:200px;">';
              echo '<div style="clip:rect(-10px,100px,100px,0px); text-align: left">'
                 . Work::displayWorkWithUnit($line['leftwork']) . '</div>';
              echo '</div>';
              echo '</div>';
              $dispCaption=false;
          }
				}
				echo '</td>';
			}
			echo '</TR>';
		}
	}
	echo "</table></div>";
}

function exportGantt($list) {
	$paramDbDisplayName=Parameter::getGlobalParameter('paramDbDisplayName');
  $currency=Parameter::getGlobalParameter('currency');
  $currencyPosition=Parameter::getGlobalParameter('currencyPosition');
	$nl="\n";
	$hoursPerDay=Parameter::getGlobalParameter('dayTime');
	$startDate=date('Y-m-d');
	$startAM=Parameter::getGlobalParameter('startAM') . ':00';
	$endAM=Parameter::getGlobalParameter('endAM') . ':00';
	$startPM=Parameter::getGlobalParameter('startPM') . ':00';
	$endPM=Parameter::getGlobalParameter('endPM') . ':00';
	$name="export_planning_" . date('Ymd_His') . ".xml";
	$now=date('Y-m-d').'T'.date('H:i:s');
	if (array_key_exists('startDate',$_REQUEST)) {
		$startDate=Security::checkValidDateTime($_REQUEST['startDate']);
	}
	$endDate='';
	if (array_key_exists('endDate',$_REQUEST)) {
		$endDate=Security::checkValidDateTime($_REQUEST['endDate']);
	}
	$maxDate = '';
	$minDate = '';
	$resultArray=array();
	if (count($list) > 0) {
		foreach ($list as $line) {
			$pStart="";
			$pStart=(trim($line['initialstartdate'])!="")?$line['initialstartdate']:$pStart;
			$pStart=(trim($line['validatedstartdate'])!="")?$line['validatedstartdate']:$pStart;
			$pStart=(trim($line['plannedstartdate'])!="")?$line['plannedstartdate']:$pStart;
			$pStart=(trim($line['realstartdate'])!="")?$line['realstartdate']:$pStart;
			if (trim($line['plannedstartdate'])!=""
			and trim($line['realstartdate'])!=""
			and $line['plannedstartdate']<$line['realstartdate'] ) {
				$pStart=$line['plannedstartdate'];
			}
			$pEnd="";
			$pEnd=(trim($line['initialenddate'])!="")?$line['initialenddate']:$pEnd;
			$pEnd=(trim($line['validatedenddate'])!="")?$line['validatedenddate']:$pEnd;
			$pEnd=(trim($line['plannedenddate'])!="")?$line['plannedenddate']:$pEnd;
			$pEnd=(trim($line['realenddate'])!="")?$line['realenddate']:$pEnd;
			if ($line['reftype']=='Milestone') {
				$pStart=$pEnd;
			}
			$line['pstart']=$pStart;
			$line['pend']=$pEnd;
			$line['pduration']=workDayDiffDates($pStart,$pEnd);
			$resultArray[]=$line;
			if ($maxDate=='' or $maxDate<$pEnd) {$maxDate=$pEnd;}
			if ($minDate=='' or $minDate>$pStart) {$minDate=$pStart;}
		}
		if ($endDate and $maxDate>$endDate) {
			$maxDate=$endDate;
		}
	}
	$res=New Resource();
	$resourceList=$res->getSqlElementsFromCriteria(array(), false, false, " id asc");

	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $nl;
	echo '<Project>' . $nl;
	echo '<Name>' . $name . '</Name>' . $nl;
	echo '<Title>' .htmlencode($paramDbDisplayName,'xml') . '</Title>' . $nl;
	echo '<CreationDate>' . $now . '</CreationDate>' . $nl;
	echo '<LastSaved>' . $now . '</LastSaved>' . $nl;
	echo '<ScheduleFromStart>1</ScheduleFromStart>' . $nl;
	echo '<StartDate>' . $minDate . 'T00:00:00</StartDate>' . $nl;
	echo '<FinishDate>' . $maxDate . 'T00:00:00</FinishDate>' . $nl;
	echo '<FYStartDate>1</FYStartDate>' . $nl;
	echo '<CriticalSlackLimit>0</CriticalSlackLimit>' . $nl;
	echo '<CurrencyDigits>2</CurrencyDigits>' . $nl;
	echo '<CurrencySymbol>' . $currency . '</CurrencySymbol>' . $nl;
	echo '<CurrencySymbolPosition>' . (($currencyPosition=='before')?'0':'1') . '</CurrencySymbolPosition>' . $nl;
	echo '<CalendarUID>1</CalendarUID>' . $nl;
	echo '<DefaultStartTime>' . $startAM . '</DefaultStartTime>' . $nl;
	echo '<DefaultFinishTime>' . $endPM . '</DefaultFinishTime>' . $nl;
	echo '<MinutesPerDay>' . ($hoursPerDay*60) . '</MinutesPerDay>' . $nl;
	echo '<MinutesPerWeek>' . ($hoursPerDay*60*5) . '</MinutesPerWeek>' . $nl;
	echo '<DaysPerMonth>20</DaysPerMonth>' . $nl;
	echo '<DefaultTaskType>1</DefaultTaskType>' . $nl;
	echo '<DefaultFixedCostAccrual>2</DefaultFixedCostAccrual>' . $nl;
	echo '<DefaultStandardRate>10</DefaultStandardRate>' . $nl;
	echo '<DefaultOvertimeRate>15</DefaultOvertimeRate>' . $nl;
	echo '<DurationFormat>7</DurationFormat>' . $nl;
	echo '<WorkFormat>3</WorkFormat>' . $nl;
	echo '<EditableActualCosts>0</EditableActualCosts>' . $nl;
	echo '<HonorConstraints>0</HonorConstraints>' . $nl;
	// echo '<EarnedValueMethod>0</EarnedValueMethod>' . $nl;
	echo '<InsertedProjectsLikeSummary>0</InsertedProjectsLikeSummary>' . $nl;
	echo '<MultipleCriticalPaths>0</MultipleCriticalPaths>' . $nl;
	echo '<NewTasksEffortDriven>0</NewTasksEffortDriven>' . $nl;
	echo '<NewTasksEstimated>1</NewTasksEstimated>' . $nl;
	echo '<SplitsInProgressTasks>0</SplitsInProgressTasks>' . $nl;
	echo '<SpreadActualCost>0</SpreadActualCost>' . $nl;
	echo '<SpreadPercentComplete>0</SpreadPercentComplete>' . $nl;
	echo '<TaskUpdatesResource>1</TaskUpdatesResource>' . $nl;
	echo '<FiscalYearStart>0</FiscalYearStart>' . $nl;
	echo '<WeekStartDay>1</WeekStartDay>' . $nl;
	echo '<MoveCompletedEndsBack>0</MoveCompletedEndsBack>' . $nl;
	echo '<MoveRemainingStartsBack>0</MoveRemainingStartsBack>' . $nl;
	echo '<MoveRemainingStartsForward>0</MoveRemainingStartsForward>' . $nl;
	echo '<MoveCompletedEndsForward>0</MoveCompletedEndsForward>' . $nl;
	echo '<BaselineForEarnedValue>0</BaselineForEarnedValue>' . $nl;
	echo '<AutoAddNewResourcesAndTasks>1</AutoAddNewResourcesAndTasks>' . $nl;
	echo '<CurrentDate>' . $now . '</CurrentDate>' . $nl;
	echo '<MicrosoftProjectServerURL>1</MicrosoftProjectServerURL>' . $nl;
	echo '<Autolink>1</Autolink>' . $nl;
	echo '<NewTaskStartDate>0</NewTaskStartDate>' . $nl;
	echo '<DefaultTaskEVMethod>0</DefaultTaskEVMethod>' . $nl;
	echo '<ProjectExternallyEdited>0</ProjectExternallyEdited>' . $nl;
	echo '<ExtendedCreationDate>1984-01-01T00:00:00</ExtendedCreationDate>' . $nl;
	echo '<ActualsInSync>0</ActualsInSync>' . $nl;
	echo '<RemoveFileProperties>0</RemoveFileProperties>' . $nl;
	echo '<AdminProject>0</AdminProject>' . $nl;
	echo '<OutlineCodes/>' . $nl;
	echo '<WBSMasks/>' . $nl;
	echo '<ExtendedAttributes/>' . $nl;
	/*<ExtendedAttributes>
	 <ExtendedAttribute>
	 <FieldID>188743731</FieldID>
	 <FieldName>Text1</FieldName>
	 </ExtendedAttribute>
	 </ExtendedAttributes>*/
	echo '<Calendars>' . $nl;
	echo '<Calendar>' . $nl;
	echo '<UID>0</UID>' . $nl;
	echo '<Name>Standard</Name>' . $nl;
	echo '<IsBaseCalendar>1</IsBaseCalendar>' . $nl;
	echo '<BaseCalendarUID>-1</BaseCalendarUID>' . $nl;
	echo '<WeekDays>' . $nl;
	for ($i=1;$i<=7;$i++) {
		echo '<WeekDay>' . $nl;
		echo '<DayType>' . $i . '</DayType>' . $nl;
		if (($i==1 or $i==7)) {
			echo '<DayWorking>0</DayWorking>' . $nl;
		} else {
			echo '<DayWorking>1</DayWorking>' . $nl;
			echo '<WorkingTimes>' . $nl;
			echo '<WorkingTime>' . $nl;
			echo '<FromTime>' . $startAM . '</FromTime>' . $nl;
			echo '<ToTime>' . $endAM . '</ToTime>' . $nl;
			echo '</WorkingTime>' . $nl;
			echo '<WorkingTime>' . $nl;
			echo '<FromTime>' . $startPM . '</FromTime>' . $nl;
			echo '<ToTime>' . $endPM . '</ToTime>' . $nl;
			echo '</WorkingTime>' . $nl;
			echo '</WorkingTimes>' . $nl;
		}
		echo '</WeekDay>' . $nl;
	}
	echo ' </WeekDays>' . $nl;
	echo '</Calendar>' . $nl;
	foreach ($resourceList as $resource) {
		echo "<Calendar>" . $nl;
		echo "<UID>" . ($resource->id) . "</UID>" . $nl;
		echo "<Name>" . ($resource->name) . "</Name>" . $nl;
		echo "<IsBaseCalendar>0</IsBaseCalendar>" . $nl;
		echo "<BaseCalendarUID>0</BaseCalendarUID>" . $nl;
		echo "</Calendar>" . $nl;
	}
	echo '</Calendars>' . $nl;
	echo '<Tasks>' . $nl;
	$cpt=0;
	$arrayTask=array();
	foreach ($resultArray as $line) {
		$cpt++;
		$arrayTask[$line['reftype'].'#'.$line['refid']]=$line['id'];
		$pct=($line['plannedwork']>0)?round(100*$line['realwork']/$line['plannedwork'],0):'';
		echo '<Task>' . $nl;
		echo '<UID>' . $line['id'] . '</UID>' . $nl;
		echo '<ID>' . $cpt . '</ID>' . $nl;  // TODO : should be order of the tack in the list
		echo '<Name>' . htmlEncode($line['refname'],'xml') . '</Name>' . $nl;
		echo '<Type>1</Type>' . $nl; // TODO : 0=Fixed Units, 1=Fixed Duration, 2=Fixed Work.
		echo '<IsNull>0</IsNull>' . $nl;
		echo '<WBS>' . $line['wbs'] . '</WBS>' . $nl;
		echo '<OutlineNumber>' . $line['wbs'] . '</OutlineNumber>' . $nl;
		echo '<OutlineLevel>' . (substr_count($line['wbs'],'.')+1) . '</OutlineLevel>' . $nl;
		echo '<Priority>' . $line['priority'] . '</Priority>' . $nl;
		echo '<Start>' . $line['pstart'] . 'T' . $startAM . '</Start>' . $nl;
		echo '<Finish>' . $line['pend'] . 'T' . $endPM . '</Finish>' . $nl;
		echo '<Duration>' . formatDuration($line['pduration'],$hoursPerDay) . '</Duration>' . $nl;
		echo '<DurationFormat>7</DurationFormat>' . $nl;
		echo '<Work>PT' . round($line['plannedwork']*$hoursPerDay,0) . 'H0M0S</Work>' . $nl;
		echo '<Stop>' . $line['pstart'] . 'T' . $startAM . '</Stop>' . $nl;
		echo '<Resume>' . $line['pstart'] . 'T' . $startAM . '</Resume>' . $nl;
		echo '<ResumeValid>0</ResumeValid>' . $nl;
		echo '<EffortDriven>0</EffortDriven>' . $nl;
		echo '<Recurring>0</Recurring>' . $nl;
		echo '<OverAllocated>0</OverAllocated>' . $nl;
		echo '<Estimated>0</Estimated>' . $nl;
		echo '<Milestone>' . (($line['reftype']=='Milestone')?'1':'0') . '</Milestone>' . $nl;
		echo '<Summary>' . (($line['elementary'])?'0':'1') . '</Summary>' . $nl;
		echo '<Critical>0</Critical>' . $nl;
		echo '<IsSubproject>0</IsSubproject>' . $nl;
		echo '<IsSubprojectReadOnly>0</IsSubprojectReadOnly>' . $nl;
		echo '<ExternalTask>0</ExternalTask>' . $nl;
		echo '<EarlyStart>' . $line['pstart'] . 'T' . $startAM . '</EarlyStart>' . $nl;
		echo '<EarlyFinish>' . $line['pend'] . 'T' . $endPM . '</EarlyFinish>' . $nl;
		echo '<LateStart>' . $line['pstart'] . 'T' . $startAM . '</LateStart>' . $nl;
		echo '<LateFinish>' . $line['pend'] . 'T' . $endPM . '</LateFinish>' . $nl;
		echo '<StartVariance>0</StartVariance>' . $nl;
		echo '<FinishVariance>0</FinishVariance>' . $nl;
		echo '<WorkVariance>0</WorkVariance>' . $nl;
		echo '<FreeSlack>0</FreeSlack>' . $nl;
		echo '<TotalSlack>0</TotalSlack>' . $nl;
		echo '<FixedCost>0</FixedCost>' . $nl;
		echo '<FixedCostAccrual>2</FixedCostAccrual>' . $nl;
		echo '<PercentComplete>' . $pct .'</PercentComplete>' . $nl;
		echo '<PercentWorkComplete>' . $pct .'</PercentWorkComplete>' . $nl;
		echo '<Cost>0</Cost>' . $nl;
		echo '<OvertimeCost>0</OvertimeCost>' . $nl;
		echo '<OvertimeWork>PT0H0M0S</OvertimeWork>' . $nl;
		echo '<ActualStart>' .  $line['pstart'] . 'T' . $startAM . '</ActualStart>' . $nl;
		echo '<ActualDuration>PT0H0M0S</ActualDuration>' . $nl;
		echo '<ActualCost>0</ActualCost>' . $nl;
		echo '<ActualOvertimeCost>0</ActualOvertimeCost>' . $nl;
		echo '<ActualWork>PT' . round($line['realwork']*$hoursPerDay,0) . 'H0M0S</ActualWork>' . $nl;
		echo '<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>' . $nl;
		echo '<RegularWork>PT' . round($line['plannedwork']*$hoursPerDay,0) . 'H0M0S</RegularWork>' . $nl;
		echo '<RemainingDuration>PT' .  round($line['plannedduration']*$hoursPerDay,0) . 'H0M0S</RemainingDuration>' . $nl;
		echo '<RemainingCost>0</RemainingCost>' . $nl;
		echo '<RemainingWork>PT' . round($line['leftwork']*$hoursPerDay,0) . 'H0M0S</RemainingWork>' . $nl;
		echo '<RemainingOvertimeCost>0</RemainingOvertimeCost>' . $nl;
		echo '<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>' . $nl;
		echo '<ACWP>0</ACWP>' . $nl;
		echo '<CV>0</CV>' . $nl;
		echo '<ConstraintType>' . (($line['elementary'])?'0':'0') . '</ConstraintType>' . $nl;
		echo '<CalendarUID>-1</CalendarUID>' . $nl;
		if ($line['elementary']) { echo '<ConstraintDate>' . $line['pstart'] . 'T' . $startAM . '</ConstraintDate>' . $nl;}
		echo '<LevelAssignments>0</LevelAssignments>' . $nl;
		echo '<LevelingCanSplit>1</LevelingCanSplit>' . $nl;
		echo '<LevelingDelay>0</LevelingDelay>' . $nl;
		echo '<LevelingDelayFormat>8</LevelingDelayFormat>' . $nl;
		echo '<IgnoreResourceCalendar>0</IgnoreResourceCalendar>' . $nl;
		echo '<HideBar>0</HideBar>' . $nl;
		echo '<Rollup>0</Rollup>' . $nl;
		echo '<BCWS>0</BCWS>' . $nl;
		echo '<BCWP>0</BCWP>' . $nl;
		echo '<PhysicalPercentComplete>0</PhysicalPercentComplete>' . $nl;
		echo '<EarnedValueMethod>0</EarnedValueMethod>' . $nl;
		/*<ExtendedAttribute>
		 <FieldID>188743731</FieldID>
		 <Value>lmk</Value>
		 </ExtendedAttribute>*/
		echo '<Active>1</Active>' . $nl;
		echo '<Manual>1</Manual>' . $nl;
		echo '<ActualWorkProtected>PT0H0M0S</ActualWorkProtected>' . $nl;
		echo '<ActualOvertimeWorkProtected>PT0H0M0S</ActualOvertimeWorkProtected>' . $nl;
		$crit=array('successorId'=>$line['id']);
		$d=new Dependency();
		$depList=$d->getSqlElementsFromCriteria($crit,false);
		foreach ($depList as $dep) {
			echo '<PredecessorLink>' . $nl;
			echo '<PredecessorUID>' . ($dep->predecessorId) . '</PredecessorUID>' . $nl;
			echo '<Type>1</Type>' . $nl;
			echo '<CrossProject>0</CrossProject>' . $nl;
			echo '<LinkLag>0</LinkLag>' . $nl;
			echo '<LagFormat>7</LagFormat>' . $nl;
			echo '</PredecessorLink>' . $nl;
		}
		echo '</Task>' . $nl;
	}
	echo '</Tasks>' . $nl;
	$arrayRessource=array();
	echo '<Resources>' . $nl;
	foreach ($resourceList as $resource) {
		$arrayResource[$resource->id]=$resource;
		echo "<Resource>" . $nl;
		echo "<UID>" . ($resource->id) . "</UID>" . $nl;
		echo "<ID>" . ($resource->id) . "</ID>" . $nl;
		echo "<Name>" . ($resource->name) . "</Name>" . $nl;
		echo "<Type>1</Type>" . $nl;
		echo "<IsNull>0</IsNull>" . $nl;
		echo "<Initials>" . ($resource->initials) . "</Initials>" . $nl;
		echo "<Group>" . SqlList::getNameFromId('Team',$resource->idTeam) . "</Group>" . $nl;
		echo "<WorkGroup>0</WorkGroup>" . $nl;
		echo "<EmailAddress>" . ($resource->email) . "</EmailAddress>" . $nl;
		echo "<MaxUnits>" . ($resource->capacity) . "</MaxUnits>" . $nl;
		echo "<PeakUnits>0</PeakUnits>" . $nl;
		echo "<OverAllocated>0</OverAllocated>" . $nl;
		echo "<CanLevel>1</CanLevel>" . $nl;
		echo "<AccrueAt>3</AccrueAt>" . $nl;
		echo "<Work>PT0H0M0S</Work>" . $nl;
		echo "<RegularWork>PT0H0M0S</RegularWork>" . $nl;
		echo "<OvertimeWork>PT0H0M0S</OvertimeWork>" . $nl;
		echo "<ActualWork>PT0H0M0S</ActualWork>" . $nl;
		echo "<RemainingWork>PT0H0M0S</RemainingWork>" . $nl;
		echo "<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>" . $nl;
		echo "<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>" . $nl;
		echo "<PercentWorkComplete>0</PercentWorkComplete>" . $nl;
		$rate=0;
		$critCost=array('idResource'=>$resource->id, 'endDate'=>null);
		$rc=new ResourceCost();
		$rcList=$rc->getSqlElementsFromCriteria($critCost, false, null, ' startDate desc');
		if (count($rcList)>0) {
			$rate=($hoursPerDay)?round($rcList[0]->cost / $hoursPerDay,2):0;

		}
		echo "<StandardRate>" . $rate . "</StandardRate>" . $nl;
		echo "<StandardRateFormat>3</StandardRateFormat>" . $nl;
		echo "<Cost>0</Cost>" . $nl;
		echo "<OvertimeRate>0</OvertimeRate>" . $nl;
		echo "<OvertimeRateFormat>3</OvertimeRateFormat>" . $nl;
		echo "<OvertimeCost>0</OvertimeCost>" . $nl;
		echo "<CostPerUse>0</CostPerUse>" . $nl;
		echo "<ActualCost>0</ActualCost>" . $nl;
		echo "<ActualOvertimeCost>0</ActualOvertimeCost>" . $nl;
		echo "<RemainingCost>0</RemainingCost>" . $nl;
		echo "<RemainingOvertimeCost>0</RemainingOvertimeCost>" . $nl;
		echo "<WorkVariance>0</WorkVariance>" . $nl;
		echo "<CostVariance>0</CostVariance>" . $nl;
		echo "<SV>0</SV>" . $nl;
		echo "<CV>0</CV>" . $nl;
		echo "<ACWP>0</ACWP>" . $nl;
		echo "<CalendarUID>" . ($resource->id) . "</CalendarUID>" . $nl;
		echo "<BCWS>0</BCWS>" . $nl;
		echo "<BCWP>0</BCWP>" . $nl;
		echo "<IsGeneric>0</IsGeneric>" . $nl;
		echo "<IsInactive>0</IsInactive>" . $nl;
		echo "<IsEnterprise>0</IsEnterprise>" . $nl;
		echo "<BookingType>0</BookingType>" . $nl;
		echo "<ActualWorkProtected>PT0H0M0S</ActualWorkProtected>" . $nl;
		echo "<ActualOvertimeWorkProtected>PT0H0M0S</ActualOvertimeWorkProtected>" . $nl;
		echo "<CreationDate></CreationDate>" . $nl;
		echo "</Resource>" . $nl;
	}
	echo "</Resources>" . $nl;
	$ass=new Assignment();
	$clauseWhere="";
	$lstAss=$ass->getSqlElementsFromCriteria(null, false, $clauseWhere, null, false);
	echo '<Assignments>' . $nl;
	foreach ($lstAss as $ass) {
		if (array_key_exists($ass->refType . '#' . $ass->refId, $arrayTask)) {
			$res=$arrayResource[$ass->idResource];
			echo "<Assignment>" . $nl;
			echo "<UID>" . ($ass->id) . "</UID>" . $nl;
			echo "<TaskUID>" . $arrayTask[$ass->refType . '#' . $ass->refId] . "</TaskUID>" . $nl;
			echo "<ResourceUID>" . ($ass->idResource) . "</ResourceUID>" . $nl;
			//echo "<PercentWorkComplete>' (($ass->plannedWork)?round($ass->realWork/$ass->plannedWork*100,0):'0') . '</PercentWorkComplete>" . $nl;
			//echo "<ActualCost>0</ActualCost>" . $nl;
			//echo "<ActualOvertimeCost>0</ActualOvertimeCost>" . $nl;
			//echo "<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>" . $nl;
			echo "<ActualStart>" . ($ass->plannedStartDate) . "T" . $startAM . "</ActualStart>" . $nl;
			//echo "<ActualWork>PT0H0M0S</ActualWork>" . $nl;
			//echo "<ACWP>0</ACWP>" . $nl;
			//echo "<Confirmed>0</Confirmed>" . $nl;
			//echo "<Cost>0</Cost>" . $nl;
			//echo "<CostRateTable>0</CostRateTable>" . $nl;
			//echo "<CostVariance>0</CostVariance>" . $nl;
			//echo "<CV>0</CV>" . $nl;
			//echo "<Delay>0</Delay>" . $nl;
			echo "<Finish>" . ($ass->plannedEndDate) . "T" . $endPM . "</Finish>" . $nl;
			//echo "<FinishVariance>0</FinishVariance>" . $nl;
			//echo "<WorkVariance>0</WorkVariance>" . $nl;
			//echo "<HasFixedRateUnits>1</HasFixedRateUnits>" . $nl;
			//echo "<FixedMaterial>0</FixedMaterial>" . $nl;
			//echo "<LevelingDelay>0</LevelingDelay>" . $nl;
			//echo "<LevelingDelayFormat>7</LevelingDelayFormat>" . $nl;
			//echo "<LinkedFields>0</LinkedFields>" . $nl;
			//echo "<Milestone>0</Milestone>" . $nl;
			//echo "<Overallocated>0</Overallocated>" . $nl;
			//echo "<OvertimeCost>0</OvertimeCost>" . $nl;
			//echo "<OvertimeWork>PT0H0M0S</OvertimeWork>" . $nl;
			echo "<RegularWork>PT" . $ass->plannedWork*$hoursPerDay . "H0M0S</RegularWork>" . $nl;
			//echo "<RemainingCost>0</RemainingCost>" . $nl;
			//echo "<RemainingOvertimeCost>0</RemainingOvertimeCost>" . $nl;
			//echo "<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>" . $nl;
			echo "<RemainingWork>PT" . $ass->leftWork*$hoursPerDay ."H0M0S</RemainingWork>" . $nl;
			//echo "<ResponsePending>0</ResponsePending>" . $nl;
			//echo "<Start>2011-11-17T08:00:00</Start>" . $nl;
			//echo "<Stop>2011-11-17T08:00:00</Stop>" . $nl;
			//echo "<Resume>2011-11-17T08:00:00</Resume>" . $nl;
			//echo "<StartVariance>0</StartVariance>" . $nl;
			echo "<Units>" . round(($res->capacity * $ass->rate / 100),1) . "</Units>" . $nl;
			//echo "<UpdateNeeded>0</UpdateNeeded>" . $nl;
			//echo "<VAC>0</VAC>" . $nl;
			echo "<Work>PT" . $ass->plannedWork*$hoursPerDay . "H0M0S</Work>" . $nl;
			//echo "<WorkContour>0</WorkContour>" . $nl;
			//echo "<BCWS>0</BCWS>" . $nl;
			//echo "<BCWP>0</BCWP>" . $nl;
			//echo "<BookingType>0</BookingType>" . $nl;
			//echo "<ActualWorkProtected>PT0H0M0S</ActualWorkProtected>" . $nl;
			//echo "<ActualOvertimeWorkProtected>PT0H0M0S</ActualOvertimeWorkProtected>" . $nl;
			//echo "<CreationDate>2011-11-18T21:06:00</CreationDate>" . $nl;
			//echo "<TimephasedData>" . $nl;
			//echo "<Type>1</Type>" . $nl;
			//echo "<UID>1</UID>" . $nl;
			//echo "<Start>" . ($ass->plannedStartDate) . "T08:00:00</Start>" . $nl;
			//echo "<Finish>" . ($ass->plannedEndDate) . "T08:00:00</Finish>" . $nl;
			//echo "<Unit>2</Unit>" . $nl;
			//echo "<Value>PT8H0M0S</Value>" . $nl;
			//echo "</TimephasedData>" . $nl;
			echo "</Assignment>" . $nl;
		}
	}
	echo "</Assignments>" . $nl;
	echo '</Project>' . $nl;
}

function formatDuration($duration, $hoursPerDay) {
	$hourDuration=$duration*$hoursPerDay;
	$res = 'PT' . $hourDuration . 'H0M0S';
	return $res;
}
?>