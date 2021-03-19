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

/*
 * ============================================================================
 * Menu defines list of items to present to users.
 */
require_once ('_securityCheck.php');
class WorkflowMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $sortOrder;
  public $idle;
  public $workflowUpdate;
  public $description;
  public $_sec_listTypeUsingWorkflow;
  public $_spe_listTypeUsingWorkflow;
  public $_sec_WorkflowDiagram;
  public $_workflowDiagram_colSpan="2";
  public $_spe_workflowDiagram;
  public $_spe_hideStatus;
  public $_sec_WorkflowStatus;
  public $_spe_hideProfile;
  public $_workflowStatus_colSpan="2";
  public $_spe_workflowStatus;
  public $_workflowStatus;
  public $isLeaveWorkflow;

  
  public $_statusList;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="75%">${name}</th>
    <th field="sortOrder" width="10%">${sortOrderShort}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array(
    "workflowUpdate"=>"hidden",
    "isLeaveWorkflow"=>"hidden"
  );
    
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

   /** ==========================================================================
   * Return the specific layout
   * @return string the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
 
  /** ==========================================================================
   * Return list of workflow status for a workflow (id)
   * @return WorkflowStatus[] an array of WorkflowStatus
   */
  public function getWorkflowstatus() {
    if ($this->id==null or $this->id=='') {
      return array();
    }
    if ($this->_workflowStatus) {
      return $this->_workflowStatus;
    }
    $ws=new WorkflowStatus();
    $crit=array('idWorkflow'=>$this->id);
    $wsList=$ws->getSqlElementsFromCriteria($crit, false);
    return $wsList;
  }
  
   /** ==========================================================================
   * Return check value of workflow status for a workflow
   * @return array[idStatusFrom][idStatusTo][idProfile] a 3 level array [idStatusFrom] [idStatusTo] [idProfile] => check value
   */
  public function getWorkflowstatusArray() {
    $wsList=$this->getWorkflowstatus();
    $result=array();
    // Initialize
    $statusList=$this->getStatusList();
    $profileList=SqlList::getList('Profile');
    foreach($statusList as $idFrom => $valFrom) {
      $result[$idFrom]=array();
      foreach($statusList as $idTo => $valTo) {
        if ($idFrom!=$idTo) {
          $result[$idFrom][$idTo]=array();
          foreach($profileList as $idProf => $valProf) {
            $result[$idFrom][$idTo][$idProf]=0;
          }
        }
      }
    }
    // Get Data
    foreach ($wsList as $ws) {
      $result[$ws->idStatusFrom][$ws->idStatusTo][$ws->idProfile]=$ws->allowed;
    }
    return $result;
  }
  
  /** ==========================================================================
   * Return true if the workflow as a status with idStatus
   * @param integer $idStatus The id status
   * @return boolean True if the workflow as a status with idStatus
   */
  public function hasStatus($idStatus) {
      $theWorkflowStatus = $this->getWorkflowstatus();
      foreach ($theWorkflowStatus as $wfStatus) {
          if ($idStatus == $wfStatus->idStatusFrom or $idStatus == $wfStatus->idStatusTo) {
              return true;
          }
      }
      return false;
  }

  /** ==========================================================================
   * Return a status of the workflow with $setStatusOrLeave = 1
   * @param string $setStatusOrLeave The setStatusOrLeave
   * @param integer $idle 0 = status has idle = 0 - 1 = status has idle = 1 - -1 all values of idle
   * @return Status a status if the workflow as a status with $setStatusOrLeave = 1 - Else null
   */
  public function getAStatusWithSetStatusOrLeave($setStatusOrLeave,$idle=-1) {
      if (!property_exists("Status", $setStatusOrLeave)) {
          return null;
      }
      $theWorkflowStatus = $this->getWorkflowstatus();
      $theStatus = null;
      foreach ($theWorkflowStatus as $wfStatus) {
          $status = new Status($wfStatus->idStatusFrom);
          if ($status->$setStatusOrLeave == 1 and ($idle===-1 or ($idle===1 and $status->idle===1) or (($idle===0 and $status->idle===0)) )) {
              $theStatus = $status;
              break;
          }
          $status = new Status($wfStatus->idStatusTo);
          if ($status->$setStatusOrLeave == 1 and ($idle===-1 or ($idle===1 and $status->idle===1) or (($idle===0 and $status->idle===0)) )) {
              $theStatus = $status;              
              break;
          }
      }
      return $theStatus;
  }

  /** ==========================================================================
   * Return true if the workflow as a status with $setStatusOrLeave = 1
   * @param string $setStatusOrLeave The setStatusOrLeave
   * @param integer $idle 0 = status has idle = 0 - 1 = status has idle = 1 - -1 all values of idle
   * @return boolean True if the workflow as a status with $setStatusOrLeave = 1
   */
  public function hasSetStatusOrLeave($setStatusOrLeave, $idle=-1) {
      return ($this->getAStatusWithSetStatusOrLeave($setStatusOrLeave,$idle)===null?false:true);
  }

