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
require_once "../tool/projeqtor.php";

?>
<table style="<?php if (isNewGui()) echo 'width:780px';?>">
    <tr>
     <td class="section"><?php echo i18n("sectionStoredFilters");?></td>
    </tr>
    <?php if (isNewGui()) {?><tr><td><div style="height:6px"></div></td></tr><?php }?>
    <tr>
      <td>
        <div id='listStoredFilters' dojoType="dijit.layout.ContentPane" region="center" ><div style="height:250px">loading...</div></div>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td>
        <div id='listSharedFilters' dojoType="dijit.layout.ContentPane" region="center"></div>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
</table>
<table style="<?php if (isNewGui()) echo 'width:780px';?>">
    <tr>
     <td class="section"><?php echo i18n("sectionActiveFilter");?></td>
    </tr>
    <tr>
      <td style="margin: 2px;"> 
        <div id='listFilterClauses' dojoType="dijit.layout.ContentPane" region="center" style="overflow: hidden;"></div>
         
        <form id='dialogFilterForm' name='dialogFilterForm' onSubmit="return false;">
         <input type="hidden" id="filterObjectClass" name="filterObjectClass" />
         <input type="hidden" id="filterClauseId" name="filterClauseId" />
         <input type="hidden" id="filterDataType" name="filterDataType" />
         <input type="hidden" id="filterName" name="filterName" />
         <table width="100%" style="border: 1px solid grey;">
           <tr><td colspan="5" class="filterHeader"><?php echo i18n("addFilterClauseTitle");?></td></tr>
           <tr style="vertical-align: top;">
             <?php //ADD qCazelles - Dynamic filter - Ticket #78?>
           	 <td style="width:<?php echo (isNewGui())?'67':'80';?>px;" title="<?php echo i18n('helpOrInput');?>" >
           	  <div id="filterLogicalOperator" style="width: <?php echo (isNewGui())?'65':'80';?>px;display: none">
           	 	<select dojoType="dijit.form.FilteringSelect"
           	 		id="orOperator" name="orOperator"
           	 		class="input" style="width: <?php echo (isNewGui())?'45px;position:relative;left:5px;':'70px;';?>" value="0">
           	 		<?php echo autoOpenFilteringSelect();?> 
           	 		<!-- BOITE DE DIALOGUE A METTRE SUR LE OR -->						<!-- TODO TODO TODO -->
           	 		<option value="0" selected><?php echo i18n('AND');?></option> <!-- TRANSLATION qCazelles -->
           	 		<option value="1"><?php echo i18n('OR');?></option>			  <!-- TRANSLATION qCazelles -->
           	 	</select>&nbsp;
           	 	</div>
           	 </td>
             <?php //END ADD qCazelles - Dynamic filter - Ticket #78?>
             <td style="width: <?php echo (isNewGui())?'197':'210';?>px;" >
               <div dojoType="dojo.data.ItemFileReadStore" jsId="attributeStore" url="../tool/jsonList.php?listType=empty" searchAttr="name" >
               </div>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="idFilterAttribute" name="idFilterAttribute" 
                missingMessage="<?php echo i18n('attributeNotSelected');?>"
                class="input" value="" style="width: <?php echo (isNewGui())?'180':'200';?>px;" store="attributeStore">
                  <script type="dojo/method" event="onChange" >
                    filterSelectAtribute(this.value);
                  </script>              
               </select>
             </td>
             <td style="width: <?php echo (isNewGui())?'117':'110';?>px;">
               <div dojoType="dojo.data.ItemFileReadStore" jsId="operatorStore" url="../tool/jsonList.php?listType=empty" searchAttr="name" >
               </div>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="idFilterOperator" name="idFilterOperator" 
                missingMessage="<?php echo i18n('valueNotSelected');?>"
                class="input" value="" style="width: 100px;" store="operatorStore">
                  <script type="dojo/method" event="onChange" >
                    filterSelectOperator(this.value);
                  </script>        
               </select>
             </td>
             <td style="width:<?php echo (isNewGui())?'370':'320';?>px;vertical-align:middle;position:relative;">
             <?php //ADD qCazelles - Dynamic filter - Ticket #78?>
               <div id="filterDynamicParameterPane" dojoType="dijit.layout.ContentPane" region="top" 
                style="<?php if (isNewGui()) echo 'position:absolute;left:200px;top:9px;width:160px;overflow:hidden;z-index:20;'?>">
                <?php if (isNewGui()) {?>
                  <div  id="filterDynamicParameterSwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" 
                    title="<?php echo i18n("dynamicValue");?>"
                    value="off" 
                    leftLabel="" rightLabel="" style="width:10px;position:relative; left:0px;top:2px;z-index:99;" >
  		              <script type="dojo/method" event="onStateChanged" >
  		                dijit.byId("filterDynamicParameter").set("checked",(this.value=="on")?true:false);
  		              </script>
  		             </div>
  		          <?php }?>
               	<input type="checkbox" id="filterDynamicParameter" name="filterDynamicParameter" value=""
               	 	dojoType="dijit.form.CheckBox" style="<?php if (isNewGui()) echo 'display:none;'?>"/>
               	 	<label class="checkLabel" for="filterDynamicParameter" 
               	 	style="<?php if (isNewGui()) echo 'font-size:90%;text-align:left;float:none;position:absolute;left:37px;top:1px;text-overflow:ellipsis'?>"><?php echo i18n('dynamicValue');?></label>
               	</div>
               <?php //END ADD qCazelles - Dynamic filter - Ticket #78?>
               <input id="filterValue" name="filterValue" value=""  
                 dojoType="dijit.form.TextBox" 
                 style="width:<?php echo (isNewGui())?'180':'320';?>px" />
               <select id="filterValueList" name="filterValueList[]" value=""  
                 dojoType="dijit.form.MultiSelect" multiple
                 style="<?php echo (isNewGui())?'width:350px;font-size:10pt;padding:30px 0px 0px 0px;color:#555555;':'width:325px;';?>height:150px;" size="10" class="selectList"></select>
               <?php if (isNewGui()) {?>
                 <div id="filterValueListHideTop" style="position:absolute;top:1px;left:1px;width:340px;height:25px;background:#ffffff;z-index:15;display:none" ></div>
               <?php }?>
               <?php if (isNewGui()) {?>
               <div  id="filterValueCheckboxSwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value="off" 
                 leftLabel="" rightLabel="" style="width:10px;position:relative; top:0px;left:5px;z-index:99;display:none;" >
  		           <script type="dojo/method" event="onStateChanged" >
  		             dijit.byId("filterValueCheckbox").set("checked",(this.value=="on")?true:false);
  		           </script>
  		         </div>
               <?php }?>
               <input type="checkbox" id="filterValueCheckbox" name="filterValueCheckbox" value=""  
                 dojoType="dijit.form.CheckBox" style="padding-top:7px;<?php echo (isNewGui())?'margin-left:5px;display:none;':'';?>";/> 
               <input id="filterValueDate" name="filterValueDate" value=""  
                 dojoType="dijit.form.DateTextBox" 
                 constraints="{datePattern:browserLocaleDateFormatJs}"
                 style="width:100px" />
               <select id="filterSortValueList" name="filterSortValueList" value="asc"  
                 dojoType="dijit.form.FilteringSelect"
                 <?php echo autoOpenFilteringSelect();?>
                 missingMessage="<?php echo i18n('valueNotSelected');?>" 
                 style="width:320px" class="input">
                  <option value="asc" SELECTED><?php echo i18n('sortAsc');?></option>
                  <option value="desc"><?php echo i18n('sortDesc');?></option>
               </select> 
             </td>
             <td style="position:relative;width:25px; text-align: center;vertical-align:<?php echo (isNewGui())?'top':'middle';?>;" align="center"> 
               <table>
                 <tr>
                  <td style="position: absolute;<?php echo (isNewGui())?'top:118px;left:-21px;':'margin-top:-60px;margin-left:-2px;';?>">
                    <button style="display:none;" id="showDetailInFilter" dojoType="dijit.form.Button" showlabel="false"
                            title="<?php echo i18n('showDetail')?>" class="resetMargin notButton notButtonRounded"
                            iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui">
                      <script type="dojo/connect" event="onClick" args="evt">
                        var objectName = dijit.byId('showDetailInFilter').get('value');
                        if( objectName ){
                          var objectClass=objectName[0].substr(2);
                          if (objectName[0].indexOf('__id')>=0) {
                            objectClass=objectName[0].substr(objectName[0].indexOf('__id')+4);
                          }  
                          if (objectClass=='TargetProductVersion' || objectClass=='OriginalProductVersion') objectClass='ProductVersion';
                          dijit.byId('filterValueList').reset();
                          showDetail('filterValueList',0,objectClass,true);
                        }
                      </script>
                    </button>
                  </td>
                 </tr>
                 <tr>
                   <td>
                     <a src="css/images/smallButtonAdd.png" class="imageColorNewGui" style="<?php echo (isNewGui())?'position:relative;right:6px;top:7px;':'margin-top:3px;';?>" onClick="addfilterClause();" title="<?php echo i18n('addFilterClause');?>" class="smallButton">
                     <?php echo (isNewGui())?formatMediumButton('Add'):formatSmallButton('Add');?>
                     </a> 
                   </td>
                  </tr>
               </table> 
             </td>
           </tr>
         </table>
        </form>
      </td>
    </tr>
    <?php if (isNewGui()) {?><tr><td><div style="height:6px"></div></td></tr><?php }?>
    <tr style="height:32px">
      <td align="center">
        <table><tr><td>
        <span id="filterDefaultButtonDiv">
          <button class="mediumTextButton" dojoType="dijit.form.Button" onclick="defaultFilter();">
            <?php echo i18n("buttonDefault");?>
          </button>
        </span>
        </td><td>
        <button class="mediumTextButton" dojoType="dijit.form.Button" onclick="clearFilter();">
          <?php echo i18n("buttonClear");?>
        </button>
        </td><td>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="cancelFilter();">
          <?php echo i18n("buttonCancel");?>
        </button>
        </td><td>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogFilterSubmit" onclick="protectDblClick(this);selectFilter();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
        </td></tr></table>
      </td>
    </tr>
  </table>