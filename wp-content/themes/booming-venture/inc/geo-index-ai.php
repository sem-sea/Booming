<?php
/**
 * B2B GEO Index: AI visibility (1.13.0).
 *
 * Every week the site asks one buyer question per category (the "cat" field in
 * blocks/geo-index/companies.json) to four AI assistants through their official
 * APIs, with web search switched on, and records which companies each answer
 * names and which domains it cites:
 *  - ChatGPT    OpenAI Responses API, tool web_search
 *  - Claude     Anthropic Messages API, tool web_search_20250305
 *  - Gemini     Gemini Interactions API, tool google_search
 *  - Perplexity Perplexity Agent API
 * Dutch list: questions in Dutch. International list: questions in English.
 *
 * API keys: Settings > GEO Index, or constants in wp-config.php
 * (BV_GI_OPENAI_KEY, BV_GI_ANTHROPIC_KEY, BV_GI_GEMINI_KEY, BV_GI_PERPLEXITY_KEY).
 * Engines without a key are skipped. Keys are never printed.
 *
 * AI score per company (0 to 100): share of the answers in its own category
 * that name it (80%) or cite its domain (20%), over all engines that answered.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

function bv_gi_ai_engines(): array {
	return [
		'openai'     => [ 'label' => 'ChatGPT', 'const' => 'BV_GI_OPENAI_KEY', 'model' => 'gpt-6-luna', 'model_label' => 'Model', 'router' => 'openai/gpt-6-luna' ],
		'claude'     => [ 'label' => 'Claude', 'const' => 'BV_GI_ANTHROPIC_KEY', 'model' => 'claude-haiku-5-5', 'model_label' => 'Model', 'router' => 'anthropic/claude-haiku-5.5' ],
		'gemini'     => [ 'label' => 'Gemini', 'const' => 'BV_GI_GEMINI_KEY', 'model' => 'gemini-3.8-flash', 'model_label' => 'Model', 'router' => 'google/gemini-3.8-flash' ],
		'perplexity' => [ 'label' => 'Perplexity', 'const' => 'BV_GI_PERPLEXITY_KEY', 'model' => 'fast', 'model_label' => 'Effort-preset', 'router' => 'perplexity/sonar' ],
	];
}

function bv_gi_ai_settings(): array {
	return wp_parse_args( (array) get_option( 'bv_gi_ai', [] ), [ 'keys' => [], 'models' => [], 'enabled' => 0, 'cap' => 700, 'router_engines' => [ 'openai', 'claude', 'gemini', 'perplexity' ] ] );
}

function bv_gi_ai_key( string $engine ): string {
	$e = bv_gi_ai_engines()[ $engine ] ?? null;
	if ( $e && defined( $e['const'] ) && constant( $e['const'] ) ) {
		return (string) constant( $e['const'] );
	}
	return (string) ( bv_gi_ai_settings()['keys'][ $engine ] ?? '' );
}

function bv_gi_ai_model( string $engine ): string {
	$m = (string) ( bv_gi_ai_settings()['models'][ $engine ] ?? '' );
	return '' !== $m ? $m : bv_gi_ai_engines()[ $engine ]['model'];
}

/*
 * OpenRouter: one key for all four assistants. Each request goes to the
 * same model family with the provider's own web search (engine "native"),
 * billed at the provider's rates plus OpenRouter's fee on credits. An
 * assistant with its own key always uses that key directly.
 */
function bv_gi_ai_router_key(): string {
	if ( defined( 'BV_GI_OPENROUTER_KEY' ) && BV_GI_OPENROUTER_KEY ) {
		return (string) BV_GI_OPENROUTER_KEY;
	}
	return (string) ( bv_gi_ai_settings()['keys']['openrouter'] ?? '' );
}

