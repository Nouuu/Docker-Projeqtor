<?php
/*
 * @author: qCazelles 
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/jsonVersionsPlanning.php');
$showOnlyActivesVersions=Parameter::getUserParameter('showOnlyActivesVersions');
$hideversionsWithoutActivity=Parameter::getUserParameter('versionsWithoutActivity');
$displayProductversionActivity = Parameter::getUserParameter('planningVersionDisplayProductVersionActivity');
$pvsArray = array();
$hideComponent=array();
$compWithAct=array();
//CHANGE qCazelles - Correction GANTT - Ticket #100
//Old
// if (isset($_REQUEST['productVersionsListId'])) {
//   $pvsArray = $_REQUEST['productVersionsListId'];
// }
// else {
//   for ($i = 0; $i < $_REQUEST['nbPvs']; $i++) {
//     $pvsArray[$i] = $_REQUEST['pvNo'.$i];
//   }
// }
//New
if (isset($_REQUEST['productVersionsListId'])) {
  if ( strpos($_REQUEST['productVersionsListId'], '_')!==false) {
    $pvsArray=explode('_', $_REQUEST['productVersionsListId']);
  }
  else {
    $pvsArray[]=$_REQUEST['productVersionsListId'];
  }
} else if (isset($_REQUEST['nbPvs'])){ //END CHANGE qCazelles - Correction GANTT - Ticket #100
	for ($i = 0; $i < $_REQUEST['nbPvs']; $i++) {
		$pvsArray[$i] = $_REQUEST['pvNo'.$i];
	}
} else { // PBE : will retreive last access if use of previous navigation button
  if (sessionValueExists('tabProductVersions')) {
    $pvsArray=getSessionValue('tabProductVersions');
  }
}
//florent ticket 4302
$object=RequestHandler::getValue('objectVersion');

if($displayProductversionActivity == 1  and $hideversionsWithoutActivity== 1){
  $cp=0;
  $comptDisplay=0;
  $comp=array();
  $displayList=array();
  if($object!='ComponentVersion'){
    foreach ($pvsArray as $id=>$idProd){ // product version 
      $prod= new ProductVersion($idProd);
      $activityOfProdV=$prod->searchActivityForVersion();
      $activityOfProductV=(isset($activityOfProdV[0]))?$activityOfProdV[0]:array();
      $activityOfProdV=(isset($activityOfProdV[1]))?$activityOfProdV[1]:array();
      $listOfCompo=ProductVersionStructure::getComposition($idProd);
      foreach ($listOfCompo as $idCVs){
        $comp[$idCVs]=ProductVersionStructure::getComposition($idCVs);
      }
      foreach ($listOfCompo as $idComponentVersion){ // component  version 
        $cp++;
        $componentVersion = new ComponentVersion($idComponentVersion);
        $result=$componentVersion->searchActivityForVersion();
        $listActivityComponent=(isset($result[0]))?$result[0]:array();
        $listActivityComponentVersion=(isset($result[1]))?$result[1]:array();
        if(empty($listActivityComponent) and empty($listActivityComponentVersion)){
          $comptDisplay++;
          $hideComponent[]=$idComponentVersion;
        }else{
          $compWithAct[$idComponentVersion]=$idComponentVersion;
        }
      }
      if(!empty($compWithAct)){
        foreach ($compWithAct as $idCompWithAct){
          foreach ($comp as $id => $val){
            if(in_array($idCompWithAct, $val)){
              $displayList[$id]=$id;
            }
          }
        }
      }
      if(empty($activityOfProdV) and empty($activityOfProductV) and $comptDisplay == $cp){
        unset($pvsArray[$id]);
      }
    }
    
  }else {
    foreach ($pvsArray as $idComponentVersion){
      $comp[$idComponentVersion]=ProductVersionStructure::getComposition($idComponentVersion);
      $cp++;
      $componentVersion = new ComponentVersion($idComponentVersion);
      $result=$componentVersion->searchActivityForVersion();
      $listActivityComponent=(isset($result[0]))?$result[0]:array();
      $listActivityComponentVersion=(isset($result[1]))?$result[1]:array();
      if(empty($listActivityComponent) and empty($listActivityComponentVersion)){
        $comptDisplay++;
        $hideComponent[]=$idComponentVersion;
      }else{
        $compWithAct[$idComponentVersion]=$idComponentVersion;
      }
    }
    if(!empty($compWithAct)){
        foreach ($compWithAct as $idCompWithAct){
          foreach ($comp as $id => $val){
            if(in_array($idCompWithAct, $val)){
              $displayList[$idCompWithAct]=$idCompWithAct;
            }
          }
        }
    }
    if($comptDisplay == $cp){
      unset($pvsArray[$idComponentVersion]);
    }
  }
  
  if(isset($displayList)){
    $compWithAct=$compWithAct+$displayList;
  }
  
  if((empty($compWithAct) and empty($pvsArray) and $object!='ComponentVersion') or (empty($compWithAct)and $object=='ComponentVersion')){ 
    echo '<div class="messageWARNING">'.i18n('noActivityVersions').'</div>';
    return;
  }
}



if($showOnlyActivesVersions== 1){
  $pvComponentActList= array();
  $productVersionActiv= array();
  $componentVersionActList=array();
  $listIdPv='';
  $productVersion = new ProductVersion();
  $componentVersion = new ComponentVersion();
  $where=" isStarted=1 and idle=0  and isDelivered=0 and isEis=0 ";
  $listActiveComponentVersion=$componentVersion->getSqlElementsFromCriteria(null,null,$where);
  $listIdPv=implode(',',$pvsArray);
  $where.="and id in ($listIdPv)";
  if($object!='ComponentVersion'){
    foreach ($productVersion->getSqlElementsFromCriteria(null,null,$where) as $id=>$objPvValide){
      $productVersionActiv[$objPvValide->id]=$objPvValide->id;
    }
    foreach ($pvsArray as  $idProductV){
      $listComponentV=ProductVersionStructure::getComposition($idProductV);
      if(isset($listComponentV) and isset($listActiveComponentVersion)){
        foreach ($listComponentV as $idComponentV){
          foreach ($listActiveComponentVersion as $id=>$ActivComponentVersion) {
            if($idComponentV==$ActivComponentVersion->id){
              $componentVersionActList[$ActivComponentVersion->id]=$ActivComponentVersion->id;
              $pvComponentActList[$idProductV]=$idProductV;
              continue;
            }
          }
        }
      }
    }
  }else{
    foreach ($listActiveComponentVersion as $id=>$objCvValide){
      $pvComponentActList[$objCvValide->id]=$objCvValide->id;
    }
  }
  $allProductVersionActive=$productVersionActiv+$pvComponentActList;
  if(empty($allProductVersionActive)){ 
    echo '<div class="messageWARNING">'.i18n('noCurrentVersion').'</div>';
    return; 
  }
  echo '{"identifier":"id", "items":[';
  drawElementJsonVersion($object,$allProductVersionActive,$displayProductversionActivity,$hideversionsWithoutActivity,$compWithAct,$hideComponent);
  
}else{
  echo '{"identifier":"id", "items":[';
  drawElementJsonVersion($object,$pvsArray,$displayProductversionActivity,$hideversionsWithoutActivity,$compWithAct,$hideComponent);
}

echo ']}';


function drawElementJsonVersion($object,$pvsArray,$displayProductversionActivity,$hideversionsWithoutActivity,$compWithAct,$hideComponent){
  if($object!='ComponentVersion'){
    foreach ($pvsArray as $idProductVersion) {
      $productVersion = new ProductVersion($idProductVersion);
      $productVersion->displayVersion();
      foreach (ProductVersionStructure::getComposition($productVersion->id) as $idComponentVersion) {
        $componentVersion = new ComponentVersion($idComponentVersion);
        $hide=SqlList::getFieldFromId('ComponentVersionType', $componentVersion->idComponentVersionType, 'lockUseOnlyForCC');
        if($displayProductversionActivity == 1  and $hideversionsWithoutActivity== 1){
          foreach ($hideComponent as $id){
            if($id==$idComponentVersion){
              $hide=1;
            }
          }
          if(in_array($idComponentVersion,$compWithAct)){
            $hide=0;
          }
        }
  
        if ($hide!=1) $componentVersion->treatmentVersionPlanning($productVersion,$hideComponent,$compWithAct);
      }
    }
  }else {
    if($hideversionsWithoutActivity==1){
      $pvsArray=$compWithAct;
    }
    foreach ($pvsArray as $idComponentVersion) {
      $componentVersion = new ComponentVersion($idComponentVersion);
      $componentVersion->displayVersion();
      foreach (ProductVersionStructure::getComposition($idComponentVersion) as $idComponentVer) {
        $componentVer = new ComponentVersion($idComponentVer);
        $hide=SqlList::getFieldFromId('ComponentVersionType', $componentVer->idComponentVersionType, 'lockUseOnlyForCC');
        if($displayProductversionActivity == 1  and $hideversionsWithoutActivity== 1){
          foreach ($hideComponent as $id){
            if($id==$idComponentVer){
              $hide=1;
            }
          }
          if(in_array($idComponentVer,$compWithAct)){
            $hide=0;
          }
        }
        if ($hide!=1) $componentVer->treatmentVersionPlanning($componentVersion,$hideComponent,$compWithAct);
      }
    }
  }
}
