/* ============================================================
 * Booming Venture Blog Images , admin pool picker
 *
 * Opens wp.media in multi-select mode, lets the operator pick the
 * pool of images, and updates the hidden #bvimg-pool-ids field plus
 * the on-page thumbnail preview grid.
 * ============================================================ */
( function ( $ ) {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media ) return;

	var $pickBtn = $( '#bvimg-pick' );
	var $hidden  = $( '#bvimg-pool-ids' );
	var $preview = $( '#bvimg-pool-preview' );
	if ( ! $pickBtn.length ) return;

	var frame = null;

	function currentIds() {
		var raw = ( $hidden.val() || '' ).trim();
		if ( ! raw ) return [];
		return raw.split( ',' ).map( function ( s ) { return parseInt( s, 10 ); } ).filter( function ( n ) { return n > 0; } );
	}

	function setIds( ids ) {
		var unique = [];
		var seen   = {};
		ids.forEach( function ( id ) {
			if ( ! seen[ id ] && id > 0 ) { seen[ id ] = 1; unique.push( id ); }
		} );
		$hidden.val( unique.join( ',' ) );
	}

	function renderPreview( attachments ) {
		$preview.empty();
		attachments.forEach( function ( a ) {
			var url = ( a.sizes && a.sizes.thumbnail && a.sizes.thumbnail.url ) || a.url;
			var $thumb = $(
				'<div class="bvimg-pool-thumb" data-id="' + a.id + '" style="position:relative;border:1px solid #e2e8f0;border-radius:0.375rem;overflow:hidden;background:#f8fafc;">' +
					'<img src="' + url + '" alt="" style="display:block;width:100%;height:90px;object-fit:cover;">' +
					'<button type="button" class="bvimg-remove" aria-label="' + ( window.BVIMG && window.BVIMG.removeLabel ? window.BVIMG.removeLabel : 'Remove' ) + '" style="position:absolute;top:4px;right:4px;border:0;background:rgba(15,23,42,0.85);color:#fff;border-radius:50%;width:22px;height:22px;line-height:1;cursor:pointer;font-size:14px;padding:0;">&times;</button>' +
				'</div>'
			);
			$preview.append( $thumb );
		} );
	}

	$pickBtn.on( 'click', function ( e ) {
		e.preventDefault();
		if ( frame ) { frame.open(); return; }
		frame = wp.media( {
			title: window.BVIMG && window.BVIMG.pickTitle ? window.BVIMG.pickTitle : 'Pick images',
			multiple: 'add',
			library: { type: 'image' },
			button: { text: window.BVIMG && window.BVIMG.pickButton ? window.BVIMG.pickButton : 'Use these images' },
		} );

		frame.on( 'open', function () {
			var selection = frame.state().get( 'selection' );
			currentIds().forEach( function ( id ) {
				var att = wp.media.attachment( id );
				att.fetch();
				selection.add( att ? [ att ] : [] );
			} );
		} );

		frame.on( 'select', function () {
			var selection = frame.state().get( 'selection' ).toJSON();
			setIds( selection.map( function ( a ) { return a.id; } ) );
			renderPreview( selection );
		} );

		frame.open();
	} );

	/* Remove a single thumbnail from the pool (in the preview area). */
	$preview.on( 'click', '.bvimg-remove', function ( e ) {
		e.preventDefault();
		var $thumb = $( this ).closest( '.bvimg-pool-thumb' );
		var id     = parseInt( $thumb.data( 'id' ), 10 );
		$thumb.remove();
		setIds( currentIds().filter( function ( x ) { return x !== id; } ) );
	} );
} )( jQuery );
