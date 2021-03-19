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

/** ===========================================================================
 * Acknowledge an operation
 */
if (!isset($user)) {
  $user=getSessionUser();
}
if ( ! isset($specific) or ! $specific) {
  errorLog("drawResourceListForSpecificAccess.php : specific variable not set");
  $specific="null"; // Avoid error
}
$table=array();
if (! $user->isResource) {
  $table[0]=' ';
}
if (!isset($includePool)) $includePool=false;
$table = getListForSpecificRights($specific,$includePool);
$selectedProject=getSessionValue('project');
if ($selectedProject and $selectedProject!='*' and (isset($limitResourceByProj) and $limitResourceByProj=='on') ) {
  $lstTopPrj=array();
  $sub=array();
  if(! is_array($selectedProject)) $selectedProject=explode(',',$selectedProject);
  $restrictTableProjectSelected=array();
  foreach ($selectedProject as $idProj){
    $prj = new Project ( $idProj, true );
    $lstTopSelectedPrj = $prj->getTopProjectList ( true );
    foreach ($lstTopSelectedPrj as $idProject){
      $lstTopPrj[$idProject]=$idProject;
    }
    $subProj = $prj->getRecursiveSubProjectsFlatList ();
    foreach ($subProj as $id=>$name){
      $sub[$id]=$name;
    }
  }
	$in=transformValueListIntoInClause(array_merge($lstTopPrj,array_keys($sub)));
	$crit='idProject in ' . $in;
	$aff=new Affectation();
	$lstAff=$aff->getSqlElementsFromCriteria(null, false, $crit, null, true);
	foreach ($lstAff as $id=>$aff) {
			$restrictTableProjectSelected[$aff->idResource]=$aff->idResource;
	}
}

$restrictArrayVisibility = getUserVisibleResourcesList(true,'List',null,$includePool);
foreach ($table as $idR=>$nameR) {
  if (isset($restrictTableProjectSelected) and !isset($restrictTableProjectSelected[$idR])) {
    unset($table[$idR]);
    continue;
  }
  if (!isset($restrictArrayVisibility[$idR])) {
    unset($table[$idR]);
    continue;
  }
}

if (!isset($table[$user->id])) {
  $table[$user->id]=$user->name;
}
foreach($table as $key => $val) {
  echo '<option value="' . $key . '"';
  if ( $key==$user->id and ! isset($specificDoNotInitialize)) { echo ' SELECTED '; }
  echo '>' . $val . '</option>';
}
?>
