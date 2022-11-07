const indicator = document.querySelector( '#edd-fix-order-numbers-progress-indicator' );
const progress = document.querySelector( '#edd-fix-order-numbers-progress' );
const button = document.querySelector( '#edd-fix-order-numbers-process' );
const i18n = window?.edd_fix_order_numbers || {}

let processing = false;
let controller;
let signal;

const setController = () => {

	if ( ! window.AbortController ) {
		return;
	}

	controller = new AbortController();
	signal = controller.signal;

}

const fixOrders = () => {

	const formData = new FormData();

	if ( processing ) {

		controller && controller.abort();
		setController();
		updateUI( i18n.process, '', '' );
		return;

	}

	formData.append( 'offset', 0 );
	formData.append( 'action', 'edd_fix_order_numbers' );
	formData.append( 'nonce', i18n.nonce );
	updateUI( i18n.cancel, 0, '0%' );
	fetchContent( formData );

}

const fetchContent = formData => {

	fetch( ajaxurl, {
		method: 'post',
		body: formData,
		signal,
	} ).then( response => {

		if ( ! response.ok ) {

			updateUI( i18n.process, '', i18n.error );
			throw Error( response.statusText );

		}

		return response.json();

	} ).then( data => {

		if ( ! data.success ) {

			updateUI( i18n.process, '', data.message || i18n.error );
			return;

		}

		if ( data.progress >= 100 ) {

			updateUI( i18n.process, 100, i18n.complete );
			return;

		}

		updateUI( i18n.cancel, data.progress, data.message )
		formData.set( 'offset', data.offset );
		fetchContent( formData );

	} ).catch( () => null );
}

const updateUI = ( label, value, message ) => {

	button.textContent = label;
	indicator.textContent = message;
	progress.hidden = message === '';
	progress.value = value;
	processing = value !== '' && value >= 0 && value < 100;

}

button && button.addEventListener( 'click', fixOrders );
setController();
