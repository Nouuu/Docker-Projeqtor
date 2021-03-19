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
 * ============================================================================ User is a resource that can connect to the application.
 */
require_once ('_securityCheck.php');
class Affectable extends SqlElement {
  
  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id; // redefine $id to specify its visible place
  public $name;
  public $userName;
  public $capacity=1;
  public $idCalendarDefinition;
  public $idProfile;
  public $isResource;
  public $isUser;
  public $isContact;
  public $isResourceTeam;
  public $email;
  public $idTeam;
  public $idOrganization;
  public $idle;
  public $dontReceiveTeamMails;
  public $_sec_Asset;
  public $_spe_asset;
  public $_constructForName=true;
  public $_calculateForColumn=array("name" => "coalesce(fullName,concat(name,' #'))","userName" => "coalesce(name,concat(fullName,' *'))");
  private static $_fieldsAttributes=array("name" => "required","isContact" => "readonly","isUser" => "readonly","isResource" => "readonly","isResourceTeam"=>"readonly","idle" => "hidden");
  private static $_databaseTableName='resource';
  private static $_databaseColumnName=array('name' => 'fullName','userName' => 'name');
  private static $_databaseCriteria=array();
  
  private static $_visibilityScope=array();
// ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY  
  private static $_organizationVisibilityScope=array();
// END ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY  

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="25%">${realName}</th>
    <th field="userName" width="20%">${userName}</th>
    <th field="photo" formatter="thumb32" width="10%">${photo}</th>
    <th field="email" width="25%">${email}</th>  
    <th field="isUser" width="5%" formatter="booleanFormatter">${isUser}</th>
    <th field="isResource" width="5%" formatter="booleanFormatter">${isResource}</th>
    <th field="isContact" width="5%" formatter="booleanFormatter">${isContact}</th>
    ';
  
  
  /**
   * ==========================================================================
   * Constructor
   *
   * @param $id the
   *          id of the object in the database (null if not stored yet)
   * @return void
   */
  function __construct($id=NULL,$withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->id and !$this->name and $this->userName) {
      $this->name=$this->userName;
    }
  }

  /**
   * ==========================================================================
   * Destructor
   *
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
  
  /**
   * ========================================================================
   * Return the specific databaseTableName
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }

  /**
   * ========================================================================
   * Return the specific databaseTableName
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }

  /**
   * ========================================================================
   * Return the specific database criteria
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
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

  // ============================================================================**********
  // THUMBS & IMAGES
  // ============================================================================**********
  
  /**
   * 
   * @param unknown $classAffectable
   * @param unknown $idAffectable
   * @param string $fileFullName
   */
  public static function generateThumbs($classAffectable, $idAffectable, $fileFullName=null) {
    $sizes=array(16,22,32,48,80); // sizes to generate, may be used somewhere
    $thumbLocation='../files/thumbs';
    $attLoc=Parameter::getGlobalParameter('paramAttachmentDirectory');
    if (!$fileFullName) {
      $image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType' => 'Resource','refId' => $idAffectable));
      if ($image->id) {
        $fileFullName=$image->subDirectory . $image->fileName;
      }
    }
    $fileFullName=str_replace('${attachmentDirectory}', $attLoc, $fileFullName);
    $fileFullName=str_replace('\\', '/', $fileFullName);
    if ($fileFullName and isThumbable($fileFullName)) {
      foreach ( $sizes as $size ) {
        $thumbFile=$thumbLocation . "/Affectable_$idAffectable/thumb$size.png";
        createThumb($fileFullName, $size, $thumbFile, true);
      }
    }
  }

  public static function generateAllThumbs() {
    $affList=SqlList::getList('Affectable', 'name', null, true);
    foreach ( $affList as $id => $name ) {
      self::generateThumbs('Affectable', $id, null);
    } 
  }

  public static function deleteThumbs($classAffectable, $idAffectable, $fileFullName=null) {
    $thumbLocation='../files/thumbs/Affectable_' . $idAffectable;
    purgeFiles($thumbLocation, null);
  }

  public static function getThumbUrl($objectClass, $affId, $size, $nullIfEmpty=false, $withoutUrlExtra=false) {
    $thumbLocation='../files/thumbs';
    $file="$thumbLocation/Affectable_$affId/thumb$size.png";
    if (file_exists($file)) {
      if ($withoutUrlExtra) {
        return $file;
      } else {
        $cache=filemtime($file);
        return "$file?nocache=".$cache."#$affId#&nbsp;#Affectable";
      }
    } else {
      if ($nullIfEmpty) {
        return null;
      } else {
      	return 'letter#'.$affId;
        //if ($withoutUrlExtra) {
        //  return "../view/img/Affectable/thumb$size.png";
        //} else {
        //  return "../view/img/Affectable/thumb$size.png#0#&nbsp;#Affectable";
        //}
      }
    }
  }
  
  public static function showBigImageEmpty($extraStylePosition, $canAdd=true) {
    $result=null;
    if(isNewGui()){
      $result .= '<div style="position: absolute;'.$extraStylePosition.';width:60px;height:60px;border-radius:40px; border: 1px solid grey;color: grey;font-size:80%; text-align:center;cursor: pointer;"';
      if ($canAdd) {
        $result .= 'onClick="addAttachment(\'file\');" title="'.i18n('addPhoto').'">';
        $result .= '<div style="left: 19px;position:relative;top: 20px;height:22px;width: 22px;" class="iconAdd iconSize22 imageColorNewGui">&nbsp;</div>';
      }else{
        $result.= '>';
      }
      $result.= '</div>';
    }else{
      $result='<div style="position: absolute;'.$extraStylePosition.';'
      		.'border-radius:40px;width:80px;height:80px;border: 1px solid grey;color: grey;font-size:80%;'
      		.'text-align:center;';
      if ($canAdd) {
      	$result.='cursor: pointer;"  onClick="addAttachment(\'file\');" title="'.i18n('addPhoto').'">';
      	$result.='<br/><br/><br/>'.i18n('addPhoto').'</div>';
      } else {
      	$result.='" ></div>';
      }
    }
    return $result;
  }
  public static function showBigImage($extraStylePosition,$affId,$filename, $attachmentId) {
    $result='<div style="position: absolute;'.$extraStylePosition.'; border-radius:40px;width:80px;height:80px;border: 1px solid grey;">'
      . '<img style="border-radius:40px;" src="'. Affectable::getThumbUrl('Resource', $affId, 80).'" '
      . ' title="'.$filename.'" style="cursor:pointer"'
      . ' onClick="showImage(\'Attachment\',\''.$attachmentId.'\',\''.htmlEncode($filename,'protectQuotes').'\');" /></div>';
    return $result;
  }
  public static function drawSpecificImage($class,$id, $print, $outMode, $largeWidth) {
    $result="";
    $image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>'Resource', 'refId'=>$id));
    if ($image->id and $image->isThumbable()) {
      if (!$print) {
        //$result.='<tr style="height:20px;">';
        //$result.='<td class="label">'.i18n('colPhoto').'&nbsp;:&nbsp;</td>';
        //$result.='<td>&nbsp;&nbsp;';
        $result.='<span class="label" style="position: absolute;top:28px;right:105px;">';
        $result.=i18n('colPhoto').'&nbsp;:&nbsp;';
        $canUpdate=securityGetAccessRightYesNo('menu'.$class, 'update') == "YES";
        if ($id==getSessionUser()->id) $canUpdate=true;
        if ($canUpdate) {
          //$result.='<img src="css/images/smallButtonRemove.png" class="roundedButtonSmall" style="height:12px" '
          //    .'onClick="removeAttachment('.htmlEncode($image->id).');" title="'.i18n('removePhoto').'" class="smallButton"/>';
          $result.= '<span onClick="removeAttachment('.htmlEncode($image->id).');" title="'.i18n('removePhoto').'" >';
          $result.= formatSmallButton('Remove');
          $result.= '</span>';
        }
        
        $horizontal='right:10px';
        $top='30px';
        $result.='</span>';
      } else {
        if ($outMode=='pdf') {
          $horizontal='left:450px';
          $top='100px';
        } else {
          $horizontal='left:400px';
          $top='70px';
        }
      }
      $extraStyle='top:30px;'.$horizontal;
      $result.=Affectable::showBigImage($extraStyle,$id,$image->fileName,$image->id);
      if (!$print) {
        //$result.='</td></tr>';
      }
    } else {
      if ($image->id) {
        $image->delete();
      }
      if (!$print) {
        $horizontal='right:10px';
        //$result.='<tr style="height:20px;">';
        //$result.='<td class="label">'.i18n('colPhoto').'&nbsp;:&nbsp;</td>';
        //$result.='<td>&nbsp;&nbsp;';
        $result.='<span class="label" style="position: absolute;top:28px;right:105px;">';
        $result.=i18n('colPhoto').'&nbsp;:&nbsp;';
        $canUpdate=securityGetAccessRightYesNo('menu'.$class, 'update') == "YES";
        if ($id==getSessionUser()->id) $canUpdate=true;
        if ($canUpdate and !isNewGui()) {
          //KEVIN
         $result.= '<span onClick="addAttachment(\'file\');"title="' . i18n('addPhoto') .'" >';
         $result.= formatSmallButton('Add');
         $result.= '</span>';
        }
        $result.='</span>';
        $extraStyle='top:30px;'.$horizontal;
        $result.=Affectable::showBigImageEmpty($extraStyle,$canUpdate);
      }
    }
    return $result;
  }
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are :
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    global $print, $outMode, $largeWidth;
    $result="";
    if($item=='asset') {
      $asset = new Asset();
      $critArray=array('idAffectable'=>(($this->id)?$this->id:'0'));
      $order = " idAssetType asc ";
      $assetList=$asset->getSqlElementsFromCriteria($critArray, false,null);
      drawAssetFromUser($assetList, $this);
    }
    return $result;
  }
  
  public static function isAffectable($objectClass=null) {
    if ($objectClass) {
      if ($objectClass=='Resource' or $objectClass=='ResourceTeam' or $objectClass=='User' or $objectClass=='Contact' 
       or $objectClass=='Affectable' or $objectClass=='ResourceSelect' or $objectClass=='Accountable' or $objectClass=='Responsible') {
        return true;
      }
    }
    return false;
  }
  
