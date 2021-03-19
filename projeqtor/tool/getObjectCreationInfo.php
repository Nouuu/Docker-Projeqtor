<?PHP
/**
 * * COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2014-2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org
 *
 * ** DO NOT REMOVE THIS NOTICE ***********************************************
 */

/**
 * ===========================================================================
 * Get creation information for given object
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog ( '   ->/tool/getObjectCreationInfo.php' );
if (! isset($obj)) {
	$objectClass = $_REQUEST ['objectClass'];
	Security::checkValidClass($objectClass);
	$objectId = $_REQUEST ['objectId'];
	if ($objectClass) $obj = new $objectClass ( $objectId ); // validated to be numeric value in SqlElement base constructor
} else {
  $objectClass=get_class($obj);
  $objectId=$obj->id;
}
if (! isset($comboDetail)) {
  $comboDetail=false;
}
if (isset($obj)) {
  $updateRight=securityGetAccessRightYesNo('menu' . $objectClass, 'update', $obj);
  $canUpdateCreationInfo=false;
  if ($obj->id and $updateRight and (!property_exists($obj, 'idle') or $obj->idle==0)) {
    $user=getSessionUser();
    $habil=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile' => $user->getProfile($obj),'scope' => 'canUpdateCreation'));
    if ($habil) {
      $list=new ListYesNo($habil->rightAccess);
      if ($list->code == 'YES') {
        $canUpdateCreationInfo=true;
      }
    }
  }
  $displayWidthButtonCI="9999";
  if (isset($_REQUEST ['destinationWidth'])) {
  	$displayWidthButtonCI=$_REQUEST ['destinationWidth'];
  }
  ?>
  <?php  if (property_exists($obj, 'idStatus') and $displayWidthButtonCI>=500 and get_class($obj)!='Mail') {
// Bug correction
// Now take care of fieldAttribute
  $extraReadonlyFields=$obj->getExtraReadonlyFields();      
  $canUpdateStatus = ( ( $obj->isAttributeSetToField('idStatus','readonly') or in_array('idStatus',$extraReadonlyFields))?false:true);
  ?>
  <div style="float:left;display:table-cell ;top:0px;width:130px;height:35px;vertical-align:middle;position:relative;z-index:99998;">
    <div style="white-space:normal;width:133px;max-width:133px;<?php echo (isFF())?'height:35px;':'height:39px;';?>max-height:39px;display:table-cell;padding:0px 4px;vertical-align: middle;zoom:0.9;-moz-transform: scale(0.9);overflow:hidden;position:absolute;<?php if ($updateRight and $canUpdateStatus) echo "cursor:pointer;";?>"
    <?php if ($updateRight and $canUpdateStatus) {?> onClick="showDirectChangeStatus();" title="<?php echo i18n('moveStatusBar');?>" <?php }?> >
    <?php if ($obj->idStatus) {
    	$status=new Status($obj->idStatus);
    	echo colorNameFormatter($status->name."#split#".$status->color);
    }?>
    </div>
    <div class="statusBar" id="directChangeStatusDiv" style="<?php echo (isFF())?'top:-15px;':'';?>white-space:normal;display:none;position:absolute;width:133px;zoom:0.9; -moz-transform: scale(0.9);padding:0px 4px 4px 4px;">
      <?php 
      $tmpClass=$objectClass;
      if ($tmpClass=='TicketSimple') $tmpClass='Ticket';
    	$idType='id' . $tmpClass . 'Type';
    	$typeClass=$tmpClass . 'Type';
    	$table=SqlList::getList('Status','name',$obj->idStatus, false );
    	if (property_exists($obj,$idType) ) {
    		reset($table);
    		$firstKey=key($table);
    		$firstName=current($table);
    		// look for workflow
    		if ($obj->$idType and $obj->idStatus) {
    			$profile="";
    			if (sessionUserExists()) {
    				$profile=getSessionUser()->getProfile($obj);
    			}
    			$type=new $typeClass($obj->$idType,true);
    			if (property_exists($type,'idWorkflow') ) {
    				$ws=new WorkflowStatus();
// MTY - LEAVE SYSTEM
            if (isLeavesSystemActiv()) {
              // For Leave System and Leave :
              //   - Leave Admin or 
              //   - Manager of Employee or
              //   - Employee of the leave 
              //   can see status
              if ($objectClass=='Leave') {
                if (isLeavesAdmin() or isManagerOfEmployee(getSessionUser()->id, $obj->idEmployee) 
                or (getSessionUser()->isEmployee==1 and $obj->idEmployee == getSessionUser()->id)) {
                  $theProfile = getFirstADMProfile();
                  if ($theProfile!=null) {
                    $profile = $theProfile->id;
                  }
                }
              }                
            }
// MTY - LEAVE SYSTEM
    				$crit=array('idWorkflow'=>$type->idWorkflow, 'allowed'=>1, 'idProfile'=>$profile, 'idStatusFrom'=>$obj->idStatus);
    				$wsList=$ws->getSqlElementsFromCriteria($crit, false);
    				$compTable=array($obj->idStatus=>'ok');
    				foreach ($wsList as $ws) {
// MTY - LEAVE SYSTEM
              // For Leave System and Leave :
              //   - Employee of the leave 
              //   status that has not id = 1 and with setSubmittedLeave = 0 and setAcceptedLeave = 1 or setRejectedLeave = 1
              // are not allowed
              if (isLeavesSystemActiv() and $objectClass=='Leave' and $ws->idStatusTo <> 1) {
                if ( getSessionUser()->isEmployee==1 and 
                     $obj->idEmployee == getSessionUser()->id and
                     !isLeavesAdmin() and 
                     !isManagerOfEmployee(getSessionUser()->id, $obj->idEmployee)         
                   ) {
                  $theStatus = new Status($ws->idStatusTo);
                  if  ($theStatus->setSubmittedLeave==1 and $theStatus->setRejectedLeave==0 and $theStatus->setAcceptedLeave==0) {
    	  				    $compTable[$ws->idStatusTo]="ok";
    		  		    }
                } else {
                  $compTable[$ws->idStatusTo]="ok";
                }
              } else {
// MTY - LEAVE SYSTEM      
                $compTable[$ws->idStatusTo]="ok";
              }
    				}    				
    				$table=array_intersect_key($table,$compTable);
    			}
    			$current=new Status($obj->idStatus,true);
    			if ($current->isCopyStatus and isset($firstKey)) {
    				$table[$firstKey]=$firstName;
    			}
    		} else {
    			$table=array($firstKey=>$firstName);
    		}
    	}
    	foreach ($table as $stId=>$stName) {
    		echo '<div style="padding-top:4px;min-height:20px;height:32px;position:relative;'.(($stId==$obj->idStatus)?'" onClick="hideDirectChangeStatus();"':'cursor:pointer;" onClick="dijit.byId(\'idStatus\').set(\'value\','.$stId.');setTimeout(\''.(($comboDetail)?'window.top.saveDetailItem()':'saveObject()').'\',100);" ').' >';
    		echo colorNameFormatter($stName."#split#".(($stId==$obj->idStatus)?'transparent':SqlList::getFieldFromId('Status', $stId, 'color')),-1,'32');
    		echo '</div>';
    	}
    	?>
    </div>
  </div>
  <?php }?>
  <div style="position:relative;top:0px;">
  <div style="float:left;margin-left:5px;<?php if($displayWidthButtonCI<800) echo 'display:none;'?>">
  <?php 
  if (property_exists ( $obj, 'lastUpdateDateTime' ) && $obj->lastUpdateDateTime) {
    echo formatDateThumb(null,$obj->lastUpdateDateTime,'left',32,'Update');
  }
  ?>
  </div>
  <div style="padding-right:16px;<?php if($displayWidthButtonCI<800) echo 'display:none;'?>" <?php echo ($canUpdateCreationInfo)?'class="buttonDivCreationInfoEdit" onClick="changeCreationInfo();"':'';?>>
  <?php 
  if (!$comboDetail and $obj->id and property_exists($obj,'idUser') and get_class($obj)!='Affectation') {
    echo formatUserThumb($obj->idUser,SqlList::getNameFromId('Affectable', $obj->idUser),'Creator',32,'right',true);
    $creationDate='';
  	if (property_exists ( $obj, 'creationDateTime' )) {
  		$creationDate=$obj->creationDateTime;
  	} else if (property_exists ( $obj, 'creationDate' )) {
  		$creationDate=$obj->creationDate;
  	}
  	if ($creationDate) {
      echo formatDateThumb($creationDate,null,'right',32);
    }
  
  }
  if (property_exists ( $obj, 'isPrivate' )) {
    echo '<div style="position:absolute;top:0px;">';
    if ($obj->isPrivate) {
      echo '<img style="position:relative;left:60px;height:16px" src="../view/img/private.png" />';
    }
    echo '</div>';
  }
  ?>
  </div>
  </div>
  <?php if($objectClass == 'ComponentVersion' or $objectClass == 'ProductVersion'){
    $user = getSessionUser();
    $idUser = $user->id;
    $isSubcribe = isSubscribeVersion($obj, $idUser);
   ?>
  <input type="hidden" id="isCurrentUserSubscription" value="<?php echo $isSubcribe ?>" />  
  <?php }
}?>