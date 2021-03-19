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
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class ResourceTeamAffectation extends SqlElement {

  public $id;
  public $idResourceTeam;
  public $idResource;
  public $rate;
  public $description;
  public $startDate;
  public $endDate;
  public $idle;
  
  
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
  
  public function control(){
    $result="";
    if($this->idle==0){
      $idResource = $this->idResource;
      $start=($this->startDate)?$this->startDate:self::$minAffectationDate;
      $end=($this->endDate)?$this->endDate:self::$maxAffectationDate;
      $ress=new Resource($this->idResource);
      $capacity=$ress->capacity;
      $rate = $this->rate;
      if($this->id){
        $teamAff = new ResourceTeamAffectation($this->id);
      }
      $periods=self::buildResourcePeriods($this->idResource,false,'Resource');
      $maxExitingRate=0;
      $i = 0;
      foreach ($periods as $period){
        $capacity=$ress->getCapacityPeriod($period['start']);
        if ($start<=$period['end'] and $end>=$period['start'] and $capacity>0) {
          $ratePeriod=floatval($period['idResource'][$this->idResource])*100/$capacity;
          if ($ratePeriod>$maxExitingRate) {
            $maxExitingRate=$ratePeriod;
            if($this->id){
              if(!$teamAff->endDate)$teamAff->endDate=self::$maxAffectationDate;
              if(!$teamAff->startDate)$teamAff->startDate=self::$minAffectationDate;
              if($teamAff->startDate <= $period['end'] and $teamAff->endDate >= $period['start']){
                $maxExitingRate -= $teamAff->rate;
              }
            }
          }
        }
      }
      $rate += $maxExitingRate;
      if($rate > 100){
        $result.='<br/>' . i18n('impossibleRateAffectationResourcePool', array($ress->name,$rate));
      }
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public static $maxAffectationDate='2099-12-31';
  public static $minAffectationDate='1970-01-01';
  
  private static function formatDate($date) {
    if ($date==self::$minAffectationDate) {
    		return "";
    }
    if ($date==self::$maxAffectationDate) {
    		return "";
    }
    return htmlFormatDate($date);
  }
  
  private static $_resourcePeriodsPerProject=array();
  public static function buildResourcePeriodsPerResourceTeam($idResource, $showIdle=false){
    if (isset(self::$_resourcePeriodsPerProject[$idResource][$showIdle])) {
    		return self::$_resourcePeriodsPerProject[$idResource][$showIdle];
    }
    $periods=self::buildResourcePeriods($idResource,$showIdle);
    $cptProj=0;
    $projects=array();
    foreach ($periods as $p) {
    		foreach($p['idResource'] as $idP=>$affP) {
    		  if (! isset($projects[$idP])) {
    		    $cptProj++;
    		    $projects[$idP]=array('position'=>$cptProj,
    		        //'name'=>SqlList::getNameFromId('Project',$idP),
    		        'periods'=>array()
    		    );
    		  }
    		  $per=$projects[$idP]['periods'];
    		  $last=end($per);
    		  if (count($per)>0
    				and $last['end']==addDaysToDate($p['start'], -1)
    				and $last['rate']==$p['idResource'][$idP]) {
    		    $projects[$idP]['periods'][count($per)-1]['end']=$p['end'];
    		  } else {
    		    $projects[$idP]['periods'][]=array('start'=>$p['start'], 'end'=>$p['end'], 'rate'=>$p['idResource'][$idP]);
    		  }
    		}
    }
    if (!isset($_resourcePeriodsPerProject[$idResource])) {
    		$_resourcePeriodsPerProject[$idResource]=array();
    }
    $_resourcePeriodsPerProject[$idResource][$showIdle]=$projects;
    return $projects;
  }
  
  private static $_resourcePeriods=array();
  public static function buildResourcePeriods($idResourceAff,$showIdle=false,$target="Team") {
    if (isset(self::$_resourcePeriods[$idResourceAff][$showIdle])) {
      return self::$_resourcePeriods[$idResourceAff][$showIdle];
    }
    $resource=array();
    $aff=new ResourceTeamAffectation();
    if ($target=='Team') {
      $crit=array('idResourceTeam'=>$idResourceAff);
    } else if ($target=='Resource'){
      $crit=array('idResource'=>$idResourceAff);
    } else {
      errorLog("Call buildResourcePeriods for incorrect target '$target'");
    }
    if (!$showIdle) {
    		$crit['idle']='0';
    }
    $list=$aff->getSqlElementsFromCriteria($crit,false,null, 'startDate asc, endDate asc');
    $res=array();
    foreach ($list as $aff) {
        $myResource = new Resource($aff->idResource);
        $resource[$myResource->id] = $myResource;
    		$start=($aff->startDate)?$aff->startDate:self::$minAffectationDate;
    		$end=($aff->endDate)?$aff->endDate:self::$maxAffectationDate;
    		if ($aff->idle) $end=self::$minAffectationDate; // If affectation is closed : no work to plan
    		$arrAffResource=array($aff->idResource=>($aff->rate/100*$myResource->capacity));
    		
    		
    		foreach($res as $r) {
    		  if (!$start or !$end) break;
    		  if ($start<=$r['start']) {
    		    if ($end>=$r['start']) {
    		      if ($end<=$r['end']) {
    		        $res[$r['start']]=array(
    		            'start'=>$r['start'],
    		            'end'=>$end,
    		            'rate'=>($aff->rate/100*$myResource->capacity)+$r['rate'],
    		            'idResource'=>array_sum_preserve_keys($r['idResource'],$arrAffResource));
    		        if ($end!=$r['end']) {
    		          $next=addDaysToDate($end, 1);
    		          $res[$next]=array(
    		              'start'=>$next,
    		              'end'=>$r['end'],
    		              'rate'=>$r['rate'],
    		              'idResource'=>$r['idResource']);
    		        }
    		        $end=($start!=$r['start'])?addDaysToDate($r['start'], -1):'';
    		      } else {
    		        if ($start!=$r['start']) {
    		          $res[$start]=array(
    		              'start'=>$start,
    		              'end'=>addDaysToDate($r['start'], -1),
    		              'rate'=>$r['rate']+($aff->rate/100*$myResource->capacity),
    		              'idResource'=>$arrAffResource);
    		        }
    		        $next=$r['start'];
    		        $res[$next]=array(
    		            'start'=>$next,
    		            'end'=>$r['end'],
    		            'rate'=>$r['rate']+($aff->rate/100*$myResource->capacity),
    		            'idResource'=>array_sum_preserve_keys($r['idResource'],$arrAffResource));
    		        $start=($end!=$r['end'])?addDaysToDate($r['end'], 1):'';
    		      }
    		    }
    		  } else { //$start>$r['startDate']
    		    if ($start<=$r['end']) {
    		      $res[$r['start']]=array(
    		          'start'=>$r['start'],
    		          'end'=>addDaysToDate($start, -1),
    		          'rate'=>$r['rate'],
    		          'idResource'=>$r['idResource']);
    		      if ($end<=$r['end']) {
    		        $res[$start]=array(
    		            'start'=>$start,
    		            'end'=>$end,
    		            'rate'=>$r['rate']+($aff->rate/100*$myResource->capacity),
    		            'idResource'=>array_sum_preserve_keys($r['idResource'],$arrAffResource));
    		        if ($end!=$r['end']) {
    		          $next=addDaysToDate($end, 1);
    		          $res[$next]=array(
    		              'start'=>$next,
    		              'end'=>$r['end'],
    		              'rate'=>$r['rate'],
    		              'idResource'=>$r['idResource']);
    		        }
    		        $start='';$end='';
    		      } else { // ($end>$r['end'])
    		        $res[$start]=array(
    		            'start'=>$start,
    		            'end'=>$r['end'],
    		            'rate'=>$r['rate']+($aff->rate/100*$myResource->capacity),
    		            'idResource'=>array_sum_preserve_keys($r['idResource'],$arrAffResource));
    		        $start=addDaysToDate($r['end'], 1);
    		      }
    		    }
    		  }
    		} // End loop
    		
    		if ($start and $end) {
    		  $res[$start]=array('start'=>$start,
    		      'end'=>$end,
    		      'rate'=>($aff->rate/100*$myResource->capacity),
    		      'idResource'=>$arrAffResource);
    		}
    }
    
//Gautier #resCap
  $tabDate = array();
  $resourceCapacityExist = false;
  foreach ($resource as $Myres){
    $tabCapRes = $Myres->getCapacityPeriod($start);
    if(sessionTableValueExist('capacityPeriod', $Myres->id)){
      $tabResCap = getSessionTableValue('capacityPeriod', $Myres->id);
      foreach ($tabResCap as $idTab){
        $resourceCapacityExist = true;
        foreach ($idTab as $valTabResCap){
          $tabDate[$valTabResCap['startDate']]=$valTabResCap['startDate'];
          $date = new DateTime($valTabResCap['endDate']);
          $date->add(new DateInterval('P1D'));
          $endDate1 = $date->format('Y-m-d');
          $tabDate[$endDate1]=$endDate1;
        }
      }
    }
  }
  if($resourceCapacityExist){
    foreach ($res as $resVal){
      $d1 = $resVal['start'];
      $d2 = $resVal['end'];
      $tabDate[$d1]= $d1;
      $date = new DateTime($d2);
      $date->add(new DateInterval('P1D'));
      $endDate2 = $date->format('Y-m-d');
      $tabDate[$endDate2]=$endDate2;
    }
    ksort($tabDate);
    $last = null;
    $nb = count($tabDate);
    foreach ($tabDate as $key=>$valDate){
      if(!$last){
        $last = $key;
        continue;
      }else{
        $endDate = new DateTime($valDate);
        $endDate->sub(new DateInterval('P1D'));
        $endDate =  $endDate->format('Y-m-d');
        $tabDate[$last]=$endDate;
        $last = $key;
      }
    }
    $final=array();
    array_pop($tabDate);
    $newResTab = array();
    foreach ($tabDate as $startDate=>$endDate){
      $found=null;
      foreach ($res as $key=>$period){
        if($startDate >= $key){
          if($startDate <= $period['end']){
            $found=$key;
          }else{
            continue;
          }
        }else{
          break;
        }
      }
      $resTab=array();
      $newResTab=array();
      if($found) {
        $poolValue=$res[$found];
        $arrIdRes=array();
        $sum = 0;
        foreach ($poolValue['idResource'] as $idRes=>$capacity){
          $NewRes = $resource[$idRes];
          $defaultCap = $NewRes->capacity;
          $cap = $NewRes->getCapacityPeriod($startDate);
          if($defaultCap != $capacity){
            $ratePool = ($capacity*100/$defaultCap);
            $rateEtp = $cap*$ratePool/100;
          }else{
            $rateEtp = $cap;
          }
          $newResTab[$idRes]= $rateEtp;
          $sum += $rateEtp;
        }
        $final[$startDate]=array('start'=>$startDate,'end'=>$endDate,'rate'=>$sum,'idResource'=>$newResTab);
      }
    }
    $res = $final;
  }
  //end    
    
    if (!isset($_resourcePeriods[$idResourceAff])) {
    		$_resourcePeriods[$idResourceAff]=array();
    }
    ksort($res);
    $_resourcePeriods[$idResourceAff][$showIdle]=$res;
    
    $maxCapacity = 0;
    $today=date('Y-m-d');
    foreach ($res as $val){
        if($val['start'] >= $today or $val['start'] <= $today and $val['end'] >= $today ){
          if($val['rate'] > $maxCapacity){
            $maxCapacity = $val['rate'];
          }
        }
    }
    $resTeam = new ResourceTeam($idResourceAff);
    $resTeam->capacity = $maxCapacity;
    $resTeam->save();
    if(!isset( self::$_resourcePeriods[$idResourceAff] )){  self::$_resourcePeriods[$idResourceAff]=array(); }
    self::$_resourcePeriods[$idResourceAff][$showIdle] = $res;
   return $res;
  }
  
  public static function drawResourceTeamAffectation($idResourceAff, $showIdle=false) {
  global $print;
  	$periods=self::buildResourcePeriods($idResourceAff,$showIdle);
  	if (count($periods)==0) return;
  	$first=reset($periods);
  	$start=$first['start'];
  	$last=end($periods);
  	$end=$last['end'];
  	$projects=array();
  	$nb=count($periods);
  	if ( ($start==Affectation::$minAffectationDate or $end==Affectation::$maxAffectationDate) and $nb>1) {
  		if ($start==Affectation::$minAffectationDate) {
  			if ($end==Affectation::$maxAffectationDate){
  				$newDur=dayDiffDates($first['end'],$last['start'])+1;
  				
  			} else {
  				$newDur=dayDiffDates($first['end'],$end)+1;
  			}
  		} else {
  			$newDur=dayDiffDates($start,$last['start'])+1;
  		}
  		$gap=ceil(max(30,$newDur)/$nb);
  		$start=($start==Affectation::$minAffectationDate)?addDaysToDate($first['end'], $gap*(-1)):$start;
  		$end=($end==Affectation::$maxAffectationDate)?addDaysToDate($last['start'], $gap):$end;
  	} 	 
  	$duration=dayDiffDates($start, $end)+1;
  	$maxRate=100;
  	$lineHeight=15;
  	$cptProj=0;
  	foreach ($periods as $p) {
  		if ($p['rate']>$maxRate) $maxRate=$p['rate'];
  		foreach($p['idResource'] as $idP=>$affP) {
  			if (! isset($projects[$idP])) {
  				$cptProj++;
  				$projects[$idP]=array('position'=>$cptProj,
  						'name'=>SqlList::getNameFromId('Resource',$idP));
  			}
  		}
  	}
  	
  	$MaxHeight = 1;
  	$nb = 0;
  	foreach ($periods as $p) {
  	  $nb++;
  	  if ( $p['rate'] > $MaxHeight){
  	    $MaxHeight =  $p['rate'];
  	  }
  	}
  	$ratio = 100/$MaxHeight;
  	if($ratio>10){
  	  $ratio = 10;
  	} 
  	$MaxHeight = $MaxHeight*$ratio;
  	$nbHeight = 15*$nb;
  	$result='<div style="position:relative;height:5px;"></div>'
  			.'<div style="position:relative;width:99%; height:'.((count($projects))*($lineHeight+4)+4+$MaxHeight).'px; '
  			.' border: 1px solid #AAAAAA;background-color:#FEFEFE;'
  			.' box-shadow:2px 2px 2px #888888; ">';
  	
  	$result.='<div style="position:absolute; height:'.$MaxHeight.'px; min-height:20px;width:99%; " > ';
    $nbPeriods = count($periods);
  	foreach ($periods as $p) {
  		$len=dayDiffDates(max($start,$p['start']), min($end,$p['end']))+1;
  		$width=($duration)?($len/$duration*100):0;
  		$left=(dayDiffDates($start, max($start,$p['start']))/$duration*100);
  		$title='['.$p['rate'].'] '.self::formatDate($p['start']).' => '.self::formatDate($p['end']);
  		$lineHeight2 = $ratio*$p['rate'];
  		$lineHeight3 = ($lineHeight2-13) / 2;
  		if($lineHeight2 < 10 and $nbPeriods == 1){
  		  if($lineHeight2 < 5 ){
  		    $top = ($MaxHeight-$lineHeight2)+5;
  		  }else{
  		    $top = ($MaxHeight-$lineHeight2)+8;
  		  }
  		}else{
  		  $top = ($MaxHeight-$lineHeight2);
  		}
  		$result.= '<div style="position:absolute;left:'.$left.'%;width:'.$width.'%;top:'.$top.'px;'
  		    //height des barres du haut
  			.' height:'.($lineHeight2).'px;'
  			.' background-color:#'.'EEEEFF'.'; '
  			.' border:1px solid #'.'AAAAEE'.';" ';
  		if (! $print)	$result.='title="'.$title.'" ';
  		$result.='>';
  	
  		if($lineHeight2 < 10 ){ 
  		  $result.='<div style="z-index:1;position: absolute; bottom:14px;right:0px;height:'.$lineHeight2.'px;white-space:nowrap;'
  		      .'width:100%;text-align:center;color:#AAAAEE'.';">';
  		}else{
  		  $result.='<div style="z-index:1;margin-top:'.$lineHeight3.'px;white-space:nowrap;'
  		      .'width:100%;text-align:center;color:#AAAAEE'.';">';
  		}
  		$result.=htmlDisplayNumericWithoutTrailingZeros($p['rate']).'';
  		$result.= '</div>';
  		$result.='</div>';	
  	}
  	$result.='</div>';
  	$MaxHeight = $MaxHeight+3;
  	$result.='<div style="position:absolute; top:'.$MaxHeight.'px; height:'.$nbHeight.'px;width:99%; " > ';
  	$periodsPerProject=self::buildResourcePeriodsPerResourceTeam($idResourceAff, $showIdle);
  	foreach ($periodsPerProject as $idP=>$proj) {
  	  foreach ($proj['periods'] as $p) {
  	    $len=dayDiffDates(max($start,$p['start']), min($end,$p['end']))+1;
  	    $width=($len/$duration*100);
  	    $left=(dayDiffDates($start, max($start,$p['start']))/$duration*100);
  	    $title='['.$p['rate'].'] '.self::formatDate($p['start']).' => '.self::formatDate($p['end']);
  	    $title.="\n".SqlList::getNameFromId('Resource', $projects[$idP]['name']);
  	    $color='#EEEEEE';
  	    $result.= '<div style="position:absolute;left:'.$left.'%;width:'.$width.'%;'
  	        .' top:'.(3+($lineHeight+4)*($proj['position']-1)).'px;'
  	            .' height:'.($lineHeight).'px;z-index:'.(99-$proj['position']).';'
  	                .' background-color:'.$color.'; '
  	                    .' border:1px solid #222222;" ';
  	    if (! $print)	$result.='title="'.$title.'" ';
  	    $result.='>';
  	    $result.='<div style="position: absolute; top:0px;left:0px;width:100%;height:'.$lineHeight.'px;overflow:visible;'
  	        .'color:'.htmlForeColorForBackgroundColor($color).';text-shadow:1px 1px '.$color.';white-space:nowrap;z-index:9999">';
  	    $result.='['.htmlDisplayNumericWithoutTrailingZeros($p['rate']).']&nbsp;'.SqlList::getNameFromId('Resource', $projects[$idP]['name']);
  	    $result.= '</div>';
  	    $result.='</div>';
  	  }
  	}
  	$result.= '</div>';
  	$result.= '</div>';
  	return $result;
  }
  public static function findPeriod($date,$list) {
    foreach ($list as $key=>$val) {
      if ($date>=$val['start'] and $date<=$val['end']) return $key;
      else if ($date<$val['start']) return null;
    }
    return null;
  }
  
  public function delete() {
    $result=parent::delete();
    $status=getLastOperationStatus($result);
    if ($status=='OK') {
      $as=new AssignmentSelection();
      $resPurge=$as->purge("idResource=$this->idResource");
    }
    
    return $result;
  }
  public function save () {
    $new=($this->id)?false:true;
    $result=parent::save();
    $status=getLastOperationStatus($result);
    if ($new and $status=='OK') {
      $ass=new Assignment();
      $assTable=$ass->getDatabaseTableName();
      $assSel=new AssignmentSelection();
      $assSelTable=$assSel->getDatabaseTableName();
      $crit="idResource=$this->idResourceTeam and uniqueResource=1 and not exists (select 'x' from $assSelTable sel where sel.idAssignment=$assTable.id and sel.idResource=$this->idResource)";
      $list=$ass->getSqlElementsFromCriteria(null,null,$crit);
      foreach ($list as $ass) {
        $sel=new AssignmentSelection();
        $sel->idAssignment=$ass->id;
        $sel->idResource=$this->idResource;
        $resSel=$sel->save();
      }
    }
    return $result;
  }
  
}
?>