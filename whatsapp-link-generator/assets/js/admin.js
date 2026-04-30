( function ( $ ) {
	'use strict';

	$( function () {

		/* Inicializar todos los color pickers */
		$( '.wlg-color-picker' ).wpColorPicker( {
			change: function ( event, ui ) {
				updatePreview();
			},
			clear: function () {
				updatePreview();
			},
		} );

		/* Actualizar vista previa del botón en la barra lateral */
		function updatePreview() {
			var primary = $( '[name="wlg_settings[color_primary]"]' ).val();
			var txtCol  = $( '[name="wlg_settings[color_btn_text]"]' ).val();
			var btnTxt  = $( '[name="wlg_settings[button_text]"]' ).val();

			var $preview = $( '#wlg-preview-btn' );
			if ( primary ) $preview.css( 'background', primary );
			if ( txtCol  ) $preview.css( 'color', txtCol );
			if ( btnTxt  ) $preview.text( btnTxt );
		}

		/* Actualizar preview también cuando se escribe el texto del botón */
		$( '[name="wlg_settings[button_text]"]' ).on( 'input', updatePreview );

	} );

} )( jQuery );
