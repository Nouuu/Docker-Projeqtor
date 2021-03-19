<?php
$indexPhp=true;
$theme="ProjeQtOr";
if (is_file ( "../tool/parametersLocation.php" )) {
  include_once '../tool/projeqtor.php';
  $theme=getTheme();
  if(isNewGui())$firstColor=Parameter::getGlobalParameter('newGuiThemeColor');
  $background=(isNewGui())?'#'.$firstColor.' !important':' #C3C3EB';
  $initialisation=false;
} else {
  function isNewGui() {
    return true;
  }
  function getTheme() {
    return 'ProjeQtOrFlatBlue';
  }
  $theme=getTheme();
  $background="#545381";
  $initialisation=true;
  $version=0;
  $build=0;
  $firstColor='545381';
  $background='#545381 !important';
}
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
 * Default page. Redirects to view directory
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html style="margin: 0px; padding: 0px;">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php if (! isset($debugIEcompatibility) or $debugIEcompatibility==false) {?>  
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php }?> 
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorFlat.css" />
   <?php if(isNewGui()){?>
   <link rel="stylesheet" type="text/css" href="../view/css/projeqtorNew.css" />
   <script type="text/javascript" src="js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
   <?php }?>
  <title>ProjeQtOr</title>
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='parseOnLoad: false, 
              isDebug: false'></script>
  <script type="text/javascript">             
     dojo.addOnLoad(function(){
       //dojo.byId("currentLocale").value=dojo.locale;
       var tempo=<?php echo (is_file ( "../tool/parametersLocation.php" ) and SSO::isEnabled())?1000:10;?>;
       window.setTimeout('dojo.byId("indexForm").submit();',tempo);
     });
  </script>
</head>

<body class="tundra <?php echo $theme;?>" style="background-color:<?php echo $background;?>;" >
  <div id="wait">
  &nbsp;
  </div>
   <?php if (1 and isNewGui()) echo '<div style="position:absolute;margin-top:-50%;margin-left:-0%;width:250%;height:250%;opacity:10%;z-index:-2;" class="loginBackgroundNewGui"></div>';?>
  <?php if (isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:60%;z-index:-1;" class="loginBackgroundNewGui"></div>';?>
  <?php if (0 and isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:5%;position:-20px;" class="loginBackgroundNewGui"></div>';?> 
  <table align="center" width="100%" height="100%" class="<?php echo (isNewGui())?'':'loginBackground';?>">
    <tr height="100%">
      <td width="100%" align="center">
        <div class=" <?php echo  (!isNewGui())?'background loginFrame':'loginFrameNewGui';?>" >
        <table  align="center" >
          <tr style="height:10px;" >
            <td align="left" style="height: 1%;" valign="top">
			        <div style="position:relative;width: 400px; height: 54px;">
			          <div style="overflow:visible;position:absolute;width: 480px; height: 280px;top:15px;text-align: center">
				        <img style="max-height:60px" src="<?php 
				          if (file_exists("../logo.gif")) echo '../logo.gif';
				          else if (file_exists("../logo.jpg")) echo '../logo.jpg';
				          else if (file_exists("../logo.png")) echo '../logo.png';
				          else echo 'img/titleSmall.png';?>" />
			          </div>
			        </div>
            </td>
          </tr>
          <tr style="height:100%" height="100%">
            <td style="height:99%;position:relative" align="left" valign="middle">
              <div  id="formDiv" dojoType="dijit.layout.ContentPane" region="center" style="width: 470px; height:210px;overflow:hidden">
                <form id="indexForm" name="indexForm" action="main.php" method="post" target="_top">
                  <input type="hidden" id="xcurrentLocale" name="xcurrentLocale" value="en" />
                  <input type="hidden" id="currentWidth" name="currentWidth" value="" />
                  <script>
                                    (function() {
                    dojo.byId('currentWidth').value= screen.width;
                    })();
                  </script>
                </form>
              </div>
              <div style="width: 470px; height:130px;position:absolute;top:160px;overflow:hidden;text-align:center;">
                  <?php    if (is_file ( "../tool/parametersLocation.php" ) and SSO::isEnabled() and ! SSO::issetAccessFromLoginScreen()) { 
                    echo '<div style="font-size:125%;font-weight:bold">'.i18n("ssoRedirectionMessage",array(SSO::getCommonName())).'</div>';
                  } else {
                    echo  "Loading ..."; 
                    if (is_file ( "../tool/parametersLocation.php" )) SSO::unsetAccessFromLoginScreen();
                  }?>    
              </div>
            </td>
          </tr>
        </table>
        </div>
      </td>
    </tr>
  </table>
</body>

</html>
