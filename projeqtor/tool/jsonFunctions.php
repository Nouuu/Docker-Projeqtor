<?PHP
/**
 * * COPYRIGHT NOTICE *********************************************************
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
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org
 *
 * ** DO NOT REMOVE THIS NOTICE ***********************************************
 */

/**
 * ===========================================================================
 * generic functions for json extractions
 */
require_once "../tool/projeqtor.php";

function jsonGetFilterArray($filterObjectClass, $comboDetail=false) {
  $arrayFilter=array();
  if (!$comboDetail and is_array(getSessionUser()->_arrayFilters)) {
    if (array_key_exists($filterObjectClass, getSessionUser()->_arrayFilters)) {
      $arrayFilter=getSessionUser()->_arrayFilters[$filterObjectClass];
    }
  } else if ($comboDetail and is_array(getSessionUser()->_arrayFiltersDetail)) {
    if (array_key_exists($filterObjectClass, getSessionUser()->_arrayFiltersDetail)) {
      $arrayFilter=getSessionUser()->_arrayFiltersDetail[$filterObjectClass];
    }
  }
  foreach ($arrayFilter as $idx=>$arr) {
    if (strpos($arr['sql']['attribute'],'PlanningMode')>0) {
      $arrayFilter[$idx]['sql']['attribute']=str_replace(array('idActivityPlanningMode','idTestSessionPlanningMode','idMilestonePlanningMode'),'idPlanningMode',$arr['sql']['attribute']);
    }
  } 
  return $arrayFilter;
}

function jsonBuildSortCriteria(&$querySelect, &$queryFrom, &$queryWhere, &$queryOrderBy, &$idTab, $arrayFilter, $obj) {
  $objectClass=($obj)?get_class($obj):'';
  $table=$obj->getDatabaseTableName();
  foreach ($arrayFilter as $crit) {
    if ($crit['sql']['operator']=='SORT') {
      $doneSort=false;
      $split=explode('_', $crit['sql']['attribute']);
      if (strpos($crit['sql']['attribute'], '__id')>0) $split=array();
      if (count($split)>1) {
        $externalClass=$split[0];
        $externalObj=new $externalClass();
        $externalTable=$externalObj->getDatabaseTableName();
        $idTab+=1;
        $externalTableAlias='T'.$idTab;
        $queryFrom.=' left join '.$externalTable.' as '.$externalTableAlias.' on ( '.$externalTableAlias.".refType='".get_class($obj)."' and ".$externalTableAlias.'.refId = '.$table.'.id )';
        $queryOrderBy.=($queryOrderBy=='')?'':', ';
        $queryOrderBy.=" ".$externalTableAlias.'.'.(($split[1]=='wbs' and property_exists($externalObj, 'wbsSortable'))?'wbsSortable':$split[1])." ".$crit['sql']['value'];
        $doneSort=true;
      }
      if (substr($crit['sql']['attribute'], 0, 2)=='id' and strlen($crit['sql']['attribute'])>2) {
        $externalClass=substr($crit['sql']['attribute'], 2);
        $externalObj=new $externalClass();
        $externalTable=$externalObj->getDatabaseTableName();
        $sortColumn='id';
        if (property_exists($externalObj, 'sortOrder') and $externalClass!='Project') {
          $sortColumn=$externalObj->getDatabaseColumnName('sortOrder');
        } else {
          $sortColumn=$externalObj->getDatabaseColumnName('name');
        }
        $idTab+=1;
        $externalTableAlias='T'.$idTab;
        $queryOrderBy.=($queryOrderBy=='')?'':', ';
        $queryOrderBy.=" ".$externalTableAlias.'.'.$sortColumn." ".str_replace("'", "", $crit['sql']['value']);
        $queryFrom.=' left join '.$externalTable.' as '.$externalTableAlias.' on '.$table.".".$obj->getDatabaseColumnName('id'.$externalClass).' = '.$externalTableAlias.'.'.$externalObj->getDatabaseColumnName('id');
        $doneSort=true;
      }
      if (!$doneSort) {
        $queryOrderBy.=($queryOrderBy=='')?'':', ';
        $queryOrderBy.=" ".$table.".".$obj->getDatabaseColumnName($crit['sql']['attribute'])." ".$crit['sql']['value'];
      }
    }
  }
}

