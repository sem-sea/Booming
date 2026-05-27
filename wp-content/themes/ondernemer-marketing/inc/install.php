<?php
/**
 * OndernemerMarketing , install hook.
 *
 * On theme activation:
 *  1. Side-loads bundled images from assets/images/ into the Media
 *     Library as attachments (so they appear in admin > Media + can
 *     be edited via the block editor).
 *  2. Sets the site logo (custom_logo theme mod) to the bundled logo.
 *  3. Sets the site icon (favicon) to the bundled square icon.
 *  4. Stores attachment IDs in wp_options under 'ondm_media_map' so
 *     patterns + WXR + helpers can look up "logo" / "hero-home" /
 *     etc. and get the right URL whether served from the theme dir
 *     (fallback) or from the Media Library (post-activation).
 *
 * Idempotent: re-activating does NOT duplicate uploads. The map is
 * consulted first; only missing entries are sideloaded.
 *
 * @package OndernemerMarketing
 */

defined( 'ABSPATH' ) || exit;

class ONDM_Install {

	const MAP_OPT  = 'ondm_media_map';
	const FLAG_OPT = 'ondm_installed_version';

	/** filename , slug for the media-map. */
	public static function bundle(): array {
		return [
			'logo'              => 'logo.png',
			'logo-variant'      => 'logo-variant.png',
			'site-icon'         => 'site-icon.png',
			'hero-home'         => 'hero-home.png',
			'over-ons-hero'     => 'over-ons-hero.png',
			'cta-illustration'  => 'cta-illustration.png',
			'klaar-start-pack'  => 'klaar-start-pack.png',
			'testimonial-1'     => 'testimonial-1.png',
			'testimonial-2'     => 'testimonial-2.png',
			'pak-social-media'  => 'pak-social-media.png',
			'pak-lead-magnet'   => 'pak-lead-magnet.png',
			'pak-website-light' => 'pak-website-light.png',
		];
	}

	public static function init(): void {
		/* Block themes have no register_activation_hook; we use
		 * the after_switch_theme hook instead. Fires once on activate. */
		add_action( 'after_switch_theme', [ __CLASS__, 'run_install' ] );
		/* Safety net: if someone clones the install or the hook missed,
		 * run on admin_init when the flag is missing. */
		add_action( 'admin_init', [ __CLASS__, 'maybe_run_install' ] );
	}

	public static function maybe_run_install(): void {
		if ( get_option( self::FLAG_OPT ) === ONDM_THEME_VERSION ) return;
		self::run_install();
	}

	public static function run_install(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) return;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$map = (array) get_option( self::MAP_OPT, [] );
		foreach ( self::bundle() as $slug => $filename ) {
			if ( ! empty( $map[ $slug ] ) && get_post( (int) $map[ $slug ] ) ) {
				continue; // already in Media Library
			}
			$source = ONDM_THEME_DIR . '/assets/images/' . $filename;
			if ( ! file_exists( $source ) ) continue;
			$id = self::sideload_local_file( $source, self::title_for( $slug ) );
			if ( $id && ! is_wp_error( $id ) ) {
				$map[ $slug ] = (int) $id;
				update_post_meta( (int) $id, '_ondm_bundle_slug', $slug );
			}
		}
		update_option( self::MAP_OPT, $map, true );

		// Set custom logo.
		if ( ! empty( $map['logo'] ) ) {
			set_theme_mod( 'custom_logo', (int) $map['logo'] );
		}
		// Set site icon (favicon).
		if ( ! empty( $map['site-icon'] ) && ! get_option( 'site_icon' ) ) {
			update_option( 'site_icon', (int) $map['site-icon'] );
		}

		// Auto-create the 10 required pages with the right template assigned.
		self::create_pages();

