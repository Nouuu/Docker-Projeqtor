<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Philippe GALLAIS - philippe.gallais@gmail.com (november 2016)
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

/***  In order to add the report :
 
INSERT INTO `report` (`id`, `name`, `idReportCategory`, `file`, `sortOrder`) VALUES (99, 'reportPlanProjectPerRessourceAndMonth', 2, 'reportPlanProjectPerRessourceAndMonth.php', 10)

INSERT INTO `habilitationreport` (`idProfile`,`idReport`,`allowAccess`) VALUES (1,99,1),(2,99,1),(3,99,1),(4,99,1)
(il est aussi possible de modifier les habilitations à la main dans Paramètres / Habilitation / Accès aux rapports)

INSERT INTO `reportparameter` (`idReport`, `name`, `paramType`, `sortOrder`, `defaultValue`) VALUES (99,'idProject','projectList',10,'currentProject'), (99,'idTeam','teamList',20,null), (99,'month','month',40,'currentMonth')

Pour les libellés internationaux : ajouter une ligne dans  tool\i18n\nls\fr\lang.js pour "reportPlanProjectPerRessourceAndMonth'
 
 Forum : https://www.projeqtor.org/en/forum/3-propose-evolutions/7495-new-report-work-plan-per-week-and-per-ressource

 
 ***/
 
 
 
include_once '../tool/projeqtor.php';

$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
}
$showAdminProj=trim(RequestHandler::getValue('showAdminProj'));
$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
	$paramYear=$_REQUEST['yearSpinner'];
	$paramYear=Security::checkValidYear($paramYear);
} 

$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
	$paramMonth=$_REQUEST['monthSpinner'];
  $paramMonth=Security::checkValidMonth($paramMonth);
};
$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
	$paramWeek=$_REQUEST['weekSpinner'];
	$paramWeek=Security::checkValidWeek($paramWeek);
};

$idOrganization = trim(RequestHandler::getId('idOrganization'));
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
  Security::checkValidId($paramTeam);
}
$scale='month';
if (array_key_exists('scale',$_REQUEST)) {
  $scale=$_REQUEST['scale'];
  $scale=Security::checkValidPeriodScale($scale);
}
$periodValue='';
if (array_key_exists('periodValue',$_REQUEST))
{
	$periodValue=$_REQUEST['periodValue'];
	$periodValue=Security::checkValidPeriod($periodValue);
}
$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ( $paramTeam) {
  $headerParameters.= i18n("team") . ' : ' . SqlList::getNameFromId('Team', $paramTeam) . '<br/>';
}
if ($paramYear) {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
}
if ($paramMonth) {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}

include "header.php";

$accessRightRead=securityGetAccessRight('menuProject', 'read');
  
$user=getSessionUser();
$queryWhere=getAccesRestrictionClause('Activity','t1',false,true,true);

if ($idProject!='') {
  $queryWhere.=  " and t1.idProject in " . getVisibleProjectsList(true, $idProject) ;
}

if ($paramMonth) {
  $queryWhere.=  " and month=".Sql::str($periodValue);
}
// Remove Admin Projects : should not appear in Work Plan
if($showAdminProj !='on'){
  $queryWhere.= " and t1.idProject not in " . Project::getAdminitrativeProjectList() ;
}

if ($paramYear) {
	$queryWhere.=  " and year=".Sql::str($paramYear);
}
if ($periodValue) {
  $queryWhere.=  " and month>=".Sql::str($periodValue);
}

if ($paramTeam) {
	$res=new Resource();
	$lstRes=$res->getSqlElementsFromCriteria(array('idTeam'=>$paramTeam));
	$inClause='(0';
	foreach ($lstRes as $res) {
		$inClause.=','.$res->id;
	}
	$inClause.=')';
	$queryWhere.= " and t1.idResource in ".$inClause;
}

if ($idOrganization ) {
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  $inClause='(0';
  foreach ($listResOrg as $res) {
    $inClause.=','.$res;
  }
  $inClause.=')';
  $queryWhere.= " and t1.idResource in ".$inClause;
}

$querySelect= 'select t1.idResource, month, sum(work) as sumWork, ' . $scale . ' as scale , t1.idProject as idproject '; 

$queryGroupBy = 't1.idResource,' .$scale . ', t1.idProject'; 
// constitute query and execute