/** 'direct', 'openrouter' or '' (not measured). */
function bv_gi_ai_via( string $engine ): string {
	if ( '' !== bv_gi_ai_key( $engine ) ) {
		return 'direct';
	}
	if ( '' !== bv_gi_ai_router_key() && in_array( $engine, (array) bv_gi_ai_settings()['router_engines'], true ) ) {
		return 'openrouter';
	}
	return '';
}

/** Engines that have a key (own or OpenRouter) and AI measuring switched on. */
function bv_gi_ai_active(): array {
	if ( empty( bv_gi_ai_settings()['enabled'] ) ) {
		return [];
	}
	return array_values( array_filter( array_keys( bv_gi_ai_engines() ), static fn( $e ) => '' !== bv_gi_ai_via( $e ) ) );
}

/*
 * Monthly cap per assistant, counted in this site: a second brake on top of
 * the spend limit at each provider. 700 covers the 143 weekly questions of
 * a long month plus one extra manual run.
 */
function bv_gi_ai_cap(): int {
	return max( 0, (int) bv_gi_ai_settings()['cap'] );
}

function bv_gi_ai_used( string $engine ): int {
	$u = (array) get_option( 'bv_gi_ai_usage', [] );
	return ( $u['month'] ?? '' ) === gmdate( 'Y-m' ) ? (int) ( $u['n'][ $engine ] ?? 0 ) : 0;
}

function bv_gi_ai_count( array $engines ): void {
	$u = (array) get_option( 'bv_gi_ai_usage', [] );
	if ( ( $u['month'] ?? '' ) !== gmdate( 'Y-m' ) ) {
		$u = [ 'month' => gmdate( 'Y-m' ), 'n' => [] ];
	}
	foreach ( $engines as $e ) {
		$u['n'][ $e ] = (int) ( $u['n'][ $e ] ?? 0 ) + 1;
	}
	update_option( 'bv_gi_ai_usage', $u, false );
}

/* ------------------------------------------------------------------ prompts and parsing (pure) */

/** The weekly question for a category; the wording alternates per ISO week. */
function bv_gi_ai_prompt( string $list, string $cat, int $week ): string {
	$t = 'nl' === $list
		? [ 'Wat is de beste %s in Nederland? Noem een paar aanbieders en zeg kort waarom.', 'Welke %s raad je aan voor een Nederlands bedrijf? Geef een korte lijst met aanbieders.' ]
		: [ 'What is the best %s? Name a few providers and say briefly why.', 'Which %s would you recommend for a B2B company? Give a short list of providers.' ];
	return sprintf( $t[ $week % 2 ], $cat );
}

/** Answer text and every source URL from any of the four API responses. */
function bv_gi_ai_extract( $json ): array {
	$out = [ 'text' => '', 'urls' => [] ];
	$walk = static function ( $node ) use ( &$walk, &$out ) {
		if ( ! is_array( $node ) ) {
			return;
		}
		$type = isset( $node['type'] ) && is_string( $node['type'] ) ? $node['type'] : '';
		if ( isset( $node['text'] ) && is_string( $node['text'] ) && in_array( $type, [ '', 'text', 'output_text' ], true ) ) {
			$out['text'] .= $node['text'] . "\n";
		}
		foreach ( [ 'url', 'uri' ] as $k ) {
			if ( isset( $node[ $k ] ) && is_string( $node[ $k ] ) && 0 === strpos( $node[ $k ], 'http' ) ) {
				$out['urls'][] = $node[ $k ];
			}
		}
		foreach ( [ 'citations' ] as $k ) { // Perplexity style: list of URL strings
			if ( isset( $node[ $k ] ) && is_array( $node[ $k ] ) ) {
				foreach ( $node[ $k ] as $c ) {
					if ( is_string( $c ) && 0 === strpos( $c, 'http' ) ) {
						$out['urls'][] = $c;
					}
				}
			}
		}
		foreach ( $node as $k => $v ) {
			if ( is_array( $v ) && 'encrypted_content' !== $k ) {
				$walk( $v );
			}
		}
	};
	$walk( $json );
	$out['urls'] = array_values( array_unique( $out['urls'] ) );
	return $out;
}

