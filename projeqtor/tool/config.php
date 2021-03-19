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

// ==================================================================================================
// This file includes all specific parameters for ProjeQtOr application
// Automatic configuration at first run
// ==================================================================================================
header ('Content-Type: text/html; charset=UTF-8');
restore_error_handler();
// Database parameters (connection information)
// BE SURE THIS DATA WAY NOT BE READABLE FROM WEB (see above important notice)
$paramFadeLoadingMode='false';
$paramDebugMode='false';
$defaultBillCode = '0';

$param=array();

$param['crlf01']='';
$label['crlf01']='crlf';
$value['crlf01']="Database configuration";

$param['DbType'] = 'mysql';                           
$label['DbType'] = "Database type";
$value['DbType'] = "Database you will use to store data. The DB engine must be installed.<br/><i>ProjeQtOr is compatible with <b>MySQL</b>, <b>MariaDB</b> (select MySql) and <b>PostgreSql</b></i>";
$pname['DbType'] = 'paramDbType';
$ctrls['DbType'] = '=mysql=pgsql=';
$lists['DbType'] = '=MySql=PostgreSql=';
$requi['DbType'] = true;

$param['DbHost'] = '127.0.0.1';                       
$label['DbHost'] = "Database host";
$value['DbHost'] = "Database Server name.<br/><i>On Windows, if Database server is local, use <b>127.0.0.1</b> for MySql rather than <b>localhost</b></i>";
$pname['DbHost'] = 'paramDbHost';
$ctrls['DbHost'] = 'mandatory';
$requi['DbHost'] = true;

$param['DbPort'] = '3306';                       
$label['DbPort'] = "Database port";
$value['DbPort'] = "Database Server Port<br/><i>Default is <b>3306</b> for MySql and MariaDB, <b>5432</b> for PostgreSql</i>";
$pname['DbPort'] = 'paramDbPort';
$ctrls['DbPort'] = '';
$requi['DbPort'] = true;

$param['DbUser'] = 'root';                            
$label['DbUser'] = "Database user";
$value['DbUser'] = "DB user that will be used to connect to the Database.<br/><i>Default is <b>root</b> for MySql, <b>postgres</b> for PostgreSql</i>";
$pname['DbUser'] = 'paramDbUser';
$ctrls['DbUser'] = 'mandatory';
$requi['DbUser'] = true;

$param['DbPassword'] = '';                       
$label['DbPassword'] = "Database password";
$value['DbPassword'] = "Password for database user<br/><i>This has been defined on Database installation. WAMP tools usually set default password to <b>mysql</b> or leave it empty</i>";
$pname['DbPassword'] = 'paramDbPassword';
$ctrls['DbPassword'] = '';
$requi['DbPassword'] = true;

$param['DbName'] = 'projeqtor';                       
$label['DbName'] = "Database schema name";  
$value['DbName'] = "Database instance name<br><i>Must be a valid instance name (avoid special characters). MySql will create it if it does not exists, <b>PosgreSql requires an already existing schema</b>.</i>";  
$pname['DbName'] = 'paramDbName';
$ctrls['DbName'] = 'mandatory';
$requi['DbName'] = true;

$param['DbDisplayName'] = 'ProjeQtOr';         
$label['DbDisplayName'] = "Name to be displayed"; 
$value['DbDisplayName'] = "Name of the ProjeQtOr instance that will be displayed on top of application<br/><i>Any string is possible, just avoid special characters</> "; 
$pname['DbDisplayName'] = 'paramDbDisplayName';
$ctrls['DbDisplayName'] = '';

$param['DbPrefix'] = '';                              
$label['DbPrefix'] = "Prefix for table names";
$value['DbPrefix'] = "Prefix for all ProjeQtOr table names<br/><i>Can be used to store several instances under same schema (not recommended). May be left blank</i>";
$pname['DbPrefix'] = 'paramDbPrefix';
$ctrls['DbPrefix'] = '';

$param['SslKey'] = '';
$label['SslKey'] = "SSL Key for SSL connexion";
$value['SslKey'] = "Location for SSL Key file for SSL connexion to MySql database<br/><i><b>Keep empty unless you know what it is</b></i>";
$pname['SslKey'] = 'SslKey';
$ctrls['SslKey'] = '';

