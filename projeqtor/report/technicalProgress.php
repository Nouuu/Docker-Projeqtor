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
  include_once('../tool/formatter.php');
//echo "workPlan.php";
  $objectClass='PlanningElement';
  $obj=new $objectClass();
  $table=$obj->getDatabaseTableName();
  $print=false;
  if ( array_key_exists('print',$_REQUEST) ) {
    $print=true;
  }

  // Header
  $headerParameters="";
  if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
	$paramProject=trim($_REQUEST['idProject']);
	Security::checkValidId($paramProject);

    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  //gautier ticket #2354
  $showIdle=false;
  
  if (array_key_exists('showIdle',$_REQUEST)) {
    $showIdle=true;
    $headerParameters.= i18n("labelShowIdle").'<br/>';
  }
  
  
  if (array_key_exists('outMode', $_REQUEST) && $_REQUEST['outMode'] == 'csv') {
      $outMode = 'csv';
  } else {
      $outMode = 'html';
  }
  if ($outMode == 'csv') {
      include_once "headerFunctions.php";
  } else {
      include "header.php";
  }

  $accessRightRead=securityGetAccessRight('menuProject', 'read');

  $querySelect = '';
  $queryFrom='';
  $queryWhere='';
  $queryOrderBy='';
  $idTab=0;
//   if (! array_key_exists('idle',$_REQUEST) ) {
//     $queryWhere= $table . ".idle=0 ";
//   }
  //gautier ticket #2354
  if ($showIdle) {
    $queryWhere ="1=1 ";
  }else{
    $queryWhere= $table . ".idle=0 ";
  }
  $queryWhere.= ($queryWhere=='')?'':' and ';
  $queryWhere.=getAccesRestrictionClause('Activity',$table,false,true,true);
  if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
	  $paramProject=trim($_REQUEST['idProject']);
	  Security::checkValidId($paramProject);
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(true, $paramProject) ;
  }

  $querySelect .= $table . ".* ";
  $queryFrom .= $table;
  $queryOrderBy .= $table . ".wbsSortable ";

  // constitute query and execute
  $query='select ' . $querySelect
       . ' from ' . $queryFrom
       . ' where ' . $queryWhere
       . ' order by ' . $queryOrderBy;
  $result=Sql::query($query);
  $test=array();
  if (Sql::$lastQueryNbRows > 0) $test[]="OK";
  if (checkNoData($test))  if (!empty($cronnedScript)) goto end; else exit;

  if (Sql::$lastQueryNbRows > 0) {
    // Header
    if ($outMode != 'csv') {
    echo '<table align="center">';
    echo '<TR>';
    echo '  <TD class="reportTableHeader" style="width:10px; border-right: 0px;" rowspan="2"></TD>';
    echo '  <TD class="reportTableHeader" style="width:200px; border-left:0px; text-align: left;" rowspan="2">' . i18n('colTask') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width:50px" rowspan="2" nowrap>' . i18n('colWeight') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:50px" colSpan="2" nowrap>' . i18n('colPlannedDate2') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:50px" colSpan="2"nowrap>' . i18n('technicalWork') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:50px" colSpan="3"nowrap>' . i18n('technicalProgress') . '</TD>' ;
    echo '</TR> ';
    echo '<TR>';
    echo '  <TD class="reportTableHeader" style="width:100px" nowrap>' . i18n('colStart') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:100px" nowrap >' . i18n('colEnd') . '</TD>';
    echo '  <TD  class="reportTableHeader" style="width:55px" nowrap>' . i18n('colAssigned') . '</TD>' ;
    echo '  <TD  class="reportTableHeader" style="width:55px" nowrap>' . i18n('colReal') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colToRealise') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colRealised') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:70px" nowrap>' . i18n('colProgress') . '</TD>' ;

    echo '</TR>';
    }else{
        echo encodeCSV(i18n('colTask')).';';
        echo encodeCSV(i18n('colUnitWeight')).';';
        echo encodeCSV(i18n('colPlannedStartDate')).';';
        echo encodeCSV(i18n('colPlannedEndDate')).';';
        echo encodeCSV(i18n('colAssigned')).';';
        echo encodeCSV(i18n('colReal')).';';
        echo encodeCSV(i18n('colUnitToRealise')).';';
        echo encodeCSV(i18n('colUnitRealised')).';';
        echo encodeCSV(i18n('colUnitProgress'));
        echo "\n";
    }
    // Treat each line
    while ($line = Sql::fetchLine($result)) {
      $line=array_change_key_case($line,CASE_LOWER);
      $weight=$line['unitweight'];
      $startDate=$line['plannedstartdate'];
      $endDate=$line['plannedenddate'];
      $assignedWork=$line['assignedwork'];
      //$plannedWork=$line['plannedwork'];
      $realWork=$line['realwork'];
      $toRealise=$line['unittorealise'];
      $realised=$line['unitrealised'];
      $progress=$line['unitprogress'];
      $pGroup=($line['elementary']=='0')?1:0;
      $compStyle="";
      if( $pGroup) {
        $rowType = "group";
        $compStyle="font-weight: bold; background: #E8E8E8 ;";
      } else if( $line['reftype']=='Milestone'){
        $rowType  = "mile";
        $compStyle="font-weight: light; font-style:italic;";
      } else {
        $rowType  = "row";
      }
      $wbs=$line['wbssortable'];
      $level=(strlen($wbs)+1)/4;
      $tab="";
      for ($i=1;$i<$level;$i++) {
        $tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
      }
      if($outMode != 'csv'){
      echo '<TR>';
      echo '    <TD class="reportTableData" style="border-right:0px;' . $compStyle . '">'.formatIcon($line['reftype'], 16).'</TD>'; 
      echo '    <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '" nowrap>' . $tab . htmlEncode($line['refname']) . '</TD>';
      echo '    <TD class="reportTableData" style="' . $compStyle . '">' .round($weight). '</TD>' ;
      echo '    <TD class="reportTableData" style="width:100px;' . $compStyle . '">' . dateFormatter($startDate) . '</TD>' ;
      echo '    <TD class="reportTableData" style="width:100px;' . $compStyle . '">' . dateFormatter($endDate) . '</TD>';
      echo '    <TD class="reportTableData" style="width:60px;' . $compStyle . '">' . Work::displayWorkWithUnit($assignedWork) . '</TD>' ;
      echo '    <TD class="reportTableData" style="width:50px;' . $compStyle . '">' . Work::displayWorkWithUnit($realWork) . '</TD>' ;
      echo '    <TD class="reportTableData" style="width:65px;' . $compStyle . '">' . $toRealise . '</TD>' ;
      echo '    <TD class="reportTableData" style="width:65px;' . $compStyle . '">' . $realised . '</TD>' ;
      echo '    <TD class="reportTableData" style="width:69px;' . $compStyle . '">' . percentFormatter(round($progress)) . '</TD>' ;
      echo '</TR>';
      }else{
        echo encodeCSV(htmlEncode($line['refname'])) .';';
        echo $weight.';';
        echo $startDate.';';
        echo $endDate.';';
        echo $assignedWork.';';
        echo $realWork.';';
        echo $toRealise.';';
        echo $realised.';';
        echo $progress;
        echo "\n";
      }
    }
  }
  if($outMode != 'csv'){
  echo "</table>";
  }

end:
  
?>