/**
 * Does this answer name the company? Normal names: whole word, any case.
 * Names that are also common words ("strict"): only as a list item, in bold,
 * or as the domain.
 */
function bv_gi_ai_mentions( string $text, array $c ): bool {
	if ( false !== stripos( $text, $c['domain'] ) ) {
		return true;
	}
	$names = array_unique( array_merge( [ $c['name'] ], (array) ( $c['alias'] ?? [] ) ) );
	foreach ( $names as $n ) {
		$q = preg_quote( $n, '/' );
		// a single common word ("Make", "Exact") needs list or bold context; "Exact Online" does not
		if ( empty( $c['strict'] ) || ! preg_match( '/^\p{L}+$/u', $n ) ) {
			if ( preg_match( '/(?<![\p{L}\p{N}])' . $q . '(?![\p{L}\p{N}])/iu', $text ) ) {
				return true;
			}
		} elseif ( preg_match( '/(^|\n)\s*(?:[-*\x{2022}]|\d+[.)])\s*(?:\*\*|__)?\s*' . $q . '(?![\p{L}\p{N}])|\*\*\s*' . $q . '\s*\*\*|__' . $q . '__/u', $text ) ) {
			return true;
		}
	}
	return false;
}

/** Is the company's domain among the cited sources? */
function bv_gi_ai_cites( array $urls, string $domain ): bool {
	foreach ( $urls as $u ) {
		$h = strtolower( (string) wp_parse_url( $u, PHP_URL_HOST ) );
		if ( $h === $domain || str_ends_with( $h, '.' . $domain ) ) {
			return true;
		}
	}
	return false;
}

/* ------------------------------------------------------------------ requests */

function bv_gi_ai_request( string $engine, string $prompt ): array {
	if ( 'openrouter' === bv_gi_ai_via( $engine ) ) {
		$model = (string) ( bv_gi_ai_settings()['models'][ 'router_' . $engine ] ?? '' );
		$model = '' !== $model ? $model : bv_gi_ai_engines()[ $engine ]['router'];
		$body  = [ 'model' => $model, 'input' => $prompt, 'max_output_tokens' => 3000 ];
		if ( 0 !== strpos( $model, 'perplexity/' ) ) { // Perplexity models search by themselves
			$body['tools'] = [ [ 'type' => 'openrouter:web_search', 'parameters' => [ 'engine' => 'native', 'max_results' => 5 ] ] ];
		}
		if ( 0 === strpos( $model, 'openai/' ) ) {
			$body['reasoning'] = [ 'effort' => 'low' ];
		}
		return [
			'url'     => 'https://openrouter.ai/api/v1/responses',
			'headers' => [ 'Authorization' => 'Bearer ' . bv_gi_ai_router_key(), 'Content-Type' => 'application/json', 'HTTP-Referer' => home_url( '/' ), 'X-Title' => 'Booming Venture B2B GEO Index' ],
			'data'    => wp_json_encode( $body ),
			'type'    => 'POST',
		];
	}
	$key   = bv_gi_ai_key( $engine );
	$model = bv_gi_ai_model( $engine );
	switch ( $engine ) {
		case 'openai':
			return [
				'url'     => 'https://api.openai.com/v1/responses',
				'headers' => [ 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ],
				/* Reasoning models think briefly: the answer is a short list, and thinking tokens cost money. */
				'data'    => wp_json_encode( [ 'model' => $model, 'input' => $prompt, 'tools' => [ [ 'type' => 'web_search' ] ], 'max_output_tokens' => 3000 ]
					+ ( preg_match( '/^(gpt-5|gpt-6|o\d)/', $model ) ? [ 'reasoning' => [ 'effort' => 'low' ] ] : [] ) ),
				'type'    => 'POST',
			];
		case 'claude':
			return [
				'url'     => 'https://api.anthropic.com/v1/messages',
				'headers' => [ 'x-api-key' => $key, 'anthropic-version' => '2023-06-01', 'Content-Type' => 'application/json' ],
				'data'    => wp_json_encode( [
					'model'      => $model,
					'max_tokens' => 1500,
					'messages'   => [ [ 'role' => 'user', 'content' => $prompt ] ],
					'tools'      => [ [ 'type' => 'web_search_20250305', 'name' => 'web_search', 'max_uses' => 1 ] ], // one search keeps the cost at about 1 cent per question
				] ),
				'type'    => 'POST',
			];
		case 'gemini':
			return [
				'url'     => 'https://generativelanguage.googleapis.com/v1beta/interactions',
				'headers' => [ 'x-goog-api-key' => $key, 'Content-Type' => 'application/json' ],
				'data'    => wp_json_encode( [ 'model' => $model, 'input' => $prompt, 'tools' => [ [ 'type' => 'google_search' ] ] ] ),
				'type'    => 'POST',
			];
		case 'perplexity':
			return [
				'url'     => 'https://api.perplexity.ai/v1/agent',
				'headers' => [ 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ],
				'data'    => wp_json_encode( [ 'effort' => $model, 'input' => $prompt ] ),
				'type'    => 'POST',
			];
	}
	return [];
}

