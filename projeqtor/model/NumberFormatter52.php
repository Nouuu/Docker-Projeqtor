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
 * Class to replace 5.3 NumberFormatter class in 5.2 version.
 */ 
require_once('_securityCheck.php');
class NumberFormatter52  {

   public $locale;
   public $type;
   public $decimalSeparator;  
   public $thouthandSeparator;
   const DECIMAL=2;
   const INTEGER=0;
   
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($locale, $type) {
    $this->locale=$locale;
    $this->type=$type;
    if (false !== setlocale(LC_ALL, $locale . ".UTF-8@euro", $locale . ".UTF-8", $locale) ) {
      $locale_info = localeconv();
      $this->decimalSeparator=$locale_info['decimal_point'];
      $this->thouthandSeparator=$locale_info['thousands_sep'];
    } else {
      $this->thouthandSeparator=''; // Can get better ?
      if (strtolower(substr($locale,0,2))=='fr' or strtolower(substr($locale,0,2))=='de') {
        $this->decimalSeparator=',';
        $this->thouthandSeparator=' ';
      } else {
        $this->decimalSeparator='.';
        $this->thouthandSeparator=',';
      }
    }
    setlocale(LC_NUMERIC, 'C');
    if (sessionValueExists('browserLocaleDecimalPoint')) {
      $this->decimalSeparator=getSessionValue('browserLocaleDecimalPoint');
    }
    if (sessionValueExists('browserLocaleThousandSeparator')) {
      $this->thouthandSeparator=getSessionValue('browserLocaleThousandSeparator');
    }
    if (ord($this->thouthandSeparator)>127) {
      $this->thouthandSeparator=" ";
    }
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
  }

  /** ==========================================================================
   * Format fonction (simulate)
   */ 
  function format($value) {
  	if (! is_numeric($value)) {
  		return $value;
  	}
    return number_format($value,$this->type,$this->decimalSeparator,$this->thouthandSeparator);
  }

  function formatDecimalPoint($value) {
  	if (! is_numeric($value)) {
  		return $value;
  	}
  	return number_format($value,$this->type,$this->decimalSeparator,'');
  }
  
  static function getKeyDownEvent() {
    global $browserLocale;
    $fmt=new NumberFormatter52($browserLocale, NumberFormatter52::DECIMAL);
    if ($fmt->decimalSeparator=='.') return '';
    $result='<script type="dojo/method" event="onKeyDown" args="event">';
    $result.=' if (event.keyCode==110) {return intercepPointKey(this,event);}';
    $result.='</script>';
    return $result;
  }
  static function completeKeyDownEvent($colScript) {
    global $browserLocale;
    $fmt=new NumberFormatter52($browserLocale, NumberFormatter52::DECIMAL);
    if ($fmt->decimalSeparator=='.') return $colScript;    
    $tagEvent='<script type="dojo/method" event="onKeyDown" args="event">';
    if (substr_count($colScript,$tagEvent)==0) return $colScript.self::getKeyDownEvent();
    $evt=' if (event.keyCode==110) {formChanged();return intercepPointKey(this,event);}';
    $result=str_replace($tagEvent,$tagEvent.$evt,$colScript);
    return $result;
  }
}
?>