function jsonBuildWhereCriteria(&$querySelect, &$queryFrom, &$queryWhere, &$queryOrderBy, &$idTab, $arrayFilter, $obj) {
  $objectClass=($obj)?get_class($obj):'';
  $table=$obj->getDatabaseTableName();
  $queryWhereTmp='';
  $filterIsDynamic=false;
  foreach ($arrayFilter as $crit) {
    if (array_key_exists('isDynamic', $crit) and $crit['isDynamic']=='1') {
      $filterIsDynamic=true;
      break;
    }
  }
  if (!$filterIsDynamic and count($arrayFilter)>0) {
    $arrayFilter=array_values($arrayFilter);
    for ($i=0; $i<count($arrayFilter); $i++) {
      $crit=$arrayFilter[$i];
      if ($crit['sql']['operator']!='SORT') { // Sorting already applied previously
        $split=explode('_', $crit['sql']['attribute']);
        if (strpos($crit['sql']['attribute'], '__id')>0) $split=array();
        $critSqlValue=$crit['sql']['value'];
        if (substr($crit['sql']['attribute'], -4, 4)=='Work') {
          if ($objectClass=='Ticket') {
            $critSqlValue=Work::convertImputation(trim($critSqlValue, "'"));
          } else {
            $critSqlValue=Work::convertWork(trim($critSqlValue, "'"));
          }
        }
        if ($crit['sql']['operator']=='IN' and ($crit['sql']['attribute']=='idProduct' or $crit['sql']['attribute']=='idProductOrComponent' or $crit['sql']['attribute']=='idComponent')) {
          $critSqlValue=str_replace(array(' ', '(', ')'), '', $critSqlValue);
          $splitVal=explode(',', $critSqlValue);
          $critSqlValue='(0';
          foreach ($splitVal as $idP) {
            $prod=new Product($idP);
            $critSqlValue.=', '.$idP;
            $list=$prod->getRecursiveSubProductsFlatList(false, false); // Will work only if selected is Product, not for Component
            foreach ($list as $idPrd=>$namePrd) {
              $critSqlValue.=', '.$idPrd;
            }
          }
          $critSqlValue.=')';
        }
        if ($crit['sql']['operator']=='IN' and $critSqlValue==='0') {
        	$critSqlValue='(0)';
        }
        if (count($split)>1) {
          $externalClass=$split[0];
          $externalObj=new $externalClass();
          $externalTable=$externalObj->getDatabaseTableName();
          $idTab+=1;
          $externalTableAlias='T'.$idTab;
          $queryFrom.=' left join '.$externalTable.' as '.$externalTableAlias.' on ( '.$externalTableAlias.".refType='".get_class($obj)."' and ".$externalTableAlias.'.refId = '.$table.'.id )';
          // FIX #3069 PBE - Start
          // $queryWhereTmp.=($queryWhereTmp=='' or $queryWhereTmp=='(')?'':' and ';
          if (isset($crit['orOperator']) and $crit['orOperator']=="1") {
            $queryWhereTmp.=' or ';
          } else if (count($arrayFilter)>1 and $i+1<count($arrayFilter) and isset($arrayFilter[$i+1]['orOperator']) and $arrayFilter[$i+1]['orOperator']=='1') {
            $queryWhereTmp.=' and ';
            for ($j=$i+1; $j<count($arrayFilter) and $arrayFilter[$j]['orOperator']=='1'; $j++) {
              $queryWhereTmp.='(';
            }
          } else {
            $queryWhereTmp.=' and ';
          }
          $queryWhereTmp.=$externalTableAlias.".".$split[1].' '.$crit['sql']['operator'].' '.$critSqlValue;
        } else {
          if (isset($crit['orOperator']) and $crit['orOperator']=="1") {
            $queryWhereTmp.=' or ';
          } else if (count($arrayFilter)>1 and $i+1<count($arrayFilter) and isset($arrayFilter[$i+1]['orOperator']) and $arrayFilter[$i+1]['orOperator']=='1') {
            $queryWhereTmp.=' and ';
            for ($j=$i+1; $j<count($arrayFilter) and $arrayFilter[$j]['orOperator']=='1'; $j++) {
              $queryWhereTmp.='(';
            }
          } else {
            $queryWhereTmp.=' and ';
          }
          
          if (trim($crit['sql']['operator'])!='exists' and trim($crit['sql']['operator'])!='not exists') {
            $queryWhereTmp.="(".$table.".".$crit['sql']['attribute'].' ';
          }
          $queryWhereTmp.=$crit['sql']['operator'].' '.$critSqlValue;
          if (strlen($crit['sql']['attribute'])>=9 and substr($crit['sql']['attribute'], 0, 2)=='id' and (substr($crit['sql']['attribute'], -7)=='Version' and SqlElement::is_a(substr($crit['sql']['attribute'], 2), 'Version')) and $crit['sql']['operator']=='IN') {
            $scope=substr($crit['sql']['attribute'], 2);
            $vers=new OtherVersion();
            $queryWhereTmp.=" or exists (select 'x' from ".$vers->getDatabaseTableName()." VERS "." where VERS.refType=".Sql::str($objectClass)." and VERS.refId=".$table.".id and scope=".Sql::str($scope)." and VERS.idVersion IN ".$critSqlValue.")";
          } else if ($crit['sql']['attribute']=='idClient' and $crit['sql']['operator']=='IN' and property_exists($objectClass, 'idClient') and property_exists($objectClass, '_OtherClient')) {
            $otherclient=new OtherClient();
            $queryWhereTmp.=" or exists (select 'x' from ".$otherclient->getDatabaseTableName()." other "." where other.refType=".Sql::str($objectClass)." and other.refId=".$table.".id and other.idClient IN ".$critSqlValue.")";
          }
          if ($crit['sql']['operator']=='NOT IN') {
            $queryWhereTmp.=" or ".$table.".".$crit['sql']['attribute']." IS NULL ";
          }
          if ($crit['sql']['operator']=='=' and ($critSqlValue=="'0'" or $critSqlValue=='0') ) {
            $queryWhereTmp.=' or '.$table.".".$crit['sql']['attribute']. ' is null ';
          }
          if (trim($crit['sql']['operator'])!='exists' and trim($crit['sql']['operator'])!='not exists') {
            $queryWhereTmp.=")";
          }
        }
      }
      if (isset($crit['orOperator']) and $crit['orOperator']=='1') {
        $queryWhereTmp.=')';
      }
    }
    $queryWhere.=$queryWhereTmp;
  }
}