/** Send one prompt to all active engines at once. Returns [ engine => [ 'ok', 'text', 'urls', 'error' ] ]. */
function bv_gi_ai_ask( string $prompt, array $engines ): array {
	$reqs = [];
	foreach ( $engines as $e ) {
		$reqs[ $e ] = bv_gi_ai_request( $e, $prompt );
	}
	$class = class_exists( '\WpOrg\Requests\Requests' ) ? '\WpOrg\Requests\Requests' : ( class_exists( 'Requests' ) ? 'Requests' : '' );
	$out   = [];
	if ( ! $class ) {
		foreach ( $reqs as $e => $r ) {
			$res = wp_remote_post( $r['url'], [ 'headers' => $r['headers'], 'body' => $r['data'], 'timeout' => 60 ] );
			$out[ $e ] = bv_gi_ai_parse( is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res ), is_wp_error( $res ) ? $res->get_error_message() : (string) wp_remote_retrieve_body( $res ) );
		}
		return $out;
	}
	$responses = $class::request_multiple( $reqs, [ 'timeout' => 75, 'connect_timeout' => 10, 'useragent' => BV_GI_UA ] );
	foreach ( $responses as $e => $res ) {
		if ( is_object( $res ) && isset( $res->status_code ) ) {
			$out[ $e ] = bv_gi_ai_parse( (int) $res->status_code, (string) $res->body );
		} else {
			$out[ $e ] = [ 'ok' => false, 'text' => '', 'urls' => [], 'error' => is_object( $res ) && method_exists( $res, 'getMessage' ) ? $res->getMessage() : 'request failed' ];
		}
	}
	return $out;
}

function bv_gi_ai_parse( int $code, string $body ): array {
	$json = json_decode( $body, true );
	if ( $code < 200 || $code >= 300 || ! is_array( $json ) ) {
		$msg = is_array( $json ) ? ( $json['error']['message'] ?? $json['error'] ?? $json['message'] ?? '' ) : '';
		return [ 'ok' => false, 'text' => '', 'urls' => [], 'error' => trim( 'HTTP ' . $code . ' ' . substr( is_string( $msg ) ? $msg : (string) wp_json_encode( $msg ), 0, 200 ) ) ];
	}
	$x = bv_gi_ai_extract( $json );
	return [ 'ok' => '' !== trim( $x['text'] ), 'text' => $x['text'], 'urls' => $x['urls'], 'error' => '' === trim( $x['text'] ) ? 'empty answer' : '' ];
}

/* ------------------------------------------------------------------ weekly run */

