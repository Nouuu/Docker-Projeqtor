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
 * Management of PlugIns
 */
  require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
  scriptLog('   ->/view/pluginManagement.php');
  $isIE=false;
  if (array_key_exists('isIE',$_REQUEST)) {
    $isIE=$_REQUEST['isIE'];
  }
  $user=getSessionUser();
  $collapsedList=Collapsed::getCollaspedList();
?>  
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Plugin" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div id="pluginButtonDiv" class="listTitle" dojoType="dijit.layout.ContentPane" region="top" style="z-index:3;overflow:visible">
    <table width="100%">
      <tr height="32px" style="vertical-align: middle;">
        <td width="50px" align="center">
           <?php echo formatIcon('Plugin',32,null,true);?>
        </td>
        <td><span class="title"><?php echo i18n("menuPlugin");?>&nbsp;</span>        
        </td>
        <td width="10px" >&nbsp;
        </td>
        <td width="50px"> 
        </td>
        <td>  
        </td>
      </tr>
    </table>
  </div>
  <div id="formPluginDiv" dojoType="dijit.layout.ContentPane" region="center" style="overflow-y:auto;"> 
    <form dojoType="dijit.form.Form" id="pluginForm" jsId="pluginForm" name="pluginForm" encType="multipart/form-data" method="POST" 
    <?php if ($isIE and $isIE<=9) {?>
    action="../tool/uploadPlugin.php?isIE=<?php echo htmlEncode($isIE);?>"
    target="pluginPost"
    onSubmit="uploadPlugin();"
    <?php }?> 
    >
      <table style="width:97%;margin:10px;padding: 10px;vertical-align:top;">
        <tr style="">
          <td style="width:49%;vertical-align:top;">
            <?php $titlePane="Plugin_installed"; ?> 
            <div dojoType="dijit.TitlePane" 
             open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
             id="<?php echo $titlePane;?>" 
             onHide="saveCollapsed('<?php echo $titlePane;?>');"
             onShow="saveExpanded('<?php echo $titlePane;?>');"       
             title="<?php echo i18n('pluginInstalled');?>">
            <table style="width:100%;">            
              <?php displayInstalledPlugin();?>
            </table>
            </div><br/>
          </td>
          <td style="width:10px">&nbsp;</td>
          <td style="width:49%;vertical-align:top;">
            <?php $titlePane="Plugin_available_local"; ?> 
            <div dojoType="dijit.TitlePane"
             open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
             id="<?php echo $titlePane;?>" 
             onHide="saveCollapsed('<?php echo $titlePane;?>');"
             onShow="saveExpanded('<?php echo $titlePane;?>');"
             title="<?php echo i18n('pluginAvailableLocal');?>">
            
            <table style="width:100%;">
              <tr>
                <td class="display" colspan="6" style="border:0">
                 <?php echo i18n('pluginDir',array(Plugin::unrelativeDir(Plugin::getDir()) ));?>
                <br/><br/></td>
              </tr>
              <?php displayPluginList('local');?>
            </table><br/>
            <table style="width:100%;">
              <tr height="30px"> 
                <td class="dialogLabel" style="width:200px";>
                  <label for="uploadPlugin" style="width:200px;white-space:nowrap"><?php echo i18n("addPluginFile").Tool::getDoublePoint();?></label>
                </td>
                <td style="text-align:left;width;200px" >
                 <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>" />     
                 <?php  if ($isIE and $isIE<=9) {?>
                 <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>"
                  dojoType="dojox.form.FileInput" type="file" 
                  name="pluginFile" id="pluginFile" 
                  cancelText="<?php echo i18n("buttonReset");?>"
                  label="<?php echo i18n("buttonBrowse");?>"
                  title="<?php echo i18n("helpSelectFile");?>" />
                 <?php } else {?>  
                 <div MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>"
                  dojoType="dojox.form.Uploader" type="file" 
                  url="../tool/uploadPlugin.php"
                  target="pluginPost" class="dynamicTextButton pluginFile" style="padding:0;"
                  name="pluginFile" id="pluginFile" 
                  cancelText="<?php echo i18n("buttonReset");?>"
                  multiple="false" 
                  uploadOnSelect="true"
                  onBegin="uploadPlugin();"
                  onChange="changePluginFile(this.getFileList());"
                  onError="hideWait(); dojo.style(dojo.byId('downloadProgress'), {display:'none'});"
                  label="<?php echo i18n("buttonBrowse");?>"
                  title="<?php echo i18n("helpSelectFile");?>">
                  <script type="dojo/connect" event="onComplete" args="dataArray">
                    savePluginAck(dataArray);
	                </script>
          				<script type="dojo/connect" event="onProgress" args="data">
                    saveAttachmentProgress(data);
	                </script>
	              </div>
                 <?php }?>
                 <i><span xname="pluginFileName" id="pluginFileName"></span></i> 
                  <div style="display:none">
                    <iframe name="pluginPost" id="pluginPost" jsid="pluginPost"></iframe>
                  </div>
                </td>
              </tr>
              <tr><td></td><td></tr>
            </table>          
            </div><br/>
            
            <?php $titlePane="Plugin_available_remote"; ?> 
            <div dojoType="dijit.TitlePane"
             open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
             id="<?php echo $titlePane;?>" 
             onHide="saveCollapsed('<?php echo $titlePane;?>');"
             onShow="saveExpanded('<?php echo $titlePane;?>');"
             title="<?php echo i18n('pluginAvailableRemote');?>">
            <table style="width:100%;">
              <tr>
                <td class="display" width="100%" colspan="2" style="text-align:center">
                  <br/><i><?php echo i18n("featureNotAvailable");?></i>
                </td>
              </tr>
              <tr><td colspan="2">&nbsp;</td></tr>
            </table></div>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>

