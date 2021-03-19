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

include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';
include("../external/pChart2/class/pData.class.php");
include("../external/pChart2/class/pDraw.class.php");
include("../external/pChart2/class/pImage.class.php");

$kpiColorFull=true; // decide how kpi color is displayed correspondng on threshold : false will display rounded badge, true will fill the cell 
$displayAsPct=true;

$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
}
$idOrganization="";
if (array_key_exists('idOrganization',$_REQUEST) and trim($_REQUEST['idOrganization'])!="") {
  $idOrganization=trim($_REQUEST['idOrganization']);
  $idOrganization = Security::checkValidId($idOrganization);
}
$idProjectType="";
if (array_key_exists('idProjectType',$_REQUEST) and trim($_REQUEST['idProjectType'])!="") {
  $idProjectType=trim($_REQUEST['idProjectType']);
  $idProjectType = Security::checkValidId($idProjectType);
}
$year="";
if (array_key_exists('yearSpinner',$_REQUEST)) {
  $year=trim($_REQUEST['yearSpinner']);
  $year = Security::checkValidYear($year);
}
$month="";
if (array_key_exists('monthSpinner',$_REQUEST)) {
  $month=trim($_REQUEST['monthSpinner']);
  $month = Security::checkValidMonth($month);
  if ($month and !$year) $year=date('Y');
}
$done=false;
if (array_key_exists('onlyFinished',$_REQUEST)) {
  $done=true;
}
$showThreshold=false;
if (array_key_exists('showThreshold',$_REQUEST)) {
  $showThreshold=true;
}
$scale='month';
if (array_key_exists('format',$_REQUEST)) {
  $scale=$_REQUEST['format'];
}
$scaleHist='week';
if (array_key_exists('formatHist',$_REQUEST)) {
  $scaleHist=$_REQUEST['formatHist'];
}

$class='Deliverable';
if (array_key_exists('class',$_REQUEST)) {
  $class=$_REQUEST['class'];
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($idProjectType!="") {
  $headerParameters.= i18n("colIdProjectType") . ' : ' . htmlEncode(SqlList::getNameFromId('ProjectType',$idProjectType)) . '<br/>';
}
if ($year!="") {
  $headerParameters.= i18n("year") . ' : ' . htmlFormatDate($year) . '<br/>';
}
if ($month!="") {
  $headerParameters.= i18n("month") . ' : ' . htmlFormatDate($month) . '<br/>';
}
if ($done) {
  $headerParameters.= i18n("colOnlyFinished").'<br/>';
}

include "header.php";

if ($month and $month<10) $month='0'.intval($month);

$scope=$_REQUEST['scope'];
if ($scope=='Project') {
  if (!$idProject) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('Project'))); 
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
} else if ($scope=='Organization') {
  $projectVisibility=securityGetAccessRight('menuProject','read');
  if ($projectVisibility=='ALL' and !$idProjectType and !$idOrganization) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('colIdOrganization').' / '.i18n('colIdProjectType'))); 
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
  if (!$year) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year')));
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
} else {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('error : scope is not defined'); 
  echo '</div>';
}
$idKpi=0;
$nbCols=0;
If ($class=='Deliverable') {
  $idKpi=4;
  $nbCols=9;
} else if ($class=='Incoming') {
  $idKpi=5;
  $nbCols=10;
} else {
  errorLog("Incorrect Class '$class' for kpiDeliverable report");
  if (!empty($cronnedScript)) goto end; else exit;
}

$kpi=new KpiDefinition($idKpi);

$user=getSessionUser();
$listProjects=array();
$visibleProjects=$user->getVisibleProjects(false);
if ($idProject) {
  $listProjects[$idProject]=new Project($idProject);
} else {
  $where='1=1';
  if ($idOrganization) {
    $where.=" and idOrganization='$idOrganization'";
  }
  if ($idProjectType) {
    $where.=" and idProjectType='$idProjectType'";
  }
  if (! $idOrganization and !$idProjectType) {
    $where.='and id in '.transformListIntoInClause($visibleProjects);
  } 
  if ($month) {
    $start=$year.'-'.$month.'-01';
    $end=$year.'-'.$month.'-'.date('t',strtotime($year.'-'.$month.'-01'));
  } else if ($year) {
    $start=$year.'-01-01';
    $end=$year.'-12-31';
  }
  $prj=new Project();
  $pjTable=$prj->getDatabaseTableName();
  $kh=new KpiHistory();
  $khTable=$kh->getDatabaseTableName();
  $kv=new KpiValue();
  $kvTable=$kv->getDatabaseTableName();
  $pe=new PlanningElement();
  $peTable=$pe->getDatabaseTableName();
  if (isset($start) and isset($end)) {
    $where.=" and ( ";
    $where.="    exists (select 'x' from $khTable kh where kh.idKpiDefinition=$kpi->id and kh.refType='Project' and kh.refId=$pjTable.id and kh.kpiDate>='$start' and kh.kpiDate<='$end' ) ";
    $where.=" or exists (select 'x' from $kvTable kv where kv.idKpiDefinition=$kpi->id and kv.refType='Project' and kv.refId=$pjTable.id and kv.kpiDate>='$start' and kv.kpiDate<='$end' ) ";
    $where.=" or exists (select 'x' from $peTable pe where pe.refType='Project' and pe.refId=$pjTable.id and pe.realEndDate>='$start' and pe.realEndDate<='$end' ) ";
    $where.=" ) ";
  }
  $listProjects=$prj->getSqlElementsFromCriteria(null,false,$where);
}

