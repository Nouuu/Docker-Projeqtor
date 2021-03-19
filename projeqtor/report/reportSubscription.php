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

$allTypes = false;

$paramObjectType = '';
if (array_key_exists('refType', $_REQUEST)) {
    $paramObjectType = trim($_REQUEST['refType']);
    //Security::checkValidId($paramObjectType);
}
if ($paramObjectType=='') {
  $allTypes=true;
}
$paramUser = '';
if (array_key_exists('idUser', $_REQUEST)) {
    $paramUser = trim($_REQUEST['idUser']);
    //Security::checkValidId($paramUser);
}

$headerParameters = "";
if ($paramUser != "") {
    $headerParameters .= i18n("colIdUser") . ' : ' . htmlEncode(SqlList::getNameFromId('idUser', $paramUser)) . '<br/>';
}

if ($paramObjectType != "") {
    $headerParameters .= i18n("colType") . ' : ' . htmlEncode(SqlList::getNameFromId('refType', $paramObjectType)) . '<br/>';
}

include "header.php";

if ($paramUser != "")
    $order = "refId ASC";
else
    $order = "idAffectable ASC, refId ASC";
$where ="";

if ($allTypes == true) {
    if ($paramUser != "") {
        $where .= "idAffectable = " . $paramUser;
    }
}
else {
    $where .= "refType = '" . $paramObjectType . "'";
    if ($paramUser != "") {
        $where .= " and idAffectable = " . $paramUser;
    }
}

$sub = new Subscription();
$count = 0;
$tcount = 0;
$names = array();
$users = array();
$types = array();
$typeobjs = array();
$list = $sub->getSqlElementsFromCriteria(null, false, $where, $order);

if (checkNoData($list)) if (!empty($cronnedScript)) goto end; else exit;
foreach ($list as $key => $data) {
    $user = new Resource($data->idAffectable);
    $object = new $data->refType($data->refId);
    $typeobjs[$count] = $data->refType;
    if (!in_array($data->refType, $types)) {
        $types[$tcount] = $data->refType;
        $tcount++;
    }
    $names[$count] = $object->name;
    $users[$count] = $user->name;
    $count++;
}
$count = 0;
$tcount = 0;
$rowspan = 0;

echo '<table width="95%" align="center">';
if ($allTypes == true)
    echo '<tr><td class="reportTableHeader" style="width:15%">' . i18n('colObjectType') . '</td>';
echo '<td class="reportTableHeader" style="width:55%">' .i18n('object') . '</td>';
echo '<td class="reportTableHeader" style="width:5%">' . i18n('colObjectId') . '</td>';
echo '<td class="reportTableHeader" style="width:20%">' .i18n('User') . '</td>';
echo '</tr>';

foreach ($types as $type) {
    $rowspan = 0;
    foreach ($typeobjs as $typeObject) {
        if ($typeObject == $type)
            $rowspan++;
    }
    echo '<tr>';
    if ($allTypes == true)
        echo '<td class="reportTableData" rowspan="' . $rowspan . '">' . $type   . '</td>';
    foreach ($list as $sub) {
        if ($sub->refType == $type) {
            $user = new Resource($sub->idAffectable);
            $obj = new $sub->refType($sub->refId);
            echo '<td class="reportTableDataLeft">' . $obj->name . '</td>';
            echo '<td class="reportTableData">' . '#' . $obj->id . '</td>';
            echo '<td class="reportTableData">' . $user->name . '</td>';
            echo '</tr><tr>';
        }
    }
    echo '</tr>';
}
echo '</table>';

end:

?>