/**
 * OndernemerMarketing , Funnel Calculator (vanilla, no transpile).
 * Re-computes on every input change. Currency in NL locale.
 */
( function () {
	'use strict';

	const nl = new Intl.NumberFormat( 'nl-NL', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 } );
	const num = new Intl.NumberFormat( 'nl-NL', { maximumFractionDigits: 0 } );

	function compute( root ) {
		const v  = parseFloat( root.querySelector( '[data-ondm="visitors"]' ).value )       || 0;
		const lr = parseFloat( root.querySelector( '[data-ondm="lead-rate"]' ).value )      || 0;
		const cr = parseFloat( root.querySelector( '[data-ondm="customer-rate"]' ).value )  || 0;
		const cv = parseFloat( root.querySelector( '[data-ondm="customer-value"]' ).value ) || 0;

		const leads = v * lr / 100;
		const customers = leads * cr / 100;
		const revenue = customers * cv;
		const revenueYear = revenue * 12;

		// What-if: double the lead-rate AND customer-rate.
		const customersDouble = ( v * ( lr * 2 ) / 100 ) * ( cr * 2 ) / 100;
		const revenueDouble = customersDouble * cv * 12;
		const extra = revenueDouble - revenueYear;

		root.querySelector( '[data-ondm-out="leads"]' ).textContent     = num.format( leads );
		root.querySelector( '[data-ondm-out="customers"]' ).textContent = num.format( customers );
		root.querySelector( '[data-ondm-out="revenue"]' ).textContent   = nl.format( revenue );
		root.querySelector( '[data-ondm-out="revenue-year"]' ).textContent = nl.format( revenueYear );
		root.querySelector( '[data-ondm-out="whatif"]' ).innerHTML       = '+ <strong>' + nl.format( extra ) + '</strong> extra omzet per jaar.';
	}

	function init() {
		document.querySelectorAll( '.ondm-funnel' ).forEach( function ( root ) {
			compute( root );
			root.querySelectorAll( 'input[data-ondm]' ).forEach( function ( input ) {
				input.addEventListener( 'input', function () { compute( root ); } );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', init );
	else init();
} )();
