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
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
require_once('_securityCheck.php');
class DocumentMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idDocumentType;
  public $idProject;
  public $idProduct;
  public $idDocumentDirectory;
  public $documentReference;
  public $externalReference;
  public $idAuthor;
  public $tags;
  public $idUser;
  public $creationDate;
  public $idle;
  public $cancelled;
  public $_lib_cancelled;
  
  public $_sec_Version; 
  public $idVersioningType;
  public $idDocumentVersion;
  public $idDocumentVersionRef;
  public $idStatus;
  public $_DocumentVersion=array();
  
  public $_sec_Approver;
  public $_Approver=Array();
  public $_spe_buttonSendMail;
  
  public $_sec_Lock;
  public $_spe_lockButton;
  public $locked;
  public $idLocker;
  public $lockedDate;
  
  public $version;
  public $revision;
  public $draft;
  public $_sec_Link;
  public $_Link=array();
  public $_Note=array();
  
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%"># ${id}</th>
    <th field="nameProject" width="10%">${idProject}</th>
    <th field="nameProduct" width="10%">${idProduct}</th>
    <th field="nameDocumentType" width="8%">${type}</th>
    <th field="name" width="20%">${name}</th>
    <th field="documentReference" width="20%">${documentReference}</th>
    <th field="colorNameStatus" width="8%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameDocumentVersion" width="6%">${currentDocumentVersion}</th>
    <th field="nameDocumentVersionRef" width="6%">${referenceDocumentVersion}</th>
    <th field="locked" width="4%" formatter="booleanFormatter">${locked}</th>
    <th field="idle" width="4%" formatter="booleanFormatter">${idle}</th>
    ';
