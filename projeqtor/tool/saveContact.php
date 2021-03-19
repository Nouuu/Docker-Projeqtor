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

/** ============================================================================
 * Save real work allocation.
 */

require_once "../tool/projeqtor.php";

$objectClass=((RequestHandler::isCodeSet('objectClass'))?RequestHandler::getValue('objectClass'):'');
$selectId=((RequestHandler::isCodeSet('listId'))?explode("_", RequestHandler::getValue('listId')):'');
$class=((RequestHandler::isCodeSet('class'))?RequestHandler::getValue('class'):'');
$newVal=((RequestHandler::isCodeSet('addVal'))?RequestHandler::getValue('addVal'):'');
$operation=((RequestHandler::isCodeSet('operation'))?RequestHandler::getValue('operation'):'');
$name='';
if($objectClass=='' or $selectId=='' or $operation==''){
  return;
}

$res= new $class ($newVal);
foreach ($selectId as $idCont){
  $obj= new $objectClass ($idCont);
  if($class=='Provider' and $obj->idProvider==$res->id or ($class=='Client' and $obj->idClient==$res->id)){
    if($name==''){
      $name.=$obj->name;
    }else{
        $name.=', '.$obj->name;
    }
    continue ;
  }
  
  // else if($class=='Client' and $obj->idClient!='' or ($class=='Provider' and $obj->idProvider!='')){
  //   echo '<span class="messageERROR" style="z-index:999;position:relative;top:7px">' . i18n('contact déja lié à un autre ') . '</span>';
  //   return;
  // }
  Sql::beginTransaction();
    if($operation=='add'){
      if($class!='' and $newVal!=''){
        if($class=='Provider'){
          $obj->idProvider=$newVal;
        }elseif ($class=='Client'){
          $obj->idClient=$newVal;
        }
      }else{
        return;
      }
    }elseif ($operation=='remove'){
      if($class!='' ){
        if($class=='Provider'){
          $obj->idProvider='';
        }elseif ($class=='Client'){
          $obj->idClient='';
        }
        else{
          return;
        }
      }
    }else{
      return ;
    }
  $result=$obj->save();
  displayLastOperationStatus($result);
}
if($name!=''){
  echo '<div class="messageINVALID" id="invalidMessageSave" >'.i18n('errorLinkedContact',array($name,$res->name)).'</div>';
  echo'<input type="hidden" id="lastSaveId" value="" />';
  echo '<input type="hidden" id="lastOperation" value="control" />';
  echo'<input type="hidden" id="lastOperationStatus" value="INVALID" />';
}

?>