if (checkNoData($listProjects)) if (!empty($cronnedScript)) goto end; else exit;

$period=null;
$periodValue='';
if ($month) {
  $period='month';
  if ($month<10) $month='0'.intval($month);
  $periodValue=$year.$month;
} else if ($year) {
  $period='year';
  $periodValue=$year;
}
$newKpiThresh = new KpiThreshold();
$thresholds= $newKpiThresh->getSqlElementsFromCriteria(array('idKpiDefinition'=>$kpi->id),false,null,'thresholdValue desc');
$maxKpiValue=0;
$listKpiStatus=SqlList::getList($class.'Status','name',false);
$listKpiStatusValue=SqlList::getList($class.'Status','value',true);
$listKpiStatusColor=SqlList::getList($class.'Status','color',true);
foreach ($listKpiStatusValue as $kpiValue) {
  if ($kpiValue>$maxKpiValue) $maxKpiValue=$kpiValue;
}
$arrayProj=array();
if ($idProject) {
  $arrayProj[$idProject]=$listProjects[$idProject]->name;
  // TABLE DETAIL FOR EACH ITEM (Deliverable or Incoming)
  echo '<table width="90%" align="center">';
  echo '<tr>';
  echo '<td class="reportTableHeader" colspan="'.$nbCols.'" style="width:100%">' .$listProjects[$idProject]->name. '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="reportTableHeader" style="width:'.(($nbCols==8)?'15':'10').'%">' . i18n('colReference') . '</td>';
  echo '<td class="reportTableHeader" style="width:'.(($nbCols==8)?'15':'10').'%">' . i18n('col'.$class.'Name') . '</td>';
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colId'.$class.'Type') . '</td>';
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colId'.$class.'Status') . '</td>';
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colPlannedDate') . '</td>';
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colRealDate') . '</td>';
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colValidationDate') . '</td>';
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colId'.$class.'Weight') . '</td>';
  if ($class=='Incoming') {
    echo '<td class="reportTableHeader" style="width:10%">' . i18n('colImpact')
    .'<br/><span style="font-weight:normal">('.i18n('colWork').', <i>'.i18n('colDuration').'</i>, <b>'.i18n('colCost'). '</b>)</span></td>';
  }
  echo '<td class="reportTableHeader" style="width:10%">' . htmlEncode($kpi->name) . '</td>';
  echo '</tr>';
  $query="idProject=$idProject";
	/*if ($year) {
		if ($month) {
			$query.= " and h.month='$year$month'";
		} else if ($year==date('Y') and date('m')==1) {
	    $query.= " and (h.year='$year' or h.year='".($year-1)."')";
	  } else {
	    $query.= " and h.year='$year'";
	  }
	}*/
	$newClass = new $class();
  $itemList= $newClass->getSqlElementsFromCriteria(null,false,$query,'plannedDate asc');
  $itemListInClause='(0';
  $arrayHist=array('last'=>array());
  foreach ($itemList as $item) {
    $itemListInClause.=','.$item->id;
    echo '<tr>';
    echo '<td class="reportTableDataSpanned" style="width:'.(($nbCols==8)?'15':'10').'%">' . htmlEncode($item->externalReference) . '</td>';
    echo '<td class="reportTableDataSpanned" style="width:'.(($nbCols==8)?'15':'10').'%">' . htmlEncode($item->name) . '</td>';
    $fldType='id'.$class.'Type';
    echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlEncode(SqlList::getNameFromId('Type', $item->$fldType)) . '</td>';
    $fldStatus='id'.$class.'Status';
    $color=($item->$fldStatus)?$listKpiStatusColor[$item->$fldStatus]:'#ffffff';//SqlList::getFieldFromId($class.'Status',$item->$fldStatus,'color');
    $value=($item->$fldStatus)?$listKpiStatus[$item->$fldStatus]:'';//SqlList::getNameFromId($class.'Status',$item->$fldStatus);
    $arrayHist['last'][$item->id]=$item->$fldStatus;
    if ($kpiColorFull) {
      echo '<td class="reportTableData" style="width:10%;background-color:'.$color.';text-align:center;">' . htmlDisplayColoredFull($value, $color).'</td>';
    } else {
      echo '<td class="reportTableDataSpanned" style="width:10%;font-weight:bold;text-align:left">' . htmlDisplayColored($value, $color). '</td>';
    }
    echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlFormatDate($item->plannedDate) . '</td>';
    echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlFormatDate($item->realDate) . '</td>';
    echo '<td class="reportTableDataSpanned" style="width:10%">' .htmlFormatDate($item->validationDate) . '</td>';
    $fldWeight='id'.$class.'Weight';
    $value=SqlList::getNameFromId($class.'Weight',$item->$fldWeight);
    echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlEncode($value) . '</td>';
    if ($class=='Incoming') {
      $value='';
      $value.=($item->impactWork)?Work::displayWorkWithUnit($item->impactWork).'<br/>':'';
      $value.=($item->impactDuration)?'<i>'.$item->impactDuration.'&nbsp;'.i18n("shortDay").'</i><br/>':'';
      $value.=($item->impactCost)?'<b>'.htmlDisplayCurrency($item->impactCost,true).'</b>':'';
      echo '<td class="reportTableDataSpanned" style="width:10%">' . $value.  '</td>';
    }
    $value=(isset($listKpiStatusValue[$item->$fldStatus]))?$listKpiStatusValue[$item->$fldStatus]:null;
    $dispValue=($maxKpiValue!=0 and $value!==null)?(round($value/$maxKpiValue,2)):null;
    if ($dispValue!==null and $displayAsPct) $dispValue=htmlDisplayPct($dispValue*100);
    echo '<td class="reportTableDataSpanned" style="width:10%">' . $dispValue . '</td>';
    echo '</tr>';
  }
  $itemListInClause.=')';
  echo '</table><br/><br/>';
  if (count($itemList)==0) {
  	echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  	echo i18n('reportNoData');
  	echo '</div>';
  	return;
  }
  // retreive history of status value (Deliverable or Incoming)
  $whereHist="refType='$class' and refId in $itemListInClause and ( (operation='update' and colName='id".$class."Status') or operation='insert' or operation='delete')";
  $newHistory = new History();
  $histList= $newHistory->getSqlElementsFromCriteria(null,null,$whereHist, 'refId asc, operationDate desc');
  
  foreach ($histList as $hist) {
    $key=$hist->refId;
    $dt=substr($hist->operationDate,0,10);
    if (!isset($arrayHist[$key])) $arrayHist[$key]=array();
    if ($hist->operation=='delete') {
      $arrayHist[$key][$dt]='';
      $arrayHist['last'][$key]='';
    } else if ($hist->operation=='insert') {
      if (!isset($arrayHist[$key][$dt])) {
        $arrayHist[$key][$dt]=(isset($arrayHist['last'][$key]))?$arrayHist['last'][$key]:'';
      }
      $arrayHist['last'][$key]='';
    } else { // $hist->operation=='update'
      if (!isset($arrayHist[$key][$dt])) {
        $arrayHist[$key][$dt]=$hist->newValue;
      }
      $arrayHist['last'][$key]=$hist->oldValue;
    }  
  }
  unset($arrayHist['last']);
  $arrayResP=array();
  $arrayResCurrentPeriod=array();
  foreach ($arrayHist as $key=>$h) {
    ksort($h);
    foreach ($h as $dt=>$val) {
      if ($scaleHist=='year') $p=substr($dt,0,4);
      else if ($scaleHist=='month') $p=substr($dt,0,7); //$p=str_replace('-','',substr($dt,0,7));
      else if ($scaleHist=='week') $p=weekFormat($dt); //$p=str_replace('-','',weekFormat($dt));
      else $p=$dt; //$p=str_replace('-','',$dt);
      if (!isset($arrayResP[$p])) $arrayResP[$p]=array();
      $arrayResP[$p][$key]=$val;
      if ($period) {
        $pp=$dt; //$p=str_replace('-','',$dt);
        $arrayResCurrentPeriod[$dt][$key]=$val;
      }
    }
  }
  // TABLE SYNTHESIS FOR EACH STATUS, WEEK BY WEEK (Deliverable or Incoming)
  $nbCols=count($listKpiStatus);
  echo '<table width="90%" align="center">';
  echo '<tr>';
  echo '<td class="reportTableHeader" colspan="'.($nbCols+2).'"style="width:100%">' .$kpi->name . ' - '. $listProjects[$idProject]->name. '</td>';
  echo '</tr>';
  echo '<tr>';
  $pctWidth=round(75/$nbCols,0);
  echo '<td class="reportTableHeader" style="width:10%">' . i18n('colDate') . '</td>';
  foreach ($listKpiStatus as $idK=>$nameK) {
    echo '<td class="reportTableHeader" style="width:'.$pctWidth.'%">' . $nameK. '</td>';
  }
  echo '<td class="reportTableHeader" style="width:15%">' . $kpi->name . '</td>';
  echo '</tr>';
  $currentVal=array();
  $arrDates=array();
  $arrValuesStatus=array();
  $initArray=array();
  foreach($listKpiStatus as $idK=>$nameK) {
    $initArray[$idK]=0;
    $arrValuesStatus[$idK]=array();
  }
  
  ksort($arrayResP);
  foreach($arrayResP as $p=>$res) { // $arrayResP = array [period][idItem]=>idItemStatus
    foreach ($res as $idItem=>$valItem) {
      $currentVal[$idItem]=$valItem;
    }
    $cptArray=$initArray;
    foreach ($currentVal as $idItem=>$valItem) {
      if ($valItem) {
        $cptArray[$valItem]++;
      }
    }
    
    foreach ($listKpiStatus as $idK=>$nameK) {
      $arrValuesStatus[$idK][$p]=$cptArray[$idK];
    }
    echo '<tr>';
    $disP=$p;
    if ($scaleHist=='year') $disP=$p;
    else if ($scaleHist=='month') $disP=getMonthName(substr($p,4),'auto').'-'.substr($p,2,2);
    else if ($scaleHist=='week') $disP=$p;
    else $disP=htmlFormatDate($p);
    $arrDates[$p]=$disP;
    echo '<td class="reportTableDataSpanned" style="width:10%;">'.$disP.'</td>';
    foreach ($listKpiStatus as $idK=>$nameK) {
      echo '<td class="reportTableDataSpanned" style="width:'.$pctWidth.'%">' . $cptArray[$idK] . '</td>';
    }
    $newKpiHist = new KpiHistory();
    $lstKpiVal= $newKpiHist->getSqlElementsFromCriteria(array('idKpiDefinition'=>$kpi->id,'refType'=>'Project','refId'=>$idProject,$scaleHist=>str_replace('-','',$p)),false,null,'kpiDate desc');
    $kpiDispValue='';
    $color='#ffffff';
    if (count($lstKpiVal)) {
      $kpiValue=reset($lstKpiVal);
      foreach ($thresholds as $th) {
        if ($kpiValue->kpiValue>$th->thresholdValue) {
          $color=$th->thresholdColor;
          break;
        }
      }
      $dispValue=$kpiValue->kpiValue;
      if ($dispValue and $displayAsPct) $dispValue=htmlDisplayPct($dispValue*100);
    }
    if ($kpiColorFull) {
      echo '<td class="reportTableData" style="width:15%;background-color:'.$color.';text-align:center">'
          . (($dispValue)?htmlDisplayColoredFull($dispValue, $color):'')
          . '</td>';
    } else {
      echo '<td class="reportTableDataSpanned" style="width:15%;text-align:left">'
          . (($dispValue)?htmlDisplayColored($dispValue, $color):'') . '</td>';
    }
    echo '</tr>';
  }
  if ($period) {
    $currentVal=array();
    foreach($arrayResCurrentPeriod as $p=>$res) {
      if ($period=='year') $pp=substr($p,0,4);
      else if ($period=='month') $pp=str_replace('-','',substr($p,0,7));
      else if ($period=='week') $pp=str_replace('-','',weekFormat($p));
      else $pp=str_replace('-','',$p);
      if ($pp>$periodValue) break;
      foreach ($res as $idItem=>$valItem) {
        $currentVal[$idItem]=$valItem;
      }
    }
    $cptArray=$initArray;
    foreach ($currentVal as $idItem=>$valItem) {
      if ($valItem) {
        $cptArray[$valItem]++;
      }
    }
    $disP=$periodValue;
    if ($period=='year') $disP=$periodValue;
    else if ($period=='month') $disP=getMonthName(substr($periodValue,4),'auto').'-'.substr($periodValue,2,2);
    else if ($period=='week') $disP=substr($periodValue,0,4).'-'.substr($periodValue,4);
    else $disP=htmlFormatDate($periodValue);
    echo '<tr>';
    echo '<td class="reportTableHeader" style="width:10%;">'.$disP.'</td>';
    foreach ($listKpiStatus as $idK=>$nameK) {
      echo '<td class="reportTableHeader" style="width:'.$pctWidth.'%">' . $cptArray[$idK] . '</td>';
    }
    //$critKpi=array('idKpiDefinition'=>$kpi->id,'refType'=>'Project','refId'=>$prj->id);
    //$critKpi[$period]=$periodValue;
    /*$lstKpi=(new KpiHistory())->getSqlElementsFromCriteria($critKpi,false,null,'kpiValue desc');
    if (count($lstKpi)==0) {
      $where="idKpiDefinition=$kpi->id and refType='Project' and refId=$prj->id and $period<=$periodValue";
      $lstKpi=(new KpiHistory())->getSqlElementsFromCriteria(null,false,$where,'kpiDate desc');
    }*/
    $where="idKpiDefinition=$kpi->id and refType='Project' and refId=$idProject and $period<=$periodValue";
    $newKpiHis = new KpiHistory();
    $lstKpi=$newKpiHis->getSqlElementsFromCriteria(null,false,$where,'kpiDate desc');
    $kpiDispValue='';
    $dispValue='';
    $color='#ffffff';
    if (count($lstKpi)>0) {
      $kpiValue=reset($lstKpi);
      foreach ($thresholds as $th) {
        if ($kpiValue->kpiValue>$th->thresholdValue) {
          $color=$th->thresholdColor;
          break;
        }
      }
      $dispValue=$kpiValue->kpiValue;
      if ($dispValue and $displayAsPct) $dispValue=htmlDisplayPct($dispValue*100);
    }
    if ($kpiColorFull) {
      echo '<td class="reportTableData" style="width:15%;background-color:'.$color.';text-align:center;">'
          . (($dispValue)?htmlDisplayColoredFull($dispValue, $color):'')
          . '</td>';
    } else {
      echo '<td class="reportTableDataSpanned" style="width:15%;text-align:left">'
          . (($dispValue)?htmlDisplayColored($dispValue, $color):'') . '</td>';
    }
    echo '</tr>';
  }
  echo '</table><br/><br/>';
  
} else { // $idProject == null
  echo '<table width="90%" align="center">';
  echo '<tr>';
  echo '<td class="reportTableHeader" style="width:35%">' . i18n('Project') . '</td>';
  echo '<td class="reportTableHeader" style="width:35%">' . i18n('Client') . '</td>';
  echo '<td class="reportTableHeader" style="width:30%">' . htmlEncode($kpi->name) . '</td>';
  echo '</tr>';
  $arrayProj=array();
  $cptProjectsDisplayed=0;
  $sumValues=0;
  $sumWeight=0;
  foreach($listProjects as $prj) {
    $arrayProj[$prj->id]=$prj->name;
    if (! array_key_exists($prj->id, $visibleProjects)) continue; // Will avoid to display projects not visible to user
    if ($done and ! $prj->ProjectPlanningElement->realEndDate) continue;
    if ($done and isset($start) and isset($end)) {
      if ($prj->ProjectPlanningElement->realEndDate<$start or $prj->ProjectPlanningElement->realEndDate>$end) {continue;}
    }
    $cptProjectsDisplayed++;
    echo '<tr>';
    echo '<td class="reportTableDataSpanned" style="width:35%;text-align:left">' . htmlEncode($prj->name) . '</td>';
    echo '<td class="reportTableDataSpanned" style="width:35%;text-align:left">' . htmlEncode(SqlList::getNameFromId('Client', $prj->idClient)) . '</td>';
    $critKpi=array('idKpiDefinition'=>$kpi->id,'refType'=>'Project','refId'=>$prj->id);
    if (!$period) { // Added if (1) as value displayed in table is always the actual value, 
      $kpiValue=SqlElement::getSingleSqlElementFromCriteria('KpiValue', $critKpi);
    } else {
      $critKpi[$period]=$periodValue;
      $newHistKpi = new KpiHistory();
      $lstKpi=$newHistKpi->getSqlElementsFromCriteria($critKpi,false,null,'kpiDate desc');
      $kpiValue=reset($lstKpi);
    }
    if (!$kpiValue) $kpiValue=new KpiValue();
    $color='#ffffff';
    foreach ($thresholds as $th) {
      if ($kpiValue->kpiValue>$th->thresholdValue) {
        $color=$th->thresholdColor;
        break;
      }
    }
    $dispValue=$kpiValue->kpiValue;
    if ($dispValue and $displayAsPct) $dispValue=htmlDisplayPct($dispValue*100);
    if ($kpiColorFull) {
      echo '<td class="reportTableData" style="width:30%;background-color:'.$color.';text-align:center;">' 
          . (($dispValue)?htmlDisplayColoredFull($dispValue, $color):'') 
          . '</td>';
    } else {
      echo '<td class="reportTableDataSpanned" style="width:30%;text-align:left">' 
          . (($dispValue)?htmlDisplayColored($dispValue, $color):'') . '</td>';
    }
    if ($kpiValue->kpiValue) {
      $sumValues+=$kpiValue->kpiValue*$kpiValue->weight;
      $sumWeight+=$kpiValue->weight;
    }
    echo '</tr>';
  }
  if ($cptProjectsDisplayed>0 and $scope=='Organization') {
    echo '<tr>';
    $name=$kpi->name;
    if ($idOrganization) {
      $org=new Organization($idOrganization,true);
      $name.=' - '.$org->name;
    }
    if ($idProjectType) {
      $typ=new ProjectType($idProjectType,true);
      $name.=' - '.$typ->name;
    }
    if ($done) {
      $name.=' ('.i18n("colOnlyFinished").')';
    }
    echo '<td class="reportTableHeader" style="width:70%;text-align:left" colspan="2">' . $name . '</td>';
    $consolidated=($sumWeight)?round($sumValues/$sumWeight,2):null;
    $color='#ffffff';
    foreach ($thresholds as $th) {
      if ($consolidated>$th->thresholdValue) {
        $color=$th->thresholdColor;
        break;
      }
    }
    if ($consolidated and $displayAsPct) $consolidated=htmlDisplayPct($consolidated*100);
    if ($kpiColorFull) {
      echo '<td class="reportTableData" style="width:20%;background-color:'.$color.';text-align:center;">' . (($consolidated)?htmlDisplayColoredFull($consolidated, $color):'') . '</td>';
    } else {
      echo '<td class="reportTableDataSpanned" style="width:20%;font-weight:bold;text-align:left">' . (($consolidated)?htmlDisplayColored($consolidated, $color):'') . '</td>';
    }
    echo '</tr>';
  }
  echo '</table>';
}

