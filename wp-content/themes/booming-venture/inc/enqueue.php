<?php
/**
 * Asset enqueue, fonts, theme CSS/JS, GTM, DataSpeak.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/* Minified copy of a theme asset when the release ships one
 * (tools/build-min.sh). Theme assets ignore SCRIPT_DEBUG on purpose. */
function bv_asset_rel( string $rel ): string {
	$min = preg_replace( '/\.(css|js)$/', '.min.$1', $rel );
	return ( $min && file_exists( BV_THEME_DIR . '/' . $min ) ) ? $min : $rel;
}

/* Front-end. */
add_action( 'wp_enqueue_scripts', function () {
	$css = bv_asset_rel( 'assets/css/theme.css' );
	$js  = bv_asset_rel( 'assets/js/theme.js' );
	wp_enqueue_style( 'booming-venture', BV_THEME_URI . '/' . $css, [], bv_asset_ver( $css ) );
	wp_enqueue_script(
		'booming-venture',
		BV_THEME_URI . '/' . $js,
		[],
		bv_asset_ver( $js ),
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
}, 20 );

/* Editor stylesheet. */
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_style(
		'booming-venture-editor',
		BV_THEME_URI . '/assets/css/editor.css',
		[],
		bv_asset_ver( 'assets/css/editor.css' )
	);
} );

/* Preload critical webfonts to reduce LCP. */
add_action( 'wp_head', function () {
	$fonts = [
		'/assets/fonts/Inter-Variable.woff2',
		'/assets/fonts/SpaceGrotesk-Variable.woff2',
	];
	foreach ( $fonts as $font ) {
		if ( file_exists( BV_THEME_DIR . $font ) ) {
			printf(
				'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
				esc_url( BV_THEME_URI . $font )
			);
		}
	}
}, 2 );

/* Google Tag Manager, head. */
add_action( 'wp_head', function () {
	$gtm_id = get_option( 'bv_gtm_id', 'GTM-K9532WK5' );
	if ( ! $gtm_id ) return;
	?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
<!-- End Google Tag Manager -->
	<?php
}, 1 );

/* GTM noscript, body open. */
add_action( 'wp_body_open', function () {
	$gtm_id = get_option( 'bv_gtm_id', 'GTM-K9532WK5' );
	if ( ! $gtm_id ) return;
	?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
	<?php
} );

/* DataSpeak chat.
 * Desktop: loads after the first real interaction, so it never competes
 * with the first paint. Mobile: one small chat button that loads the
 * widget on tap, so no greeting pop-up covers content or form fields. */
add_action( 'wp_footer', function () {
	$interface_id = get_option( 'bv_dataspeak_id', '6863892dbcf4fea86a49e9f8' );
	if ( ! $interface_id ) return;
	?>
<script>
window.dataspeakChatConfiguration = { interfaceId: <?php echo wp_json_encode( $interface_id ); ?> };
(function () {
  var loaded = false;
  function hideBrand() {
    var iv = setInterval(function () {
      var a = document.querySelector('a[href="https://www.dataspeak.nl"]');
      if (a) { a.style.display = 'none'; clearInterval(iv); }
    }, 500);
    setTimeout(function () { clearInterval(iv); }, 10000);
  }
  function load(done) {
    if (loaded) return;
    loaded = true;
    var s = document.createElement('script');
    s.src = 'https://chat.dataspeak.nl/v1/client.js';
    s.async = true;
    s.onload = function () { hideBrand(); if (done) done(); };
    document.head.appendChild(s);
  }
  if (window.matchMedia('(max-width: 767px)').matches) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'bv-chat-launch';
    b.setAttribute('aria-label', 'Open chat');
    b.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>';
    b.addEventListener('click', function () {
      b.classList.add('is-loading');
      load(function () { setTimeout(function () { b.remove(); }, 1500); });
    });
    document.body.appendChild(b);
    return;
  }
  var events = ['pointermove', 'pointerdown', 'scroll', 'keydown', 'touchstart'];
  function go() {
    events.forEach(function (e) { window.removeEventListener(e, go); });
    setTimeout(function () {
      if ('requestIdleCallback' in window) { requestIdleCallback(function () { load(); }, { timeout: 3000 }); }
      else { load(); }
    }, 1500);
  }
  events.forEach(function (e) { window.addEventListener(e, go, { passive: true }); });
})();
</script>
	<?php
}, 30 );

/* Preconnect only to what the first view needs. The chat widget and
 * Brevo load later, so a preconnect for them would be wasted. */
add_filter( 'wp_resource_hints', function ( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = [ 'href' => 'https://www.googletagmanager.com' ];
	}
	return $hints;
}, 10, 2 );
