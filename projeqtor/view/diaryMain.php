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
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/diaryMain.php');  
  $user=getSessionUser();
?>
<input type="hidden" name="objectClassManual" id="objectClassManual"
	value="Diary" />
<div class="container" dojoType="dijit.layout.BorderContainer">
	<div id="listDiv" dojoType="dijit.layout.ContentPane" region="top"
		class="listTitle" splitter="false"
		style="height: auto !important">
		<table width="100%" height="36px" class="listTitle"
			style="max-height: 36px;">
			<tr height="17px" style="max-height: 17px;">
				<td style="width: 50px; text-align: center">
          <?php echo formatIcon('Diary',32,null,true);?>
        </td>
				<td style="width: 50px; text-align: left"><span class="title"><?php echo i18n('menuDiary');?></span></td>
				<td style="text-align: center;"> 
		   <?php 
		   $period=Parameter::getUserParameter("diaryPeriod");
		   if (!$period) {$period="month";}
		   $year=date('Y');
		   $month=date('m');
		   $week=date('W');
		   $day=date('Y-m-d');
		   if(sessionValueExists('dateSelectorDiary')) {
		     $day = getSessionValue('dateSelectorDiary');
		     $year=date('Y',strtotime($day));
		     $month=date('m',strtotime($day));
		     $week=date('W',strtotime($day));
		     if ($period=='week') {
		       if ($week>50 and $month==1) {
             $year--;
           } else if ($week==1 and $month==12) {
             $year++;
           }
		     }
		   }
		   echo '<div style="font-size:20px; max-height:32px;" id="diaryCaption">';
		   if ($period=='month') {
		     echo i18n(date("F",mktime(0,0,0,$month,1,$year))).' '.$year;
		   } else if ($period=='week') {
         $firstday=date('Y-m-d',firstDayofWeek($week, $year));
         $lastday=addDaysToDate($firstday, 6);
         echo $year.' #'.$week."<span style='font-size:70%'> (".htmlFormatDate($firstday)." - ".htmlFormatDate($lastday).")</span>";
       } else if ($period=='day') {
         $vDayArr = array('', i18n("Monday"),i18n("Tuesday"),i18n("Wednesday"),
		                i18n("Thursday"), i18n("Friday"),i18n("Saturday"),i18n("Sunday"));
         echo $vDayArr[date("N",mktime(0,0,0,$month,date('d'),$year))]." ".htmlFormatDate($day);
       }
       echo "</div>";
		   ?>
		   </td>
				<td style="width: 200px; text-align: right; align: right;"
					nowrap="nowrap">
                <?php echo i18n("colFirstDay");
                $currentWeek=weekNumber(date('Y-m-d')) ;
                $currentYear=strftime("%Y") ;
                $currentDay=date('Y-m-d',firstDayofWeek($currentWeek,$currentYear));?> 
                <div dojoType="dijit.form.DateTextBox"
						<?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
						id="dateSelector" name=""
						dateSelector""
                  invalidMessage="<?php echo i18n('messageInvalidDate')?>"
						type="text" maxlength="10"
						style="width: 100px; text-align: center;"
						class="input roundedLeft" hasDownArrow="true"
						value="<?php if(sessionValueExists('dateSelectorDiary')){ echo getSessionValue('dateSelectorDiary');}else{ echo $currentDay; }?>">
						<script type="dojo/method" event="onChange">
                    saveDataToSession('dateSelectorDiary',formatDate(dijit.byId('dateSelector').get("value")), false);
                    return diarySelectDate(this.value);
                  </script>
					</div>
				</td>
				<td nowrap="nowrap" width="650px"><form id="diaryForm"
						name="diaryForm">
						<input type="hidden" name="diaryPeriod" id="diaryPeriod"
							value="<?php echo $period;?>" /> <input type="hidden"
							name="diaryYear" id="diaryYear" value="<?php echo $year;?>" /> <input
							type="hidden" name="diaryMonth" id="diaryMonth"
							value="<?php echo $month;?>" /> <input type="hidden"
							name="diaryWeek" id="diaryWeek" value="<?php echo $week;?>" /> <input
							type="hidden" name="diaryDay" id="diaryDay"
							value="<?php echo $day;?>" />
						<table style="width: 100%">
							<tr>
								<td style="text-align: right">
									<table style="width: 99%">
										<tr>
											<td>
		   					<?php echo i18n("colIdResource");?> 
		   					<select dojoType="dijit.form.FilteringSelect"
												class="input roundedLeft" style="width: 150px;"
												name="diaryResource" id="diaryResource"
												<?php echo autoOpenFilteringSelect();?>
												value="<?php if(sessionValueExists('diaryResource')){ echo getSessionValue('diaryResource');}else{ echo ($user->isResource)?$user->id:'0';}?>">
													<script type="dojo/method" event="onChange">
                       saveDataToSession('diaryResource',dijit.byId('diaryResource').get("value"), false);
                       loadContent("../view/diary.php","detailDiv","diaryForm");
                      </script>
        							<?php
                        $specific='diary';
                        include '../tool/drawResourceListForSpecificAccess.php'?>  
       						</select>
											</td>
										</tr>
									</table>
								</td>
								<td>&nbsp;</td>
								<td width="25px"><span class="nobr">&nbsp;</span></td>
		               <?php
              if (Parameter::getGlobalParameter('filterByStatus') == 'YES') {  ?>
            <td width="36px">
									<button title="<?php echo i18n('filterByStatus');?>"
										dojoType="dijit.form.Button" id="iconStatusButton"
										name="iconStatusButton"
										iconClass="dijitButtonIcon dijitButtonIconStatusChange"
										class="detailButton" showLabel="false">
										<script type="dojo/connect" event="onClick" args="evt">
                     protectDblClick(this);
						         if (dijit.byId('barFilterByStatus').domNode.style.display == 'none') {
							         dijit.byId('barFilterByStatus').domNode.style.display = 'block';
						         } else {
							         dijit.byId('barFilterByStatus').domNode.style.display = 'none';
						         }
                     saveDataToSession("displayByStatusList_Diary", dijit.byId('barFilterByStatus').domNode.style.display, true);
                     loadContent("../view/diary.php","detailDiv","diaryForm");
          				 </script>
									</button>
								</td>
			      <?php } ?>
            <td width="5px">
									<div dojoType="dijit.form.DropDownButton"
										id="listItemsSelector" jsId="listItemsSelector"
										name="listItemsSelector" showlabel="false" class="comboButton"
										iconClass="iconGlobalView iconSize22 imageColorNewGui"
										title="<?php echo i18n('itemSelector');?>">
										<div dojoType="dijit.TooltipDialog" class="white"
											id="listItemsSelectorDialog"
											style="position: absolute; top: 50px; right: 40%">
											<script type="dojo/connect" event="onShow" args="evt">
                      oldSelectedItems=dijit.byId('diarySelectItems').get('value');
                    </script>
											<div style="text-align: center; position: relative;">
												<div style="position: absolute; top: 34px; right: 42px;"></div>
											</div>
											<div style="height: 5px; border-bottom: 1px solid #AAAAAA"></div>
											<div>
  							    	<?php
                      $itemsToDisplay=Parameter::getUserParameter('diarySelectedItems');
                      ?>
    									<select dojoType="dojox.form.CheckedMultiSelect"
													multiple="true"
													style="border: 1px solid #A0A0A0; width: initial; height: 120px;"
													;
    									id="diarySelectItems"
													name="diarySelectItems"
													onChange="diarySelectItems(this.value);"
													value="<?php echo $itemsToDisplay ?>">
  							      <?php
                      $arrOpt=array("All"=>"activityStreamAllItems",
                                    "Action"=>'Action',
                                    "Ticket"=>'Ticket',
                                    "MilestonePlanningElement"=>'Milestone',
                                    "Meeting"=>'Meeting',
                                    "Activity"=>"Activity");
                      foreach ($arrOpt as $key=>$val)
                         echo '<option value="'.$key.'">'.htmlencode(i18n($val)).'</option>';
                      ?>
                      </select>
											</div>
											<div style="height: 5px; border-top: 1px solid #AAAAAA"></div>
											<div style="text-align: center; position: relative;">
												<button title="" dojoType="dijit.form.Button"
													class="mediumTextButton" id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
                        <script type="dojo/connect" event="onClick"
														args="evt">
                          dijit.byId('listItemsSelector').closeDropDown();
                        </script>
												</button>
												<div style="position: absolute; bottom: 33px; right: 42px;"></div>
											</div>
										</div>
									</div>
								</td>
								</td>
								<td style="text-align: right">
									<table style="width: 99%">
										<tr>
											<td><?php echo i18n("labelShowDone")?>&nbsp;</td>
											<td>
												<div title="<?php echo i18n('labelShowDone')?>"
													dojoType="dijit.form.CheckBox" class="whiteCheck"
													type="checkbox" id="showDone" name="showDone"
													<?php if (sessionValueExists('showDoneDiary')){  if(getSessionValue('showDoneDiary')=='on'){ echo 'checked';}}?>>
													<script type="dojo/method" event="onChange">
                  saveDataToSession('showDoneDiary',dijit.byId('showDone').get("value"), false);
                  loadContent("../view/diary.php","detailDiv","diaryForm");
                </script>
												</div>
											</td>
										</tr>
										<tr>
											<td><?php echo i18n("labelShowIdle")?>&nbsp;</td>
											<td>
												<div title="<?php echo i18n('showIdleElements')?>"
													dojoType="dijit.form.CheckBox" class="whiteCheck"
													type="checkbox" id="showIdle" name="showIdle"
													<?php if (sessionValueExists('showIdleDiary')){  if(getSessionValue('showIdleDiary')=='on'){ echo 'checked';}}
                else if (Parameter::getUserParameter('showIdleDefault')=='true'){ echo 'checked';}?>>
													<script type="dojo/method" event="onChange">
                  saveDataToSession('showIdleDiary',dijit.byId('showIdle').get("value"), false);
                  loadContent("../view/diary.php","detailDiv","diaryForm");
                </script>
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</form></td>
			</tr>
		</table>
		   <?php
      if (Parameter::getGlobalParameter('filterByStatus')=='YES') {
        $displayStatus=Parameter::getUserParameter("displayByStatusList_Diary");
        if (!$displayStatus) $displayStatus='none';
        $arrObj=array(new Action(), new Ticket(), new Milestone(), new Meeting(), new Activity());
        $listStatus=array();
        foreach ($arrObj as $obj) {
          $listObjStatus=$obj->getExistingStatus();
          foreach ($listObjStatus as $status) {
            if (!in_array($status, $listStatus)) $listStatus[]=$status;
          }
        }
        ?>

  <div class="listTitle" id="barFilterByStatus" dojoType="dijit.layout.ContentPane" region="top" style="display: <?php echo $displayStatus;?>; width: 100%; height: auto;">
			<table style="display: block; width: 100%">
				<tr style="display: inlineblock; width: 100%">
					<td style="font-weight: bold; padding-left: 50px;"><?php echo i18n("colIdStatus");?>&nbsp;:&nbsp;</td>
			<?php
      $cptStatus=0;
      foreach ($listStatus as $status) {
        $cptStatus+=1;?>
				<td
						style="float: left; height: 100%; width: 130px; white-space: nowrap">
						<div id="showStatus<?php echo $cptStatus; ?>"
							title="<?php echo $status->name; ?>"
							dojoType="dijit.form.CheckBox" type="checkbox"
							value="<?php echo $status->id; ?>">
							<script type="dojo/method" event="onChange">
                 loadContent("../view/diary.php","detailDiv","diaryForm");
					    </script>
						</div>
					<?php echo $status->name; ?>&nbsp;&nbsp;
				</td>
<?php  } ?>
		</tr>
			</table>
			<input type="hidden" id="countStatus"
				value="<?php echo $cptStatus; ?>" />
		</div>
<?php } ?>
		<div height="18px" vertical-align="middle" style="max-height: 18px;">
				<table width="100%">
					<tr>
						<td width="50%;">
							<div class="buttonDiary" onClick="diaryPrevious();">
								<img src="../view/css/images/left.png" />
							</div>
						</td>
						<td style="width: 1px"></td>
						<td width="50%">
							<div class="buttonDiary" onClick="diaryNext();">
								<img src="../view/css/images/right.png" />
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
  
  <?php
  $destinationHeight=intval($_REQUEST['destinationHeight'])-54;
  if (isset($displayStatus) and $displayStatus!='none') $destinationHeight-=16;
  ?>
  <div id="detailDiv" dojoType="dijit.layout.ContentPane" region="center" style="overflow-x:auto;height: <?php echo $destinationHeight?>px">
   <?php include 'diary.php'; ?>
  </div>
	</div>