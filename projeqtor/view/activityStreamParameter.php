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
 * ============================================================================ Presents an object.
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

$user = getSessionUser ();
$showClosed=Parameter::getUserParameter("activityStreamShowClosed");
$addedRecently=Parameter::getUserParameter("activityStreamAddedRecently");
$updatedRecently=Parameter::getUserParameter("activityStreamUpdatedRecently");
$activityStreamNumberElement=Parameter::getUserParameter("activityStreamNumberElement");
$activityStreamIdNote=Parameter::getUserParameter("activityStreamIdNote");
$activityStreamNumberDays=Parameter::getUserParameter("activityStreamNumberDays");
$showOnlyNotes=Parameter::getUserParameter('showOnlyNotes');
if($showOnlyNotes=='')$showOnlyNotes='NO';
if(!$activityStreamNumberDays){
  $activityStreamNumberDays="7";
}
$inputWidth=(RequestHandler::getValue('destinationWidth')<1000)?100:150;

if(!isNewGui()){
?>
<table width="100%">
	<tr height="32px">
		<td width="50px" <?php if (isNewGui()) echo 'style="position:relative;top:2px;"';?> align="center"><?php echo formatIcon('ActivityStream', 32, null, true);?></td>
		<td><span class="title"><?php echo i18n('menuActivityStream');?>&nbsp;</span></td>
	</tr>
</table>

<div style="width: 100%; margin: 0 auto; height: <?php echo (isNewGui())?'110':'90';?>px; padding-bottom: 2px; border-bottom: 1px solid #CCC;background-color:<?php echo (isNewGui())?'var(--color-background)':'#ffffff';?>">
  <form id="activityStreamForm" name="activityStreamForm">
		<table width="100%" class="activityStream" style="margin-left:10px;<?php if (isNewGui()) echo 'position:relative;top:-15px;';?>">
			<tr>
				<td valign="top" width="20%">
				  <table >
					  <input type="hidden" id="activityStreamShowClosed" name="activityStreamShowClosed" value="<?php echo $showClosed;?>" />
			      <tr style="height:20px;"><td colspan="2"></td></tr>
						<tr>
							<td colspan="2" align="left" style="white-space:nowrap;padding-right:20px">
							  <a onclick="resetActivityStreamListParameters();refreshActivityStreamList();" href="#" style="cursor: pointer;">
							    <?php echo i18n("activityStreamResetParameters");?>
							  </a>							  
							</td>
						</tr>
						<tr>
						  <td colspan="2" align="left" style="white-space:nowrap;padding-right:20px;">
							  <a onclick="switchActivityStreamListShowClosed();refreshActivityStreamList();" href="#" style="cursor: pointer;<?php if (isNewGui()) echo 'position:relative;top:5px;';?>">
							    <?php echo ucfirst(i18n("labelShowIdle"));?>
							  </a><?php $displayShowClosedCheck=($showClosed)?'inline-block':'none';?><span id="activityStreamShowClosedCheck" style="display:<?php echo $displayShowClosedCheck;?>;margin-left:10px;";><img src="css/images/iconSelect.png"/></span>
							</td>
						</tr>
						<tr>
  					  <td align="left" style="width:10%;white-space:nowrap;">
  						 <?php echo i18n("limitDisplayActivityStream");?>&nbsp;:
  						</td>
  						<td align="left" style="margin-top:10px;padding-right:20px;">
    						<select title="<?php echo i18n('limitDisplayActivityStream')?>" type="text" class="filterField roundedLeft" dojoType="dijit.form.FilteringSelect" required
    						value="<?php echo ($activityStreamNumberElement!='')?$activityStreamNumberElement:'100';?>" 
                            <?php echo autoOpenFilteringSelect(); ?> 
                            id="activityStreamNumberElement" name="activityStreamNumberElement" style="width:80px;margin-left:16px;<?php if (!isNewGui()) echo 'height:20px;font-size:8pt;';?>" onChange="refreshActivityStreamList();">
                              <option value="10">10</option>
                              <option value="50">50</option>
                              <option value="100" >100</option>
                              <option value="200" >200</option>
    				        </select>
    				    </td>
					   </tr>
					</table>
				</td>
				<td valign="top" width="20%">
					<table class="activityStreamFilter" >
						<tr>
						  <td colspan="2" style="text-align: left"><strong><?php echo i18n('filterOnAuthor')?></strong></td>
						</tr>
						<tr style="height:5px;"></tr>
						<tr>						  
							<td style="width:10%" align="right">
							 <?php echo ucfirst(i18n('colIdAuthor'));?>&nbsp;:&nbsp;
							</td>
							<td align="left" style="margin-top:10px;padding-right:20px">
							  <select title="<?php echo i18n('filterOnAuthor')?>" type="text" class="filterField roundedLeft inputParameter" dojoType="dijit.form.FilteringSelect"
                <?php echo autoOpenFilteringSelect();?> style="width:<?php echo $inputWidth;?>px;"
                id="activityStreamAuthorFilter" name="activityStreamAuthorFilter" >
                  <?php 
                    $selectedAuthor=Parameter::getUserParameter('activityStreamAuthorFilter');
                    if ($selectedAuthor==' ') $selectedAuthor=null;
                    htmlDrawOptionForReference('idUser', $selectedAuthor); ?>
                  <script type="dojo/method" event="onChange" >
                    refreshActivityStreamList();
                  </script>
                </select>
							</td>
					  </tr>
					  <tr style="height:2px;"></tr>
					  <tr>
					   <td align="right">
							 <?php echo ucfirst(i18n('Team'));?>&nbsp;:&nbsp;
						 </td>
						 <td align="left" style="padding-right:20px">
							  <select title="<?php echo i18n('filterOnTeam')?>" type="text" class="filterField roundedLeft inputParameter" dojoType="dijit.form.FilteringSelect"
                <?php echo autoOpenFilteringSelect();?> style="width:<?php echo $inputWidth;?>px;"
                id="activityStreamTeamFilter" name="activityStreamTeamFilter" >
                  <?php 
                    $selectedTeam=Parameter::getUserParameter('activityStreamTeamFilter');
                    if ($selectedTeam==' ') $selectedTeam=null;
                    htmlDrawOptionForReference('idTeam', $selectedTeam, null, false); ?>
                  <script type="dojo/method" event="onChange" >
                    refreshActivityStreamList();
                  </script>
                </select>
						 </td>						
						</tr>
					</table>
				</td>
				<td valign="top" width="20%">
					<table class="activityStreamFilter">		
						<tr>
						  <td colspan="2" style="text-align: left"><strong><?php echo i18n('filterOnElement')?></strong></td>
						</tr>
						<tr style="height:5px;"></tr>
						<tr>
						  <td style="width:10%;" align="right">
							 <?php echo ucfirst(i18n('colType'));?>&nbsp;:&nbsp;
							</td>
						  <td align="left" style="margin-top:10px;padding-right:20px">
							  <select title="<?php echo i18n('filterOnElement')?>" type="text" class="filterField roundedLeft inputParameter" dojoType="dijit.form.FilteringSelect"
                <?php echo autoOpenFilteringSelect();?> style="width:<?php echo $inputWidth;?>px;"
                id="activityStreamTypeNote" name="activityStreamTypeNote">
                  <?php 
                    $selectedElementType=Parameter::getUserParameter('activityStreamElementType');
                    htmlDrawOptionForReference('idImportable', $selectedElementType, null, false); ?>
                  <script type="dojo/method" event="onChange" >
                    refreshActivityStreamList();
                    activityStreamTypeRead();
                  </script>
                </select>
							</td>
						</tr>
						<tr>
						<td align="right" style="width:10%">
					   <?php echo ucfirst(i18n('colId'));?>&nbsp;:&nbsp;
					  </td>
					  <td align="left" style="padding-right:20px;">
              <div style="width:30px;font-size:8pt;" class="filterField rounded" dojoType="dijit.form.TextBox" value="<?php echo $activityStreamIdNote;?>"
               type="text" id="activityStreamIdNote" name="activityStreamIdNote" onChange="refreshActivityStreamList();" <?php echo (trim($selectedElementType)=="")?"readonly=readonly":"";?>>
              </div>
            </td>
            </tr>
              <tr>
              <input type="hidden" id="showOnlyNotesValue" name="showOnlyNotesValue" value="<?php echo $showOnlyNotes;?>" /> 
               <td colspan="2" style="width:50%;white-space:nowrap;" align="right">
                <a onclick="showOnlyNoteStream();" href="#" style="cursor: pointer;display:flex;">
                 <?php echo i18n("showOnlyNotes");?>
                 <?php $displayShowOnlyNotes=($showOnlyNotes=='YES')?'inline-block':'none';?>
                 <span id="showOnlyNotes" style="display:<?php echo $displayShowOnlyNotes;?>;margin-left:10px;";><img src="css/images/iconSelect.png"/></span>
               </a>
              </td>
            </tr>
		  </table>
        </td>
       <td valign="top" width="20%">
        <table>
          <input type="hidden" id="activityStreamAddedRecently" name="activityStreamAddedRecently" value="<?php echo $addedRecently;?>" />   
          <input type="hidden" id="activityStreamUpdatedRecently" name="activityStreamUpdatedRecently" value="<?php echo $updatedRecently;?>" /> 
         	<tr>
					  <td colspan="2" style="text-align: left"><strong><?php echo i18n('filterOnDate')?></strong></td>
					</tr>      
					<tr style="height:5px;"></tr>
					<tr>             
          <td colspan="2" style="width:50%;white-space:nowrap;" align="right">
               <a onclick="switchActivityStreamListAddedRecently();refreshActivityStreamList();" href="#" style="cursor: pointer;display:flex;">
                 <?php echo i18n("dashboardTicketMainAddedRecently");?>
                 <?php $displayAddedRecentlyCheck=($addedRecently)?'inline-block':'none';?>
                 <span id="activityStreamAddedRecentlyCheck" style="display:<?php echo $displayAddedRecentlyCheck;?>;margin-left:10px;";><img src="css/images/iconSelect.png"/></span>
               </a>
          </td>
          </tr>
					<tr>
						<td colspan="2" align="left" style="width:50%;white-space:nowrap;">
							 <a onClick="switchActivityStreamListUpdatedRecently();refreshActivityStreamList();" href="#" style="cursor: pointer;display:flex;<?php if (isNewGui()) echo 'position:relative;top:5px;';?>">
							   <?php echo i18n("dashboardTicketMainUpdatedRecently");?>
							   <?php $displayUpdatedRecentlyCheck=($updatedRecently)?'inline-block':'none';?>
							   <span id="activityStreamUpdatedRecentlyCheck" style="display:<?php echo $displayUpdatedRecentlyCheck;?>;margin-left:10px;";><img src="css/images/iconSelect.png"/></span>							   
							 </a>
						</td>
					</tr>
				  <tr>
						<td style="width:10%;white-space:nowrap;" align="left">
							<?php echo ucfirst(i18n('colDays'));?>&nbsp;:
						</td>
						<td align="left">		
              <div style="width:30px;<?php if (!isNewGui()) echo 'font-size:8pt;';?>" class="filterField rounded" dojoType="dijit.form.TextBox" value="<?php echo $activityStreamNumberDays;?>"
               type="text" id="activityStreamNumberDays" name="activityStreamNumberDays" onChange="refreshActivityStreamList();">
              </div>
            </td>
          </tr>		
				</table>
       </td>
       <td valign="top" width="">
         <table ><tr><td>
          <div style="position:absolute;<?php echo (isNewGui())?'top:0px;right:30px':'top:42px;right:10px';?>" class="imageColorNewGui" onClick="refreshActivityStreamList();"><?php echo formatBigButton('Refresh');?></div>
         </td></tr></table>
       </td>      
			</tr>
		</table>
	</form>
</div>
<?php }else{ ?>
<table width="100%">
	<tr height="32px">
		<td width="50px" <?php if (isNewGui()) echo 'style="position:relative;top:2px;"';?> align="center"><?php echo formatIcon('ActivityStream', 32, null, true);?></td>
		<td><span class="title"><?php echo i18n('menuActivityStream');?>&nbsp;</span></td>
	</tr>
</table>

<div style="width: 100%; margin: 0 auto; height: <?php echo (isNewGui())?'90':'90';?>px; padding-bottom: 2px; border-bottom: 1px solid #CCC;background-color:<?php echo (isNewGui())?'var(--color-background)':'#ffffff';?>">
  <form id="activityStreamForm" name="activityStreamForm">
		<table width="100%"  style="margin-left:10px;<?php if (isNewGui()) echo 'position:relative;top:-29px;';?>">
			<tr>
			
			
			
			<td valign="top" width="15%">
         <table ><tr><td>
          <div title=" <?php echo i18n('paramRefreshUpdates');?>" style="cursor:pointer;left:40px;position:absolute;top:80px;" class="iconSize32 iconRefresh  imageColorNewGui" onClick="refreshActivityStreamList();"></div>
          <div class="iconSize32 iconEraser  imageColorNewGui" title=" <?php echo i18n('activityStreamResetParameters');?>" style="cursor:pointer;position:absolute;top:80px;left:100px;"  onClick="resetActivityStreamListParametersNewGui();refreshActivityStreamList();"></div>
         </td></tr></table>
       </td>   
			
			
				<td valign="top" width="25%">
					<table>
						<tr>
						  <td > <div style="height:120px;text-align:center;writing-mode: vertical-rl; transform: rotate(180deg);border-left:1px solid;margin-right:15px;">
						  <strong><?php echo ucfirst(i18n('filterOnElementActivityStream'));?></strong> </div>
						  </td>
						  <td>
						    <table>
						    <tr>
						      <td style="width:10%;" align="right">
      			       <?php echo ucfirst(i18n('colType'));?>&nbsp;&nbsp;
      			     </td>
      			     <td align="left" style="margin-top:10px;width:<?php echo $inputWidth;?>px;">
        			     <select title="<?php echo i18n('filterOnElement')?>" type="text" class="filterField roundedLeft inputParameter" dojoType="dijit.form.FilteringSelect"
                        <?php echo autoOpenFilteringSelect();?> style="width:<?php echo $inputWidth;?>px;"
                        id="activityStreamTypeNote" name="activityStreamTypeNote">
                          <?php 
                            $selectedElementType=Parameter::getUserParameter('activityStreamElementType');
                            htmlDrawOptionForReference('idImportable', $selectedElementType, null, false); ?>
                          <script type="dojo/method" event="onChange" >
                          refreshActivityStreamList();
                          activityStreamTypeRead();
                        </script>
                  </select>
        			 </td>
        			 <td align="right" style="width:5%;">
        			   <?php echo ucfirst(i18n('colId'));?>&nbsp;&nbsp;
        			 </td>
        			 <td align="left" style="padding-right:20px;">
                      <div style="width:30px;font-size:8pt;" class="filterField rounded" dojoType="dijit.form.TextBox" value="<?php echo $activityStreamIdNote;?>"
                       type="text" id="activityStreamIdNote" name="activityStreamIdNote" onChange="refreshActivityStreamList();" <?php echo (trim($selectedElementType)=="")?"readonly=readonly":"";?>>
                      </div>
                </td>
  						</tr>
  						<tr>						  
  							<td style="width:10%" align="right">
  							 <?php echo ucfirst(i18n('colIdAuthor'));?>&nbsp;&nbsp;
  							</td>
  							<td align="left" style="margin-top:10px;padding-right:20px">
  							  <select title="<?php echo i18n('filterOnAuthor')?>" type="text" class="filterField roundedLeft inputParameter" dojoType="dijit.form.FilteringSelect"
                  <?php echo autoOpenFilteringSelect();?> style="width:<?php echo $inputWidth;?>px;"
                  id="activityStreamAuthorFilter" name="activityStreamAuthorFilter" >
                    <?php 
                      $selectedAuthor=Parameter::getUserParameter('activityStreamAuthorFilter');
                      if ($selectedAuthor==' ') $selectedAuthor=null;
                      htmlDrawOptionForReference('idUser', $selectedAuthor); ?>
                    <script type="dojo/method" event="onChange" >
                    refreshActivityStreamList();
                  </script>
                  </select>
  							</td>
  					  </tr>
  					  <tr style="height:2px;"></tr>
  					  <tr>
  					   <td align="right">
  							 <?php echo ucfirst(i18n('Team'));?>&nbsp;&nbsp;
  						 </td>
  						 <td align="left" style="padding-right:20px">
  							  <select title="<?php echo i18n('filterOnTeam')?>" type="text" class="filterField roundedLeft inputParameter" dojoType="dijit.form.FilteringSelect"
                  <?php echo autoOpenFilteringSelect();?> style="width:<?php echo $inputWidth;?>px;"
                  id="activityStreamTeamFilter" name="activityStreamTeamFilter" >
                    <?php 
                      $selectedTeam=Parameter::getUserParameter('activityStreamTeamFilter');
                      if ($selectedTeam==' ') $selectedTeam=null;
                      htmlDrawOptionForReference('idTeam', $selectedTeam, null, false); ?>
                    <script type="dojo/method" event="onChange" >
                    refreshActivityStreamList();
                  </script>
                  </select>
  						 </td>
  						 </tr>
						  </table>
						  
						  </td>
						</tr>
					</table>
				</td>
				
       <td valign="top" width="20%">
        <table>
          <input type="hidden" id="activityStreamAddedRecently" name="activityStreamAddedRecently" value="<?php echo $addedRecently;?>" />   
          <input type="hidden" id="activityStreamUpdatedRecently" name="activityStreamUpdatedRecently" value="<?php echo $updatedRecently;?>" /> 
						<tr>
						  <td> <div style="height:120px;text-align:center;writing-mode: vertical-rl; transform: rotate(180deg);border-left:1px solid;margin-right:15px;">
						  <strong><?php echo i18n('filterOnEdit')?></strong> </div>
						  </td>
						  <td>
						    <table>
						      <tr>
                  <td  style="padding:10px;white-space:nowrap;text-align:right;">
                     <?php echo i18n("dashboardTicketMainAddedRecently");?>&nbsp;&nbsp;&nbsp;&nbsp;
                     <?php $displayAddedRecentlyCheck=($addedRecently)?'inline-block':'none';?>
                  </td>
                  <td style="text-align:left;">
                  <div  id="addRecentlySwitch" name="addRecentlySwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value="<?php if($displayAddedRecentlyCheck=='inline-block'){?>on<?php }else{?>off<?php }?>" leftLabel="" rightLabel="">
                  <script type="dojo/method" event="onStateChanged" >
                    switchActivityStreamListAddedRecently();refreshActivityStreamList();
                  </script>
                  </div>
                </td>
                </tr>
                
        			  <tr>
        						<td style="padding:10px;white-space:nowrap;text-align:right;">
        							   <?php echo i18n("dashboardTicketMainUpdatedRecently");?>
        							   <?php $displayUpdatedRecentlyCheck=($updatedRecently)?'inline-block':'none';?>
        						</td>
        					  <td style="text-align:left;">
                  <div  id="updatedRecentlySwitch" name="updatedRecentlySwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value="<?php if($displayUpdatedRecentlyCheck=='inline-block'){?>on<?php }else{?>off<?php }?>" leftLabel="" rightLabel="">
                    <script type="dojo/method" event="onStateChanged" >
                    switchActivityStreamListUpdatedRecently();refreshActivityStreamList();
                  </script>
                  </div>
                </td>
        					</tr>
        					
        				  <tr>
        						<td style="width:10%;white-space:nowrap;" align="right">
        							<?php echo ucfirst(i18n('since'));?>&nbsp;
        						</td>
        						<td align="left">		
                      <div style="width:30px;<?php if (!isNewGui()) echo 'font-size:8pt;';?>" class="filterField rounded" dojoType="dijit.form.TextBox" value="<?php echo $activityStreamNumberDays;?>"
                       type="text" id="activityStreamNumberDays" name="activityStreamNumberDays" onChange="refreshActivityStreamList();">
                      </div>
                   </td>
                   <td style="width:10%;white-space:nowrap;" align="right">
        							<?php echo i18n('days');?>&nbsp;
        						</td>
                   </tr>
                
             </td>
						</tr>
					</table>
            
          </tr>		
				</table>
       </td>
       
       <td valign="top" width="20%">
       <table>
       <input type="hidden" id="activityStreamShowClosed" name="activityStreamShowClosed" value="<?php echo $showClosed;?>" />
       	<tr>
  			  <td align="right" style="padding:10px;white-space:nowrap;">
  				    <?php echo ucfirst(i18n("labelShowIdle"));?>
  				  <?php $displayShowClosedCheck=($showClosed)?'inline-block':'none';?>
  				</td>
  		    <td style="text-align:left;">
          <div  id="showIdleSwitchAS" name="showIdleSwitchAS" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value="<?php if($displayShowClosedCheck=='inline-block'){?>on<?php }else{?>off<?php }?>" leftLabel="" rightLabel="">
            <script type="dojo/method" event="onStateChanged" >
              switchActivityStreamListShowClosed();refreshActivityStreamList();
            </script>
          </div>
        </td>
  				
  				
			 </tr>
			 <tr style="margin-top:10px;">
        <input type="hidden" id="showOnlyNotesValue" name="showOnlyNotesValue" value="<?php echo $showOnlyNotes;?>" /> 
         <td  style="padding:10px;white-space:nowrap;" align="right">
           <?php echo ucfirst(i18n("showOnlyNotes"));?>
           <?php $displayShowOnlyNotes=($showOnlyNotes=='YES')?'inline-block':'none';?>
        </td>
        <td style="text-align:left;">
          <div  id="showOnlyNoteSwitch" name="showOnlyNoteSwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value="<?php if($displayShowOnlyNotes=='inline-block'){?>on<?php }else{?>off<?php }?>" leftLabel="" rightLabel="">
            <script type="dojo/method" event="onStateChanged" >
              showOnlyNoteStream();
            </script>
          </div>
        </td>
       </tr>
       <tr>
  		  <td align="left" style="width:10%;white-space:nowrap;">
  			 <?php echo i18n("limitDisplayActivityStream");?>&nbsp;
  			</td>
  			<td align="left" style="margin-top:10px;padding-right:20px;">
  				<select title="<?php echo i18n('limitDisplayActivityStream')?>" type="text" class="filterField roundedLeft" dojoType="dijit.form.FilteringSelect" required
  				        value="<?php echo ($activityStreamNumberElement!='')?$activityStreamNumberElement:'100';?>" 
                        <?php echo autoOpenFilteringSelect(); ?> 
                      id="activityStreamNumberElement" name="activityStreamNumberElement" style="width:80px;margin-left:16px;<?php if (!isNewGui()) echo 'height:20px;font-size:8pt;';?>" onChange="refreshActivityStreamList();">
                        <option value="10">10</option>
                        <option value="50">50</option>
                        <option value="100" >100</option>
                        <option value="200" >200</option>
  		     </select>
  		  </td>
			 </tr>
       </table>
       </td>
       
			</tr>
		</table>
	</form>
</div>
<?php } ?>