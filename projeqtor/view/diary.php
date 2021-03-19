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
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/view/diary.php');   

  if (! isset($destinationHeight)) {
    $destinationHeight=$_REQUEST['destinationHeight'];
  }
  
  $cpt=0;
  $arrayActivities=array(); // Array of activities to display
  $idRessource=getSessionUser()->id;
  $showDone=false;
  $showIdle=false;
  $selectedTypes=Parameter::getUserParameter('diarySelectedItems');
  if (! isset($period)) {
  	$period=htmlentities($_REQUEST['diaryPeriod']);
    $year=htmlentities($_REQUEST['diaryYear']);
    $month=htmlentities($_REQUEST['diaryMonth']);
    $week=htmlentities($_REQUEST['diaryWeek']);
    $day=htmlentities($_REQUEST['diaryDay']);
    Parameter::storeUserParameter("diaryPeriod",$period);
    $idRessource=$_REQUEST['diaryResource'];
    $selectedTypes=$_REQUEST['diarySelectItems'];
    $showIdle=(isset($_REQUEST['showIdle']))?true:false;
    $showDone=(isset($_REQUEST['showDone']))?true:false;
  }
  
  if ($selectedTypes=='' or $selectedTypes=='none') $selectedTypes='All';
  
  if(sessionValueExists('diaryResource')) {
    $idRessource = getSessionValue('diaryResource');
  }
  if(sessionValueExists('showIdleDiary')) {
    if(getSessionValue('showIdleDiary')=='on'){
      $showIdle = true;
    }else{
      $showIdle = false;
    }
  }
  if(sessionValueExists('showDoneDiary')) {
    if(getSessionValue('showDoneDiary')=='on'){
      $showDone = true;
    }else{
      $showDone = false;
    }
  }
  if(sessionValueExists('dateSelectorDiary')) {
    $day = getSessionValue('dateSelectorDiary');
    $year= date('Y',strtotime($day));
    $month= date('m',strtotime($day));
    $week= date('W',strtotime($day));
    if ($period=='week') {
      if ($week>50 and $month==1) {
        $year--;
      } else if ($week==1 and $month==12) {
        $year++;
      }
    }
  }
  
  $ress=new Resource($idRessource);
  $calendar=$ress->idCalendarDefinition;
  $weekDaysCaption=array(
  		1=>i18n("Monday"),
  		2=>i18n("Tuesday"),
  		3=>i18n("Wednesday"),
  		4=>i18n("Thursday"),
  		5=>i18n("Friday"),
  		6=>i18n("Saturday"),
  		7=>i18n("Sunday"),
  );
  $projectColorArray=array();
  $projectNameArray=array();
  $totalHeight=$destinationHeight;
  $trHeight=$totalHeight;
  if ($period=="month") {
  	$firstDay=$year.'-'.$month.'-01';
  	$lastDayOfMonth=date('t',strtotime($year.'-'.$month.'-01'));
    $week=weekNumber($firstDay);
  	$lastWeek=weekNumber($year.'-'.$month.'-'.$lastDayOfMonth);
  	//$lastWeek=weekNumber('2017-04-30');
  	if ($lastWeek==1) {
  	  while ($lastWeek==1) {
    	  $lastDayOfMonth--;
    	  $lastWeek=weekNumber($year.'-'.$month.'-'.$lastDayOfMonth);
  	  }
  	  $lastWeek+=1;
  	}
	  if ($lastWeek>$week) {
		  $trHeight=floor(($totalHeight-20)/($lastWeek-$week+1));
	  } else {
		  $trHeight=floor(($totalHeight-20)/($lastWeek+1));
	  }
  } else if ($period=="week") {
    $trHeight=$totalHeight-10;
  } else if ($period=="day") {
    $trHeight=$totalHeight;
  }
  if ($period=="month") {
    if ($month=='01' and $week>50) {
      $currentDay=date('Y-m-d',firstDayofWeek($week,$year-1));
    } else {
  	  $currentDay=date('Y-m-d',firstDayofWeek($week,$year));
    }
  	$lastDayOfMonth=date('t',strtotime($year.'-'.$month.'-01'));
	  //$weekOfLastDayOfMonth=date('W',strtotime($year.'-'.$month.'-'.$lastDayOfMonth));
  	$weekOfLastDayOfMonth=$lastWeek;
  	$firstDayOfLastWeek=date('Y-m-d',firstDayofWeek($weekOfLastDayOfMonth, $year ));
  	$endDay=addDaysToDate($firstDayOfLastWeek, 6);
  	$inScopeDay=false;	
  } else if ($period=="week") {
  	$currentDay=date('Y-m-d',firstDayofWeek($week,$year));
  	$endDay=addDaysToDate($currentDay, 6);
  	$inScopeDay=true;
  } else if ($period=="day") {
  	$currentDay=$day;
  	$endDay=$currentDay;
  	$inScopeDay=true;
  }

  echo '<TABLE style="width:100%;height:'.($totalHeight).'px">';
  
  if ($period!='day') {
    echo '<tr height="10px"><td></td>';
    for ($i=1; $i<=7;$i++) {
  	  echo '<td class="section" style="width: 14%;">'.$weekDaysCaption[$i].'</td>';
    }
  } else {
  	echo '<tr height="0px"><td></td>';
  	$trHeight=$totalHeight;
  }
  $arrayActivities=getAllActivities($currentDay, $endDay, $idRessource, $selectedTypes, $showDone,$showIdle);
  drawDiaryLineHeader($currentDay, $trHeight,$period); 
  while ($currentDay<=$endDay) {
  	if ($period=="month") {
  		if (substr($currentDay,5,2)==$month) {
  			$inScopeDay=true;
  		} else {
  			$inScopeDay=false;
  		}
  	}
  	echo '<td style="width: '.(($period=='day')?'100':'14').'%; border: 1px solid #AAAAAA;background-color:'.(($inScopeDay)?'white':'transparent').'">';
  	drawDay($currentDay,$idRessource,$inScopeDay,$period,$calendar); 
  	$currentDay=addDaysToDate($currentDay, 1);
  	if ($currentDay<=$endDay and date('N', strtotime($currentDay))==1) {
      drawDiaryLineHeader($currentDay, $trHeight,$period);
  	}
  }
  echo '</tr></TABLE>';
  