$param['SslCert'] = '';
$label['SslCert'] = "Certificate for SSL connexion";
$value['SslCert'] = "Location for SSL Certificate file for SSL connexion to MySql database <br/><i><b>Keep empty unless you know what it is</b></i>";
$pname['SslCert'] = 'SslCert';
$ctrls['SslCert'] = '';

$param['SslCa'] = '';
$label['SslCa'] = "CA for SSL connexion";
$value['SslCa'] = "Location for Certificate Authority file for SSL connexion to MySql database <br/><i><b>Keep empty unless you know what it is</b></i>";
$pname['SslCa'] = 'SslCa';
$ctrls['SslCa'] = '';

$param['ldap_allow_login'] = 'false';                              
$label['ldap_allow_login'] = "Allow login from Ldap";
$value['ldap_allow_login'] = "'true' or 'false', if set to true, ProjeQtOr can log users from Ldap";
$pname['ldap_allow_login'] = 'paramLdap_allow_login';
$ctrls['ldap_allow_login'] = '=false=true=';
$hide['ldap_allow_login']=true;

$param['ldap_base_dn'] = 'dc=mydomain,dc=com';                              
$label['ldap_base_dn'] = "Ldap Base DN";
$value['ldap_base_dn'] = "Ldap Base DN (dc=mydomain,dc=com)";
$pname['ldap_base_dn'] = 'paramLdap_base_dn';
$ctrls['ldap_base_dn'] = '';
$hide['ldap_base_dn']=true;

$param['ldap_host'] = 'localhost';                              
$label['ldap_host'] = "Ldap Host address";
$value['ldap_host'] = "Ldap Host address (server name)";
$pname['ldap_host'] = 'paramLdap_host';
$ctrls['ldap_host'] = '';
$hide['ldap_host']=true;

$param['ldap_port'] = '389';                              
$label['ldap_port'] = "Ldap Port";
$value['ldap_port'] = "Ldap Port (default is 389)";
$pname['ldap_port'] = 'paramLdap_port';
$ctrls['ldap_port'] = '';
$hide['ldap_port']=true;

$param['ldap_version'] = '3';                              
$label['ldap_version'] = "Ldap version";
$value['ldap_version'] = "Ldap version (can be 2 or 3)";
$pname['ldap_version'] = 'paramLdap_version';
$ctrls['ldap_version'] = '=2=3=';
$hide['ldap_version']=true;

$param['ldap_search_user'] = 'cn=Manager,dc=mydomain,dc=com';                              
$label['ldap_search_user'] = "Ldap Search User";
$value['ldap_search_user'] = "DN of Ldap user used for search functionality";
$pname['ldap_search_user'] = 'paramLdap_search_user';
$ctrls['ldap_search_user'] = '';
$hide['ldap_search_user']=true;

$param['ldap_search_pass'] = 'secret';                              
$label['ldap_search_pass'] = "LDAP Search User Password";
$value['ldap_search_pass'] = "Password of Ldap user used for search functionality";
$pname['ldap_search_pass'] = 'paramLdap_search_pass';
$ctrls['ldap_search_pass'] = '';
$hide['ldap_search_pass']=true;

$param['ldap_user_filter'] = 'uid=%USERNAME%';                              
$label['ldap_user_filter'] = "Ldap filter";
$value['ldap_user_filter'] = "Ldap filter to find used name (must include %USERNAME%)";
$pname['ldap_user_filter'] = 'paramLdap_user_filter';
$ctrls['ldap_user_filter'] = '';
$hide['ldap_user_filter']=true;
 
// $param['MailSender'] = '';                              
// $label['MailSender'] = "eMail address of sender";
// $value['MailSender'] = "a valid email as sender for mailing function";
// $pname['MailSender'] = 'paramMailSender';
// $ctrls['MailSender'] = 'email';

// $param['MailReplyTo'] = '';                              
// $label['MailReplyTo'] = "eMail address to reply to";
// $value['MailReplyTo'] = "a valid email to define the reply to for mailing function";
// $pname['MailReplyTo'] = 'paramMailReplyTo';
// $ctrls['MailReplyTo'] = 'email';

