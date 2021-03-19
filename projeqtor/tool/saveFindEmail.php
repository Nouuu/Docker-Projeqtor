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

$isId = RequestHandler::getValue('isId');
//showDetail
if($isId == 'true'){
  $result = "";
  $id=RequestHandler::getValue('id');
  $id = str_replace(array(',',';'),'_',$id);
  $listAttendees=explode('_',$id);
  foreach ($listAttendees as $val){
    if (Security::checkValidId($val,false)) {
      $name = SqlList::getFieldFromId('Affectable', $val, 'email');
      if($name != "" and Security::checkValidId($val,false)){
        $result .= $name.','; 
      }
    } else {
      if ($val) $result .= $val.',';
    }
  }
  $result = rtrim($result, ',');
  echo $result;
//onBlur
}else{
  
  $adress=RequestHandler::getValue('adress');
  if($adress != null){
    $listTeam=array_map('strtolower',SqlList::getList('Team','name'));
    $listName=array_map('strtolower',SqlList::getList('Affectable'));
    $listUserName=array_map('strtolower',SqlList::getList('Affectable','userName'));
    $listInitials=array_map('strtolower',SqlList::getList('Affectable','initials'));
    $listAttendees=explode(',',str_replace(';',',',$adress));
    $listEmail = array_map('strtolower',SqlList::getList('Resource','email'));
    $adressMail = explode(",",$adress);
    $stockAdress = "";
    
    foreach($adressMail as $email){
      if(filter_var($email,FILTER_VALIDATE_EMAIL)){
        $stockAdress.=$email.',';
      } 
    }

    $adress="";
  
    foreach($listAttendees as $attendee){
      $attendee=strtolower(trim($attendee));
      if(in_array($attendee,$listName)) {
        $adress.=($adress)?',':'';
        $aff=new Affectable(array_search($attendee,$listName));
        if($aff->email) {
          $adress.='' . $aff->email . ',';
        }
      }else if(in_array($attendee,$listUserName)) {
        $adress.=($adress)?', ':'';
        $aff=new Affectable(array_search($attendee,$listUserName));
        if ($aff->email) {
          $adress.='' . $aff->email . '';
        }
      }else if(in_array($attendee,$listInitials)) {
        if($attendee != null){
          $adress.=($adress)?',':'';
          $aff=new Affectable(array_search($attendee,$listInitials));
          if ($aff->email) {
            $adress.='' . $aff->email . '';
          }
        }
      }else if(in_array($attendee,$listTeam)) {
        $adress.=($adress)?',':'';
        $id=array_search($attendee,$listTeam);
        $aff=new Affectable();
        $lst=$aff->getSqlElementsFromCriteria(array('idTeam'=>$id));
        foreach ($lst as $aff) {
          $adress.=($adress)?',':'';
          if ($aff->email) {
            $adress.='' .$aff->email. '';
          }
        }
      }else{
        if(filter_var($attendee,FILTER_VALIDATE_EMAIL)){
          $adress.=($adress)?',':'';
        }else{
          $adress.=''.$attendee.',';
        }
      }
    }
    $adress=str_ireplace(',,',',',$adress);
    
    echo rtrim($stockAdress.$adress,',');
  }else{
    echo "";
  }
}

?>