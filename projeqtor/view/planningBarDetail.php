<?php
include_once "../tool/projeqtor.php";

$class=null;
if (isset($_REQUEST['class'])) {
  $class=$_REQUEST['class'];
}
Security::checkValidClass($class);
if ($class=='Replan' or $class=='Construction' or $class=='Fixed') {
  $class='Project';
}
$id=null;
if (isset($_REQUEST['id'])) {
  $id=$_REQUEST['id'];
}
Security::checkValidId($id);
$scale='day';
if (isset($_REQUEST['scale'])) {
  $scale=$_REQUEST['scale'];
}
if ($scale!='day' and $scale!='week') {
  echo '<div style="background-color:#FFF0F0;padding:3px;border:1px solid #E0E0E0;">'.i18n('ganttDetailScaleError')."</div>";
  return;
}

$objectClassManual = RequestHandler::getValue('objectClassManual');
if($objectClassManual == 'ResourcePlanning' ){
  $idAssignment = RequestHandler::getId('idAssignment');
}

$dates=array();
$work=array();
$maxCapacity=array();
$minCapacity=array();
$maxSurbooking=array();
$minSurbooking=array();
$ressAll=array();
$start=null;
$end=null;
$resourceList = array(0=>0);

if ($class=='Resource' or $class=='ResourceTeam') {
  echo '<div style="background-color:#FFF0F0;padding:3px;border:1px solid #E0E0E0;">'.i18n('noDataToDisplay')."</div>";
  return;
}
$crit=array('refType'=>$class,'refId'=>$id);

if (! class_exists($class.'PlanningElement')) {
  echo "";
  return;
}
$pe=SqlElement::getSingleSqlElementFromCriteria($class.'PlanningElement', $crit);
if ($pe->assignedWork==0 and $pe->leftWork==0 and $pe->realWork==0) {
  echo '<div style="background-color:#FFF0F0;padding:3px;border:1px solid #E0E0E0;">'.i18n('noDataToDisplay')."</div>";
  return;
}

if($objectClassManual == 'ResourcePlanning' ){
  $crit=array('refType'=>$class,'refId'=>$id,'idAssignment'=>$idAssignment);
}
$wk=new Work();
$wkLst=$wk->getSqlElementsFromCriteria($crit);
foreach($wkLst as $wk) {
  $dates[$wk->workDate]=$wk->workDate;
  if (!$start or $start>$wk->workDate) $start=$wk->workDate;
  if (!$end or $end<$wk->workDate) $end=$wk->workDate;
  $keyAss=$wk->idAssignment.'#'.$wk->idResource;
  $resourceList[$keyAss]=$wk->idResource;
  if (! isset($work[$keyAss])) $work[$keyAss]=array();
  if (! isset($work[$keyAss]['resource'])) {
    $ress=new ResourceAll($wk->idResource);
    $ressAll[$wk->idResource]=$ress;
    $work[$keyAss]['capacity']=($ress->capacity>1)?$ress->capacity:'1';
    $work[$keyAss]['resource']=$ress->name;
    $work[$keyAss]['idResource']=$ress->id;
    if ($ress->isResourceTeam) {
      $ass=new Assignment($wk->idAssignment);
      $work[$keyAss]['capacity']=($ass->capacity>1)?$ass->capacity:'1';
    }
    if ($work[$keyAss]['capacity']>1) {
      $work[$keyAss]['resource'].=' ('.i18n('max').' = '.htmlDisplayNumericWithoutTrailingZeros($work[$keyAss]['capacity']).' '.i18n('days').')';
    }
  }
  $work[$keyAss][$wk->workDate]=array('work'=>$wk->work,'type'=>'real');
  $maxCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
  $minCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
  $maxSurbooking[$wk->idResource]=0;
  $minSurbooking[$wk->idResource]=0;
}


