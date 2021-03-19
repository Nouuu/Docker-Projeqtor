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
 * This abstract class is design to handle and control $_REQUEST values
 */  
require_once('_securityCheck.php');
abstract class RequestHandler {

  public static function getValue($code,$required=false,$default=null) {
    if (isset($_REQUEST[$code])) {
      return $_REQUEST[$code];
    } else {
      if ($required) {
        throwError("parameter '$code' not found in Request");
        exit;
      } else {
        return $default;
      }  
    }
  }
  public static function isCodeSet($code) {
    return isset($_REQUEST[$code]);
  }
  
  public static function setValue($code,$value) {
    
    $_REQUEST[$code]=$value;
  }
  
  public static function unsetCode($code) {
    if (isset($_REQUEST[$code])) {
      unset($_REQUEST[$code]);
    }
  }
  
  public static function getClass($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if ($val==$default) return $val;
    if (strtolower($val)=='null' or strtolower($val)=='undefined') return null; 
    return Security::checkValidClass($val);
  }
  
  public static function getId($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if ($val==$default) return $val;
    if (! is_array($val) and (strtolower($val)=='null' or strtolower($val)=='undefined')) return null;
    return Security::checkValidId($val);
  }
  
  public static function getNumeric($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if (!$val and $default!==null) return $default;
    if ($val==$default) return $val;
    return Security::checkValidNumeric($val);
  }
  
  public static function getAlphanumeric($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if ($val==$default) return $val;
    return Security::checkValidAlphanumeric($val);
  }
  
  public static function getDatetime($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if ($val==$default) return $val;
    return Security::checkValidDateTime($val);
  }
  
  public static function getYear($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if ($val==$default) return $val;
    return Security::checkValidYear($val);
  }
  
  public static function getMonth($code,$required=false,$default=null) {
    $val=self::getValue($code,$required,$default);
    if ($val==$default) return $val;
    return Security::checkValidMonth($val);
  }
  public static function getExpected($code,$required=false,$expectedList=array()) {
    $val=self::getValue($code,$required,null);
    if ($val==null and !$required) return null;
    if (in_array($val, $expectedList)) {
      return $val;
    } else {
      throwError("parameter $code='$val' has an unexpected value");
      exit;
    }
  }
  public static function getBoolean($code) {
    $val=self::getValue($code,false,null);
    if (!$val or $val=='off' or $val=='false') return false;
    else return true;
  }
  // debug log to keep
  public static function dump() {
    debugTraceLog('===== Dump of $_REQUEST =============================================================');
    foreach ($_REQUEST as $code=>$val) {
      if (is_array($val)) {
        $cpt=count($val);
        debugTraceLog(" => $code is an array of $cpt elements");
        debugTraceLog($val);
      } else {
        debugTraceLog(" => $code='$val'");
      }
    }
    debugTraceLog('===== End of Dump ===================================================================');
  }
  
}
?>