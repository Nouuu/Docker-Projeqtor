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
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveWorkUnit.php');

$mode = RequestHandler::getValue('mode');
$idCatalog= RequestHandler::getId('idCatalog');
$WUReference=RequestHandler::getValue('WUReferences');
$WUDescription=RequestHandler::getValue('WUDescriptions');
$WUIncoming=RequestHandler::getValue('WUIncomings');
$WULivrable=RequestHandler::getValue('WULivrables');
$ValidityDateWU=RequestHandler::getValue('ValidityDateWU');
$idWorkUnit = RequestHandler::getId('idWorkUnit');
Sql::beginTransaction();
$result = "";

if($mode == 'edit'){
  $wu = new WorkUnit($idWorkUnit);
  $actPl = new ActivityPlanningElement();
  $countActPL = $actPl->countSqlElementsFromCriteria(array('idWorkUnit'=>$idWorkUnit));
  //$res='<b>Contrôles invalides.</b><br/><br/>'.i18n('workUnitIsUseByActivity').'<input type="hidden" id="lastOperationStatus" value="INVALID" /><input type="hidden" id="lastSaveId" value="" /><input type="hidden" id="lastOperation" value="control" />';
  $wu->idCatalogUO = $idCatalog;
  $wu->reference = $WUReference;
  $wu->description = $WUDescription;
  $wu->entering = $WUIncoming;
  $wu->deliverable = $WULivrable;
  $wu->validityDate = $ValidityDateWU;
  $catalog = new CatalogUO($idCatalog);
  $wu->idProject = $catalog->idProject;
  $res = $wu->save();
  $result = getLastOperationStatus($res);
  $complexity = new Complexity();
  $listComplexity = $complexity->getSqlElementsFromCriteria(array('idCatalogUO'=>$idCatalog));
  foreach ($listComplexity as $comp){
    $charge = RequestHandler::getNumeric('charge'.$comp->id);
    $charge = Work::convertWork($charge);
    if($charge==0)$charge=null;
    $price = RequestHandler::getNumeric('price'.$comp->id);
    $duration = RequestHandler::getNumeric('duration'.$comp->id);
    $compVal = SqlElement::getSingleSqlElementFromCriteria('ComplexityValues', array('idCatalogUO'=>$idCatalog,'idComplexity'=>$comp->id,'idWorkUnit'=>$idWorkUnit));
     if(!$compVal->id){
        if( !$charge and !$price and !$duration)continue;
        $compValue = new ComplexityValues();
        $compValue->idCatalogUO = $idCatalog;
        $compValue->idComplexity = $comp->id;
        $compValue->idWorkUnit = $wu->id;
        $charge = Work::convertWork($charge);
        $compValue->charge = $charge;
        $compValue->price = $price;
        $compValue->duration = $duration;
        $compValue->save();
     }else{
       $compValue = new ComplexityValues($compVal->id);
        if( !$charge and !$price and !$duration){
          $compValue->delete();
        }else{
          $compValue->charge = $charge;
          $compValue->price = $price;
          $compValue->duration = $duration;
          $compValue->save();
        }
     }
      if($result == "NO_CHANGE"){
        $res = i18n('WorkUnit').' #'.$idWorkUnit.' '.i18n("resultUpdated").'<input type="hidden" id="lastSaveId" value="'.$idWorkUnit.'" /><input type="hidden" id="lastOperation" value="update" /><input type="hidden" id="lastOperationStatus" value="OK" />';
      }
  }
}else{
  $wu = new WorkUnit();
  $wu->idCatalogUO = $idCatalog;
  $wu->reference = $WUReference;
  $wu->description = $WUDescription;
  $wu->entering = $WUIncoming;
  $wu->deliverable = $WULivrable;
  $wu->validityDate = $ValidityDateWU;
  $catalog = new CatalogUO($idCatalog);
  $wu->idProject = $catalog->idProject;
  $res = $wu->save();
  if($result == ""){
    $result = getLastOperationStatus($res);
  }
  if ($result == "OK") {
    $complexity = new Complexity();
    $listComplexity = $complexity->getSqlElementsFromCriteria(array('idCatalogUO'=>$idCatalog));
    foreach ($listComplexity as $comp){
      $charge = RequestHandler::getNumeric('charge'.$comp->id);
      $charge = Work::convertWork($charge);
      if($charge==0)$charge=null;
      $price = RequestHandler::getNumeric('price'.$comp->id);
      $duration = RequestHandler::getNumeric('duration'.$comp->id);
      if( !$charge and !$price and !$duration)continue;
      $compValue = new ComplexityValues();
      $compValue->idCatalogUO = $idCatalog;
      $compValue->idComplexity = $comp->id;
      $compValue->idWorkUnit = $wu->id;
      $compValue->charge = $charge;
      $compValue->price = $price;
      $compValue->duration = $duration;
      $compValue->save();
    }
  }
}
// Message of correct saving
displayLastOperationStatus($res);

?>