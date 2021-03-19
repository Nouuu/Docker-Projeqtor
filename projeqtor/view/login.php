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
 * Connnexion page of application.
 */
$mobile=false;
   require_once "../tool/projeqtor.php";
   if (isset($locked) and $locked) {
     include_once "../view/locked.php";
     exit;
   }
   SSO::unsetAvoidSSO();
   SSO::setAccessFromLoginScreen();
   header ('Content-Type: text/html; charset=UTF-8');
   scriptLog('   ->/view/login.php');
   setSessionValue('application', "PROJEQTOR");
// MTY - MULTI CALENDAR
   // Delete calendar's cookies
   setcookie("uOffDayList", "",0,'/');
   setcookie("uWorkDayList", "",0,'/');
   setcookie("offDayList", "",0,'/');
   setcookie("workDayList", "",0,'/');
// MTY - MULTI CALENDAR      
   if (getSessionValue('setup', null, true) or version_compare(ltrim(Sql::getDbVersion(),'V'), '5.0.0',"<") ) {
     $msgList=array();
   } else {
     $msg=new Message();
     $msgList=$msg->getSqlElementsFromCriteria(array('showOnLogin'=>'1', 'idle'=>'0'));
     $msgTypeList=SqlList::getList('MessageType','color');
   }
   $showPassword=true;
   $lockPassword=Parameter::getGlobalParameter('lockPassword');
   if (getBooleanValue($lockPassword)) { $showPassword=false; }
   $hidePasswordOnLogin=Parameter::getGlobalParameter('lockPasswordOnLogin');
   if (getBooleanValue($hidePasswordOnLogin)) { $showPassword=false; }
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="keywork" content="projeqtor, project management" />
  <meta name="author" content="projeqtor" />
  <meta name="Copyright" content="Pascal BERNARD" />
