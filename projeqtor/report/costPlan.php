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
//echo "costPlan.php";
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
		$idProject = trim($_REQUEST['idProject']);
		$idProject = Security::checkValidId($idProject);
		$headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $idProject)) . '<br/>';
  }
  $showIdle=false;
  if (array_key_exists('showIdle',$_REQUEST)) {
    $showIdle=true;
    $headerParameters.= i18n("labelShowIdle").'<br/>';
  }
  include "header.php";

  $accessRightRead=securityGetAccessRight('menuProject', 'read');

  $querySelect = '';
  $queryFrom='';
  $queryWhere='';
  $queryOrderBy='';
  $idTab=0;

  if ($showIdle) {
    $queryWhere ="1=1 ";
  }else{
    $queryWhere= $table . ".idle=0 ";
  }
  $queryWhere.= ($queryWhere=='')?'':' and ';
  $queryWhere.=getAccesRestrictionClause('Activity',$table);
  if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
	  $idProject = $_REQUEST['idProject'];
	  $idProject = Security::checkValidId($idProject);
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(true, $idProject) ;
  }
  // Remove Admin Projects : should not appear in Work Plan
  $queryWhere.= " and $table.idProject not in " . Project::getAdminitrativeProjectList() ;
  
  $querySelect .= $table . ".* ";
  $queryFrom .= $table;
  $queryOrderBy .= $table . ".wbsSortable ";

  // constitute query and execute
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query='select ' . $querySelect
       . ' from ' . $queryFrom
       . ' where ' . $queryWhere
       . ' order by ' . $queryOrderBy;
  $result=Sql::query($query);
//echo $query;
  $test=array();
  if (Sql::$lastQueryNbRows > 0) $test[]="OK";
  if (checkNoData($test))  if (!empty($cronnedScript)) goto end; else exit;

  if (Sql::$lastQueryNbRows > 0) {
    // Header
    echo '<table align="center">';
    echo '<TR>';
    echo '  <TD class="reportTableHeader" style="width:10px; border-right: 0px;"></TD>';
    echo '  <TD class="reportTableHeader" style="width:200px; border-left:0px; text-align: left;">' . i18n('colTask') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colValidated') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colAssigned') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colReal') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colLeft') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colReassessed') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:70px" nowrap>' . i18n('progress') . '</TD>' ;
    echo '</TR>';
    // Treat each line
    while ($line = Sql::fetchLine($result)) {
    	$line=array_change_key_case($line,CASE_LOWER);
      $validatedCost=$line['validatedcost'];
      $assignedCost=$line['assignedcost'];
      $plannedCost=$line['plannedcost'];
      $realCost=$line['realcost'];
      $leftCost=$line['leftcost'];
      $progress=' 0';
      if ($plannedCost>0) {
        $progress=round(100*$realCost/$plannedCost);
      } else {
        if ($line['done']) {
          $progress=100;
        }
      }
      // pGroup : is the tack a group one ?
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
      echo '<TR>';
      echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '">'.formatIcon($line['reftype'], 16).'</TD>';
      echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '" nowrap>' . $tab . htmlEncode($line['refname']) . '</TD>';
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($validatedCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($assignedCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($realCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($leftCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($plannedCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . percentFormatter($progress) . '</TD>' ;
      echo '</TR>';
    }
  }
  echo "</table>";

end:
  
?>