//<th field="nameCurrentVersion" width="10%">${idCurrentVersion}</th>
//<th field="nameCurrentRefVersion" width="10%">${idCurrentRefVersion}</th>
    
   private static $_fieldsAttributes=array(
    "id"=>"nobr",
    "idProject"=>"required",
    "idStatus"=>"required",
    "locked"=>"readonly",
    "idLocker"=>"readonly",
    "lockedDate"=>"readonly",
    "idDocumentDirectory"=>"required",
    "idDocumentType"=>"required",
    "idVersioningType"=>"required",
    "idDocumentVersion"=>"readonly",
    "idDocumentVersionRef"=>"hidden",
    "version"=>"hidden",
    "revision"=>"hidden",
    "draft"=>"hidden",
    "idStatus"=>"readonly",
    "documentReference"=>"readonly",
   	"idle"=>"nobr",
    "cancelled"=>"nobr"
   );
   
   private static $_colCaptionTransposition = array(
       'idDocumentType' => 'type',
       'idDocumentVersion' => 'currentDocumentVersion',
       'idDocumentVersionRef'=>'referenceDocumentVersion'
   );
   private static $_databaseColumnName = array();
   
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if (!$this->id and sessionValueExists('Directory')) {
    	$this->idDocumentDirectory=getSessionValue('Directory');
    	self::$_fieldsAttributes['idDocumentDirectory']="readonly";
    	$dir=new DocumentDirectory($this->idDocumentDirectory);
    	$this->idDocumentType=$dir->idDocumentType;
    	$this->idProduct=$dir->idProduct;
    	$this->idProject=$dir->idProject;
    } 
    if ($this->id and $this->idDocumentVersion) {
    	self::$_fieldsAttributes['idVersioningType']="readonly";
    }
    if (!$this->id and ! $this->idAuthor and sessionUserExists()) {
    	$user=getSessionUser();
    	$this->idAuthor=$user->id;
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
    
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
 
    /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  /** ========================================================================
   * Return the specific databaseColumnName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  public function drawSpecificItem($item){
  	global $print;
    $result="";
    if ($item=='lockButton' and !$print and $this->id) {
    	if ($this->locked) {
        $canUnlock=false;
        $user=getSessionUser();
        if ($user->id==$this->idLocker) {
        	$canUnlock=true;
    	  } else {
          $right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->getProfile($this), 'scope'=>'document'));        
          if ($right) {
            $list=new ListYesNo($right->rightAccess);
            if ($list->code=='YES') {
              $canUnlock=true;
            }
          }  
    	  }
    	  if ($canUnlock) {
	    		$result .= '<tr><td></td><td>';
	        $result .= '<button id="unlockDocument" dojoType="dijit.form.Button" showlabel="true" class="roundedVisibleButton"'; 
	        $result .= ' title="' . i18n('unlockDocument') . '" >';
	        $result .= '<span>' . i18n('unlockDocument') . '</span>';
	        $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
	        $result .=  '  unlockDocument();';
	        $result .= '</script>';
	        $result .= '</button>';
	        $result .= '</td></tr>';
    	  }
    	} else {
	    	$result .= '<tr><td></td><td>';
	    	$result .= '<button id="lockDocument" dojoType="dijit.form.Button" showlabel="true"'; 
	      $result .= ' title="' . i18n('lockDocument') . '" class="roundedVisibleButton" >';
	      $result .= '<span>' . i18n('lockDocument') . '</span>';
	      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
	      $result .=  '  lockDocument();';
	      $result .= '</script>';
	      $result .= '</button>';
	      $result .= '</td></tr>';
    	}
    	$result .= '<input type="hidden" id="idCurrentUser" name="idCurrentUser" value="' . getSessionUser()->id . '" />';
    	return $result;
    }
    if ($item=='buttonSendMail') {
    	if ($print or ! $this->id) {
    		return "";
    	}
    	$result .= '<tr><td colspan="2">';
    	$result .= '<button id="sendInfoToApprovers" dojoType="dijit.form.Button" showlabel="true"';
    	$result .= ' title="' . i18n('sendInfoToApprovers') . '"class="roundedVisibleButton" >';
    	$result .= '<span>' . i18n('sendInfoToApprovers') . '</span>';
    	$result .=  '<script type="dojo/connect" event="onClick" args="evt">';
    	$result .= '   if (checkFormChangeInProgress()) {return false;}';
    	$result .=  '  var email="";';
    	$result .=  '  if (dojo.byId("email")) {email = dojo.byId("email").value;}';
    	$result .=  '  loadContent("../tool/sendMail.php","resultDivMain","objectForm",true);';
    	$result .= '</script>';
    	$result .= '</button>';
    	$result .= '</td></tr>';
    	return $result;
    }
  }
  
  public function control() {
  	$result="";

  	if (!trim($this->idProject) and !trim($this->idProduct)) {
  		$result.="<br/>" . i18n('messageMandatory',array(i18n('colIdProject') . " " . i18n('colOrProduct')));
  	}
  	//gautier
  	if( trim($this->idDocumentDirectory) != "" and trim($this->idProject) != ""){
  	   $dir = new DocumentDirectory($this->idDocumentDirectory);
  	   $proj = new Project($dir->idProject,true);
  	   $subProjList= $proj->getSubProjectsList(false);
  	   $subProjList = array_flip($subProjList);
  	   array_push($subProjList, $dir->idProject);
  	   if(trim($dir->idProject) != "" and !in_array($this->idProject, $subProjList)){
  	     $result.="<br/>" . i18n("projectMustBeIn", array($proj->name));
  	   }
  	}
  	if(trim($this->idProject) == "" and trim($this->idDocumentDirectory) != ""){
  	  $dir = new DocumentDirectory($this->idDocumentDirectory);
  	  $proj = new Project($dir->idProject,true);
  	  if(trim($dir->idProject) ) {
  	    $result.="<br/>" . i18n("projectMustBeIn", array($proj->name));
  	  }
  	}
  	//end
  	$defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;  	 
  }

  public function getNewVersion($type, $draft) {
    if ($type=="major") {
      
    } else if ($type=="minor") {
      
    } else { // 'none'
      
    }
  }
  
  public function save() {
  	$old=$this->getOld();
  	$sep=Parameter::getGlobalParameter('paramPathSeparator');
  	if ($old->name!=$this->name) {
  		$this->documentReference=str_replace($old->name, $this->name, $this->documentReference);
  	}
  	$result=parent::save();
  	if ($old->idDocumentDirectory!=$this->idDocumentDirectory and $this->id) {
  		// directory changed, must must files !
  		$oldDir=New DocumentDirectory($old->idDocumentDirectory);
  		$oldLoc=$oldDir->getLocation();
  		$newDir=New DocumentDirectory($this->idDocumentDirectory);
  		$newLoc=$newDir->getLocation();
  		if ($oldLoc!=$newLoc) {
  			if (! is_dir($newLoc)) {
  				mkdir($newLoc,0777,true);
  			}
  			$vers=new DocumentVersion();
  			$versList=$vers->getSqlElementsFromCriteria(array('idDocument'=>$this->id));
	  		foreach ($versList as $vers) {
	  		  rename($oldLoc.$sep.$vers->fileName.'.'.$vers->id,$newLoc.$sep.$vers->fileName.'.'.$vers->id);
	  		}
  		}
  	}
  	if ($old->documentReference!=$this->documentReference and $this->id) {
  	  $vers=new DocumentVersion();
      $versList=$vers->getSqlElementsFromCriteria(array('idDocument'=>$this->id));
      foreach ($versList as $vers) {
        $vers->save(true);
      }
  	}
  	//gautier 
//   	if($this->idProject == "" and $this->idDocumentDirectory != ""){
//   	  $dir = new DocumentDirectory($this->idDocumentDirectory);
//   	  $this->idProject = $dir->idProject;
//   	  $this->save();
//   	}
  	Tag::saveTagList($this->tags,$old->tags,'Document');
  	return $result;
  }
  
  public function sendMailToApprovers($onlyNotApproved=true) {
  	$crit=array('refType'=>'DocumentVersion', 'refId'=>$this->idDocumentVersion);
  	if ($onlyNotApproved) {
  		$crit['approved']='0';
  	}
  	$app=new Approver();
  	$appList=$app->getSqlElementsFromCriteria($crit);
  	$dest="";
  	foreach ($appList as $app) {
  		$res=new Affectable($app->idAffectable);
  		$resMail=(($res->name)?$res->name:$res->userName);
  		$resMail.=(($res->email)?' <'.$res->email.'>':'');
  		$resMail=$res->email;
  		$dest.=($dest)?', ':'';	
  		$dest.=$resMail;
  	}  	
  	$title=$this->parseMailMessage(Parameter::getGlobalParameter('paramMailTitleApprover'));
  	$msg=$this->parseMailMessage(Parameter::getGlobalParameter('paramMailBodyApprover'));
  	$result=(sendMail($dest,$title,$msg))?'OK':'';
  	if ($result) {
  		return $dest;
  	} else {
  		return 0;
  	}
  }
}
?>