<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : 
 *  => g.miraillet : Fix #1502
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

// LEAVE SYSTEM

/** ============================================================================
 * Save the Leaves System Habilitations.
 */
require_once "../tool/projeqtor.php";

// TODO (SECURITY) : enforce security (habilitation to change parameters, lock fixed params, ...)
$status="NO_CHANGE";
$errors="";

$forceRefreshMenu=false;

Sql::beginTransaction();

$leavesSystemHabilitation = getLeavesSystemHabilitation();
$newLeavesSystemHabilitation = array();
$menusList = getLeavesSystemMenu();

// Unset habilitation for menus that are'nt 
//      - item or object
//      - in menu List
$tmp = $leavesSystemHabilitation;
foreach($tmp as $key => $hab) {
    $isMenu = false;
    $hasMenu = false;
    foreach($menusList as $menu) {
        if ($hab->menuName == $menu->name) {
            if ($menu->type =="menu") {$isMenu = true;} else {$hasMenu = true;}
            break;
        }
    }
    if (!$hasMenu or $isMenu) {
        unset($leavesSystemHabilitation[$key]);        
    }
}

// Init new Habilitation
foreach($leavesSystemHabilitation as $key => $hab) {
    $newLeavesSystemHabilitation[$key] = new LeavesSystemHabilitation(null,true);
    $newLeavesSystemHabilitation[$key]->id = $hab->id;
    $newLeavesSystemHabilitation[$key]->menuName = $hab->menuName;
    $newLeavesSystemHabilitation[$key]->viewAccess = null;
    $newLeavesSystemHabilitation[$key]->readAccess = null;
    $newLeavesSystemHabilitation[$key]->createAccess = null;
    $newLeavesSystemHabilitation[$key]->updateAccess = null;
    $newLeavesSystemHabilitation[$key]->deleteAccess = null;    
}

foreach($_REQUEST as $fld => $val) {
    // Only for habilitation
    if (strpos($fld,"menu")!==false) {
        $habilitationInfos = explode("_", $fld);
        $menuName = $habilitationInfos[0];
        $access = $habilitationInfos[1];
        $type = $habilitationInfos[2];
        $id = $habilitationInfos[3];
        foreach($newLeavesSystemHabilitation as $key => $hab) {
//            if ($hab->id == $id and $val=='yes') {
            if ($hab->id == $id) {
                if (trim($newLeavesSystemHabilitation[$key]->$type)=="") {
                    $newLeavesSystemHabilitation[$key]->$type = $access;                    
                } else {
                    $newLeavesSystemHabilitation[$key]->$type .= $access;
                }
                break;
            }
        }
    }
}

// Compare old and new Habilitation
foreach($newLeavesSystemHabilitation as $hab) {
    foreach($leavesSystemHabilitation as $oldHab) {
        // Same id
        if ($hab->id == $oldHab->id) {
            // Access change
            if ($hab->viewAccess != $oldHab->viewAccess or
                $hab->readAccess != $oldHab->readAccess or
                $hab->createAccess != $oldHab->createAccess or
                $hab->updateAccess != $oldHab->updateAccess or
                $hab->deleteAccess != $oldHab->deleteAccess
                ) {
                // Save Changes
                $result = $hab->save();
                $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
                $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
                if ($isSaveNO_CHANGE===false) {
                  if ($isSaveOK===false) {
                    $status="ERROR";
                    $errors=$result;
                  } else if ($status=="NO_CHANGE") {
                    $status="OK";
                  }
                }
            }
            break;
        }
    }
}

if ($status=='ERROR') {
    Sql::rollbackTransaction();
    echo '<div class="messageERROR" >' . $errors . '</div>';
} else if ($status=='WARNING'){ 
    // unset sessionValue = leavesSystemHabilitation
    unsetSessionValue("leavesSystemHabilitation");

    $forceRefreshMenu='leavesSystemHabilitation';
    
    Sql::commitTransaction();
    echo '<div class="messageWARNING" >' . i18n('messageLeavesSystemHabilitationSaved') . ' - ' .$errors .'</div>';
    $status='INVALID';
} else if ($status=='OK'){ 
    // unset sessionValue = leavesSystemHabilitation
    unsetSessionValue("leavesSystemHabilitation");

    $forceRefreshMenu='leavesSystemHabilitation';

    Sql::commitTransaction();
    echo '<div class="messageOK" >' . i18n('messageLeavesSystemHabilitationSaved') . '</div>';
} else {
    Sql::rollbackTransaction();
    echo '<div class="messageNO_CHANGE" >' . i18n('messageLeavesSystemHabilitationSaved') . '</div>';
}
echo '<input type="hidden" id="forceRefreshMenu" value="'.$forceRefreshMenu.'" />';
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';