// $param['AdminMail'] = '';                              
// $label['AdminMail'] = "eMail of administrator";
// $value['AdminMail'] = "a valid email of the administratror (will appear on error messages)";
// $pname['AdminMail'] = 'paramAdminMail';
// $ctrls['AdminMail'] = 'email';

$param['MailSmtpServer'] = 'localhost';                              
$label['MailSmtpServer'] = "SMTP Server";
$value['MailSmtpServer'] = "address of SMTP (mail) server, may be left blank (default is 'localhost')";
$pname['MailSmtpServer'] = 'paramMailSmtpServer';
$ctrls['MailSmtpServer'] = '';
$hide['MailSmtpServer']=true;

$param['MailSmtpPort'] = '25';                              
$label['MailSmtpPort'] = "SMTP Port";
$value['MailSmtpPort'] = "port to talk to SMTP (mail) server (default is '25')";
$pname['MailSmtpPort'] = 'paramMailSmtpPort';
$ctrls['MailSmtpPort'] = '';
$hide['MailSmtpPort']=true;

$param['MailSendmailPath'] = '';                              
$label['MailSendmailPath'] = "Sendmail program path";
$value['MailSendmailPath'] = "to set only on issue to send mails, or not using default sendmail";
$pname['MailSendmailPath'] = 'paramMailSendmailPath';
$ctrls['MailSendmailPath'] = '';
$hide['MailSendmailPath']=true;

// $param['DefaultPassword'] = 'projeqtor';                              
// $label['DefaultPassword'] = "Default password for initialization";
// $value['DefaultPassword'] = "any string possible as default password";
// $pname['DefaultPassword'] = 'paramDefaultPassword';
// $ctrls['DefaultPassword'] = 'mandatory';

$param['PasswordMinLength'] = '5';                              
$label['PasswordMinLength'] = "Min length for password";
$value['PasswordMinLength'] = "any integer, to force a long password (keep is reasonable)";
$pname['PasswordMinLength'] = 'paramPasswordMinLength';
$ctrls['PasswordMinLength'] = 'integer';
$hide['PasswordMinLength']=true;

// === i18n (internationalization)
$param['crlf02']='';
$label['crlf02']='crlf';
$value['crlf02']="Localization <span style='font-size:70%;'><i>(Can be changed afterwards on Global Parameters screen)</i></span>";

$param['DefaultLocale'] = 'en';                              
$label['DefaultLocale'] = "Default language";
$value['DefaultLocale'] = "Default language for the General User Interface<br/><i>Each user will be able to select his own display language</i>";
$pname['DefaultLocale'] = 'paramDefaultLocale';
$ctrls['DefaultLocale'] = '=';
$lists['DefaultLocale'] = '=';
$requi['DefaultLocale'] = true;
$list=Parameter::getLangList();
foreach ($list as $nls=>$lang) {
  //$value['DefaultLocale'].=", '$nls' for $lang";
  $ctrls['DefaultLocale'].=$nls."=";
  $lists['DefaultLocale'].=$lang."=";
}
    
$param['DefaultTimezone'] = 'Europe/Paris';                              
$label['DefaultTimezone'] = "Default time zone";
$value['DefaultTimezone'] = "Default time zone that will be used to define server time<br/><i>List conforms to reference <a href='http://us3.php.net/manual/en/timezones.php' target='#'>http://us3.php.net/manual/en/timezones.php</a></i>";
$pname['DefaultTimezone'] = 'paramDefaultTimezone';
$ctrls['DefaultTimezone'] = '=';
$requi['DefaultTimezone'] = true;
 $listTimezone=Parameter::getTimezoneList();
  foreach ($listTimezone as $nls=>$zone) {
    $ctrls['DefaultTimezone'].=$zone."=";
  }

$param['Currency'] = '€';                              
$label['Currency'] = "Currency";
$value['Currency'] = "Currency displayed for costs<br/><i>Keep it small, 3 character max, <b>1 character is best</b></i>";
$pname['Currency'] = 'currency';
$ctrls['Currency'] = '';

