<?php
use PhpOffice\PhpSpreadsheet\Calculation\Database;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
require_once('_securityCheck.php');
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

class ConsolidationValidation extends SqlElement{
	public $id;
	public $idProject;
	public $idResource;
	public $revenue;
	public $validatedWork;
	public $realWork;
	public $realWorkConsumed;
	public $leftWork;
	public $plannedWork;
    public $margin;	
    public $validationDate;
    public $month;
	
	
	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id=NULL, $withoutDependentObjects=false) {

	}
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {}
	
	
	/** ==========================================================================
	 * Draw project table 
	 */
	static function drawProjectConsolidationValidation($idProject,$idProjectType,$idOrganization,$year,$month){
	  $lockedProjects=array();
	  $levels=array();
	  $canChangeValidation=true;
	  $month=(strlen($month)==1)?'0'.$month:$month;
	  $cons= new ConsolidationValidation();
	  $idProject=($idProject=='')?0:$idProject;
	  $idProjectType=($idProjectType=='')?0:$idProjectType;
	  $idOrganization=($idOrganization=='')?0:$idOrganization;
	  $lstProject=$cons->getVisibleProjectToConsolidated($idProject,$idProjectType,$idOrganization);
	  $projectsList=$lstProject[0];
	  $srtingProjectList=$lstProject[1];
	  $length=count($projectsList);
	  $concMonth=$year.$month;
	  $curUser=getSessionUser();
	  $prof=$curUser->idProfile;
	  $countLocked=0;
	  $c=0;
	  $proj=new Project();
	  $adminProjects=$proj->getAdminitrativeProjectList(true);
	  
	  //________ search projects with locked imputation ________//
      
	  $canLock=array();
	  if($srtingProjectList!=''){
	    $lockImputation=new LockedImputation();
	    $where="idProject in ($srtingProjectList) and (month <'".$concMonth."' or month>'".$concMonth."')";
	    $lstLockedImpAfterBefor=$lockImputation->getSqlElementsFromCriteria(null,null,$where);
	    
	    $where="idProject in ($srtingProjectList) and month ='".$concMonth."'";
	    $lstLockedImpThisMonth=$lockImputation->getSqlElementsFromCriteria(null,null,$where);
	    $canLock=$projectsList;
	    if(isset($projectsList)){
    	    foreach ($projectsList as $id=>$proj) {
    	      foreach ($lstLockedImpAfterBefor as $lockProjImpAfterBefor){
    	        if($lockProjImpAfterBefor->idProject==$proj->id){
    	          $lockedProjectsAfterBefor[$proj->id]=$lockProjImpAfterBefor->month;
    	          unset($canLock[$id]);
    	          continue;
    	        }
    	      }
    	      foreach ($lstLockedImpThisMonth as $lockProjImp){
    	        if($lockProjImp->idProject==$proj->id){
    	          $countLocked++;
    	          $lockedProjects[$proj->id]=$lockProjImp->month;
    	        }
    	      }
    	    }
	    }
	  }
      //----------------------------------------------------///
      
      //________ search habilitation and acces right for all projects ________//
	      $param='lockedImputation';
      $habLockedImputation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'lockedImputation'));
      if($habLockedImputation->rightAccess=='1'){
        $canLockString=array();
        foreach ($canLock as $projToLocked){
          $profAssPro=$curUser->getProfile($projToLocked);
          $habLockedImputationProj=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$profAssPro,'scope'=>'lockedImputation'));
          if($habLockedImputationProj->rightAccess!='1'){
            unset($canLock[$projToLocked->id]);
            continue;
          }
          $canLockString[]=$projToLocked->id;
        }
        $canLockString=implode(',', $canLockString);
        $lockedFunction=',\''.$canLockString.'\',\'All\',\''.$concMonth.'\');"';
      }
      
      $habValidationImputation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'validationImputation'));

      ///----------------------------------------------------///
      
	  //*** Header***//
	  $result  ='<div id="imputationValidationDiv" align="center" style="margin-top:20px;margin-bottom:30px; overflow-y:auto; width:100%;">';
	  $result .='  <table width="98%" style="margin-left:20px;margin-right:20px;border: 1px solid grey;background:white;">';
	  $result .='   <tr class="reportHeader">';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:20%;text-align:center;vertical-align:center;">'.i18n('Project').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colRevenue').'</td>';
	  $result .='     <td style="width:40%;border: 1px solid grey;border-right: 1px solid white;">';
	  $result .='      <table width="100%"><tr><td colspan="5" style="height:30px;text-align:center;vertical-align:center;">'.ucfirst (i18n('technicalWork')).'</td></tr>';
	  $result .='        <tr>
	                       <td style="border-top: 1px solid white;border-right: 1px solid white;width:20%;height:30px;text-align:center;vertical-align:center;">'.ucfirst (i18n('colWorkApproved')).'</td>';
	  $result .='          <td style="border-top: 1px solid white;border-right: 1px solid white;width:20%;height:30px;text-align:center;vertical-align:center;">'.ucfirst (i18n('totalReal')).'</td>';
	  $result .='          <td style="border-top: 1px solid white;border-right: 1px solid white;width:20%;height:30px;text-align:center;vertical-align:center;">'.ucfirst (i18n('colRealCons')).'</td>
	                       <td style="border-top: 1px solid white;border-right: 1px solid white;width:20%;height:30px;text-align:center;vertical-align:center;">'.i18n('colRemainToDo').'</td>
	                       <td style="border-top: 1px solid white;border-right: 1px solid white;width:20%;height:30px;text-align:center;vertical-align:center;">'.ucfirst (i18n('colWorkReassessed')).'</td>
	                     </tr>
	                   </table>';
	  $result .='     </td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colMargin').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:7%;text-align:center;vertical-align:center;">';
	  $result .='      <table style="width:100%"><tr>';
	  $result .='          <td colspan="2"> '.i18n('colBlocking').'</td></tr>';
	  if($habLockedImputation->rightAccess=='1'){
	   $result .='<tr>';
	   $result .='          <td style="height:32px;padding-left:20px;width:10%;cursor:pointer;">';
	   $mode='onclick="lockedImputation(\'UnLocked\'';
        $result .='            <div region="center" id="UnlockedImputation" '.$mode.$lockedFunction.' class="iconUnLocked32 iconUnLocked iconSize32" title="'.ucfirst(i18n('unlockAllProjects')).'" ></div>';
        $result .='          </td>';
        $result .='          <td style="height:32px;padding-right:5px;padding-top:5px;width:10%;cursor:pointer;">';
        $mode='onclick="lockedImputation(\'Locked\'';
        $result .='            <div region="center" id="lockedImputation" '.$mode.$lockedFunction.' class="iconLocked32 iconLocked iconSize32" title="'.ucfirst(i18n('lockAllProjects')).'"></div>';
	    $result .='           </td>';
	  }
	  $result .='      </tr>';
	  $result .='    </table>';
	  $result .='     </td>';
	  $result .='     <td colspan="2" style="border: 1px solid grey;height:60px;width:13%;text-align:center;vertical-align:center;">';
	  $result .='      <table width="100%"><tr style="height:20px;"><td width="100%;" colspan="2">'.i18n('menuConsultationValidation').'</td></tr>';
	  $result .='        <tr style="100%" >';
	  if($habValidationImputation->rightAccess=='1'){
	    $paddingTop="5";
	    $classMediumTextButtonInClass= "";
	    if(isNewGui()){
	      $paddingTop="38";
	      $classMediumTextButtonInClass= "mediumTextButton";
	    }
	  
	  $result .='          <td >
	                        <div id="buttonValidationAll" style="width:40%;float:right;padding-top:'.$paddingTop.'px;cursor:pointer;margin-top: 2px;" title="'.i18n('validatedAll').'" class=" '.$classMediumTextButtonInClass.' iconSubmitted32 iconSubmitted iconSize32"
	                             onClick="validateOrCancelAllConsolidation(\''.$srtingProjectList.'\',\'validaTionCons\',\''.$concMonth.'\');">
	                        </div>
	                       </td>';
	  $result .='          <td>
	                        <div id="buttonCancelAll" style="width:40%;height:32px;padding-top:5px;cursor:pointer;" title="'.i18n('cancelAll').'" class="iconUnsubmitted32 iconUnsubmitted iconSize32"
	                             onClick="validateOrCancelAllConsolidation(\''.$srtingProjectList.'\',\'cancelCons\',\''.$concMonth.'\');" >
	                        </div>
	                       </td>';
	 }
     $result .='        </tr>
                       </table>
                      </td>
	                 </tr>';
      $compt=0;
	  if(isset($projectsList)){
        for($i=0;$i<$length;$i++) {                 //*** Draw row for each  project ***//
          $compt++;
          //________ get informations ________//
          if(empty($projectsList[$i]))continue;
          $idCheckBox=$projectsList[$i]->id;
          $uniqueId=$concMonth.$projectsList[$i]->id;
          $lock=((isset($lockedProjects[$idCheckBox]))?$lockedProjects[$idCheckBox]:'');
          $lockBefor=((isset($lockedProjectsAfterBefor[$idCheckBox]))?$lockedProjectsAfterBefor[$idCheckBox]:'');
          $consValPproj=SqlElement::getSingleSqlElementFromCriteria("ConsolidationValidation",array("idProject"=>$projectsList[$i]->id,"month"=>$concMonth));
          $asSub=($projectsList[$i]->getSubProjectsList())?true:false;
          
          if($consValPproj->id!=''){  
            $reel=$consValPproj->realWork;
            $leftWork=$consValPproj->leftWork;
            $plannedWork=$consValPproj->plannedWork;
            $validatedWork=$consValPproj->validatedWork;
            $revenue=$consValPproj->revenue;
            $margin=$consValPproj->margin;
            $reelCons=$consValPproj->realWorkConsumed;
            $id=$projectsList[$i]->id;
            $clauseWhere="idProject=$id and month > '".$concMonth."'";
            $afterConsValidated=$consValPproj->getSqlElementsFromCriteria(null,null,$clauseWhere);
            if(!empty($afterConsValidated)){
              $canChangeValidation=false;
            }
          }else{                                                          //-------- validated -------- //               
            $lstPeProject=$projectsList[$i]->ProjectPlanningElement;
            $reel=$lstPeProject->realWork;
            $leftWork=$lstPeProject->leftWork;
            $plannedWork=$lstPeProject->plannedWork;
            $validatedWork=$lstPeProject->validatedWork;
            $revenue=($lstPeProject->revenue!='')?$lstPeProject->revenue:0;
            $margin=$validatedWork-$plannedWork;
            $reelCons=ConsolidationValidation::getReelWorkConsumed($projectsList[$i],$concMonth);
          }
          $profAss=$curUser->getProfile($projectsList[$i]);
          ///----------------------------------------------------///
          
          //________ draw by wbs ________//
          
          $wbs=$projectsList[$i]->ProjectPlanningElement->wbsSortable;
          $split=explode('.', $wbs);
          $level=0;
          $testWbs='';
          foreach($split as $sp) {
            $testWbs.=(($testWbs)?'.':'').$sp;
            if (isset($levels[$testWbs])) $level=$levels[$testWbs]+1;
          }
          $levels[$wbs]=$level;
          $tab="";
          for ($j=1; $j<=$level; $j++) {
            $tab.='&nbsp;&nbsp;&nbsp;';
          }
          ///----------------------------------------------------///
          
          //________ go to and css style ________//
          $classSub='Project';
          $goto='';
          $style='style="padding-left:15px;';
          if ( securityCheckDisplayMenu(null, $classSub) and securityGetAccessRightYesNo('menu'.$classSub, 'read', '')=="YES") {
            $goto=' onClick="gotoElement(\''.$classSub.'\',\''.htmlEncode($projectsList[$i]->id).'\');" ';
            $style.='cursor: pointer;';
          }
          ///----------------------------------------------------///
          
          $style.=(isNewGui() and isset($goto) and $goto!='')?'" class="classLinkName"':'"';
    	   $result .='   <tr id="tr_'.$uniqueId.'" onMouseOver="dojo.byId(\'tr_'.$uniqueId.'\').style.background=\'#DFDFDF\'"  onMouseOut="dojo.byId(\'tr_'.$uniqueId.'\').style.background=\'#FFFFFF\'">';
    	   $result .='    <td style="border-top: 1px solid black;border-right: 1px solid black;height:30px;text-align:left;vertical-center;">
    	                   <table>
    	                     <tr>
    	                       <td>'.$tab.'</td>
    	           	           <td><div  '.$style.' '.$goto.'>'.$projectsList[$i]->name.'</div></td>
    	                     </tr>
    	                   </table>
    	                  </td>';
    	   $result .='    <td style="border-top: 1px solid black;border-right: 1px solid black;height:30px;text-align:center;vertical-align:center;">
    	                     <input type="hidden" id="revenue_'.$uniqueId.'" name="revenue_'.$uniqueId.'" value="'.$revenue.'"/>
    	                     '.costFormatter($revenue).'
    	                  </td>';
    	   $result .='    <td style="border-top: 1px solid black;border-right: 1px solid black;height:30px;text-align:center;vertical-align:center;" >';
    	   $result .='     <table style="width:100%;height:100%" >';
    	   $result .='       <tr>';
    	   $result .='         <td style="border-right: 1px solid black;width:20%;text-align:center;vertical-align:center;">
    	                         <input type="hidden" id="validatedWork_'.$uniqueId.'" name="validatedWork_'.$uniqueId.'" value="'.$validatedWork.'"/>
    	                         '.workFormatter($validatedWork).'
    	                       </td>';
    	   $result .='         <td style="border-right: 1px solid black;width:20%;text-align:center;vertical-align:center;">
    	                         <input type="hidden" id="realWork_'.$uniqueId.'" name="realWork_'.$uniqueId.'" value="'.$reel.'"/>
  	                             '.workFormatter($reel).'
    	                       </td>';
    	   $result .='         <td style="border-right: 1px solid black;width:20%;text-align:center;vertical-align:center;">
    	                         <input type="hidden" id="realWorkConsumed_'.$uniqueId.'" name="realWorkConsumed_'.$uniqueId.'" value="'.(($reelCons!='')?$reelCons:0).'"/>
    	                         '.workFormatter($reelCons).'
    	                       </td>';
    	   $result .='         <td style="border-right: 1px solid black;width:20%;text-align:center;vertical-align:center;">
    	                         <input type="hidden" id="leftWork_'.$uniqueId.'" name="leftWork_'.$uniqueId.'" value="'.$leftWork.'"/>
                  	             '.workFormatter($leftWork).'
                        	   </td>';
    	   $result .='         <td style="width:20%;text-align:center;vertical-align:center;">
    	                         <input type="hidden" id="plannedWork_'.$uniqueId.'" name="plannedWork_'.$uniqueId.'" value="'.$plannedWork.'"/>
    	                         '.workFormatter($plannedWork).'
                               </td>';
    	   $result .='       </tr>';
    	   $result .='     </table>';
    	   $result .='    </td>';
    	   $result .='    <td style="border-top: 1px solid black;border-right: 1px solid black;height:30px;text-align:center;vertical-align:center;color:'.(($margin<0)?"red":"").'">
    	                    <input type="hidden" id="margin_'.$uniqueId.'" name="margin_'.$uniqueId.'" value="'.$margin.'"/>
    	                    '.workFormatter($margin).'
    	                        </div>
    	                  </td>';
    	   $result .='     <td style="border-top: 1px solid black;border-right: 1px solid black;height:30px;text-align:center;vertical-align:center;">';
    	   $result .='       <div style="color:margin:2px 0px 2px 2px;" id="lockedDiv_'.$uniqueId.'" name="lockedDiv_'.$uniqueId.'" dojoType="dijit.layout.ContentPane" region="center">';
    	   $result .=          ConsolidationValidation::drawLockedDiv($uniqueId,$concMonth,$lock,$lockBefor,$asSub,$profAss, (isset($adminProjects[$idCheckBox]))?$idCheckBox:false /*,$consValPproj*/);
    	   $result .='       </div>';
    	   $result .='    </td>';
    	   $result .='    <td style="border-top: 1px solid black;border-right: 1px solid black;height:30px;text-align:center;vertical-align:center;">';
    	   $result .='       <div style="margin:2px 2px 2px 2px;" id="validatedDiv_'.$uniqueId.'" name="validatedDiv_'.$uniqueId.'" dojoType="dijit.layout.ContentPane" region="center">';
           $result .=          ConsolidationValidation::drawValidationDiv($consValPproj,$canChangeValidation,$uniqueId,$concMonth,$asSub,$profAss);
           $result .='       </div>';
           $result .='    </td>';
           $result .='     <input type="hidden" id="validatedLine'.$idCheckBox.'" name="'.$uniqueId.'" value="0"/>';
           $result .='   </tr>';
    	  }
	  }else{   // no project 
	    $result .='   <tr>';
	    $result .='    <td colspan="10">';
	    $result .='    <div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;border-right: 1px solid grey;">'.i18n('noDataFound').'</div>';
	    $result .='    </td>';
	    $result .='   </tr>';
	  }
	  $result .='     <input type="hidden" id="countLine" name="countLine" value="'.$compt.'"/>';
	  $result .='  </table>';
	  $result .='</div>';
	  
	  echo $result;
	}
	
	
	/** ==========================================================================
	 * Draw table with icon to locked/unlocked imputation of  project 
	 * @return table
	 */
	static function drawLockedDiv($proj,$month,$lock,$lockBefor,$asSub,$prof, $isAdmin /*,$consValPproj*/){
	    $habLockedImputation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'lockedImputation'));
	    $mode=($lock=='')?"'Locked'":"'UnLocked'";
	    $right=/*($consValPproj->id=='')?*/$habLockedImputation->rightAccess/*:'2'*/;
        $functionLocked=($right=='1' and !$isAdmin /*and $consValPproj->id==''*/ and ($lockBefor=='' or $lock!=''))?'onclick="lockedImputation('.$mode.',\''.$proj.'\',\'false\',\''.$month.'\',\''.$asSub.'\');"':'';
	    $alreadyMonthLockded=($lockBefor!='')?getMonthName(substr($lockBefor,-2)):'';
        if($lockBefor!='')$titleAlredyLock=i18n('alreadyLock',array(lcfirst ($alreadyMonthLockded),substr($lockBefor, 0,-2)));
	    $result ='  <table  style="width:100%;">';
	    $result .='    <tr>';
	    $result .='      <td  style="padding-left:33%;width:50%;">';
	    if($lock==''){ // unlocked
	      $title=($lockBefor!='')?$titleAlredyLock:i18n('colLockProject');
	      if ($isAdmin) {
	        $p=new Project($isAdmin,true);
	        $title=i18n("colType")." '".SqlList::getNameFromId('Type', $p->idProjectType)."'";
	      }
	      if($lockBefor=='' and !$isAdmin)$style=(($right=='1' /*and $consValPproj->id==''*/)?'style="cursor:pointer;"':'style="cursor: not-allowed;"');
	      else $style='style="cursor: not-allowed;-webkit-filter:saturate(0);-moz-filter:saturate(0);filter:saturate(0);"';
	      $result .='      <div '.$style.'  id="UnlockedImputation_'.$proj.'"  '.$functionLocked.' class="iconUnLocked32 iconUnLocked iconSize32" title="'.$title.'" ></div>';
	    
	    }else{   //locked
	      
	      if(1 or $lockBefor=='')$style=(($right=='1' /*and $consValPproj->id==''*/)?'style="margin-left:5px;cursor:pointer;"':'style="cursor: not-allowed;margin-left:5px;"');
	      else $style='style="margin-left:5px;cursor: not-allowed;-webkit-filter:saturate(0);-moz-filter:saturate(0);filter:saturate(0);"';
	      $result .='      <div '.$style.' id="lockedImputation_'.$proj.'" '.$functionLocked.' class="iconLocked32 iconLocked iconSize32" title="'.ucfirst(i18n('colUnlockProject')).'"></div>';
	    }
	    $result .='     <input type="hidden" id="projHabilitationLocked_'.substr($proj, 6).'" name="projHabilitationLocked_'.substr($proj, 6).'" value="'.$right.'"/>';
	    $result .='     </td>';
	    $result .='   </tr>';
	    $result .='  </table>';
	  return $result;
	}
	
	/** ==========================================================================
	 * Draw table with icon to validated/cancel consolidation of  project
	 * @return table
	 */
	
	static function drawValidationDiv($consValPproj,$canChangeValidation,$uniqueId,$concMonth,$asSub,$prof){
	  $habValidationImputation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'validationImputation'));
	  $right=$habValidationImputation->rightAccess;
	  $result="";
	  if($consValPproj->id!=''){  //consolidation  exist 
	    $resource=new User ($consValPproj->idResource);
	    $resourceName=$resource->name;
	    $validatedDate=$consValPproj->validationDate;
	    $result .='      <table style="width:100%;">';
	    $result .='        <tr>';
        $validFunct=($right=='1' and $canChangeValidation)?'onClick="saveOrCancelConsolidationValidation(\''.$uniqueId.'\',\''.$concMonth.'\',\''.$asSub.'\');"':'';
        $result .='          <td style="width:60px;max-width:60px;padding-left:20px;height:32px;'.(($right==1  and $canChangeValidation)?"cursor:pointer;":"cursor:not-allowed;").'" ><div style="" id="buttonCancel_'.$uniqueId.'" '.$validFunct.' >
                             '.formatIcon('Submitted', 32,i18n('cancelConsolidation'),false,true).'
                           </div></td>';
	    $result .='          <td style="">'. i18n('validatedWork', array($resourceName, htmlFormatDate($validatedDate))).'</td>';
	    if($right=='1'  and !$canChangeValidation)$right='2';

	  }else{
	    $result .='      <table style="width:100%;">';
	    $result .='        <tr>';
	    $validFunct=($right=='1')?'onClick="saveOrCancelConsolidationValidation(\''.$uniqueId.'\',\''.$concMonth.'\',\''.$asSub.'\');"':'';

	    $result .='          <td style="width:60px;max-width:60px;padding-left:20px;'.(($right==1)?"cursor:pointer;":"cursor:not-allowed;").'" ><div style="" id="buttonValidation_'.$uniqueId.'" '.$validFunct.' >
	                           '.formatIcon('Unsubmitted', 32, i18n('validateConsolidation'),false,true).'
	                         </div></td>';
	    $result .='          <td style="padding-left:5px;">'.i18n('unvalidatedWorkPeriod').'</td>';
	  }
	  $result .='          <input type="hidden" id="projHabilitationValidation_'.substr($uniqueId, 6).'" name="projHabilitationValidation_'.substr($uniqueId, 6).'" value="'.$right.'"/>';
	  $result .='          </tr>';
	  $result .='        </table>';
	  return $result;
	}
	
	/** ==========================================================================
	 * Get Visible Project
	 * @return two list of project (first list of object, second list of string id)
	 */
	static  function getVisibleProjectToConsolidated ($idProject,$idProjectType,$idOrganization,$report=false) {
	  $currentUser=new User(getCurrentUserId());
	  $visibleProject=getVisibleProjectsList();
	  if($report){
    	  $user=getSessionUser();
    	  $visibleProject=$user->getVisibleProjects();
    	  $countProj=count($visibleProject);
    	  $cProj=0;
    	  $stringProj="(";
    	  foreach ($visibleProject as $idP=>$nameProj){
    	    $cProj++;
    	    if($cProj==$countProj){
    	      $stringProj.=$idP.')';
    	    }else{
    	      $stringProj.=$idP.',';
    	    }
    	  }
    	  $visibleProject=$stringProj;
	  }
	  $where="id in $visibleProject ";
	  $result=array();
	  $lstProject=array();
	  $proj= new Project();
	  //$lstProject=array();
	  if($idProject==0 and $idProjectType==0 and $idOrganization==0){ // no list is selected
	    $where.="order by sortOrder";
	    $lstProject=$proj->getSqlElementsFromCriteria(null,null,$where);
	  }else if($idProjectType!=0 or $idOrganization!=0){  // project or organization whas select 
	    $critArray=array();
	    if($idProject!=0){
	      $where=ConsolidationValidation::clauseWhithSubProj($idProject);
	    }
	    ($idProjectType!=0 )?$where.=" and idProjectType=$idProjectType ":"";
 	    ($idOrganization!=0)?$where.=" and idOrganization=$idOrganization ":"";
 	    $where.="order by sortOrder";
	    $lstProject=$proj->getSqlElementsFromCriteria(null,null,$where);
	  }else{   // project was select 
        $where=ConsolidationValidation::clauseWhithSubProj($idProject);
        $where.="order by sortOrder";
        $lstProject=$proj->getSqlElementsFromCriteria(null,null,$where);
	  }
	  $result[0]=$lstProject;
	  $stringProjectList="";
	  foreach ($lstProject as $proj){
	    if($stringProjectList==""){
	      $stringProjectList.=$proj->id;
	    }else{
	      $stringProjectList.=','.$proj->id;
	    }
	  }
	  $result[1]=$stringProjectList;
	  return $result;
	}
	
	/** ==========================================================================
	 * Get Visible Project
	 * @return a clause 
	 */
	
	static function clauseWhithSubProj($idProject){
	  $lstProj=$idProject;
	  $proj=new Project($idProject);
	  $sub=$proj->getRecursiveSubProjectsFlatList();
	  foreach ($sub as $id=>$subproj){
	    $lstProj.=",".$id;
	  }
	 return "id in ($lstProj)";
	}
	
	/** ==========================================================================
	 * Get real work consumed this month 
	 * @return real work 
	 */
	static function getReelWorkConsumed ($project,$month) {
	  $work=new Work();
// 	  if($project->getSubProjectsList()){
// 	    $sub=$project->getSubProjectsList();
// 	    $subList=$project->id.','.implode(',', array_keys($sub));
// 	    $where="idProject in ($subList) and month = '".$month."' ";
// 	  }else{
// 	    $where="idProject = $project->id and month = '".$month."' ";
// 	  }
	  $lstProj=$project->getRecursiveSubProjectsFlatList(false,true);
	  $where="idProject in ".transformListIntoInClause($lstProj)." and month = '".$month."' ";
	  $reelCons=$work->sumSqlElementsFromCriteria('work',null,$where);
	  return $reelCons;
	}

}