// Graph
if (! testGraphEnabled()) { return;}

if ($idProject) {
  $dataSet = new pData();
  $graphWidth=700;
  $graphHeight=500;
  $cpt=0;
  foreach ($arrValuesStatus as $idStat=>$arrStat) {
    $cpt++;
    $dataSet->addPoints($arrStat,"status".$cpt);
    $dataSet->setSerieOnAxis("status".$cpt,0);
    $dataSet->setSerieDescription("status".$cpt,$listKpiStatus[$idStat]);    
    $dataSet->setSerieDrawable("status".$cpt,true);
    $color=$listKpiStatusColor[$idStat];
    $format=array_merge(hex2rgb($color),array("Alpha"=>255));
    $dataSet->setPalette("status".$cpt,$format);
  }
  $dataSet->addPoints($arrDates,"dates");
  $dataSet->setAbscissa("dates");
  $graph = new pImage($graphWidth,$graphHeight,$dataSet);
  /* Draw the background */
  $graph->Antialias = FALSE;
  $Settings = array("R"=>255, "G"=>255, "B"=>255, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
  $graph->drawFilledRectangle(0,0,$graphWidth,$graphHeight,$Settings);
  /* Add a border to the picture */
  $graph->drawRectangle(0,0,$graphWidth-1,$graphHeight-1,array("R"=>150,"G"=>150,"B"=>150));
  /* Set the default font */
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>9,"R"=>50,"G"=>50,"B"=>50));
  /* Draw the scale */
  //$dataSet->setAxisUnit(0,' '.$currency.' ');
  $marginTop=50;
  $marginBottom=80+15*count($arrValuesStatus)+10;
  $marginLeft=50;
  $marginRight=20;
  $graph->setGraphArea($marginLeft,$marginTop,$graphWidth-$marginRight,$graphHeight-$marginBottom);
  $graph->drawFilledRectangle($marginLeft,$marginTop,$graphWidth-$marginRight,$graphHeight-$marginBottom,array("R"=>230,"G"=>230,"B"=>230));
  $formatGrid=array("Mode"=>SCALE_MODE_ADDALL_START0, "GridTicks"=>0,
      "DrawYLines"=>false, "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
      "LabelRotation"=>90, "GridR"=>150,"GridG"=>150,"GridB"=>150);
  $graph->drawScale($formatGrid);
  $format=array( "BorderR"=>0,"BorderG"=>0,"BorderB"=>0);
  $graph->drawStackedBarChart($format);
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>12,"R"=>50,"G"=>50,"B"=>50));
  $name=$kpi->name;
  $prj=new Project($idProject,true);
  $name.=' - '.$prj->name;
  $name=wordwrap($name,50);
  $graph->drawText($graphWidth/2,25,$name,array("FontSize"=>12,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10,"R"=>100,"G"=>100,"B"=>100));
  $graph->drawLegend($marginLeft,$graphHeight-$marginBottom+80,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
      "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
      "FontR"=>55,"FontG"=>55,"FontB"=>55,
      "Margin"=>5));
  $imgName=getGraphImgName("kpitermsummary");
  $graph->Render($imgName);
  echo '<br/><br/><br/>';
  echo '<table width="95%" align="center"><tr><td align="center">';
  echo '<img style="width:'.$graphWidth.'px;height:'.$graphHeight.'" src="' . $imgName . '" />';
  echo '</td></tr></table>';
  echo '<br/>';
  
}

