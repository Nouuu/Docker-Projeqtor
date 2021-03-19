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

/** ============================================================================
 * Activity is main planned element
 */  
require_once('_securityCheck.php');
class CronExecution extends SqlElement {
  
  public $id;    // redefine $id to specify its visible place
  public $cron;
  public $fileExecuted;
  public $idle;
  public $fonctionName;
  public $nextTime;
  public $_noHistory;
  
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

  public function save($withRelaunch=true) {
    return parent::save();
  }
  
  public function calculNextTime(){
    $UTC=new DateTimeZone(Parameter::getGlobalParameter ( 'paramDefaultTimezone' ));
    $date=new DateTime('now');
    $splitCron=explode(" ",$this->cron);
    $minute=1;
		$splitMinuteCron=explode("/",$splitCron[0]);
		if(count($splitMinuteCron)==2){
		  $splitCron[0]=$splitMinuteCron[0];
		  $mod = $date->format('i')%$splitMinuteCron[1];
		  if($mod > 0){
		    $minute = $splitMinuteCron[1] - $mod;
		  }else{
		    $minute = $splitMinuteCron[1];
		  }
		}
		$splitHourCron=explode("/",$splitCron[1]);
		if(count($splitHourCron)==2){
			$splitCron[1]=$splitHourCron[0];
			$mod = $date->format('H')%$splitHourCron[1];
			if($mod > 0){
			  if($splitCron[0] != '*'){
			    $minute = ($splitHourCron[1]-$mod)*60-$date->format('i')+$splitCron[0];
			  }else{
			    $minute = ($splitHourCron[1]-$mod)*60-$date->format('i');
			  }
			}else{
			  if($splitCron[0] != '*'){
			    $minute = abs($date->format('i') - $splitCron[0]);
			  }else{
			  	$minute = abs(60-$date->format('i'));
			  }
			}
		}
		$date->modify('+'.$minute.' minute');
    $count=0;
    if(count($splitCron)==5){
      $find=false;
      while(!$find){ //cron minute/hour/dayOfMonth/month/dayOfWeek
        if(($splitCron[0]=='*' || $date->format("i")==$splitCron[0])
        && ($splitCron[1]=='*' || $date->format("H")==$splitCron[1])
        && ($splitCron[2]=='*' || $date->format("d")==$splitCron[2])
        && ($splitCron[3]=='*' || $date->format("m")==$splitCron[3])
        && ($splitCron[4]=='*' || $date->format("N")==$splitCron[4])){
          $find=true;
          $date->setTime($date->format("H"), $date->format("i"), 0);
          $this->nextTime=$date->format("U");
          $this->save(false);
        }else{
          $date->modify('+1 minute');
        }
        $count++;
        if($count>=2150000){
          $this->idle=1;
          $this->save(false);
          $find=true;
          errorLog("Can't find next time for cronexecution because too many execution #".$this->id);
        }
      }
    }else{
      errorLog("Can't find next time for cronexecution because too many execution #".$this->id);
    }
  }
  
  public static function drawCronExecutionDefintion($scope) {
    $cronExecution=null;
    $cronExecution=SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'cron'.ucfirst($scope)));
    if (!$cronExecution->id) {
      $cronExecution->idle=1;
    }
    echo "<br/>";
    $splitCron=($cronExecution->cron)?explode(" ",$cronExecution->cron):array('0','*','*','*','*');
    foreach ($splitCron as $key=>$line){
      if($line=="*")$splitCron[$key]=i18n("all");
    }
    $minutes=$splitCron[0];
    $hours=$splitCron[1];
    $dayOfMonth=$splitCron[2];
    $month=$splitCron[3];
    $dayOfWeek=$splitCron[4];
    echo "<table style='float:left;'>";
    echo "  <tr><td class='linkHeader' style='width:100px;padding:0px 5px'>".i18n('colFrequency')."</td><td class='linkHeader' style='width:80px;padding:0px 5px'>".i18n('colValue')."</td></tr>";
    echo "  <tr><td class='linkData'>".i18n('minute')."</td><td class='linkData'>$minutes</td></tr>";
    echo "  <tr><td class='linkData'>".i18n('hour')."</td><td class='linkData'>$hours</td></tr>";
    echo "  <tr><td class='linkData'>".i18n('colFixedDay')."</td><td class='linkData'>$dayOfMonth</td></tr>";
    echo "  <tr><td class='linkData'>".i18n('month')."</td><td class='linkData'>".self::getMonthName($month)."</td></tr>";
    echo "  <tr><td class='linkData'>".i18n('colFixedDayOfWeek')."</td><td class='linkData'>".self::getWeekDayName($dayOfWeek)."</td></tr>";
    echo " </table>";
    echo "&nbsp;&nbsp;";
    echo "<button id='cronExecution$scope' dojoType='dijit.form.Button' class='roundedVisibleButton' showlabel='true' style='position:relative;top:-2px'>";
    echo i18n("cronDefineParameters");
    echo "  <script type='dojo/connect' event='onClick' args='evt'>";
    echo "    loadDialog('dialogCronDefinition', null, true, '&cronScope=$scope', true);";
    echo "  </script>";
    echo "</button><br/>";
    echo "&nbsp;&nbsp;";
    if ($cronExecution->id) {
      echo "<button id='cronExecutionActivate$scope' class='roundedVisibleButton' dojoType='dijit.form.Button' showlabel='true' style='position:relative;top:-2px'>";
      echo $cronExecution->idle ? i18n("cronExecutionActivate") : i18n("cronExecutionDesactivate");
      echo "  <script type='dojo/connect' event='onClick' args='evt'>";
      echo "    cronActivation('$scope');";
      echo "  </script>";
      echo "</button>";
    }
    if ($cronExecution->idle==1) {
      echo "<div style='white-space:nowrapo;height:30px; width:400px;color:#A00000;position:relative;left:12px;'>".i18n('cronExecutionNotRunning')."</div>";
    } else {
      echo "<div style='white-space:nowrapo;height:30px; width:400px;color:#00A000;position:relative;left:12px;'>".i18n('cronExecutionRunning')."</div>";
    }
  }
  public static function getObjectFromScope($scope) {
    $obj=SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'cron'.ucfirst($scope)));
    if (! trim($obj->cron)) $obj->cron='0 * * * *';
    return $obj;
  }
  public static function getWeekDayName($day) {
    if ($day=='*' or $day=='all' or $day==i18n('all')) return i18n('all');
    $dayName=array(0=>'Sunday',   1=>'Monday', 2=>'Tuesday',  3=>'Wednesday',
                   4=>'Thursday', 5=>'Friday', 6=>'Saturday', 7=>'Sunday');
    return i18n($dayName[$day]);
  }
  public static function getMonthName($month) {
    if ($month=='*' or $month=='all' or $month==i18n('all')) return i18n('all');
    return getMonth(0,$month-1);
  }
}
?>