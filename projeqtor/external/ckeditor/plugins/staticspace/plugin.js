( function() {

	'use strict';

	var pluginCss =
		'.cke-staticspace-container {' +
			'position: relative;' +
		'}' +
		'.cke-staticspace-toolbar {' +
			'position: absolute;' +
			'z-index: 1000;' +
		'}' +
		'.cke-staticspace-toolbar-right {' +
			'right: 0;' +
		'}' +
		'.cke-staticspace-toolbar-top {' +
			'bottom: 100%;' +
			'margin-bottom:3px;' +
		'}';

	CKEDITOR.plugins.add('staticspace', {

		onLoad: function() {
			CKEDITOR.addCss( pluginCss );
		},
		init: function( editor ) {
			var priority = editor.config.staticSpacePriority || 9;

			editor.on( 'loaded', function() {
				create( editor );
			}, null, null, priority );
		}

	} );

	function create( editor ) {
		if ( editor.element ) {
			var innerHtml = editor.fire( 'uiSpace', { space: 'top', html: '' } ).html;

			if ( innerHtml ) {

				var positionX = editor.config.staticSpacePositionX ? editor.config.staticSpacePositionX : 'left',
					positionY = editor.config.staticSpacePositionY ? editor.config.staticSpacePositionY : 'top',

					containerTpl = CKEDITOR.addTemplate( 'staticspace', 
						'<div class="cke-staticspace-container" style="display:none;">' + 
							'<div' +
								' id="cke_{name}"' +
								' class="cke {id} cke_reset_all cke_chrome cke_editor_{name} cke-staticspace-toolbar cke_{langDir} ' + CKEDITOR.env.cssClass + 
								( positionX === 'right' ? ' cke-staticspace-toolbar-right ' : ' cke-staticspace-toolbar-left ' ) + 
								( positionY === 'bottom' ? ' cke-staticspace-toolbar-bottom ' : ' cke-staticspace-toolbar-top ' ) + '"' +
								' dir="{langDir}"' +
								' title="' + ( CKEDITOR.env.gecko ? ' ' : '' ) + '"' +
								' lang="{langCode}"' +
								' role="presentation"' +
								( editor.title ? ' aria-labelledby="cke_{name}_arialbl"' : ' ' ) +
								'>' +
								( editor.title ? '<span id="cke_{name}_arialbl" class="cke_voice_label">{voiceLabel}</span>' : ' ' ) +
								'<div class="cke_inner">' +
									'<div id="{spaceId}" class="cke_{space}" role="presentation">{content}</div>' +
								'</div>' +
							'</div>' +
						'</div>' ),

					space = CKEDITOR.dom.element.createFromHtml( containerTpl.output( {
						id: editor.id,
						name: editor.name,
						langDir: editor.lang.dir,
						langCode: editor.langCode,
						space: 'top',
						spaceId: editor.ui.spaceId( 'top' ),
						content: innerHtml,
					} ) );

				if ( editor.elementMode === CKEDITOR.ELEMENT_MODE_REPLACE ) {
					space.getDocument().appendStyleText( pluginCss );
				}

				if ( positionY === 'bottom' ) {
					space.insertAfter( editor.element );
				} else {
					space.insertBefore( editor.element );
				}

				editor.focusManager.add( space, 1 );

				if ( editor.focusManager.hasFocus ) {
					space.show();
				}

				editor.on( 'focus', function() {
					space.show();
				} );

				editor.on( 'blur', function() {
					space.hide();
				} );

				editor.on( 'destroy', function() {
					space.remove();
				} );
			}
		}
	}

} )();

/**
 * Vertical position of static space.
 * Allowed values:
 * * 'top'
 * * 'bottom'
 *
 * Example:
 *
 *				config.staticSpacePositionY = 'bottom';
 *
 * @cfg {String} [staticSpacePositionY='top']
 * @member CKEDITOR.config
 */
/**
 * Horizontal position of static space.
 * Allowed values:
 * * 'left'
 * * 'right'
 *
 * Example:
 *
 *				config.staticSpacePositionX = 'right';
 *
 * @cfg {String} [staticSpacePositionX='left']
 * @member CKEDITOR.config
 */
/**
 * Plugin load priority.
 * For CKEDITOR.replace mode (iframe or div) the value should be <= 9.
 *
 * Example:
 *
 *				config.staticSpacePriority = 9;
 *
 * Defaults to: 19
 *
 * @cfg {String} [staticSpacePositionX='left']
 * @member CKEDITOR.config
 */