$wk=new PlannedWork();
$wkLst=$wk->getSqlElementsFromCriteria($crit);
foreach($wkLst as $wk) {
  $dates[$wk->workDate]=$wk->workDate;
  if (!$start or $start>$wk->workDate) $start=$wk->workDate;
  if (!$end or $end<$wk->workDate) $end=$wk->workDate;
  $keyAss=$wk->idAssignment.'#'.$wk->idResource;
  $resourceList[$keyAss]=$wk->idResource;
  if (! isset($work[$keyAss])) $work[$keyAss]=array();
  if (! isset($work[$keyAss]['resource'])) {
    $ress=new ResourceAll($wk->idResource);
    $ressAll[$wk->idResource]=$ress;
    $work[$keyAss]['capacity']=($ress->capacity>1)?$ress->capacity:'1';
    $work[$keyAss]['resource']=$ress->name;
    $work[$keyAss]['idResource']=$ress->id;
    if ($ress->isResourceTeam) {
      $ass=new Assignment($wk->idAssignment);
      $work[$keyAss]['capacity']=($ass->capacity>1)?$ass->capacity:'1';
    }
    if ($work[$keyAss]['capacity']>1) {
      $work[$keyAss]['resource'].=' ('.i18n('max').' = '.htmlDisplayNumericWithoutTrailingZeros($work[$keyAss]['capacity']).' '.i18n('days').')';
    }
  }
  if (! isset($work[$keyAss][$wk->workDate]) ) {
    $work[$keyAss][$wk->workDate]=array('work'=>$wk->work,'type'=>'planned','surbooked'=>$wk->surbooked,'surbookedWork'=>$wk->surbookedWork);
  }
  $maxCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
  $minCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
  $maxSurbooking[$wk->idResource]=0;
  $minSurbooking[$wk->idResource]=0;
}


$where="idProject in ".Project::getAdminitrativeProjectList();
$act = new Activity();
$actList = $act->getSqlElementsFromCriteria(null,null,$where);
$actListId=array(0=>0);
foreach ($actList as $activity){
	$actListId[$activity->id]=$activity->id;
}
$wk=new Work();
$where = "refType='Activity' and refId in (".implode(',', $actListId).") and idResource in (".implode(',', $resourceList).")";
$actWorkList = $wk->getSqlElementsFromCriteria(null,null,$where);
$resourceList = array_flip($resourceList);

foreach($actWorkList as $wk) {
  if ($start>$wk->workDate) continue;
  if ($end<$wk->workDate) continue;
	$dates[$wk->workDate]=$wk->workDate;
	$keyAss=$resourceList[$wk->idResource];
	if (! isset($work[$keyAss])) $work[$keyAss]=array();
	if (! isset($work[$keyAss]['resource'])) {
		$ress=new ResourceAll($wk->idResource);
		$ressAll[$wk->idResource]=$ress;
		$work[$keyAss]['capacity']=($ress->capacity>1)?$ress->capacity:'1';
		$work[$keyAss]['resource']=$ress->name;
		$work[$keyAss]['idResource']=$ress->id;
		if ($ress->isResourceTeam) {
			$ass=new Assignment($wk->idAssignment);
			$work[$keyAss]['capacity']=($ass->capacity>1)?$ass->capacity:'1';
		}
		if ($work[$keyAss]['capacity']>1) {
			$work[$keyAss]['resource'].=' ('.i18n('max').' = '.htmlDisplayNumericWithoutTrailingZeros($work[$keyAss]['capacity']).' '.i18n('days').')';
		}
	}
	

	if($wk->work ==($ress->capacity/2) and isset($work[$keyAss][$wk->workDate])) {
	   $capacity=$work[$keyAss][$wk->workDate]['work']+$wk->work;
    	$work[$keyAss][$wk->workDate]=array('work'=>$capacity,'type'=>'planned_administrative');
    	$maxCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
    	$minCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
    	$maxSurbooking[$wk->idResource]=0;
    	$minSurbooking[$wk->idResource]=0;
	}else{
	  $work[$keyAss][$wk->workDate]=array('work'=>$wk->work,'type'=>'administrative');
	  $maxCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
	  $minCapacity[$wk->idResource]=$work[$keyAss]['capacity'];
	  $maxSurbooking[$wk->idResource]=0;
	  $minSurbooking[$wk->idResource]=0;
	}

}

