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

include_once("../tool/projeqtor.php");
$proj='*';
if(sessionValueExists('project')){
  $proj=getSessionValue('project');
} else {
  setSessionValue('project', "*");
}
$prj=new Project();
$prj->id='*';
//$cpt=$prj->countMenuProjectsList();
$limitToActiveProjects=true;
$critFld=null;
$critVal=null;
if (sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1) {
  $limitToActiveProjects=false;
}
if (sessionValueExists('projectSelectorShowHandlelProject') and getSessionValue('projectSelectorShowHandlelProject')==1) {
	$critFld='handled';
	$critVal=1;
}
$subProjectsToDraw=$prj->drawSubProjects('selectedProject', false, true, $limitToActiveProjects);     
$cpt=substr_count($subProjectsToDraw,'<tr>');
$displayMode="standard";
$paramDisplayMode=Parameter::getUserParameter('projectSelectorDisplayMode');
if ($paramDisplayMode) {
  setSessionValue('projectSelectorDisplayMode', $paramDisplayMode);
}
if (sessionValueExists('projectSelectorDisplayMode')) {
  $displayMode=getSessionValue('projectSelectorDisplayMode');
}
$nbProj=0;
$arrProj=explode(',', $proj);
$first=null;
if ($proj=='*') {
  $nbProj=0;
} else {
  foreach ($arrProj as $idp) {
    if (trim($idp)!='' and trim($idp)!='*') {
      $nbProj++;
      if (! $first) $first=$idp;
    }
  }
}
if ($displayMode!='standard' and $nbProj>1) {
  $proj=$first;
  $nbProj=1;
  setSessionValue('project',$proj);
}
?>
<?php if ($displayMode=='standard') {?>
<span maxsize="160px" style="position: absolute; left:0px; top:px; height: 20px; width: 241px; color:#202020;" 
  dojoType="dijit.form.DropDownButton"
  id="selectedProject" jsId="selectedProject" name="selectedProject" showlabel="true" class="">
  <span style="width:220px; text-align: left;">
    <div style="width:220px; overflow: hidden; text-align: left;" >
    <?php
if ($nbProj==0) {
  echo '<i>' . i18n('allProjects') . '</i>';
} else if($nbProj > 1){
  echo '<i>'.i18n('selectedProject').'</i>';
} else {
  $projObject=new Project($proj);
  echo htmlEncode($projObject->name);
}
    ?>
    </div>
  </span>
  <span dojoType="dijit.TooltipDialog" class="white" <?php echo ($cpt>25)?'style="max-width:900px;"':'';?>> 
     <button id="multiProjectSelectButton" dojoType="dijit.form.Button" style="right:25px;position:absolute;"><?php echo i18n('buttonOK')?>
       <script type="dojo/connect" event="onClick" args="evt">
          selectedMultiProject();
       </script>
     </button>  
     <?php $heightSelectorList=(sessionValueExists('screenHeight'))?round(intval(getSessionValue('screenHeight'))*0.80):500; ?>
    <div style="max-height: <?php echo $heightSelectorList;?>px; overflow-x: hidden; overflow-y: auto;">
    <?php 
      echo $subProjectsToDraw;
    ?>
    </div> 
  </span>
</span>
    <div id="multiProjectSelector"  dojoType="dijit.form.TextBox" style="display:none" value="">
     <script type="dojo/connect" event="onChange" args="evt">
       setSelectedProject(this.value, '<i>'+i18n('selectedProject')+'</i>', 'selectedProject',true);
     </script>
   </div>
   <div id="projectSelectorFiletering" style="display:none" dojoType="dijit.form.FilteringSelect" >
     <script type="dojo/connect" event="onChange" args="evt">
       setSelectedProject(this.value, this.displayedValue, 'selectedProject',true);
     </script>
     <option value=""></option>
     <?php htmlDrawOptionForReference("idProject", null, null, false, $critFld, $critVal, $limitToActiveProjects);?>
   </div>
   <input type="hidden" id="projectSelectorMode" value="Standard" />
   <?php if(!isNewGui()){?>
   <div style="text-align:left;position:absolute; top:<?php echo (isNewGui())?'-2':'-2';?>px; left:281px; padding:0px;">
      <button id="projectSelectorComboButton" dojoType="dijit.form.Button" showlabel="false " style="position: relative; left:26px; top:2px; height: 20px"
         title="<?php echo i18n('searchProject');?>" iconClass="iconSearch16 iconSearch iconSize16" >
         <script type="dojo/connect" event="onClick" args="evt">        
            showDetail('projectSelectorFiletering', false , 'Project',true,null,true);    
         </script>
       </button>
	</div>
	<?php }?>
<?php } else if ($displayMode=='select') {?>
<select dojoType="dijit.form.FilteringSelect" class="input" 
   style="position: absolute; left:4px; top:<?php echo (isNewGui())?'-3':'1';?>px; width: 241px;height:<?php echo (isNewGui())?'20':'22';?>px;" 
   <?php echo autoOpenFilteringSelect();?>
   name="projectSelectorFiletering" id="projectSelectorFiletering" >
   <script type="dojo/connect" event="onChange" args="evt">
    if (this.isValid()) {
      setSelectedProject(this.value, this.displayedValue, null,true);
    }
  </script>
   <option value="*"><i><?php echo i18n("allProjects");?></i></option>
   <?php htmlDrawOptionForReference("idProject", $proj, null, true,$critFld, $critVal, $limitToActiveProjects);?>
</select>
<input type="hidden" id="projectSelectorMode" value="Filtering" />
 <?php if(!isNewGui()){?>
   <div style="text-align:left;position:absolute; top:1px; left:281px; padding:0px;">
      <button id="projectSelectorComboButton" dojoType="dijit.form.Button" showlabel="false " style="position: relative; left:26px; top:-1px; height: 20px"
         title="<?php echo i18n('searchProject');?>" iconClass="iconSearch16 iconSearch iconSize16">
         <script type="dojo/connect" event="onClick" args="evt">        
            showDetail('projectSelectorFiletering', false , 'Project',false,null,true);    
         </script>
       </button>
	</div>
	<?php }?>
<?php } else if($displayMode=="search") {?>
<select id="projectSelectorFiletering" data-dojo-type="dijit.form.FilteringSelect" class="input" style="position: absolute; left:4px; top:<?php echo (isNewGui())?'-3':'1';?>px; width: 241px;height:<?php echo (isNewGui())?'20':'22';?>px;"  
<?php echo autoOpenFilteringSelect();?>
name="projectSelectorFiletering" 
    data-dojo-props="
        queryExpr: '*${0}*',
        autoComplete:false">
  <script type="dojo/connect" event="onChange" args="evt">
    if (this.isValid()) {
      setSelectedProject(this.value, this.displayedValue, null,true);
    }
  </script>
   <option value="*"><i><?php echo i18n("allProjects");?></i></option>
   <?php htmlDrawOptionForReference("idProject", $proj, null, true,$critFld, $critVal, $limitToActiveProjects);?>  
</select>
	<input type="hidden" id="projectSelectorMode" value="Filtering" />
	 <?php if(!isNewGui()){?>
   <div style="text-align:left;position:absolute; top:1px; left:281px; padding:0px;">
      <button id="projectSelectorComboButton" dojoType="dijit.form.Button" showlabel="false " style="position: relative; left:26px; top:-1px; height: 20px"
         title="<?php echo i18n('searchProject');?>" iconClass="iconSearch16 iconSearch iconSize16">
         <script type="dojo/connect" event="onClick" args="evt">        
            showDetail('projectSelectorFiletering', false , 'Project',false,null,true);    
         </script>
       </button>
	</div>
	<?php }?>
<?php } else  {
  ?>
ERROR : Unknown display mode
<?php }?>