/** Unique [ list, category ] pairs. */
function bv_gi_ai_categories(): array {
	$pairs = [];
	foreach ( [ 'nl', 'intl' ] as $list ) {
		foreach ( (array) ( bv_gi_companies()[ $list ] ?? [] ) as $c ) {
			if ( ! empty( $c['cat'] ) ) {
				$pairs[ $list . '|' . $c['cat'] ] = [ $list, $c['cat'] ];
			}
		}
	}
	return array_values( $pairs );
}

function bv_gi_ai_start(): void {
	if ( ! bv_gi_ai_active() ) {
		return;
	}
	update_option( 'bv_gi_ai_queue', bv_gi_ai_categories(), false );
	update_option( 'bv_gi_ai_work', [ 'started' => time(), 'week' => (int) gmdate( 'W' ), 'rows' => [], 'answers' => [], 'errors' => [] ], false );
	wp_clear_scheduled_hook( 'bv_gi_ai_batch' );
	wp_schedule_single_event( time() + 30, 'bv_gi_ai_batch' );
}
add_action( 'bv_gi_weekly', 'bv_gi_ai_start', 20 );

add_action( 'bv_gi_ai_batch', static function () {
	if ( get_transient( 'bv_gi_ai_lock' ) ) {
		return;
	}
	set_transient( 'bv_gi_ai_lock', 1, 5 * MINUTE_IN_SECONDS );
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 240 ); // phpcs:ignore
	}
	$active  = bv_gi_ai_active();
	$engines = $active;
	$capped  = false;
	$queue   = (array) get_option( 'bv_gi_ai_queue', [] );
	$work    = (array) get_option( 'bv_gi_ai_work', [] );
	$t0      = time();
	while ( $engines && $queue && time() - $t0 < 120 ) {
		$cap = bv_gi_ai_cap();
		foreach ( $engines as $i => $e ) {
			if ( bv_gi_ai_used( $e ) >= $cap ) {
				unset( $engines[ $i ] );
				$work['errors'][ $e ] = sprintf( 'Maandlimiet van %d vragen bereikt; de meting gaat volgende maand verder.', $cap );
			}
		}
		$engines = array_values( $engines );
		if ( ! $engines ) {
			$capped = true;
			break;
		}
		[ $list, $cat ] = array_shift( $queue );
		$prompt  = bv_gi_ai_prompt( $list, $cat, (int) ( $work['week'] ?? 0 ) );
		bv_gi_ai_count( $engines );
		$answers = bv_gi_ai_ask( $prompt, $engines );
		foreach ( (array) ( bv_gi_companies()[ $list ] ?? [] ) as $c ) {
			if ( ( $c['cat'] ?? '' ) !== $cat ) {
				continue;
			}
			$row = $work['rows'][ $list ][ $c['domain'] ] ?? [ 'n' => 0, 'm' => 0, 'c' => 0, 'eng' => [] ];
			foreach ( $answers as $e => $a ) {
				if ( ! $a['ok'] ) {
					continue;
				}
				$named = bv_gi_ai_mentions( $a['text'], $c );
				$cited = bv_gi_ai_cites( $a['urls'], $c['domain'] );
				$row['n']++;
				$row['m'] += $named ? 1 : 0;
				$row['c'] += $cited ? 1 : 0;
				$row['eng'][ $e ] = ! empty( $row['eng'][ $e ] ) || $named;
			}
			$work['rows'][ $list ][ $c['domain'] ] = $row;
		}
		foreach ( $answers as $e => $a ) {
			$work['answers'][ $list ][ $cat ][ $e ] = [ 'text' => mb_substr( trim( $a['text'] ), 0, 900 ), 'urls' => array_slice( $a['urls'], 0, 8 ) ];
			if ( ! $a['ok'] ) {
				$work['errors'][ $e ] = $a['error'];
			}
		}
		$work['prompts'][ $list ][ $cat ] = $prompt;
	}
	update_option( 'bv_gi_ai_queue', $queue, false );
	update_option( 'bv_gi_ai_work', $work, false );
	delete_transient( 'bv_gi_ai_lock' );
	if ( $queue && $capped ) {
		/* Every assistant hit its monthly cap: keep the run and the last full results, try again tomorrow. */
		wp_schedule_single_event( time() + DAY_IN_SECONDS, 'bv_gi_ai_batch' );
	} elseif ( $queue && $engines ) {
		wp_schedule_single_event( time() + 60, 'bv_gi_ai_batch' );
	} elseif ( ! empty( $work['rows'] ) ) {
		$work['completed'] = time();
		$work['engines']   = $active;
		update_option( 'bv_gi_ai_results', [ 'completed' => $work['completed'], 'engines' => $active, 'rows' => $work['rows'], 'prompts' => $work['prompts'] ?? [] ], false );
		update_option( 'bv_gi_ai_answers', $work['answers'], false );
		update_option( 'bv_gi_ai_work', $work, false );
		if ( function_exists( 'bv_gi_touch_pages' ) ) {
			bv_gi_touch_pages();
		}
	}
} );

