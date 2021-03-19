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
 * Save a filter : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the filter info
if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];
if ($filterObjectClass!='Planning' and $filterObjectClass!='GlobalPlanning' and $filterObjectClass!='VersionsPlanning' and $filterObjectClass!='ResourcePlanning' ) Security::checkValidClass($filterObjectClass, 'filterObjectClass');

if (! array_key_exists('idFilter',$_REQUEST)) {
  throwError('idFilter parameter not found in REQUEST');
}
$idFilter=$_REQUEST['idFilter']; // validated to be numeric value in SqlElement base constructor.
Sql::beginTransaction();
$filter=new Filter($idFilter);
$name=$filter->name;
$filter->delete();

echo "<div id='saveFilterResult' style='z-index:9;position: absolute;left:50%;width:100%;margin-left:-50%;top:20px'>";
echo '<table width="100%"><tr><td align="center" >';
echo '<span class="messageOK" style="z-index:999;position:relative;top:7px;padding:10px 20px;white-space:nowrap" >' . i18n('colFilter') . " '" . htmlEncode($name) . "' " . i18n('resultDeleted') . ' (#'.htmlEncode($filter->id).')</span>';
echo '</td></tr></table>';
echo "</div>"; 

$flt=new Filter();
$crit=array('idUser'=> $user->id, 'refType'=>$filterObjectClass );
$orderByFilter = "sortOrder ASC";
$filterList=$flt->getSqlElementsFromCriteria($crit,false,null,$orderByFilter);;
htmlDisplayStoredFilter($filterList,$filterObjectClass);
Sql::commitTransaction();
?>