// MTY - LEAVE SYSTEM  
  /** ==========================================================================
   * Return a status of the workflow with setSubmittedLeave = 0 and setAcceptedLeave = 0 and setRejectedLeave = 0
   * @param integer $idle 0 = status has idle = 0 - 1 = status has idle = 1 - -1 all values of idle
   * @return Status a status if the workflow as this type of status - Else null
   */
  public function getAStatusWhichIsNeutral($idle=-1) {
      $theWorkflowStatus = $this->getWorkflowstatus();
      $theStatus = null;
      foreach ($theWorkflowStatus as $wfStatus) {
          $status = new Status($wfStatus->idStatusFrom);
          if ($status->setSubmittedLeave == 0 and $status->setAcceptedLeave == 0 and $status->setRejectedLeave == 0 and
              ($idle===-1 or ($idle===1 and $status->idle===1) or (($idle===0 and $status->idle===0)) )) {
              $theStatus = $status;
              break;
          }
          $status = new Status($wfStatus->idStatusTo);
          if ($status->setSubmittedLeave == 0 and $status->setAcceptedLeave == 0 and $status->setRejectedLeave == 0 and
              ($idle===-1 or ($idle===1 and $status->idle===1) or (($idle===0 and $status->idle===0)) )) {
              $theStatus = $status;              
              break;
          }
      }
      return $theStatus;
  }
