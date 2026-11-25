<?php
/**
 * Asset enqueue, fonts, theme CSS/JS, GTM.
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

/* Preconnect only to what the first view needs. Brevo loads later, so a
 * preconnect for it would be wasted. */
add_filter( 'wp_resource_hints', function ( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = [ 'href' => 'https://www.googletagmanager.com' ];
	}
	return $hints;
}, 10, 2 );