<?php if (! isset($debugIEcompatibility) or $debugIEcompatibility==false) {?>  
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php }?>  
  <title><?php echo (Parameter::getGlobalParameter('paramDbDisplayName'))?Parameter::getGlobalParameter('paramDbDisplayName'):i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="../view/img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="../view/img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtorFlat.css" />
  <?php if(isNewGui()){?>
   <link rel="stylesheet" type="text/css" href="../view/css/projeqtorNew.css" />
   <script type="text/javascript" src="js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
   <script type="text/javascript" src="../external/dojox/mobile/deviceTheme.js" data-dojo-config="mblUserAgent: 'Custom'"></script> 
   <?php }?>
  <script type="text/javascript" src="../external/CryptoJS/rollups/md5.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/CryptoJS/rollups/sha256.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/phpAES/aes.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/phpAES/aes-ctr.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../view/js/projeqtor.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../view/js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"
    djConfig='modulePaths: {"i18n":"../../tool/i18n",
                            "i18nCustom":"../../plugin"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js?version=<?php echo $version.'.'.$build;?>"></script>

  <?php Plugin::includeAllFiles();?>
  <script type="text/javascript"> 
    var isNewGui=<?php echo (isNewGui())?'true':'false';?>;
    var customMessageExists=<?php echo(file_exists(Plugin::getDir()."/nls/$currentLocale/lang.js"))?'true':'false';?>;
    dojo.require("dojo.parser");
    dojo.require("dojo.date");
    dojo.require("dojo.date.locale");
    dojo.require("dojo.number");
    dojo.require("dijit.focus");
    dojo.require("dojo.i18n");
    dojo.require("dijit.Dialog"); 
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.CheckBox");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.FilteringSelect");
    if (isNewGui){
    dojo.require("dojox.mobile.parser");
    dojo.require("dojox.mobile.Switch");
    dojo.require("dojox.mobile.SwapView");
    dojo.require("dojox.mobile.PageIndicator");
    }
    require(["dojo/sniff"], function(sniff) {
      var mobileExists=<?php echo (file_exists("../mobile"))?'true':'false';?>;
      if(mobileExists && (sniff("android") || sniff("ios") || sniff("bb") ) ) { 
        dojo.addOnLoad(function(){
          redirectMobile();
        });
      }
    });
    var fadeLoading=<?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramFadeLoadingMode'));?>;
    var aesLoginHash="<?php echo md5(session_id());?>";
    var browserLocaleDateFormat="";
    var browserLocaleDateFormatJs="";
    var aesKeyLength=<?php echo Parameter::getGlobalParameter('aesKeyLength');?>;
    dojo.addOnLoad(function(){
      if (isNewGui) {
        changeTheme('<?php echo getTheme();?>');
        <?php if (Parameter::getUserParameter('newGuiThemeColor')) { ?>
        setColorTheming('<?php echo '#'.Parameter::getUserParameter('newGuiThemeColor');?>','<?php echo '#'.Parameter::getUserParameter('newGuiThemeColorBis');?>');
        <?php } else {?>
        setColorTheming('#545381', '#e97b2c');
        <?php }?>
      }
      currentLocale="<?php echo $currentLocale?>";
      saveResolutionToSession();
      //saveBrowserLocaleToSession();
      dijit.Tooltip.defaultPosition=["below","right"];
      dijit.byId('login').focus(); 
      // For IE, focus to login is delayed
      dijit.byId('password').focus(); 
      setTimeout("dijit.byId('login').focus();",10);
      //dijit.byId('login').focus(); 
      var changePassword=false;
      hideWait();
      showMessage(1, <?php echo count($msgList);?>);
      if (dojo.isIE && dojo.isIE<=8) {
        $varsParam=new Array();
        $varsParam[0]=dojo.isIE;
        dojo.byId('loginResultDiv').innerHTML=
          '<input type="hidden" id="isLoginPage" name="isLoginPage" value="true" />'
          +'<div class="messageERROR" style="width:100%">'+i18n("warningIE", $varsParam )+'</div>';
        //dojo.byId('loginResultDiv').style.position="fixed";
        //dojo.byId('loginResultDiv').style.top="0px";
        //dojo.byId('loginResultDiv').style.width="100%";
        var hideMessage=function() {
          dojo.byId('loginResultDiv').innerHTML=
          '<input type="hidden" id="isLoginPage" name="isLoginPage" value="true" />'
        };
        disableWidget('password');
        disableWidget('login');
        disableWidget('loginButton');
        disableWidget('passwordButton');
        disableWidget('passwordButton');
        disableWidget('rememberMe');
      } else if (dojo.isIE && dojo.isIE<=10) {
        $varsParam=new Array();
        $varsParam[0]=dojo.isIE;
        dojo.byId('loginResultDiv').innerHTML=
          '<input type="hidden" id="isLoginPage" name="isLoginPage" value="true" />'
          +'<div class="messageWARNING" style="width:100%">'+i18n("warningIE", $varsParam )+'</div>';
      }
    });

    function showMessage(id, idMax) {
      contentNode=dojo.byId('loginMessage_'+id);
      if (! contentNode) return;
      dojo.fadeIn({ 
		    node: contentNode ,
		    duration: 800, 
		    onEnd: function() {
		      id++;
			    if (id<=idMax) { showMessage(id, idMax);}
				}
  		}).play();
    } 
  </script>
</head>
<?php 
if(isNewGui()){
  $firstColor=Parameter::getUserParameter('newGuiThemeColor');
  if(!$firstColor){
    $firstColor= '545381';
  }
?>
<body id="body" class="nonMobile tundra <?php echo getTheme();?>" onLoad="hideWait();" style="overflow: auto;<?php if (isNewGui()) echo 'background-color:#'.$firstColor.' !important;';?>" onBeforeUnload="">
<?php 
}else{
?>
<body class="<?php echo getTheme();?>" onLoad="hideWait();" style="overflow: auto;" onBeforeUnload="">
<?php
}
 if (array_key_exists('objectClass', $_REQUEST) and array_key_exists('objectId', $_REQUEST)  ) {
	Security::checkValidClass($_REQUEST['objectClass']);
echo '<input type="hidden" id="objectClass" value="' . $_REQUEST['objectClass'] . '" />';
echo '<input type="hidden" id="objectId" value="' . htmlEncode($_REQUEST['objectId']) . '" />';
}    
$dbVersion=Sql::getDbVersion();
?>
  <div class="listTitle" style="position:absolute;top:10px;right:10px;<?php if(isNewGui()) echo'padding: 3px;border-radius: 5px;color:var(--color-toolbar-text) !important;font-size:20px;background:transparent !important';?>;opacity:<?php echo($version!=$dbVersion)?'':'60%;';?>"><?php ;
  
  if ($version==$dbVersion) {
  	echo $version;
  } else {
    echo $dbVersion.' &rarr; '.$version;
  }
  ?></div>
  <div id="waitLogin" style="display:none" >
  </div>
  <div class="<?php echo (isNewGui() and !empty($msgList))?'loginMessageContainerNew':'loginMessageContainer';?>">
    <?php if(isNewGui()){
            echo '<div style="margin: 5% 2.5% 5% 2.5%;width: 95%;" id="contentMessageDivLogin" >';
            echo '<script type="dojo/method" event="onload">saveDataToSession("contentMessageDivLogin",dojo.byId("contentMessageDivLogin").offsetWidth, false);</script>';
          }
     ?>
    	<?php 
    	$cpt=0;
    	$count=count($msgList);
    	foreach ($msgList as $msg) {
       #Florent ticket 4030
       $startDate=$msg->startDate;
       $endDate=$msg->endDate;
       $today=date('Y-m-d H:i:s');
       if( $startDate <= $today && $endDate >= $today || $startDate=='' && $endDate=='' || $startDate<= $today && $endDate=='' ){ 
        $cpt++;?>
      <div class="loginMessage" id="loginMessage_<?php echo $cpt;?>" style="border-bottom:<?php echo (isNewGui() and $cpt<$count)?'1px solid':'';?>;">
      <?php if (isNewGui()){
          $messageType=new MessageType($msg->idMessageType);
      }?>
      <div class="loginMessageTitle" style="color:<?php echo (isNewGui())?'white':$msgTypeList[$msg->idMessageType];?>;"><?php echo htmlEncode($msg->name);?></div>
      <br/>
      <?php 
      if(isNewGui()){
       $currentWidth=(RequestHandler::isCodeSet('currentWidth')?RequestHandler::getValue('currentWidth'):'');
       if($currentWidth!='')$calculatedWidth=(($currentWidth*0.33)*0.95)*0.8;
       $width=($currentWidth!='')?$calculatedWidth:400;
       echo htmlSetClickableImages($msg->description,$width);
      }else{
        echo $msg->description;
      }?>
      <br/>
      <br/>
      </div>
      <?php }}?>
    <?php if(isNewGui())echo '</div>';?>
  </div>
  <?php if (1 and isNewGui()) echo '<div style="position:absolute;margin-top:-50%;margin-left:-0%;width:250%;height:250%;opacity:10%;z-index:-2;" class="loginBackgroundNewGui"></div>';?>
  <?php if (isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:60%;z-index:-1;" class="loginBackgroundNewGui"></div>';?>
  <?php if (0 and isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:5%;position:-20px;" class="loginBackgroundNewGui"></div>';?>
  <table align="center" width="100%" height="100%" class="<?php echo (isNewGui())?'':'loginBackground';?>">
    <tr height="100%">
	    <td width="100%" align="center">
	      <div class="background <?php  echo (isNewGui())?'loginFrameNewGui':'loginFrame' ;?>" >
	          <!--  <div style="position:fixed; top:0px; right:0px; height:128px;width:128px;box-shadow:0px 0px 50px #FFFFFF; background: #FFFFFF; border-radius:64px;"> 
	          <img style="position:absolute; top:2px;right:-2px;" src="../view/img/logoMedium.png"  />
	          </div>  -->
	          <?php if(isNewGui() and $dbVersion==''){?>
	          <div class="messageInitLogin" style="height:370px">
	           <div style="text-align: center;margin-top:15px;"><span class="titleWelcomeMessage"><?php echo i18n('welcomeOnProjeQtOr');?></span></div>
	           <div style="text-align: left;margin:15px;margin-top:35px;padding-bottom:20px"><span class="textWelcomeMessage"><?php echo i18n('projeqtorIntroducoryText');?></span></div>
	          </div>
	          <?php }?>
			  <table  align="center">
			    <?php if(isNewGui()){?>
			    <tr style="height:42px;" >
			     <td align="center" style="position:relative;height: 1%;" valign="center">
			       <div style="position:relative;height:75px;">
			         <div class="divLoginIconDrawing" style="position:absolute;background-color:#<?php echo $firstColor;?>";>
			           	<div class="divLoginIconBig"></div>		         
			         </div>
			       </div>
			     </td>
			    </tr>
			    <?php }?>
			    <tr style="height:10px;" >
			      <td align="left" style="position:relative;height: 1%;" valign="top">
			        <div style="position:relative;width: 400px; height: 54px;">
			          <div style="z-index:10;overflow:visible;position:absolute;width: 480px; height: 50px;top:15px;text-align: center">
				        <img style="max-height:60px" src="<?php 
				          if (file_exists("../logo.gif")) echo '../logo.gif';
				          else if (file_exists("../logo.jpg")) echo '../logo.jpg';
				          else if (file_exists("../logo.png")) echo '../logo.png';
				          else echo '../view/img/titleSmall.png';?>" />
			          </div>
			        </div>
			      </td>
			    </tr>
			    <tr style="height:100%" height="100%">
			      <td style="height:99%" align="left" valign="middle">
			        <div  id="formDiv" dojoType="dijit.layout.ContentPane" region="center" style="background:transparent !important;width: 470px; overflow:hidden;position: relative;">
			          <form  dojoType="dijit.form.Form" id="loginForm" jsId="loginForm" name="loginForm" encType="multipart/form-data" action="" method="" >
			            <script type="dojo/method" event="onSubmit" >             
                    connect(false);
    		            return false;        
                  </script>
                  <br/><br/>
			            <table width="100%">
			              <tr>     
			               <td title="<?php echo i18n("login");?>" style="background:transparent !important;width: 100px;">
			                  
			               </td>
			               <td title="<?php echo i18n("login");?>" style="width:<?php echo (isNewGui())?'450px':'250px';?>">
			                 <?php if(isNewGui())echo '<div class="loginDivContainer container">'; ?>
			                   <div class="<?php echo (isNewGui())?'inputLoginIconNewGui iconLoginUserNewGui imageColorNewGui iconSize22':'inputLoginIcon iconLoginUser';?> ">&nbsp;</div>
			                   <input tabindex="1" id="login" type="text"  class="<?php echo (isNewGui())?'inputLoginNewGui':'inputLogin';?>"
			                   dojoType="dijit.form.TextBox" />
                               <input type="hidden" id="hashStringLogin" name="login" value=""/>  
                              <?php if(isNewGui())echo '</div>'; ?>
			               </td>
			               <td width="100px">&nbsp;</td>
			              </tr>
			              <tr style="font-size:50%"><td colspan="3">&nbsp;</td></tr>
			              <tr>
			                <td title="<?php echo i18n("password");?>" style="background:transparent !important;">
			                  
			                </td>  
			                <td title="<?php echo i18n("password");?>">
			                <?php if(isNewGui())echo '<div class="loginDivContainer container" style="float:left">'; ?>
			                   <div  class="<?php echo (isNewGui())?'inputLoginIconNewGui iconLoginPasswordNewGui imageColorNewGui iconSize22':'inputLoginIcon iconLoginPassword';?> ">&nbsp;</div>
			                   <input  tabindex="2" id="password" type="password" class="<?php echo (isNewGui())?'inputLoginNewGui':'inputLogin';?>" dojoType="dijit.form.TextBox" />
                                <input type="hidden" id="hashStringPassword" name="password" value=""/>
                             <?php if(isNewGui()){
                               echo '<div class="iconView imageColorNewGui iconSize22" style="cursor:pointer;float:right;position:relative;top:6px;margin-right:4px;" onClick="dojo.setAttr(\'password\',\'type\',((dojo.getAttr(\'password\',\'type\')==\'password\')?\'text\':\'password\'));" ></div>';
                               echo '</div>';
                             }?>
			                </td>
			                <td>
                             </td>
			              </tr>
			              <?php if (Parameter::getGlobalParameter('rememberMe')!='NO') {?>
			              <tr style="font-size:50%"><td colspan="2">&nbsp;</td></tr>
			              <tr style="height:30px">
			                <td></td>
			                <?php if(!isNewGui()){ ?>
			                   <td><div style="width:200px;text-align:center;"><div class="greyCheck" dojoType="dijit.form.CheckBox" type="checkbox" name="rememberMe"></div> <?php echo i18n('rememberMe');?></div></td>
			                <?php }else{?>
			                   <td style="<?php if(isNewGui()) echo "font-size:12px;height:32px;padding-top: 10px;";?>">
			                     <div style="width:auto;text-align:center;float:left;" class="switchLogin">
			                       <div class="colorSwitch" data-dojo-type="dojox/mobile/Switch" name="rememberMe"  value="off" leftLabel="" rightLabel="" style="top:4px;z-index:99;margin-right:5px;">
			                       </div> <?php echo i18n('rememberMe');?>
                                 </div>
                                  <?php if($showPassword and isNewGui()){?>
                                  <div  id="passwordButton" style="float:right;" class="largeTextButton passwordButtonNewGui" onClick="connect(true);return false;" > <?php echo i18n('buttonChangePassword') ?></div> 
                                  <?php } ?>
                               </td>
			                <?php }?>
			                <td></td>
			              </tr>
			              <?php }?>
			              <tr style="font-size:50%;height:14px;"><td colspan="3">&nbsp;</td></tr>
			              <tr>
			                <td style="background:transparent !important;">&nbsp;</td>
			                <td style="text-align:center" >
			                  <button tabindex="3" id="loginButton"  dojoType="dijit.form.Button" type="submit" class="largeTextButton" showlabel="true" >
			                  <?php echo i18n('loginLib');?>
			                    <script type="dojo/connect" event="onClick" args="evt">
                                 return true;
                                </script>
			                  </button>
			                </td>
			                <td></td>
			              </tr>
	<?php 
	if ($showPassword and !isNewGui()) { 
	?>               <tr style="height:5px"><td colspan="3" ></td></tr>
			              <tr>
			                <td style="background:transparent !important;">&nbsp;</td>
			                <td style="text-align:center">  
			                  <button tabindex="4" id="passwordButton" class="largeTextButton" type="button" dojoType="dijit.form.Button" showlabel="true"><?php echo i18n('buttonChangePassword') ?>
			                    <script type="dojo/connect" event="onClick" args="evt">
                                  connect(true);
                                  return false;
                                </script>
			                  </button> 
			                </td>
			                <td ></td>
			              </tr>
  <?php }?>
			              <tr><td colspan="3">&nbsp;</td></tr>
			              <tr>
			                
			                <td colspan="3" style="position:fixed;width:100%; height:100%">
			                  <div id="loginResultDiv" dojoType="dijit.layout.ContentPane" region="none" style="margin-left: 6px;">
			                    <input type="hidden" id="isLoginPage" name="isLoginPage" value="true" />
			                    <?php if (Parameter::getGlobalParameter('applicationStatus')=='Closed'
			                          or Sql::getDbVersion()!=$version) {
			                    	      echo '<div style="position:fixed;top:50%; left:50%;margin-left:-220px;margin-top:55px;">';
			                    	      if (!isNewGui()) echo '<img src="../view/img/closedApplication.gif" width="60px"/>';
			                    	      echo '</div>';
			                    	      echo '<div class="messageERROR" >';
			                    	      if (Parameter::getGlobalParameter('applicationStatus')=='Closed') {
			                    	        echo htmlEncode(Parameter::getGlobalParameter('msgClosedApplication'),'withBR');
			                    	      } else {
			                    	      	echo i18n('wrongMaintenanceUser');
			                    	      }
			                    	      echo '</div>';
			                          } else if (array_key_exists('lostConnection',$_REQUEST)) {
			                            //echo '<div class="messageWARNING">'.i18n("disconnectMessage");
			                            echo '<div class="messageWARNING">';
			                            //echo '<br/>';
			                            echo i18n("errorConnection").'</div>';
			                          } else if (isset($errorSSO)) {
			                            echo '<div class="messageERROR" >';
			                            echo $errorSSO;
			                            echo '</div>';
			                          }
			                     ?>
			                  </div>
			                </td>
			              </tr>
			            </table>
			          </form>
		          </div>
		        </td>
		      </tr>
	      </table>
	      </div>
      </td>
    </tr>
  </table>
    <div id="dialogConfirm" dojoType="dijit.Dialog" title="<?php echo i18n("dialogConfirm");?>">
  <table>
    <tr>
      <td width="50px">
           <?php echo formatIcon('Confirm',32);?>
      </td>
      <td>
        <div id="dialogConfirmMessage"></div>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <input type="hidden" id="dialogConfirmAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogConfirm').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" id="dialogConfirmSubmitButton" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);dijit.byId('dialogConfirm').acceptCallback();dijit.byId('dialogConfirm').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
  
</body>
</html>