/** AI score and per-engine mentions for one company, or null without data. */
function bv_gi_ai_score( string $list, string $domain ): ?array {
	$r = get_option( 'bv_gi_ai_results', [] )['rows'][ $list ][ $domain ] ?? null;
	if ( ! $r || empty( $r['n'] ) ) {
		return null;
	}
	return [
		'score' => (int) round( 100 * ( 0.8 * $r['m'] / $r['n'] + 0.2 * $r['c'] / $r['n'] ) ),
		'eng'   => (array) $r['eng'],
		'n'     => (int) $r['n'],
		'm'     => (int) $r['m'],
	];
}

/* ------------------------------------------------------------------ settings page */

add_action( 'admin_menu', static function () {
	add_options_page( 'GEO Index', 'GEO Index', 'manage_options', 'bv-geo-index', 'bv_gi_ai_settings_page' );
} );

add_action( 'admin_post_bv_gi_ai_save', static function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'bv_gi_ai_save' ) ) {
		wp_die( 'Not allowed.' );
	}
	$s = bv_gi_ai_settings();
	foreach ( array_keys( bv_gi_ai_engines() ) as $e ) {
		$new = trim( sanitize_text_field( wp_unslash( $_POST['key'][ $e ] ?? '' ) ) );
		if ( ! empty( $_POST['del'][ $e ] ) ) {
			unset( $s['keys'][ $e ] );
		} elseif ( '' !== $new ) {
			$s['keys'][ $e ] = $new;
		}
		$s['models'][ $e ] = trim( sanitize_text_field( wp_unslash( $_POST['model'][ $e ] ?? '' ) ) );
	}
	$rk = trim( sanitize_text_field( wp_unslash( $_POST['key']['openrouter'] ?? '' ) ) );
	if ( ! empty( $_POST['del']['openrouter'] ) ) {
		unset( $s['keys']['openrouter'] );
	} elseif ( '' !== $rk ) {
		$s['keys']['openrouter'] = $rk;
	}
	$s['router_engines'] = array_values( array_intersect( array_keys( bv_gi_ai_engines() ), array_map( 'sanitize_key', (array) wp_unslash( $_POST['router_engines'] ?? [] ) ) ) );
	$s['enabled'] = empty( $_POST['enabled'] ) ? 0 : 1;
	$s['cap']     = max( 0, min( 5000, (int) ( $_POST['cap'] ?? 700 ) ) );
	update_option( 'bv_gi_ai', $s, false );
	if ( ! empty( $_POST['run'] ) ) {
		do_action( 'bv_gi_weekly' );
	}
	wp_safe_redirect( admin_url( 'options-general.php?page=bv-geo-index&saved=1' ) );
	exit;
} );

