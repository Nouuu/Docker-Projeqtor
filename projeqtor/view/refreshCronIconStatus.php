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
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refrehCronIconStatus.php'); 

$cronStatus=RequestHandler::getValue('cronStatus');
$cronStatus = ucfirst($cronStatus);
$oldStatus = ucfirst(Cron::check());
$simuIndex=Parameter::getGlobalParameter('simuIndex');
if($simuIndex){
	$simuClass = 'simuToolBar';
	$simuBarColor = 'style="background-color:#ff7777 !important;"';
}else{
	$simuClass = '';
	$simuBarColor='';
}
$actualStatus=$cronStatus;
?>
<div class="pseudoButton <?php echo $simuClass;?>" id="cronStatusButton" name="cronStatusButton"
style="height:28px; position:relative;top:-5px; z-index:30; width:32px;" title="<?php if($oldStatus != $cronStatus){echo i18n('cronStopping');}else{if($cronStatus == 'Running'){echo i18n('cronRunning');}else{echo i18n('cronStopped');}}?>"
onClick="checkCronStatus('<?php echo $cronStatus;?>');">
  <img id="cronStatus" name="cronStatus" style="<?php if($oldStatus != $cronStatus){echo 'filter:grayscale(100%);';$actualStatus='temp';}?>height:22px;width:22px;padding-top:3px;" src="img/iconCron<?php echo $cronStatus;?>.png" />
  <input type='hidden' id='actualCronStatusInDiv' value='<?php echo $actualStatus;?>' />
</div>