		update_option( self::FLAG_OPT, ONDM_THEME_VERSION, true );
	}

	/**
	 * Idempotent page creation. Looks up each page by slug; creates it
	 * if missing, with the right title, template assignment, and a
	 * skeleton content block that references the matching pattern.
	 *
	 * Also sets the homepage (show_on_front + page_on_front) once.
	 */
	private static function create_pages(): void {
		require_once ABSPATH . 'wp-admin/includes/post.php';

		$pages = [
			// slug => [ title, template, content ]
			'home' => [
				'title'    => 'OndernemerMarketing',
				'template' => '',
				'content'  => '<!-- wp:paragraph --><p>Welkom bij OndernemerMarketing. Bewerk deze pagina via Weergave , Editor.</p><!-- /wp:paragraph -->',
			],
			'diensten' => [
				'title'    => 'Diensten',
				'template' => 'page-diensten',
				'content'  => '<!-- wp:paragraph --><p>Onze diensten , bewerk via de Site Editor.</p><!-- /wp:paragraph -->',
			],
			'pakketten' => [
				'title'    => 'Pakketten',
				'template' => 'page-pakketten',
				'content'  => '<!-- wp:paragraph --><p>Onze pakketten , bewerk via de Site Editor.</p><!-- /wp:paragraph -->',
			],
			'google-ads-uitbesteden' => [
				'title'    => 'Google Ads Uitbesteden',
				'template' => 'page-google-ads',
				'content'  => '<!-- wp:paragraph --><p>Google Ads uitbesteden , bewerk via de Site Editor.</p><!-- /wp:paragraph -->',
			],
			'tools' => [
				'title'    => 'Tools',
				'template' => 'page-tools',
				'content'  => '<!-- wp:paragraph --><p>Onze tools , bewerk via de Site Editor.</p><!-- /wp:paragraph -->',
			],
			'over-ons' => [
				'title'    => 'Over ons',
				'template' => 'page-over-ons',
				'content'  => '<!-- wp:paragraph --><p>Over ons , bewerk via de Site Editor.</p><!-- /wp:paragraph -->',
			],
			'contact' => [
				'title'    => 'Contact',
				'template' => 'page-contact',
				'content'  => '<!-- wp:paragraph --><p>Contact , bewerk via de Site Editor.</p><!-- /wp:paragraph -->',
			],
			'blog' => [
				'title'    => 'Blog',
				'template' => '',
				'content'  => '<!-- wp:paragraph --><p>Berichten worden hier getoond.</p><!-- /wp:paragraph -->',
			],
			'privacybeleid' => [
				'title'    => 'Privacybeleid',
				'template' => '',
				'content'  => self::legal_content( 'privacy' ),
			],
			'algemene-voorwaarden' => [
				'title'    => 'Algemene voorwaarden',
				'template' => '',
				'content'  => self::legal_content( 'terms' ),
			],
			'disclaimer' => [
				'title'    => 'Disclaimer',
				'template' => '',
				'content'  => self::legal_content( 'disclaimer' ),
			],
		];

		$home_id = 0;
		$blog_id = 0;
		foreach ( $pages as $slug => $cfg ) {
			$existing = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $existing instanceof WP_Post ) {
				if ( 'home' === $slug ) $home_id = (int) $existing->ID;
				if ( 'blog' === $slug ) $blog_id = (int) $existing->ID;
				if ( $cfg['template'] && ! get_post_meta( $existing->ID, '_wp_page_template', true ) ) {
					update_post_meta( $existing->ID, '_wp_page_template', $cfg['template'] );
				}
				continue;
			}
			$post_id = wp_insert_post( [
				'post_title'   => $cfg['title'],
				'post_name'    => $slug,
				'post_content' => $cfg['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			], true );
			if ( is_wp_error( $post_id ) || ! $post_id ) continue;
			if ( $cfg['template'] ) {
				update_post_meta( $post_id, '_wp_page_template', $cfg['template'] );
			}
			if ( 'home' === $slug ) $home_id = (int) $post_id;
			if ( 'blog' === $slug ) $blog_id = (int) $post_id;
		}

		/* Set the homepage to "home" page + posts page to "blog". Only
		 * touch wp_options if they aren't already configured by the
		 * operator. */
		if ( $home_id && 'posts' === get_option( 'show_on_front' ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
			if ( $blog_id ) update_option( 'page_for_posts', $blog_id );
		}
	}

	/** Long-form Dutch legal copy , boilerplate that the operator
	 *  should review before going live. */
	private static function legal_content( string $kind ): string {
		switch ( $kind ) {
			case 'privacy':
				return implode( "\n\n", [
					'<!-- wp:paragraph --><p><strong>Laatst bijgewerkt:</strong> ' . current_time( 'd-m-Y' ) . '</p><!-- /wp:paragraph -->',
					'<!-- wp:paragraph --><p>OndernemerMarketing (KvK: <em>vul in</em>), gevestigd te Breedveldsingel 1, 3055PG Rotterdam, is verantwoordelijk voor de verwerking van persoonsgegevens zoals weergegeven in deze privacyverklaring.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Welke gegevens we verzamelen</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Wij verwerken persoonsgegevens die je zelf aan ons verstrekt, zoals voor- en achternaam, e-mailadres, telefoonnummer en de inhoud van je bericht via het contactformulier. Daarnaast verzamelen we anonieme analytische gegevens (geen cookies van derden zonder toestemming).</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Waarom we deze gegevens verzamelen</h2><!-- /wp:heading -->',
					'<!-- wp:list --><ul class="wp-block-list"><li>Om contact met je op te nemen na een intakeaanvraag.</li><li>Om de overeenkomst tussen ons uit te voeren.</li><li>Om onze dienstverlening te verbeteren.</li><li>Om te voldoen aan een wettelijke verplichting.</li></ul><!-- /wp:list -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Bewaartermijn</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Wij bewaren je persoonsgegevens niet langer dan strikt nodig is om de doelen te realiseren waarvoor je gegevens worden verzameld. Contactformulier-inzendingen worden maximaal 24 maanden bewaard.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Delen met derden</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>OndernemerMarketing verkoopt jouw gegevens niet aan derden. Wij verstrekken alleen aan derden indien dit nodig is voor de uitvoering van onze overeenkomst of om te voldoen aan een wettelijke verplichting. Met bedrijven die je gegevens verwerken in onze opdracht (zoals onze e-mailprovider Brevo), sluiten wij een verwerkersovereenkomst om eenzelfde niveau van beveiliging en vertrouwelijkheid te garanderen.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Cookies</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Wij gebruiken alleen functionele en analytische cookies die geen inbreuk maken op je privacy. Je kunt je afmelden via de browserinstellingen.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Inzage, correctie of verwijdering</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Je hebt het recht om je persoonsgegevens in te zien, te corrigeren of te verwijderen. Stuur een verzoek naar <a href="mailto:info@ondernemermarketing.nl">info@ondernemermarketing.nl</a>. Je kunt ook een klacht indienen bij de Autoriteit Persoonsgegevens.</p><!-- /wp:paragraph -->',
					'<!-- wp:paragraph --><p><em>Deze privacyverklaring is een Nederlandse boilerplate. Laat hem juridisch reviewen voordat je live gaat.</em></p><!-- /wp:paragraph -->',
				] );

			case 'terms':
				return implode( "\n\n", [
					'<!-- wp:paragraph --><p><strong>Laatst bijgewerkt:</strong> ' . current_time( 'd-m-Y' ) . '</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 1 , Definities</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>In deze algemene voorwaarden wordt verstaan onder Opdrachtnemer: OndernemerMarketing (KvK: <em>vul in</em>), gevestigd te Breedveldsingel 1, 3055PG Rotterdam. Opdrachtgever: de natuurlijke of rechtspersoon die met Opdrachtnemer een overeenkomst aangaat.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 2 , Toepasselijkheid</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Deze voorwaarden zijn van toepassing op alle offertes, aanbiedingen en overeenkomsten waarbij Opdrachtnemer diensten levert aan Opdrachtgever. Afwijkingen van deze voorwaarden zijn alleen geldig indien schriftelijk overeengekomen.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 3 , Looptijd en opzegging</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Maandelijkse diensten kennen een minimale initi&euml;le looptijd van 3 maanden. Daarna geldt een opzegtermijn van 1 maand. Eenmalige projecten worden geleverd binnen de in de offerte afgesproken termijn.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 4 , Tarieven en betaling</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Alle bedragen zijn exclusief BTW tenzij anders vermeld. Facturen worden maandelijks vooraf gefactureerd met een betalingstermijn van 14 dagen. Bij overschrijding is wettelijke handelsrente verschuldigd.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 5 , Aansprakelijkheid</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>De totale aansprakelijkheid van Opdrachtnemer is beperkt tot het bedrag dat in het lopende kalenderjaar door Opdrachtgever aan Opdrachtnemer is betaald. Opdrachtnemer is niet aansprakelijk voor indirecte schade, gederfde winst of gemiste besparingen.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 6 , Vertrouwelijkheid</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Beide partijen verplichten zich tot geheimhouding van alle vertrouwelijke informatie die zij in het kader van de overeenkomst van elkaar verkrijgen.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Artikel 7 , Toepasselijk recht</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Op alle overeenkomsten is Nederlands recht van toepassing. Geschillen worden voorgelegd aan de bevoegde rechter te Rotterdam.</p><!-- /wp:paragraph -->',
					'<!-- wp:paragraph --><p><em>Dit is een Nederlandse boilerplate. Laat de voorwaarden juridisch reviewen voordat je live gaat.</em></p><!-- /wp:paragraph -->',
				] );

			case 'disclaimer':
			default:
				return implode( "\n\n", [
					'<!-- wp:paragraph --><p><strong>Laatst bijgewerkt:</strong> ' . current_time( 'd-m-Y' ) . '</p><!-- /wp:paragraph -->',
					'<!-- wp:paragraph --><p>De informatie op deze website wordt zorgvuldig samengesteld. OndernemerMarketing garandeert echter niet dat de informatie altijd volledig, juist of actueel is.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Gebruik op eigen risico</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Aan de inhoud van deze website kunnen geen rechten worden ontleend. OndernemerMarketing aanvaardt geen aansprakelijkheid voor enige schade die kan voortvloeien uit het gebruik van de informatie op deze website.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Externe links</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Deze website kan links bevatten naar externe websites. OndernemerMarketing is niet verantwoordelijk voor de inhoud of het privacybeleid van deze externe websites.</p><!-- /wp:paragraph -->',
					'<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Intellectueel eigendom</h2><!-- /wp:heading -->',
					'<!-- wp:paragraph --><p>Alle teksten, afbeeldingen en andere content op deze website zijn eigendom van OndernemerMarketing en mogen niet zonder schriftelijke toestemming worden gekopieerd of hergebruikt.</p><!-- /wp:paragraph -->',
				] );
		}
	}

	/** Copy a local file into uploads, generate metadata, return attachment ID. */
	private static function sideload_local_file( string $source, string $title ): int {
		$filename = wp_basename( $source );
		$upload = wp_upload_bits( $filename, null, file_get_contents( $source ) );
		if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) return 0;

		$wp_filetype = wp_check_filetype( $upload['file'] );
		$attachment = [
			'post_mime_type' => $wp_filetype['type'] ?? 'image/png',
			'post_title'     => sanitize_text_field( $title ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];
		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
		if ( is_wp_error( $attach_id ) || ! $attach_id ) return 0;

		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		/* Sensible alt-text default so screen readers don't read the filename. */
		update_post_meta( $attach_id, '_wp_attachment_image_alt', $title );

		return (int) $attach_id;
	}

	private static function title_for( string $slug ): string {
		$map = [
			'logo'              => 'OndernemerMarketing logo',
			'logo-variant'      => 'OndernemerMarketing logo (variant)',
			'site-icon'         => 'OM site icon',
			'hero-home'         => 'Ondernemer aan het werk , marketing zonder gedoe',
			'over-ons-hero'     => 'Team OndernemerMarketing op kantoor',
			'cta-illustration'  => 'Glimlachende ondernemer met laptop',
			'klaar-start-pack'  => 'Ondernemer met camera klaar voor de start',
			'testimonial-1'     => 'Tevreden klant , Sarah van der Berg',
			'testimonial-2'     => 'Tevreden klant , Lisa Hartmann',
			'pak-social-media'  => 'Social Media Funnel Pack illustratie',
			'pak-lead-magnet'   => 'Lead Magnet Landingspagina illustratie',
			'pak-website-light' => 'Website Light illustratie',
		];
		return $map[ $slug ] ?? ucfirst( str_replace( '-', ' ', $slug ) );
	}

	/* ----------------------------------------------------------------
	 * Public helpers used by patterns + WXR.
	 * ---------------------------------------------------------------- */

	/** URL for a bundled image. Prefers Media Library attachment if installed,
	 *  falls back to the theme directory so patterns render even before
	 *  the install hook has run. */
	public static function img( string $slug, string $size = 'full' ): string {
		$map = (array) get_option( self::MAP_OPT, [] );
		if ( ! empty( $map[ $slug ] ) ) {
			$id  = (int) $map[ $slug ];
			$src = wp_get_attachment_image_url( $id, $size );
			if ( $src ) return (string) $src;
		}
		$filename = self::bundle()[ $slug ] ?? null;
		if ( ! $filename ) return '';
		return ONDM_THEME_URI . '/assets/images/' . $filename;
	}

	/** Attachment ID for a bundled image (0 if not yet sideloaded). */
	public static function img_id( string $slug ): int {
		$map = (array) get_option( self::MAP_OPT, [] );
		return (int) ( $map[ $slug ] ?? 0 );
	}
}

ONDM_Install::init();