// MTY - LEAVE SYSTEM  
    
  private function getStatusList() {
  	if ($this->_statusList) {
  		return $this->_statusList;
  	}
  	$statusList=SqlList::getList('Status');
  	foreach ($statusList as $idStatus=>$status) {
  		$critArray=array('scope'=>'workflow', 'objectClass'=>'workflow#'.$this->id, 'idUser'=>$idStatus);
  		$cs=SqlElement::getSingleSqlElementFromCriteria("ColumnSelector", $critArray);
  		if ($cs and $cs->id and $cs->hidden) {
  			unset ($statusList[$idStatus]);
  		}
  	}
  	$this->_statusList=$statusList;
  	return $statusList;
  }
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param string $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item, $limitToProfile=null){
    global $_REQUEST, $print;
    if (array_key_exists('destinationWidth', $_REQUEST)) {
      $detailWidth=$_REQUEST['destinationWidth'];
	    $detailWidth=preg_replace('/[^0-9]/','', $detailWidth); // only allow digits
      $detailWidth-=40;
      $detailWidth.='px';
    } else {
      $detailWidth="100%";
    }
    $canUpdate=securityGetAccessRightYesNo('menu'.get_class($this), 'update', $this, getSessionUser())=="YES";
    $result="";
    if ($item=='workflowStatus') {
      $wp= new workflowProfile();
      $profileAuthorizationList=$wp->getAuthorizationProfileList($this->id);
      $width="100px";
      $statusList=$this->getStatusList();
      $profileList=SqlList::getList('Profile');
      $profileIdList="";
      foreach ($profileList as $profileCode => $profileValue) {
        $profileIdList.=$profileCode . " ";
      }     
      $nbProfiles=count($profileList);
      $height="100%";
      if (RequestHandler::isCodeSet("destinationHeight")) {
        $height=(intval(RequestHandler::getValue("destinationHeight"))-100)."px";
      }
      $result .= '<div style="border: 1px solid #A0A0A0;overflow: auto; width: ' . $detailWidth . '; height:'.$height.'">';
      $result .= '<table style="zoom:100%;">';

      $wsListArray=$this->getWorkflowstatusArray();
      foreach ($statusList as $statLineCode => $statLineValue) {
        $result .= ' <tr>';
        $result .= '  <th class="workflowHeader">' . i18n('from') . '&nbsp;\\&nbsp;' . i18n('to') . '</th>';
        foreach ($statusList as $statCode => $statValue) {
          $result .= '  <th class="workflowHeader">' . $statValue . '</th>';
        }
        $result .= '  <th class="workflowHeader"></th>';
        $result .='</tr>'; 
        $result .= '<tr>';
        $result .= '  <td class="workflowHeader">' . $statLineValue . '</td>';
        foreach ($statusList as $statColumnCode => $statColumnValue) {
          $result .= '  <td class="workflowData">';
          if ($statColumnCode!=$statLineCode) {
            $allChecked=true;
            foreach ($profileList as $profileCode => $profileValue) {  
              if ($wsListArray[$statLineCode][$statColumnCode][$profileCode]==0) {
                $allChecked=false;
              }
            }
            $title=$statLineValue . ' => ' . $statColumnValue;
            $result .='<table>' ;
            $result .= '  <tr title="' . $title . '"><td>';
            // dojotype not set to improve perfs
            $result .= '  <input xdojoType="dijit.form.CheckBox" type="checkbox" ';
            if ($canUpdate) $result .= ' onclick="workflowSelectAll('. $statLineCode . ',' . $statColumnCode . ',\'' . $profileIdList .'\');"';
            else $result.=' onclick="return false;" ';
            $name = 'val_' . $statLineCode . '_' . $statColumnCode;
            $result .= ' name="' . $name . '" id="' . $name . '" ';
            $result .= ($allChecked)?' checked ':'';
            $result .= '/>';
            $result .= ' </td>';
            $result .= '  <td><b>' . i18n('all') . '</b></td></tr>';  
            foreach ($profileList as $profileCode => $profileValue) {
              $toSkip = 0;
              foreach ($profileAuthorizationList as $profileAuthorization) {
                if ($profileAuthorization->idProfile == $profileCode)$toSkip = 1;
              }
              if ($toSkip == 0) {
              $titleProfile=$title."\n".$profileValue;
                $result.='  <tr title="'.$titleProfile.'" class="workflowDetail" ><td valign="top" style="vertical-align: top;" >';
                // dojotype not set to improve perfs
                $result.='  <input xdojoType="dijit.form.CheckBox" type="checkbox" ';
                if ($canUpdate) $result.=' onclick="workflowChange('.$statLineCode.','.$statColumnCode.',\''.$profileIdList.'\');"';
                else $result.=' onclick="return false;" ';
                $name='val_'.$statLineCode.'_'.$statColumnCode.'_'.$profileCode;
                $result.=' name="'.$name.'" id="'.$name.'" ';
                if ($wsListArray[$statLineCode][$statColumnCode][$profileCode]==1) {
                  $result.='checked';
                }
                $result.=' />';
                $result.=' </td> ';
                $result.='  <td><div style="width:60px;overflow: hidden; white-space: nowrap; overflow: hidden; "><span class="nobr">'.$profileValue.'</span></div></td></tr>';
              }
            }
            $result .= '</table>';
          }
          $result .='</td>';
        }
        $result .= '  <td class="workflowHeader">' . $statLineValue . '</td>';
        $result .= '</tr>';
      } 
      $result .= ' <tr>';
      $result .= '  <th class="workflowHeader">' . i18n('from') . '&nbsp;\\&nbsp;' . i18n('to') . '</th>';
      foreach ($statusList as $statCode => $statValue) {
        $result .= '  <th class="workflowHeader">' . $statValue . '</th>';
      }
      $result .='</tr>'; 
      $result .= '</table>';
      $result .= '</div>';
      
    // WORKFLOW DIAGRAM  
    } else if ($item=='workflowDiagram') {
      $statusId=RequestHandler::getId('idStatus',false,null);
      $statusList=$this->getStatusList();
      $statusListUsed=array();
      $statusColorList=SqlList::getList('Status', 'color');
      foreach ($statusColorList as $key=>$val) {
        if (strtolower($val)=='#ffffff') {
          $statusColorList[$key]='#eeeeee';
        }
      }
      $profileList=SqlList::getList('Profile');
      $width="75";
      $height="15";
      $sepWidth="10";
      $sepHeight="10";
      $dottedStyle='dotted';
      $arrowDownImg='<div class="wfDownArrow"></div>';
      $arrowUpImg='<div class="wfUpArrow"></div>';
      $wsListArray=$this->getWorkflowstatusArray();
      $crossArray=array();
      foreach ($statusList as $statLineCode => $statLineValue) {
        $crossArray[$statLineCode]=array();
        foreach ($statusList as $statColumnCode => $statColumnValue) {
          $allChecked=true;
          $oneChecked=false;
          $profileCheck=false;
          if ($statColumnCode!=$statLineCode) {    
            foreach ($profileList as $profileCode => $profileValue) {  
              if ($wsListArray[$statLineCode][$statColumnCode][$profileCode]==0) {
                $allChecked=false;
              } else {
                $oneChecked=true;
                if ($limitToProfile!=null and $profileCode==$limitToProfile) {
                	$profileCheck=true;
                }
              }
            }            
          }
          if ($limitToProfile!=null) {
          	if ($profileCheck==true) {
          	  $val="ALL";
          	} else {
          		$val="NO";
          	}
          } else if ($allChecked) {
            $val="ALL";
          } else if ($oneChecked) {
            $val="ONE";
          } else {
            $val="NO";
          }
          $crossArray[$statLineCode][$statColumnCode]=$val;
          if ($val!='NO') {
          	$statusListUsed[$statLineCode]=$statLineValue;
          	$statusListUsed[$statColumnCode]=$statColumnValue;
          }
        }
      }
      if ($limitToProfile!=null) {
      	$statusList=array_intersect($statusList,$statusListUsed);
      }
      $i=0;
      $max=array();
      $maxAll=array();
      $maxOne=array();
      $min=array();
      $minAll=array();
      $minOne=array();
      $borderLeft=array();
      $sepLeft=array();
      $borderRight=array();
      $sepRight=array();
      foreach ($statusList as $statLineCode => $statLineValue) {
        $j=0;
        $i++;
        $max[$i]=$i;
        $maxAll[$i]=$i;
        $maxOne[$i]=$i;
        $min[$i]='';
        $minAll[$i]='';
        $minOne[$i]='';
        foreach ($statusList as $statColumnCode => $statColumnValue) {
          $j++;
          //$min[$j]=$j;
          if ($crossArray[$statLineCode][$statColumnCode]!="NO") {
            if ($crossArray[$statLineCode][$statColumnCode]=="ALL") {
              $styleLine='solid';
            } else {
              $styleLine=$dottedStyle;
            }      
            if ($i<$j) {
              $max[$i]=$j;
              if ($crossArray[$statLineCode][$statColumnCode]=="ALL") {
                $maxAll[$i]=$j; 
              } else {
                $maxOne[$i]=$j; 
              }
              for ($t=$i+1;$t<$j;$t++) {
                $borderLeft[$t][$j]='2px ' . $styleLine . ' ' . $statusColorList[$statLineCode];
                $sepLeft[$t][$j]='2px ' . $styleLine . ' ' . $statusColorList[$statLineCode];
              }
              $sepLeft[$i][$j]='2px ' . $styleLine . ' ' . $statusColorList[$statLineCode];
            } else if ($i>$j) {
              if ($min[$i]=='') {
                $min[$i]=$j;
                $minOne[$i]=$j;
              }
              if ($crossArray[$statLineCode][$statColumnCode]=="ALL") {
                if ($minAll[$i]=='') {
                  $minAll[$i]=$j;
                }
              } 
              for ($t=($j+1);$t<=$i;$t++) {
                if (! isset($borderRight[$t][$j])) {
                  $borderRight[$t][$j]='2px ' . $styleLine . ' ' . $statusColorList[$statLineCode];
                }
                if (! isset($sepRight[$t-1][$j])) {
                  $sepRight[$t-1][$j]='2px ' . $styleLine . ' ' . $statusColorList[$statLineCode];
                }
              }
              //$sepRight[$j-1][$i-1]='2px ' . $styleLine . ' ' . $statusColorList[$statLineCode];
            }
          }
        } 
      }
      if (!$print) {
        $result.='<div style="width:'.$detailWidth.'; height:auto; overflow-x: auto; overflow-y: hidden; border: 1px solid #A0A0A0;">';
        //$result.='<div>';
      }
      $result.='<table style="zoom:90%;margin:0; spacing:0; padding:0; background-color:#FFFFFF;">';
      $result .= '<tr><td style="padding:10px;font-weight:bold;font-size:150%;color:#000000;text-align:left" colspan="' . (count($statusList)*2+1) .'"><span style="background-color:#EEEEEE;border-radius:20px;padding:5px 15px;width:auto">'.$this->name.'</span></td></tr>';
      $result.='<tr><td colspan="' . (count($statusList)*2+1) .'"><div style="height: ' . $sepHeight . 'px;"></div></td></tr>';
      $i=0;
      foreach($statusList as $idL=>$nameL) {
        $i++;
        $result.='<tr>';
        $result.='<td><div style="width:' . $sepWidth . 'px">' . '</div></td>'; 
        $j=0;
        foreach($statusList as $idC=>$nameC) {
          $j++;
          $colorI=$statusColorList[$idL];
          if (! $colorI or $colorI=='#FFFFFF') {
            $colorI="#000000";
          }
          if ($idL==$idC) {
            if($idL==$statusId){
              $result.='<td style="border:2px solid ' . $colorI . ';">';
              $result.='<div style="text-align:center;color:' . getForeColor($colorI) . ';background-color:' . $colorI . '; width:' . $width . 'px;height: ' . $height . 'px;">' . $nameL . '</div>';
            } else {
              $result.='<td style="border:2px solid ' . $colorI . ';">';
              $result.='<div style="text-align:center; width:' . $width . 'px;height: ' . $height . 'px;">' . $nameL . '</div>';
            }
          } else if ($i<$j) {
            $border='';
            $arrow="";
            if ($max[$i]>$j) {
              $form=$dottedStyle;
              if ($maxAll[$i]>$j) {
                $form='solid';
              }
              $border.='border-bottom:2px ' . $form . ' ' . $colorI . ';';           
            }
            if (isset($borderLeft[$i][$j])) {
              $border.='border-left:' . $borderLeft[$i][$j] . ';';
              //$arrow=$arrowImg;
            }
            $result.='<td style="' . $border . '">';
            $result.='<div style="width:' . $width . 'px;height: ' . $height . 'px;">' . $arrow . '</div>';
          } else {
            $border='';
            $arrow="";
            if ($min[$i] and $min[$i]<=$j) {
              $form=$dottedStyle;
              if ($minAll[$i] and $minAll[$i]<=$j) {
                $form='solid';
              }
              $border.='border-bottom:2px ' . $form . ' ' . $colorI . ';';            
            }
            if (isset($borderRight[$i][$j])) {
              $border.='border-left:' . $borderRight[$i][$j] . ';';
              //$arrow=$arrowImg;
            }
            $result.='<td style="' . $border . '">';
            $result.='<div style="width:' . $width . 'px;height: ' . $height . 'px;">' . $arrow . '</div>';
          }
          $result.='</td>';
          if ($i<=$j and $max[$i]>$j) {
            $border='border-bottom:2px ' . $dottedStyle . ' ' . $colorI . ';';
            if ($maxAll[$i]>$j) {
              $border='border-bottom:2px solid ' . $colorI . ';';
            }
            $result.='<td  style="' . $border . '"><div style="width:' . $sepWidth . 'px;height: ' . $height . 'px;"></div></td>';
          } else if ($j<$i and $min[$i] and $min[$i]<=$j) {
            $border='border-bottom:2px ' . $dottedStyle . ' ' . $colorI . ';';
            if ($minAll[$i] and $minAll[$i]<=$j) {
              $border='border-bottom:2px solid ' . $colorI . ';';
            }
            $result.='<td  style="' . $border . '"><div style="width:' . $sepWidth . 'px;height: ' . $height . 'px;"></div></td>';
          } else {
            $result.='<td><div style="width:' . $sepWidth . 'px;height: ' . $height . 'px;"></div></td>';
          } 
        }
        $result.='</tr>';
        $j=0;
        $result.='<tr>';
        $result.='<td><div style="width:' . $sepWidth . 'px; height: ' . $sepHeight . 'px;">' . '</div></td>'; 
        foreach($statusList as $idC=>$nameC) {
          $j++;
          $border='';
          $arrow="";
          if (isset($sepLeft[$i][$j])){
            $border.='border-left:' . $sepLeft[$i][$j] . ';';
            if ($i==$j-1) {
              $arrow=$arrowDownImg;
            }
          }
          if (isset($sepRight[$i][$j])){
            $border.='border-left:' . $sepRight[$i][$j] . ';';
            if ($i==$j) {
              $arrow=$arrowUpImg;
            }
          }
          $result.='<td style="' . $border . '"><div style="height: ' . $sepHeight . 'px;width:' . $sepWidth . 'px;position: relative;">' . $arrow . '</div></td>';
          $result.='<td><div style="height: ' . $sepHeight . 'px;width:' . $sepWidth . 'px;"></div></td>';
        }
        $result.='</tr>';
        
      }

      $result.='</table>';
      if (! $print) {
      	//$result.='</div>';
        $result.='</div>';
      }
    } else if ($item=='hideStatus') {
    	if (!$print and $this->id) {
    	  if(!isNewGui()){
          $positionParameterIcon = "top:-1px;";
          $iconClass="iconParameter iconSize16 ";
          $class= '';
        }else{
          $positionParameterIcon = "top:12px;";
          $iconClass="iconParameter iconSize22 imageColorNewGui";
          $class= 'class="resetMargin detailButton notButton"';
        }
        $result.='<button id="workflowParameterButton" dojoType="dijit.form.Button" showlabel="false"';
        $result.='title="'.i18n('workflowParameters').'"'; 
        $result.=' '.$class.' iconClass="'.$iconClass.'" style="position:absolute;right:3px;'.$positionParameterIcon.'">';
        $result.=' <script type="dojo/connect" event="onClick" args="evt">';
		    $result.='  showWorkflowParameter('.htmlEncode($this->id).');';
        $result.=' </script>';
        $result.='</button>';
      }
      //gautier tableauWorkflow
    } else if ($item=='hideProfile') {
      if (!$print and $this->id) {
        if(!isNewGui()){
          $positionParameterIcon = "top:-1px;";
          $iconClass="iconParameter iconSize16 ";
          $class= '';
        }else{
          $positionParameterIcon = "top:12px;";
          $iconClass="iconParameter iconSize22 imageColorNewGui";
          $class= 'class="resetMargin detailButton notButton"';
        }
        $result.='<button id="workflowParameterProfileButton" dojoType="dijit.form.Button" showlabel="false"';
        $result.='title="'.i18n('workflowProfileParameters').'"';
        $result.=' '.$class.' iconClass="'.$iconClass.'" style="position:absolute;right:3px;'.$positionParameterIcon.'">';
        $result.=' <script type="dojo/connect" event="onClick" args="evt">';
        $result.='  showWorkflowProfileParameter('.htmlEncode($this->id).');';
        $result.=' </script>';
        $result.='</button>';
      }
    }else if ($item=='listTypeUsingWorkflow') {
      global $nbColMax;
      if (array_key_exists('destinationWidth', $_REQUEST)) {
        $maxWidth=preg_replace('/[^0-9]/','', $detailWidth);
        $maxWidth= $maxWidth/ (($nbColMax)?$nbColMax:2);
        $maxWidth =  $maxWidth.'px';
      } else {
        $maxWidth =  '978px';
      }
      if($this->id){
      $type=new Type();
      $typeList=$type->getSqlElementsFromCriteria(array('idWorkflow'=>$this->id),null,null,'scope asc',null,true);
      $tab = array();
      foreach ($typeList as $val){
        $tab[$val->scope][$val->name]=$this->id;
      }
      $result.=' <div style="width:'.$maxWidth.'; overflow:auto;">';
      $result.='  <table>';
      $result.=' <tr>';
      $scopeName = "";
      foreach ($tab as $scope=>$val){
        $result.='  <td style="vertical-align:top;padding-right:5px;">';
        $result.='  <table>';
        foreach ($val as $name=>$value){
          if($scopeName != $scope){
            $result.='    <tr style="border-bottom:1px solid #aaaaaa;"  ><td style="padding:2px 5px;"class="workflowHeader" style="padding:2px 5px;">'. i18n($scope) .'</td> </tr>';
          }
          $scopeName = $scope;
          $result.='    <tr style="border-bottom:1px solid #aaaaaa;"  ><td  class="workflowData" style="padding:2px 5px;">'. $name .'</td> </tr>';
        } 
        $result.='  </table>';
        $result.='  </td>';
      }
      $result.= ' </tr>';
      $result.='  </table>';
      $result.=' </div>';
      }
    }
    return $result;
  }
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $statusList=SqlList::getList('Status');
    $firstStatus=key($statusList);
    $firstStatusName=current($statusList);
    $firstStatusFound=false;
    $search = 'val_' . $firstStatus . '_';
    
    // V6.2 control on existing way to exit first status (recorded) more annoying than real use : REMOVED
    /*foreach ($_REQUEST as $field=>$value) { // The first status (default=recorded) is seached in the workflow definition 
      if (substr($field,0,strlen($search))==$search) {
        $firstStatusFound=true;
        break;
      }
    }    
    if (!$firstStatusFound) { // First status is not found : so it will never be possible to quit this workflow ...
        $result.='<br/>' . i18n('msgFirstStatusMandatoryInWorkflow',array($firstStatusName)); 
    }*/ 
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    return ($result==""?"OK":$result);
    }
  
  /** =========================================================================
   * save data
   * @param void
   */
  public function save() {
    global $_REQUEST;
    
// MTY - LEAVE SYSTEM    
    if (isLeavesSystemActiv()) {
        $oldWfStatuses = $this->getWorkflowStatusList(-1,"id");
    }
// MTY - LEAVE SYSTEM    
    
    projeqtor_set_time_limit(300);
    
    if ($this->workflowUpdate and $this->workflowUpdate!="[     ]" and $this->workflowUpdate!="[      ]") {
      $old=$this->getOld();
      if (! $old->workflowUpdate or $old->workflowUpdate=="[      ]") {
        $this->workflowUpdate="[     ]";
      } else {
        $this->workflowUpdate="[      ]";
      }
    }
    $result = parent::save();   
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    // save detail (workflowstatus)
    $statusList=$this->getStatusList();
    $profileList=SqlList::getList('Profile');
    $ws=new WorkflowStatus();
    //$ws->purge("idWorkFlow='" . $this->id . "'");
    $oldArray=$this->getWorkflowstatusArray();
    foreach ($statusList as $statLineCode => $statLineValue) {
      foreach ($statusList as $statColumnCode => $statColumnValue) {
        if ($statLineCode!=$statColumnCode) {
          foreach ($profileList as $profileCode => $profileValue) {
            $oldVal=$oldArray[$statLineCode][$statColumnCode][$profileCode];
            $valName = 'val_' . $statLineCode . '_' . $statColumnCode . '_' . $profileCode;
            if (array_key_exists($valName,$_REQUEST)) {            
              if ($oldVal!=1) {
                $ws=new WorkflowStatus();
                $ws->idWorkflow=$this->id;
                $ws->idProfile=$profileCode;
                $ws->idStatusFrom=$statLineCode;
                $ws->idStatusTo=$statColumnCode;
                $ws->allowed=1;
                $ws->save();    
              }
            } else {
              if ($oldVal==1) {
                $crit=array('idWorkflow'=>$this->id,
                            'idProfile'=>$profileCode,
                            'idStatusFrom'=>$statLineCode,
                            'idStatusTo'=>$statColumnCode);
                $ws=SqlElement::getSingleSqlElementFromCriteria('WorkflowStatus', $crit);
                $ws->delete();
              }  
            }
          }   
        }     
      }
    }
    
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
        $newWfStatuses = $this->getWorkflowStatusList(-1,"id");

        // No difference between old status in workflow and new status in workflow
        // => Nothing else to do
        if (count(twoArraysObjects_diff($oldWfStatuses, $newWfStatuses))===0) { return $result; }
        // LEAVE TYPE
        // Search Leave types associated to the workflow
        $lvTypeList = LeaveType::getList(-1,$this->id);
        // If no leave type associated with this workflow => nothing else to do
        if (count($lvTypeList)===0) {return $result;}

        $alertLess="";
        $alertMore="";

        $whereLeaveTypeClause = "";
        foreach ($lvTypeList as $lvType) {
            $whereLeaveTypeClause .= $lvType->id.",";
        }
        $whereLeaveTypeClause = "idLeaveType in (".substr($whereLeaveTypeClause,0,-1).")";
        
        // ==================================
        // LESS STATUS IN NEW WORKFLOW STATUS        
        // ==================================
        // Search for less status in new workflow statuses
        $statusLess=array();    
        foreach($oldWfStatuses as $key=>$value) {
            if (!in_array($value,$newWfStatuses)) {
                $statusLess[$key] = $value;                
            }        
        }
        // If less status => Do something
        if (count($statusLess)>0) {
            // Search for Leaves that have :
            //    - a status is the less status list
            //          AND
            //    - a leave type associated with this workflow
            // STATUS
            $whereStatusClause = "";
            foreach($statusLess as $status) {
                $whereStatusClause .= $status->id.",";
            }
            $whereStatusClause = "idStatus in (".substr($whereStatusClause,0,-1).")";

            $whereClause = $whereStatusClause. " AND ". $whereLeaveTypeClause;

            // Search the leaves
            $leave = new Leave();
            $leaveList = $leave->getSqlElementsFromCriteria(null,false,$whereClause);
            // Leaves => set statusOutOfWorkflow = 1
            if (count($leaveList)>0) {
                $alertLess = "ChangeWorkflowWithLeavesHavingStatusOutOfWorkflow";
                // For each leaves that have lost status in the new workflow
                $queryWhere = "id in (";
                foreach($leaveList as $leave) {
                    $queryWhere .= $leave->id.",";
                }
                $queryWhere = substr($queryWhere,0,-1).")";
                $query = "update ".$leave->getDatabaseTableName()." set statusOutOfWorkflow=1 WHERE ".$queryWhere;
                SqlDirectElement::execute($query);
            }
        }
        
        // ==================================
        // MORE STATUS IN NEW WORKFLOW STATUS        
        // ==================================
        $statusMore=array();    
        foreach($newWfStatuses as $key=>$value) {
            if (!in_array($value,$oldWfStatuses)) {
                $statusMore[$value->id] = $value;                
            }        
        }
        // If more status => something to do
        if (count($statusMore)>0) {
            // WHERE STATUS
            $whereStatusClause = "";
            foreach($statusMore as $status) {
                $whereStatusClause .= $status->id.",";
            }
            $whereStatusClause = "idStatus in (".substr($whereStatusClause,0,-1).")";
                       
            // Search for Leaves that have :
            //    - a status is the more status list
            //          AND
            //    - statusOutOfWorkflow = 1 or statusSetLeaveChange = 1
            //          AND
            //    - a leave type associated with this workflow            
            $whereStatusOutSetChangeClause = " AND (statusOutOfWorkflow=1 OR statusSetLeaveChange=1)";            
            $whereClause = $whereStatusClause. " AND ". $whereLeaveTypeClause.$whereStatusOutSetChangeClause;
            // Search the leaves
            $leave = new Leave();
            $leaveList = $leave->getSqlElementsFromCriteria(null,false,$whereClause);
            // No Leave and no less alert => Nothing else to do
            if (count($leaveList)===0 and $alertLess=="") {return $result;}
            // Update Leave's statusOutOfWorkflow=0 and statusSetLeaveChange=0 with transition resynchronize with the status
            // For each leave : Has setXXXXLeave resynchronize with transition
            $leaveToChangeResynchronized = array();
            foreach($leaveList as $leave) {
                if ($leave->submitted==$statusMore[$leave->idStatus]->setSubmittedLeave AND
                    $leave->accepted==$statusMore[$leave->idStatus]->setAcceptedLeave AND
                    $leave->rejected==$statusMore[$leave->idStatus]->setRejectedLeave                        
                   )  {
                    array_push($leaveToChangeResynchronized, $leave->id);
                }
            }
            $lR = count($leaveToChangeResynchronized);
            if ($lR>0) {
                $queryWhere = "id in (";
                for($i=0; $i<$lR; $i++) {
                    $queryWhere .= $leaveToChangeResynchronized[$i].",";
                }
                $queryWhere = substr($queryWhere,0,-1).")";
                $query = "update ".$leave->getDatabaseTableName()." set statusOutOfWorkflow=0, statusSetLeaveChange=0 WHERE ".$queryWhere;
                SqlDirectElement::execute($query);
            }
            
            // For each leave : Has setXXXXLeave of the status change
            $leaveToChangeStatusSetLeaveChange = array();
            foreach($leaveList as $leave) {
                foreach ($leaveToChangeResynchronized as $leaveR) {
                    if ($leave->id == $leaveR) {continue;}
                }
                if (($leave->submitted==1 and $statusMore[$leave->idStatus]->setSubmittedLeave==0) OR
                    ($leave->submitted==0 and $statusMore[$leave->idStatus]->setSubmittedLeave==1)    
                   )  {
                    array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                    continue;
                }
                if (($leave->accepted==1 and $statusMore[$leave->idStatus]->setAcceptedLeave==0) OR
                    ($leave->accepted==0 and $statusMore[$leave->idStatus]->setAcceptedLeave==1)    
                   )  {
                    array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                    continue;
                }
                if (($leave->rejected==1 and $statusMore[$leave->idStatus]->setRejectedLeave==0) OR
                    ($leave->rejected==0 and $statusMore[$leave->idStatus]->setRejectedLeave==1)    
                   )  {
                    array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                    continue;
                }
                if (($leave->rejected==0 and $leave->accepted==0 and $leave->submitted==0 and 
                    ($statusMore[$leave->idStatus]->setRejectedLeave==1 or 
                     $statusMore[$leave->idStatus]->setAcceptedLeave==1 or
                     $statusMore[$leave->idStatus]->setSubmittedLeave==1))    
                   )  {
                    array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                    continue;
                }
            }
            $l = count($leaveToChangeStatusSetLeaveChange);
            if ($l>0) {
                $alertMore = "StatusSetTransitionLeaveHasChange";
                $queryWhere = "id in (";
                for($i=0; $i<$l; $i++) {
                    $queryWhere .= $leaveToChangeStatusSetLeaveChange[$i].",";
                }
                $queryWhere = substr($queryWhere,0,-1).")";
                $query = "update ".$leave->getDatabaseTableName()." set statusOutOfWorkflow=0, statusSetLeaveChange=1 WHERE ".$queryWhere;
                SqlDirectElement::execute($query);
            }
        }
        
        if ($alertLess!="" or $alertMore!="") {
            // Send Notification or Alert or email
            // Sender = User
            $receivers[0] = getSessionUser();            
            // Receiver = leaves admin
            $receivers[1] = getLeavesAdmin();
            
            $title = i18n("ChangesOnWorkflowHasImpactOnLeaves");
            $alertMore = ($alertMore==""?"":"".i18n("AND").$alertMore);
            $content = i18n($alertLess).($alertLess!=""?" ":"").$alertMore;
            $name = strtoupper(i18n("Workflow"))." - ".i18n("maintenanceOnLeavesRequired");
            sendNotification($receivers, $this, "WARNING", $title, $content, $name);        
        }
    }
