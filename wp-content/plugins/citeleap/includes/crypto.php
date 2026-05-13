<?php
/**
 * CiteLeap , at-rest encryption for API keys.
 *
 * Why this exists:
 *   API keys stored plaintext in wp_options are a known production
 *   risk per WP plugin security 2026 baseline. If a DB backup leaks,
 *   the keys are immediately usable. AES-256-CBC with a key derived
 *   from WordPress salts (AUTH_KEY by default) keeps them encrypted
 *   at rest. The decryption key is in wp-config, NOT the database,
 *   so DB-only exfiltration cannot decrypt.
 *
 * Format:
 *   "cl1:<base64(iv)>:<base64(ciphertext)>"
 *
 * Backward compatibility:
 *   decrypt() returns plaintext unchanged for any value that does not
 *   start with the "cl1:" prefix. This lets v1.1.0 -> v1.2.0 upgrades
 *   keep working until the operator re-saves their keys, at which
 *   point they get migrated to the encrypted format.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Crypto {

	private const PREFIX = 'cl1:';

	public static function encrypt( string $plaintext ): string {
		if ( '' === $plaintext ) return '';
		if ( ! function_exists( 'openssl_encrypt' ) ) return $plaintext;
		$key = self::derive_key();
		$iv  = random_bytes( 16 );
		$ct  = openssl_encrypt( $plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
		if ( false === $ct ) return $plaintext;
		return self::PREFIX . base64_encode( $iv ) . ':' . base64_encode( $ct );
	}

	public static function decrypt( string $value ): string {
		if ( '' === $value )                       return '';
		if ( 0 !== strpos( $value, self::PREFIX ) ) return $value;        // legacy plaintext
		if ( ! function_exists( 'openssl_decrypt' ) ) return '';
		$parts = explode( ':', $value, 3 );
		if ( count( $parts ) !== 3 )               return '';
		$iv = base64_decode( (string) $parts[1], true );
		$ct = base64_decode( (string) $parts[2], true );
		if ( false === $iv || false === $ct )      return '';
		$key = self::derive_key();
		$pt  = openssl_decrypt( $ct, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
		return false === $pt ? '' : (string) $pt;
	}

	public static function mask( string $key ): string {
		$key = (string) $key;
		$len = strlen( $key );
		if ( 0 === $len )  return '';
		if ( $len <= 8 )   return str_repeat( '•', $len );
		return str_repeat( '•', max( 0, $len - 4 ) ) . substr( $key, -4 );
	}

	private static function derive_key(): string {
		/* Use AUTH_KEY (a 64-char salt in every WP install) as the
		 * derivation seed. If for any reason AUTH_KEY is empty, fall
		 * back to wp_salt('auth'). Always SHA-256 down to 32 bytes. */
		$seed = defined( 'AUTH_KEY' ) && AUTH_KEY ? (string) AUTH_KEY : (string) wp_salt( 'auth' );
		return hash( 'sha256', 'citeleap:' . $seed, true );
	}
}
