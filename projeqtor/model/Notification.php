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
class Notification extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
    public $id;    // redefine $id to specify its visible place 
    public $idNotificationDefinition;
    public $idNotifiable;
    public $notifiedObjectId;
    public $creationDateTime;
    public $name;
    public $idNotificationType;
    public $idMenu;
    public $title;
    public $notificationDate;
    public $notificationTime;
    public $sendEmail=0;
    public $content;
    public $idPluginIdVersion;
  public $_sec_treatment;
    public $idUser;
    public $idResource;
    public $idStatusNotification;
    public $emailSent=0;
    public $idle;  
  //public $_sec_Link;
    //public $_Link=array();
    public $_Attachment=array();
    public $_Note=array();

  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="name" width="30" >${name}</th>
    <th field="notificationDate" width="8%" formatter="dateFormatter">${targetDate}</th>
    <th field="colorNameStatusNotification" width="10%" formatter="colorTranslateNameFormatter">${idStatus}</th>
    <th field="colorNameNotificationType" width="10%" formatter="colorTranslateNameFormatter">${type}</th>
    <th field="nameUser" formatter="thumbName22" width="12%" >${receiver}</th>
    <th field="nameResource" formatter="thumbName22" width="12%" >${issuer}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array(
                                            "name"                      => "required", 
                                            "notifiedObjectId"          => "hidden",
                                            "idNotificationType"        => "required",
                                            "idStatusNotification"      => "required",
                                            "notificationDate"          => "required,nobr",
                                            "idUser"                    => "required",
                                            "idResource"                => "readonly",
                                            "title"                     => "required",
                                            "content"                   => "required",
                                            "idNotificationDefinition"  => "hidden",
                                            "idNotifiable"              => "hidden",
                                            "idMenu"                    => "hidden",
                                            "creationDateTime"          => "hidden",
                                            "sendEmail"                 => "hidden",
                                            "emailSent"                 => "hidden",
                                            "idPluginIdVersion"         => "hidden",
                                            "idle"                      => "nobr"
                                        );  
  
  private static $_colCaptionTransposition = array(
                                                   "idUser"             => "receiver",
                                                   "idResource"         => "issuer",
                                                   "notificationDate"   => "targetDate",
                                                   "idStatusNotification"=> "idStatus",
                                                   "notificationTime"   => "time",
      "idNotificationType"=>"type"
                                                  );
  
  private static $_databaseColumnName = array();
  
//    private static $_databaseTableName = '';
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if (!is_null($id)) {
        self::$_fieldsAttributes['creationDateTime'] = 'readonly';
        
        if (!is_null($this->idNotificationDefinition)) {
            self::$_fieldsAttributes['idNotificationDefinition'] = 'readonly';
//            self::$_fieldsAttributes['idMenu'] = 'readonly';
            self::$_fieldsAttributes['idNotifiable'] = 'readonly,nobr';
            self::$_fieldsAttributes['notifiedObjectId'] = 'readonly,size1/3';
            self::$_fieldsAttributes['idNotificationType'] = 'readonly';
            self::$_fieldsAttributes['name'] = 'readonly';
            self::$_fieldsAttributes['title'] = 'readonly';
            self::$_fieldsAttributes['content'] = 'readonly';
            self::$_fieldsAttributes['notificationDate'] = 'readonly,nobr';
            if (is_null($this->notificationTime)) {
                self::$_fieldsAttributes['notificationTime'] = 'hidden';                
            } else {
                self::$_fieldsAttributes['notificationTime'] = 'readonly';                
            }
            self::$_fieldsAttributes['content'] = 'readonly';
            self::$_fieldsAttributes['idResource'] = 'readonly';
            self::$_fieldsAttributes['idUser'] = 'readonly';
            self::$_fieldsAttributes['notificationType'] = 'readonly';
            self::$_fieldsAttributes['sendEmail'] = 'readonly';
            if ($this->sendEmail) {
                self::$_fieldsAttributes['emailSent'] = 'readonly';
            }
        }
    }
    if ($this->id<=0) {
        self::$_fieldsAttributes['idStatusNotification'] = 'hidden';
        $this->idStatusNotification = 1;
        self::$_colCaptionTransposition['idResource'] = 'receiver';
        self::$_fieldsAttributes['idResource'] = 'required';        
    }
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }


// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    return $colScript;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $userId = getSessionUser()->id;
    // For new Notification
