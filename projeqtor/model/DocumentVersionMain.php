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
class DocumentVersionMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;
  public $name;
  public $fullName;
  public $version;
  public $revision;
  public $draft;
  public $fileName;
  public $fileSize;
  public $mimeType;
  public $versionDate;
  public $createDateTime;
  public $updateDateTime;
  public $extension;
  public $idDocument;
  public $idAuthor;
  public $idStatus;
  public $description;
  public $isRef;
  public $approved;
  public $disapproved;
  public $importFile;
  public $target;
  public $idle;
  
  // Flo #2948
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%"># ${id}</th>
    <th field="name" width="8%">${name}</th>
    <th field="version" width="6%">${version}</th>
    <th field="revision" width="6%">${revision}</th>
    <th field="fileSize" width="6%">${fileSize}</th>
    <th field="versionDate" width="10%">${versionDate}</th>
    <th field="nameAuthor" width="10%">${idAuthor}</th>
    <th field="nameDocument" width="14%" >${idDocument}</th>
    <th field="colorNameStatus" width="14%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="approved" width="6%" formatter="booleanFormatter">${approved}</th>
    <th field="idle" width="6%" formatter="booleanFormatter">${idle}</th>
   
    ';
  
  private static $_colCaptionTransposition = array('name'=>'nextDocumentVersion', 
      'fullName'=>'name');
  private static $_fieldsAttributes=array(
  		"importFile"=>"hidden,noExport,calculated",
  		"target"=>"hidden,noExport,calculated"
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
  
// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  // Flo #2948
  /** ==========================================================================
   * Return the specific layout
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
  protected function getStaticFieldsAttributes() {
  	return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $critWhere="idDocument='". Sql::fmtId($this->idDocument) . "' and name='" . $this->name . "'";
    if ($this->id) {
    	$critWhere .= " and id<>" . Sql::fmtId($this->id);
    }
    $lst=$this->getSqlElementsFromCriteria(null, false, $critWhere);
    if (count($lst)>0) {
        $result.='<br/>' . i18n('errorDuplicateDocumentVersion',array($this->name));
    }
    if (isset($this->importFile)) {
    	if (! file_exists($this->importFile)) {
        $result.='<br/>' . i18n('errorNotFoundFile'). ' : '.$this->importFile;
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  
  function save($fromDoc=false) {
    $mode="";
  	if ($this->id) {
  		$this->updateDateTime=Date('Y-m-d H:i:s');
      $mode='update';
  	} else  {
  		$this->createDateTime=Date('Y-m-d H:i:s');
      $mode='insert';
  	}
  	$doc=new Document($this->idDocument);
  	$saveDoc=false;
  	$suffix=Parameter::getGlobalParameter('versionReferenceSuffix');
  	if ($doc->documentReference) {
  	  $this->fullName=$doc->documentReference.str_replace('{VERS}',$this->name,$suffix);
  	} else {
  		$this->fullName=$doc->name;
  	}
  	if ($this->importFile) {
  	  enableSilentErrors();
  		$this->fileName=@basename($this->importFile);
  		$paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');
  		$paramFilenameCharsetForImport=Parameter::getGlobalParameter('filenameCharsetForImport');
  		if ($paramFilenameCharset) {
  		  $this->importFile=iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$this->importFile);
  		} else if ($paramFilenameCharsetForImport) {
  		  $this->importFile=iconv("UTF-8", $paramFilenameCharsetForImport.'//TRANSLIT//IGNORE',$this->importFile);
  		}
  		$this->fileSize=@filesize($this->importFile);
  	}
  	$pos=strrpos($this->fileName,'.');
  	if ($pos) {
  	  $this->fullName.=substr($this->fileName,$pos);
  	}
  	$this->fullName=substr($this->fullName,0,$this->getDataLength('fullName'));
  	
  	$result=parent::save();
  	
  	$resultStatus=getLastOperationStatus($result);
  	
  	if ($resultStatus!='ERROR' and $resultStatus!='INVALID' and isset($this->importFile)) {
  		$resultFileImport=$this->storeImportFile();
  		if ($resultFileImport!='OK') {
  			return $resultFileImport;
  		}
  	}
  	if ($resultStatus!="OK") {
      return $result;     
    }
    if ( ($doc->version==null) 
    or ( $this->version>$doc->version ) 
    or ( $this->version==$doc->version and $this->revision>$doc->revision) 
    or ( $this->version==$doc->version and $this->revision==$doc->revision and ($this->draft>$doc->draft or (!$this->draft and $doc->draft))) ) {
      $doc->version=$this->version;
      $doc->revision=$this->revision;
      $doc->draft=$this->draft;
      $doc->idDocumentVersion=$this->id;
      $saveDoc=true;
    }
    if ($this->isRef) {
      $doc->idDocumentVersionRef=$this->id;
      $saveDoc=true;
      $critWhere="idDocument='" . Sql::fmtId($this->idDocument) . "' and isRef='1' and id<>" . Sql::fmtId($this->id);
      $list=$this->getSqlElementsFromCriteria(null, false, $critWhere);
      foreach ($list as $elt) {
      	$elt->isRef='0';
      	$elt->save();
      } 
    }
    if ($doc->idDocumentVersion==$this->id) {
    	$doc->idStatus=$this->idStatus;
    	$st=new Status($this->idStatus);
    	$doc->idle=$st->setIdleStatus;
      $saveDoc=true;
    }
    if ($saveDoc and !$fromDoc) {
      $resDoc=$doc->save();
   }
    
    // Inset approvers from document if not existing (on creation)
    if ($mode=='insert') {
      $approver=new Approver();
      $crit=array('refType'=>'Document','refId'=>$this->idDocument);
      $lstDocApp=$approver->getSqlElementsFromCriteria($crit);
      foreach ($lstDocApp as $app) {
        $newApp=new Approver();
        $newApp->refType='DocumentVersion';
        $newApp->refId=$this->id;
        $newApp->idAffectable=$app->idAffectable;
        $newApp->save();
      }
    }
  	return $result;
  }
  
  function delete() {
    $result=parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $saveDoc=false;
  	$recalcDoc=false;
  	$crit=array('idDocument'=>$this->idDocument);
  	$doc=new Document($this->idDocument);
    if ($doc->idDocumentVersion==$this->id) {
      $doc->version=null;
      $doc->revision=null;  
      $doc->draft=null;
      $doc->idDocumentVersion=null;
      if ($this->isRef) {
      	$doc->idDocumentVersionRef=null;
      }
      $saveDoc=true;
      //$doc->save();
    }
  	$list=$this->getSqlElementsFromCriteria($crit, false, null, 'id desc',false);
  	if (count($list)>0) {
  		$dv=$list[0];
  		//$dv->save();
  		$doc->version=$dv->version;
      $doc->revision=$dv->revision;  
      $doc->draft=$dv->draft;
      $doc->idDocumentVersion=$dv->id;
      $doc->idStatus=$dv->idStatus;
      $st=new Status($dv->idStatus);
      $doc->idle=$st->setIdleStatus;
      $saveDoc=true;
  	}
  	if ($saveDoc==true) {
      $doc->save();
  	}
  	return $result;
  }
  
  function getUploadFileName() {
  	$paramPathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$doc=New Document($this->idDocument);
    $dir=New DocumentDirectory($doc->idDocumentDirectory);
    $uploaddir = $dir->getLocation();
    if (! file_exists($uploaddir)) {
    	$dir->createDirectory();
    }
    $fileName=$this->fileName;
    /* $ext = strtolower ( pathinfo ( $fileName, PATHINFO_EXTENSION ) );
    if (substr($ext,0,3)=='php' or substr($ext,0,4)=='phtm') {
    	$fileName.=".projeqtor";
    }*/ // Not usefull : id of Document Version is added after file extension 
    $paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');
    if ($paramFilenameCharset) {
    	$fileName=iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$fileName);
    }
    return $uploaddir . $paramPathSeparator . $fileName . '.' . $this->id;
  }

  function checkApproved() {
    $crit=array('refType'=>'DocumentVersion','refId'=>$this->id);
    $app=new Approver();
    $list=$app->getSqlElementsFromCriteria($crit);
    if (count($list)==0) {
      $approved=0;
      $disapproved=0;
    } else {
	    $approved=1;
	    $disapproved=0;
    	foreach ($list as $app) {
	      if (! $app->approved and !$disapproved) {
	        $approved=0;
	      }
	      if ($app->disapproved) {
	      	$disapproved=1;
	      }
	    }
    }
    $this->approved=$approved;
    $this->disapproved=$disapproved;
    $this->save();
  }
  
  public function isThumbable() {
    return isThumbable($this->fileName);
  }
  
  private function storeImportFile() {
  	$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
    //$this->importFile
    //$this->fileName=basename($this->importFile); already done, before conversion of importFile to avoid double conversion
    $uploadfile = $this->getUploadFileName();
    $split=explode($pathSeparator,$uploadfile);
    unset($split[count($split)-1]);
    $dir='';
    foreach ($split as $dirElt) {
      $dir.=$dirElt.$pathSeparator;
      if (! file_exists($dir)) {
        mkdir($dir,0777,true);
      }
    }
    enableCatchErrors();
    if ( ! copy($this->importFile,$uploadfile) ) {
      $error=htmlGetErrorMessage(i18n('errorUploadFile',array('hacking')));
      errorLog(i18n('errorUploadFile',array('hacking')));
      return $error;
    }
    if ($this->target=='DELETE') {
    	@kill($this->importFile);
    } else if ($this->target) {
    	if (file_exists($this->target) and is_dir($this->target)) {
    		@rename($this->importFile,$this->target.$pathSeparator.basename($this->importFile));
    	} else {
    		traceLog(i18n('errorUploadFile',array(' could not move import file to '.$this->target)));
    	}
    }
    disableCatchErrors();
    return 'OK';
  }
}
?>