// MTY - LEAVE SYSTEM    
    return $result;
  }
   
  public function copy() {
     $result=parent::copy();
     $new=$result->id;
     $ws=new WorkflowStatus();
     $crit=array('idWorkflow'=>$this->id);
     $lst=$ws->getSqlElementsFromCriteria($crit);
     foreach ($lst as $ws) {
       $ws->idWorkflow=$new;
       $ws->id=null;
       $ws->save();
     }
     
     Sql::$lastQueryNewid=$new;
     return $result;
  }
  
  /** 
   * Returns list of possible status for given object, depending on current user profile
   */
  public static function getAllowedStatusListForObject($obj) {
    $class=get_class($obj);
    if ($class=='TicketSimple') $class='Ticket';
    $idType='id' . $class . 'Type';
    $typeClass=$class . 'Type';
    $st=new Status();
    $table=$st->getSqlElementsFromCriteria(array('idle'=>'0'),null,null,'sortOrder asc',true);
    if (property_exists($obj,$idType) and property_exists($obj,'idStatus') and $obj->$idType and $obj->idStatus) {
      $allowedTable=array();
      $profile=getSessionUser()->getProfile($obj);
      $type=new $typeClass($obj->$idType,true);
      if (property_exists($type,'idWorkflow') ) {
        $ws=new WorkflowStatus();
        $crit=array('idWorkflow'=>$type->idWorkflow, 'allowed'=>1, 'idProfile'=>$profile, 'idStatusFrom'=>$obj->idStatus);
        $wsList=$ws->getSqlElementsFromCriteria($crit, false, null, null, true);
        foreach ($wsList as $ws) {
          $allowedTable['#'.$ws->idStatusTo]=new Status($ws->idStatusTo);
        }
      }
// MTY - LEAVE SYSTEM        
        $leaveSystemCond = true;
        if (isLeavesSystemActiv() and $class=='Leave') {
            if (isLeavesAdmin() or isManagerOfEmployee(getSessionUser()->id, $obj->idEmployee)) {
                $leaveSystemCond = false;                
            }
        }
        if ($leaveSystemCond) {
// MTY - LEAVE SYSTEM        
      $table=array_intersect_key($table, $allowedTable);
    }
    }
    return $table;
  }
  