//     if (is_null($this->id)) {
//         // Inits the status to unread
//         $this->idStatusNotification = 1;
//         // Inits creationDate with today
//         $this->creationDateTime=date('Y-m-d H:i:s');
//         // idUser is in fact the receiver
//         $this->idUser = $this->idResource;
//         // idResource = connected user ==> $userId
//         $this->idResource = $userId;
//     }
    if ( ! $this->id ) {
      // Inits the status to unread
      $this->idStatusNotification = 1;
      // Inits creationDate with today
      $this->creationDateTime=date('Y-m-d H:i:s');
      if ($this->idNotificationDefinition) {
        if ( ! $this->idUser) {$this->idUser = $this->idResource;}
        if ( ! $this->idResource){ $this->idResource = $userId;}
      } else {
        // idUser is in fact the receiver
        $this->idUser = $this->idResource;
        // idResource = connected user ==> $userId
        $this->idResource = $userId;
      }
    }
    
    $old = $this->getOld();
    // Status change AND user connected = receiver => don't sent email
    if($this->idStatusNotification !== $old->idStatusNotification and $userId === $this->idUser and $this->id!==null) {
        $this->emailSent = 1;
    }
    
    // Idle = 1 => status = 'read'
    if ($this->idle) {
        $this->idStatusNotification = 2;
    }
    $result = parent::save();
    
    return $result;
    
  }
  
  public function sendEmail() {
      if ($this->emailSent===1 or $this->sendEmail===0) {return "OK";}

      // Retrieve the receiver email
//      $receiver = new User($this->idResource);
      $receiver = new User($this->idUser);
      if ($receiver->email==="") {return "OK";}
      
      // Send email
      $resultMail = sendMail($receiver->email, $this->title, $this->content);
      if ($resultMail===true) {
        // Set emailSent to 1
        $this->emailSent = 1;
        $result = parent::save();
        return $result;
      } else {return "KO";}
  }
  
  public function countUnreadNotifications($idMenu=null) {
      $arrayCountUnreadNofications = [ 
                                        "total"       => 0,
                                        "ALERT"       => 0,
                                        "WARNING"     => 0,
                                        "INFO"        => 0
                                     ];
      // Unread status
//      $unreadStatusId = SqlElement::getSingleSqlElementFromCriteria("Status", array("name" => "unread"))->id;
      
      // Types
      $type = new Type();
      $lstTypes = $type->getSqlElementsFromCriteria(array("scope" => "Notification"));
      $lstTypesIdName= [];
      foreach ($lstTypes as $type) {
          $lstTypesIdName[$type->id] = $type->name;
      }
      
      //User Connected
      $userId = getSessionUser()->id;
      
      // Today Date
      $currentDate = new DateTime();
      $theCurrentDate = $currentDate->format('Y-m-d');
      
      // Where for retrieve notifications
      $where  = "idStatusNotification = 1";
      $where .= " AND idle = 0";
      $where .= " AND idUser = $userId";
      //$where .= " AND notificationDate <= '$theCurrentDate'";
      //$where .= " AND IF(ISNULL(notificationTime) OR notificationDate<DATE(NOW()),(1=1),notificationTime<TIME(NOW()))";
      $where .= " AND (      notificationDate<'$theCurrentDate'";
      $where .= "       OR ( notificationDate='$theCurrentDate' AND notificationTime IS NULL )";
      $timeNow=(Sql::isPgsql())?'CURRENT_TIME':'TIME(NOW())';
      $where .= "       OR ( notificationDate='$theCurrentDate' AND notificationTime IS NOT NULL AND notificationTime<$timeNow )";
      $where .= "     )";
      
      if (!is_null($idMenu)) {
          $where .= " AND idMenu = $idMenu";
      }
      
      // List of unread notifications for connected user and notificationDate <= current date
      $lstNotif = $this->getSqlElementsFromCriteria(null, false, $where);
      if (is_null($lstNotif)) {
          return $arrayCountUnreadNofications;      
      }
      
      $arrayCountUnreadNofications['total'] = count($lstNotif);
      foreach($lstNotif as $notif) {
          // Alerts
          $keyType = array_search('ALERT', $lstTypesIdName);
          if ($notif->idNotificationType == $keyType) {
              $arrayCountUnreadNofications['ALERT']++;          
          }
          
          // Warnings
          $keyType = array_search('WARNING', $lstTypesIdName);
          if ($notif->idNotificationType == $keyType) {
              $arrayCountUnreadNofications['WARNING']++;          
          }

          // Informations
          $keyType = array_search('INFO', $lstTypesIdName);
          if ($notif->idNotificationType == $keyType) {
              $arrayCountUnreadNofications['INFO']++;                    }
      }
      
      return $arrayCountUnreadNofications;
  }
    
}
?>