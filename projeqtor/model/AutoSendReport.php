<?php
use Spipu\Html2Pdf\Html2Pdf;
use PHPMailer\PHPMailer\PHPMailer;
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

class AutoSendReport extends SqlElement{
  
	public $id;
	public $name;
	public $idReport;
	public $idResource;
	public $idReceiver;
	public $idle;
	public $sendFrequency;
	public $otherReceiver;
	public $cron;
	public $nextTime;
	public $reportParameter;
	
	private static $_databaseTableName = 'cronautosendreport';
	
	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	*/
	function __construct($id=NULL, $withoutDependentObjects=false) {
	  parent::__construct($id,$withoutDependentObjects);
	}
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }
	
	protected function getStaticDatabaseTableName() {
		$paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
		return $paramDbPrefix . self::$_databaseTableName;
	}
	
	function save(){
	  return parent::save();
	}
	
	public function calculNextTime($cron=null){
		$UTC=new DateTimeZone(Parameter::getGlobalParameter ( 'paramDefaultTimezone' ));
		$date=new DateTime('now');
		$date->modify('+1 minute');
		if(!$cron){
		  $splitCron=explode(" ",$this->cron);
		}else{
		  $splitCron=explode(" ",$cron);
		}
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
					errorLog("Can't find next time for cronAutoSendReport because too many execution #".$this->id);
				}
			}
		}else{
			errorLog("Can't find next time for cronAutoSendReport because too many execution #".$this->id);
		}
	}
	
	public function sendReport($idReport, $reportParameter){
	  global $displayResource, $outMode, $showMilestone, $portfolio, $columnsDescription, $graphEnabled, $showProject, $rgbPalette, $arrayColors,$cronnedScript;
	  ob_start();
	  $report = new Report($idReport);
	  $parameter = json_decode($reportParameter);
	  $landscape = 'L';
	  foreach ($parameter as $paramName=>$paramValue){
	  	if($paramName != 'yearSpinner' and $paramName != 'monthSpinner' and $paramName != 'weekSpinner' and $paramName != 'startDate' and $paramName != 'periodValue'){
	  	  RequestHandler::setValue($paramName, $paramValue);
	  	  if($paramName == 'orientation'){
	  	    $landscape = $paramValue;
	  	  }
	  	}
	  }
  	foreach ($parameter as $paramName=>$paramValue){
  	  if($paramName == 'yearSpinner'){
  	    if($paramValue == 'current'){
  	      RequestHandler::setValue($paramName, date('Y'));
  	      RequestHandler::setValue("periodType", "year");
  	      RequestHandler::setValue("periodValue", date('Y'));
  	    }else if($paramValue == 'previous'){
  	      RequestHandler::setValue($paramName, date('Y')-1);
  	      RequestHandler::setValue("periodType", "year");
  	      RequestHandler::setValue("periodValue", date('Y')-1);
  	    }
  	  }
  	  if($paramName == 'monthSpinner'){
  	  if($paramValue == 'current'){
  	      RequestHandler::setValue($paramName, date('m'));
  	      RequestHandler::setValue("periodType", "month");
  	      RequestHandler::setValue("periodValue", date('m'));
  	    }else if($paramValue == 'previous'){
  	      RequestHandler::setValue($paramName, date('m')-1);
  	      RequestHandler::setValue("periodType", "month");
  	      RequestHandler::setValue("periodValue", date('m')-1);
  	    }
  	  }
  	  if($paramName == 'weekSpinner'){
  	  if($paramValue == 'current'){
  	      RequestHandler::setValue($paramName, date('W'));
  	      RequestHandler::setValue("periodType", "week");
  	      RequestHandler::setValue("periodValue", date('W'));
  	    }else if($paramValue == 'previous'){
  	      RequestHandler::setValue($paramName, date('W')-1);
  	      RequestHandler::setValue("periodType", "week");
  	      RequestHandler::setValue("periodValue", date('W')-1);
  	    }
  	  }
  	  if($paramName == 'startDate'){
  	    if($paramValue == 'currentDate'){
  	      RequestHandler::setValue($paramName, date('Y-m-d'));
  	    }else{
  	      RequestHandler::setValue($paramName, $paramValue);
  	    }
  	  }
  	  if($paramName == 'periodValue'){
  	    if(strpos($paramValue, '-') != ''){
  	      $value = explode('-', $paramValue);
  	      $year = '';
  	      if($value[0] == "currentYear"){
  	      	$year = date('Y');
  	      	RequestHandler::setValue($paramName, $year);
  	      }else if($value[0] == 'previousYear'){
  	      	$year = date('Y')-1;
  	      	RequestHandler::setValue($paramName, $year);
  	      }
  	      if(count($value) > 1){
  	      	if($value[1] == 'currentMonth'){
  	      		$month = date('m');
  	      		RequestHandler::setValue($paramName, $year.$month);
  	      	}else if($value[1] == 'previousMonth'){
  	      		$month = date('m')-1;
  	      		if($month < 10){
  	      			$month = '0'.$month;
  	      		}
  	      		RequestHandler::setValue($paramName, $year.$month);
  	      	}
  	      	if($value[1] == 'currentWeek'){
  	      		$week = date('W');
  	      		RequestHandler::setValue($paramName, $year.$week);
  	      	}else if($value[1] == 'previousWeek'){
  	      		$week = date('W')-1;
  	      		if($week < 10){
  	      			$week = '0'.$week;
  	      		}
  	      		RequestHandler::setValue($paramName, $year.$week);
  	      	}
  	      }
  	    }else{
  	      RequestHandler::setValue($paramName, $paramValue);
  	      continue;
  	    }
  	  }
  	}
	  $reportFile=explode('?', $report->file);
	  $file = $reportFile[0];
	  if (count($reportFile) > 1) {
	    $reportFileParam = explode('&', $reportFile[1]);
	    foreach ($reportFileParam as $value){
	    	$param = explode('=', $value);
	    	RequestHandler::setValue($param[0], $param[1]);
	    }
	  }
	  if ($file == '../tool/jsonPlanning.php' or $file == '../tool/jsonResourcePlanning.php') {
	  	$file = substr($file, 0, -4).'_pdf.php';
	  }
	  header ('Content-Type: text/html; charset=UTF-8');
	  echo '<html>
            	  <head>
            	    <link rel="stylesheet" type="text/css" href="../view/css/jsgantt.css" />
            	    <link rel="stylesheet" type="text/css" href="../view/css/projeqtorPrint.css" />
            	    <link rel="stylesheet" type="text/css" href="../view/css/projeqtorFlat.css" />
	              </head><body>';
	  // Important : swicth user to the one who generated the report
	  $cronUser=getSessionUser();
	  setSessionUser(new User($this->idResource));
	  include '../report/'.$file;
	  // Important : swicth user back to Cron (admin)
	  setSessionUser($cronUser);
    echo '</body></html>';
    $result = ob_get_clean();
    ob_clean();
    require_once '../external/html2pdf/vendor/autoload.php';
    $pdf = new Html2Pdf($landscape);
    $pdf->writeHTML($result);
    $path = Parameter::getGlobalParameter('paramReportTempDirectory');
    $fileName=__DIR__.'/'.$path.$this->name.'.pdf';
    $pdf->output($fileName, 'F');
    $title = Parameter::getGlobalParameter('paramMailTitleReport');
    $title = str_replace('${dbName}', Parameter::getGlobalParameter('paramDbDisplayName'), $title);
    $title = str_replace('${report}', $report->name, $title);
    $title = str_replace('${date}', date('Y-m-d'), $title);
    if(!$title){
      $title = 'No title';
    }
    $message = Parameter::getGlobalParameter('paramMailBodyReport');
    $message = str_replace('${dbName}', Parameter::getGlobalParameter('paramDbDisplayName'), $message);
    $message = str_replace('${report}', $report->name, $message);
    $message = str_replace('${date}', date('Y-m-d'), $message);
    if(!$message){
      $message = 'No message';
    }
    $resource = new Resource($this->idReceiver, true);
    sendMail($resource->email, $title, $message, null, null, null, array($fileName), null,null,false,true);
    $email = explode(',', $this->otherReceiver);
    foreach ($email as $dest){
      if($dest != $resource->email and $dest != ''){
        sendMail($dest, $title, $message, null, null, null, array($fileName), null,null,false,true);
      }
    }
	}
	
	public static function drawAutoSendReportList($idUser, $idReceiver){
	  $noData = true;
	  $autoSendReport = new AutoSendReport();
	  $user = getSessionUser();
	  $listUser=array();
    if($idUser){
      $af=new Affectable($idUser);
      $listUser[$idUser]=($af->name)?$af->name:$af->userName;
    } else {
      $listUser = getListForSpecificRights('imputation');
    }
	  $result = "";
	  $result .='<div id="autoSendReportDiv" align="center" style="margin-top:20px;margin-bottom:20px; overflow-y:auto; width:100%;">';
	  $result .='  <table width="98%" style="margin-left:20px;margin-right:20px;border: 1px solid grey;">';
	  $result .='   <tr class="reportHeader">';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colIdUser').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:16%;text-align:center;vertical-align:center;">'.i18n('colSendName').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:15%;text-align:center;vertical-align:center;">'.i18n('colReport').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:18%;text-align:center;vertical-align:center;">'.i18n('colReceiver').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:8%;text-align:center;vertical-align:center;">'.i18n('colFrequency').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:8%;text-align:center;vertical-align:center;">'.i18n('colNextSend').'</td>';
	  $result .='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:20%;text-align:center;vertical-align:center;">'.i18n('colParameters').'</td>';
	  $result .='     <td style="border: 1px solid grey;height:60px;width:5%;text-align:center;vertical-align:center;">'.i18n('colActive').'</td>';
	  $result .='   </tr>';
	  foreach ($listUser as $id=>$name){
	    if($idReceiver){
	      $crit = array("idResource"=>$id, "idReceiver"=>$idReceiver);
	    }else{
	      $crit = array("idResource"=>$id);
	    }
	    $listAutoSendReport = $autoSendReport->getSqlElementsFromCriteria($crit);
	    $countLine = 0;
  	  foreach ($listAutoSendReport as $send){
  	    $noData = false;
  	    $resource = new Resource($send->idResource, true);
  	    $receiver = new Resource($send->idReceiver, true);
  	    $report = new Report($send->idReport, true);
  	  	$result .='<tr>';
  	  	if($countLine == 0){
    				$result .='<td style="border-top: 1px solid grey;border-left: 1px solid grey;border-right: 1px solid grey;height:40px;width:10%;text-align:left;vertical-align:center;">';
    				$result .='<table align="center"><tr>'
    						    .'<td style="text-align:right">'.formatUserThumb($resource->id, $resource->name, null, 22, 'right').'</td>'
    								.'<td style="white-space:nowrap;text-align:left">&nbsp'.$resource->name.'</td></tr>';
    				$result .=' </table></td>';
  			}else{
    				$result .='     <td style="border-left: 1px solid grey;border-right: 1px solid grey;height:40px;width:10%;"></td>';
  			}
  			$result .='<td style="border: 1px solid grey;height:40px;width:16%;text-align:center;vertical-align:center;">'.$send->name.'</td>';
  			$result .='<td style="border: 1px solid grey;height:40px;width:15%;text-align:center;vertical-align:center;">'.i18n($report->name).'</td>';
  			$result .='<td style="border: 1px solid grey;height:40px;width:18%;text-align:center;vertical-align:center;">';
  			$result .='<table align="center"><tr>'
  			    .'<td style="text-align:right">'.formatUserThumb($receiver->id, $receiver->name, null, 22, 'right').'</td>'
  			    .'<td style="white-space:nowrap;text-align:left">&nbsp'.$receiver->name.'</td></tr>';
  			if($send->otherReceiver != ''){
  			  $listOtherReceiver = explode(',', $send->otherReceiver);
  			  foreach ($listOtherReceiver as $otherReceiver){
  			    if($otherReceiver != $receiver->email){
    			    $result .='<tr><td colspan="2">';
    			    $result .= $otherReceiver;
    			    $result .='</td></tr>';
  			    }
  			  }
  			}
  			$result.='</table>';
  			$result .='</td>';
  			$result .='<td style="border: 1px solid grey;height:40px;width:8%;text-align:center;vertical-align:center;">'.i18n($send->sendFrequency).'</td>';
  			$result .='<td style="border: 1px solid grey;height:40px;width:8%;text-align:center;vertical-align:center;font-style:italic;">'.htmlFormatDateTime(date('Y-m-d H:i', $send->nextTime)).'</td>';
  			$result .='<td style="border: 1px solid grey;height:40px;width:20%;text-align:center;vertical-align:center;">';
  			$param = json_decode($send->reportParameter);
  			$strParam = '';
  			$separator=' | ';
  			foreach ($param as $name=>$value){
  			  if(is_array($value)){
  			    $class = substr($name, 2);
  			    if(Security::checkValidClass($class)){
  			      $array = array();
  			      foreach ($value as $id){
  			        if(Security::checkValidId($id)){
  			          $obj = new $class($id, true);
  			          if($name = 'idProfile'){
  			          	$array[$id]=substr($obj->name, 7);
  			          }else{
  			          	$array[$id]=$obj->name;
  			          }
  			        }else{
  			          $array[$id]=$id;
  			        }
  			      }
  			      $value = implode(', ', $array);
  			    }else{
  			      $value = implode(', ', $value);
  			    }
  			  }
  			  if(trim($value) != ''){
    		    if($name == 'idProject'){
    		      $proj = new Project($value, true);
    		    	$strParam .= i18n('Project').' : '.$proj->name.$separator;
    		    	continue;
    		    }
    		    if($name == 'idResource'){
    		      $res = new Resource($value, true);
    		    	$strParam .= i18n('colIdResource').' : '.$res->name.$separator;
    		    	continue;
    		    }
    		    if($name == 'idTeam'){
    		      $team = new Team($value, true);
    		    	$strParam .= i18n('team').' : '.$team->name.$separator;
    		    	continue;
    		    }
    		    if($name == 'idOrganization'){
    		      $org = new Organization($value, true);
    		    	$strParam .= i18n('organization').' : '.$org->name.$separator;
    		    	continue;
    		    }
    		    if($name == 'yearSpinner'){
    		      $strParam .= i18n('setTo'.ucfirst($value).'Year').$separator;
    		      continue;
    		    }
    		    if($name == 'monthSpinner'){
    		      if($value == 'current' or $value == 'previous'){
    		        $strParam .= i18n('setTo'.ucfirst($value).'Month').$separator;
    		      }else{
    		        $strParam .= i18n('startMonth').' : '.$value.$separator;
    		      }
    		      continue;
    		    }
    		    if($name == 'weekSpinner'){
    		    	$strParam .= i18n('setTo'.ucfirst($value).'Week').$separator;
    		    	continue;
    		    }
    		    if($name == 'startDate'){
    		      if($value == 'currentDate'){
    		        $strParam .= i18n('colStartDate').' : '.i18n($value).$separator;
    		      }else{
    		        $strParam .= i18n('colStartDate').' : '.$value.$separator;
    		      }
    		      continue;
    		    }
    		    if($name != 'reportFile' and $name != 'reportId' and $name != 'orientation' and $name != 'reportCodeName' and $name != 'page'
    		    and $name != 'print' and $name != 'report' and $name != 'reportName' and $name != 'periodValue' and $name != 'periodType' and $name != 'objectClassList'){
    		      if($name == 'periodScale'){
    		        $strParam .= i18n($name).' : '.i18n($value).$separator;
  		        }else if($name == 'nbOfMonths'){
    		        	$strParam .= i18n('numberOfMonths').' : '.$value.$separator;
    		      }else{
    		        if(substr($name, 0, 2) == 'id'){
    		        	$idValue = SqlList::getNameFromId(substr($name, 2), $value);
    		        	$strParam .= i18n('col'.ucfirst($name)).' : '.$idValue.$separator;
    		        }else if($name == 'responsible' or $name == 'requestor' or $name == 'issuer' or $name == 'requestor'){
    		          if($name == 'requestor'){
    		            $idValue = SqlList::getNameFromId('Contact', $value);
    		          }else{
    		            $idValue = SqlList::getNameFromId('resource', $value);
    		          }
    		          $strParam .= i18n('col'.ucfirst($name)).' : '.$idValue.$separator;
    		        }else {
    		          $strParam .= i18n('col'.ucfirst($name)).' : '.$value.$separator;
    		        }
    		      }
    		    }
    		    if($name == 'periodValue'){
    		      if(trim(strpos($value, 'previous')) != '' or trim(strpos($value, 'current')) != ''){
    		      	continue;
    		      }else{
    		        $strParam .= i18n('colPeriod').' : '.$value.$separator;
    		      }
    		    }
  			  }
  			}
  			$strParam = substr($strParam, 0, -2);
  			$strParam=str_replace(' ','&nbsp;',$strParam);
  			$strParam=str_replace('&nbsp;|',' |',$strParam);
  			$result .= $strParam;
  			$result .='</td>';
  			$backgroud = '#a3d179';
  			if($send->idle){
  			  $backgroud = '#ff7777';
  			}
  			$result .='<td style="border: 1px solid grey;height:40px;width:5%;text-align:center;vertical-align:center;">';
  			$result .='<table width="100%"><tr><td width="50%" style="background-color: '.$backgroud.';border-right:1px solid grey;height:40px;">';
  			$checked = '';
  			if(!$send->idle){
  				$checked = 'checked';
  			}
  			$result .=' <div dojoType="dijit.form.CheckBox" type="checkbox" name="activeCheckBox'.$send->id.'" id="activeCheckBox'.$send->id.'" '.$checked.'>';
  			$result .='<script type="dojo/method" event="onChange">activeAutoSendReport('.$send->id.')</script>';
    		$result .=' </div>';
    		$result .='</td><td width="50%">';
  			$result .= '<a onClick="removeAutoSendReport('.$send->id.');" title="'.i18n('removeAutoSendReport').'" > '.formatMediumButton('Remove').'</a>';
  			$result .='</td></tr></table>';
  			$result .= '</td>';
  			$result .='</tr>';
  	  	$countLine++;
  	  }
	  }
	  if($noData==true){
	  	$result .='<tr><td colspan="8">';
	  	$result .='<div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noDataFound').'</div>';
	  	$result .='</td></tr>';
	  }
	  $result .='  </table>';
	  $result .='</div>';
	  echo $result;
	}
	public static function htmlReturnOptionForMinutesHoursCron($selection, $isHours=false, $isDayOfMonth=false, $required=false) {
		$arrayWeekDay=array();
		$max=59;
		$start=0;
		$modulo=5;
		if($isHours){
			$max=23;
			$start=0;
			$modulo=1;
		}
		if($isDayOfMonth){
			$max=31;
			$start=1;
			$modulo=1;
		}
		for($i=$start;$i<=$max;$i++){
			$key=$i;
			//if($key<10)$key='0'.$key;
			if ( $i % $modulo==0) $arrayWeekDay[$key]=$key;
		}
		$result="";
		if (! $required) {
			$result.='<option value="*" '.(($selection=='*')?'selected':'').'>'.i18n('all').'</option>';
		}
		foreach($arrayWeekDay as $key=>$line) {
			$result.= '<option value="' . $key . '"';
			if ($selection!==null and $key==$selection ) { $result.= ' SELECTED '; }
			$result.= '>'.$line.'</option>';
		}
		return $result;
	}
}