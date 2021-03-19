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
 * Action is establised during meeting, to define an action to be followed.
 */ 
require_once('_securityCheck.php');
class Audit extends SqlElement {
	
	// List of fields that will be exposed in general user interface
	public $_sec_description;
	public $id; // redefine $id to specify its visible place
	public $sessionId;
	public $auditDay;
	public $idUser;
	public $userName;
	public $platform;
	public $browser;
	public $browserVersion;
	public $userAgent;
	public $_sec_connectionStatus;
	public $connectionDateTime;
	public $lastAccessDateTime;
	public $disconnectionDateTime;
	public $duration;
	public $durationSeconds;
	public $durationDisplay;
	public $idle;
	public $_spe_disconnectButton;
	public $requestRefreshParam;
	public $requestRefreshProject;
	public $requestDisconnection;
	public $_noHistory;
	public $_readOnly = true;
	
	public static $_lastAudit=null;
	
	// Define the layout that will be used for lists
	private static $_layout = '
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="sessionId" width="15%" ># ${sessionId}</th>
    <th field="userName" width="15%" >${idUser}</th>
    <th field="connectionDateTime" formatter="dateTimeFormatter" width="12%" >${connection}</th>
    <th field="lastAccessDateTime" formatter="dateTimeFormatter" width="12%"  >${lastAccess}</th>
    <th field="durationDisplay" width="10%"  >${duration}</th>
    <th field="platform" width="10%" >${platform}</th>
    <th field="browser" width="10%" >${browser}</th>
    <th field="requestDisconnection" width="6%" formatter="booleanFormatter" >${requestDisconnection}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
	private static $_fieldsAttributes = array (
			"auditDay" => "hidden",
			"disconnectionDateTime" => "hidden",
			"idUser" => "hidden",
			"requestRefreshParam" => "hidden",
			"requestRefreshProject" => "hidden",
	    "duration"=>"hidden",
	    "durationSeconds"=>"hidden"
	);
	
	private static $_colCaptionTransposition = array(
	    'connectionDateTime'=>'connection',
	    'durationSeconds'=>'duration',
	    'durationDisplay'=>'duration',
			'lastAccessDateTime'=> 'lastAccess');
	
	/**
	 * ==========================================================================
	 * Constructor
	 * 
	 * @param $id the
	 *        	id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct ( $id );
	}
	
	/**
	 * ==========================================================================
	 * Destructor
	 * 
	 * @return void
	 */
	function __destruct() {
		parent::__destruct ();
	}
	
	// ============================================================================**********
	// GET STATIC DATA FUNCTIONS
	// ============================================================================**********
	
	/**
	 * ==========================================================================
	 * Return the specific layout
	 * 
	 * @return the layout
	 */
	protected function getStaticLayout() {
		return self::$_layout;
	}
	
	/** ============================================================================
	 * Return the specific colCaptionTransposition
	 * @return the colCaptionTransposition
	 */
	protected function getStaticColCaptionTransposition($fld=null) {
		return self::$_colCaptionTransposition;
	}
	