function bv_gi_ai_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s       = bv_gi_ai_settings();
	$meta    = (array) get_option( 'bv_gi_meta', [] );
	$res     = (array) get_option( 'bv_gi_ai_results', [] );
	$work    = (array) get_option( 'bv_gi_ai_work', [] );
	$queue   = count( (array) get_option( 'bv_gi_ai_queue', [] ) );
	$squeue  = count( (array) get_option( 'bv_gi_queue', [] ) );
	$cats    = count( bv_gi_ai_categories() );
	$fmt     = static fn( $t ) => $t ? wp_date( 'j F Y H:i', $t ) : '-';
	$links   = [
		'openai'     => 'https://platform.openai.com/api-keys',
		'claude'     => 'https://platform.claude.com/',
		'gemini'     => 'https://aistudio.google.com/apikey',
		'perplexity' => 'https://docs.perplexity.ai/',
	];
	?>
	<div class="wrap">
		<h1>B2B GEO Index</h1>
		<?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success"><p>Opgeslagen.</p></div><?php endif; ?>
		<p>De index meet elke maandag de websites van 200 bedrijven. Met API-sleutels meet hij ook hoe vaak ChatGPT, Claude, Gemini en Perplexity deze bedrijven noemen: één vraag per categorie per AI-assistent (<?php echo (int) $cats; ?> vragen per assistent per week).</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="bv_gi_ai_save">
			<?php wp_nonce_field( 'bv_gi_ai_save' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row">AI-meting</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?>> Aan</label></td></tr>
				<?php $rk = bv_gi_ai_router_key(); $rconst = defined( 'BV_GI_OPENROUTER_KEY' ) && BV_GI_OPENROUTER_KEY; ?>
				<tr>
					<th scope="row">OpenRouter<br><span style="font-weight:400">(één sleutel voor alle vier)</span></th>
					<td>
						<?php if ( $rconst ) : ?>
							<p>Sleutel staat in wp-config.php (BV_GI_OPENROUTER_KEY).</p>
						<?php else : ?>
							<input type="password" name="key[openrouter]" value="" autocomplete="new-password" class="regular-text" placeholder="<?php echo $rk ? esc_attr( 'Ingesteld, eindigt op ' . substr( $rk, -4 ) ) : 'OpenRouter API-sleutel'; ?>">
							<?php if ( $rk ) : ?><label><input type="checkbox" name="del[openrouter]" value="1"> Verwijder</label><?php endif; ?>
						<?php endif; ?>
						<p>
							<?php foreach ( bv_gi_ai_engines() as $e => $def ) : ?>
								<label style="margin-right:12px"><input type="checkbox" name="router_engines[]" value="<?php echo esc_attr( $e ); ?>" <?php checked( in_array( $e, (array) $s['router_engines'], true ) ); ?>> <?php echo esc_html( $def['label'] ); ?></label>
							<?php endforeach; ?>
						</p>
						<p class="description">Maak een sleutel aan op <a href="https://openrouter.ai/settings/keys" target="_blank" rel="noopener">openrouter.ai</a>, koop vooraf tegoed, zet automatisch bijvullen uit en geef de sleutel een limiet met maandelijkse reset (bijvoorbeeld $20). Elke assistent met een eigen sleutel hieronder gebruikt die eigen sleutel. Veiliger: <code>define( 'BV_GI_OPENROUTER_KEY', '...' );</code> in wp-config.php.</p>
					</td>
				</tr>
				<?php foreach ( bv_gi_ai_engines() as $e => $def ) :
					$k     = bv_gi_ai_key( $e );
					$const = defined( $def['const'] ) && constant( $def['const'] );
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $def['label'] ); ?></th>
						<td>
							<?php if ( $const ) : ?>
								<p>Sleutel staat in wp-config.php (<?php echo esc_html( $def['const'] ); ?>).</p>
							<?php else : ?>
								<input type="password" name="key[<?php echo esc_attr( $e ); ?>]" value="" autocomplete="new-password" class="regular-text" placeholder="<?php echo $k ? esc_attr( 'Ingesteld, eindigt op ' . substr( $k, -4 ) ) : 'API-sleutel'; ?>">
								<?php if ( $k ) : ?><label><input type="checkbox" name="del[<?php echo esc_attr( $e ); ?>]" value="1"> Verwijder</label><?php endif; ?>
								<p class="description">Maak een aparte sleutel aan via <a href="<?php echo esc_url( $links[ $e ] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( wp_parse_url( $links[ $e ], PHP_URL_HOST ) ); ?></a> en stel daar een harde bestedingslimiet in. Veiliger: zet hem in wp-config.php als <code><?php echo esc_html( "define( '" . $def['const'] . "', '...' );" ); ?></code>, dan staat hij niet in de database.</p>
							<?php endif; ?>
							<p><label><?php echo esc_html( $def['model_label'] ); ?>: <input type="text" name="model[<?php echo esc_attr( $e ); ?>]" value="<?php echo esc_attr( $s['models'][ $e ] ?? '' ); ?>" placeholder="<?php echo esc_attr( $def['model'] ); ?>"></label></p>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr><th scope="row">Maandlimiet</th><td><input type="number" name="cap" min="0" max="5000" step="10" value="<?php echo (int) $s['cap']; ?>" class="small-text"> vragen per AI-assistent per maand<p class="description">Een rem in je eigen site, naast de bestedingslimiet bij de aanbieder. Een normale maand is ongeveer <?php echo (int) ( $cats * 4.35 ); ?> vragen.</p></td></tr>
				<tr><th scope="row">Nu meten</th><td><label><input type="checkbox" name="run" value="1"> Start na opslaan direct een nieuwe meting (websites en AI)</label></td></tr>
			</table>
			<?php submit_button( 'Opslaan' ); ?>
		</form>
		<h2>Status</h2>
		<table class="widefat striped" style="max-width:760px">
			<tbody>
				<tr><td>Websitemeting gestart</td><td><?php echo esc_html( $fmt( $meta['started'] ?? 0 ) ); ?></td></tr>
				<tr><td>Websitemeting klaar</td><td><?php echo esc_html( $fmt( $meta['completed'] ?? 0 ) ); ?><?php echo $squeue ? ' (nog ' . (int) $squeue . ' bedrijven in de wachtrij)' : ''; ?></td></tr>
				<tr><td>AI-meting gestart</td><td><?php echo esc_html( $fmt( $work['started'] ?? 0 ) ); ?></td></tr>
				<tr><td>AI-meting klaar</td><td><?php echo esc_html( $fmt( $res['completed'] ?? 0 ) ); ?><?php echo $queue ? ' (nog ' . (int) $queue . ' vragen in de wachtrij)' : ''; ?></td></tr>
				<tr><td>Vragen deze maand</td><td><?php echo esc_html( implode( ', ', array_map( static fn( $e ) => bv_gi_ai_engines()[ $e ]['label'] . ' ' . bv_gi_ai_used( $e ) . ' / ' . bv_gi_ai_cap(), array_keys( bv_gi_ai_engines() ) ) ) ); ?></td></tr>
				<tr><td>Actieve AI-assistenten</td><td><?php echo esc_html( implode( ', ', array_map( static fn( $e ) => bv_gi_ai_engines()[ $e ]['label'] . ( 'openrouter' === bv_gi_ai_via( $e ) ? ' (via OpenRouter)' : '' ), bv_gi_ai_active() ) ) ?: 'geen' ); ?></td></tr>
				<?php foreach ( (array) ( $work['errors'] ?? [] ) as $e => $err ) : ?>
					<tr><td>Laatste fout <?php echo esc_html( bv_gi_ai_engines()[ $e ]['label'] ?? $e ); ?></td><td><?php echo esc_html( $err ); ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
