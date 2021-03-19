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

/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once('_securityCheck.php');
class Habilitation extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProfile;
  public $idMenu;
  public $allowAccess;
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Execute specific query to dispatch updates so that if a sub-menu is activates
   * its main menu is also activated.
   * Also dispatch to unactivate main parameter if no-submenu is activated
   * @return void
   */
  static function correctUpdates() {
    $habiObj=new Habilitation();
    $menuObj=new Menu();
    $profObj=new Profile();
    
    Sql::$maintenanceMode=true;
  	$query="insert into " . $habiObj->getDatabaseTableName() . " (idProfile, idMenu, allowAccess)";
    $query.=" SELECT profile.id, menu.id, 0";
    $query.=" FROM " . $profObj->getDatabaseTableName() . " profile, " . $menuObj->getDatabaseTableName() . " menu";
    $query.=" WHERE (profile.id, menu.id) not in (select idProfile, idMenu from " . $habiObj->getDatabaseTableName() . ")";
  	$result=Sql::query($query);
    // Set Main menu to accessible if one of sub-menu is available
    $query="select distinct h.idProfile profile, m.idMenu menu from " . $habiObj->getDatabaseTableName() . " h," .  $menuObj->getDatabaseTableName() . " m";
    $query.=" where h.idMenu = m.id and h.allowAccess=1 and m.idMenu<>0 and (m.idle=0 or m.type='menu')";
    $result=Sql::query($query);
    $critList="";
    $critListInsert="";
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine($result);
      while ($line) {
        $critList.=($critList=='')?'(':',';
        $critListInsert.=($critListInsert=='')?'(':',';
        $critList.="('" . $line['menu'] . "', '" . $line['profile'] . "')";
        $critListInsert.="('" . $line['menu'] . "', '" . $line['profile'] . "')";
        $line = Sql::fetchLine($result);
      }
      $critList.=')';
      $query='update ' . $habiObj->getDatabaseTableName() . ' set allowAccess=1 where (idMenu,idProfile) in ' . $critList;
      $result=Sql::query($query);
    }
    
    // Set Main menu to not accessible if none of sub-menu is available
    $query="SELECT h.idProfile as profile, m.idMenu as menu";
    $query.=" FROM " . $habiObj->getDatabaseTableName(). " h , " . $menuObj->getDatabaseTableName() . " m ";
    $query.=" WHERE h.idMenu = m.id and m.idle=0";
    $query.=" GROUP BY h.idProfile, m.idMenu";
    $query.=" HAVING m.idMenu<>0 and Sum(h.allowAccess) = 0";
    $result=Sql::query($query);
    $critList="((0,0)";
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine($result);
      while ($line) {
        $critList.=',';
        $critList.="('" . $line['menu'] . "', '" . $line['profile'] . "')";
        $line = Sql::fetchLine($result);
      }
      $critList.=')';
      $query='update ' . $habiObj->getDatabaseTableName() . ' set allowAccess=0 where (idMenu,idProfile) in ' . $critList;
      Sql::query($query);
    }    
    Sql::$maintenanceMode=false;    
  }

}
?>