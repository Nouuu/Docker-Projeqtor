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
 * Welcom screen (replacing Today if no access right)
 */
$maintenance=true;
require_once "../tool/projeqtor.php";
SSO::resetTry();  
$user=getSessionUser();
$mainPage=(! $user or ! $user->id)?true:false;
?>  
<div style="width:100%;height:100%;overflow:hidden">
<table style="width:100%;height:100%;">
    <tr style="height:100%; vertical_align: middle;">
      <td style="width:100%;text-align: center;position:relative">
        <div style="position:relative;width:100%;height:100%;left:0px;">        
          <div style="position:absolute;width:100%;height:100%; top:25%;">
            <img style="height:50%;top:25%;left:25%;opacity:0.10;filter:alpha(opacity=10);" src="img/logoBig.png" />
          </div>
          <div id="welcomeTitle" style="position:absolute;height:100%;top:5%;right:5%;<?php if ($mainPage) echo "cursor:pointer;";?>" 
          <?php if ($mainPage) echo ' onclick="window.location=\'../index.php\';" title="'.i18n('ssoReconnect').'" ';?>
          >
            <?php $logo="../view/img/title.png"; 
                  if (file_exists("../logo.gif")) $logo="../logo.gif"; 
                  if (file_exists("../logo.png")) $logo="../logo.png"; ?> 
            <img style="max-height:60px" src="<?php echo $logo;?>" style="width: 300px; height:54px"/>
          </div>
        </div>
        <?php 
if ($mainPage) {?>
<div style="font-family:verdana, arial;font-size:150%;font-weight:bold;color:#A0A0A0; position:fixed;bottom:50%;left:0;width:100%;text-align:center;"><?php echo i18n('ssoCloseWindowMessage')?></div>
<?php }?>
      </td>
    </tr>
</table>
</div>