if ($pe->idPlanningMode=='22') { // RECW
  $start=$pe->plannedStartDate;
  $end=$pe->plannedEndDate;
}
if (!$start or !$end) {
  if ($pe->elementary) echo '<div style="background-color:#FFF0F0;padding:3px;border:1px solid #E0E0E0;">'.i18n('noDataToDisplay').'<br/>'.i18n('planningCalculationRequired')."</div>";
  else echo '<div style="background-color:#FFF0F0;padding:3px;border:1px solid #E0E0E0;">'.i18n('noDataToDisplay')."</div>";
	return;
}

if($objectClassManual != 'ResourcePlanning' ){
  if ($pe->plannedStartDate && $pe->plannedStartDate<$start){
    $start=$pe->plannedStartDate;
  }
}
$variableCapacity=array();
$surbooking=array();
$dt=$start;
while ($dt<=$end) {
  if (!isset($dates[$dt])) {
    $dates[$dt]=$dt;
  }
  foreach ($ressAll as $ress) {
    if (!isset($variableCapacity[$ress->id])) $variableCapacity[$ress->id]=array();
    if (!isset($surbooking[$ress->id])) $surbooking[$ress->id]=array();
    $capa=$ress->getCapacityPeriod($dt);
    $surbook=$ress->getSurbookingCapacity($dt,true);
    if (! $ress->isResourceTeam) {
      if ($capa!=$ress->capacity) {
        $variableCapacity[$ress->id][$dt]=$capa;
      }
      if ($capa>$maxCapacity[$ress->id]) $maxCapacity[$ress->id]=$capa;
      if ($capa<$minCapacity[$ress->id]) $minCapacity[$ress->id]=$capa;
    }
    if (!isset($maxSurbooking[$ress->id])) $maxSurbooking[$ress->id]=0;
    if (!isset($minSurbooking[$ress->id])) $minSurbooking[$ress->id]=0;
    if ($surbook>$maxSurbooking[$ress->id]) $maxSurbooking[$ress->id]=$surbook;
    if ($surbook<$minSurbooking[$ress->id]) $minSurbooking[$ress->id]=$surbook;
  }
  $dt=addDaysToDate($dt, 1);
}
ksort($dates);