$param['CurrencyPosition'] = 'after';                              
$label['CurrencyPosition'] = "Currency position";
$value['CurrencyPosition'] = "Position of currency displayed for costs<br/><i>Usually <b>$</b> is displayed before ($12.34) while <b>€</b> is displayed after (12,34€)</i>";
$pname['CurrencyPosition'] = 'currencyPosition';
$ctrls['CurrencyPosition'] = '=after=before=none=';

$param['crlf03']='';
$label['crlf03']='crlf';
$value['crlf03']='Security';

// $param['FadeLoadingMode'] = 'true';                              
// $label['FadeLoadingMode'] = "Use fading mode for frames refresh";
// $value['FadeLoadingMode'] = "'true' or 'false', if set to 'true' screens will appear in a fading motion";
// $pname['FadeLoadingMode'] = 'paramFadeLoadingMode';
// $ctrls['FadeLoadingMode'] = '=true=false=';

// $param['IconSize'] = '22';                              
// $label['IconSize'] = "Icon size on menu tree";
// $value['IconSize'] = "'16' for small icons, '22' for medium icons, '32' for big icons";
// $pname['IconSize'] = 'paramIconSize';
// $ctrls['IconSize'] = '=16=22=32=';

// $param['DefaultTheme'] = 'ProjeQtOrFlatBlue';                              
// $label['DefaultTheme'] = "Default color theme, proposed while login";
// $value['DefaultTheme'] = "select a theme in the list";
// $pname['DefaultTheme'] = 'defaultTheme';
// $ctrls['DefaultTheme'] = '=ProjeQtOrFlatBlue=ProjeQtOrFlatRed=ProjeQtOrFlatGreen=ProjeQtOrFlatGrey'
//                         .'=ProjeQtOr=ProjeQtOrFire=ProjeQtOrForest=ProjeQtOrEarth=ProjeQtOrWater=ProjeQtOrWine=';

$param['AttachmentDirectory'] = '../files/attach/';                              
$label['AttachmentDirectory'] = "Directory to store Attachments";
$value['AttachmentDirectory'] = "Any valid directory to store attachments. Set to empty string to disable attachments<br/><span style='color:#A05050'><i><b>Security hint :</b> move it ouside web access, default relative path is not secured and should be used for tests only</i></span>";
$pname['AttachmentDirectory'] = 'paramAttachmentDirectory';
$ctrls['AttachmentDirectory'] = '';

$param['DocumentDirectory'] = '../files/documents/';
$label['DocumentDirectory'] = "Directory to store Documents";
$value['DocumentDirectory'] = "Any valid directory to store files for document versions<br/><span style='color:#A05050'><i><b>Security hint :</b> move it ouside web access, default relative path is not secured and should be used for tests only</i></span>";
$pname['DocumentDirectory'] = 'documentRoot';
$ctrls['DocumentDirectory'] = '';

$param['AttachmentMaxSize'] = 1024*1024*10;                              
$label['AttachmentMaxSize'] = "Max file size for attachment";
$value['AttachmentMaxSize'] = "size in bytes (1024 * 1024 * MB)";
$pname['AttachmentMaxSize'] = 'paramAttachmentMaxSize';
$ctrls['AttachmentMaxSize'] = 'integer';
$hide['AttachmentMaxSize'] = true;

$param['AttachmentMaxSizeMail'] = 1024*1024*2;
$label['AttachmentMaxSizeMail'] = "Max file size for attachment for Mail";
$value['AttachmentMaxSizeMail'] = "size in bytes (1024 * 1024 * MB)";
$pname['AttachmentMaxSizeMail'] = 'AttachmentMaxSizeMail';
$ctrls['AttachmentMaxSizeMail'] = 'integer';
$hide['AttachmentMaxSizeMail'] = true;

$param['ReportTempDirectory'] = '../files/report/';                              
$label['ReportTempDirectory'] = "Temp directory for reports";
$value['ReportTempDirectory'] = "any valid directory in the web structure";
$pname['ReportTempDirectory'] = 'paramReportTempDirectory';
$ctrls['ReportTempDirectory'] = '';
$hide['ReportTempDirectory']=true;

