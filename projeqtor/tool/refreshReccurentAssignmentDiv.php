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
scriptLog('   ->/view/refreshReccurentAssignmentDiv.php'); 

$idResource = RequestHandler::getId('assignmentIdResource');
$idAssignment=RequestHandler::getId('assignmentId');
$keyDownEventScript=NumberFormatter52::getKeyDownEvent();

$assignmentObj = new Assignment($idAssignment);

$assRec=array();
for ($i=1;$i<=7;$i++) $assRec[$i]=null;
$ar=new AssignmentRecurring();
$arList=$ar->getSqlElementsFromCriteria(array('idAssignment'=>$idAssignment));
foreach($arList as $ar) {
  $assRec[$ar->day]=$ar->value;
}

$resource=new ResourceAll($idResource);

if($resource->id){
	$calendar = new CalendarDefinition($resource->idCalendarDefinition);
}else{
	$calendar = new CalendarDefinition();
}

?>
<table style="margin-left:143px;">
  <tr><td colspan="7">&nbsp;</td></tr>
  <tr>
    <td colspan="7" class="section"><?php echo i18n("sectionRecurringWeek");?></td>
  </tr>
  <tr>
    <?php for ($i=1; $i<=7; $i++) {?>
    <td class="dialogLabel" style="text-align:center"><?php echo i18n('colWeekday' . $i);?></td>
    <?php }?>
  </tr>
  <tr>
    <?php for ($i=1; $i<=6; $i++) {?>
    <td>
    <?php  $value=(isset($assRec[$i]))?Work::displayWork($assRec[$i]):0;
            $dayofweek = 'dayOfWeek'.$i;?>
      <div dojoType="dijit.form.NumberTextBox"  style="width:53px;" name="recurringAssignmentW<?php echo $i;?>" id="recurringAssignmentW<?php echo $i;?>" value="<?php echo $value;?>" 
      constraints="{min:0,max:999.99}" class="input <?php if ($calendar->$dayofweek == 1) echo ' offDay';?>" >
      <?php echo $keyDownEventScript;?> 
      </div>
    </td>
    <?php }?>
    <td>
    <?php  $value=(isset($assRec[7]))?Work::displayWork($assRec[7]):0;?>
      <div dojoType="dijit.form.NumberTextBox"  style="width:53px;" name="recurringAssignmentW7" id="recurringAssignmentW7" value="<?php echo $value;?>" 
      constraints="{min:0,max:999.99}" class="input <?php if ($calendar->dayOfWeek0 == 1) echo ' offDay';?>" >
      <?php echo $keyDownEventScript;?> 
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <button class="<?php echo (isNewGui())?'':'roundedVisibleButton';?>" dojoType="dijit.form.Button" type="button" >
      <script type="dojo/connect" event="onClick" >
                var val1=dijit.byId('recurringAssignmentW1').get('value');
                for (var i=2; i<=7; i++) {
                  if (! dojo.hasClass('widget_recurringAssignmentW'+i,'offDay')) dijit.byId('recurringAssignmentW'+i).set("value",val1);
                }
              </script>
       <?php echo i18n("copy");?>
      </button>
    </td>
    <td colspan="5" style="text-align:right">
    <?php echo i18n('paramWorkUnit').'&nbsp;=&nbsp;'.i18n(Work::getWorkUnit());?> 
    </td>
  </tr> 
   <tr><td colspan="5">&nbsp;</td></tr>
</table>