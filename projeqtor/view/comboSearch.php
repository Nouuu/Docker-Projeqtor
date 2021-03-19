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
 * Print page of application.
 */
/* @var string $paramDebugMode */
   require_once "../tool/projeqtor.php";
   ob_start();
   scriptLog('   ->/view/comboSearch.php'); 
   $comboDetail=true;
   $mode="";
   if (RequestHandler::isCodeSet('mode')) {
     $mode=RequestHandler::getValue('mode');
   }
   if (RequestHandler::isCodeSet('currentSelectedProject')) {
     setSessionValue('idProjectSelectedForComboDetail', RequestHandler::getId('currentSelectedProject'));
   }
   
   //header("Cache-Control: public, max-age=86400");
 ?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>   
  <meta http-equiv="Cache-control" content="public">
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorFlat.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
    <?php if (isNewGui()) {?>
  <link rel="stylesheet" type="text/css" href="css/projeqtorNew.css" />
  <script type="text/javascript" src="js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorNewGui.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/dojox/mobile/deviceTheme.js" data-dojo-config="mblUserAgent: 'Custom'"></script>
  <?php }?>
  <script type="text/javascript" src="../external/CryptoJS/rollups/md5.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/CryptoJS/rollups/sha256.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/phpAES/aes.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/phpAES/aes-ctr.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/jsgantt.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="js/projeqtorWork.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorFormatter.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/ckeditor/ckeditor.js"></script>
  <script type="text/javascript">
        var dojoConfig = {
            modulePaths: {"i18n":"../../tool/i18n",
                          "i18nCustom":"../../plugin"},
            parseOnLoad: true,
            isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>
        };
  </script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js?version=<?php echo $version;?>"></script>
  <?php Plugin::includeAllFiles();?>
  <script type="text/javascript">
    var isNewGui=<?php echo (isNewGui())?'true':'false';?>;
    var customMessageExists=<?php echo(file_exists(Plugin::getDir()."/nls/$currentLocale/lang.js"))?'true':'false';?>; 
    dojo.require("dojo.data.ItemFileWriteStore");
    dojo.require("dojo.date");
    dojo.require("dojo.date.locale");
    dojo.require("dojo.dnd.Container");
    dojo.require("dojo.dnd.Manager");
    dojo.require("dojo.dnd.Source");
    dojo.require("dojo.dom-construct");
    dojo.require("dojo.dom-geometry");
    dojo.require("dojo.i18n");
    dojo.require("dojo.fx.easing");
    //dojo.require("dojox.fx.ext-dojo.NodeList-style"); // ====================NEW
    dojo.require("dojo.NodeList-fx");
    dojo.require("dojo.parser");   // ===================== NEW
    dojo.require("dojo.query");
    dojo.require("dojo.store.DataStore");
    dojo.require("dijit.ColorPalette");
    dojo.require("dijit.Dialog"); 
    dojo.require("dijit.Editor");
    dojo.require("dijit._editor.plugins.AlwaysShowToolbar");
    dojo.require("dijit._editor.plugins.FullScreen");
    dojo.require("dijit._editor.plugins.FontChoice");
    dojo.require("dijit._editor.plugins.Print");
    dojo.require("dijit._editor.plugins.TextColor");
    //dojo.require("dijit._editor.plugins.LinkDialog");
    //dojo.require("dojox.editor.plugins.LocalImage");
    dojo.require("dijit.Fieldset");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.CheckBox");
    dojo.require("dijit.form.ComboBox");
    dojo.require("dijit.form.DateTextBox");
    dojo.require("dijit.form.FilteringSelect");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.MultiSelect");
    dojo.require("dijit.form.NumberSpinner");
    dojo.require("dijit.form.NumberTextBox");
    dojo.require("dijit.form.RadioButton");
    dojo.require("dijit.form.Select");
    dojo.require("dijit.form.Textarea");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.TimeTextBox");
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.InlineEditBox");
    dojo.require("dijit.layout.AccordionContainer");
    dojo.require("dijit.layout.BorderContainer");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.layout.TabContainer");
    dojo.require("dijit.Menu"); 
    dojo.require("dijit.MenuBar"); 
    dojo.require("dijit.MenuBarItem");
    dojo.require("dijit.PopupMenuBarItem");
    dojo.require("dijit.ProgressBar");
    dojo.require("dijit.TitlePane");
    dojo.require("dijit.Toolbar") 
    dojo.require("dijit.Tooltip");
    dojo.require("dijit.Tree"); 
    dojo.require("dojox.form.FileInput");
    dojo.require("dojox.form.Uploader");
    dojo.require("dojox.form.uploader.FileList");
    dojo.require("dojox.fx.scroll");
    dojo.require("dojox.fx");
    dojo.require("dojox.grid.DataGrid");
    dojo.require("dojox.image.Lightbox");
    var browserLocaleDateFormat="<?php echo Parameter::getUserParameter('browserLocaleDateFormat');?>";
    var browserLocaleDateFormatJs=browserLocaleDateFormat.replace(/D/g,'d').replace(/Y/g,'y');
    <?php $fmt=new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );?>
    var browserLocaleDecimalSeparator="<?php echo $fmt->decimalSeparator?>";
    dojo.addOnLoad(function(){
      if (isNewGui) {
        changeTheme('<?php echo getTheme();?>');
        setColorTheming('<?php echo '#'.Parameter::getUserParameter('newGuiThemeColor');?>','<?php echo '#'.Parameter::getUserParameter('newGuiThemeColorBis');?>');
      }
      currentLocale="<?php echo $currentLocale;?>";
//       var onKeyPressFunc = function(event) {
//             if(event.ctrlKey && event.keyChar == 's'){
//               event.preventDefault();
//               window.top.globalSave();
//             }  
//       };
      var onKeyDownFunc = function(event) {
        if (event.keyCode == 83 && (navigator.platform.match("Mac") ? event.metaKey : event.ctrlKey) && ! event.altKey) { // CTRL + S (save)
          event.preventDefault();
          if (window.top.dojo.isFF) stopDef();
          window.top.globalSave();
        } else if (event.keyCode == 112) { // F1 (show help)
          event.preventDefault();
          if (window.top.dojo.isFF) stopDef();
          window.top.showHelp();
        }
      };
      //dojo.connect(document, "onkeypress", this, onKeyPressFunc);
      dojo.connect(document, "onkeydown", this, onKeyDownFunc);
      dojo.fadeIn({
          node : dojo.byId('body'),
          duration : 300,
          onEnd : function() {
          }
      }).play();
    });
  </script>
</head>
<body id="body" style="opacity:0" class="nonMobile tundra comboDetail <?php echo getTheme();?>" onload="ckEditorReplaceAll();window.top.hideWait();">
  <input type="hidden" id="comboDetail" name="comboDetail" value="true" />
  <input type="hidden" id="comboDetailId" name="comboDetailId" value="" />
  <input type="hidden" id="comboDetailName" name="comboDetailName" value="" />
  <?php 
  if ($mode=='search') {
    echo '<div id="listDiv" style="height:100%" dojoType="dijit.layout.ContentPane" region="top" splitter="true">';
    include 'objectList.php';
    echo '</div>';
  } else if ($mode=='new'){
    echo '<div id="detailDiv" style="height:100%" dojoType="dijit.layout.ContentPane" region="center" splitter="false">';
    include 'objectDetail.php';
    echo '</div>';    
  }
  ?>
</body>
</html>