// ADD BY Marc TABARY - 2017-02-20 ORGANIZATION VISIBILITY  
  public static function  getOrganizationVisibilityScope($scope='List') {
    if (isset(self::$_organizationVisibilityScope[$scope])) return self::$_organizationVisibilityScope[$scope];
    $orga='all';
    $crit=array('idProfile'=>getSessionUser()->idProfile, 'scope'=>'orgaVisibility'.$scope);
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    if ($habil and $habil->id) {
      $orga=SqlList::getFieldFromId('ListOrgaSubOrga', $habil->rightAccess,'code',false);
    }
    self::$_organizationVisibilityScope[$scope]=$orga;
    return $orga;
  }
// END ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY
  public static function  getVisibilityScope($scope='List',$idProject=null) {
    $profile=getSessionUser()->getProfile($idProject);
    if (isset(self::$_visibilityScope[$scope][$profile])) return self::$_visibilityScope[$scope][$profile];
    $res='all';
    $crit=array('idProfile'=>$profile, 'scope'=>'resVisibility'.$scope);
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    if ($habil and $habil->id) {
      $res=SqlList::getFieldFromId('ListTeamOrga', $habil->rightAccess,'code',false);
    }
    self::$_visibilityScope[$scope][$profile]=$res;
    return $res;
  }
  
  public static function sort($aff1, $aff2) {
    $name1=($aff1->name)?$aff1->name:$aff1->userName;
    $name2=($aff2->name)?$aff2->name:$aff2->userName;
    if ($name1<$name2) {
      return -1;
    } else if ($name1>$name2) {
      return 1;
    } else {
      return 0;
    }
  }
}
?>