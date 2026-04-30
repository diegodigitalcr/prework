( function () {
	'use strict';

	var phoneInput   = document.getElementById( 'wlg-phone' );
	var messageInput = document.getElementById( 'wlg-message' );
	var btn          = document.getElementById( 'wlg-btn' );
	var resultBox    = document.getElementById( 'wlg-result' );
	var linkOutput   = document.getElementById( 'wlg-link-output' );
	var copyBtn      = document.getElementById( 'wlg-copy-btn' );
	var openLink     = document.getElementById( 'wlg-open-link' );
	var errorBox     = document.getElementById( 'wlg-error' );
	var previewText  = document.getElementById( 'wlg-preview-text' );

	if ( ! phoneInput ) return; // shortcode not present on this page

	/* Live preview while the user types the message */
	var defaultPreview = 'Aquí aparecerá tu texto personalizado';

	messageInput.addEventListener( 'input', function () {
		var val = messageInput.value.trim();
		previewText.textContent = val.length ? val : defaultPreview;
	} );

	/* Phone input: strip non-numeric chars except leading '+' for preview only */
	phoneInput.addEventListener( 'input', function () {
		hideError();
	} );

	/* Generate button */
	btn.addEventListener( 'click', function () {
		hideError();
		resultBox.style.display = 'none';

		var rawPhone = phoneInput.value.trim();
		var message  = messageInput.value.trim();

		/* Validation */
		if ( ! rawPhone ) {
			showError( 'Por favor ingresa tu número de teléfono.' );
			phoneInput.focus();
			return;
		}

		/* Sanitize phone: keep only digits and leading '+' */
		var phone = rawPhone.replace( /[^\d+]/g, '' );

		if ( phone.length < 7 ) {
			showError( 'El número de teléfono parece demasiado corto. Incluye el código de país.' );
			phoneInput.focus();
			return;
		}

		/* Build wa.me URL */
		var url = 'https://wa.me/' + encodeURIComponent( phone );
		if ( message.length ) {
			url += '?text=' + encodeURIComponent( message );
		}

		/* Show result */
		linkOutput.value  = url;
		openLink.href     = url;
		resultBox.style.display = 'block';
		linkOutput.select();
	} );

	/* Copy button */
	copyBtn.addEventListener( 'click', function () {
		if ( ! linkOutput.value ) return;

		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( linkOutput.value ).then( function () {
				flashCopied();
			} );
		} else {
			linkOutput.select();
			document.execCommand( 'copy' );
			flashCopied();
		}
	} );

	function flashCopied() {
		copyBtn.classList.add( 'copied' );
		setTimeout( function () {
			copyBtn.classList.remove( 'copied' );
		}, 1800 );
	}

	function showError( msg ) {
		errorBox.textContent    = msg;
		errorBox.style.display  = 'block';
	}

	function hideError() {
		errorBox.style.display = 'none';
	}
} )();
