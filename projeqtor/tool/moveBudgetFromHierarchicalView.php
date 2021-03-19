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
 * Move task (from before to)
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/moveBudgetFromHierarchicalView.php');
$idFrom = RequestHandler::getValue('idFrom');
$idTo = RequestHandler::getValue('idTo');

$mode=RequestHandler::getValue('mode');
if ($mode!='before' and $mode!='after') {
  $mode='before ?';
}

$idSource = explode('_',$idFrom)[1];
$idTarget = explode('_',$idTo)[1];
$source = new Budget($idSource);
$target = new Budget($idTarget);


// UPDATE PARENTS (recursively)
if ($idTarget) {
  if ($source->idBudget != $target->idBudget) {
    $oldParent=$source->idBudget;
  }
  $source->idBudget = $target->idBudget;
  if ($mode=='after') {
    $source->bbsSortable =  $target->bbsSortable.'.1';
    $source->bbs =  $target->bbs.'.1';
  } else {
    $split=explode('.',$target->bbs);
    $last=$split[count($split)-1];
    $new=intval($last)-1;
    if (count($split)==1) {
      $source->bbs =  $new.'.1';
    } else {
      $newBbsRoot=substr($target->bbs,0,strrpos($target->bbs, '.'));
      $source->bbs =  $newBbsRoot.'.'.$new.'.1';
    }
    $source->bbsSortable =  formatSortableWbs($source->bbs);;
  }
  $source->save();
  $parent=new Budget($target->idBudget);
  $parent->regenerateBbsLevel();
  if (isset($oldParent) and $oldParent) {
    $parent=new Budget($oldParent);
    $parent->regenerateBbsLevel();
  }
}
?>