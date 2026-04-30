( function () {
	'use strict';

	/* ── Referencias DOM ── */
	var wrap         = document.getElementById( 'wlg-country-wrap' );
	var countryBtn   = document.getElementById( 'wlg-country-btn' );
	var countryPanel = document.getElementById( 'wlg-country-panel' );
	var countrySearch= document.getElementById( 'wlg-country-search' );
	var countryList  = document.getElementById( 'wlg-country-list' );
	var flagEl       = document.getElementById( 'wlg-c-flag' );
	var dialEl       = document.getElementById( 'wlg-c-dial' );
	var numberInput  = document.getElementById( 'wlg-number' );
	var messageInput = document.getElementById( 'wlg-message' );
	var btn          = document.getElementById( 'wlg-btn' );
	var resultBox    = document.getElementById( 'wlg-result' );
	var linkOutput   = document.getElementById( 'wlg-link-output' );
	var copyBtn      = document.getElementById( 'wlg-copy-btn' );
	var openLink     = document.getElementById( 'wlg-open-link' );
	var errorBox     = document.getElementById( 'wlg-error' );
	var previewText  = document.getElementById( 'wlg-preview-text' );

	if ( ! countryBtn ) return;

	var currentDial    = '1';
	var defaultPreview = 'Aquí aparecerá tu texto personalizado';

	/* ── Inicializar país por defecto desde PHP ── */
	var defaultDial = ( typeof wlgData !== 'undefined' && wlgData.defaultCountry )
		? wlgData.defaultCountry
		: '1';

	var defaultItem = countryList
		? countryList.querySelector( '[data-dial="' + defaultDial + '"]' )
		: null;

	if ( defaultItem ) {
		selectCountry( defaultItem );
	}

	/* ══════════════════════════════
	   SELECTOR DE PAÍS
	   ══════════════════════════════ */

	/* Abrir / cerrar */
	countryBtn.addEventListener( 'click', function () {
		var isOpen = ! countryPanel.hidden;
		countryPanel.hidden = isOpen;
		countryBtn.setAttribute( 'aria-expanded', String( ! isOpen ) );
		if ( ! isOpen ) {
			countrySearch.value = '';
			filterCountries( '' );
			countrySearch.focus();
		}
	} );

	/* Cerrar al hacer clic fuera */
	document.addEventListener( 'click', function ( e ) {
		if ( wrap && ! wrap.contains( e.target ) ) {
			countryPanel.hidden = true;
			countryBtn.setAttribute( 'aria-expanded', 'false' );
		}
	} );

	/* Cerrar con Escape */
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && ! countryPanel.hidden ) {
			countryPanel.hidden = true;
			countryBtn.setAttribute( 'aria-expanded', 'false' );
			countryBtn.focus();
		}
	} );

	/* Filtro de búsqueda */
	if ( countrySearch ) {
		countrySearch.addEventListener( 'input', function () {
			filterCountries( this.value );
		} );
	}

	function filterCountries( query ) {
		var q     = query.toLowerCase().trim();
		var items = countryList ? countryList.querySelectorAll( '.wlg-country-item' ) : [];
		var found = 0;
		items.forEach( function ( item ) {
			var name = item.dataset.name || '';
			var dial = item.dataset.dial || '';
			var match = ! q || name.includes( q ) || dial.includes( q );
			item.classList.toggle( 'wlg-hidden', ! match );
			if ( match ) found++;
		} );
	}

	/* Seleccionar un país de la lista */
	if ( countryList ) {
		countryList.addEventListener( 'click', function ( e ) {
			var item = e.target.closest( '.wlg-country-item' );
			if ( ! item ) return;
			selectCountry( item );
			countryPanel.hidden = true;
			countryBtn.setAttribute( 'aria-expanded', 'false' );
			numberInput.focus();
		} );
	}

	function selectCountry( item ) {
		var flag = item.querySelector( '.wlg-ci-flag' );
		var dial = item.dataset.dial;

		currentDial           = dial;
		flagEl.textContent    = flag ? flag.textContent : '🌎';
		dialEl.textContent    = '+' + dial;

		/* Marcar seleccionado */
		var all = countryList ? countryList.querySelectorAll( '.wlg-country-item' ) : [];
		all.forEach( function ( i ) { i.classList.remove( 'wlg-selected' ); } );
		item.classList.add( 'wlg-selected' );
	}

	/* ══════════════════════════════
	   PREVIEW EN VIVO
	   ══════════════════════════════ */

	if ( messageInput && previewText ) {
		messageInput.addEventListener( 'input', function () {
			var val = this.value.trim();
			previewText.textContent = val.length ? val : defaultPreview;
		} );
	}

	if ( numberInput ) {
		numberInput.addEventListener( 'input', hideError );
	}

	/* ══════════════════════════════
	   GENERAR ENLACE
	   ══════════════════════════════ */

	btn.addEventListener( 'click', function () {
		hideError();
		resultBox.style.display = 'none';

		var number  = numberInput  ? numberInput.value.trim()  : '';
		var message = messageInput ? messageInput.value.trim() : '';

		if ( ! number ) {
			showError( 'Por favor ingresa tu número de teléfono.' );
			if ( numberInput ) numberInput.focus();
			return;
		}

		/* Solo dígitos */
		var cleanNumber = number.replace( /\D/g, '' );

		if ( cleanNumber.length < 6 ) {
			showError( 'El número parece demasiado corto.' );
			if ( numberInput ) numberInput.focus();
			return;
		}

		var fullNumber = currentDial + cleanNumber;
		var url = 'https://wa.me/' + fullNumber;
		if ( message ) {
			url += '?text=' + encodeURIComponent( message );
		}

		if ( linkOutput ) linkOutput.value = url;
		if ( openLink )   openLink.href    = url;
		resultBox.style.display = 'block';
		if ( linkOutput ) linkOutput.select();
	} );

	/* ══════════════════════════════
	   COPIAR AL PORTAPAPELES
	   ══════════════════════════════ */

	if ( copyBtn ) {
		copyBtn.addEventListener( 'click', function () {
			var text = linkOutput ? linkOutput.value : '';
			if ( ! text ) return;

			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( text ).then( flashCopied );
			} else {
				if ( linkOutput ) linkOutput.select();
				document.execCommand( 'copy' );
				flashCopied();
			}
		} );
	}

	function flashCopied() {
		copyBtn.classList.add( 'wlg-copied' );
		setTimeout( function () { copyBtn.classList.remove( 'wlg-copied' ); }, 1800 );
	}

	/* ── Helpers ── */
	function showError( msg ) {
		if ( errorBox ) {
			errorBox.textContent   = msg;
			errorBox.style.display = 'block';
		}
	}

	function hideError() {
		if ( errorBox ) errorBox.style.display = 'none';
	}

} )();