$width=20;
echo '<table id="planningBarDetailTable" style="height:'.(count($work)*22).'px;background-color:#FFFFFF;border-collapse: collapse;marin:0;padding:0;width:100%">';
$heightNormal=20;
$heightCapacity=20;
foreach ($work as $resWork) {
  $resObj=$ressAll[$resWork['idResource']];
  echo '<tr style="height:20px;border:1px solid #505050;">';
  $overCapa=null;
  $underCapa=null;
  $surbooked=null;
  foreach ($dates as $dt) {
    $color="#ffffff";
    $tdColor="";
    $height=20; $w=0;    
    $heightSurbooked=0;
    $capacityTop=$maxCapacity[$resWork['idResource']]; //$resWork['capacity'];
    if (!isset($variableCapacity[$resWork['idResource']][$dt])) {
      $heightNormal=20;
      $heightCapacity=20;
    } else {
      $tmp=$ressAll[$resWork['idResource']];
      if ($variableCapacity[$resWork['idResource']][$dt]>$tmp->capacity) {
        if (!$overCapa or $variableCapacity[$resWork['idResource']][$dt]>$overCapa) {
          $overCapa=$variableCapacity[$resWork['idResource']][$dt];
        }
      } else {
        if (!$underCapa or $variableCapacity[$resWork['idResource']][$dt]<$underCapa) {
          $underCapa=$variableCapacity[$resWork['idResource']][$dt];
        }
      }
      $heightNormal=round(20*$resWork['capacity']/$capacityTop,0);
      $heightCapacity=round(20*$variableCapacity[$resWork['idResource']][$dt]/$capacityTop,0);
    }
    if ($capacityTop==0) $capacityTop=1;
    if (isset($resWork[$dt])) {
      $w=$resWork[$dt]['work'];       
      if (!$pe->validatedEndDate or $dt<=$pe->validatedEndDate) {
        $color=($resWork[$dt]['type']=='real')?"#507050":"#50BB50";  
      } else {
        $color=($resWork[$dt]['type']=='real')?"#705050":"#BB5050";
      }
      if($resWork[$dt]['type']=='administrative'){
      	$color="#3d668f";
      }
      if (isset($resWork[$dt]['surbooked']) and $resWork[$dt]['surbooked']==1) {
        $sb=$resWork[$dt]['surbookedWork'];
        $height=round(($w-$sb)*20/$capacityTop,0);
        $heightSurbooked=round($sb*20/$capacityTop,0);
      } else {
        $height=round($w*20/$capacityTop,0);
      }
    }
    if(isOffDay($dt, SqlList::getFieldFromId('ResourceAll', $resWork['idResource'], 'idCalendarDefinition'))){
      $color="#dddddd";
      $tdColor="background-color:#dddddd;";
    }
    echo '<td style="padding:0;width:'.$width.'px;border-right:1px solid #eeeeee;position:relative;'.$tdColor.'">';
    if(isset($resWork[$dt]) and $resWork[$dt]['type']=='planned_administrative'){
      $height=$height/2;
      echo '<div style="display:block;background-color:#3d668f;position:absolute;top:0px;left:0px;width:100%;height:'.$height.'px;"></div>';
      echo '<div style="display:block;background-color:'.$color.';position:absolute;bottom:0px;left:0px;width:100%;height:'.$height.'px;"></div>';
    }else{
      echo '<div style="border-top:1px solid #555555;display:block;background-color:'.$color.';position:absolute;bottom:0px;left:0px;width:100%;height:'.$height.'px;"></div>';
    }
    if ($heightSurbooked>0) echo '<div style="display:block;background-color:#f4bf42;position:absolute;bottom:'.$height.'px;left:0px;width:100%;height:'.$heightSurbooked.'px;"></div>';
    if ($maxCapacity[$resWork['idResource']]!=$resWork['capacity'] or $minCapacity[$resWork['idResource']]!=$resWork['capacity']) {
      echo '<div style="display:block;background-color:transparent;position:absolute;bottom:0px;left:0px;width:100%;border-top:1px solid grey;height:'.$heightNormal.'px;"></div>';
    }
    if ($heightNormal!=$heightCapacity and isset($variableCapacity[$resWork['idResource']][$dt])) {
      echo '<div style="display:block;background-color:transparent;position:absolute;bottom:0px;left:0px;width:100%;border-top:1px solid red;height:'.$heightCapacity.'px;"></div>';
    }
    echo '</td>';
  }
  echo '<td style="border-left:1px solid #505050;"><div style="width:200px; max-width:200px;overflow:hidden; text-align:left;max-height:20px;">&nbsp;';
  if ($overCapa) echo '<div style="float:right;padding-right:3px">&nbsp;<img style="width:10px" src="../view/img/arrowUp.png" />&nbsp;'.htmlDisplayNumericWithoutTrailingZeros($overCapa).'</div>';
  if ($underCapa) echo '<div style="float:right">&nbsp;<img style="width:10px" src="../view/img/arrowDown.png" />&nbsp;'.htmlDisplayNumericWithoutTrailingZeros($underCapa).'</div>';
  if ($maxSurbooking[$resWork['idResource']]!=0 or $minSurbooking[$resWork['idResource']]!=0) { 
    if ($maxSurbooking[$resWork['idResource']]) echo '<div style="float:right;padding-right:3px;">&nbsp;<span style="color:#f4bf42;font-weight:bold">+</span>&nbsp;'.htmlDisplayNumericWithoutTrailingZeros($maxSurbooking[$resWork['idResource']]).'</div>';
    else if ($minSurbooking[$resWork['idResource']]) echo '<div style="float:right;padding-right:3px;">&nbsp;<span style="color:#f4bf42;font-weight:bold">-</span>&nbsp;'.htmlDisplayNumericWithoutTrailingZeros((-1)*$minSurbooking[$resWork['idResource']]).'</div>';
  }
  echo $resWork['resource'].'&nbsp;</div></td>';
  echo '</tr>';
}
echo '</table>';