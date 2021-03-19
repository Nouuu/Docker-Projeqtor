/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
  config.toolbar = [
    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', '-', 'CopyFormatting','RemoveFormat'] },
    { name: 'paragraph', items: [ 'Indent', 'Outdent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock','-','NumberedList', 'BulletedList'] },
    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
    { name: 'tools', items: [ 'Replace','Print','Maximize','ProjeqtorFullscreen'] },
    { name: 'styles', items: [ 'Font', 'FontSize' ] },
    { name: 'links', items: [ 'Link', 'Unlink', 'Image','Table','Blockquote','Smiley','SpecialChar','PasteFromWord','Source'] }
  ];
  // 'showBlocks'
  config.enterMode = CKEDITOR.ENTER_DIV;
  //config.enterMode = CKEDITOR.ENTER_BR;
  config.removeDialogTabs = 'link:advanced;image:advanced;image:link';
  config.removePlugins='magicline';
  config.uploadUrl = '../tool/uploadImage.php';
  config.imageUploadUrl = '../tool/uploadImage.php';
  config.image_previewText = CKEDITOR.tools.repeat( 'Image', 1 );
  config.magicline_color = '#aaaaaa';
  config.extraAllowedContent = 'span(*){*};div(*){*};p(*){*};table(*){*};tr(*){*};td(*){*};pre(*){*};blockquote(*){*};br[clear];style;';
  config.pasteFilter='span(*){*};div(*){*};p(*){*};table(*){*};tr(*){*};td(*){*};pre(*){*};blockquote(*){*};br[clear];style';
  config.resize_minHeight = 150;
  config.extraPlugins = 'openlink';
  config.extraPlugins += ',sourcearea';
  //config.extraPlugins += ',sourcearea';
  //config.extraPlugins += ',image2';
  //config.extraPlugins += ',uploadimage';
  //gautier
  if (dojo.byId('ckeditorType')){
    var cktype=dojo.byId('ckeditorType').value;
    if ((cktype != 'CK' && ! currentEditorIsNote) || forceCkInline) {
      config.removeButtons = 'tools,Maximize';
      config.extraPlugins += ',staticspace';
      config.staticSpacePriority=1;
      if (! forceCkInline) config.extraPlugins+=',projeqtorfullscreen';
      //config.staticSpacePositionY='bottom';
      //config.staticSpacePositionX='left';
      if (forceCkInline) config.removePlugins += ',elementspath';
      if (forceCkInline) config.resize_enabled = false;
    }
  }
  //config.pasteFromWordRemoveStyles = false; // Removed in 4.6.0
  //config.pasteFromWordRemoveFontStyles = false; // Deprecated in 4.6.0, defaults to false
  config.scayt_sLang = getLocalLocation();
  config.scayt_autoStartup = getLocalScaytAutoStartup();
  
};
/*
CKEDITOR.editorConfig = function( config ) {
config.toolbarGroups = [
  { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
  { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
  { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
  { name: 'forms', groups: [ 'forms' ] },
  { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
  { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
  { name: 'links', groups: [ 'links' ] },
  { name: 'insert', groups: [ 'insert' ] },
  { name: 'styles', groups: [ 'styles' ] },
  { name: 'colors', groups: [ 'colors' ] },
  { name: 'tools', groups: [ 'tools' ] },
  { name: 'others', groups: [ 'others' ] },
  { name: 'about', groups: [ 'about' ] }
];

config.removeButtons = 'Save,NewPage,Preview,Print,Templates,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Strike,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Flash,HorizontalRule,Smiley,PageBreak,Iframe,Styles,Format,ShowBlocks,About';
};
*/