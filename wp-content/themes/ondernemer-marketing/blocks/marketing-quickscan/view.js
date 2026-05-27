/**
 * OndernemerMarketing , Marketing Quickscan.
 */
( function () {
	'use strict';

	function bandFor( score ) {
		if ( score >= 10 ) return {
			title: '&#x1F680; Pro , jij hebt de basis goed staan.',
			advice: 'Volgende stap: schalen via performance marketing. Bekijk onze Groei Machine of Volledig Uitbesteed tier voor de volgende stap.',
		};
		if ( score >= 6 ) return {
			title: '&#x1F4C8; Gevorderd , er liggen kansen.',
			advice: 'Je hebt de basis grotendeels staan. Met een Kickstart Funnel of het Lead Magnet Landingspagina pakket sluit je de gaten in 2-4 weken.',
		};
		return {
			title: '&#x1F331; Beginner , veel groeipotentieel.',
			advice: 'Je marketing staat nog in de kinderschoenen. Begin met het Social Media Funnel Pack of de Klare Start Pack om snel een fundament te leggen.',
		};
	}

	function init() {
		document.querySelectorAll( '.ondm-quickscan' ).forEach( function ( root ) {
			const form   = root.querySelector( '[data-ondm-form]' );
			const result = root.querySelector( '[data-ondm-result]' );
			if ( ! form || ! result ) return;
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				let score = 0;
				new FormData( form ).forEach( function ( v ) { score += parseInt( v, 10 ) || 0; } );
				const band = bandFor( score );
				root.querySelector( '[data-ondm-out="score"]'  ).textContent = score;
				root.querySelector( '[data-ondm-out="band"]'   ).innerHTML   = band.title;
				root.querySelector( '[data-ondm-out="advice"]' ).textContent = band.advice;
				result.hidden = false;
				result.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', init );
	else init();
} )();
