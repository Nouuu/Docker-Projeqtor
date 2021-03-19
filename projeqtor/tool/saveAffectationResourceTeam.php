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
scriptLog('   ->/tool/saveAffectationResourceTeam.php');

$idResourceTeam = RequestHandler::getId('idResourceTeam');
$resource= RequestHandler::getId('affectationResourceTeam');
$rate = RequestHandler::getValue('affectationRateResourceTeam');
$start = RequestHandler::getValue('affectationStartDateResourceTeam');
$end = RequestHandler::getValue('affectationEndDateResourceTeam');
$description = RequestHandler::getValue('affectationDescriptionResourceTeam');
$idle = RequestHandler::getBoolean('affectationIdleResourceTeam');
if($idle){
  $idle = 1;
}else{
  $idle = 0;
}
$mode = RequestHandler::getValue('mode');
Sql::beginTransaction();
$result = "";
  $idAffectation = RequestHandler::getId('idAffectation');
  $resourceTeam=new ResourceTeamAffectation($idAffectation);
  if($mode == 'edit'){
    //POOL AFF
    $autoAffectationPool=Parameter::getGlobalParameter('autoAffectationPool');
    if($autoAffectationPool=="IMPLICIT"){
      if($start != $resourceTeam->startDate or $end != $resourceTeam->endDate){
        $aff = new Affectation();
        $listAffRes = $aff->getSqlElementsFromCriteria(array('idResource'=>$resource,'idResourceTeam'=>$resourceTeam->idResourceTeam));
        foreach ($listAffRes as $affR){
          $affResStart = $affR->startDate;
          $affResEnd = $affR->endDate;
          $affPoolProject = new Affectation();
          $listAffPoolProject = $affPoolProject->getSqlElementsFromCriteria(array('idProject'=>$affR->idProject,'idResource'=>$resourceTeam->idResourceTeam));
          foreach ($listAffPoolProject as $poolAffPerProj){
            $startPoolProj = $poolAffPerProj->startDate;
            $endPoolProj = $poolAffPerProj->endDate;
            if( $affResStart <= $endPoolProj and $affResEnd >= $startPoolProj ){
              if($start != $resourceTeam->startDate){
                $affR->startDate = $startPoolProj;
                if($startPoolProj < $affResStart)$affR->startDate = $affResStart; 
              }
              if($end != $resourceTeam->endDate){
                $affR->endDate = $endPoolProj;
                if($endPoolProj < $affResEnd)$affR->endDate = $affResEnd;
              }
              $affR->save();
            }else{
              $affR->delete();
            }
          }
        }
      }
    }
  $resourceTeam->idResource = $resource;
  $resourceTeam->rate = $rate;
  $resourceTeam->description = nl2brForPlainText($description);
  $resourceTeam->idle = $idle;
  $resourceTeam->startDate = $start;
  $resourceTeam->endDate = $end;
  $result=$resourceTeam->save();
}else{
  $resourceTeam=new ResourceTeamAffectation();
  $resourceTeam->idResourceTeam = $idResourceTeam;
  $resourceTeam->idResource = $resource;
  $resourceTeam->rate = $rate;
  $resourceTeam->description = nl2brForPlainText($description);
  $resourceTeam->idle = $idle;
  $resourceTeam->startDate = $start;
  $resourceTeam->endDate = $end;
  $res=$resourceTeam->save();

  if (!$result) {
    $result=$res;
  }
}

// Message of correct saving
displayLastOperationStatus($result);

?>