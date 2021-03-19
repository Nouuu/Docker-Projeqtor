<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Julien PAPASIAN
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

include_once '../tool/formatter.php';

if (!isset($print) or ! $print) {
    $print = false;
    $printWidthDialog = "100%";
    $internalWidth = '100%';
    $nameWidth = "15%";
} else {
    $printWidthDialog = $printWidth . 'px';
    $nameWidthValue = 120;
    $nameWidth = $nameWidthValue . 'px;';
    $internalWidth = ($printWidth - $nameWidthValue + 8) . 'px';
}
$context = "popup";
if (isset($obj)) {
    $objectClass = get_class($obj);
    $objectId = $obj->id;
    $context = "detail";
} else {
    if (!array_key_exists('objectClass', $_REQUEST)) {
        throwError('Parameter objectClass not found in REQUEST');
    }
    $objectClass = $_REQUEST['objectClass'];

    if (!array_key_exists('objectId', $_REQUEST)) {
        throwError('Parameter objectId not found in REQUEST');
    }
    $objectId = $_REQUEST['objectId'];
}

$joblistDefinition = null;
$obj = new $objectClass($objectId);
$type = 'id' . $objectClass . 'Type';
$job = new Job();
/*$jobList = $job->getSqlElementsFromCriteria(array('refType' => $objectClass, 'refId' => $objectId));
if (count($jobList) > 0) {
    $job = array_shift($jobList);
    $joblistDefinition = new JoblistDefinition($job->idJoblistDefinition);
    if ($joblistDefinition->id and ( ( $joblistDefinition->nameChecklistable != $objectClass)
            or ( $joblistDefinition->idType and $joblistDefinition->idType != $obj->$type)
            )) {
        $job->delete();
        unset($job);
    }
    // Clear dupplicate
    if (count($jobList) > 0) {
        foreach ($jobList as $del) {
            $del->delete();
        }
    }
}
if (!isset($job) or ! $job or ! $job->id) {
    $job = new Job();
}*/

if (!$joblistDefinition or ! $joblistDefinition->id) {
    if (property_exists($obj, $type)) {
        $crit = array('nameChecklistable' => $objectClass, 'idType' => $obj->$type, 'idle'=>'0');
        $joblistDefinition = SqlElement::getSingleSqlElementFromCriteria('JoblistDefinition', $crit);
    }
    if (!$joblistDefinition or ! $joblistDefinition->id) {
    	$crit="nameChecklistable='$objectClass' and idle=0";
    	if (property_exists($obj,$type)) {$crit.=" and idType is null ";}
    	$jd=new $joblistDefinition();
    	$jdList=$jd->getSqlElementsFromCriteria(null,false,$crit);
    	$joblistDefinition=reset($jdList);
    }
}
if (!$joblistDefinition or ! $joblistDefinition->id) {
    echo '<div class="ERROR" >' . i18n('noJoblistDefined') . '</div>';
    exit;
}
$cdl = new JobDefinition();
$defLines = $cdl->getSqlElementsFromCriteria(array('idJoblistDefinition' => $joblistDefinition->id), false, null, 'sortOrder asc');
//usort($defLines,"ChecklistDefinitionLine::sort");
$cl = new Job();
$linesTmp = $cl->getSqlElementsFromCriteria(array('refType' => $objectClass, 'refId' => $objectId));

$linesVal = array();
foreach ($linesTmp as $line) {
    $linesVal[$line->idJobDefinition] = $line;
}

$canUpdate = (securityGetAccessRightYesNo('menu' . $objectClass, 'update', $obj) == 'YES');
if ($obj->idle)
    $canUpdate = false;
if ($print)
    $canUpdate = false;

$dToday = new DateTime();
$status = array('done' => '#a5eda5',
    'warning' => '#edb584',
    'alert' => '#eda5a5',
    'blank' => '#FFFFFF');