$param['MemoryLimitForPDF'] = '512';                              
$label['MemoryLimitForPDF'] = "Memory limit for PDF reports";
$value['MemoryLimitForPDF'] = "any numeric value, for size in MB";
$pname['MemoryLimitForPDF'] = 'paramMemoryLimitForPDF';
$ctrls['MemoryLimitForPDF'] = '';
$hide['MemoryLimitForPDF']=true;

$param['logFile'] = '../files/logs/projeqtor_${date}.log';                              
$label['logFile'] = "Log file name";
$value['logFile'] = "Any valid file name, including path. May contain '\${date}' to get 1 file per day<br/><span style='color:#A05050'><i><b>Security hint :</b> move it ouside web access, default relative path is not secured and should be used for tests only</i></span>";
$pname['logFile'] = 'logFile';
$ctrls['logFile'] = '';
$requi['logFile'] = true;

$param['logLevel'] = '2';                              
$label['logLevel'] = "Log level";
$value['logLevel'] = "Defines the level of traces written in the log file.<br><i>You'll be able to change this afterwards on administration screen</i>";
$pname['logLevel'] = 'logLevel';
$ctrls['logLevel'] = '=4=3=2=1=';
$lists['logLevel']= '=Script, Debug, Trace and Error=Debug, Trace and Error=Trace and Error=Error only';
$requi['logLevel'] = true;

$firstColor= '545381';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorFlat.css" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtorNew.css" />
  <script type="text/javascript" src="js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"
  
    djConfig='modulePaths: {"i18n":"../../tool/i18n",
                            "i18nCustom":"../../plugin"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js"></script>
  <script type="text/javascript"> 
    var customMessageExists=false;
    var isNewGui=true;
    dojo.require("dojo.parser");
    dojo.require("dojo.i18n");
    dojo.require("dojo.date");
    dojo.require("dojo.date.locale");
    dojo.require("dojo.number");
    dojo.require("dijit.Dialog"); 
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.FilteringSelect");
    var fadeLoading=<?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramFadeLoadingMode'));?>;
    dojo.addOnLoad(function(){
      currentLocale="<?php echo $currentLocale?>";
      setColorTheming('#545381','#e97b2c');
      saveResolutionToSession();
      userBrowserLocaleForDates="";
      var browserLocaleDateFormat=null;
      var browserLocaleTimeFormat=null;
      //saveBrowserLocaleToSession();
      dijit.Tooltip.defaultPosition=["below","right"];
      //dojo.byId('login').focus();
      <?php 
      if (sessionValueExists('theme')){
        echo "dojo.byId('body').className='" . getSessionValue('theme') . "';";
      }
      ?>
      var changePassword=false;
      hideWait();
    }); 
  </script>
</head>