// constitute query and execute for planned post $end (last real work day)
$h=new KpiHistory();
$hTable=$h->getDatabaseTableName();
$query = "select SUM(prj.valueP) / SUM(prj.weightP) as value, prj.periodP as period";
$query.= " from (select MAX(h.kpiValue*h.weight) as valueP, MIN(h.weight) as weightP, h.$scale as periodP, h.refId as idP";
$query.= " from $hTable h";
$query.= " where h.idKpiDefinition=$kpi->id and h.refType='Project' and h.refId in " . transformListIntoInClause($arrayProj);
if ($done) {$query.= " and h.refDone=1";}
if ($year) {
  if ($month==1 or (!$month and $year==date('Y') and date('m')==1)) {
    $query.= " and (h.year='$year' or h.year='".($year-1)."')";
  } else {
    $query.= " and h.year='$year'";
  }
}
$query.= " group by h.$scale, h.refId) prj ";
$query.= " group by periodP";
$result=Sql::query($query);

$end='';
$start='';
$valArray=array();
foreach ($result as $line) {
  if ($end=='' or $line['period']>$end) $end=$line['period'];
  if ($start=='' or $line['period']<$start) $start=$line['period'];
  $arrValues[$line['period']]=round($line['value'],2)*(($displayAsPct)?100:1);
}
$cptProjectsDisplayed=count($arrayProj);
if ($cptProjectsDisplayed==0 and (!$start or !$end)) {
  return;
}
$lastValue=VOID;
$arrDates=array();
$date=$start;
while ($date<=$end) {
  if (isset($arrValues[$date])) {
    $lastValue=$arrValues[$date];
  } else {
    if ($done) {
      $arrValues[$date]=VOID;
    } else {
      $arrValues[$date]=$lastValue;
    }
  }
  $arrDates[$date]=$date;
  if ($scale=='day') {
    $d=substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
    $d=addDaysToDate($d, 1);
    $date=str_replace('-','',$d);
  } else if ($scale=='week') { 
    $w=substr($date,4,2);
    $y=substr($date,0,4);
    $w++;
    $lastDayOfYear=$y.'-12-31';
    if ($w>weekNumber($lastDayOfYear)) {
      $y++;
      $w='1';
    }
    if ($w<10) $w='0'.intval($w);
    $date=$y.$w; 
  } else if ($scale=='month') { 
    $m=substr($date,4,2);
    $y=substr($date,0,4);
    $m++;
    if ($m>12) {
      $y++;
      $m=1;
    }
    if ($m<10) $m='0'.intval($m);
    $date=$y.$m;
  } else if ($scale=='year') { 
    $date++;  
  }
}
ksort($arrValues);

