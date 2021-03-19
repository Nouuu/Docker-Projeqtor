<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 *
 ******************************************************************************
 *** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
 ******************************************************************************
 * 
 * This file is an add-on to ProjeQtOr, packaged as a plug-in module.
 * It is NOT distributed under an open source license. 
 * It is distributed in a proprietary mode, only to the customer who bought
 * corresponding licence. 
 * The company ProjeQtOr remains owner of all add-ons it delivers.
 * Any change to an add-ons without the explicit agreement of the company 
 * ProjeQtOr is prohibited.
 * The diffusion (or any kind if distribution) of an add-on is prohibited.
 * Violators will be prosecuted.
 *    
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/** ============================================================================
 * Activity is main planned element
 */  
require_once('_securityCheck.php');
class Tag extends SqlElement {

  public $id;
  public $name;
  public $refType;
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

  public static function saveTagList($newTags, $oldTags, $refType) {
    $newTagList=explode('#',$newTags);
    $oldTagList=explode('#',$oldTags);
    foreach ($newTagList as $tag) {
      if (! trim($tag)) continue;
      $newTag=SqlElement::getSingleSqlElementFromCriteria('Tag', array('name'=>$tag));
      if (!$newTag->id) {
        $newTag->name=$tag;
        $newTag->idle=0;
        $newTag->refType=$refType;
        $newTag->save();
      }
    }
    if ($refType) {
      $doc=new $refType();
      foreach ($oldTagList as $tag) {
        if (! trim($tag)) continue;
        if (! in_array($tag, $newTagList)) { // Tag is deleted
          $cpt=$doc->countSqlElementsFromCriteria(null,"tags like '%#$tag#%'");
          if ($cpt==0) { // no more item uses this tag
            $oldTag=SqlElement::getSingleSqlElementFromCriteria('Tag', array('name'=>$tag));
            if ($oldTag->id) {
              $oldTag->delete();
            }
          }
        }
      }
    }
  }
}
?>