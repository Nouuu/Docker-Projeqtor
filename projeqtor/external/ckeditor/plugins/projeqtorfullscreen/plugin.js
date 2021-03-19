CKEDITOR.plugins.add( 'projeqtorfullscreen', {
  icons: 'projeqtorfullscreen',
  init: function( editor ) {
    editor.addCommand( 'projeqtorOpenFullScreen', {
      exec: function( editor ) {
        displayFullScreenCK(editor.name);
      }
    });
    editor.ui.addButton( 'ProjeqtorFullscreen', {
      label: editor.lang.maximize.maximize,
      command: 'projeqtorOpenFullScreen',
      toolbar: 'links,50'
    });
    CKEDITOR.addCss(
        '.cke_contents_ltr blockquote { padding-left: 10px; padding-right: 8px; border-left-width: 4px;  border-left-color: #f8d4ba;}'
      + 'blockquote {border-color: #f8d4ba; font-family: courier new, courier, serif; font-style:normal;}'
      + 'body ::-webkit-scrollbar {height: 8px; width: 8px; background: var(--color-light); border-radius: 10px;}'
      + 'bosy ::-webkit-scrollbar-thumb {background: var(--color-medium); border-radius: 10px;}'
    );
  }
});