<body id="body" class="nonMobile ProjeQtOrFlatGrey ProjeQtOrNewGui" onLoad="hideWait();" style="overflow: auto; ">
  <div id="waitLogin" >
  </div> 
  <table align="left" valign="top" width="100%" height="100%" class="background">
      <tr height="5%"><td colspan="4">&nbsp;</td></tr>
    <tr height="10%">
      <td rowspan="2" width="80px" valign="top">
      </td>
      <td width="10px" valign="top">
        <img style="height: 54px" src="img/titleSmall.png" />
      </td>
      <td>
        <div class="siteH1" style="color:var(--color-secondary);font-size:32pt;">Configuration</div>
        <br/>
      </td>
    </tr>
    <tr height="90%">
      <td colspan="3" align="left" valign="top">
          <form  dojoType="dijit.form.Form" id="configForm" jsId="configForm" name="configForm" encType="multipart/form-data" action="" method="POST" >
            <script type="dojo/method" event="onSubmit" >
              var callBck=function() {
                dojo.byId("bottom").scrollIntoView();
              };
              loadContent("../tool/configCheck.php","configResultDiv", "configForm", null, null, null, null,callBck);
              return false;        
            </script>
            <table>
            <?php foreach ($param as $par=>$val) {
              $requiredClass=(isset($requi[$par]) and  $requi[$par]=true)?'required':'';
              if ($label[$par]=='crlf') {?>
              <tr style="height:40px"><td></td><td colspan="3" class="siteH2" style="color:var(--color-dark);" ><?php if (isset($value[$par])) echo $value[$par];?></td></tr>
              <?php } else {?>
              <tr style="height:36px;<?php if (isset($hide[$par]) and $hide[$par]==true) echo 'display:none;';?>">     
                <td class="label" style="width:300px"><label style="width:300px;color:var(--color-dark);"><?php echo $label[$par]?>&nbsp;:&nbsp;</label></td>
                <td style="vertical-align:top;">
                <?php if (substr($ctrls[$par],0,1)=='=') {?>
                <select id="param[<?php echo $par;?>]" class="input <?php echo $requiredClass;?>" name="param[<?php echo $par;?>]" 
                   style="width:300px" dojoType="dijit.form.FilteringSelect" 
                   <?php echo autoOpenFilteringSelect();?>
                   value="<?php echo $val;?>">
                 <?php $split=explode('=',$ctrls[$par]);
                 if (isset($lists[$par])) $splitVal=explode('=',$lists[$par]);
                 else $splitVal=$split;
                 $selected=false;
                 foreach($split as $idx=>$val) {
                   if ($val!='=' and $val) {
                   	 echo '<option value="'.$val.'"';
                   	 if ($val==$param[$par] and ! $selected) {
                   	   echo ' selected="selected" ';
                   	   $selected=true;
                   	 }
                   	 echo '>';
                   	 echo $splitVal[$idx];
                   	 echo '</option>';
                   }
                 }?>
                </select>                    
                <?php } else {?>
                <input id="param[<?php echo $par;?>]" name="param[<?php echo $par;?>]" 
                   style="width:300px" type="text"  dojoType="dijit.form.TextBox" class="input <?php echo $requiredClass;?>"
                   value="<?php echo $val;?>" />
                <?php }?>
                </td>
                <td>
                &nbsp;&nbsp;
                  <input id="pname[<?php echo $par;?>]" name="pname[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $pname[$par];?>" />
                  <input id="label[<?php echo $par;?>]" name="label[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $label[$par];?>" />
                  <input id="value[<?php echo $par;?>]" name="value[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $value[$par];?>" />
                  <input id="ctrls[<?php echo $par;?>]" name="ctrls[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $ctrls[$par];?>" /> 
                </td>
                <td style="color:var(--color-medium);vertical-align:middle;">
                   <?php echo $value[$par]?>
                </td>
              </tr>
            <?php } 
              }?>
              <tr>
                <td class="label" style="width:300px"><label style="width:300px">Parameter file name&nbsp;:&nbsp;</label></td>
                <td><input id="location" name="location" class="input required"
                   style="width:300px" type="text"  dojoType="dijit.form.TextBox" 
                   value="../files/config/parameters.php" />
                </td>
                <td></td>
                <td style="color:#a0a0a0;vertical-align:middle;">File name with path where to store current parameters. Must be a .php file name.
                <br/><span style='color:#A05050'><i><b>Security hint :</b> move it ouside web access, default relative path is not secured and should be used for tests only</i></span>
              </tr>
              <tr><td colspan="4">&nbsp;</td>
              </tr>
              <tr>
                <td></td>
                <td colspan="3" >
                  <button tabindex="4" type="submit" style="" class="roundedVisibleButton" id="configButton" dojoType="dijit.form.Button" showlabel="true">&nbsp;Save configuration&nbsp;
                    <script type="dojo/connect" event="onClick" args="evt">
                    return true;
                    </script>
                  </button>
                  <span style="color:var(--color-medium)">Configuration parameters will be saved to above file</span>
                </td>
              </tr>
              <tr><td colspan="4">&nbsp;</td></tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="3">
                  <div id="configResultDiv" name="configResultDiv" dojoType="dijit.layout.ContentPane" region="center" 
                    style="width:100%; border: 0px solid black; overflow: auto;">
					<br/><br/><br/><br/><br/>
                  </div>
				  <br/>
                </td>
              </tr>
            </table>
          </form>
      </td>
    </tr>
  </table>
  <div id="bottom" name="bottom" style="width:100%; border: 0px solid black; overflow: auto;">&nbsp;<br/></div>
</body>
</html>
