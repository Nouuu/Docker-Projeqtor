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
 * RiskType defines the type of a risk.
 */ 
require_once('_securityCheck.php');
class PlannedWorkManual extends GeneralWork {

	 public $period;
	 public $idInterventionMode;
   public $inputUser;
   public $inputDateTime;
   public $idWork;
   public $idPlannedWork;
   private static $_size='22';
   
   public static $_cacheColor=array();
   
	 private static $_colCaptionTransposition = array(
	     'workDate'=>'date'
	 );
	 private static $_fieldsAttributes=array(
	     "day"=>"hidden,noExport,noImport",
	     "week"=>"hidden,noExport,noImport",
	     "month"=>"hidden,noExport,noImport",
	     "year"=>"hidden,noExport,noImport",
	     "dailyCost"=>"hidden,noExport,noImport"
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  // ================================================================================================
  //
  // ================================================================================================
  
  public function control(){
    $result="";
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function deleteControl() {
    $result='';
   
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  
  public function delete() {
    $old=$this->getOld();
    $result=parent::delete();
    if ($old->refType) {
      // Save planned work depending on work type
      $old->saveWork();
    }
    return $result;
  }
  
  public function save() {
    $old=$this->getOld();
    if ($this->refType) {
      $refType=$this->refType;
      $item=new $refType($this->refId);
      $this->idProject=$item->idProject;
    }
    $result=parent::save();
    if ($this->refType) {
      // Save planned work depending on work type
      $this->saveWork();
    }
    if ($old->refType and ($old->refType!=$this->refType or $old->refId!=$this->refId)) {
      $old->work=0;
      $old->saveWork();
    }
    return $result;
  }
  public function simpleSave() {
    return parent::save();
  }
  
  function saveWork() {
    $type=self::getWorkType();
    $wClass=($type=='real')?'Work':'PlannedWork';
    $wField='id'.$wClass;
    $w=new $wClass();
    $critAss=array('idResource'=>$this->idResource, 'refType'=>$this->refType, 'refId'=>$this->refId);
    $crit=array('workDate'=>$this->workDate, 'idResource'=>$this->idResource, 'refType'=>$this->refType, 'refId'=>$this->refId);
    $sum=$this->sumSqlElementsFromCriteria('work', $crit);
    $workList=$w->getSqlElementsFromCriteria($crit,true);
    if (count($workList)>1) {
      traceLog("ERROR - PlannedWorkManuel::save() : found more than one $wClass");
      traceLog($crit);
    }
    $work=reset($workList);
    if ($sum==0) {
      if ($work->id) {
        $workResult=$work->delete();
        $this->$wField=null;
      }
    } else {
      $ass=new Assignment();
      $assList=$ass->getSqlElementsFromCriteria($critAss,true);
      if (count($assList)>1) {
        traceLog("ERROR - PlannedWorkManuel::save() : found more than one Assignment");
        traceLog($critAss);
      }
      $ass=reset($assList);
      if (!$ass->id) {
        $ass->idProject=$this->idProject;
        $ass->manual=1;
        $assResult=$ass->save();
      }
      $work->work=$sum;
      $work->idProject=$this->idProject;
      $work->setDates($this->workDate);
      $work->idAssignment=$ass->id;
      $work->manual=1;
      $workResult=$work->save();
      if (!$this->idAssignment or $this->idAssignment!=$ass->id or !$this->$wField or $this->$wField!=$work->id) {
        if (!$this->idAssignment or $this->idAssignment!=$ass->id) {
          $this->idAssignment=$ass->id;
        } 
        if (!$this->$wField or $this->$wField!=$work->id) {
          $this->$wField=$work->id;
        }
        $this->simpleSave();
      }
    }
    if ($this->idAssignment) {
      $this->updateAssignment();
    }
  }
  public function updateAssignment() {
    if (!$this->idAssignment) return;
    $ass=new Assignment($this->idAssignment);
    $crit=array('refType'=>$this->refType, 'refId'=>$this->refId, 'idResource'=>$this->idResource);
    $pw=new PlannedWork();
    $w=new Work();
    $realwork=$w->sumSqlElementsFromCriteria('work', $crit);
    $plannedwork=$pw->sumSqlElementsFromCriteria('work', $crit);
    $realStart=$w->getMinValueFromCriteria('workDate', $crit,null,true);
    $realEnd=$w->getMaxValueFromCriteria('workDate', $crit);
    $plannedStart=$pw->getMinValueFromCriteria('workDate', $crit,null,true);
    $plannedEnd=$pw->getMaxValueFromCriteria('workDate', $crit);
    $ass->assignedWork=$plannedwork+$realwork;
    $ass->leftWork=$plannedwork;
    $ass->realWork=$realwork;
    $ass->plannedStartDate=$plannedStart;
    $ass->plannedEndDate=$plannedEnd;
    $ass->realStartDate=$realStart;
    $ass->realEndDate=$realEnd;
    $ass->manual=1;
    $resAss=$ass->save();
  }
  
  
  public function getMenuClass() {
    return "menuActivity";
  }
  
  public static function getWorkType() {
    $param=Parameter::getGlobalParameter('plannedWorkManualType');
    if ($param=='real') return 'real';
    else return 'planned';
  }
  
  public static function getManageCapacity($resource) {
    // First version, not dependant of resource
    // 'NO';         // half day is O.5, do not use capacity of resource (always considered as 1)
    // 'LIMIT';      // half day is always 0.5, but use capacity of resource to limit (resource with capacity of 0.8, cannot have 2 half days same day)
    // 'DURATION';   // half day is half capcacity of resource, and use capacity of resource to limit (resource with capacity of 0.8, can have 2 half days same day, will reserve 0.4 each)
    $manageCapacity=Parameter::getGlobalParameter('manageCapacityForIntervention');
    if (!$manageCapacity) return 'LIMIT';
    else return $manageCapacity;
  }
  public static function drawLine($scope, $idResource, $year, $month, $refType, $refId, $readonly=false) {
    SqlElement::$_cachedQuery['WorkPeriod']=array();
    // draw line for given resource and month
    // if $idAssignment is not null, we are on update of existing assignment
    // if $idActivity is not null, we are on creation of new assignment (so no existing data to retreive)
    $month=intval($month);
    $monthWithZero=(($month<10)?'0':'').$month;
    $lastDay=lastDayOfMonth($month,$year);
    $lastDayWithZero=(($lastDay<10)?'0':'').$lastDay;
    $max=($scope=='intervention')?$lastDay:31;
    $size=self::$_size;
    $midSize=($size-1)/2;
    $letterSize=($size/2)-2;
    $crit="idResource=$idResource and workDate>='$year-$monthWithZero-01' and workDate<='$year-$monthWithZero-$lastDayWithZero'";
    $critWork=$crit;
    if ($refType and $refId) {
      $crit.=" and ( (refType='$refType' and refId=$refId) or refType is null)";
    }
    $adminProject=Project::getAdminitrativeProjectList(true,true);
    $w=new Work();
    $wList=$w->sumSqlElementsFromCriteria('work', null, $critWork." and manual=0", 'workDate, idProject');
    $realWork=array();
    $realWorkAdmin=array();
    if ($wList) {
      foreach ($wList as $sum) {
        if (isset($adminProject[$sum['idproject']])) {
          if (isset($realWorkAdmin[$sum['workdate']])) $realWorkAdmin[$sum['workdate']]+=$sum['sumwork'];
          else $realWorkAdmin[$sum['workdate']]=$sum['sumwork'];
        } 
        if (isset($realWork[$sum['workdate']])) $realWork[$sum['workdate']]+=$sum['sumwork'];
        else $realWork[$sum['workdate']]=$sum['sumwork'];
      }
    }
    $pwm=new PlannedWorkManual();
    $lstPwm=$pwm->getSqlElementsFromCriteria(null,null,$crit);
    $exist=array();
    $resObj=new ResourceAll($idResource);
    $manageCapacity=self::getManageCapacity($resObj); // Take into account capacity of resource
    foreach ($lstPwm as $pwm) {
        if (!isset($exist[$pwm->workDate])) $exist[$pwm->workDate]=array();
        $exist[$pwm->workDate][$pwm->period]=array('refType'=>$pwm->refType,'refId'=>$pwm->refId,'mode'=>$pwm->idInterventionMode);
    }
    for ($i=1;$i<=$max;$i++) {
      if ($i>$lastDay) {
        echo '<td style="border:0;background-color:transparent"></td>'; 
        continue;
      }
      $locked=false;
      $validated=false;
      $colorAM='#ffffff';
      $colorPM='#ffffff';
      $letterAM='';
      $letterPM='';
      $date=$year.'-'.(($month<10)?'0':'').$month.'-'.(($i<10)?'0':'').$i;
      if (isOffDay($date,$resObj->idCalendarDefinition)) {
        $colorAM="#d0d0d0";
        $colorPM="#d0d0d0";
      }
      $week=weekFormat($date);
      $week=substr($week, 0,4).substr($week, 5,7);
      $workPeriod=SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', array('idResource'=>$idResource,'periodValue'=>$week));
      if($workPeriod->validated or $workPeriod->submitted){
        $validated=true;
        $locked=true;
      }
      $capacity=1;
      if ($manageCapacity=='LIMIT' or $manageCapacity=='DURATION') $capacity=$resObj->getCapacityPeriod($date);
      $halfDayDuration=0.5;
      if ($manageCapacity=='DURATION') $halfDayDuration=$capacity/2;
      $real=(isset($realWork[$date]))?$realWork[$date]:0;
      $realAdmin=(isset($realWorkAdmin[$date]))?$realWorkAdmin[$date]:0;
      if (($capacity-$real)<$halfDayDuration) $locked='ALL';
      if (isset($exist[$date])) {
        foreach (array('AM','PM') as $period) {
          if (isset($exist[$date][$period])) {
            if ( (($capacity-$real)<(2*$halfDayDuration)) ) {
              if ($period=='AM') {
                if ($locked=='AM') $locked=false;
                else $locked='PM';  
              } else {
                if ($locked=='PM') $locked=false;
                else $locked='AM';
              }
            }
            $type=$exist[$date][$period]['refType'];
            $id=$exist[$date][$period]['refId'];
            $mode=$exist[$date][$period]['mode'];
            $colorName='color'.$period;
            if ($type and $id) $$colorName=self::getColor($type,$id);
            $letterName='letter'.$period;
            if ($mode) $$letterName=SqlList::getFieldFromId('InterventionMode', $mode, 'letter',false);
          }
        }
      }
      echo '<td style="border:1px solid #a0a0a0; position:relative">';
      echo '<table style="width:100%;height:100%">';
      $color=getForeColor($colorAM);
      $cursorAM=($readonly or $locked=='AM' or $locked=='ALL')?"normal":"pointer";
      $cursorPM=($readonly or $locked=='PM' or $locked=='ALL')?"normal":"pointer";
      $allowDouble=true;
      if ($capacity-$real-$halfDayDuration<$halfDayDuration) $allowDouble=false;
      $onClickAM=($readonly or $locked=='AM' or $locked=='ALL')?'onClick="selectInterventionNoCapacity();"':'onClick="selectInterventionDate(\''.$date.'\',\''.$idResource.'\',\'AM\','.(($allowDouble)?'true':'false').',event);"';
      $onClickPM=($readonly or $locked=='PM' or $locked=='ALL')?'onClick="selectInterventionNoCapacity();"':'onClick="selectInterventionDate(\''.$date.'\',\''.$idResource.'\',\'PM\','.(($allowDouble)?'true':'false').',event);"';
      echo '<tr style="height:'.$midSize.'px;"><td '.$onClickAM.' style="cursor:'.$cursorAM.';width:100%;background:'.$colorAM.';border-bottom:1px solid #e0e0e0;position:relative;text-align:center;"><div style="max-height:'.$midSize.'px;width:100%;overflow-hidden;font-size:'.$letterSize.'px;position:absolute;top:-1px;color:'.$color.';">'.$letterAM.'</div></td></tr>';
      $color=getForeColor($colorPM);
      echo '<tr style="height:'.$midSize.'px;"><td '.$onClickPM.' style="cursor:'.$cursorPM.';width:100%;background:'.$colorPM.';border:0;position:relative;text-align:center;"><div style="max-height:'.$midSize.'px;width:100%;overflow-hidden;font-size:'.$letterSize.'px;position:absolute;top:-1px;color:'.$color.';">'.$letterPM.'</div></td></tr>';
      echo '</table>';
      $leftAdmin=0;
      if ($real and ($real-$realAdmin)>0) {
        $height=intval($size*($real-$realAdmin)/(($capacity)?$capacity:(($resObj->capacity)?$resObj->capacity:1)));
        if ($height>$size) $height=$size;
        $background = 'background-color:#202020;opacity:0.5';
        //echo '<div style="pointer-events: none;position:absolute;top:0;'.$background.'; height:'.$height.'px;width:'.$size.'px"> </div>';
        echo '<div style="pointer-events: none;position:absolute;top:0;'.$background.'; width:'.$height.'px;height:'.$size.'px"> </div>';
        $leftAdmin=$height;
      }
      if ($realAdmin) {
        $height=intval($size*$realAdmin/(($capacity)?$capacity:$resObj->capacity));
        if ($height>$size) $height=$size;
        $background = 'background-color:#3d668f;opacity:1';
        //echo '<div style="pointer-events: none;position:absolute;top:0;'.$background.'; height:'.$height.'px;width:'.$size.'px"> </div>';
        // Title will not be shown as pointer-event is set to none (and this is important)
        echo '<div title="absence" style="left:'.($leftAdmin).'px;pointer-events: none;position:absolute;top:0;'.$background.'; width:'.$height.'px;height:'.$size.'px"></div>';
      }
      if ($validated) {
        //$positionGrid=($totalRemplissage<100)?$totalRemplissage:100;
        $background = 'repeating-linear-gradient(-45deg,#505050,#505050 2px,transparent 2px,transparent 7px);#00BFFF';
        echo '<div style="position:absolute;top:0;background:'.$background.'; height:'.$size.'px;width:'.$size.'px"> </div>';
      }
      echo '</td>';
    }
  }
  /**
   * 
   * @param unknown $scope 'intervention' or 'assignment'
   * @param unknown $resourceList
   * @param unknown $monthsList
   */
  public static function drawTable($scope, $resourceList, $monthList, $refObj, $readonly=false) {
    if ($scope=='assignment') {
      if (is_array($resourceList) and count($resourceList)>1) { 
        echo "ERROR - Only one resource for assignment mode";
        exit;
      }
      if (! is_array($monthList) and count($monthList)>1) {
        echo "ERROR - monthList must be a list for assignment mode";
        exit;
      } 
      if (! $refObj) {
        echo "ERROR - refObj (refype#refId) must be a set for assignment mode";
        exit;
      } 
      $split=explode('#',$refObj);
      $refType=$split[0];
      $refId=$split[1];
    } else if ($scope=='intervention') {
      if (is_array($monthList) and count($monthList)>1) {
        echo "ERROR - Only one month for intervention mode";
        exit;
      }
      if (! is_array($resourceList) and count($resourceList)>1) {
        echo "ERROR - Resource must be a list for intervention mode";
        exit;
      }
    } else {
      echo "ERROR - invalid parameters";
    }
    $size=self::$_size;
    
    if ($scope=='intervention') {
      $nameWidth=150;
      $monthYear=(is_array($monthList))?$monthList[0]:$monthList;
      $monthYear=str_replace('-','',$monthYear);
      $year=substr($monthYear,0,4);
      $month=substr($monthYear,4);
      $nbDays=lastDayOfMonth(intval($month),$year);
      echo '<table>';
      echo '<tr>';
      echo '<td class="reportTableHeader" rowSpan="2" style="width:'.$nameWidth.'px">'.i18n('menuResource').'</td>';
      echo '<td class="reportTableHeader" colspan="'.$nbDays.'">'.getMonthName($month).' '.$year.'</td>';
      echo '</tr>';
      echo '<tr >';
      for ($i=1;$i<=$nbDays;$i++) {
        $date=$year.'-'.$month.'-'.(($i<10)?'0':'').$i;
        if (isOffDay($date)) $classDay="reportTableHeader";
        else $classDay="noteHeader";
        echo '<td class="'.$classDay.'" style="padding:0;font-weight:normal;width:'.$size.'px">'.$i.'</td>';
      }
      echo '</tr>';
      $resourceListName =  array_flip($resourceList);
      foreach ($resourceListName as $idName=>$idResourceListName){
        $nameRes=SqlList::getNameFromId('Resource', $idName);
        if ($nameRes==$idName) {
          unset($resourceListName[$idName]);
        } else {
          $resourceListName[$idName]=$nameRes;
        }
      }
      $resourceListName = new ArrayObject($resourceListName);
      $resourceListName->asort();
      foreach ($resourceListName as $idRes=>$nameRes) {
        echo '<tr style="height:'.$size.'px">';
        echo '<td class="noteHeader" style="width:'.$nameWidth.'px;"><div style="white-space:nowrap;max-width:'.$nameWidth.'px;max-height:'.$size.'px;overflow:hidden;">'.$nameRes.'</div></td>';
        self::drawLine($scope, $idRes, $year, $month, null, null, $readonly);
        echo '<tr>';
      }
      echo '</table>';
    } else {
      $nameWidth=150;
      $nbDays=31;
      $idRes=(is_array($resourceList))?$resourceList[0]:$resourceList;
      echo '<table>';
      echo '<tr>';
      echo '<td class="reportTableHeader" rowSpan="2" style="width:'.$nameWidth.'px">'.i18n('months').'</td>';
      echo '<td class="reportTableHeader" colspan="'.$nbDays.'">'.i18n('sectionRepartitionMonthly').'</td>';
      echo '</tr>';
      echo '<tr >';
      for ($i=1;$i<=$nbDays;$i++) {
        echo '<td class="noteHeader" style="width:'.$size.'px">'.$i.'</td>';
      }
      echo '</tr>';
      foreach ($monthList as $monthYear) {
        $monthYear=str_replace('-','',$monthYear);
        $year=substr($monthYear,0,4);
        $month=substr($monthYear,4);
        echo '<tr style="height:'.$size.'px">';
        echo '<td class="noteHeader" style="width:'.$nameWidth.'px"><div style="white-space:nowrap;max-width:'.$nameWidth.'px;max-height:'.$size.'px;overflow:hidden;">'.getMonthName($month).' '.$year.'</div></td>';
        self::drawLine($scope, $idRes, $year, $month, $refType, $refId, $readonly);
        echo '<tr>';
      }
      echo '</table>';            
    }
    $listR=(is_array($resourceList))?implode(',',$resourceList):$resourceList;
    $listM=(is_array($monthList))?implode(',',$monthList):$monthList;
    echo '<input type="hidden" id="plannedWorkManualInterventionSize" value="'.self::$_size.'" style="background:#ffe0e0"/>';
    echo '<input type="hidden" id="plannedWorkManualInterventionResourceList" value="'.$listR.'" style="background:#ffe0e0"/>';
    echo '<input type="hidden" style="width:500px;background:#ffe0e0" id="plannedWorkManualInterventionMonthList" value="'.$listM.'" />';
    
  }
  
  public static function drawActivityTable($idProject=null,$monthYear=null,$readonly=false) {
    $keyDownEventScript=NumberFormatter52::getKeyDownEvent();
    if($idProject){
      if(is_array($idProject)){
        $listProj=array();
        foreach ($idProject as $proj){
          $myProj = new Project($proj);
          array_push($listProj, $myProj->getRecursiveSubProjectsFlatList(false,true));
        }
      }else{
        $myProj = new Project($idProject);
        $listProj = $myProj->getRecursiveSubProjectsFlatList(false,true);
      }
      $listProj=transformListIntoInClause($listProj);
      $where = " idPlanningMode = 23 and idle = '0' and idProject in $listProj" ;
      $order = null;
    }else{
      $user = getSessionUser();
      $listProj = transformListIntoInClause($user->getVisibleProjects());
      $where = " idPlanningMode = 23 and idle = '0' and idProject in $listProj" ;
      $order = " wbs asc";
    }
    $pe=new PlanningElement();
    $list=$pe->getSqlElementsFromCriteria(null,null,$where,$order);
    $nameWidth=250;
    $idWidth=20;
    $nbDays=31;
    $year=null;
    $size=self::$_size;
    $midSize=($size-1)/2;
    $projList=array();
    if ($monthYear) {
      $monthYear=str_replace('-','',$monthYear);
      $year=substr($monthYear,0,4);
      $month=substr($monthYear,4);
      $nbDays=lastDayOfMonth(intval($month),$year);
    }
    $tabRefId = array();
    foreach ($list as $val){
      $tabRefId[]=$val->refId;
    }
    if(strlen($monthYear)==5){
      $monthYear = substr_replace($monthYear,'0',4,-1);
    }
    $where= " month='$monthYear' and refType='Activity' and refId in ".transformValueListIntoInClause($tabRefId);
    $obj=new PlannedWorkManual();
    $listOfDayByEtp=$obj->countGroupedSqlElementsFromCriteria(null,array('refId','day','period'), $where);
    echo '<table>';
    echo '<tr>';
    echo '<td class="reportTableHeader" style="min-width: '.$nameWidth.'px;width:'.$nameWidth.'px">'.i18n('Project').'</td>';
    echo '<td class="reportTableHeader" style="min-width: '.($nameWidth+($idWidth*2)).'px;width:'.($nameWidth+($idWidth*2)).'px" colspan="3">'.i18n('Activity').'</td>';
    echo '<td class="reportTableHeader" style="min-width: '.$idWidth.'px;width:'.$idWidth.'px">'.i18n('unitCapacity').'</td>';
    if ($monthYear) {
      for ($i=1;$i<=$nbDays;$i++) {
        $date=$year.'-'.$month.'-'.(($i<10)?'0':'').$i;
        if (isOffDay($date)) $classDay="reportTableHeader";
        else $classDay="noteHeader";
        echo '<td class="'.$classDay.'" style="padding:0;font-weight:normal;width:'.$size.'px">'.$i.'</td>';
      }
    }   
    echo '</tr>';
    if(count($list)== 0){
      echo '<tr><td colspan="2">';
      echo '<div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noActivityManualPlanningModeFound').'</div>';
      echo '</td></tr>';
    }
    if(sessionValueExists('selectActivityPlannedWorkManual')){
      $isSaveSession = getSessionValue('selectActivityPlannedWorkManual');
      $act = new Activity($isSaveSession,true);
      $project =new Project($act->idProject,true);
      $profile=getSessionUser()->getProfile($project);
      $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'assignmentEdit'));
      if($habil->rightAccess!=1){
        $isSaveSession = null;
        setSessionValue('selectActivityPlannedWorkManual', null);
      }
    }
    $valueRefType = null;
    $valueRefId = null;
    foreach ($list as $pe) {
      if(strlen($monthYear)==5){
        $monthYear = substr_replace($monthYear,'0',4,-1);
      }
      $peIntervention = SqlElement::getSingleSqlElementFromCriteria('InterventionCapacity', array('refType'=>$pe->refType,'refId'=>$pe->refId,'month'=>$monthYear));
      $valueFte = $peIntervention->fte;
      $mode = ($peIntervention->id)?$peIntervention->id:0;
      //$class  = "dojoxGridRow"; 
      $class = "";
      if (!isset($projList[$pe->idProject])) {
        $proj=new Project($pe->idProject,true);
        $projList[$pe->idProject]=$proj->name;
      }
      $badgeSize=self::$_size-4;
      
      $project =new Project($pe->idProject,true);
      $profile=getSessionUser()->getProfile($project);
      $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'assignmentEdit'));
      $readonlyHabil = false;
      if($habil->rightAccess!=1)$readonlyHabil=true;
      $onClick=($readonly or $readonlyHabil)?'':'onClick="selectInterventionActivity(\''.$pe->refType.'\', \''.$pe->refId.'\',\''.$pe->id.'\');"';
      $cursor=($readonly or $readonlyHabil)?"normal":"pointer";
      $colorBadge='<div style="border-radius:'.($badgeSize/2+2).'px;border:1px solid #e0e0e0;width:'.$badgeSize.'px;height:'.$badgeSize.'px;float:left;background-color:'.self::getColor($pe->refType,$pe->refId).'" ></div>';
      if(isset($isSaveSession)){
        if($isSaveSession == $pe->refId){
          $class = "dojoxGridRowSelected";
          $valueRefType = $pe->refType;
          $valueRefId = $pe->refId;
        }
      }
      echo '<tr id="'.$pe->refId.'" style="border:1px solid #a0a0a0;cursor:'.$cursor.'" class="interventionActivitySelector'.$pe->id.' interventionActivitySelector '.$class.'">';
      echo '<td class="dojoxGridCell interventionActivitySelector interventionActivitySelector'.$pe->id.'" style="width:'.$nameWidth.'px" '.$onClick.'>'.$projList[$pe->idProject].'</td>';
      echo '<td class="dojoxGridCell noteDataCenter interventionActivitySelector interventionActivitySelector'.$pe->id.'" style="width:'.($idWidth).'px" '.$onClick.'>#'.$pe->refId.'</td>';
      echo '<td class="dojoxGridCell interventionActivitySelector interventionActivitySelector'.$pe->id.'" style="border-right:0;width:'.($idWidth).'px" '.$onClick.'>'.$colorBadge.'</td>';
      echo '<td class="dojoxGridCell interventionActivitySelector interventionActivitySelector'.$pe->id.'" style="border-left:0;width:'.($nameWidth).'px" >';
      echo '  <table style="width:100%;" ><tr>';
      echo '          <td style="width:90%;" '.$onClick.'><div style="max-height:30px;overflow:hidden;">'.$pe->refName.'</div></td>';
      $goto=($readonly or $readonlyHabil)?'':'onClick="gotoElement('."'".$pe->refType."','".htmlEncode($pe->refId)."'".');"';
      if($class=='dojoxGridRowSelected'){
        $iconGoto='<div class="iconGotoWhite16 iconGoto iconSize16 imageColorNewGui" style="z-index:500;width:16px;height:16px;;" title="">&nbsp;</div>';
      }else{
        $iconGoto='<div class="iconGoto16 iconGoto iconSize16 imageColorNewGui" style="z-index:500;width:16px;height:16px;;" title="">&nbsp;</div>';
      }
      echo '          <td '.$goto.' style="width:10%;">'.$iconGoto.'</td>';
      echo '  </tr></table>';
      echo '</td>';
      $paddingRight="";
      if(isNewGui())$paddingRight="padding-right:7px;";
      echo '<td class="dojoxGridCell noteDataCenter interventionActivitySelector interventionActivitySelector'.$pe->id.'" style="'.$paddingRight.' text-align:center;margin:0;padding;0;width:'.$idWidth.'px">';
      if(!$readonly and !$readonlyHabil){
        if($valueFte==0)$valueFte=null;
        echo '<img  id="idImageInterventionActivitySelector'.$pe->refId.'" src="../view/img/savedOk.png"
                    style="display: none; position:relative;top:2px;left:5px; height:16px;float:left;"/>';
        echo '<div dojoType="dijit.form.NumberTextBox" id="interventionActivitySelector'.$pe->refId.'" name="interventionActivitySelector'.$pe->refId.'"
      						  class="dijitReset dijitInputInner dijitNumberTextBox interventionFTE"
        					  value="'.$valueFte.'"
                    style="padding:1px;background:none;max-width:100%;display:block;;margin:2px 0px" >
                     <script type="dojo/method" event="onChange">
                      saveInterventionCapacity("'.$pe->refType.'",'.$pe->refId.','.$monthYear.','.$pe->id.'); 
                     </script>';
        echo $keyDownEventScript;
        echo '</div>';
      } else {
        echo $peIntervention->fte;
      }
      echo'</td>';
      if ($monthYear) {
        for ($i=1;$i<=$nbDays;$i++) {
          $date=$year.'-'.$month.'-'.(($i<10)?'0':'').$i;
          //$date=$year.'-'.(($month<10)?'0':'').$month.'-'.(($i<10)?'0':'').$i;
          $colorAM='#ffffff';
          $colorPM='#ffffff';
          if (isOffDay($date)) {
            $colorAM="#d0d0d0";
            $colorPM="#d0d0d0";
          }
          if(strlen($month)==1){
            $month = '0'.$month;
          }
          $y = $i;
          if(strlen($y)==1){
            $y = '0'.$y;
          }
          $myDate = $year.$month.$y;
          if($peIntervention->fte){
            if(isset($listOfDayByEtp[$pe->refId.'|'.$myDate.'|AM'])){
              if($listOfDayByEtp[$pe->refId.'|'.$myDate.'|AM']==$peIntervention->fte)$colorAM = "#50BB50";
              if($listOfDayByEtp[$pe->refId.'|'.$myDate.'|AM']>$peIntervention->fte)$colorAM = "#BB5050";
            }
            if(isset($listOfDayByEtp[$pe->refId.'|'.$myDate.'|PM'])){
              if($listOfDayByEtp[$pe->refId.'|'.$myDate.'|PM']==$peIntervention->fte)$colorPM = "#50BB50";
              if($listOfDayByEtp[$pe->refId.'|'.$myDate.'|PM']>$peIntervention->fte)$colorPM = "#BB5050";
            }
          }
          echo '<td style="border:1px solid #a0a0a0;" '.$onClick.'>';
          echo '<table style="width:100%;height:100%">';
          $color=getForeColor($colorAM);
          echo '<tr style="height:'.$midSize.'px;">
                <td id="'.$myDate.'AM'.$pe->refId.'" style="width:100%;background:'.$colorAM.';border-bottom:1px solid #e0e0e0;position:relative;text-align:center;"></td>
                </tr>';
          $color=getForeColor($colorPM);
          echo '<tr style="height:'.$midSize.'px;">
                <td id="'.$myDate.'PM'.$pe->refId.'" style="width:100%;background:'.$colorPM.';border:0;position:relative;text-align:center;"></td>
                </tr>';
          echo '</table>';  
          echo '</td>';
        }
      }
      echo '</tr>';
    }
    echo '</table>';
    echo '<input type="hidden" id="interventionActivityType" value="'.$valueRefType.'" style="width:80px;background:#ffe0e0" />';
    echo '<input type="hidden" id="interventionActivityId" value="'.$valueRefId.'" style="width:30px;background:#ffe0e0" />';
  }
  public static function setSize($size) {
    if ($size<20) {
      $size=20;
    }
    self::$_size=$size;
  }
  
  
  public static function getColor($type,$id) {
    if (! $type or !$id ) return '';
    $key=$type.'#'.$id;
    if (isset(self::$_cacheColor[$key])) {
      return self::$_cacheColor[$key];
    }
    $pe=$type.'PlanningElement';
    if (property_exists($pe, 'color')) {
      $obj=SqlElement::getSingleSqlElementFromCriteria($pe, array('refType'=>$type,'refId'=>$id));
      if ($obj->color) {
        self::$_cacheColor[$key]=$obj->color;
        return self::$_cacheColor[$key];
      }
    }
    self::$_cacheColor[$key]=Absence::$_colorTab[$id%10];
    return self::$_cacheColor[$key];
  }
}
require_once '../tool/formatter.php';
?>