$graphWidth=700;
$graphHeight=400;
$indexToday=0;
$maxPlotted=50; // max number of point to get plotted lines. If over lines are not plotted/

$cpt=0;
$modulo=intVal(50*count($arrDates)/$graphWidth);
if ($modulo<0.5) $modulo=0;
foreach ($arrDates as $id=>$date) {
  if ($modulo!=0 and $cpt % $modulo !=0 ) {
    $arrDates[$id]=VOID;
  } else {
    if ($scale=='day') {
      $arrDates[$id]=htmlFormatDate(substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2)); 
    } else if ($scale=='week') {
      $arrDates[$id]=htmlFormatDate(substr($date,0,4).'-'.substr($date,4,2));
    } else if ($scale=='month') {
      $arrDates[$id]=getMonthName(substr($date,4),'auto').'-'.substr($date,2,2);
    } else if ($scale=='year') {
      $arrDates[$id]=$date;
    }
  }
  $cpt++;
}

$dataSet = new pData();
// Definition of series
$dataSet->addPoints($arrValues,"kpi");
$dataSet->addPoints($arrDates,"dates");
$dataSet->setSerieOnAxis("kpi",0);
$dataSet->setAbscissa("dates");

/* Create the pChart object */
$graph = new pImage($graphWidth,$graphHeight,$dataSet);

