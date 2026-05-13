/* ============================================================
 * Booming Venture , Brevo Form Fix
 * auto-name.js , derive a Name from the Email input as the user
 * types, and auto-fill any sibling Name field that is empty.
 *
 * Works with: Contact Form 7, Fluent Forms, Gravity Forms,
 * WPForms, Brevo native subscribe form, plain HTML <form>.
 *
 * Behaviour:
 *   1. Watches every email input on the page (type="email" OR
 *      name/id/placeholder containing "email").
 *   2. On input, derives a name from the local-part of the email
 *      ("john.doe@x.com" -> "John Doe", "ana_p_22@y.nl" -> "Ana P").
 *   3. Looks for a name field in the SAME <form>: type="text" with
 *      name/id/placeholder containing "name" / "naam" / "nombre".
 *   4. If that field is empty (or only contains a value we previously
 *      auto-filled), updates it. If the user has typed their own
 *      name, leaves it alone.
 *
 * Implementation notes:
 *   - No external dependencies, no jQuery required.
 *   - Uses MutationObserver to handle forms injected after page load
 *     (Brevo subscribe widgets, CF7 AJAX rebuild, etc).
 *   - Each auto-filled name field is tagged data-bvbf-filled="1" so
 *     the script can tell what it owns.
 * ============================================================ */
( function () {
	'use strict';

	var NAME_HINT_RE  = /(^|[\b\-_\s])(name|naam|nombre|nom|fullname|firstname|first.?name|your.?name|voornaam)([\b\-_\s]|$)/i;
	var EMAIL_HINT_RE = /(^|[\b\-_\s])(email|e.?mail|e_mail)([\b\-_\s]|$)/i;

	function deriveName( email ) {
		if ( ! email || typeof email !== 'string' ) return '';
		var at = email.indexOf( '@' );
		if ( at <= 0 ) return '';
		var local = email.substring( 0, at );
		local = local.replace( /[+\.\-_0-9]+/g, ' ' );
		local = local.replace( /\s+/g, ' ' ).trim();
		if ( ! local ) return '';
		return local.split( ' ' ).map( function ( part ) {
			return part.charAt( 0 ).toUpperCase() + part.substring( 1 ).toLowerCase();
		} ).join( ' ' );
	}

	function isEmailField( el ) {
		if ( ! el || el.tagName !== 'INPUT' ) return false;
		if ( el.type === 'email' ) return true;
		var hints = [ el.name || '', el.id || '', el.placeholder || '', el.getAttribute( 'autocomplete' ) || '' ].join( ' ' );
		return EMAIL_HINT_RE.test( hints );
	}

	function isNameField( el ) {
		if ( ! el || el.tagName !== 'INPUT' ) return false;
		if ( el.type !== 'text' && el.type !== '' && el.type !== 'search' ) return false;
		var hints = [ el.name || '', el.id || '', el.placeholder || '', el.getAttribute( 'autocomplete' ) || '' ].join( ' ' );
		if ( EMAIL_HINT_RE.test( hints ) ) return false;
		return NAME_HINT_RE.test( hints );
	}

	function findNameFieldInScope( emailField ) {
		var scope = emailField.form || emailField.closest( 'form, .wpcf7-form, .fluentform, .gform_wrapper, .wpforms-form, .sib_signup_form' ) || document;
		var inputs = scope.querySelectorAll( 'input' );
		for ( var i = 0; i < inputs.length; i++ ) {
			if ( isNameField( inputs[ i ] ) ) return inputs[ i ];
		}
		return null;
	}

	function maybeFill( emailField ) {
		var nameField = findNameFieldInScope( emailField );
		if ( ! nameField ) return;

		/* Respect a real human entry. We only overwrite when the field
		 * is empty OR contains a value we previously auto-filled. */
		var ours = nameField.getAttribute( 'data-bvbf-filled' ) === '1';
		if ( nameField.value && ! ours ) return;

		var derived = deriveName( emailField.value );
		if ( ! derived ) return;
		if ( nameField.value === derived ) return;

		nameField.value = derived;
		nameField.setAttribute( 'data-bvbf-filled', '1' );

		/* Dispatch input + change events so frameworks (Fluent Forms,
		 * Gravity, WPForms) pick up the new value for their own state. */
		try {
			nameField.dispatchEvent( new Event( 'input',  { bubbles: true } ) );
			nameField.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} catch ( e ) {
			/* IE11 fallback , unreachable on supported browsers. */
		}
	}

	function attach( emailField ) {
		if ( emailField.getAttribute( 'data-bvbf-bound' ) === '1' ) return;
		emailField.setAttribute( 'data-bvbf-bound', '1' );
		emailField.addEventListener( 'input',  function () { maybeFill( emailField ); } );
		emailField.addEventListener( 'change', function () { maybeFill( emailField ); } );
		emailField.addEventListener( 'blur',   function () { maybeFill( emailField ); } );
	}

	function scan( root ) {
		root = root || document;
		var inputs = root.querySelectorAll( 'input' );
		for ( var i = 0; i < inputs.length; i++ ) {
			if ( isEmailField( inputs[ i ] ) ) attach( inputs[ i ] );
		}
	}

	function init() {
		scan( document );

		/* Re-scan when new forms appear in the DOM (Brevo widget,
		 * CF7 AJAX rerender, modal lead-magnet, etc.). */
		if ( typeof MutationObserver === 'function' ) {
			var mo = new MutationObserver( function ( muts ) {
				for ( var i = 0; i < muts.length; i++ ) {
					var added = muts[ i ].addedNodes || [];
					for ( var j = 0; j < added.length; j++ ) {
						var n = added[ j ];
						if ( n && n.nodeType === 1 ) scan( n );
					}
				}
			} );
			mo.observe( document.body || document.documentElement, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