?>
<?php if (!$print) { ?>
  <div id="dialogJobInfo" dojoType="dijit.Dialog" title="<?php echo i18n("dialogJobInfo");?>">
  <input dojoType="dijit.form.TextBox" type="hidden" id="dialogJobInfoJobId" name="dialogJobInfoJobId" />
  <table>
    <tr>
      <td>
        <table >
          <tr id="dialogJobInfoCreatorLine">
            <td class="dialogLabel"  >
              <label for="dialogJobInfoCreator" ><?php echo i18n("colResponsible") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <select dojoType="dijit.form.FilteringSelect" id="dialogJobInfoCreator" class="input" value="" <?php echo autoOpenFilteringSelect ();?>>
                <?php htmlDrawOptionForReference('idUser', null, null, true);?>
              </select>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr id="dialogJobInfoDateLine">
            <td class="dialogLabel" >
              <label for="dialogJobInfoDate" ><?php echo i18n("colValidatedEndDate") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <div id="dialogJobInfoDate" dojoType="dijit.form.DateTextBox"
                 constraints="{datePattern:browserLocaleDateFormatJs}"
                 invalidMessage="<?php echo i18n('messageInvalidDate');?> "
                 type="text" maxlength="10"
                 style="width:100px; text-align: center;" class="input"
                 hasDownArrow="true"
                 >
              </div>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogJobInfo').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogJobInfoSubmit" onclick="protectDblClick(this);saveJobInfo();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
    <?php if ($context == 'popup') { ?>
        <form id="dialogJoblistForm" name="dialogJoblistForm" action="">
        <?php } ?>
        <input type="hidden" name="joblistDefinitionId" value="<?php echo $joblistDefinition->id; ?>" />
        <!--<input type="hidden" name="jobId" value="<?php echo $job->id; ?>" />-->
        <input type="hidden" name="joblistObjectClass" value="<?php echo $objectClass; ?>" />
        <input type="hidden" name="joblistObjectId" value="<?php echo $objectId; ?>" />
    <?php } else { ?>
        <table style="width:<?php echo $printWidthDialog; ?>;">
            <tr><td>&nbsp;</td></tr>
            <tr><td class="section"><?php echo i18n("sectionJoblist"); ?></td></tr>
            <tr style="height:0.5em;font-size:80%"><td>&nbsp;</td></tr>
        </table>
    <?php } ?>
    <table style="width:<?php echo $printWidthDialog; ?>;">
        <tr>
            <td class="notedata" style="width:600px">
                <table style="width:<?php echo $printWidthDialog; ?>; border: 1px solid #AAA" >
                    <?php
                    foreach ($defLines as $line) {
                        if (isset($linesVal[$line->id])) {
                            $lineVal = $linesVal[$line->id];
                        } else {
                            $lineVal = new Job();
                        }
                        if(is_null($lineVal->idUser)) {
                            $idUser = getSessionUser();
                        }
                        ?>
                        <tr id="job_<?php echo $line->id ?>">
														<td style="width:3px;">&nbsp;</td>
                            <td style="width:25px;">
                              <?php
                              $creationDate=$lineVal->creationDate;
                              if (!$creationDate) { // Will determine expected target date from other data
                              	$pe=$objectClass.'PlanningElement';
                              	if (property_exists($obj, $pe) and property_exists($obj->$pe, 'plannedEndDate')) {
                              		$creationDate=$obj->$pe->plannedEndDate;
                              	} else if (property_exists($obj, 'actualDueDate')){
                              		$creationDate=$obj->actualDueDate;
                              	} else if (property_exists($obj, 'actualDueDateTime')){
                              		$creationDate=substr($obj->actualDueDateTime,0,10);
                              	}
                              }
                              if($lineVal->value) {
                                  $color = $status['done'];
                              } elseif(!is_null($creationDate) && $creationDate < $dToday->format('Y-m-d')) {
                                  $color = $status['alert'];
                              } elseif(!is_null($creationDate) && $line->daysBeforeWarning > 0) {
                                  $warningDate = new DateTime($creationDate);
                                  $warningDate->modify('-'.$line->daysBeforeWarning.' days');
                                  if($warningDate->format('Y-m-d') < $dToday->format('Y-m-d')) {
                                      $color = $status['warning'];
                                  } else {
                                      $color = $status['blank'];
                                  }
                              } else {
                                  $color = $status['blank'];
                              }
                              echo '<div style="border: 1px solid #AAAAAA;background:'.$color.';';
                              echo 'width: 18px; height: 18px;border-radius:9px; text-align: center">&nbsp;</div>';
                              ?>
                            </td>
                            <td style="width:<?php echo ($print) ? '500px' : '300px;min-width:300px'; ?>;" title="<?php echo ($print) ? '' : $line->title; ?>" >
                                <?php
                                    $checkName = "job_" . $line->id."_check";
                                    if ($print) {
                                        $checkImg = "checkedKO.png";
                                        if ($lineVal->value) {
                                            $checkImg = 'checkedOK.png';
                                        }
                                        echo '<img src="img/' . $checkImg . '" />&nbsp;' . htmlEncode($line->name) . '&nbsp;&nbsp;';
                                    } else {
                                        ?>
                                        <div dojoType="dijit.form.CheckBox" type="checkbox"
                                             name="<?php echo $checkName; ?>" id="<?php echo $checkName; ?>"
                                             <?php if (!$canUpdate) echo 'readonly'; ?>
                                             <?php
                                             if ($lineVal->value) {
                                                 echo 'checked';
                                             }
                                             ?> ></div>
                                        <span style="cursor:pointer;" onClick="dojo.byId('<?php echo $checkName; ?>').click();"><?php echo htmlEncode($line->name); ?>&nbsp;&nbsp;</span>
                                        <?php
                                    }
                                ?>
                            </td>
                            <?php if (!$print) { ?>
                                <td >&nbsp;</td>
                            <?php } ?>
                            <td style="text-align:right; width:<?php echo ($print) ? '15px' : '100px;min-width:100px'; ?>; color: #A0A0A0;white-space:nowrap">
                                <?php
                                //if ($lineVal->checkTime and ! $print) {
                                  if (!$print) {
                                    echo '<div dojoType="dijit.form.TextBox" type="hidden" id="job_'.$line->id .'_idUser" name="job_'.$line->id .'_idUser" value="' . htmlEncode($lineVal->idUser) . '"></div>';
                                    echo '<div dojoType="dijit.form.TextBox" type="hidden" id="job_'.$line->id .'_creationDate" name="job_'.$line->id .'_creationDate" value="' . htmlEncode(substr($lineVal->creationDate,0,10)) . '"></div>';
                                  }
																	?>
																		<div style="padding-right:15px;" class="buttonDivCreationInfoEdit" onClick="changeJobInfo(<?php echo $line->id ?>)">
																		<?php
															      echo formatUserThumb($lineVal->idUser, SqlList::getNameFromId('User', $lineVal->idUser), 'Creator');
																		echo formatDateThumb(substr($lineVal->creationDate, 0, 10), null);
                                    //echo formatDateThumb($lineVal->checkTime, null);
																		?>
																		</div>
																		<?php
                                //}
                                ?></td>
                            <td style="width:3px;">&nbsp;</td>
                            <td style="vertical-align:top;width:<?php echo ($print) ? '115px;font-size:90%;' : '150px;'; ?>">
                                <?php if (!$print) { ?>
                                    <textarea dojoType="dijit.form.Textarea"
                                              id="jobComment_<?php echo $line->id; ?>" name="job_<?php echo $line->id; ?>_comment"
                                              style="width: 150px;min-height: 25px; font-size: 90%"
                                              maxlength="4000"
                                              class="input"><?php echo $lineVal->comment; ?></textarea>
                                              <?php
                                          } else {
                                              echo htmlEncode($lineVal->comment);
                                          }
                                          ?>
                            </td>
                        </tr>
                    <?php } // end foreach($defLine ?>
                </table>
            </td></tr>
        <tr><td style="width:<?php echo $printWidthDialog; ?>;">&nbsp;</td></tr>
        <?php if (!$print and $context == 'popup') { ?>
            <tr>
                <td style="width: 100%;" align="center">
                    <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogJoblist').hide();">
                        <?php echo i18n("buttonCancel"); ?>
                    </button>
                    <button id="dialogJoblistSubmit" dojoType="dijit.form.Button" type="submit"
                            onclick="protectDblClick(this);saveJoblist();return false;" >
                                <?php echo i18n("buttonOK"); ?>
                    </button>
                </td>
            </tr>
        <?php } ?>
    </table>
    <?php if (!$print and $context == 'popup') { ?></form><?php } ?>
