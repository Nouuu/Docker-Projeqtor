<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

// ADD BY Marc TABARY - 2017-02-23 - SELECT LIST FOR OBJECTS LINKED BY ID TO MAIN OBJECT

/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListObjectLinkedByIdToMainObject.php');

if (! array_key_exists('linkObjectClassName',$_REQUEST)) {
  throwError('linkObjectClassName parameter not found in REQUEST');
}

if (! array_key_exists('mainObjectClass',$_REQUEST)) {
  throwError('mainObjectClass parameter not found in REQUEST');
}

$linkObjectClassName = $_REQUEST['linkObjectClassName'];
$mainObjectClass = $_REQUEST['mainObjectClass'];

$list =  getUserVisibleObjectsList($linkObjectClassName, true, 'List', 'id'.$mainObjectClass);

?>
<select id="linkedObjectId" size="14" name="linkedObjectId[]" multiple
onchange="selectLinkObjectItem();"  ondblclick="saveLinkObject();"
class="selectList" >
 <?php
 foreach ($list as $key => $name) {
   echo "<option value='$key'>#".htmlEncode($key)." - ".htmlEncode($name)."</option>";
 }
 ?>
</select>
<!-- END ADD BY Marc TABARY - 2017-02-23 - SELECT LIST FOR OBJECTS LINKED BY ID TO MAIN OBJECT
