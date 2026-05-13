/* CiteLeap admin progression layer.
 *
 * Why this exists: WP admin-post handlers run the LLM call
 * synchronously and only redirect back when finished. For long
 * operations (10 to 60 seconds) the page looks frozen and the
 * operator does not know what is happening.
 *
 * What this does: when a CiteLeap form submits, show a centered
 * overlay with the action title + a stepwise narrative + a spinner.
 * The overlay is dismissed automatically once the page reloads.
 * Pure vanilla JS, no jQuery, no deps. */
( function () {
	'use strict';

	var STEP_NARRATIVES = {
		'citeleap_generate_ideas': [
			'Reading your site context (site name, audience, categories)…',
			'Sending the idea-generation prompt to your reasoning model…',
			'Waiting for the model to draft 10 unique ideas…',
			'Parsing the JSON response and deduping against existing slugs…',
			'Persisting new ideas to the queue…',
		],
		'citeleap_write_idea': [
			'Loading your master writing prompt and substituting variables…',
			'Sending the prompt to your writing model…',
			'Waiting for a 1,200 to 1,600 word draft…',
			'Parsing the JSON object (title, slug, meta, body)…',
			'Inserting as a WordPress draft post, writing SEO meta, assigning category…',
		],
		'citeleap_run_refresh': [
			'Loading the post content and master prompt…',
			'Sending the refresh-mode prompt to your writing model…',
			'Waiting for the rewritten body…',
			'Either overwriting the live post OR parking as pending review (depends on Refresh mode)…',
		],
		'citeleap_run_tick': [
			'Acquiring the scheduler lock (5-min transient)…',
			'Running the refresh tick (if auto-refresh on)…',
			'Running the content tick: pinned > priority > FIFO…',
			'Drafting + scheduling the picked item…',
		],
		'citeleap_add_topics':       [ 'Splitting lines and slugifying each topic…', 'Deduping against existing slugs + queue…', 'Persisting new queue entries…' ],
		'citeleap_bulk_refresh':     [ 'Resolving each line to a post ID (numeric / slug / URL)…', 'Deduping against the refresh queue…', 'Persisting refresh queue entries…' ],
		'citeleap_schedule_drafted': [ 'Updating WP post_date_gmt to your picked datetime…', 'Moving the WP post to status=future…', 'Marking the queue row scheduled…' ],
		'citeleap_reschedule':       [ 'Updating WP post_date_gmt…' ],
		'citeleap_unschedule':       [ 'Moving the WP post back to status=draft…', 'Marking the queue row drafted…' ],
		'citeleap_publish_now':      [ 'Updating WP post_date_gmt to now…', 'Moving the WP post to status=publish…' ],
		'citeleap_approve_refresh':  [ 'Reading the pending-refresh content from post meta…', 'Overwriting the live post title + content + excerpt…', 'Clearing pending meta, incrementing refresh count…' ],
		'citeleap_reject_refresh':   [ 'Deleting the pending-refresh meta…' ],
		'citeleap_test_key_claude':  [ 'Sending a one-token "OK" request to api.anthropic.com…' ],
		'citeleap_test_key_openai':  [ 'Sending a one-token "OK" request to api.openai.com…' ],
		'citeleap_test_key_gemini':  [ 'Sending a one-token "OK" request to generativelanguage.googleapis.com…' ],
		'citeleap_pause':            [ 'Setting paused flag on the row…' ],
		'citeleap_resume':           [ 'Clearing paused flag…' ],
		'citeleap_retry':            [ 'Resetting row status back to queued…' ],
		'citeleap_pin_datetime':     [ 'Updating publish_at override on the row…' ],
		'citeleap_remove_idea':      [ 'Removing the queue row…' ],
	};

	var TITLES = {
		'citeleap_generate_ideas':   'Generating ideas',
		'citeleap_write_idea':       'Writing draft',
		'citeleap_run_refresh':      'Refreshing post',
		'citeleap_run_tick':         'Running scheduler tick',
		'citeleap_add_topics':       'Queuing manual topics',
		'citeleap_bulk_refresh':     'Queuing refresh items',
		'citeleap_schedule_drafted': 'Scheduling draft',
		'citeleap_reschedule':       'Rescheduling',
		'citeleap_unschedule':       'Unscheduling',
		'citeleap_publish_now':      'Publishing now',
		'citeleap_approve_refresh':  'Approving refresh',
		'citeleap_reject_refresh':   'Rejecting refresh',
		'citeleap_pause':            'Pausing',
		'citeleap_resume':           'Resuming',
		'citeleap_retry':            'Retrying',
		'citeleap_pin_datetime':     'Pinning datetime',
		'citeleap_remove_idea':      'Removing',
	};

	function ensureOverlay() {
		if ( document.getElementById( 'citeleap-overlay' ) ) return document.getElementById( 'citeleap-overlay' );
		var ov = document.createElement( 'div' );
		ov.id = 'citeleap-overlay';
		ov.innerHTML = ''
			+ '<div class="citeleap-overlay-card">'
			+   '<div class="citeleap-spinner"></div>'
			+   '<h3 id="citeleap-overlay-title">Working...</h3>'
			+   '<ol id="citeleap-overlay-steps"></ol>'
			+   '<p class="citeleap-overlay-hint">This is a synchronous request. The page will reload automatically when it finishes.</p>'
			+ '</div>';
		document.body.appendChild( ov );
		return ov;
	}

	function showOverlay( action ) {
		var ov = ensureOverlay();
		var title = TITLES[ action ] || ( 'Working: ' + action );
		var steps = STEP_NARRATIVES[ action ] || [ 'Processing request…' ];
		document.getElementById( 'citeleap-overlay-title' ).textContent = title;
		var ol = document.getElementById( 'citeleap-overlay-steps' );
		ol.innerHTML = '';
		steps.forEach( function ( s, i ) {
			var li = document.createElement( 'li' );
			li.textContent = s;
			li.style.opacity = i === 0 ? '1' : '0.35';
			ol.appendChild( li );
		} );
		ov.classList.add( 'is-visible' );
		/* Walk through the narrative every 4s so the operator sees motion. */
		var idx = 1;
		var iv = setInterval( function () {
			var li = ol.children[ idx ];
			if ( ! li ) { clearInterval( iv ); return; }
			li.style.opacity = '1';
			idx++;
		}, 4000 );
	}

	function isCiteLeapForm( form ) {
		var action = form.querySelector( 'input[name="action"]' );
		return action && action.value && /^citeleap_/.test( action.value );
	}

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target;
		if ( ! ( form instanceof HTMLFormElement ) ) return;
		if ( ! isCiteLeapForm( form ) ) return;
		/* Skip if a confirm() rejected the submit. */
		setTimeout( function () {
			var action = form.querySelector( 'input[name="action"]' );
			if ( action ) showOverlay( action.value );
		}, 0 );
	}, true );
} )();
