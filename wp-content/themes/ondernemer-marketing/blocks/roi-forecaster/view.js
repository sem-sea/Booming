/**
 * OndernemerMarketing , ROI Forecaster.
 */
( function () {
	'use strict';

	const nl  = new Intl.NumberFormat( 'nl-NL', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 } );
	const num = new Intl.NumberFormat( 'nl-NL', { maximumFractionDigits: 0 } );
	const fix = new Intl.NumberFormat( 'nl-NL', { maximumFractionDigits: 1, minimumFractionDigits: 1 } );

	function compute( root ) {
		const budget = parseFloat( root.querySelector( '[data-ondm="budget"]' ).value ) || 0;
		const cac    = parseFloat( root.querySelector( '[data-ondm="cac"]' ).value )    || 1;
		const ltv    = parseFloat( root.querySelector( '[data-ondm="ltv"]' ).value )    || 0;
		const months = parseInt(   root.querySelector( '[data-ondm="months"]' ).value, 10 ) || 1;

		const customersMonth = budget / cac;
		const customersTotal = customersMonth * months;
		const revenueTotal   = customersTotal * ltv;
		const totalSpend     = budget * months;
		const roi            = totalSpend > 0 ? ( ( revenueTotal - totalSpend ) / totalSpend ) * 100 : 0;
		const paybackMonths  = customersMonth > 0 ? ( budget / ( customersMonth * ltv ) ) * 12 : 0;
		const ltvCac         = cac > 0 ? ltv / cac : 0;

		root.querySelector( '[data-ondm-out="customers-month"]' ).textContent = num.format( customersMonth );
		root.querySelector( '[data-ondm-out="customers-total"]' ).textContent = num.format( customersTotal );
		root.querySelector( '[data-ondm-out="revenue-total"]'  ).textContent  = nl.format( revenueTotal );
		root.querySelector( '[data-ondm-out="roi"]'            ).textContent  = num.format( roi ) + '%';
		root.querySelector( '[data-ondm-out="payback"]'        ).textContent  = fix.format( paybackMonths ) + ' mnd';

		const band = root.querySelector( '[data-ondm-out="health-band"]' );
		if ( ltvCac >= 3 ) {
			band.innerHTML = '<p class="ondm-tool__whatif-label" style="color:#16a34a">&#10003; Gezond profiel (LTV/CAC = ' + fix.format( ltvCac ) + ').</p><p class="ondm-tool__whatif-value">Veilig op te schalen.</p>';
		} else if ( ltvCac >= 1.5 ) {
			band.innerHTML = '<p class="ondm-tool__whatif-label" style="color:#b45309">&#9888; Marginaal (LTV/CAC = ' + fix.format( ltvCac ) + ').</p><p class="ondm-tool__whatif-value">Optimaliseer eerst je conversie voordat je schaalt.</p>';
		} else {
			band.innerHTML = '<p class="ondm-tool__whatif-label" style="color:#b91c1c">&#10005; Ongezond (LTV/CAC = ' + fix.format( ltvCac ) + ').</p><p class="ondm-tool__whatif-value">Verlaag je CAC of verhoog je LTV voordat je adverteert.</p>';
		}
	}

	function init() {
		document.querySelectorAll( '.ondm-roi' ).forEach( function ( root ) {
			compute( root );
			root.querySelectorAll( 'input[data-ondm]' ).forEach( function ( input ) {
				input.addEventListener( 'input', function () { compute( root ); } );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', init );
	else init();
} )();
