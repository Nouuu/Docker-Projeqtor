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
  
  scriptLog('   ->/tool/jsonPlanning.php');
  SqlElement::$_cachedQuery['Project']=array();
  SqlElement::$_cachedQuery['Ticket']=array();
  SqlElement::$_cachedQuery['Activity']=array();
  SqlElement::$_cachedQuery['Resource']=array();
  SqlElement::$_cachedQuery['PlanningElement']=array();
  
  $global=RequestHandler::getBoolean('global');
  $objectClass='PlanningElement';
  $columnsDescription=Parameter::getPlanningColumnDescription();
  $obj=new $objectClass();
  $table=$obj->getDatabaseTableName();
  $displayResource=Parameter::getUserParameter('displayResourcePlan');
  if (!$displayResource) $displayResource="initials";
  $print=false;
  if ( array_key_exists('print',$_REQUEST) ) {
    $print=true;
    include_once('../tool/formatter.php');
  }
  $saveDates=false;
  if ( array_key_exists('listSaveDates',$_REQUEST) ) {
    $saveDates=true;
  }
  if (! isset($portfolio)) {
    $portfolio=false;
  }
  if ( array_key_exists('portfolio',$_REQUEST) ) {
    $portfolio=true;
  }
  $showResource=false;
  if ( array_key_exists('showResource',$_REQUEST) ) {
    $showResource=true;
  }
  $plannableProjectsList=getSessionUser()->getListOfPlannableProjects();
  $startDate="";
  $endDate="";
  if (array_key_exists('startDatePlanView',$_REQUEST) and array_key_exists('endDatePlanView',$_REQUEST)) {
    $startDate=trim($_REQUEST['startDatePlanView']);
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
  $baselineTop=null;
  if (array_key_exists('selectBaselineTop',$_REQUEST)) {
    $baselineTop=trim($_REQUEST['selectBaselineTop']);
  } else {
    $baselineTop=trim(getSessionValue('planningBaselineTop'));
  }
  $baselineBottom=null;
  if (array_key_exists('selectBaselineBottom',$_REQUEST)) {
    $baselineBottom=trim($_REQUEST['selectBaselineBottom']);
  } else {
    $baselineBottom=trim(getSessionValue('planningBaselineBottom'));
  }
  // Header
  if (array_key_exists('outMode', $_REQUEST) && $_REQUEST['outMode'] == 'csv') {
    $outMode = 'csv';
  } else if (! isset($outMode) ) {
    $outMode = "html";
  }
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
      if(! RequestHandler::getValue("format")){
          echo '<div style="background: #FFDDDD;font-size:150%;margin-top:20px;color:#808080;text-align:center;padding:20px">';
          echo i18n('messageNoData',array(i18n('colFormat'))); // TODO i18n message
          echo '</div>';
          exit;
      }
		  Security::checkValidPeriodScale(trim($_REQUEST['format']));
      $headerParameters.= i18n("colFormat") . ' : ' . i18n($_REQUEST['format']) . '<br/>';
    }
    if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
      Security::checkValidId(trim($_REQUEST['idProject']));
      $headerParameters.= i18n("colIdProject") . ' : ' . (SqlList::getNameFromId('Project', $_REQUEST['idProject'])) . '<br/>';
    }
  	if($outMode == 'csv') {
        include "../report/headerFunctions.php";
    } else {
  	  include "../report/header.php";
  	}
  }
  if (! isset($outMode)) { $outMode=""; }

  $showIdleProjects=(sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;
  
  $showIdle=true;
  if (array_key_exists('idle',$_REQUEST)) {
    $showIdle=true;
  }
  if ($portfolio) {
  	$accessRightRead=securityGetAccessRight('menuProject', 'read');
  } else {
    $accessRightRead=securityGetAccessRight('menuActivity', 'read');
  }
  if ( ! ( $accessRightRead!='ALL' or (sessionValueExists('project') and getSessionValue('project')!='*'))
   and ( ! array_key_exists('idProject',$_REQUEST) or trim($_REQUEST['idProject'])=="") and !$portfolio and strpos(getSessionValue('project'), ",") === null) {
      $listProj=explode(',',getVisibleProjectsList(! $showIdleProjects));
      if (count($listProj)-1 > Parameter::getGlobalParameter('maxProjectsToDisplay')) {
        echo i18n('selectProjectToPlan');
        return;
      }
  }
  $querySelect = '';
  $queryFrom='';
  $queryWhere='';
  $queryOrderBy='';
  $idTab=0;
  if (! array_key_exists('idle',$_REQUEST) ) {
    $queryWhere= $table . ".idle=0 ";
  }
  $queryWhere.= ($queryWhere=='')?'':' and ';
  if ($portfolio) {
  	$queryWhere.='( ('.getAccesRestrictionClause('Project',$table).')';
  	$queryWhere.=' OR ('.getAccesRestrictionClause('Milestone',$table,$showIdleProjects).') )';
  } else {
    if ($global) $queryWhere.="(1=1)"; // on GlobalPlanning, restriction on acces is applied on query in the FROM 
    else $queryWhere.=getAccesRestrictionClause('Activity',$table,$showIdleProjects);
  }
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
// MTY - LEAVE SYSTEM
   // If it's not the leave system project and portfolio
// PBE : do not show Leave System Project on Portfolio : it's not a real project
//   if ($portfolio and isLeavesSystemActiv()) {
//       $queryWhere.=  "(".$table . ".idProject not in " . Project::getAdminitrativeProjectList() ;      
//       $queryWhere.=  " OR ".$table . ".idProject = " . ((Project::getLeaveProjectId())?Project::getLeaveProjectId():0).") " ;      
//   } else {
  $queryWhere.=  $table . ".idProject not in " . Project::getAdminitrativeProjectList() ;
//   }
// MTY - LEAVE SYSTEM
  $querySelect .= $table . ".* ";
  $queryOrderBy .= $table . ".wbsSortable ";
  if ($global) {
    $queryFrom .= GlobalPlanningElement::getTableNameQuery() .' as '. $table;
  } else {
    $queryFrom .= $table;
  }

  $showMilestone=false;
  if ($portfolio) {
  	$queryWhere.=' and ( refType=\'Project\' ';
    if (array_key_exists('showMilestone',$_REQUEST) ) {
      $showMilestone=trim($_REQUEST['showMilestone']);
    } else if (array_key_exists('listShowMilestone',$_REQUEST) ) {
      $showMilestone=trim($_REQUEST['listShowMilestone']);
    } else {
  	  $showMilestoneObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowMilestone'));
      $showMilestone=trim($showMilestoneObj->parameterValue);
    }
    if ($showMilestone) {
  	  $queryWhere.=' or refType=\'Milestone\' ';
    }
  	$queryWhere.=')';
  }
  // Retreive baseline info
  $arrayBase=array();
  $arrayBase['top']=array();
  $arrayBase['bottom']=array();
  $arrayBase['list']=array();
  if ($baselineTop) $arrayBase['list']['top']=$baselineTop;
  if ($baselineBottom) $arrayBase['list']['bottom']=$baselineBottom;
  $peb=new PlanningElementBaseline();
  $pebTable=$peb->getDatabaseTableName();
  foreach ($arrayBase['list'] as $pos=>$id) {
    $query='select refType as itemtype, refId as itemid,' 
    . ' coalesce(plannedStartDate,validatedStartDate,initialStartDate) as startdate,'
    . ' coalesce(plannedEndDate,validatedEndDate,initialEndDate) as enddate'
    . ' from ' . $pebTable
    . ' where ' . str_replace('planningelement','planningelementbaseline',$queryWhere) . ' and idBaseline='.Sql::fmtId($id)
    . ' order by ' . str_replace('planningelement','planningelementbaseline',$queryOrderBy);
    $resBase=Sql::query($query);
   while ($base = Sql::fetchLine($resBase)) {
     if ($base['startdate'] and $base['enddate'] and $base['itemtype'] and $base['itemid']) {
       $arrayBase[$pos][$base['itemtype'].'_'.$base['itemid']]=array('start'=>$base['startdate'],'end'=>$base['enddate']);
     }
   }
  }
  
  // Apply restrictions on Filter
  $act=new Activity();
  $pe=new PlanningElement();
  $peTable=$pe->getDatabaseTableName();
  $actTable=$act->getDatabaseTableName();
  $querySelectAct="$actTable.id as id, pet.wbsSortable as wbs";
  $queryFromAct="$actTable left join $peTable as pet on (pet.refType='Activity' and pet.refId=$actTable.id)";
  $queryWhereAct="1=1 ";
  $queryOrderByAct="$actTable.id asc";
  $applyFilter=false;
  $arrayFilter=jsonGetFilterArray('Planning', false);
  $arrayRestrictWbs=array();
  $cpt=0;
  if (count($arrayFilter)>0 and ! $portfolio and !$global) {
    $applyFilter=true;
    jsonBuildWhereCriteria($querySelectAct,$queryFromAct,$queryWhereAct,$queryOrderByAct,$cpt,$arrayFilter,$act);
    $queryAct='select ' . $querySelectAct
    . ' from ' . $queryFromAct
    . ' where ' . $queryWhereAct
    . ' order by ' . $queryOrderByAct;
    $resultAct=Sql::query($queryAct);
    while ($line = Sql::fetchLine($resultAct)) {
      //$arrayRestrictWbs[$line['wbs']]=$line['id'];
      $wbsExplode=explode('.',$line['wbs']);
      $wbsParent="";
      foreach ($wbsExplode as $wbsTemp) {
        $wbsParent=$wbsParent.(($wbsParent)?'.':'').$wbsTemp;
        if (!isset($arrayRestrictWbs[$wbsParent])) {
          $arrayRestrictWbs[$wbsParent]=$line['id'];
        } else {
          //$arrayRestrictWbs[$wbsParent].=','.$line['id'];
        }
      }
    }
    ksort($arrayRestrictWbs);
  }
  // constitute query and execute
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query='SELECT ' . $querySelect
       . "\n FROM " . $queryFrom
       . "\n WHERE " . $queryWhere
       . "\n ORDER BY " . $queryOrderBy;
  $result=Sql::query($query);
  if (isset($debugJsonQuery) and $debugJsonQuery) { // Trace in configured to
     debugTraceLog("jsonPlanning: ".$query); // Trace query
     debugTraceLog("  => error (if any) = ".Sql::$lastQueryErrorCode.' - '.Sql::$lastQueryErrorMessage);
     debugTraceLog("  => number of lines returned = ".Sql::$lastQueryNbRows);
  }
  $nbQueriedRows=Sql::$lastQueryNbRows;
  
  if ($applyFilter and count($arrayRestrictWbs)==0) {
    $nbQueriedRows=0;
  }
    
  $nbRows=0;
  if ($print) {
    if ( array_key_exists('report',$_REQUEST) ) {
      $test=array();
      if ($nbQueriedRows > 0) $test[]="OK";
      if (checkNoData($test))  exit;
    }
    if ($outMode=='mpp') {
    	exportGantt($result);
    } else {
    	displayGantt($result);
    }
  } else {
    // return result in json format
    $na=Parameter::getUserParameter('notApplicableValue');
    $na=trim($na,"'");
    if (!$na) $na=null;
    $arrayObj=array();
    $rootWbsArray=array();
    $d=new Dependency();
    echo '{"identifier":"id",' ;
    echo ' "items":[';
    if ($nbQueriedRows > 0) {
    	$collapsedList=Collapsed::getCollaspedList();
    	$topProjectArray=array();
      while ($line = Sql::fetchLine($result)) {
        $line["health"]='';
        $line["quality"]='';
        $line["trend"]='';
        $line["overallprogress"]='';
      	$line=array_change_key_case($line,CASE_LOWER);
      	if ($applyFilter and !isset($arrayRestrictWbs[$line['wbssortable']])) continue; // Filter applied and item is not selected and not a parent of selected
      	if ($line['id'] and !$line['refname']) { // If refName not set, delete corresponding PE (results from incorrect delete
      	  $peDel=new PlanningElement($line['id'],true);
      	  $peDel->delete();
      	  continue;
      	}
        if ($line['reftype']=='Milestone' and $portfolio and $showMilestone and $showMilestone!='all' ) {   
          $mile=new Milestone($line['refid'],true);
          if ($mile->idMilestoneType!=$showMilestone) {
          	continue;
          }
        }
        if ($portfolio and $line["reftype"]=="Milestone" and $line["topreftype"]!='Project' && !isset($topProjectArray[$line['idproject']]) ) { // Case project is closed containing non closed Milestone
          continue;
        }
        echo (++$nbRows>1)?',':'';
        echo  '{';
        $nbFields=0;
        $idPe="";
        // NEW
        if (isset($line['isglobal']) and $line['isglobal']==1 and $line['progress']==$na) {
          // If real is set, start must be lower...
          if (trim($line['realenddate']) and $line['realenddate']!=$na) {
            if (trim($line['realstartdate']) and $line['realstartdate']!=$na and $line['realstartdate']>$line['realenddate']) {
              $line['realstartdate']=$line['realenddate'];
            }
            if (trim($line['plannedstartdate']) and $line['plannedstartdate']!=$na and $line['plannedstartdate']>$line['realenddate']) {
              $line['plannedstartdate']=$line['realenddate'];
            }
            if (trim($line['plannedenddate']) and $line['plannedenddate']!=$na and $line['plannedenddate']>$line['realenddate']) {
              $line['plannedenddate']=$line['realenddate'];
            }
          }
          if ($line['reftype']=='Ticket' and $line['validatedwork']>0 and $line['realwork']>0) {// Ticket by work
            if (trim($line['realenddate'])!="" and $line['realenddate']!=$na and $line['leftwork']==0) {
              $line['progress']='100';
            } else {
              $line['progress']=round(100*$line['realwork']/$line['validatedwork'],0);
            }
          } else if (trim($line['realenddate']) and $line['realenddate']!=$na) { // is started, so try and get progress from duration
            $line['progress']='100';
          } else if (trim($line['realstartdate']) and $line['realstartdate']!=$na) { // is started, so try and get progress from duration 
            $pStart="";
            $pStart=(trim($line['initialstartdate'])!="" and $line['initialstartdate']!=$na)?$line['initialstartdate']:$pStart;
            $pStart=(trim($line['validatedstartdate'])!="" and $line['validatedstartdate']!=$na)?$line['validatedstartdate']:$pStart;
            $pStart=(trim($line['plannedstartdate'])!="" and $line['plannedstartdate']!=$na)?$line['plannedstartdate']:$pStart;
            $pStart=(trim($line['realstartdate']!="" and $line['realstartdate']!=$na)!="")?$line['realstartdate']:$pStart;
            if (trim($line['plannedstartdate'])!="" and $line['plannedstartdate']!=$na 
            and trim($line['realstartdate'])!="" and $line['realstartdate']!=$na
            and $line['plannedstartdate']<$line['realstartdate'] ) {
              $pStart=$line['plannedstartdate'];
            }
            $pEnd="";
            $pEnd=(trim($line['initialenddate'])!="" and $line['initialenddate']!=$na)?$line['initialenddate']:$pEnd;
            $pEnd=(trim($line['validatedenddate'])!="" and $line['validatedenddate']!=$na)?$line['validatedenddate']:$pEnd;
            $pEnd=(trim($line['plannedenddate'])!="" and $line['plannedenddate']!=$na)?$line['plannedenddate']:$pEnd;
            $pEnd=(trim($line['realenddate'])!="" and $line['realenddate']!=$na)?$line['realenddate']:$pEnd;
            //if ($pEnd=="") {$pEnd=date('Y-m-d');}
            if ($line['reftype']=='Milestone') {
              $pStart=$pEnd;
            }
            $pStart=substr($pStart,0,10);
            $pEnd=substr($pEnd,0,10);
            if ($line['reftype']=='Decision') {
              if ($line['done']==1) $line['progress']='100';
              else $line['progress']='0';
            } else if (trim($line['realenddate'])!="" and $line['realenddate']!=$na) {
              $line['progress']='100';
            } else if ($pStart==$pEnd) {
              $line['progress']='50';
            } else {
              $taskLength=dayDiffDates($pStart,$pEnd)+1;
              if ($taskLength>0) {
                $progressLength=dayDiffDates($pStart,date('Y-m-d'))+1;
                $line['progress']=round($progressLength/$taskLength*100,0);
              } else {
                $line['progress']='50';
              }
            }
          } else {
            $line['progress']='0';
          }
          if (intval($line['progress'])>100) $line['progress']='100';
          if (strpos($line['wbs'],'._#')!==false) {
            $rootWbs=substr($line['wbs'],0,strpos($line['wbs'],'._#'));
            if (! isset($rootWbsArray[$rootWbs])) {
              $rootWbsSortable=formatSortableWbs($rootWbs);
              $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('wbsSortable'=>$rootWbsSortable));
              $topId=$pe->id;
              //$max=substr($pe->getMaxValueFromCriteria('wbsSortable',array('topId'=>$topId)),-3);
              //$rootWbsArray[$rootWbs]=array('topId'=>$topId,'val'=>$max);
              $rootWbsArray[$rootWbs]=array('topId'=>$topId,'val'=>0);
            }
            $rootWbsArray[$rootWbs]['val']+=1;
            $wbsVal=$rootWbsArray[$rootWbs]['val'];
            $line['wbs']=$rootWbs.'._'.$wbsVal;
            $line['wbs']=""; // Hide WBS
            $line['wbssortable']=formatSortableWbs($line['wbs']);
            $line['topid']= $rootWbsArray[$rootWbs]['topId'];
          }
        } else if ($line["plannedwork"]>0 and $line["leftwork"]==0 and $line["elementary"]==1 ) {
        	$line["plannedstartdate"]='';
        	$line["plannedenddate"]='';
        }
        if (! $line["plannedduration"] and $line["validatedduration"]) { // Initialize planned duration to validated
          if (!$line["plannedstartdate"]) $line["plannedstartdate"]=($line["validatedstartdate"])?$line["validatedstartdate"]:date('Y-m-d');
          $line["plannedduration"]=$line["validatedduration"];
          $line["plannedenddate"]=addWorkDaysToDate($line["plannedstartdate"], $line["validatedduration"]);
        }
        $line["validatedworkdisplay"]=($line["validatedwork"]==$na)?$na:Work::displayWorkWithUnit($line["validatedwork"]);
        $line["assignedworkdisplay"]=($line["assignedwork"]==$na)?$na:Work::displayWorkWithUnit($line["assignedwork"]);
        $line["realworkdisplay"]=($line["realwork"]==$na)?$na:Work::displayWorkWithUnit($line["realwork"]);
        $line["leftworkdisplay"]=($line["leftwork"]==$na)?$na:Work::displayWorkWithUnit($line["leftwork"]);
        $line["plannedworkdisplay"]=($line["plannedwork"]==$na)?$na:Work::displayWorkWithUnit($line["plannedwork"]);
        $line["validatedcostdisplay"]=($line["validatedcost"]==$na)?$na:htmlDisplayCurrency($line["validatedcost"],true);
        $line["assignedcostdisplay"]=($line["assignedcost"]==$na)?$na:htmlDisplayCurrency($line["assignedcost"],true);
        $line["realcostdisplay"]=($line["realcost"]==$na)?$na:htmlDisplayCurrency($line["realcost"],true);
        $line["leftcostdisplay"]=($line["leftcost"]==$na)?$na:htmlDisplayCurrency($line["leftcost"],true);
        $line["plannedcostdisplay"]=($line["plannedcost"]==$na)?$na:htmlDisplayCurrency($line["plannedcost"],true);
        if ($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1 or ($columnsDescription['IdHealthStatus']['show']==1 and $portfolio)
              or ($columnsDescription['QualityLevel']['show']==1 and $portfolio ) or ($columnsDescription['IdTrend']['show']==1 and $portfolio) or ($columnsDescription['IdOverallProgress']['show']==1 and $portfolio)) {
          $ref=$line['reftype'];
          $type='id'.$ref.'Type';
          $item=new $ref($line['refid'],true);
          if($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1){
            if (isset($line["idstatus"]) and isset($line["idtype"]) and $line["idstatus"]) {
              $line["status"]=SqlList::getNameFromId('Status',$line["idstatus"])."#split#".SqlList::getFieldFromId('Status',$line["idstatus"],'color');
              $line["type"]=SqlList::getNameFromId('Type',$line["idtype"]);
            } else {
              $line["status"]=(property_exists($item,'idStatus'))?SqlList::getNameFromId('Status',$item->idStatus)."#split#".SqlList::getFieldFromId('Status',$item->idStatus,'color'):null;
              $line["type"]=(property_exists($item,$type))?SqlList::getNameFromId('Type',$item->$type):null;
            }
          }
          if($columnsDescription['IdHealthStatus']['show']==1 and $portfolio){
            $line["health"]=(property_exists($item,'idHealth'))?SqlList::getNameFromId('Health',$item->idHealth)."#split#".SqlList::getFieldFromId('Health',$item->idHealth,'color'):null;
          }
          if($columnsDescription['QualityLevel']['show']==1 and $portfolio ){
            $line["quality"]=(property_exists($item,'idQuality'))?SqlList::getNameFromId('Quality',$item->idQuality)."#split#".SqlList::getFieldFromId('Quality',$item->idQuality,'color'):null;
          }
           if($columnsDescription['IdTrend']['show']==1 and $portfolio ){
            $line["trend"]=(property_exists($item,'idTrend'))?SqlList::getNameFromId('Trend',$item->idTrend)."#split#".SqlList::getFieldFromId('Trend',$item->idTrend,'color'):null;
          }
          if($columnsDescription['IdOverallProgress']['show']==1 and $portfolio ){
            $line["overallprogress"]=(property_exists($item,'idOverallProgress'))?SqlList::getNameFromId('OverallProgress',$item->idOverallProgress):null;
          }
        }
        $line["planningmode"]=SqlList::getNameFromId('PlanningMode',$line['idplanningmode']);
        if ($line["reftype"]=="Project") {
        	$topProjectArray[$line['refid']]=$line['id'];
        	$proj=new Project($line["refid"],true);
        	if ($proj->isUnderConstruction) {
        	  $line['reftype']='Construction';
        	}
        	if ($proj->fixPlanning) {
        	  $line['reftype']='Fixed';
        	} else if ( ! isset($plannableProjectsList[$line["refid"]]) ) {
        	  $line['reftype']='Fixed';
        	} else if ($line["needreplan"]) {
        	  $line['reftype']='Replan';
        	}
        } else if ($portfolio and $line["reftype"]=="Milestone" and $line["topreftype"]!='Project') {
          if (! isset($topProjectArray[$line['idproject']])) { // Case project is closed containing non closed Milestone
            $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>'Project','refId'=>$line['idproject']));
            $topProjectArray[$line['idproject']]=$pe->id;
          }
          $line["topid"]=$topProjectArray[$line['idproject']];
        }
        foreach ($line as $id => $val) {
          if ($val==null) {$val=" ";}
          if ($val=="") {$val=" ";}
          echo (++$nbFields>1)?',':'';
          //echo '"' . htmlEncode($id) . '":"' . htmlEncodeJson(($val)) . '"';
          if ($id=='refname' or $id=='resource') {
          	$val=htmlEncode(htmlEncodeJson($val));
          } else {
          	$val=htmlEncodeJson($val);
          }
          echo '"' . htmlEncode($id) . '":"' . $val . '"';
          if ($id=='id') {$idPe=$val;}
        }
        //add expanded status
        if($portfolio){
           echo',"idhealthstatus":"sdfs"';
           echo',"qualitylevel":"fffffff"';
           echo',"idtrend":"dsfdsfsdfds"';
        }
        $refItem=$line['reftype'].'_'.$line['refid'];
        if (isset($collapsedList['Planning_'.$refItem])) {
        	echo ',"collapsed":"1"';
        } else {
        	echo ',"collapsed":"0"';
        }
        if ($baselineTop and isset($arrayBase['top'][$refItem])) {
          echo ',"baseTopStart":"'.$arrayBase['top'][$refItem]['start'].'"';
          echo ',"baseTopEnd":"'.$arrayBase['top'][$refItem]['end'].'"';
        }
        if ($baselineBottom and isset($arrayBase['bottom'][$refItem])) {
          echo ',"baseBottomStart":"'.$arrayBase['bottom'][$refItem]['start'].'"';
          echo ',"baseBottomEnd":"'.$arrayBase['bottom'][$refItem]['end'].'"';
        }
        if ($line['reftype']!='Project' and $line['reftype']!='Fixed' and $line['reftype']!='Construction' and $line['reftype']!='Replan') {
          $arrayResource=array();
          // if ($showResource) { //
          if (isset($line['idresource']) and $line['idresource'] ) {
            $ass=new Assignment();
            $ass->idResource=$line['idresource'];
            $assList=array($line['idresource']=>$ass);
            $resp=$line['idresource'];
          } else { // Must always retreive resource to display value in column, even if not displayed
          	$crit=array('refType'=>$line['reftype'], 'refId'=>$line['refid']);
            $ass=new Assignment();
            $assList=$ass->getSqlElementsFromCriteria($crit,false); 
            $resp="";	        
  	        if (isset($arrayObj[$line['reftype']])) {
  	          $objElt=$arrayObj[$line['reftype']];
  	        } else {
              $objElt=new $line['reftype']();
              if (! property_exists($objElt,'idResource')) {
                $objElt=null;
              }
              $arrayObj[$line['reftype']]=$objElt;
  	        }
  	        if ($objElt) {
  	          $resp=SqlList::getFieldFromId($line['reftype'], $line['refid'], 'idResource');
  	        }
          }
	        foreach ($assList as $ass) {       	
	        	$res=new ResourceAll($ass->idResource,true);
	        	if (! isset($arrayResource[$res->id])) {
  	        	$display=($displayResource=='NO')?null:$res->$displayResource;
  	        	if ($displayResource=='initials' and ! $display) {
  	        	  //$encoding=mb_detect_encoding($res->name, 'ISO-8859-1, UTF-8');
  	        	  //$display=$encoding;
  	        	  $words=mb_split(' ',str_replace(array('"',"'"), ' ', $res->name));
  	        	  $display='';
  	        	  foreach ($words as $word) {
  	        	    $display.=(mb_substr($word,0,1,'UTF-8'));
  	        	  }
  	        	}
  	        	if ($display)	{
  	        	  $arrayResource[$res->id]=htmlEncode($display);
  	        	  if ($resp and $resp==$res->id ) {
  	        		  $arrayResource[$res->id]='<b>'.htmlEncode($display).'</b>';
  	        	  }
  	        	}
  	        }
          }
	        //$res=new Resource($ass->idResource);
	        echo ',"resource":"' . htmlEncodeJson(implode(', ',$arrayResource)) . '"';
        } else {
          echo ',"resource":""';
        }
        if (is_numeric($idPe) and floatval($idPe)==$idPe) {
          $crit=array('successorId'=>$idPe);
        } else {
          $crit=array('successorId'=>'0');
        }
        $listPred="";
        $depList=$d->getSqlElementsFromCriteria($crit,false);
        foreach ($depList as $dep) {
          $listPred.=($listPred!="")?',':'';
          $listPred.="$dep->predecessorId#$dep->id#$dep->successorRefType#$dep->dependencyType";
        }
        echo ', "depend":"' . $listPred . '"';
        echo ', "color":"'.trim(((isset($line['color']))?$line['color']:''),'#').'"';
        echo '}';
      }
    }
    echo ' ] }';
  }

  function displayGantt($result) {
  	global $displayResource, $outMode, $showMilestone, $portfolio,  $columnsDescription, $nbQueriedRows;
  	$csvSep=Parameter::getGlobalParameter('csvSeparator');
    $showWbs=false;
    if (array_key_exists('showWBS',$_REQUEST) ) {
      $showWbs=true;
    }
    $showResource=false;
    if ( array_key_exists('showResource',$_REQUEST) ) {
      $showResource=true;
    }
    // calculations
    $startDate=date('Y-m-d');
    if (array_key_exists('startDate',$_REQUEST)) {
      $startDate=$_REQUEST['startDate'];
	    Security::checkValidDateTime($startDate);
    }
    $endDate='';
    if (array_key_exists('endDate',$_REQUEST)) {
      $endDate=$_REQUEST['endDate'];
	    Security::checkValidDateTime($endDate);
    }
    $format='day';
    if (array_key_exists('format',$_REQUEST)) {
      $format=$_REQUEST['format'];
	    Security::checkValidPeriodScale($format);
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
    if ($nbQueriedRows > 0) {
      $resultArray=array();
      while ($line = Sql::fetchLine($result)) {
      	$line=array_change_key_case($line,CASE_LOWER);
        if ($line['reftype']=='Milestone' and $portfolio and $showMilestone and $showMilestone!='all' ) {   
          $mile=new Milestone($line['refid'],true);
          if ($mile->idMilestoneType!=$showMilestone) {
            continue;
          }
        }
        if ($line["plannedwork"]>0 and $line["leftwork"]==0) {
          $line["plannedstartdate"]='';
          $line["plannedenddate"]='';
        }
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
        //if ($pEnd=="") {$pEnd=date('Y-m-d');}
        if ($line['reftype']=='Milestone') {
          $pStart=$pEnd;
        }
        if (strlen($pStart)>10) $pStart=substr($pStart,0,10);
        if (strlen($pEnd)>10) $pStart=substr($pEnd,0,10);
        if (trim($line['realstartdate']) and isset($line['isglobal']) and $line['isglobal']==1 and ! $line['progress']) {
          if ($pStart==$pend) {
            $line['progress']='50';
          } else {
            $taskLength=dayDiffDates($pStart,$pend);
            if ($taskLength>0) {
              $progressLength=dayDiffDates($pStart,date('Y-m-d'));
              $line['progress']=round($progressLength/$taskLength*100,0);
            } else {
              $line['progress']='50';
            }
          } 
        }
        $line['pstart']=$pStart;
        $line['pend']=$pEnd;
        $line['type'] = '';
        if($line['reftype'] == 'Project') {
            $project = new Project($line['refid']);
            $line['color'] = $project->color;
            $type = new Type($project->idProjectType);
            $line['type'] = $type->name;
            $status = new Status($project->idStatus);
            $line['status'] = $status->name;
            $line['statuscolor'] = $status->color;
            if($portfolio){
              $health= new Health($project->idHealth);
              $line['health'] = $health->name;
              $quality= new Quality($project->idQuality);
              $line['quality'] = $quality->name;
              $crit=array('id'=>$project->idTrend);
              $trend=SqlElement::getSingleSqlElementFromCriteria('Trend', $crit);
              $line['trend'] = $trend->name;
              $overallProgress= new OverallProgress($project->idOverallProgress);
              $line['overallprogress'] = $overallProgress->name;
            }
        } else if ($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1 
          or (($columnsDescription['IdHealthStatus']['show']==1 or $columnsDescription['QualityLevel']['show']==1 or $columnsDescription['IdTrend']['show']==1 or $columnsDescription['IdOverallProgress']['show']==1) and $portfolio)) {
          $ref=$line['reftype'];
          if ($ref=='PeriodicMeeting') $ref='Meeting';
          $type='id'.$ref.'Type';
          $item=new $ref($line['refid'],true);
          if($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1 ){
            $line["type"]=SqlList::getNameFromId('Type',$item->$type);
            if (property_exists($item,"idStatus")) {
              $status = new Status($item->idStatus);
              $line['status'] = $status->name;
              $line['statuscolor'] = $status->color;
            } else {
              $line['status'] = '';
              $line['statuscolor'] = '';
            }
          }
          if($columnsDescription['IdHealthStatus']['show']==1 and $portfolio){
            if (property_exists($item,"idHealth")) {
              $health = new Health ($item->idHealth);
              $line['health'] = $health->name;
              $line['healthcolor'] = $health->color;
            }else{
              $line['health'] = '';
              $line['healthcolor'] = '';
            }
          }
          if($columnsDescription['QualityLevel']['show']==1 and $portfolio){
            if (property_exists($item,"idQuality")) {
              $quality = new Quality($item->idQuality);
              $line['quality'] = $quality->name;
              $line['qualitycolor'] = $quality->color;
            }else{
              $line['quality'] = '';
              $line['qualitycolor'] = '';
            }
          }
          if($columnsDescription['IdTrend']['show']==1 and $portfolio){
            if (property_exists($item,"idTrend")) {
              $trend = new Trend($item->idTrend);
              $line['trend'] = $trend->name;
              $line['trendcolor'] = $trend->color;
            }else{
              $line['trend'] = '';
              $line['trendcolor'] = '';
            }
          }
          if($columnsDescription['IdOverallProgress']['show']==1 and $portfolio){
            if (property_exists($item,"idOverallProgress")) {
              $overallProgress = new OverallProgress($item->idOverallProgress);
              $line['overallprogress'] = $overallProgress->name;
            }else{
              $line['overallprogress'] = '';
            }
          }
          
        }
        if ($line['reftype']!='Project' and $line['reftype']!='Fixed' and $line['reftype']!='Construction' and $line['reftype']!='Replan') { // 'Fixed' and 'Construction' are projects !!!!
          $arrayResource=array();
          if (isset($columnsDescription['Resource']) and $columnsDescription['Resource']['show']==1) { // Must always retreive resource to display value in column, even if not displayed
            $crit=array('refType'=>$line['reftype'], 'refId'=>$line['refid']);
            $ass=new Assignment();
            $assList=$ass->getSqlElementsFromCriteria($crit,false);
            $resp="";
            if (isset($arrayObj[$line['reftype']])) {
              $objElt=$arrayObj[$line['reftype']];
            } else {
              $objElt=new $line['reftype']();
              if (! property_exists($objElt,'idResource')) {
                $objElt=null;
              }
              $arrayObj[$line['reftype']]=$objElt;
            }
            if ($objElt) {
              $resp=SqlList::getFieldFromId($line['reftype'], $line['refid'], 'idResource');
            }
            foreach ($assList as $ass) {
              $res=new Resource($ass->idResource,true);
              if (! isset($arrayResource[$res->id])) {
                $display=$res->$displayResource;
                if ($displayResource=='initials' and ! $display) {
                  $words=mb_split(' ',str_replace(array('"',"'"), ' ', $res->name));
                  $display='';
                  foreach ($words as $word) {
                    $display.=(mb_substr($word,0,1,'UTF-8'));
                  }
                }
                if ($display)	{
                  $arrayResource[$res->id]=htmlEncode($display);
                  if ($resp and $resp==$res->id ) {
                    $arrayResource[$res->id]='<b>'.htmlEncode($display).'</b>';
                  }
                }
              }
            }
          }
          //$res=new Resource($ass->idResource);
          $line["resource"]= htmlEncodeJson(implode(', ',$arrayResource));
        } else {
          $line["resource"]="";
        }
        $resultArray[]=$line;
        if ($maxDate=='' or $maxDate<$pEnd) {$maxDate=$pEnd;}
        //if ($minDate=='' or $minDate>$pStart) {$minDate=$pStart;}
        if ($minDate=='' or ($minDate>$pStart and trim($pStart))) { $minDate=$pStart;}
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
      //$sortArray=Parameter::getPlanningColumnOrder();
	  $sortArray=array_merge(array(), Parameter::getPlanningColumnOrder());
    $cptSort=0;
    unset($columnsDescription['ObjectType']);
    unset($columnsDescription['ExterRes']);
    if(!$portfolio){
      unset($columnsDescription['IdHealthStatus']);
      unset($columnsDescription['QualityLevel']);
      unset($columnsDescription['IdTrend']);
      unset($columnsDescription['IdOverallProgress']);
    }
    foreach ($columnsDescription as $ganttColName=>$ganttCol) { 
      if ($ganttCol['show']==1) $cptSort++; 
    }
	  if($outMode != 'csv') {
      //echo '<table dojoType="dojo.dnd.Source" id="wishlistNode" class="container ganttTable" style="border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
      echo '<div style="overflow:hidden;">';
      echo '<table style="font-size:80%; border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
      echo '<tr style="height: 20px;"><td colspan="' . ($cptSort+1) . '">&nbsp;</td>';
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
        	$span=numberOfDaysOfMonth($day)+numberOfDaysOfMonth(addMonthsToDate($day,1))+numberOfDaysOfMonth(addMonthsToDate($day,2));
        	$span=3*30/5;
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
        if (isset($columnsDescription[$col]) and $columnsDescription[$col]['show']!=1) continue; 
        if ($col=='ValidatedWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colValidated') . '</TD>' ;
      	if ($col=='AssignedWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colAssigned') . '</TD>' ;
        if ($col=='RealWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colReal') . '</TD>' ;
        if ($col=='LeftWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colLeft') . '</TD>' ;
        if ($col=='PlannedWork') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colReassessed') . '</TD>' ;
        if ($col=='ValidatedCost') echo '  <TD class="reportTableHeader" style="width:30px">'. i18n('colValidatedCost') . '</TD>' ;
        if ($col=='AssignedCost') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colAssignedCost') . '</TD>' ;
        if ($col=='RealCost') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colRealCost') . '</TD>' ;
        if ($col=='LeftCost') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colLeftCost') . '</TD>' ;
        if ($col=='PlannedCost') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colPlannedCost') . '</TD>' ;
        if ($col=='Type') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colType') . '</TD>' ;
        if ($col=='IdStatus') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colIdStatus') . '</TD>' ;
        if ($col=='IdHealthStatus'  and $portfolio) echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colIdHealthStatus') . '</TD>' ;
        if ($col=='QualityLevel'  and $portfolio) echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colQualityLevel') . '</TD>' ;
        if ($col=='IdTrend' and $portfolio) echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colIdTrend') . '</TD>' ;
        if ($col=='IdOverallProgress'  and $portfolio) echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colProgress') . '</TD>' ;
        if ($col=='Duration') echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colDuration') . '</TD>' ;
        if ($col=='Progress') echo '  <TD class="reportTableHeader" style="width:30px">'  . i18n('colPct') . '</TD>' ;
        if ($col=='StartDate') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colStart') . '</TD>' ;
        if ($col=='EndDate') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colEnd') . '</TD>' ;
        if ($col=='Resource') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colResource') . '</TD>' ;
        if ($col=='Priority') echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colPriorityShort') . '</TD>' ;
        if ($col=='IdPlanningMode') echo '  <TD class="reportTableHeader" style="width:150px">'  . i18n('colIdPlanningMode') . '</TD>' ;
        if ($col=='Id') echo '  <TD class="reportTableHeader" style="width:18px">'  . i18n('colId') . '</TD>' ;
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
          $span=30/5;
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
	  } else {
	      $currency=' ('.Parameter::getGlobalParameter('currency').')';
	      $workUnit=' ('.Work::displayShortWorkUnit().')';
        echo chr(239) . chr(187) . chr(191); // Needed by Microsoft Excel to make it CSV
        echo i18n('colElement') . $csvSep . i18n('colId') . $csvSep . i18n('colTask') . $csvSep  ; 
        foreach ($sortArray as $col) {
          if (isset($columnsDescription[$col]) and $columnsDescription[$col]['show']!=1) continue; 
          if ($col=='ValidatedWork') echo i18n('colValidatedWork') . $workUnit . $csvSep ;
          if ($col=='AssignedWork') echo i18n('colAssignedWork') . $workUnit . $csvSep ;
          if ($col=='RealWork') echo i18n('colRealWork') . $workUnit . $csvSep ;
          if ($col=='LeftWork') echo i18n('colLeftWork') . $workUnit . $csvSep ;
          if ($col=='PlannedWork') echo i18n('colPlannedWork') . $workUnit . $csvSep ;
          if ($col=='ValidatedCost') echo i18n('colValidatedCost') . $currency . $csvSep ;
          if ($col=='AssignedCost') echo i18n('colAssignedCost') . $currency . $csvSep ;
          if ($col=='RealCost') echo i18n('colRealCost') . $currency . $csvSep ;
          if ($col=='LeftCost') echo i18n('colLeftCost') . $currency . $csvSep ;
          if ($col=='PlannedCost') echo i18n('colPlannedCost') . $currency . $csvSep ;
          if ($col=='Type') echo i18n('colType') . $csvSep ;
          if ($col=='IdStatus') echo i18n('colIdStatus') . $csvSep . i18n('colStatusColor') . $csvSep ;
          if ($col=='IdHealthStatus'  and $portfolio) echo i18n('colIdHealthStatus') . $csvSep . i18n('colHealthStatusColor') . $csvSep ;
          if ($col=='QualityLevel'  and $portfolio) echo i18n('colQualityLevel') . $csvSep . i18n('colQualityLevelColor') . $csvSep ;
          if ($col=='IdTrend' and $portfolio) echo i18n('colIdTrend') . $csvSep . i18n('colTrendColor') . $csvSep ;
          if ($col=='IdOverallProgress'  and $portfolio) echo i18n('colProgress') . $csvSep ;
          if ($col=='Duration') echo i18n('colDuration') . ' ('.i18n('shortDay') . ')' . $csvSep ;
          if ($col=='Progress') echo i18n('colProgress'). ' (' .i18n('colPct') . ')' . $csvSep ;
          if ($col=='StartDate') echo i18n('colStart') . $csvSep ;
          if ($col=='EndDate') echo i18n('colEnd') . $csvSep ;
          if ($col=='Resource') echo i18n('colResource') . $csvSep ;
          if ($col=='Priority') echo i18n('colPriority') . $csvSep ;
          if ($col=='IdPlanningMode') echo i18n('colIdPlanningMode') . $csvSep ;
          if ($col=='Id') echo i18n('colId') . $csvSep ;
        }
        echo "\n";
      }
      // lines
      $width=round($colWidth/$colUnit) . "px;";
      $collapsedList=Collapsed::getCollaspedList();
      $closedWbs='';
      $level=1;
      $wbsLevelArray=array();
      foreach ($resultArray as $line) {
        $pEnd=$line['pend'];
        $pStart=$line['pstart'];
        $realWork=$line['realwork'];
        $plannedWork=$line['plannedwork'];
        $progress=$line['progress'];

        // pGroup : is the task a group one ?
        $pGroup=($line['elementary']=='0')?1:0;
        if ($line['reftype']=='Fixed') $pGroup=1;
        if ($line['reftype']=='Replan') $pGroup=1;
        if ($closedWbs and strlen($line['wbssortable'])<=strlen($closedWbs)) {
          $closedWbs="";
        }
        $scope='Planning_'.$line['reftype'].'_'.$line['refid'];
        $collapsed=false;
        if ($pGroup and array_key_exists($scope, $collapsedList)) {
          $collapsed=true;
          if (! $closedWbs) {
            $closedWbs=$line['wbssortable'];
          }
        }
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
        $wbsTest=$wbs;
        $level=1;
        while (strlen($wbsTest)>3) {
        	$wbsTest=substr($wbsTest,0,strlen($wbsTest)-6);
        	if (array_key_exists($wbsTest, $wbsLevelArray)) {
        		$level=$wbsLevelArray[$wbsTest]+1;
        		$wbsTest="";
        	}
        }
        $wbsLevelArray[$wbs]=$level;
        //$level=(strlen($wbs)+1)/4;
        $tab="";
        for ($i=1;$i<$level;$i++) {
          $tab.='<span class="ganttSep" >&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        }
        $pName=($showWbs)?$line['wbs']." ":"";
        $pName.= htmlEncode($line['refname']);
        
        $durationNumeric=($rowType=='mile' or $pStart=="" or $pEnd=="")?'-':workDayDiffDates($pStart, $pEnd);
        $duration=$durationNumeric . "&nbsp;" . i18n("shortDay");
        //echo '<TR class="dojoDndItem ganttTask' . $rowType . '" style="margin: 0px; padding: 0px;">';

        if ($closedWbs and $closedWbs!=$line['wbssortable']) {
          //echo ' display:none;';
          continue;
        }
		if($outMode != 'csv') {
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
          if ($line['reftype']=='Milestone') {
            echo '<img style="width:12px" src="../view/css/images/mile.gif" />';
          } else {
            echo '<img style="width:12px" src="../view/css/images/none.gif" />';
          }
        }
        //<div style="float: left;width:16px;">&nbsp;</div></span>';
        echo '</span>&nbsp;';
        echo $pName . '</span></TD>';
        foreach ($sortArray as $col) {
          if (isset($columnsDescription[$col]) and $columnsDescription[$col]['show']!=1) continue;
          if ($col=='ValidatedWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . Work::displayWorkWithUnit($line["validatedwork"])  . '</TD>' ;
          if ($col=='AssignedWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["assignedwork"])  . '</TD>' ;
          if ($col=='RealWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["realwork"])  . '</TD>' ;
          if ($col=='LeftWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["leftwork"])  . '</TD>' ;
          if ($col=='PlannedWork') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  Work::displayWorkWithUnit($line["plannedwork"])  . '</TD>' ;
          if ($col=='ValidatedCost') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . costFormatter($line["validatedcost"])  . '</TD>' ;
          if ($col=='AssignedCost') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  costFormatter($line["assignedcost"])  . '</TD>' ;
          if ($col=='RealCost') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  costFormatter($line["realcost"])  . '</TD>' ;
          if ($col=='LeftCost') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  costFormatter($line["leftcost"])  . '</TD>' ;
          if ($col=='PlannedCost') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  costFormatter($line["plannedcost"])  . '</TD>' ;
          if ($col=='Type') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . ($line["type"])  . '</TD>' ;
          if ($col=='IdStatus') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  ($line["status"])  . '</TD>' ;
          if ($col=='IdHealthStatus' and $portfolio) echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  ($line["health"])  . '</TD>' ;
          if ($col=='QualityLevel' and $portfolio) echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  ($line["quality"])  . '</TD>' ;
          if ($col=='IdTrend' and $portfolio) echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  ($line["trend"])  . '</TD>' ;
          if ($col=='IdOverallProgress' and $portfolio) echo '  <TD class="reportTableData" style="' . $compStyle . '" >' .  ($line["overallprogress"])  . '</TD>' ;
          if ($col=='Duration') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . $duration  . '</TD>' ;
          if ($col=='Progress') echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . percentFormatter($progress) . '</TD>' ;
          if ($col=='StartDate') echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . (($pStart)?dateFormatter($pStart):'-') . '</TD>' ;
          if ($col=='EndDate') echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . (($pEnd)?dateFormatter($pEnd):'-') . '</TD>' ;
          if ($col=='Resource') echo '  <TD class="reportTableData" style="text-align:left;' . $compStyle . '" >' . $line["resource"]  . '</TD>' ;
          if ($col=='Priority') echo '  <TD class="reportTableData" style="text-align:center;' . $compStyle . '" >' . $line["priority"]  . '</TD>' ;
          if ($col=='IdPlanningMode') echo '  <TD class="reportTableData" style="text-align:left;' . $compStyle . '" ><span class="nobr">' . SqlList::getNameFromId('PlanningMode', $line["idplanningmode"])  . '</span></TD>' ;
          if ($col=='Id') echo '  <TD class="reportTableData" style="text-align:right;' . $compStyle . '" >' . $line["id"] . '</TD>' ;
        }
		    if ($pGroup) {
          $pColor='#505050;';
          //$pBackground='background:#505050 url(../view/img/grey.png) repeat-x;';
          $pBackground='background-color:#505050;';
        } else {
          if ($line['notplannedwork']>0) {        		
        		$pColor='#9933CC';
        		$pBackground='background-color:#9933CC;';
        	} else if (trim($line['validatedenddate'])!="" && $line['validatedenddate'] < $pEnd) {
            $pColor='#BB5050';
            //$pBackground='background:#BB5050 url(../view/img/red.png) repeat-x;';
            $pBackground='background-color:#BB5050;';
          } else  {
            $pColor="#50BB50";
            //$pBackground='background:#50BB50 url(../view/img/green.png) repeat-x;';
            $pBackground='background-color:#50BB50;';
          }
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
            $fontSize='90%';
            if ( substr($days[($i)],-2)!='26' or (substr($days[($i)],5,2)!='03' and substr($days[($i)],5,2)!='06' and substr($days[($i)],5,2)!='09' and substr($days[($i)],5,2)!='12') ) {
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
              echo '<tr height="' . $height . 'px"><td style="width:100%; ' . $pBackground . 'height:' .  $height . 'px;"></td></tr>';
              echo '</table>';
              if ($pGroup and $days[$i]==$pStart and $outMode!='pdf') {
                if ($format=='quarter' or $format=='month') {
                  echo '<div class="" style="float:left; height:4px"></div>';
                } else { 
                  echo '<div class="ganttTaskgroupBarExt" style="float:left; height:4px"></div>'
                  . '<div class="ganttTaskgroupBarExt" style="float:left; height:3px"></div>'
                  . '<div class="ganttTaskgroupBarExt" style="float:left; height:2px"></div>'
                  . '<div class="ganttTaskgroupBarExt" style="float:left; height:1px"></div>';
                }
              }
              if ($pGroup and $days[$i]==$pEnd and $outMode!='pdf') {
                if ($format=='quarter' or $format=='month') {
                  echo '<div class="" style="float:left; height:4px"></div>';
                } else { 
                  echo '<div class="ganttTaskgroupBarExt" style="float:right; height:4px"></div>'
	                . '<div class="ganttTaskgroupBarExt" style="float:right; height:3px"></div>'
	                . '<div class="ganttTaskgroupBarExt" style="float:right; height:2px"></div>'
	                . '<div class="ganttTaskgroupBarExt" style="float:right; height:1px"></div>';
                }
	            }
              $dispCaption=($showResource)?true:false;
            }
          } else {
            echo '<td class="reportTableData" width="' . $width .'" style="width: ' . $width . $color . $noBorder . '">';
          }
          echo '</td>';
          if ($format=="quarter") {
            $dom=intval(substr($days[$i],8,2));
            if ($dom>=26) {
              $lastDayOfMonth=date('t',strtotime($days[$i]));
              $i=array_search(substr($days[$i],0,8).$lastDayOfMonth,$days);
            } else {
              $i+=4;
            }
          }
        }
        echo '</TR>';
      } else {
          echo i18n($line['reftype']) . $csvSep . $line['refid'] . $csvSep . html_entity_decode(strip_tags($tab), ENT_QUOTES, 'UTF-8') . html_entity_decode($pName, ENT_QUOTES, 'UTF-8') . $csvSep;
          foreach ($sortArray as $col) {          
            if (isset($columnsDescription[$col]) and $columnsDescription[$col]['show']!=1) continue;
            if ($col=='ValidatedWork') echo formatNumericOutput(Work::displayWork($line["validatedwork"]))  . $csvSep;
            if ($col=='AssignedWork') echo formatNumericOutput(Work::displayWork($line["assignedwork"]))  . $csvSep;
            if ($col=='RealWork') echo formatNumericOutput(Work::displayWork($line["realwork"]))  . $csvSep;
            if ($col=='LeftWork') echo formatNumericOutput(Work::displayWork($line["leftwork"]))  . $csvSep;
            if ($col=='PlannedWork') echo formatNumericOutput(Work::displayWork($line["plannedwork"]))  . $csvSep;
            if ($col=='ValidatedCost') echo formatNumericOutput($line["validatedcost"])  . $csvSep;
            if ($col=='AssignedCost') echo formatNumericOutput($line["assignedcost"])  . $csvSep;
            if ($col=='RealCost') echo formatNumericOutput($line["realcost"])  . $csvSep;
            if ($col=='LeftCost') echo formatNumericOutput($line["leftcost"])  . $csvSep;
            if ($col=='PlannedCost') echo formatNumericOutput($line["plannedcost"])  . $csvSep;
            if ($col=='Type') echo $line["type"]  . $csvSep;
            if ($col=='IdStatus') echo $line["status"]  . $csvSep . $line["statuscolor"]  . $csvSep;
            if ($col=='IdHealthStatus' and $portfolio) echo $line["health"]  . $csvSep . $line["healthcolor"]  . $csvSep;
            if ($col=='QualityLevel' and $portfolio) echo $line["quality"]  . $csvSep . $line["qualitycolor"]  . $csvSep;
            if ($col=='IdTrend' and $portfolio) echo $line["trend"]  . $csvSep . $line["trendcolor"]  . $csvSep;
            if ($col=='IdOverallProgress' and $portfolio) echo $line["overallprogress"]  . $csvSep ;
            if ($col=='Duration') echo $durationNumeric . $csvSep;
            if ($col=='Progress') echo $progress . $csvSep;
            if ($col=='StartDate') echo (($pStart)?dateFormatter($pStart):'-'). $csvSep;
            if ($col=='EndDate') echo (($pEnd)?dateFormatter($pEnd):'-'). $csvSep;
            if ($col=='Resource') echo strip_tags($line["resource"])  . $csvSep;
            if ($col=='Priority') echo $line["priority"]  . $csvSep;
            if ($col=='IdPlanningMode') echo SqlList::getNameFromId('PlanningMode', $line["idplanningmode"])  . $csvSep;
          }
          echo "\n";
		}
      }
    }
  	if($outMode != 'csv') {
  	  echo "</table></div>";
  	}
  }

  function exportGantt($result) {
    global $nbQueriedRows,$applyFilter,$arrayRestrictWbs;
  	$paramDbDisplayName=Parameter::getGlobalParameter('paramDbDisplayName');
  	$currency=Parameter::getGlobalParameter('currency');
  	$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
  	$exportAssignments=true;
  	if (Parameter::getGlobalParameter('doNotExportAssignmentsForXMLFormat')===true) $exportAssignments=false;
  	if (Parameter::getUserParameter('exportAssignmentsForXMLFormat')=='NO') $exportAssignments=false;
  	$nl="\n";
  	$tab="\t";
  	$hoursPerDay=Parameter::getGlobalParameter('dayTime');
    $startDate=date('Y-m-d');
    $startAM=Parameter::getGlobalParameter('startAM') . ':00';
    $endAM=Parameter::getGlobalParameter('endAM') . ':00';
    $startPM=Parameter::getGlobalParameter('startPM') . ':00';
    $endPM=Parameter::getGlobalParameter('endPM') . ':00';
    $name="export_planning_" . date('Ymd_His') . ".xml";
    $now=date('Y-m-d').'T'.date('H:i:s');
    if (array_key_exists('startDate',$_REQUEST)) {
      $startDate=$_REQUEST['startDate'];
	    Security::checkValidDateTime($startDate);
    }
    $endDate='';
    if (array_key_exists('endDate',$_REQUEST)) {
      $endDate=$_REQUEST['endDate'];
	    Security::checkValidDateTime($endDate);
    }
    $maxDate = '';
    $minDate = '';
    $resultArray=array();
    $selectItems="('',0)";
    $allItems=array();
    if ($nbQueriedRows > 0) {
      while ($line = Sql::fetchLine($result)) {
      	$line=array_change_key_case($line,CASE_LOWER);
      	$allItems[$line['id']]=$line['id'];
      	$selectItems.=",('".$line['reftype']."',".$line['refid'].")";
      	if ($applyFilter and !isset($arrayRestrictWbs[$line['wbssortable']])) continue; // Filter applied and item is not selected and not a parent of selected
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
        if (! $pStart) $pStart=date('Y-m-d');
        if (! $pEnd) $pEnd=date('Y-m-d');
        $line['pstart']=$pStart;
        $line['pend']=$pEnd;
        $line['pduration']=workDayDiffDates($pStart,$pEnd);
        if ($line['reftype']=='Milestone') {
          $line['pduration']=0;
        }
        $resultArray[]=$line;
        if ($maxDate=='' or $maxDate<$pEnd) {$maxDate=$pEnd;}
        if ($minDate=='' or $minDate>$pStart) {$minDate=$pStart;}
      }
      if ($endDate and $maxDate>$endDate) {
        $maxDate=$endDate;
      }
    }
    
    for ($i=0;$i<count($resultArray);$i++) {
      $wbs=$resultArray[$i]['wbssortable'];
      for ($j=$i+1;$j<count($resultArray);$j++) {
        if (substr($resultArray[$j]['wbssortable'],0,strlen($wbs))!=$wbs) break;
        if ($resultArray[$j]['pstart']<$resultArray[$i]['pstart']) {
          $resultArray[$i]['pstart']=$resultArray[$j]['pstart'];
          $resultArray[$i]['pduration']=workDayDiffDates($resultArray[$i]['pstart'],$resultArray[$i]['pend']);
        }
        if ($resultArray[$j]['pend']>$resultArray[$i]['pend']) {
          $resultArray[$i]['pend']=$resultArray[$j]['pend'];
          $resultArray[$i]['pduration']=workDayDiffDates($resultArray[$i]['pstart'],$resultArray[$i]['pend']);
        }
      }
    }
    if (getSessionValue('project') and getSessionValue('project')!='*' and strpos(getSessionValue('project'), ",") === null) {
      $prj=new Project(getSessionValue('project'), true);
      $lstTopPrj=$prj->getTopProjectList(true);
      $in=transformValueListIntoInClause($lstTopPrj);
      $where="idProject in " . $in;
      $aff=new Affectation();
      $affList=$aff->getSqlElementsFromCriteria(null,false, $where, " id asc");
      $resourceList=array();
      foreach ($affList as $aff) {
        if (isset($resourceList[$aff->idResource])) return;
        $resourceList[$aff->idResource]=new ResourceAll($aff->idResource,true);
      }
    } else {
      $res=new ResourceAll();
      $asTemp=new Assignment();
      $asTable=$asTemp->getDatabaseTableName();
      $crit="id in ( select idResource from $asTable asx where (asx.refType, asx.refId) in ($selectItems) )";
      $resourceList=$res->getSqlElementsFromCriteria(null, false, $crit, " id asc");
    }

    echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $nl;
    echo '<Project xmlns="http://schemas.microsoft.com/project">' . $nl;
    echo $tab.'<SaveVersion>14</SaveVersion>' . $nl;
    echo $tab.'<Name>' . htmlEncode($name,'xml') . '</Name>' . $nl;
    echo $tab.'<Title>' . htmlEncode($paramDbDisplayName,'xml') . '</Title>' . $nl;
    echo $tab.'<CreationDate>' . $now . '</CreationDate>' . $nl;
    echo $tab.'<LastSaved>' . $now . '</LastSaved>' . $nl;
    echo $tab.'<ScheduleFromStart>1</ScheduleFromStart>' . $nl;
    echo $tab.'<StartDate>' . $minDate . 'T00:00:00</StartDate>' . $nl;
    echo $tab.'<FinishDate>' . $maxDate . 'T00:00:00</FinishDate>' . $nl;
    echo $tab.'<FYStartDate>1</FYStartDate>' . $nl;
    echo $tab.'<CriticalSlackLimit>0</CriticalSlackLimit>' . $nl;
    echo $tab.'<CurrencyDigits>2</CurrencyDigits>' . $nl;
    echo $tab.'<CurrencySymbol>' . $currency . '</CurrencySymbol>' . $nl;
    echo $tab.'<CurrencyCode>' . getCurrencyCode($currency) . '</CurrencyCode>' . $nl;
    echo $tab.'<CurrencySymbolPosition>' . (($currencyPosition=='before')?'2':'3') . '</CurrencySymbolPosition>' . $nl;
    echo $tab.'<CalendarUID>0</CalendarUID>' . $nl;
    echo $tab.'<DefaultStartTime>' . $startAM . '</DefaultStartTime>' . $nl;
    echo $tab.'<DefaultFinishTime>' . $endPM . '</DefaultFinishTime>' . $nl;
    echo $tab.'<MinutesPerDay>' . ($hoursPerDay*60) . '</MinutesPerDay>' . $nl;
    echo $tab.'<MinutesPerWeek>' . ($hoursPerDay*60*5) . '</MinutesPerWeek>' . $nl;
    echo $tab.'<DaysPerMonth>20</DaysPerMonth>' . $nl;
    echo $tab.'<DefaultTaskType>2</DefaultTaskType>' . $nl;
    echo $tab.'<DefaultFixedCostAccrual>2</DefaultFixedCostAccrual>' . $nl;
    echo $tab.'<DefaultStandardRate>10</DefaultStandardRate>' . $nl;
    echo $tab.'<DefaultOvertimeRate>15</DefaultOvertimeRate>' . $nl;
    echo $tab.'<DurationFormat>'.getDurationFormat().'</DurationFormat>' . $nl;
    echo $tab.'<WorkFormat>'.getWorkFormat().'</WorkFormat>' . $nl;
    echo $tab.'<EditableActualCosts>0</EditableActualCosts>' . $nl;
    echo $tab.'<HonorConstraints>0</HonorConstraints>' . $nl;
    // echo $tab.'<EarnedValueMethod>0</EarnedValueMethod>' . $nl;
    echo $tab.'<InsertedProjectsLikeSummary>0</InsertedProjectsLikeSummary>' . $nl;
    echo $tab.'<MultipleCriticalPaths>0</MultipleCriticalPaths>' . $nl;
    echo $tab.'<NewTasksEffortDriven>1</NewTasksEffortDriven>' . $nl;
    echo $tab.'<NewTasksEstimated>0</NewTasksEstimated>' . $nl;
    echo $tab.'<SplitsInProgressTasks>0</SplitsInProgressTasks>' . $nl;
    echo $tab.'<SpreadActualCost>0</SpreadActualCost>' . $nl;
    echo $tab.'<SpreadPercentComplete>0</SpreadPercentComplete>' . $nl;
    echo $tab.'<TaskUpdatesResource>1</TaskUpdatesResource>' . $nl;
    echo $tab.'<FiscalYearStart>0</FiscalYearStart>' . $nl;
    echo $tab.'<WeekStartDay>1</WeekStartDay>' . $nl;
    echo $tab.'<MoveCompletedEndsBack>0</MoveCompletedEndsBack>' . $nl;
    echo $tab.'<MoveRemainingStartsBack>0</MoveRemainingStartsBack>' . $nl;
    echo $tab.'<MoveRemainingStartsForward>0</MoveRemainingStartsForward>' . $nl;
    echo $tab.'<MoveCompletedEndsForward>0</MoveCompletedEndsForward>' . $nl;
    echo $tab.'<BaselineForEarnedValue>0</BaselineForEarnedValue>' . $nl;
    echo $tab.'<AutoAddNewResourcesAndTasks>1</AutoAddNewResourcesAndTasks>' . $nl;
    echo $tab.'<CurrentDate>' . $now . '</CurrentDate>' . $nl;
    echo $tab.'<MicrosoftProjectServerURL>1</MicrosoftProjectServerURL>' . $nl;
    echo $tab.'<Autolink>1</Autolink>' . $nl;
    echo $tab.'<NewTaskStartDate>0</NewTaskStartDate>' . $nl;
    echo $tab.'<NewTasksAreManual>1</NewTasksAreManual>'. $nl;
    echo $tab.'<DefaultTaskEVMethod>0</DefaultTaskEVMethod>' . $nl;
    echo $tab.'<ProjectExternallyEdited>1</ProjectExternallyEdited>' . $nl;
    echo $tab.'<ExtendedCreationDate>1984-01-01T00:00:00</ExtendedCreationDate>' . $nl;
    echo $tab.'<ActualsInSync>0</ActualsInSync>' . $nl;
    echo $tab.'<RemoveFileProperties>0</RemoveFileProperties>' . $nl;
    echo $tab.'<AdminProject>0</AdminProject>' . $nl;
    echo $tab.'<UpdateManuallyScheduledTasksWhenEditingLinks>0</UpdateManuallyScheduledTasksWhenEditingLinks>'. $nl;
	  echo $tab.'<KeepTaskOnNearestWorkingTimeWhenMadeAutoScheduled>0</KeepTaskOnNearestWorkingTimeWhenMadeAutoScheduled>'. $nl;
	  if (Parameter::getUserParameter('lang')=='fr') {
    echo $tab.'<Views>' . $nl;
    echo $tab.$tab.'<View>' . $nl;
    echo $tab.$tab.$tab.'<Name>Gantt a&amp;vec chronologie</Name>' . $nl;
    echo $tab.$tab.'</View>' . $nl;
    echo $tab.$tab.'<View>' . $nl;
    echo $tab.$tab.$tab.'<Name>Diagramme de &amp;Gantt</Name>' . $nl;
    //echo $tab.$tab.$tab.'<IsCustomized>true</IsCustomized>' . $nl;
    echo $tab.$tab.'</View>' . $nl;
    echo $tab.$tab.'<View>' . $nl;
    echo $tab.$tab.$tab.'<Name>Chrono&amp;logie</Name>' . $nl;
    echo $tab.$tab.'<IsCustomized>true</IsCustomized>' . $nl;
    echo $tab.$tab.'</View>' . $nl;
    echo $tab.'</Views>' . $nl;
    echo $tab.'<Filters>' . $nl;
    echo $tab.$tab.'<Filter>' . $nl;
    echo $tab.$tab.$tab.'<Name>&amp;Toutes les tÃƒÂ¢ches</Name>' . $nl;
    echo $tab.$tab.'</Filter>' . $nl;
    echo $tab.$tab.'<Filter>' . $nl;
    echo $tab.$tab.$tab.'<Name>Toutes les &amp;ressources</Name>' . $nl;
    echo $tab.$tab.'</Filter>' . $nl;
    echo $tab.'</Filters>' . $nl;
    echo $tab.'<Groups>' . $nl;
    echo $tab.$tab.'<Group>' . $nl;
    echo $tab.$tab.$tab.'<Name>Aucun &amp;groupe</Name>' . $nl;
    echo $tab.$tab.'</Group>' . $nl;
    echo $tab.$tab.'<Group>' . $nl;
    echo $tab.$tab.$tab.'<Name>Aucun &amp;groupe</Name>' . $nl;
    echo $tab.$tab.'</Group>' . $nl;
    echo $tab.'</Groups>' . $nl;
    echo $tab.'<Tables>' . $nl;
    echo $tab.$tab.'<Table>' . $nl;
    echo $tab.$tab.$tab.'<Name>&amp;EntrÃƒÂ©e</Name>' . $nl;
    echo $tab.$tab.'</Table>' . $nl;
    echo $tab.'</Tables>' . $nl;
	  }
    echo $tab.'<Maps/>' . $nl;
    echo $tab.'<Reports/>' . $nl;
    echo $tab.'<Drawings/>' . $nl;
    echo $tab.'<DataLinks/>' . $nl;
    echo $tab.'<VBAProjects/>' . $nl;
	  
    echo $tab.'<OutlineCodes/>' . $nl;
    echo $tab.'<WBSMasks/>' . $nl;
    echo $tab.'<ExtendedAttributes/>' . $nl;
    /*<ExtendedAttributes>
        <ExtendedAttribute>
            <FieldID>188743731</FieldID>
            <FieldName>Text1</FieldName>
        </ExtendedAttribute>
    </ExtendedAttributes>*/
    $cal=new CalendarDefinition();
    $calList=$cal->getSqlElementsFromCriteria(array('idle'=>'0'));
    echo $tab.'<Calendars>' . $nl;
    foreach($calList as $cal) {
      echo $tab.$tab.'<Calendar>' . $nl;
      echo $tab.$tab.$tab.'<UID>'.($cal->id -1).'</UID>' . $nl;
      echo $tab.$tab.$tab.'<Name>Standard</Name>' . $nl;
      echo $tab.$tab.$tab.'<IsBaseCalendar>'.(($cal->id==1)?1:0).'</IsBaseCalendar>' . $nl;
      echo $tab.$tab.$tab.'<IsBaselineCalendar>0</IsBaselineCalendar>' .$nl;
      echo $tab.$tab.$tab.'<BaseCalendarUID>-1</BaseCalendarUID>' . $nl;
      echo $tab.$tab.$tab.'<WeekDays>' . $nl;
      for ($i=1;$i<=7;$i++) {
        echo $tab.$tab.$tab.$tab.'<WeekDay>' . $nl;
        echo $tab.$tab.$tab.$tab.$tab.'<DayType>' . $i . '</DayType>' . $nl;
        if (($i==1 or $i==7)) {
        	echo $tab.$tab.$tab.$tab.$tab.'<DayWorking>0</DayWorking>' . $nl;
        } else {
  	      echo $tab.$tab.$tab.$tab.$tab.'<DayWorking>1</DayWorking>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.'<WorkingTimes>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.'<WorkingTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.$tab.'<FromTime>' . $startAM . '</FromTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.$tab.'<ToTime>' . $endAM . '</ToTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.'</WorkingTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.'<WorkingTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.$tab.'<FromTime>' . $startPM . '</FromTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.$tab.'<ToTime>' . $endPM . '</ToTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.$tab.'</WorkingTime>' . $nl;
  	      echo $tab.$tab.$tab.$tab.$tab.'</WorkingTimes>' . $nl;
        }
        echo $tab.$tab.$tab.$tab.'</WeekDay>' . $nl;
      }
      $calDay=new Calendar();
      $dayList=$calDay->getSqlElementsFromCriteria(array('idCalendarDefinition'=>$cal->id),null,null,'calendarDate asc');
      foreach ($dayList as $calDay) {
        echo $tab.$tab.$tab.$tab.'<WeekDay>' . $nl;
  			echo $tab.$tab.$tab.$tab.$tab.'<DayType>0</DayType>' . $nl;
  			echo $tab.$tab.$tab.$tab.$tab.'<DayWorking>'.(($calDay->isOffDay)?0:1).'</DayWorking>' . $nl;
  			echo $tab.$tab.$tab.$tab.$tab.'<TimePeriod>' . $nl;
  			echo $tab.$tab.$tab.$tab.$tab.$tab.'<FromDate>'.$calDay->calendarDate.'T00:00:00</FromDate>' . $nl;
  			echo $tab.$tab.$tab.$tab.$tab.$tab.'<ToDate>'.$calDay->calendarDate.'T23:59:00</ToDate>' . $nl;
  			echo $tab.$tab.$tab.$tab.$tab.'</TimePeriod>' . $nl;
  			echo $tab.$tab.$tab.$tab.'</WeekDay>'.$nl;
      }
      echo $tab.$tab.$tab.'</WeekDays>' . $nl;
      echo $tab.$tab.'</Calendar>' . $nl;
      foreach ($resourceList as $resource) {
        if ($exportAssignments==false) continue;
      	echo $tab.$tab."<Calendar>" . $nl;
        echo $tab.$tab.$tab."<UID>" . htmlEncode($resource->id,'xml') . "</UID>" . $nl;
        echo $tab.$tab.$tab."<Name>" . htmlEncode($resource->name,'xml') . "</Name>" . $nl;
        echo $tab.$tab.$tab."<IsBaseCalendar>0</IsBaseCalendar>" . $nl;
        echo $tab.$tab.$tab."<IsBaselineCalendar>0</IsBaselineCalendar>". $nl;
        echo $tab.$tab.$tab."<BaseCalendarUID>".($resource->idCalendarDefinition -1)."</BaseCalendarUID>" . $nl;
        echo $tab.$tab."</Calendar>" . $nl;
      }
    }
    echo $tab.'</Calendars>'.$nl;
    echo $tab.'<Tasks>' . $nl;
    // First task for project
    echo $tab.$tab.'<Task>' . $nl;
    echo $tab.$tab.$tab.'<UID>0</UID>' . $nl;
    echo $tab.$tab.$tab.'<ID>0</ID>' . $nl;
    echo $tab.$tab.$tab.'<Name>' . htmlEncode($paramDbDisplayName,'xml') . '</Name>' . $nl;
    echo $tab.$tab.$tab.'<Active>1</Active>' . $nl;
    echo $tab.$tab.$tab.'<Manual>1</Manual>' . $nl;
    echo $tab.$tab.$tab.'<Type>1</Type>' . $nl;
    echo $tab.$tab.$tab.'<IsNull>0</IsNull>' . $nl;
    echo $tab.$tab.$tab.'<CreateDate>'.$now.'</CreateDate>' . $nl;
    echo $tab.$tab.$tab.'<WBS>0</WBS>' . $nl;
    echo $tab.$tab.$tab.'<OutlineNumber>0</OutlineNumber>' . $nl;
    echo $tab.$tab.$tab.'<OutlineLevel>0</OutlineLevel>' . $nl;
    echo $tab.$tab.$tab.'<Priority>500</Priority>' . $nl;
    echo $tab.$tab.$tab.'<Start>'.date('Y-m-d').'T00:00:00</Start>' . $nl;
    echo $tab.$tab.$tab.'<Finish>'.date('Y-m-d').'T00:00:00</Finish>' . $nl;
    echo $tab.$tab.$tab.'<Duration>PT0H0M0S</Duration>' . $nl;
    echo $tab.$tab.$tab.'<ManualStart>'.date('Y-m-d').'T00:00:00</ManualStart>' . $nl;
    echo $tab.$tab.$tab.'<ManualFinish>'.date('Y-m-d').'T00:00:00</ManualFinish>' . $nl;
    echo $tab.$tab.$tab.'<ManualDuration>PT0H0M0S</ManualDuration>' . $nl;
    echo $tab.$tab.$tab.'<DurationFormat>21</DurationFormat>' . $nl;
    echo $tab.$tab.$tab.'<FreeformDurationFormat>7</FreeformDurationFormat>' . $nl;
    echo $tab.$tab.$tab.'<Work>PT0H0M0S</Work>' . $nl;
    echo $tab.$tab.$tab.'<ResumeValid>0</ResumeValid>' . $nl;
    echo $tab.$tab.$tab.'<EffortDriven>1</EffortDriven>' . $nl;
    echo $tab.$tab.$tab.'<Recurring>0</Recurring>' . $nl;
    echo $tab.$tab.$tab.'<OverAllocated>0</OverAllocated>' . $nl;
    echo $tab.$tab.$tab.'<Estimated>0</Estimated>' . $nl;
    echo $tab.$tab.$tab.'<Milestone>0</Milestone>' . $nl;
    echo $tab.$tab.$tab.'<Summary>1</Summary>' . $nl;
    echo $tab.$tab.$tab.'<DisplayAsSummary>0</DisplayAsSummary>' . $nl;
    echo $tab.$tab.$tab.'<Critical>0</Critical>' . $nl;
    echo $tab.$tab.$tab.'<IsSubproject>0</IsSubproject>' . $nl;
    echo $tab.$tab.$tab.'<IsSubprojectReadOnly>0</IsSubprojectReadOnly>' . $nl;
    echo $tab.$tab.$tab.'<ExternalTask>0</ExternalTask>' . $nl;
    echo $tab.$tab.$tab.'<EarlyStart>'.date('Y-m-d').'T00:00:00</EarlyStart>' . $nl;
    echo $tab.$tab.$tab.'<EarlyFinish>'.date('Y-m-d').'T00:00:00</EarlyFinish>' . $nl;
    echo $tab.$tab.$tab.'<LateStart>'.date('Y-m-d').'T00:00:00</LateStart>' . $nl;
    echo $tab.$tab.$tab.'<LateFinish>'.date('Y-m-d').'T00:00:00</LateFinish>' . $nl;
    echo $tab.$tab.$tab.'<StartVariance>0</StartVariance>' . $nl;
    echo $tab.$tab.$tab.'<FinishVariance>0</FinishVariance>' . $nl;
    echo $tab.$tab.$tab.'<WorkVariance>0.00</WorkVariance>' . $nl;
    echo $tab.$tab.$tab.'<FreeSlack>0</FreeSlack>' . $nl;
    echo $tab.$tab.$tab.'<TotalSlack>0</TotalSlack>' . $nl;
    echo $tab.$tab.$tab.'<StartSlack>0</StartSlack>' . $nl;
    echo $tab.$tab.$tab.'<FinishSlack>0</FinishSlack>' . $nl;
    echo $tab.$tab.$tab.'<FixedCost>0</FixedCost>' . $nl;
    echo $tab.$tab.$tab.'<FixedCostAccrual>2</FixedCostAccrual>' . $nl;
    echo $tab.$tab.$tab.'<PercentComplete>0</PercentComplete>' . $nl;
    echo $tab.$tab.$tab.'<PercentWorkComplete>0</PercentWorkComplete>' . $nl;
    echo $tab.$tab.$tab.'<Cost>0</Cost>' . $nl;
    echo $tab.$tab.$tab.'<OvertimeCost>0</OvertimeCost>' . $nl;
    echo $tab.$tab.$tab.'<OvertimeWork>PT0H0M0S</OvertimeWork>' . $nl;
    echo $tab.$tab.$tab.'<ActualDuration>PT0H0M0S</ActualDuration>' . $nl;
    echo $tab.$tab.$tab.'<ActualCost>0</ActualCost>' . $nl;
    echo $tab.$tab.$tab.'<ActualOvertimeCost>0</ActualOvertimeCost>' . $nl;
    echo $tab.$tab.$tab.'<ActualWork>PT0H0M0S</ActualWork>' . $nl;
    echo $tab.$tab.$tab.'<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>' . $nl;
    echo $tab.$tab.$tab.'<RegularWork>PT0H0M0S</RegularWork>' . $nl;
    echo $tab.$tab.$tab.'<RemainingDuration>PT0H0M0S</RemainingDuration>' . $nl;
    echo $tab.$tab.$tab.'<RemainingCost>0</RemainingCost>' . $nl;
    echo $tab.$tab.$tab.'<RemainingWork>PT0H0M0S</RemainingWork>' . $nl;
    echo $tab.$tab.$tab.'<RemainingOvertimeCost>0</RemainingOvertimeCost>' . $nl;
    echo $tab.$tab.$tab.'<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>' . $nl;
    echo $tab.$tab.$tab.'<ACWP>0.00</ACWP>' . $nl;
    echo $tab.$tab.$tab.'<CV>0.00</CV>' . $nl;
    echo $tab.$tab.$tab.'<ConstraintType>0</ConstraintType>' . $nl;
    echo $tab.$tab.$tab.'<CalendarUID>-1</CalendarUID>' . $nl;
    echo $tab.$tab.$tab.'<LevelAssignments>1</LevelAssignments>' . $nl;
    echo $tab.$tab.$tab.'<LevelingCanSplit>1</LevelingCanSplit>' . $nl;
    echo $tab.$tab.$tab.'<LevelingDelay>0</LevelingDelay>' . $nl;
    echo $tab.$tab.$tab.'<LevelingDelayFormat>8</LevelingDelayFormat>' . $nl;
    echo $tab.$tab.$tab.'<IgnoreResourceCalendar>0</IgnoreResourceCalendar>' . $nl;
    echo $tab.$tab.$tab.'<HideBar>0</HideBar>' . $nl;
    echo $tab.$tab.$tab.'<Rollup>0</Rollup>' . $nl;
    echo $tab.$tab.$tab.'<BCWS>0.00</BCWS>' . $nl;
    echo $tab.$tab.$tab.'<BCWP>0.00</BCWP>' . $nl;
    echo $tab.$tab.$tab.'<PhysicalPercentComplete>0</PhysicalPercentComplete>' . $nl;
    echo $tab.$tab.$tab.'<EarnedValueMethod>0</EarnedValueMethod>' . $nl;
    echo $tab.$tab.$tab.'<IsPublished>0</IsPublished>' . $nl;
    echo $tab.$tab.$tab.'<CommitmentType>0</CommitmentType>' . $nl;
    echo $tab.$tab.'</Task>' . $nl;
    
    $cpt=0;
    $arrayTask=array();
    
    foreach ($resultArray as $line) {
    	$cpt++;
    	$arrayTask[$line['reftype'].'#'.$line['refid']]=array('id'=>$line['id']);
    	$pct=($line['plannedwork']>0)?round(100*$line['realwork']/$line['plannedwork'],2):'';
    	if (!$pct) $pct='0';
    	if (!$pct and $line['plannedwork']==0 and $line['realwork']==0 and trim($line['realenddate'])) {
    	  $pct=100;
    	}
    	if ($line['leftwork']==0) $pct=100;
    	//if ($line['realwork']>0 and $line['leftwork']==0) $pct=100;
    	if ($line['reftype']=='Milestone') {
    	  if ($line['realenddate']) $pct=100;
    	  else $pct=0;
    	}
    	if ($line['plannedwork']==0 and $line['reftype']!='Milestone') {
    	  //$line['plannedwork']=0.01;
    	}
      echo $tab.$tab.'<Task>' . $nl;
      echo $tab.$tab.$tab.'<UID>' . $line['id'] . '</UID>' . $nl;
      echo $tab.$tab.$tab.'<projeqtorType>' . $line['reftype'] . '</projeqtorType>' . $nl;
      //gautier #4648
      if ($line['reftype']=='Activity') {
        echo $tab.$tab.$tab.'<projeqtorPlanningMode>' . $line['idplanningmode'] . '</projeqtorPlanningMode>' . $nl;
        $activ = new Activity($line['refid'],true);
        echo $tab.$tab.$tab.'<projeqtorActivityType>' . $activ->idActivityType . '</projeqtorActivityType>' . $nl;
      }
      //end Gautier
      echo $tab.$tab.$tab.'<ID>' . $cpt . '</ID>' . $nl;  // TODO : should be order of the tack in the list
      echo $tab.$tab.$tab.'<Name>' . htmlEncode($line['refname'],'xml') . '</Name>' . $nl;
      echo $tab.$tab.$tab.'<Active>1</Active>'. $nl;
      echo $tab.$tab.$tab.'<Manual>1</Manual>'. $nl;
      echo $tab.$tab.$tab.'<Type>1</Type>' . $nl; // TODO : 0=Fixed Units, 1=Fixed Duration, 2=Fixed Work.
      echo $tab.$tab.$tab.'<IsNull>0</IsNull>' . $nl;
      echo $tab.$tab.$tab.'<CreateDate>'.date('Y-m-d').'T'.date('H:i:s').'</CreateDate>'. $nl;
      echo $tab.$tab.$tab.'<WBS>' . $line['wbs'] . '</WBS>' . $nl;
      echo $tab.$tab.$tab.'<OutlineNumber>' . $line['wbs'] . '</OutlineNumber>' . $nl;
      echo $tab.$tab.$tab.'<OutlineLevel>' . (substr_count($line['wbs'],'.')+1) . '</OutlineLevel>' . $nl;
      echo $tab.$tab.$tab.'<Priority>' . $line['priority'] . '</Priority>' . $nl;
      echo $tab.$tab.$tab.'<Start>' . $line['pstart'] . 'T' . $startAM . '</Start>' . $nl;
      echo $tab.$tab.$tab.'<Finish>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</Finish>' . $nl;
      echo $tab.$tab.$tab.'<Duration>' . formatDuration($line['pduration'],$hoursPerDay) . '</Duration>' . $nl;
      echo $tab.$tab.$tab.'<ManualStart>' . $line['pstart'] . 'T' . $startAM . '</ManualStart>' . $nl;
      echo $tab.$tab.$tab.'<ManualFinish>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</ManualFinish>' . $nl;
      echo $tab.$tab.$tab.'<ManualDuration>' . formatDuration($line['pduration'],$hoursPerDay) . '</ManualDuration>' . $nl;
      echo $tab.$tab.$tab.'<DurationFormat>'.getDurationFormat().'</DurationFormat>' . $nl;
      echo $tab.$tab.$tab.'<FreeformDurationFormat>'.getDurationFormat().'</FreeformDurationFormat>' . $nl;
      echo $tab.$tab.$tab.'<Work>PT' . formatWork($line['plannedwork'],$hoursPerDay).'</Work>' . $nl;
      $arrayTask[$line['reftype'].'#'.$line['refid']]['start']=$line['pstart'] . 'T' . $startAM ;
      $arrayTask[$line['reftype'].'#'.$line['refid']]['end']=$line['pend'] . 'T' . $endPM;
      $arrayTask[$line['reftype'].'#'.$line['refid']]['duration']=formatDuration($line['pduration'],$hoursPerDay);
      $arrayTask[$line['reftype'].'#'.$line['refid']]['pct']=$pct;
      $remainingDuration=0;
      $actualDuration=$line['pduration'];
      if ($pct==100) echo $tab.$tab.$tab.'<Stop>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</Stop>' . $nl;
      //else echo $tab.$tab.$tab.'<Stop></Stop>' . $nl;
      if ($pct==100) echo $tab.$tab.$tab.'<Resume>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</Resume>' . $nl;
      //else echo $tab.$tab.$tab.'<Resume></Resume>' . $nl;
//       if ($pct) {
//         // V1
//         $length=dayDiffDates($line['pstart'], $line['pend'])+1;
//         $lengthPct=$length*$pct/100;
//         $lengthDays=floor($lengthPct);
//         $lengthHours=$lengthPct-$lengthDays;
//         $lengthHH=floor($lengthHours*$hoursPerDay);
//         $lengthMM=floor((($lengthHours*$hoursPerDay)-$lengthHH)*60);
//         $stopDate=addDaysToDate($line['pstart'], $lengthDays);
//         $stop=$stopDate."T".htmlFixLengthNumeric(substr($startAM,0,2)+$lengthHH,2).":".htmlFixLengthNumeric($lengthMM,2).":00";
//         $resume=$stop;
//         $actualDuration=$line['pduration']*$pct/100;
//         $remainingDuration=$line['pduration']-$actualDuration;
//         if ($pct==100) {
//           echo $tab.$tab.$tab.'<Stop>'.$line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM).'</Stop>' . $nl;
//           echo $tab.$tab.$tab.'<Resume></Resume>' . $nl;          
//         } else {
//           echo $tab.$tab.$tab."<Stop>" . $stop . "</Stop>" . $nl;
//           echo $tab.$tab.$tab."<Resume>" . $resume . "</Resume>" . $nl;
//         }
//         $arrayTask[$line['reftype'].'#'.$line['refid']]['stop']=$stop;
//       } else {
//         echo $tab.$tab.$tab.'<Stop></Stop>' . $nl;
//         echo $tab.$tab.$tab.'<Resume></Resume>' . $nl;
//         $remainingDuration=$line['pduration'];
//         $actualDuration=0;
//       }
      //echo $tab.$tab.$tab.'<Resume>' . $line['pstart'] . 'T' . $startAM . '</Resume>' . $nl;
      echo $tab.$tab.$tab.'<ResumeValid>0</ResumeValid>' . $nl;
      echo $tab.$tab.$tab.'<EffortDriven>1</EffortDriven>' . $nl;
      echo $tab.$tab.$tab.'<Recurring>0</Recurring>' . $nl;
      echo $tab.$tab.$tab.'<OverAllocated>0</OverAllocated>' . $nl;
      echo $tab.$tab.$tab.'<Estimated>0</Estimated>' . $nl;
      echo $tab.$tab.$tab.'<Milestone>' . (($line['reftype']=='Milestone')?'1':'0') . '</Milestone>' . $nl;
      echo $tab.$tab.$tab.'<Summary>' . (($line['elementary'])?'0':'1') . '</Summary>' . $nl;
      echo $tab.$tab.$tab.'<DisplayAsSummary>' . (($line['elementary'])?'0':'1') . '</DisplayAsSummary>' . $nl;
      echo $tab.$tab.$tab.'<Critical>0</Critical>' . $nl;
      echo $tab.$tab.$tab.'<IsSubproject>0</IsSubproject>' . $nl;
      echo $tab.$tab.$tab.'<IsSubprojectReadOnly>0</IsSubprojectReadOnly>' . $nl;
      echo $tab.$tab.$tab.'<ExternalTask>0</ExternalTask>' . $nl;
      echo $tab.$tab.$tab.'<EarlyStart>' . $line['pstart'] . 'T' . $startAM . '</EarlyStart>' . $nl;
      echo $tab.$tab.$tab.'<EarlyFinish>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</EarlyFinish>' . $nl;
      echo $tab.$tab.$tab.'<LateStart>' . $line['pstart'] . 'T' . $startAM . '</LateStart>' . $nl;
      echo $tab.$tab.$tab.'<LateFinish>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</LateFinish>' . $nl;
      echo $tab.$tab.$tab.'<StartVariance>0</StartVariance>' . $nl;
      echo $tab.$tab.$tab.'<FinishVariance>0</FinishVariance>' . $nl;
      //echo $tab.$tab.$tab.'<WorkVariance>'.(round($line['plannedwork']*$hoursPerDay,0)*60*1000).'.00</WorkVariance>' . $nl;
      echo $tab.$tab.$tab.'<WorkVariance>0</WorkVariance>' . $nl;
      echo $tab.$tab.$tab.'<FreeSlack>0</FreeSlack>' . $nl;
      echo $tab.$tab.$tab.'<TotalSlack>0</TotalSlack>' . $nl;
      echo $tab.$tab.$tab.'<StartSlack>0</StartSlack>' . $nl;
      echo $tab.$tab.$tab.'<FinishSlack>0</FinishSlack>' . $nl;
      echo $tab.$tab.$tab.'<FixedCost>0</FixedCost>' . $nl;
      echo $tab.$tab.$tab.'<FixedCostAccrual>2</FixedCostAccrual>' . $nl;
      echo $tab.$tab.$tab.'<PercentComplete>'.$pct.'</PercentComplete>' . $nl;
      //echo $tab.$tab.$tab.'<PercentWorkComplete>'.$pct.'</PercentWorkComplete>' . $nl;
      echo $tab.$tab.$tab.'<Cost>0</Cost>' . $nl;
      echo $tab.$tab.$tab.'<OvertimeCost>0</OvertimeCost>' . $nl;
      echo $tab.$tab.$tab.'<OvertimeWork>PT0H0M0S</OvertimeWork>' . $nl;
      //if ($pct>0) echo $tab.$tab.$tab.'<ActualStart>' .  $line['pstart'] . 'T' . $startAM . '</ActualStart>' . $nl;
      echo $tab.$tab.$tab.'<ActualStart>' . (($line['pstart'])?$line['pstart'] . 'T' . $startAM:'') . '</ActualStart>' . $nl;
      echo $tab.$tab.$tab.'<ActualDuration>'.formatDuration($actualDuration,$hoursPerDay) .'</ActualDuration>' . $nl;
      if ($pct==100) echo $tab.$tab.$tab.'<ActualFinish>' . $line['pend'] . 'T' . (($line['reftype']=='Milestone')?$startAM:$endPM) . '</ActualFinish>' . $nl;
      echo $tab.$tab.$tab.'<ActualCost>0</ActualCost>' . $nl;
      echo $tab.$tab.$tab.'<ActualOvertimeCost>0</ActualOvertimeCost>' . $nl;
      echo $tab.$tab.$tab.'<ActualWork>PT' . formatWork($line['realwork'],$hoursPerDay) . '</ActualWork>' . $nl;
      echo $tab.$tab.$tab.'<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>' . $nl;
      echo $tab.$tab.$tab.'<RegularWork>PT' . formatWork($line['plannedwork'],$hoursPerDay) . '</RegularWork>' . $nl;
      //echo $tab.$tab.$tab.'<RemainingDuration>' .  formatDuration($remainingDuration,$hoursPerDay) . '</RemainingDuration>' . $nl;
      echo $tab.$tab.$tab.'<RemainingDuration>' .  formatDuration($remainingDuration,$hoursPerDay) . '</RemainingDuration>' . $nl;
      echo $tab.$tab.$tab.'<RemainingCost>0</RemainingCost>' . $nl;
      echo $tab.$tab.$tab.'<RemainingWork>PT' . formatWork($line['leftwork'],$hoursPerDay) . '</RemainingWork>' . $nl;
      echo $tab.$tab.$tab.'<RemainingOvertimeCost>0</RemainingOvertimeCost>' . $nl;
      echo $tab.$tab.$tab.'<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>' . $nl;
      echo $tab.$tab.$tab.'<ACWP>0.00</ACWP>' . $nl;
      echo $tab.$tab.$tab.'<CV>0.00</CV>' . $nl;
      echo $tab.$tab.$tab.'<ConstraintType>' . (($line['elementary'])?'2':'2') . '</ConstraintType>' . $nl;
      echo $tab.$tab.$tab.'<CalendarUID>-1</CalendarUID>' . $nl;
      if (1 or $line['elementary']) { echo $tab.$tab.$tab.'<ConstraintDate>' . $line['pstart'] . 'T' . $startAM . '</ConstraintDate>' . $nl;}
      echo $tab.$tab.$tab.'<LevelAssignments>0</LevelAssignments>' . $nl;
      echo $tab.$tab.$tab.'<LevelingCanSplit>0</LevelingCanSplit>' . $nl;
      echo $tab.$tab.$tab.'<LevelingDelay>0</LevelingDelay>' . $nl;
      echo $tab.$tab.$tab.'<LevelingDelayFormat>8</LevelingDelayFormat>' . $nl;
      echo $tab.$tab.$tab.'<IgnoreResourceCalendar>1</IgnoreResourceCalendar>' . $nl;
      echo $tab.$tab.$tab.'<HideBar>0</HideBar>' . $nl;
      echo $tab.$tab.$tab.'<Rollup>0</Rollup>' . $nl;
      echo $tab.$tab.$tab.'<BCWS>0.00</BCWS>' . $nl;
      echo $tab.$tab.$tab.'<BCWP>0.00</BCWP>' . $nl;
      echo $tab.$tab.$tab.'<PhysicalPercentComplete>'.(($line['elementary']==1)?$pct:$pct).'</PhysicalPercentComplete>' . $nl;
      echo $tab.$tab.$tab.'<EarnedValueMethod>0</EarnedValueMethod>' . $nl;
      /*<ExtendedAttribute>
        <FieldID>188743731</FieldID>
        <Value>lmk</Value>
        </ExtendedAttribute>*/
      //echo $tab.$tab.$tab.'<ActualWorkProtected>PT0H0M0S</ActualWorkProtected>' . $nl;
      //echo $tab.$tab.$tab.'<ActualOvertimeWorkProtected>PT0H0M0S</ActualOvertimeWorkProtected>' . $nl;
      $crit=array('successorId'=>$line['id']);
      $d=new Dependency();
      $depList=$d->getSqlElementsFromCriteria($crit,false);
      $nbHour = Parameter::getGlobalParameter('dayTime');
      foreach ($depList as $dep) {
        if (! isset($allItems[$dep->predecessorId])) continue;
        echo $tab.$tab.$tab.'<PredecessorLink>' . $nl;
        echo $tab.$tab.$tab.$tab.'<PredecessorUID>' . htmlEncode($dep->predecessorId) . '</PredecessorUID>' . $nl;
        echo $tab.$tab.$tab.$tab.'<Type>1</Type>' . $nl;
        echo $tab.$tab.$tab.$tab.'<CrossProject>0</CrossProject>' . $nl;
        $delai = 0;
        if($dep->dependencyDelay){
          $delai = $nbHour*$dep->dependencyDelay*600;
        }
        echo $tab.$tab.$tab.$tab.'<LinkLag>'.$delai.'</LinkLag>' . $nl;
        echo $tab.$tab.$tab.$tab.'<LagFormat>7</LagFormat>' . $nl;
        echo $tab.$tab.$tab.'</PredecessorLink>' . $nl;
      }
      echo $tab.$tab.$tab.'<IsPublished>1</IsPublished>' . $nl;
      echo $tab.$tab.$tab.'<CommitmentType>0</CommitmentType>' . $nl;
//       if ($line['reftype']!='Milestone') {
//         echo $tab.$tab.$tab.'<TimephasedData>' . $nl;
//         echo $tab.$tab.$tab.'<Type>11</Type>' . $nl;
//         echo $tab.$tab.$tab.'<UID>'.$line['id'].'</UID>' . $nl;
//         echo $tab.$tab.$tab.'<Start>' . $line['pstart'] . 'T' . $startAM . '</Start>' . $nl;
//         echo $tab.$tab.$tab.'<Finish>' . $line['pend'] . 'T' . $endPM . '</Finish>' . $nl;
//         echo $tab.$tab.$tab.'<Unit>3</Unit>' . $nl;
//         echo $tab.$tab.$tab.'<Value>'.$pct.'</Value>' . $nl;
//         echo $tab.$tab.$tab.'</TimephasedData>' . $nl;
//       }
      echo $tab.$tab.$tab.'<projeqtorPlanningModeId>'.$line["idplanningmode"].'</projeqtorPlanningModeId>'. $nl;;
      echo $tab.$tab.$tab.'<projeqtorPlanningModeName>'.SqlList::getNameFromId('PlanningMode',$line['idplanningmode']).'</projeqtorPlanningModeName>'. $nl;;
      echo $tab.$tab.'</Task>' . $nl;
    }
    echo $tab.'</Tasks>' . $nl;
    $arrayRessource=array();
    echo $tab.'<Resources>' . $nl;
    foreach ($resourceList as $resource) {
      if ($exportAssignments==false) continue;
      $arrayResource[$resource->id]=$resource;
      echo $tab.$tab."<Resource>" . $nl;
      echo $tab.$tab.$tab."<UID>" . htmlEncode($resource->id) . "</UID>" . $nl;
      echo $tab.$tab.$tab."<ID>" . htmlEncode($resource->id) . "</ID>" . $nl;
      echo $tab.$tab.$tab."<Name>" . htmlEncode($resource->name,'xml') . "</Name>" . $nl;
      echo $tab.$tab.$tab."<Type>1</Type>" . $nl;
      echo $tab.$tab.$tab."<IsNull>0</IsNull>" . $nl;
      echo $tab.$tab.$tab."<Initials>" . htmlEncode($resource->initials,'xml') . "</Initials>" . $nl;
      echo $tab.$tab.$tab."<Group>" . htmlEncode(SqlList::getNameFromId('Team',$resource->idTeam),'xml') . "</Group>" . $nl;
      echo $tab.$tab.$tab."<WorkGroup>0</WorkGroup>" . $nl;
      echo $tab.$tab.$tab."<EmailAddress>" . htmlEncode($resource->email,'xml') . "</EmailAddress>" . $nl;
      echo $tab.$tab.$tab."<MaxUnits>" . htmlEncode($resource->capacity) . "</MaxUnits>" . $nl;
      echo $tab.$tab.$tab."<PeakUnits>0</PeakUnits>" . $nl;
      echo $tab.$tab.$tab."<OverAllocated>0</OverAllocated>" . $nl;
      echo $tab.$tab.$tab."<CanLevel>1</CanLevel>" . $nl;
      echo $tab.$tab.$tab."<AccrueAt>3</AccrueAt>" . $nl;
      echo $tab.$tab.$tab."<Work>PT0H0M0S</Work>" . $nl;
      echo $tab.$tab.$tab."<RegularWork>PT0H0M0S</RegularWork>" . $nl;
      echo $tab.$tab.$tab."<OvertimeWork>PT0H0M0S</OvertimeWork>" . $nl;
      echo $tab.$tab.$tab."<ActualWork>PT0H0M0S</ActualWork>" . $nl;
      echo $tab.$tab.$tab."<RemainingWork>PT0H0M0S</RemainingWork>" . $nl;
      echo $tab.$tab.$tab."<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>" . $nl;
      echo $tab.$tab.$tab."<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>" . $nl;
      echo $tab.$tab.$tab."<PercentWorkComplete>0</PercentWorkComplete>" . $nl;
      $rate=0;
      $critCost=array('idResource'=>$resource->id, 'endDate'=>null);
      $rc=new ResourceCost();
      $rcList=$rc->getSqlElementsFromCriteria($critCost, false, null, ' startDate desc');
      if (count($rcList)>0) {
      	$rate=($hoursPerDay)?round($rcList[0]->cost / $hoursPerDay,2):0;

      }
      echo $tab.$tab.$tab."<StandardRate>" . $rate . "</StandardRate>" . $nl;
      echo $tab.$tab.$tab."<StandardRateFormat>3</StandardRateFormat>" . $nl;
      echo $tab.$tab.$tab."<Cost>0</Cost>" . $nl;
      echo $tab.$tab.$tab."<OvertimeRate>0</OvertimeRate>" . $nl;
      echo $tab.$tab.$tab."<OvertimeRateFormat>3</OvertimeRateFormat>" . $nl;
      echo $tab.$tab.$tab."<OvertimeCost>0</OvertimeCost>" . $nl;
      echo $tab.$tab.$tab."<CostPerUse>0</CostPerUse>" . $nl;
      echo $tab.$tab.$tab."<ActualCost>0</ActualCost>" . $nl;
      echo $tab.$tab.$tab."<ActualOvertimeCost>0</ActualOvertimeCost>" . $nl;
      echo $tab.$tab.$tab."<RemainingCost>0</RemainingCost>" . $nl;
      echo $tab.$tab.$tab."<RemainingOvertimeCost>0</RemainingOvertimeCost>" . $nl;
      echo $tab.$tab.$tab."<WorkVariance>0</WorkVariance>" . $nl;
      echo $tab.$tab.$tab."<CostVariance>0</CostVariance>" . $nl;
      echo $tab.$tab.$tab."<SV>0</SV>" . $nl;
      echo $tab.$tab.$tab."<CV>0</CV>" . $nl;
      echo $tab.$tab.$tab."<ACWP>0</ACWP>" . $nl;
      echo $tab.$tab.$tab."<CalendarUID>" . htmlEncode($resource->id) . "</CalendarUID>" . $nl;
      echo $tab.$tab.$tab."<BCWS>0</BCWS>" . $nl;
      echo $tab.$tab.$tab."<BCWP>0</BCWP>" . $nl;
      echo $tab.$tab.$tab."<IsGeneric>0</IsGeneric>" . $nl;
      echo $tab.$tab.$tab."<IsInactive>0</IsInactive>" . $nl;
      echo $tab.$tab.$tab."<IsEnterprise>0</IsEnterprise>" . $nl;
      echo $tab.$tab.$tab."<BookingType>0</BookingType>" . $nl;
      //echo "<ActualWorkProtected>PT0H0M0S</ActualWorkProtected>" . $nl;
      //echo "<ActualOvertimeWorkProtected>PT0H0M0S</ActualOvertimeWorkProtected>" . $nl;
      //echo "<CreationDate></CreationDate>" . $nl;
      echo $tab.$tab."</Resource>" . $nl;
    }
    echo $tab."</Resources>" . $nl;
    $ass=new Assignment();
    $clauseWhere="(refType,refId) in ($selectItems)";
    $lstAss=$ass->getSqlElementsFromCriteria(null, false, $clauseWhere, 'refType, refId, idResource', true);
    $currentKey=null;
    $precId=null;
    // Merge assignments for same resource on same activity
    foreach ($lstAss as $idAss=>$ass) {
      $key="$ass->refType#$ass->refId#$ass->idResource";
      if ($precId and $currentKey==$key) {
        $precAss=$lstAss[$precId];
        $precAss->assignedWork+=$ass->assignedWork;
        $precAss->realWork+=$ass->realWork;
        $precAss->leftWork+=$ass->leftWork;
        $precAss->plannedWork+=$ass->plannedWork;
        if ($ass->realStartDate and $ass->realStartDate<$precAss->realStartDate) $precAss->realStartDate=$ass->realStartDate;
        if ($ass->plannedStartDate and $ass->plannedStartDate<$precAss->plannedStartDate) $precAss->plannedStartDate=$ass->plannedStartDate;
        if ($ass->realEndDate and $ass->realEndDate>$precAss->realEndDate) $precAss->realEndDate=$ass->realEndDate;
        if ($ass->plannedEndDate and $ass->plannedEndDate>$precAss->plannedEndDate) $precAss->plannedEndDate=$ass->plannedEndDate;
        unset($lstAss[$idAss]);
        $lstAss[$precId]=$precAss;
      } else {
        $precId=$idAss;
        $currentKey=$key;
      }
    }
    echo $tab.'<Assignments>' . $nl;
    foreach ($lstAss as $ass) {
      if ($exportAssignments==false) continue;
      //if ($ass->plannedWork==0) continue;
    	if (array_key_exists($ass->refType . '#' . $ass->refId, $arrayTask)) {
    	  $task=$arrayTask[$ass->refType . '#' . $ass->refId];
    	  if (isset($arrayResource[$ass->idResource])) {
          $res=$arrayResource[$ass->idResource];
    	  } else {
    	    $res=new Resource($ass->idResource,true);
    	    $arrayResource[$ass->idResource]=$res;
    	  }
    	  // From Assignment
    	  $pctAss=(floatval($ass->plannedWork))?round($ass->realWork/$ass->plannedWork*100,2):(($ass->leftWork==0)?100:0);
    	  $assStart=($ass->realStartDate)?$ass->realStartDate:$ass->plannedStartDate;
    	  $assEnd=($ass->leftWork==0)?$ass->realEndDate:$ass->plannedEndDate;
    	  $assPlan=$ass->plannedWork;
    	  $assReal=$ass->realWork;
    	  $assLeft=$ass->leftWork;
    	  // From Activity
    	  $pctAss=$task['pct'];
    	  //$assStart=$task['start'];
    	  //$assEnd=$task['end'];
    	  //$assPlan=$ass->plannedWork;
    	  //$assReal=$assPlan*$pctAss/100;
    	  //$assLeft=$assPlan-$assReal;
	      echo $tab.$tab."<Assignment>" . $nl;
	      echo $tab.$tab.$tab."<UID>" . htmlEncode($ass->id) . "</UID>" . $nl;
	      echo $tab.$tab.$tab."<TaskUID>" . $arrayTask[$ass->refType . '#' . $ass->refId]['id'] . "</TaskUID>" . $nl;
	      echo $tab.$tab.$tab."<ResourceUID>" . htmlEncode($ass->idResource) . "</ResourceUID>" . $nl;	      
	      echo $tab.$tab.$tab."<PercentWorkComplete>".(($pctAss==100 or $pctAss==0)?'':$pctAss)."</PercentWorkComplete>" . $nl;
	      echo $tab.$tab.$tab."<ActualCost>0</ActualCost>" . $nl;
	      echo $tab.$tab.$tab."<ActualOvertimeCost>0</ActualOvertimeCost>" . $nl;
	      echo $tab.$tab.$tab."<ActualOvertimeWork>PT0H0M0S</ActualOvertimeWork>" . $nl;
	      echo $tab.$tab.$tab."<ActualStart>" . (($assStart)?$assStart . "T" . $startAM:'') . "</ActualStart>" . $nl;
	      echo $tab.$tab.$tab."<ActualWork>PT" . formatWork($assReal,$hoursPerDay) ."</ActualWork>" . $nl;
	      echo $tab.$tab.$tab."<ACWP>0</ACWP>" . $nl;
	      echo $tab.$tab.$tab."<Confirmed>0</Confirmed>" . $nl;
	      echo $tab.$tab.$tab."<Cost>0</Cost>" . $nl;
	      echo $tab.$tab.$tab."<CostRateTable>0</CostRateTable>" . $nl;
	      echo $tab.$tab.$tab."<RateScale>0</RateScale>" . $nl;
	      echo $tab.$tab.$tab."<CostVariance>0</CostVariance>" . $nl;
	      echo $tab.$tab.$tab."<CV>0</CV>" . $nl;
	      echo $tab.$tab.$tab."<Delay>0</Delay>" . $nl;
	      echo $tab.$tab.$tab."<Finish>" . htmlEncode($assEnd) . "T" . $endPM . "</Finish>" . $nl;
	      echo $tab.$tab.$tab."<FinishVariance>0</FinishVariance>" . $nl;
	      //echo $tab.$tab.$tab."<WorkVariance>".(round($ass->plannedWork*$hoursPerDay,0)*60*1000).".00</WorkVariance>" . $nl;
	      echo $tab.$tab.$tab."<WorkVariance>0.00</WorkVariance>" . $nl;
	      echo $tab.$tab.$tab."<HasFixedRateUnits>1</HasFixedRateUnits>" . $nl;
	      echo $tab.$tab.$tab."<FixedMaterial>0</FixedMaterial>" . $nl;
	      echo $tab.$tab.$tab."<LevelingDelay>0</LevelingDelay>" . $nl;
	      echo $tab.$tab.$tab."<LevelingDelayFormat>39</LevelingDelayFormat>" . $nl;
	      echo $tab.$tab.$tab."<LinkedFields>0</LinkedFields>" . $nl;
	      echo $tab.$tab.$tab."<Milestone>".(($ass->refType=='Milestone')?'1':'0')."</Milestone>" . $nl;
	      echo $tab.$tab.$tab."<Overallocated>0</Overallocated>" . $nl;
	      echo $tab.$tab.$tab."<OvertimeCost>0</OvertimeCost>" . $nl;
	      echo $tab.$tab.$tab."<OvertimeWork>PT0H0M0S</OvertimeWork>" . $nl;
	      echo $tab.$tab.$tab."<RegularWork>PT" . formatWork($assPlan,$hoursPerDay) . "</RegularWork>" . $nl;
	      echo $tab.$tab.$tab."<RemainingCost>0</RemainingCost>" . $nl;
	      echo $tab.$tab.$tab."<RemainingOvertimeCost>0</RemainingOvertimeCost>" . $nl;
	      echo $tab.$tab.$tab."<RemainingOvertimeWork>PT0H0M0S</RemainingOvertimeWork>" . $nl;
	      echo $tab.$tab.$tab."<RemainingWork>PT" . formatWork($assLeft,$hoursPerDay) ."</RemainingWork>" . $nl;
	      echo $tab.$tab.$tab."<ResponsePending>0</ResponsePending>" . $nl;
	      echo $tab.$tab.$tab.'<Start>'. (($assStart)?$assStart . "T" . $startAM:'') . '</Start>'. $nl;
	      if ($pctAss==100) echo $tab.$tab.$tab."<Stop>" . htmlEncode($assEnd) . "T" . $endPM . "</Stop>" . $nl;
	      //else echo $tab.$tab.$tab.'<Stop></Stop>' . $nl;
	      if ($pctAss==100) echo $tab.$tab.$tab."<Resume>" . htmlEncode($assEnd) . "T" . $endPM . "</Resume>" . $nl;
	      //else echo $tab.$tab.$tab.'<Resume></Resume>' . $nl;
// 	      if ($pctAss) {
// 	        $length=dayDiffDates($assStart, $assEnd)+1;
// 	        $lengthPct=$length*$pctAss/100;
// 	        $lengthDays=floor($lengthPct);
// 	        $lengthHours=$lengthPct-$lengthDays;
// 	        $lengthHH=floor($lengthHours*$hoursPerDay);
// 	        $lengthMM=floor((($lengthHours*$hoursPerDay)-$lengthHH)*60);
// 	        $stopDate=addDaysToDate($assStart, $lengthDays);
// 	        $stop=($pctAss)?$stopDate."T".htmlFixLengthNumeric(substr($startAM,0,2)+$lengthHH,2).":".htmlFixLengthNumeric($lengthMM,2).":00":'';
// 	        $resume=($pctAss<100)?$stop:'';
// 	        echo $tab.$tab.$tab."<Stop>" . $stop . "</Stop>" . $nl;
// 	        echo $tab.$tab.$tab."<Resume>" . $resume . "</Resume>" . $nl;
// 	      } else {
// 	        echo $tab.$tab.$tab.'<Stop></Stop>' . $nl;
// 	        echo $tab.$tab.$tab.'<Resume></Resume>' . $nl;
// 	      }
	      
	      
// 	      if ($pctAss) {
// 	        echo $tab.$tab.$tab."<Stop>" . htmlEncode($task['stop']) . "</Stop>" . $nl;
// 	        echo $tab.$tab.$tab."<Resume>" . htmlEncode($task['stop']) . "</Resume>" . $nl;
// 	      } else {
// 	        echo $tab.$tab.$tab.'<Stop>'. (($assStart)?$assStart . "T" . $startAM:'') .'</Stop>' . $nl;
// 	        echo $tab.$tab.$tab.'<Resume>'. (($assStart)?$assStart . "T" . $startAM:'') .'</Resume>' . $nl;
// 	      }
	      echo $tab.$tab.$tab."<StartVariance>0</StartVariance>" . $nl;
	      echo $tab.$tab.$tab."<Units>" . round(($res->capacity * $ass->rate / 100),1) . "</Units>" . $nl;
	      echo $tab.$tab.$tab."<UpdateNeeded>0</UpdateNeeded>" . $nl;
	      echo $tab.$tab.$tab."<VAC>0.00</VAC>" . $nl;
	      echo $tab.$tab.$tab."<Work>PT" . formatWork($ass->plannedWork,$hoursPerDay) . "</Work>" . $nl;
	      echo $tab.$tab.$tab."<WorkContour>0</WorkContour>" . $nl;
	      echo $tab.$tab.$tab."<BCWS>0</BCWS>" . $nl;
	      echo $tab.$tab.$tab."<BCWP>0</BCWP>" . $nl;
	      echo $tab.$tab.$tab."<BookingType>0</BookingType>" . $nl;
	      //echo "<ActualWorkProtected>PT0H0M0S</ActualWorkProtected>" . $nl;
	      //echo "<ActualOvertimeWorkProtected>PT0H0M0S</ActualOvertimeWorkProtected>" . $nl;
	      //echo "<CreationDate>2011-11-18T21:06:00</CreationDate>" . $nl;
	      //echo "<TimephasedData>" . $nl;
	      //echo "<Type>1</Type>" . $nl;
	      //echo "<UID>1</UID>" . $nl;
	      //echo "<Start>" . htmlEncode($ass->plannedStartDate) . "T08:00:00</Start>" . $nl;
	      //echo "<Finish>" . htmlEncode($ass->plannedEndDate) . "T08:00:00</Finish>" . $nl;
	      //echo "<Unit>2</Unit>" . $nl;
	      //echo "<Value>PT8H0M0S</Value>" . $nl;
	      //echo "</TimephasedData>" . $nl;
	      echo $tab.$tab."</Assignment>" . $nl;
    	}
    }
    echo $tab."</Assignments>" . $nl;
    echo '</Project>' . $nl;
  }

  function formatDuration($duration, $hoursPerDay) {
    $hourDuration=$duration*$hoursPerDay;
  	$res = 'PT' . round($hourDuration,0) . 'H0M0S';
  	return $res;
  }
  
  function formatWork($work, $hoursPerDay) {
    //if ($work<0.01) return '0H0M0S';
    $hWork=$work*$hoursPerDay;
    $h=floor($hWork);
    $m=floor(($hWork-$h)*60);
    return $h.'H'.$m.'M0S';
  }
  
  function getCurrencyCode($currency) {
    if ($currency=='â‚¬') return 'EUR';
    else if ($currency=='$') return 'USD';
    else if ($currency=='Â£') return 'GBP';
  }
  function getDurationFormat() {
    return 7; // Dureation must always be in days
    if (Work::getWorkUnit()=='days') return 7;
    else if  (Work::getWorkUnit()=='hours') return 5;
    return 7; // By default return 7, but should not be reached
  }
  function getWorkFormat() {
    if (Work::getWorkUnit()=='days') return 3;
    else if  (Work::getWorkUnit()=='hours') return 2;
    return 3; // By default return 7, but should not be reached
  }
?>