/* Draw the background */
$graph->Antialias = FALSE;
$Settings = array("R"=>255, "G"=>255, "B"=>255, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawFilledRectangle(0,0,$graphWidth,$graphHeight,$Settings);

/* Add a border to the picture */
$graph->drawRectangle(0,0,$graphWidth-1,$graphHeight-1,array("R"=>150,"G"=>150,"B"=>150));

/* Set the default font */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>9,"R"=>50,"G"=>50,"B"=>50));

/* Draw the scale */
$dataSet->setAxisUnit(0,($displayAsPct)?"%  ":"  ");
$graph->setGraphArea(55,70,$graphWidth-20,$graphHeight-60);
$graph->drawFilledRectangle(55,70,$graphWidth-20,$graphHeight-60,array("R"=>230,"G"=>230,"B"=>230));

$graph->Antialias = TRUE;
if ($showThreshold) {
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>9,"R"=>50,"G"=>50,"B"=>50));
  $cpt=0;
  foreach ($thresholds as $th) {
    if ($th->thresholdValue==0) continue;
    $cpt++;
    $arrTh=array();
    foreach ($arrDates as $dt) {$arrTh[]=$th->thresholdValue*(($displayAsPct)?100:1);}
    $dataSet->addPoints($arrTh,"th".$cpt);
    $format=array_merge(hex2rgb($th->thresholdColor),array("Alpha"=>255));
    $dataSet->setPalette("th".$cpt,$format);
    $dataSet->setSerieDrawable("th".$cpt,true);
    $dataSet->setSerieWeight("th".$cpt,1);
    //$dataSet->setSerieOnAxis("th"+$cpt,0);
  }
}
$formatGrid=array("LabelSkip"=>$modulo, "SkippedAxisAlpha"=>(($modulo>9)?0:20), "SkippedGridTicks"=>0,
    "Mode"=>SCALE_MODE_FLOATING , "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>150,"GridG"=>150,"GridB"=>150);
$graph->drawScale($formatGrid);
$dataSet->setSerieDrawable("kpi",false);
$graph->drawLineChart();

$dataSet->setSerieWeight("kpi",1);
$dataSet->setPalette("kpi",array("R"=>120,"G"=>140,"B"=>250,"Alpha"=>255));
$dataSet->setSerieDrawable("kpi",true);
$graph->drawLineChart();
$cpt=0;
if (count($arrDates)==1) {
  $graph->drawPlotChart();
} 
foreach ($thresholds as $th) {
  $cpt++;
  $dataSet->setSerieDrawable("th".$cpt,false);
}
$graph->drawLineChart();
if (count($arrDates)<$maxPlotted) {
  $graph->drawPlotChart();
}
//if ($showToday) $graph->drawXThreshold(array($indexToday),array("Alpha"=>70,"Ticks"=>0));
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>12,"R"=>50,"G"=>50,"B"=>50));
$name=i18n('kpiProgress').' '.$kpi->name;
if ($idProject) {
  $prj=new Project($idProject,true);
  $name.=' - '.$prj->name;
} 
if ($idOrganization) {
  $org=new Organization($idOrganization,true);
  $name.=' - '.$org->name;
}
if ($idProjectType) {
  $typ=new ProjectType($idProjectType,true);
  $name.=' - '.$typ->name;
}
$name=wordwrap($name,50); 
$graph->drawText($graphWidth/2,35,$name,array("FontSize"=>18,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));

/* Render the picture (choose the best way) */
$imgName=getGraphImgName("kpiworkloadchart");
$graph->Render($imgName);
echo '<br/><br/><br/>';
echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img style="width:700px;height:400px" src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

end:

?>