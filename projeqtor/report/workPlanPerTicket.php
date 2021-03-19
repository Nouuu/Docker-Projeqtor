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

  $objectClass='workElement';
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
  
 $test=array();
  if (Sql::$lastQueryNbRows > 0) $test[]="OK";
  if (checkNoData($test))  if (!empty($cronnedScript)) goto end; else exit;

  if (Sql::$lastQueryNbRows > 0) {
    // Header
    echo '<table align="center">';
    echo '<TR>';
    echo '  <TD class="reportTableHeader" style="width:10px; border-right: 0px;"></TD>';
    echo '  <TD class="reportTableHeader" style="width:200px; border-left:0px; text-align: left;">' . i18n('colTask') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width:50px" nowrap>' . i18n('colPlanned') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:50px" nowrap>' . i18n('colReal') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:50px" nowrap>' . i18n('colLeft') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:70px" nowrap>' . i18n('progress') . '</TD>' ;
    echo '</TR>';
  }

  $arrayTest = array();
  $arrayWe = array();
  $result=array();
  $prj = new Project();
  $queryWhere1='';
  if ($showIdle) {
     $queryWhere1.="1=1 ";
  }else{
     $queryWhere1.="project.idle=0 ";
  }
  $queryWhere1.= ($queryWhere1=='')?'':' and ';
  $queryWhere1.=getAccesRestrictionClause('Project',"project");
  if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
	  $paramProject=trim($_REQUEST['idProject']);
	  Security::checkValidId($paramProject);
    $queryWhere1.= ($queryWhere1=='')?'':' and ';
    $prj=new Project();
    $prjTable=$prj->getDatabaseTableName();
    $queryWhere1.=  "$prjTable.id in " . getVisibleProjectsList(true, $paramProject) ;
  }
  $order = "sortOrder asc";
  $prjLst = $prj->getSqlElementsFromCriteria(null,false,$queryWhere1,$order);
  //$arrayP[]=$prjLst;
  $level = "";
  foreach ($prjLst as $lstPrj){
    $we = new WorkElement();
    $crit = array("idProject" => $lstPrj->id);  
    $weLst = $we->getSqlElementsFromCriteria($crit,false);
    $level=(strlen($lstPrj->sortOrder)+1)/4;
    $arrayP = array(
      "refId" => $lstPrj->id,
      "refName" => $lstPrj->name,
      "refType" => "Project",
      "parent" => $lstPrj->idProject,
      "level" => $level,
      "plannedWork" => "0",
      "realWork" => "0",
      "leftWork" => "0",
    );
    $result["Project#$lstPrj->id"]=$arrayP;

    foreach($weLst as $lstWe){
        $arrayWE = array(
        "refId" => $lstWe->id,
        "refName" =>$lstWe->refName,
        "refType" => "Ticket",
        "parent" => $lstWe->idProject,
        "plannedWork" => $lstWe->plannedWork,
        "realWork" => $lstWe->realWork,
        "leftWork" => $lstWe->leftWork,
        "level" => $level+1,
        );
        addTotal($lstPrj->id,null, null, $lstWe->plannedWork); 
        addTotal($lstPrj->id,null, $lstWe->leftWork, null);
        addTotal($lstPrj->id,$lstWe->realWork, null, null );
        $result["WE#$lstWe->id"]=$arrayWE;
    }
  }
  foreach ($result as $lstResult=>$resultt){
    $tab="";
    $plannedWork = $resultt["plannedWork"];
    $realWork = $resultt['realWork'];
    $leftWork = $resultt['leftWork'];
    $progress = 0;
    if ( ($realWork + $leftWork) > 0) {
      $progress = $realWork / ($realWork + $leftWork) * 100;
    }
    for ($i=1;$i<$resultt["level"];$i++) {
      $tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
    }
    $compStyle="";
    if($resultt['refType'] == "Project"){
      $compStyle="font-weight: bold; background: #E8E8E8 ;";
    } else {
      $compStyle="font-weight: light; font-style:italic;";
    }
    echo '<TR>';
    echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '">'.formatIcon($resultt["refType"], 16).'</TD>';
    echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '" nowrap>'. $tab . htmlEncode($resultt["refName"]) . '</TD>';
    echo '  <TD class="reportTableData" style="' . $compStyle . '">' . Work::displayWorkWithUnit($plannedWork)  . '</TD>' ;
    echo '  <TD class="reportTableData" style="' . $compStyle . '">' . Work::displayWorkWithUnit($realWork) . '</TD>' ;
    echo '  <TD class="reportTableData" style="' . $compStyle . '">' . Work::displayWorkWithUnit($leftWork) . '</TD>' ;
    echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . percentFormatter(round($progress)) . '</TD>' ;
    echo '</TR>';
  }
  echo "</table>";
  
function addTotal($idProject,$realWork,$leftWork,$plannedWork){
  global $result;
  if($plannedWork) $result["Project#$idProject"]['plannedWork'] += $plannedWork;
  if($leftWork) $result["Project#$idProject"]['leftWork'] += $leftWork;
  if($realWork) $result["Project#$idProject"]['realWork'] += $realWork;
  if($result["Project#$idProject"]['parent']) addTotal($result["Project#$idProject"]['parent'],$realWork,$leftWork,$plannedWork);
}

end:

?>