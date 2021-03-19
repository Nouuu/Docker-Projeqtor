<?PHP
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
 * Get the list of objects, in Json format, to display the grid list
 */
require_once "../tool/projeqtor.php"; 
$idCatalog=RequestHandler::getId('idCatalog');
$name=RequestHandler::getValue('name');
$idZone=RequestHandler::getValue('idZone');
$complexity = new complexity();
$exist = $complexity->countSqlElementsFromCriteria(array('idCatalogUO'=>$idCatalog,'idZone'=>$idZone));
Sql::beginTransaction();
if(!$exist){
  $complexity->idCatalogUO = $idCatalog;
  $complexity->name = $name;
  $complexity->idZone = $idZone;
  if(trim($name)!=''){
    $complexity->save();
  }else{
    return;
  }
}else{
  $complexity = SqlElement::getSingleSqlElementFromCriteria('Complexity', array('idCatalogUO'=>$idCatalog,'idZone'=>$idZone));
  if(trim($name)!=''){
    $complexity->name = $name;
    $complexity->save();
  }else{
    $compVal = new ComplexityValues();
    $nbCompVal = $compVal->countSqlElementsFromCriteria(array('idComplexity'=>$complexity->id));
    if(!$nbCompVal){
        $complexity->delete();
    }else{
        echo $complexity->name;
      return;
    }
  }
}
Sql::commitTransaction();
?>