// MTY - LEAVE SYSTEM
    public static function getAllowedStatusForObjectList($obj) {
        $statusList = self::getAllowedStatusListForObject($obj);
        $list = array();
        foreach($statusList as $status) {
            $list[$status->id] = $status->name;
}
        return $list;
    }
    
    /**
     * Get the list of From-To statuses of the workflow
     * @return array [statusFrom][statusTo]
     */
    public function getListStatusFromTo() {
        $lstStatus = $this->getWorkflowstatus();
        
        $theStatusList = array();
        foreach($lstStatus as $status) {
            if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
                $theStatusList[$status->idStatusFrom][$status->idStatusTo]=1;
            } else {
                if (!array_key_exists($status->idStatusTo, $theStatusList[$status->idStatusFrom])) {
                    $theStatusList[$status->idStatusFrom][$status->idStatusTo]=1;
                }
            }
        }
        return $theStatusList;
    }

     /**
      * Get the list of statuses of the workflow
      * @param integer $idle = -1 for idle = 0 or 1
      * @param $sortBy = Status's attribute to sort array with
      * @return Status[]
     */
    public function getWorkflowStatusList($idle=-1, $sortBy="sortOrder") {        
        $lstStatus = $this->getWorkflowstatus();
        $theStatusListId = array();
        foreach($lstStatus as $status) {
            if (!in_array($status->idStatusFrom, $theStatusListId)) {
                array_push($theStatusListId, $status->idStatusFrom);
            }
            if (!in_array($status->idStatusTo, $theStatusListId)) {
                array_push($theStatusListId, $status->idStatusTo);
            }
        }
        $l = count($theStatusListId);
        // No statuses for this workflow ?????
        if ($l===0) { return array(); }
        
        $whereClause = "";
        if ($idle!=-1) {
            $whereClause = "idle = $idle AND id in (";
        } else {
            $whereClause = "id in (";
        }
        for ($i=0;$i<$l; $i++) {
            $whereClause .= $theStatusListId[$i].',';
        }
        $whereClause = substr($whereClause, 0,-1) .")";
        
        $status = new Status();
        $theStatusList = $status->getSqlElementsFromCriteria(null,false,$whereClause);
        $theStatusListSorted = sortArrayOfModelObjectsByAField($theStatusList,$sortBy,true);
        return $theStatusListSorted;
    }
       
    public static function getWorkflowLeaveMngList($withClosed=false) {
        $wkfList = LeaveType::getWorkflowList(($withClosed?-1:0), -1);
        return $wkfList;
    }
    
    /**
     * Get the list of From-To statuses of the workflows which are dedicated to the Leave Management
     * @return array [statusFrom][statusTo]
     */
    public static function getLeaveMngListStatusFromTo() {
        $wkfList = self::getWorkflowLeaveMngList();
        $theStatusList = array();

        foreach($wkfList as $wkf) {
            $lstStatus = $wkf->getWorkflowstatus();

            foreach($lstStatus as $status) {
                if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
                    $theStatusList[$status->idStatusFrom][$status->idStatusTo]=1;
                } else {
                    if (!array_key_exists($status->idStatusTo, $theStatusList[$status->idStatusFrom])) {
                        $theStatusList[$status->idStatusFrom][$status->idStatusTo]=1;
                    }
                }
            }
        }
        return $theStatusList;
    }
    
    /**
     * Get the list of status of the workflows which are dedicated to the Leave Management
     * @return WorkflowStatus[] Object
     */
    static function getLeaveMngListStatus($orderBySortOrder=true) {
        $wkfList = self::getWorkflowLeaveMngList();
        $theStatusList = array();
        foreach($wkfList as $wkf) {
            $lstStatus = $wkf->getWorkflowstatus();

            foreach($lstStatus as $status) {
                if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
                    $theStatus = new Status($status->idStatusFrom);
                    $theStatusList[$status->idStatusFrom] = $theStatus;
                }
                if (!array_key_exists($status->idStatusTo, $theStatusList)) {
                    $theStatus = new Status($status->idStatusTo);
                    $theStatusList[$status->idStatusTo] = $theStatus;
                }
            }
        }
        if ($orderBySortOrder) {
            usort($theStatusList, function($a, $b)
                {
                    return strcmp($a->sortOrder, $b->sortOrder);
                }
            );            
        }
        return $theStatusList;
    }
// MTY - LEAVE SYSTEM
}?>