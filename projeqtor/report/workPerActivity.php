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

  require_once "../tool/projeqtor.php";
  include_once('../tool/formatter.php');
//echo "workPerActivity.php";
// Creation of object "table"
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
  $showIdle=false;
  
  if (array_key_exists('showIdle',$_REQUEST)) {
    $showIdle=true;
    $headerParameters.= i18n("labelShowIdle").'<br/>';
  }
  include "header.php";


  $accessRightRead=securityGetAccessRight('menuProject', 'read');

  // Preparation of query ///
  $querySelect = '';
  $queryFrom='';
  $queryWhere='';
  $queryOrderBy='';
  $idTab=0;
  if ($showIdle) {
    $queryWhere="1=1 ";
  }else{
    $queryWhere= $table . ".idle=0 ";
  }
//  if (! array_key_exists('idle',$_REQUEST) ) {
//    $queryWhere= $table . ".idle=0 ";
//  }

  // Where clause
  $queryWhere.= ($queryWhere=='')?'':' and ';
  $queryWhere.=getAccesRestrictionClause('Activity',$table,false,true,true);
  if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
	  $paramProject=trim($_REQUEST['idProject']);
	  Security::checkValidId($paramProject);
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(true, $paramProject) ;
  }

  // Select clause
  $querySelect .= $table . ".* ";

  // From clause
  $queryFrom .= $table;

  // Order By clause
  $queryOrderBy .= $table . ".wbsSortable ";

  // build of query
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query='select ' . $querySelect
       . ' from ' . $queryFrom
       . ' where ' . $queryWhere
       . ' order by ' . $queryOrderBy;
  $result=Sql::query($query);

  // Test execution of query
  $test=array();
  if (Sql::$lastQueryNbRows > 0) $test[]="OK";
  if (checkNoData($test))  if (!empty($cronnedScript)) goto end; else exit;

  // Verify query result is not empty
  if (Sql::$lastQueryNbRows > 0) {

    // Headers of columns
    echo '<table>';
    echo '<TR>';
    echo '  <TD class="reportTableHeader" style="width:10px; border-right: 0px;"></TD>';
    echo '  <TD class="reportTableHeader" style="width:200px; border-left:0px; text-align: left;">' . i18n('colTask') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colValidated') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colAssigned') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colReal') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colLeft') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colReassessed') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:70px" nowrap>' . i18n('progress') . '</TD>' ;
	echo '  <TD class="reportTableHeader" style="width:70px" nowrap>Indicator</TD>' ;
    echo '</TR>';

    // Treat each line of result
    while ($line = Sql::fetchLine($result)) {
      $line=array_change_key_case($line,CASE_LOWER);
  	  // Store result elements
      $validatedWork=round($line['validatedwork'],2);
      $assignedWork=round($line['assignedwork'],2);
      $plannedWork=round($line['plannedwork'],2);
      $realWork=round($line['realwork'],2);
      $leftWork=round($line['leftwork'],2);
      $progress=' 0';

      // Compute progress value
      if ($plannedWork>0) {
        $progress=round(100*$realWork/$plannedWork);
      } else {
        if ($line['done']) {
          $progress=100;
        }
      }

      // Check if activity has a parent one
          $pGroup=($line['elementary']=='0')?1:0;
      $compStyle=""; // complementary css style
	  $compStyleWarning=""; // complementary css style
	$indicator=""; // indicator for smiley

	  // Compare planned and assigned work
	  // Depending on result, display correspondong smiley
	  $indicator="neutral";
	  if($plannedWork >$assignedWork) {
			$indicator="unhappy";
		  if( $pGroup) {
			$rowType = "group";
			$compStyle="font-weight: bold; background: #E8E8E8 ;";
		  } else if( $line['reftype']=='Milestone'){
			$rowType  = "mile";
			$compStyle="font-weight: light; font-style:italic;";
		  } else {
			$rowType  = "row";
			$compStyleWarning="color: #FF1C32 ;";
		  }
	  }	 else if($plannedWork<$assignedWork) {
			$indicator="happy";
			if( $pGroup) {
			$rowType = "group";
			$compStyle="font-weight: bold; background: #E8E8E8 ;";
		  } else if( $line['reftype']=='Milestone'){
			$rowType  = "mile";
			$compStyle="font-weight: light; font-style:italic;";
		  } else {
			$rowType  = "row";
			$compStyleWarning="color: #65FF2D ;";
		  }
		} else if($plannedWork == $assignedWork) {
			$indicator="neutral";
			if( $pGroup) {
			$rowType = "group";
			$compStyle="font-weight: bold; background: #E8E8E8 ;";
		  } else if( $line['reftype']=='Milestone'){
			$rowType  = "mile";
			$compStyle="font-weight: light; font-style:italic;";
		  } else {
			$rowType  = "row";
		  }
		}

	// Display the line
      $wbs=$line['wbssortable'];
      $level=(strlen($wbs)+1)/4;
      $tab="";
      for ($i=1;$i<$level;$i++) {
        $tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
      }
      echo '<TR>';
      echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '"><img style="width:16px" src="../view/css/images/icon' . $line['reftype'] . '16.png" /></TD>';
      echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '" nowrap>' . $tab . htmlEncode($line['refname']) . '</TD>';
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . Work::displayWorkWithUnit($validatedWork) . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle."".$compStyleWarning . '">' . Work::displayWorkWithUnit($assignedWork) . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . Work::displayWorkWithUnit($realWork) . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle."".$compStyleWarning  . '">' . Work::displayWorkWithUnit($leftWork) . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle."".$compStyleWarning  . '">' . Work::displayWorkWithUnit($plannedWork) . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . percentFormatter($progress) . '</TD>' ;
	  echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '"><img style="width:16px" src="../view/css/images/indicator_' . $indicator . '.png" /></TD>';
      echo '</TR>';
    }
  }
  echo "</table>";
  
end:
  
?>