$tab=array();
$start="";
$end="";
$prj=new Project();
$prjTable=$prj->getDatabaseTableName();
for ($i=1;$i<=2;$i++) {
  $obj=($i==1)?new Work():new PlannedWork();
  $var=($i==1)?'real':'plan';
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query=$querySelect 
     . ' from ' . $obj->getDatabaseTableName().' t1, '.$prjTable.' t2 '
     . ' where ' . $queryWhere." and t1.idProject=t2.id "
     . ' group by ' . $queryGroupBy
     . ' order by t1.idResource asc '; 
  $result=Sql::query($query);
  //echo $query."<br/><br/>";
  
  
  while ($line = Sql::fetchLine($result)) {
  	$line=array_change_key_case($line,CASE_LOWER);
    $date=$line['scale'];
    $proj=$line['idproject'];
    $work=$line['sumwork'];     
    //$work=round($line['sumwork'],2);
    $ress=$line['idresource'];	

	$val = 0;
	if (array_key_exists ($proj, $tab)) {
	  if (array_key_exists ($ress, $tab[$proj])) {
		if (array_key_exists ($date, $tab[$proj][$ress]) ){
			$val = $tab[$proj][$ress][$date];
		}
	  }
	}
    $tab[$proj][$ress][$date]= $work+$val;
	//echo "on écrit $work dans tab [$proj][$ress][$date] qui prend pour valeur".$tab[$proj][$ress][$date]."<br/>";
	
    if ($start=="" or $start>$date) {
      $start=$date;
    }
    if ($end=="" or $end<$date) {
      $end=$date;
    }
  }
}
if (checkNoData($tab)) if (!empty($cronnedScript)) goto end; else exit;

$arrDates=array();
$arrYear=array();
$date=$start;
while ($date<=$end) {
  $arrDates[]=$date;
  $year=substr($date,0,4);
  if (! array_key_exists($year,$arrYear)) {
    $arrYear[$year]=0;
  }
  $arrYear[$year]+=1;

    $day=substr($date,0,4) . '-' . substr($date,4,2) . '-01';
    $next=addMonthsToDate($day,1);
    $date=substr($next,0,4) . substr($next,5,2);
  
}

  

// Header

echo "<table width='95%' align='center'><tr>";
echo '<td>';

echo '<table width="100%" align="left"><tr>';
echo "<td>&nbsp;</td>";
echo "<td class='legend'>" . Work::displayWorkUnit() . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr></table>";


echo "<br/>";
echo '<table width="100%" align="left">';

// Affichage ligne des années
echo '<tr rowspan="2">';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
foreach ($arrYear as $year=>$nb) {
  echo '<td class="reportTableHeader" colspan="' . $nb . '">' . $year . '</td>';
}
echo '<td class="reportTableHeader" rowspan="2" style="width:40px;">' . i18n('sum') . '</td>';
echo '</tr>';

// Affichage ligne des mois
echo '<tr>';
$arrSum=array();
foreach ($arrDates as $date) {
  echo '<td class="reportTableColumnHeader" >';
  echo substr($date,4,2); 
  echo '</td>';
  $arrSum[$date]=0;
} 
echo '</tr>';


$sumProj=array();
$sumProjUnit=array();

// parcours des projets

foreach($tab as $proj=>$projet) {
  $sumProj[$proj]=array();
  $sumProjUnit[$proj]=array();

  echo '<tr><td class="reportTableLineHeader" style="width:200px;" rowspan="'.count($tab[$proj]).'">' . htmlEncode(SqlList::getNameFromId('Project',$proj)) . '</td>'; 
  $firstResource=true;
  foreach($tab[$proj] as $resource=>$ressource) { 
    if (!$firstResource) echo '<tr>';
    echo '<td class="reportTableLineHeader" style="width:200px;">' . htmlEncode(SqlList::getNameFromId('Affectable',$resource)) . '</td>';
    
    $sum=0;
    foreach($arrDates as $date) {
      $val=0;
      if (array_key_exists ($date, $tab[$proj][$resource]) ) {
        $val=$tab[$proj][$resource][$date];
      }
      echo '<td class="reportTableData">';
      echo Work::displayWork($val,2);
      $sum+=$val;
      $arrSum[$date]+=$val;
      echo '</td>';
    } // fin du parcours des mois
    echo '<td class="reportTableColumnHeader">';
    echo Work::displayWork($sum,2);
    echo '</td>';
   
    echo '</tr>';
    $firstResource=false;
    } // fin des ressources
  
  } // fin des projets


echo "<tr><td>&nbsp;</td></tr>";
echo '<tr><td class="reportTableHeader" style="width:40px;" colspan =2>' . i18n('sum') . '</td>';

$sum=0;
$cumul=array();
$cumulUnit=array();
foreach ($arrSum as $date=>$val) {
  echo '<td class="reportTableHeader" >' . Work::displayWork($val) . '</td>';
  $sum+=$val;
  $cumul[$date]=$sum;
  $cumulUnit[$date]=Work::displayWork($sum);
}


echo '<td class="reportTableHeader">' . Work::displayWork($sum) . '</td>';
echo '</tr>';

echo '</table>';


echo '</td></tr></table>';

end:

?>