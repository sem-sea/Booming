<?php
/**
 * Asset enqueue, fonts, theme CSS/JS, GTM, DataSpeak.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/* Front-end. */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'booming-venture',
		BV_THEME_URI . '/assets/css/theme.css',
		[],
		bv_asset_ver( 'assets/css/theme.css' )
	);

	wp_enqueue_script(
		'booming-venture',
		BV_THEME_URI . '/assets/js/theme.js',
		[],
		bv_asset_ver( 'assets/js/theme.js' ),
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
		'/assets/fonts/Inter-Regular.woff2',
		'/assets/fonts/Inter-SemiBold.woff2',
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

/* DataSpeak chat, footer, async, after consent if cookie present. */
add_action( 'wp_footer', function () {
	$interface_id = get_option( 'bv_dataspeak_id', '6863892dbcf4fea86a49e9f8' );
	if ( ! $interface_id ) return;
	?>
<script>
window.dataspeakChatConfiguration = { interfaceId: <?php echo wp_json_encode( $interface_id ); ?> };
(function(){
  var loadDataSpeak = function () {
    if (window.__bvDataSpeakLoaded) return;
    window.__bvDataSpeakLoaded = true;
    var s = document.createElement('script');
    s.src = 'https://chat.dataspeak.nl/v1/client.js';
    s.async = true;
    s.onload = function () {
      var iv = setInterval(function () {
        var a = document.querySelector('a[href="https://www.dataspeak.nl"]');
        if (a) { a.style.display = 'none'; clearInterval(iv); }
      }, 500);
      setTimeout(function () { clearInterval(iv); }, 10000);
    };
    document.head.appendChild(s);
  };
  if ('requestIdleCallback' in window) {
    requestIdleCallback(loadDataSpeak, { timeout: 4000 });
  } else {
    setTimeout(loadDataSpeak, 2500);
  }
})();
</script>
	<?php
}, 30 );

/* DNS-prefetch / preconnect for known third parties. */
add_filter( 'wp_resource_hints', function ( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = [ 'href' => 'https://chat.dataspeak.nl', 'crossorigin' ];
		$hints[] = [ 'href' => 'https://www.googletagmanager.com' ];
		$hints[] = [ 'href' => 'https://api.brevo.com' ];
		$hints[] = [ 'href' => 'https://images.unsplash.com' ];
	}
	return $hints;
}, 10, 2 );
