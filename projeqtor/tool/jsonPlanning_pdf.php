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
  scriptLog('   ->/tool/jsonPlanning_pdf.php');
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
  $user=getSessionUser();
  $starDate="";
  $endDate="";
  if (array_key_exists('startDatePlanView',$_REQUEST) and array_key_exists('endDatePlanView',$_REQUEST)) {
    $starDate= trim($_REQUEST['startDatePlanView']);
    $endDate= trim($_REQUEST['endDatePlanView']);
    //$user=getSessionUser();
    $paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningStartDate'));
    $paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningEndDate'));
    if ($saveDates) {
      $paramStart->parameterValue=$starDate;
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
  // Header
  if ( array_key_exists('report',$_REQUEST) ) {
    $headerParameters="";
    if (array_key_exists('startDate',$_REQUEST) and trim($_REQUEST['startDate'])!="") {
      $headerParameters.= i18n("colStartDate") . ' : ' . dateFormatter($_REQUEST['startDate']) . '<br/>';
    }
    if (array_key_exists('endDate',$_REQUEST) and trim($_REQUEST['endDate'])!="") {
      $headerParameters.= i18n("colEndDate") . ' : ' . dateFormatter($_REQUEST['endDate']) . '<br/>';
    }
    if (array_key_exists('format',$_REQUEST)) {
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
  if ( ! ( $accessRightRead!='ALL' or (sessionValueExists('project') and getSessionValue('project')!='*') and strpos(getSessionValue('project'), ",") === null)
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
  	//$queryWhere.=getAccesRestrictionClause('Project',$table);
    $queryWhere.='( ('.getAccesRestrictionClause('Project',$table).')';
  	$queryWhere.=' OR ('.getAccesRestrictionClause('Milestone',$table,$showIdleProjects).') )';
  } else {
    $queryWhere.=getAccesRestrictionClause('Activity',$table,$showIdleProjects);
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
  $queryWhere.=  $table . ".idProject not in " . Project::getAdminitrativeProjectList() ;

  $querySelect .= $table . ".* ";
  $queryFrom .= $table;

  $queryOrderBy .= $table . ".wbsSortable ";
  
  $showMilestone=false;
  if ($portfolio) {
    $queryWhere.=' and ( refType=\'Project\' ';
    if (array_key_exists('showMilestone',$_REQUEST) ) {
      $showMilestone=trim($_REQUEST['showMilestone']);
    } else {
      $showMilestoneObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowMilestone'));
      $showMilestone=trim($showMilestoneObj->parameterValue);
    }
    if ($showMilestone) {
      $queryWhere.=' or refType=\'Milestone\' ';
    }
    $queryWhere.=')';
  }
  
  // constitute query and execute
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query='select ' . $querySelect
       . ' from ' . $queryFrom
       . ' where ' . $queryWhere
       . ' order by ' . $queryOrderBy;
  $result=Sql::query($query);
  if (isset($debugJsonQuery) and $debugJsonQuery) { // Trace in configured to
    debugTraceLog("jsonPlanning_pdf: ".$query); // Trace query
    debugTraceLog("  => error (if any) = ".Sql::$lastQueryErrorCode.' - '.Sql::$lastQueryErrorMessage);
    debugTraceLog("  => number of lines returned = ".Sql::$lastQueryNbRows);
  }
  $nbRows=0;
  if ($print) {
    if ( array_key_exists('report',$_REQUEST) ) {
      $test=array();
      if (Sql::$lastQueryNbRows > 0) $test[]="OK";
      if (checkNoData($test))  exit;
    }
    displayGantt($result);
  } else {
    traceLog("jsonPlanning_pdf called in non print mode ?????");
  }

  /**
  *
  * displayGantt
  *
  **/

  function displayGantt($result) {
  	global $displayResource, $outMode, $showMilestone, $portfolio, $columnsDescription;
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
    }

    $endDate='';
    if (array_key_exists('endDate',$_REQUEST)) {
      $endDate=$_REQUEST['endDate'];
    }
    $format='day';
    if (array_key_exists('format',$_REQUEST)) {
      $format=$_REQUEST['format'];
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
    if (Sql::$lastQueryNbRows > 0) {
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
        if($line['reftype'] == 'Project') {
          $project = new Project($line['refid']);
          $line['color'] = $project->color;
          $type = new Type($project->idProjectType);
          $line['type'] = $type->name;
          $status = new Status($project->idStatus);
          $line['status'] = $status->name;
          $line['statuscolor'] = $status->color;
        } else if ($columnsDescription['IdStatus']['show']==1 or $columnsDescription['Type']['show']==1) {
          $ref=$line['reftype'];
          if ($ref=='PeriodicMeeting') $ref='Meeting';
          $type='id'.$ref.'Type';
          $item=new $ref($line['refid'],true);
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
	  $table_witdh = "97%";
	  //Init tab sizes
	  if($format == "day"){
		  if($topUnits < 11){
			$left_size = 0.4;
		  } else if($topUnits < 21){
			$left_size = 0.3;
		  } else {
			$left_size = 0.2;
		  }
	  } else if($format=='week') {
		  if($topUnits < 24){
			$left_size = 0.4;
		  } else if($topUnits < 34){
			$left_size = 0.3;
		  } else if($topUnits < 44){
			$left_size = 0.25;
		  } else {
			$left_size = 0.2;
		  }
	  } else if($format=='month') {
		  if($topUnits < 21){
			$left_size = 0.4;
		  } else if($topUnits < 31){
			$left_size = 0.35;
		  } else {
			$left_size = 0.3;
		  }
		  $table_witdh = "96%";
    } else if($format=='quarter') {
      if($topUnits < 21){
      $left_size = 0.4;
      } else if($topUnits < 31){
      $left_size = 0.35;
      } else {
      $left_size = 0.3;
      }
      $table_witdh = "96%";	  
    }
	  $right_size = 1 - $left_size;
	  $fontsize_global = $left_size * 1.5;

      // Header
    $sortArray=array_merge(array(), Parameter::getPlanningColumnOrder());
    $cptSort=0;
    unset($columnsDescription['ObjectType']);
    unset($columnsDescription['ExterRes']);
    foreach ($columnsDescription as $ganttCol) { 
      if ($ganttCol['show']==1) $cptSort++; 
    }
      echo '<table style="font-size:'.($fontsize_global*100).'%; border: 1px solid #AAAAAA; margin: 0px; padding: 0px;height: 100%;width:'.$table_witdh.'">';
      echo '<tr style="height: 2%;width:100%;padding:0px;margin:0px;">
			<td colspan="' . (1+$cptSort) . '" style="width:'.($left_size*100).'%;padding:0px;margin:0px;">&nbsp;</td>';
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
        }
        echo '<td class="reportTableHeader" colspan="' . $span . '" style="width:'.(($right_size*100)/$topUnits).'%;padding:0px;margin:0px;">';
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
      echo '<TR style="height: 2%;width:100%;padding:0px;margin:0px;">';
      echo '  <TD class="reportTableHeader" style="border-right:0px;width:'.(5*$left_size).'%padding:0px;margin:0px;"></TD>';
      echo '  <TD class="reportTableHeader" style=" border-left:0px; text-align: left;width:'.(19*$left_size).'%;padding:0px;margin:0px;">' . i18n('colTask') . '</TD>';
      foreach ($sortArray as $col) {   
        if (isset($columnsDescription[$col]) and $columnsDescription[$col]['show']!=1) continue;
        if ($col=='ValidatedWork') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colValidated') . '</TD>' ;
        if ($col=='AssignedWork') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colAssigned') . '</TD>' ;
        if ($col=='RealWork') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colReal') . '</TD>' ;
        if ($col=='LeftWork') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colLeft') . '</TD>' ;
        if ($col=='PlannedWork') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colReassessed') . '</TD>' ;
        if ($col=='ValidatedCost') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">'. i18n('colValidatedCost') . '</TD>' ;
        if ($col=='AssignedCost') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colAssignedCost') . '</TD>' ;
        if ($col=='RealCost') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colRealCost') . '</TD>' ;
        if ($col=='LeftCost') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colLeftCost') . '</TD>' ;
        if ($col=='PlannedCost') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colPlannedCost') . '</TD>' ;
        if ($col=='Type') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colType') . '</TD>' ;
        if ($col=='IdStatus') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colIdStatus') . '</TD>' ;
        if ($col=='Duration') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">' . i18n('colDuration') . '</TD>' ;
        if ($col=='Progress') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colPct') . '</TD>' ;
        if ($col=='StartDate') echo '  <TD class="reportTableHeader" style="width:'.(8*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colStart') . '</TD>' ;
        if ($col=='EndDate') echo '  <TD class="reportTableHeader" style="width:'.(8*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colEnd') . '</TD>' ;
        if ($col=='Resource') echo '  <TD class="reportTableHeader" style="width:'.(10*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colResource') . '</TD>' ;
        if ($col=='Priority') echo '  <TD class="reportTableHeader" style="width:'.(5*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colPriorityShort') . '</TD>' ;
        if ($col=='IdPlanningMode') echo '  <TD class="reportTableHeader" style="width:'.(10*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colIdPlanningMode') . '</TD>' ;   
        if ($col=='Id') echo '  <TD class="reportTableHeader" style="width:'.(3*$left_size).'%;padding:0px;margin:0px;">'  . i18n('colId') . '</TD>' ;
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
		      $font_size_header = "90%";
        } else if($format=='week') {
          $title=substr(htmlFormatDate($day),0,5);
		      $font_size_header = "100%";
        } else if ($format=='day') {
          $color=($openDays[$i]==1)?'':'background-color:' . $weekendColor . ';';
          $title=substr($days[$i],-2);
				  if($topUnits < 10){
					$font_size_header = "100%";
				  } else if(($topUnits <16) or (($topUnits > 20) and ($topUnits < 26))){
					$font_size_header = "90%";
				  } else if(($topUnits <18) or (($topUnits > 25) and ($topUnits < 30))){
					$font_size_header = "80%";
				  } else if(($topUnits <21) or (($topUnits > 29) and ($topUnits < 36))){
					$font_size_header = "70%";
				  } else {
					$font_size_header = "60%";
				  }
        } else if ($format=='quarter') {
          $tDate = explode("-", $day);
          $date= mktime(0, 0, 0, $tDate[1], $tDate[2]+1, $tDate[0]);
          $title=substr($day,5,2);
          $span=numberOfDaysOfMonth($day);
          $font_size_header = "90%";
        }
        echo '<td class="reportTableColumnHeader" colspan="' . $span . '" style="font-size:'.$font_size_header.';magin:0px;padding:0px;width:'.(($right_size*100)/$numUnits).'%;' . $color . '">';
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
      $closedWbs='';
      foreach ($resultArray as $line) {
        $pEnd=$line['pend'];
        $pStart=$line['pstart'];
        $realWork=$line['realwork'];
        $plannedWork=$line['plannedwork'];
        $progress=$line['progress'];

        // pGroup : is the tack a group one ?
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
          $compStyle="font-weight: bold; background: #E8E8E8;padding:0px;margin:0px;";
          $bgColor="background: #E8E8E8;";
        } else if( $line['reftype']=='Milestone'){
          $rowType  = "mile";
        } else {
          $rowType  = "row";
        }
        $wbs=$line['wbssortable'];
        $level=(strlen($wbs)+1)/4;
        $tab="";
        /*for ($i=1;$i<$level;$i++) {
          $tab.='<span class="ganttSep" >&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        }     */
        $pName=($showWbs)?$line['wbs']." ":"";
        $pName.= htmlEncode($line['refname']);
        $duration=($rowType=='mile' or $pStart=="" or $pEnd=="")?'-':workDayDiffDates($pStart, $pEnd) . "&nbsp;" . i18n("shortDay");
        //echo '<TR class="dojoDndItem ganttTask' . $rowType . '" style="margin: 0px; padding: 0px;">';

        if ($closedWbs and $closedWbs!=$line['wbssortable']) {
          //echo ' display:none;';
          continue;
        }
        echo '<TR style="height:2%;width:100%;padding:0px;margin:0px;' ;
        echo '">';
        echo '  <TD class="reportTableData" style="height:100%;border-right:0px;' . $compStyle . 'width:'.(5*$left_size).'%;">'.formatIcon($line['reftype'], 16).'</TD>';
        echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . 'width:'.(19*$left_size).'%;"><span class="nobr">' . $tab ;
        echo '<span style="height:100%;vertical-align:middle;">';
        if ($pGroup) {
          if ($collapsed) {
            echo '<img style="height:50%" src="../view/css/images/plus.gif" />';
          } else {
            echo '<img style="height:50%" src="../view/css/images/minus.gif" />';
          }
        } else {
        	if ($line['reftype']=='Milestone') {
        		echo '<img style="height:50%" src="../view/css/images/mile.gif" />';
        	} else {
            echo '<img style="height:50%" src="../view/css/images/none.gif" />';
        	}
        }
        //<div style="float: left;width:16px;">&nbsp;</div></span>';
        echo '</span>&nbsp;';
        echo $pName . '</span></TD>';
        foreach ($sortArray as $col) {
          if (isset($columnsDescription[$col]) and $columnsDescription[$col]['show']!=1) continue;
          if ($col=='ValidatedWork') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' . Work::displayWorkWithUnit($line["validatedwork"])  . '</TD>' ;
          if ($col=='AssignedWork') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  Work::displayWorkWithUnit($line["assignedwork"])  . '</TD>' ;
          if ($col=='RealWork') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  Work::displayWorkWithUnit($line["realwork"])  . '</TD>' ;
          if ($col=='LeftWork') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  Work::displayWorkWithUnit($line["leftwork"])  . '</TD>' ;
          if ($col=='PlannedWork') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  Work::displayWorkWithUnit($line["plannedwork"])  . '</TD>' ;
          if ($col=='ValidatedCost') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' . costFormatter($line["validatedcost"])  . '</TD>' ;
          if ($col=='AssignedCost') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  costFormatter($line["assignedcost"])  . '</TD>' ;
          if ($col=='RealCost') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  costFormatter($line["realcost"])  . '</TD>' ;
          if ($col=='LeftCost') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  costFormatter($line["leftcost"])  . '</TD>' ;
          if ($col=='PlannedCost') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  costFormatter($line["plannedcost"])  . '</TD>' ;
          if ($col=='Type') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' . ($line["type"])  . '</TD>' ;
          if ($col=='IdStatus') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' .  ($line["status"])  . '</TD>' ;
          if ($col=='Duration') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' . $duration  . '</TD>' ;
          if ($col=='Progress') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(5*$left_size).'%;" >' . percentFormatter($progress) . '</TD>' ;
          if ($col=='StartDate') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(8*$left_size).'%;">'  . (($pStart)?dateFormatter($pStart):'-') . '</TD>' ;
          if ($col=='EndDate') echo '  <TD class="reportTableData" style="' . $compStyle . 'width:'.(8*$left_size).'%;">'  . (($pEnd)?dateFormatter($pEnd):'-') . '</TD>' ;
          if ($col=='Resource') echo '  <TD class="reportTableData" style="text-align:left;' . $compStyle . 'width:'.(10*$left_size).'%;" >' . $line["resource"]  . '</TD>' ;
          if ($col=='Priority') echo '  <TD class="reportTableData" style="text-align:center;' . $compStyle . 'width:'.(5*$left_size).'%;" >' . $line["priority"]  . '</TD>' ;
          if ($col=='IdPlanningMode') echo '  <TD class="reportTableData" style="text-align:left;' . $compStyle . 'width:'.(10*$left_size).'%;white-space:nowrap" >' . SqlList::getNameFromId('PlanningMode', $line["idplanningmode"])  . '</TD>' ;
          if ($col=='Id') echo '  <TD class="reportTableData" style="text-align:right;' . $compStyle . 'width:'.(3*$left_size).'%;" >' . $line["id"]  . '</TD>' ;
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
              echo '<td class="reportTableData" style="' . $color . $noBorder . ';color:' . $pColor . ';width:'.(($right_size*100)/$numDays).'%;">';
              if($progress < 100) {
                echo '&loz;' ;
              } else {
                echo '&diams;' ;
              }
            } else {
              $subHeight=round((18-$height)/2);
              echo '<td class="reportTableData" style="padding:0px;margin:0px;font-size:'.$fontSize.';' . $color . '; vertical-align: middle;' . $noBorder . ';width:'.(($right_size*100)/$numDays).'%;">';
              if ($pGroup and ($days[$i]==$pStart or $days[$i]==$pEnd) and $outMode!='pdf') {
                echo '<div class="ganttTaskgroupBarExtInvisible" style="float:left; height:4px"></div>';
              }
              echo '<table width="100%" height="' .  $height . 'px" >';
              //echo '<tr style="height:' . $subHeight . 'px;"><td style="' . $noBorder . '"></td></tr>';
              echo '<tr height="' .  $height . 'px" width="100%"><td style="' . $pBackground .  'height:' .  $height . 'px;width:100%;padding:0px;margin:0px;"></td></tr>';
              //echo '<tr style="height:' . $subHeight . 'px;"><td style="' . $noBorder . '"></td></tr>';
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
              $dispCaption=($showResource)?true:false;
            }
          } else {
            echo '<td class="reportTableData" style="'. $color . $noBorder . 'padding:0px;margin:0px;width:'.(($right_size*100)/$numDays).'%;">';
            //if($format=='week') {
              //echo '&nbsp;&nbsp;';
            //}
            if ($days[$i]>$pEnd and $dispCaption) {
            	echo '<div style="position: relative; top: 0px; height: 12px;">';
            	echo '<div style="position: absolute; top: -1px; left: 1px; height:12px;">';
            	echo '<div style="clip:rect(-10px,100px,100px,0px); text-align: left">' . $line['resource'] . '</div>';
            	echo '</div>';
            	echo '</div>';
            	$dispCaption=false;
            }
          }
          echo '</td>';
        }
        echo '</TR>';
      }
      echo "</table>";
    }
  }

  function formatDuration($duration, $hoursPerDay) {
    $hourDuration=$duration*$hoursPerDay;
  	$res = 'PT' . $hourDuration . 'H0M0S';
  	return $res;
  }
?>