	/**
	 * ==========================================================================
	 * Return the specific fieldsAttributes
	 * 
	 * @return the fieldsAttributes
	 */
	protected function getStaticFieldsAttributes() {
		return self::$_fieldsAttributes;
	}
	static function updateAudit() {
		// $source can be "main" (from projeqtor.php), "login" (from loginCheck.php) or "alert" (from checkAlertToDisplay.php)
		global $remoteDb;
		if (isset($remoteDb) and $remoteDb) return;
		if (self::$_lastAudit and self::$_lastAudit==date("Y-m-d H:i:s")) return; // Do not save audit more than once each second
		self::$_lastAudit=date("Y-m-d H:i:s");
		if (! getSessionUser() )
			return;
		$audit = SqlElement::getSingleSqlElementFromCriteria ( 'Audit', array (
				'sessionId' => session_id () 
		) );
		if (! $audit->id) {
			$audit->sessionId = session_id ();
			$audit->auditDay = date ( 'Ymd' );
			$audit->connectionDateTime = date ( 'Y-m-d H:i:s' );
			$user = getSessionUser();
			$audit->idUser = $user->id;
			$audit->userName = $user->name;
			$audit->userAgent = $_SERVER ['HTTP_USER_AGENT'];
			$browser = self::getBrowser ( null, true );
			$audit->platform = $browser ['platform'];
			$audit->browser = $browser ['browser'];
			$audit->browserVersion = $browser ['version'];
			$audit->disconnectionDateTime = null;
		} else if ($audit->requestDisconnection) {
			$script = basename ( $_SERVER ['SCRIPT_NAME'] );
			if ($script == 'checkAlertToDisplay.php') {
				echo '<b>' . i18n ( 'disconnect' ) . '</b>';
				echo '<br/>' . '<br/>';
				echo i18n ( 'disconnected' );
				echo '<input type="hidden" id="idAlert" name="idAlert" value="" ./>';
				echo '<input type="hidden" id="alertType" name="alertType" value="INFO" ./>';
				Audit::finishSession ();
				exit ();
			}
		} else {
			if ($audit->requestRefreshParam) {
				$audit->requestRefreshParam = 0;
				Parameter::refreshParameters ();
			}
			if ($audit->requestRefreshProject and basename ( $_SERVER ['SCRIPT_NAME'] ) == 'checkAlertToDisplay.php') {
				$audit->requestRefreshProject = 0;
				echo '<input type="hidden" id="requestRefreshProject" name="requestRefreshProject" value="true" ./>';
			}
		}
		$audit->lastAccessDateTime = date ( 'Y-m-d H:i:s' );
		$audit->calculateDuration();
		$audit->idle = 0;
		$audit->auditDay = date ( 'Ymd' );
		$result = $audit->save ();
	}
	private function calculateDuration() {
	  // date_diff is only supported from PHP 5.3
	  date_default_timezone_set('UTC');
	  $now=strtotime("now");
	  $duration=strtotime($this->lastAccessDateTime,$now) - strtotime($this->connectionDateTime,$now);
	  $this->durationSeconds=$duration;
	  if ($duration<86400) $this->duration=date( 'H:i:s',$duration );
	  else $this->duration='23:59:59';
	  // Display duration as days.Hours:Minutes.Seconds
	  $disp='';
	  if ($duration>=86400) {
	    $days=floor($duration/86400);
	    $duration=($duration%86400);
	    $disp.=$days.'.';
	  } 
	  $this->durationDisplay=$disp.date('H:i:s',$duration);
	  $tz=Parameter::getGlobalParameter('paramDefaultTimezone');
	  if ($tz) date_default_timezone_set($tz); else date_default_timezone_set('Europe/Paris');
	}
	static function finishSession() {
	  global $remoteDb;
	  global $simuIndex;
	  if (isset($remoteDb) and $remoteDb) return;
	  if (isset($simuIndex)) return;
		$audit = SqlElement::getSingleSqlElementFromCriteria ( 'Audit', array (
				'sessionId' => session_id () 
		) );
		if ($audit->id) {
			$audit->lastAccessDateTime = date ( 'Y-m-d H:i:s' );
			$audit->requestRefreshParam = 0;
			$audit->disconnectionDateTime = $audit->lastAccessDateTime;
			$audit->calculateDuration();
			$audit->idle = 1;
			if($audit->idle== 1){
			  $audit->sessionId=$audit->sessionId.'_'.date('YmdHis');
			}
			$audit->save ();
		}
		AuditSummary::updateAuditSummary ( $audit->auditDay );
		enableCatchErrors();
		enableSilentErrors();
		$user = getSessionUser();
		$user->disconnect();
		// terminate the session
		if (ini_get ( "session.use_cookies" )) {
			$params = session_get_cookie_params ();
			// TODO : use browser time zone to compute time
			// date_default_timezone_set("UTC");
			setcookie ( session_name (), '', time ()- 42000, $params ["path"], $params ["domain"], $params ["secure"], $params ["httponly"] );
		}
		try {
			resetSession();
			error_reporting(0);
      //session_write_close();
			@session_destroy();
		} catch ( Exception $e ) {
			// tried twice : OK let's give up.
		}
		disableCatchErrors();
		disableSilentErrors();
	}
	static function getBrowser() {
	  if (!isset($_SERVER ['HTTP_USER_AGENT'])) return null;
		$u_agent = $_SERVER ['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$ub = 'Unknown';
		$version = "";
		
		// First get the platform?
		if (preg_match ( '/linux/i', $u_agent )) {
			$platform = 'Linux';
		} elseif (preg_match ( '/macintosh|mac os x/i', $u_agent )) {
			$platform = 'Mac';
		} elseif (preg_match ( '/windows|win32/i', $u_agent )) {
			$platform = 'Windows';
		}
		$u_agent_search=$u_agent;
		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match ( '/MSIE/i', $u_agent ) && ! preg_match ( '/Opera/i', $u_agent )) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		} elseif (preg_match ( '/Trident/i', $u_agent )) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
			$u_agent_search=str_replace("rv:","MSIE/",$u_agent_search);  
	  } elseif (preg_match ( '/Firefox/i', $u_agent )) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		} elseif (preg_match ( '/Chrome/i', $u_agent )) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		} elseif (preg_match ( '/Safari/i', $u_agent )) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		} elseif (preg_match ( '/Opera/i', $u_agent )) {
			$bname = 'Opera';
			$ub = "Opera";
		} elseif (preg_match ( '/Netscape/i', $u_agent )) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}
		
		// finally get the correct version number
		$known = array (
				'Version',
				$ub,
				'other' ,
				'rv'
		);
		$pattern = '#(?P<browser>' . join ( '|', $known ) . ')[/ ]+(?P<version>[0-9.|a-zA-Z.]*)#';
		if (! preg_match_all ( $pattern, $u_agent_search, $matches )) {
			// we have no matching number just continue
		} else {
		// see how many we have
			$i = count ( $matches ['browser'] );
			if ($i != 1) {
				// we will have two since we are not using 'other' argument yet
				// see if version is before or after the name
				if (strripos ( $u_agent, "Version" ) < strripos ( $u_agent, $ub ) or ! isset ( $matches ['version'] [1] )) {
					$version = $matches ['version'] [0];
				} else {
					$version = $matches ['version'] [1];
				}
			} else {
				$version = $matches ['version'] [0];
			}
		}
		// check if we have a number
		if ($version == null || $version == "") {
			$version = "?";
		}
		return array (
				'userAgent' => $u_agent,
				'browser' => $bname,
				'version' => $version,
				'platform' => $platform,
				'pattern' => $pattern 
		);
	}
	public function drawSpecificItem($item) {
		global $print, $comboDetail;
		$result = "";
		if ($item == 'disconnectButton') {
			$result .= "<table><tr><td class='label' valign='top'><label>&nbsp;</label>";
			$result .= "</td><td>";
			$result .= '<button id="disconnectSession" dojoType="dijit.form.Button" showlabel="true"';
			if ($this->sessionId == session_id ()) {
				$result .= ' disabled="disabled" ';
			}
			$result .= ' title="' . i18n ( 'disconnectSession' ) . '" style="vertical-align: middle;">';
			$result .= '<span>' . i18n ( 'disconnect' ) . '</span>';
			$result .= '<script type="dojo/connect" event="onClick" args="evt">';
			$result .= '    loadContent("../tool/disconnectSession.php?idAudit=' . htmlEncode($this->id) . '","resultDivMain","objectForm",true);';
			$result .= '</script>';
			$result .= '</button>';
			$result .= "</td></tr></table>";
		}
		return $result;
	}
	public function save() {
	  global $remoteDb;
	  if (isset($remoteDb) and $remoteDb) return;
	  return parent::save();
	}
}
?>