<?php 
function displayPluginList($location) {
  if ($location=='local') {
    $files=Plugin::getZipList();
  } else if ($location=='remote') {
    $files=array();
  } else {
    return; // unknown location
  }
  if (count($files)==0) {
    echo '<tr><td class="display" width="100%" colspan="6" style="text-align:center">';
    echo '<br/><i>'.i18n("noPluginAvailable").'</i></td></tr>';
  } else {
    echo '<tr>';
    echo '<td style="width:5%">&nbsp;</td>';
    echo '<td style="width:10%" class="noteHeader smallButtonsGroup"></td>';
    echo '<td class="noteHeader">'.i18n("colFile").'</td>';
    echo '<td style="width:15%" class="noteHeader">'.i18n("colDate").'</td>';
    echo '<td style="width:10%" class="noteHeader">'.i18n("colSize").'</td>';
    echo '<td style="width:5%">&nbsp;</td>';
    echo '</tr>';
    foreach ($files as $file) {
      echo '<tr>';
      echo '<td></td>';
      echo '<td class="noteData" style="text-align:center;white-space:nowrap"  >';
      echo ' <a onClick="installPlugin(\''.$file['name'].'\');" title="' . i18n('installPlugin') . '" /> '.formatSmallButton('Add').'</a>';
      echo ' <a onClick="loadDialog(\'dialogPluginInfo\', null, true, \'&filename='.$file['name'].'\', true);" title="' . i18n('dialogPluginInfo') . '" /> '.formatSmallButton('View').'</a>';
      echo ' <a onClick="deletePlugin(\''.$file['name'].'\');" title="' . i18n('buttonDeletePluginFile') . '"  /> '.formatSmallButton('Remove').'</a>';
      
//      
       
      echo '</td>';
      echo '<td class="noteData">'.$file['name'].'</td>';
      echo '<td class="noteData" style="text-align:center">'.htmlFormatDate(substr($file['date'],0,10),true).'</td>';
      echo '<td class="noteData" style="text-align:center">'.htmlGetFileSize($file['size']).'</td>';
      echo '<td></td>';
      echo '</tr>';
    } 
  }
}

function displayInstalledPlugin() {
  $pl=new Plugin();
  $plList=$pl->getSqlElementsFromCriteria(array('isDeployed'=>'1','idle'=>'0'),false,null,'name asc');
  if (count($plList)==0) {
    echo '<tr><td class="display" width="100%" colspan="6" style="text-align:center">';
    echo '<br/><i>'.i18n("noPluginAvailable").'</i></td></tr>';
  } else {
    echo '<tr>';
    echo '<td style="width:20%" class="noteHeader">'.i18n("colName").'</td>';
    echo '<td style="width:40%" class="noteHeader">'.i18n("colDescription").'</td>';
    echo '<td style="width:10%" class="noteHeader">'.i18n("colVersion").'</td>';
    echo '<td style="width:10%" class="noteHeader">'.i18n("colDeploymentDate").'</td>';
    echo '<td style="width:10%" class="noteHeader">'.i18n("colDeploymentVersion").'</td>';
    echo '<td style="width:10%" class="noteHeader">'.i18n("colCompatibilityVersion").'</td>';
    echo '</tr>';
    foreach ($plList as $plugin) {
      echo '<tr>';
      echo '<td class="noteData">'.htmlEncode($plugin->name).'</td>';
      echo '<td class="noteData">'.htmlEncode($plugin->description).'</td>';
      echo '<td class="noteData">V'.htmlEncode($plugin->pluginVersion).'</td>';
      echo '<td class="noteData" style="text-align:center">'.htmlFormatDate($plugin->deploymentDate,true).'</td>';
      echo '<td class="noteData" style="text-align:center">'.htmlEncode($plugin->deploymentVersion).'</td>';
      echo '<td class="noteData" style="text-align:center">'.htmlEncode($plugin->compatibilityVersion).'</td>';
      echo '</tr>';
    }
  }

}
?>