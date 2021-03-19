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
 * Client is the owner of a project.
 */ 
require_once('_securityCheck.php');
class MessageLegalFollowup extends SqlElement {

  public $id;
  public $name;
  public $idMessageLegal;
  public $idUser;
  public $firstViewDate;
  public $lastViewDate;
  public $acceptedDate;
  public $accepted;
  public $_noHistory=true;
  
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
  
  static function updateMessageLegalFollowup() {
    $idUser = getCurrentUserId();
    $messageLegal = new MessageLegal();
    $theDate = new DateTime();
    $currentDate = $theDate->format("Y-m-d H:i:s");
    $where = " idle=0 AND ( (startDate <= '$currentDate' AND endDate >= '$currentDate') OR (startDate is null  AND endDate >= '$currentDate') OR (startDate <= '$currentDate' AND endDate is null) OR  (startDate is null  AND endDate is null ))";
    $listMessageLegal = $messageLegal->getSqlElementsFromCriteria(null,null,$where);
    $messLegalFollowup = new MessageLegalFollowup();
    $tabIdListMessageLegalFollowup = SqlList::getListWithCrit('MessageLegalFollowup',array('idUser'=>$idUser),'idMessageLegal');
    foreach ($listMessageLegal as $mess){
      if (in_array($mess->id, $tabIdListMessageLegalFollowup)) {
        continue;
      }
      $messLegalFollowup = new MessageLegalFollowup();
      $messLegalFollowup->idUser = $idUser;
      $messLegalFollowup->name = $mess->name;
      $messLegalFollowup->idMessageLegal= $mess->id;
      $messLegalFollowup->save();
    }
  }
  
}
?>