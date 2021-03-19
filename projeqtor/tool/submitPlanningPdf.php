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

/** ===========================================================================
 * Copy an object as a new one (of the same class) : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";
projeqtor_set_time_limit(300);
// Get parameters
if (! array_key_exists('printOrientation',$_REQUEST)) {
  throwError('printOrientation parameter not found in REQUEST');
}
$printOrientation=$_REQUEST['printOrientation'];

if (! array_key_exists('printZoom',$_REQUEST)) {
  throwError('printZoom parameter not found in REQUEST');
}
$printZoom=$_REQUEST['printZoom'];

$printRepeat="norepeat";
if (array_key_exists('printRepeat',$_REQUEST)) {
  $printRepeat="repeat";
}

$printFormat=RequestHandler::getValue('printFormat');
if (! $printFormat) $printFormat="A4";

Parameter::storeUserParameter("printOrientation", $printOrientation);
Parameter::storeUserParameter("printZoom", $printZoom);
Parameter::storeUserParameter("printRepeat", $printRepeat);
Parameter::storeUserParameter("printFormat", $printFormat);
?>