function drawDay($date,$ress,$inScopeDay,$period,$calendar=1) {
	global $cpt, $trHeight;
	$dayHeight=$trHeight;
	if ($period=='month') {
	  $dayHeight-=15;
	}
	echo '<table style="width:100%; height: 100%;'.(($date==date('Y-m-d'))?'border:0px solid #555555;':'').'">';
	if ($period!='day') {
		echo '<tr style="height:10px">';
		echo '<td class="report'.(($date==date('Y-m-d'))?'Table':'').'Header" style="padding:0;cursor: pointer;'.((!$inScopeDay)?'color:#AAAAAA':'').'"';
		echo ' onClick="diaryDay(\''.$date.'\');" >';
		//echo $date.'/';
		echo substr($date,8,2);
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr >';
	$bgColor="#FFFFFF";
	if ($date==date('Y-m-d')) { 
		$bgColor="#ffffaa"; 
	} else if (isOffDay($date,$calendar)) {
		$bgColor="#dfdfdf";
	}
	
	echo '<td style="vertical-align:top;background-color:'.$bgColor.';">';
	echo '<div style="overflow-y: auto; overflow-x:hidden; height:'.$dayHeight.'px;">';
	echo '<table style="width:'.(($period=='day')?'99%':'97%').';background-color:white;">';
	$lst=getActivity($date);
	foreach ($lst as $item) {
	    $plannedColor='';
		$cpt++;
		$hint=i18n($item['class']).' #'.$item['id']."\n"
				.$item['name']."\n"
				.i18n('colIdProject').": ".$item['projectName'];
		$hintHtml=i18n($item['class']).' #'.$item['id']."<br/>"
				.'<b>'.$item['name']."</b><br/>"
				.i18n('colIdProject').": <i>".$item['projectName'].'</i><br/>';
		if ($item['date']) { $hintHtml.=i18n('colDate').": <i>".$item['date']."</i>"; }
		if ($item['work'] and $item['real']) { $hintHtml.=i18n('colRealWork').": ".Work::displayWorkWithUnit($item['work'])."<br/>"; $plannedColor='background-color:#EEEEEE;';}
		if ($item['work'] and ! $item['real']) { $hintHtml.=i18n('planned').": <i>".Work::displayWorkWithUnit($item['work'])."</i><br/>"; $plannedColor='background-color:#FFFFFF;';}
		if ($item['isResourceTeam']) { $hintHtml.=i18n('ResourceTeam').": <i>".SqlList::getNameFromId('ResourceAll', $item['idResourceTeam'], false)."</i>"; }
		echo '<tr>';
		echo '<td style="padding: 3px 3px 3px 3px;margin-right:20px;width:100%;position:relative;max-width:250px;'.$plannedColor.'">';
		echo '<div id="item_'.$cpt.'" style="border:1px solid: #EEEEEE; box-shadow: 2px 2px 4px #AAAAAA; width: 100%;border-style:solid;border-width:0px 0px 0px 5px;border-color:'.$item['color'].'">';
		echo '<table style="width:100%"><tr>';		
		echo '<td><a style="position:absolute;left:15px;width:18px;top:3px;height:17px;z-index:20;">'.formatIcon($item['class'], 16,null,false).'</a>';
		if($item['isResourceTeam']){
		  echo '<a style="position:absolute;left:15px;width:18px;height:17px;z-index:20;">'.formatIcon('Team', 16,null,false).'</a>';
		}
		echo '</td>';
		echo '<td style="color:#555555">';
		//Modification ici , typename ne marche pas...
		echo '<div style="cursor:pointer;height:100%;word-wrap:break-word;margin-left:27px;" onClick="gotoElement(\''.$item['class'].'\', '.$item['id'].', false);" >';
		echo '<div style="float:right;width:22px;height:22px;position:relative;margin-right:10px;" id="userThumb'.$item['id'].'">'.formatUserThumb($item['responsibleId'],$item['responsibleName'], "", 22, 'left', false).'</div>';
		if ($item['real']) {
		  echo $item['name'];
		} else {
			echo '<i>'.$item['name'].'</i>';
		}
		if ($period=='week' or $period=='day') {
		  echo '<table style="vertical-align:top;display:block;">';
		  if ($item['projectName']) echo '<tr><td style="text-align:right;font-weight:bold;vertical-align:top;">'.i18n('colIdProject').'&nbsp;:&nbsp;</td><td>'.$item['projectName'].'</td></tr>';
		  if ($item['typeName']) echo '<tr><td style="text-align:right;font-weight:bold;vertical-align:top;">'.i18n('colType').'&nbsp;:&nbsp;</td><td>'.$item['typeName'].'</td></tr>';
		  if ($item['priorityName'])echo '<tr><td style="text-align:right;font-weight:bold;vertical-align:top;">'.i18n('colIdPriority').'&nbsp;:&nbsp;</td><td>'.$item['priorityName'].'</td></tr>';
		  echo '</table>';
		}
		if ($period=='day') {
		  echo '<div style="padding:5px;width:98%;border:1px solid #A0A0A0;margin-top:5px;margin-bottom:5px;">'.$item['description'].'</div>';
		  if ($item['date']) { echo i18n('colDate').": <i>".$item['date']."</i>"; }
		  if ($item['work'] and $item['real']) { echo i18n('colRealWork').": ".Work::displayWorkWithUnit($item['work']).""; }
		  if ($item['work'] and ! $item['real']) { echo i18n('planned').": <i>".Work::displayWorkWithUnit($item['work'])."</i>"; }
		}
		echo '<div style="width:100%;float:left;position:relative;left:-18px;padding-top:2px">';
		echo '   <div style="float:right;min-width:22px;height:22px;position:relative;margin-top:5px;margin-right:-13px;">#'.$item['id'].'</div>';
		$marginRight="";if(isNewGui())$marginRight='margin-right:15px;';
		echo '   <div style="'.$marginRight.' float:left;width:22px;height:22px;position:relative;top:1px;">'.formatColorThumb("idPriority",$item['priorityId'], 22, 'left', i18n('colIdPriority').' : '.$item['priorityName']).'</div>';
		echo '   <div style="width:60%;position:relative;margin-left:20px;min-height:23px;height:28px;overflow:hidden;top:0px" class="colorNameData">'.colorNameFormatter($item['statusName'].'#split#'.SqlList::getFieldFromId('Status', $item['statusId'], 'color')).'</div>';
		//Ticket #458 F.KARA
    if ($item['meetingStartTime'])    echo '<div style="width:35%;position:relative;margin-left:1px;height:15px;top: 0px"><b> ' . $item['meetingStartTime'] . '</b>';
    else echo '<div style="width:35%;position:relative;margin-left:1px;height:5px;top: 0px">';
		echo '</div>';
		echo '</div>';
		echo '</td></tr></table>';
		echo '</div>';
		// To display a tooltip in replacement of Hint
		if ($period!='day') {
		  echo '<div dojoType="dijit.Tooltip" connectId="item_'.$cpt.'" position="above">';
		  echo $hintHtml;
		  echo '</div>';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';	
	echo '</div>';
	echo '</td>';		
	echo '</tr>';
	echo '</table>';
}

function getActivity($date) {
	global $arrayActivities;
	if (array_key_exists($date, $arrayActivities)) {
		return $arrayActivities[$date];
	} else {
		return array();
	}
}

function getAllActivities($startDate, $endDate, $ress, $selectedTypes, $showDone=false, $showIdle=false) {
	global $projectColorArray, $projectNameArray, $allActi;
	$result=array();
	$typesList=explode(',', $selectedTypes);
  foreach ($typesList as $typeFilter) {
    if ($typeFilter=='All') $arrObj=array(new Action(), new Ticket(), new MilestonePlanningElement(), new MeetingPlanningElement());
    else if ($typeFilter=="Meeting") $arrObj=array(new MeetingPlanningElement());
    else $arrObj=array(new $typeFilter());
    if (isset($_REQUEST['countStatus'])) {
      $statusWhere="IN (";
      $listStatusFilter=array();
      $countStatus=$_REQUEST['countStatus'];
      for ($i=1; $i<=$countStatus; $i++) {
        if (array_key_exists("objectStatus$i", $_REQUEST) and trim($_REQUEST["objectStatus$i"])!='') {
          $statusWhere.=((strlen($statusWhere)==4)?"":", ").$_REQUEST["objectStatus$i"];
          $listStatusFilter[]=$_REQUEST["objectStatus$i"];
        }
      }
      $statusWhere.=")";
    }
    foreach ($arrObj as $obj) {
      if (get_class($obj)=='MeetingPlanningElement') {
        $ass=new Assignment();
        $assTable=$ass->getDatabaseTableName();
        $meet=new Meeting();
        $meetTable=$meet->getDatabaseTableName();
        $mpeTable=$obj->getDatabaseTableName();
        $critWhere=" ( exists (select 'x' from $assTable ass where ass.refType='Meeting' and ass.refId = $mpeTable.refId and ass.idResource=".Sql::fmtId($ress);
        $critWhere.=((isset($countStatus))?" AND  exists ( select 'x' from $meetTable meet where id = ass.refId AND meet.idStatus $statusWhere)":"").")";
        $critWhere.="  or exists (select 'x' from $meetTable meet where id=$mpeTable.refId and meet.idResource=".Sql::fmtId($ress);
        $critWhere.=((isset($countStatus))?" AND meet.idStatus $statusWhere":"").") )";
      } else if (get_class($obj)=='MilestonePlanningElement') {
        if (!isset($countStatus)) $critWhere="1=1";
        else {
          $mlst=new Milestone();
          $mlstTable=$mlst->getDatabaseTableName();
          $mlstpeTable=$obj->getDatabaseTableName();
          $critWhere="exists (select 'x' from $mlstTable mlst where id=$mlstpeTable.refId AND mlst.idStatus $statusWhere)";
        }
      } else {
        $critWhere="idResource=".Sql::fmtId($ress);
        if (isset($countStatus)) $critWhere.=" AND idStatus $statusWhere";
      }
      if (!$showDone and !$showIdle) {
        $critWhere.=" and done=0 ";
      }
      if (!$showIdle) {
        $critWhere.=" and idle=0 ";
      }
      if (property_exists($obj, 'actualDueDate') and property_exists($obj, 'initialDueDate')) {
        $critWhere.=" and ( "." (actualDueDate>='$startDate' and actualDueDate<='$endDate') "." or ( actualDueDate is null and (initialDueDate>='$startDate' and initialDueDate<='$endDate') )"." )";
      } else if (property_exists($obj, 'actualDueDateTime') and property_exists($obj, 'initialDueDateTime')) {
        $critWhere.=" and ( "." (actualDueDateTime>='$startDate 00:00:00' and actualDueDateTime<='$endDate 23:59:59') "." or ( actualDueDateTime is null and (initialDueDateTime>='$startDate 00:00:00' and initialDueDateTime<='$endDate 23:59:59') )"." )";
      } else if (property_exists($obj, 'validatedEndDate')) {
        $refType=str_replace('PlanningElement', '', get_class($obj));
        $critWhere.=" and refType='$refType' and validatedEndDate>='$startDate' and validatedEndDate<='$endDate' ";
        $critWhere.=" and idProject in ".transformListIntoInClause(getSessionUser()->getVisibleProjects(true));
        if ($refType=='Milestone' and $ress!=getSessionUser()->id) {
          $lstMile=SqlList::getListWithCrit('Milestone', array('idResource'=>$ress));
          $critWhere.=" and refId in ".transformListIntoInClause($lstMile);
        }
      } else {
        $critWhere.=" and 1=0";
      }
      $lst=$obj->getSqlElementsFromCriteria(null, false, $critWhere);

      foreach ($lst as $o) {
        if (get_class($o)=='MilestonePlanningElement' or get_class($o)=='MeetingPlanningElement') {
          $refType=$o->refType;
          $item=new $refType($o->refId);
        } else {
          $item=$o;
        }
        if (array_key_exists($o->idProject, $projectColorArray)) {
          $color=$projectColorArray[$o->idProject];
          $projectId=$o->idProject;
          $projectName=$projectNameArray[$o->idProject];
        } else {
          $pro=new Project($item->idProject);
          $color=$pro->getColor();
          $projectId=$pro->id;
          $projectName=$pro->name;
          $projectColorArray[$o->idProject]=$color;
          $projectNameArray[$o->idProject]=$projectName;
        }
        $typeName=null;
        $typeId=null;
        $type='id'.get_class($item).'Type';
        if (property_exists($item, $type)) {
          $typeId=$item->$type;
          $typeName=SqlList::getNameFromId('Type', $item->$type);
        }
        $priorityName=null;
        $priorityId=null;
        if (property_exists($item, 'idPriority')) {
          $priorityId=$item->idPriority;
          $priorityName=SqlList::getNameFromId('Priority', $item->idPriority);
        }
        $responsibleName=null;
        $responsibleId=null;
        if (property_exists($item, 'idResource')) {
          $responsibleId=$item->idResource;
          $responsibleName=SqlList::getNameFromId('Affectable', $item->idResource);
        }
        $statusName=null;
        $statusId=null;
        if (property_exists($item, 'idStatus')) {
          $statusId=$item->idStatus;
          $statusName=SqlList::getNameFromId('Status', $item->idStatus);
        }
        $description=null;
        if (property_exists($item, 'description')) {
          $description=$item->description;
        }
        //Ticket #438 F.KARA
        $meetingStartTime=null;
        if(property_exists($item,'meetingStartTime')) {
            $meetingStartTime=substr($item->meetingStartTime,0,5);
        }
        $date=null;
        $dateField="";
        $name="";
        $id=$o->id;
        $class=get_class($o);
        if (property_exists($obj, 'actualDueDate') and property_exists($obj, 'initialDueDate')) {
          if ($o->actualDueDate) {
            $date=$o->actualDueDate;
            $dateField=i18n('colActualDueDate');
          } else {
            $date=$o->initialDueDate;
            $dateField=i18n('colInitialDueDate');
          }
          $name=$o->name;
        } else if (property_exists($obj, 'actualDueDateTime') and property_exists($obj, 'initialDueDateTime')) {
          if ($o->actualDueDateTime) {
            $date=substr($o->actualDueDateTime, 0, 10);
            $dateField=i18n('colActualDueDate');
          } else {
            $date=substr($o->initialDueDateTime, 0, 10);
            $dateField=i18n('colInitialDueDate');
          }
          $name=$o->name;
        } else if (property_exists($obj, 'validatedEndDate')) {
          $name=$o->refName;
          $id=$o->refId;
          $class=$o->refType;
          $date=$o->validatedEndDate;
          $dateField=i18n('colValidatedEndDate');
        }
        if ($date) {
          if (!array_key_exists($date, $result)) {
            $result[$date]=array();
          }
          
          $isResourceTeam=false;
          $idResourceTeam=null;
          $ass = new Assignment();
          $assList = $ass->getSqlElementsFromCriteria(array('refType'=>get_class($item), 'refId'=>$item->id, 'isResourceTeam'=>1));
          foreach ($assList as $asgn){
          	$resTeam = ResourceTeamAffectation::getSingleSqlElementFromCriteria('ResourceTeamAffectation', array('idResourceTeam'=>$asgn->idResource, 'idResource'=>$ress));
          	if($resTeam->id){
          		$idResourceTeam = $resTeam->idResourceTeam;
          		$isResourceTeam=true;
          	}
          }
        
          $result[$date]["$class#$id"]=array(
              'class'=>$class,
              'id'=>$id,
              'work'=>0,
              'real'=>false,
              'name'=>$name,
              'color'=>$color,
              'date'=>$dateField,
              'projectId'=>$projectId,
              'projectName'=>$projectName,
              'typeId'=>$typeId,
              'typeName'=>$typeName,
              'priorityId'=>$priorityId,
              'priorityName'=>$priorityName,
              'responsibleId'=>$responsibleId,
              'responsibleName'=>$responsibleName,
              'statusId'=>$statusId,
              'statusName'=>$statusName,
              'description'=>$description,
              'meetingStartTime'=> $meetingStartTime, //Ticket #438 F.KARA - Get the start time of a meeting
              'isResourceTeam'=> $isResourceTeam,
              'idResourceTeam'=> $idResourceTeam
          );
        }
      }
    }
	}
	// Planned Activities and real work
	$ressList = array(Sql::fmtId($ress));
	$resourceTeamAff = SqlList::getListWithCrit('ResourceTeamAffectation', array('idResource'=>$ress));
	foreach ($resourceTeamAff as $id){
	  $resTeamaff = new ResourceTeamAffectation($id);
	  array_push($ressList, Sql::fmtId($resTeamaff->idResourceTeam));
	}
	$ressList = '('.implode(',', $ressList).')';
	$critWhere="idResource in ".$ressList;
	$critWhere.=" and workDate>='$startDate' and workDate<='$endDate'";
	if ($selectedTypes != 'All') {
	  $selectedTypes = str_replace(",", "','", $selectedTypes);
	  $critWhere.=" AND refType IN ('$selectedTypes')";
	}
	$pw=new PlannedWork();
	$w=new Work();
	$pwList=$pw->getSqlElementsFromCriteria(null,false,$critWhere);
	$wList=$w->getSqlElementsFromCriteria(null,false,$critWhere);
	$workList=array_merge($pwList,$wList);
	//KEVIN
	foreach ($workList as $pw) {
	  if (!$pw->refType) continue;
	  $item=new $pw->refType($pw->refId);
	  if (($item->done and !$showDone and !$showIdle) or ($item->idle and !$showIdle) or  (isset($countStatus) and !in_array($item->idStatus, $listStatusFilter)))
	    continue;
		if ($pw->refType=='Meeting') {
		    if(isset($item->meetingStartTime)) {
                $display=htmlFormatTime($item->meetingStartTime);
            }
		} else if (get_class($pw)=='Work') {
				$display='['.Work::displayWorkWithUnit($pw->work).'] ';
		} else {
		  $display='<i>('.Work::displayWorkWithUnit($pw->work).')</i> ';
		}
		if (array_key_exists($item->idProject,$projectColorArray)) {
			$color=$projectColorArray[$item->idProject];
			$projectId=$item->idProject;
			$projectName=$projectNameArray[$item->idProject];
		} else {
			$pro=new Project($item->idProject);
			$color=$pro->getColor();
			$projectId=$item->idProject;
			$projectName=$pro->name;
			$projectColorArray[$item->idProject]=$color;
			$projectNameArray[$item->idProject]=$projectName;
		}
		$date=$pw->workDate;
		if (!array_key_exists($date, $result)) {
			$result[$date]=array();
		}
		$typeId=null;
		$typeName=null;
		$type='id'.get_class($item).'Type';
		if (property_exists($item,$type)) {
		  $typeId=$item->$type;
		  $typeName=SqlList::getNameFromId('Type', $item->$type);
		}
		$priorityId=null;
		$priorityName=null;
		if (property_exists($item,'idPriority')) {
		  $priorityId=$item->idPriority;
		  $priorityName=SqlList::getNameFromId('Priority', $item->idPriority);
		}
		$responsibleId=null;
		$responsibleName=null;
		if (property_exists($item,'idResource')) {
		  $responsibleId=$item->idResource;
		  $responsibleName=SqlList::getNameFromId('Affectable', $item->idResource);
		}
		$statusName=null;
		$statusId=null;
		if (property_exists($item,'idStatus')) {
		  $statusId=$item->idStatus;
		  $statusName=SqlList::getNameFromId('Status', $item->idStatus);
		}
		$description=null;
		if (property_exists($item,'description')) {
		  $description=$item->description;
		}
        //Ticket #438 F.KARA
		$meetingStartTime=null;
		if(property_exists($item,'meetingStartTime')) {
		    $meetingStartTime=substr($item->meetingStartTime,0,5);;
        }
        $isResourceTeam=false;
        $idResourceTeam=null;
        $ass = new Assignment();
        $assList = $ass->getSqlElementsFromCriteria(array('refType'=>get_class($item), 'refId'=>$item->id, 'isResourceTeam'=>1));
        foreach ($assList as $asgn){
          $resTeam = ResourceTeamAffectation::getSingleSqlElementFromCriteria('ResourceTeamAffectation', array('idResourceTeam'=>$asgn->idResource, 'idResource'=>$ress));
          if($resTeam->id){
            $idResourceTeam = $resTeam->idResourceTeam;
            $isResourceTeam=true;
          }
        }
		$result[$date][$pw->refType.'#'.$pw->refId]=array(
				'class'=>$pw->refType,
		    'id'=>$pw->refId,
				'work'=>$pw->work,
		    'real'=>((get_class($pw)=='Work')?true:false),
				'name'=>$item->name,
				'color'=>$color,
		    'date'=>"",
				'projectId'=>$projectId,
				'projectName'=>$projectName,
				'typeId'=>$typeId,
				'typeName'=>$typeName,
				'priorityId'=>$priorityId,
				'priorityName'=>$priorityName,
				'responsibleId'=>$responsibleId,
				'responsibleName'=>$responsibleName,
		    'statusId'=>$statusId,
		    'statusName'=>$statusName,
		    'description'=>$description,
            'meetingStartTime'=> $meetingStartTime,//Ticket #438 F.KARA - Get the start time of a meeting
		    'isResourceTeam'=> $isResourceTeam,
		    'idResourceTeam'=> $idResourceTeam 
		);
	}
	return $result;
}

function drawDiaryLineHeader($currentDay, $trHeight,$period) {
	echo '</tr>';
	echo '<tr height="'.$trHeight.'px"><td class="buttonDiary" ';
	$week=weekNumber($currentDay);
	$weekYear=substr($currentDay,0,4);
	if (intval($week)==1 and substr($currentDay,5,2)==12) $weekYear+=1;
	if ($period=="month") {
	  echo 'onClick="diaryWeek('.$week.','.$weekYear.');"';
	} else if ($period=="week") {
		echo 'onClick="diaryMonth('.substr($currentDay,5,2).','.substr($currentDay,0,4).');"';
	} else if ($period=="day") {
		echo 'onClick="diaryWeek('.$week.','.$weekYear.');"';
	}	
	echo '>';
	if ($period=='week') {
		$month=substr($currentDay,5,2);
		$monthArr=array(i18n("January"),i18n("February"),i18n("March"),
				i18n("April"), i18n("May"),i18n("June"),
				i18n("July"), i18n("August"), i18n("September"),
				i18n("October"),i18n("November"),i18n("December"));
		$dispMonth=(mb_strlen($monthArr[$month-1],'UTF-8')>4)?mb_substr($monthArr[$month-1],0,4,'UTF-8').'.':$monthArr[$month-1];
		echo '<div style="font-size:80%">'.$dispMonth.'</div>';
	} else {
	  echo '<div >'.weekNumber($currentDay).'</div>';
	}
	if ($period=="month") {
		echo '<img src="../view/css/images/right.png" /></td>';
	} else {
		echo '<img src="../view/css/images/left.png" /></td>';
	}
}
?>

