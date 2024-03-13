<?php

namespace Platonic\Admin;

/**
 * Class Admin_Notices
 *
 * @package Platonic\Admin
 * @author Gerard Reches
 */
class Admin_Notices {

	public const ERROR = 'error';
	public const WARNING = 'warning';
	public const SUCCESS = 'success';
	public const INFO = 'info';

	protected static string $transient_name;

	/**
	 * @param string $transient_name
	 */
	public static function initialize( string $transient_name = 'admin_notices' ): void {

		if ( has_action( 'admin_notices', array( static::class, 'display' ) ) ) {
			_doing_it_wrong( __METHOD__, sprintf( __( 'Class %s has already been initialized.' ), static::class ), '3.1.0' );

			return;
		}

		if ( ! is_string( $transient_name ) ) {
			_doing_it_wrong( __METHOD__, sprintf( __( '%s must be a valid string.' ), '$transient_name' ), '2.8.0' );

			return;
		}

		if ( strlen( $transient_name ) > 172 ) {
			_doing_it_wrong( __METHOD__, sprintf( __( '%s must be 172 characters or fewer in length.' ), '$transient_name' ), '4.4' );

			return;
		}

		static::$transient_name = $transient_name;

		add_action( 'admin_notices', array( static::class, 'display' ) );

		add_action( 'admin_enqueue_scripts', array( static::class, 'admin_enqueue_scripts' ) );

		add_action( 'wp_ajax_dismiss_admin_notice', array( static::class, 'dismiss' ) );
		add_action( 'wp_ajax_nopriv_dismiss_admin_notice', array( static::class, 'dismiss' ) );
	}

	public static function admin_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );

		$ajax_url   = admin_url( 'admin-ajax.php' );
		$ajax_nonce = wp_create_nonce( 'dismiss_admin_notice_nonce' );

		wp_add_inline_script(
			'jquery',
			"jQuery(document).ready(function($) {
				$('[data-notice-dismissible]').each(function(){
					var code = $(this).data('notice-code');
					$(this).on('click', 'button.notice-dismiss', function(event){
						$.ajax({
				            url : '{$ajax_url}',
				            type : 'post',
				            data : {
				                action : 'dismiss_admin_notice',
				                _ajax_nonce : '{$ajax_nonce}',
				                code : code
				            },
				            error : function( response ) {
				                console.log( response );
				            }
				        });
					});
				});
			});"
		);
	}

	public static function dismiss(): void {
		// Generates 403 error code if not met.
		check_ajax_referer( 'dismiss_admin_notice_nonce' );

		$code    = wp_unslash( $_POST['code'] );
		$removed = static::remove( $code );

		if ( $removed ) {
			wp_send_json_success( "Notice {$code} dismissed." );
		} else {
			wp_send_json_error( "Notice {$code} could not be dismissed." );
		}
	}

	/**
	 * Add an error admin notice.
	 *
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $persistent
	 * @param string|null $code
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_error( string $message, bool $dismissible = false, bool $unique = false, bool $persistent = false, string $code = null, bool $log = false ): void {
		static::add( self::ERROR, $message, $dismissible, $unique, $persistent, $code, $log );
	}

	/**
	 * Add a warning admin notice.
	 *
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $persistent
	 * @param string|null $code
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_warning( string $message, bool $dismissible = false, bool $unique = false, bool $persistent = false, string $code = null, bool $log = false ): void {
		static::add( self::WARNING, $message, $dismissible, $unique, $persistent, $code, $log );
	}

	/**
	 * Add a success admin notice.
	 *
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $persistent
	 * @param string|null $code
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_success( string $message, bool $dismissible = false, bool $unique = false, bool $persistent = false, string $code = null, bool $log = false ): void {
		static::add( self::SUCCESS, $message, $dismissible, $unique, $persistent, $code, $log );
	}

	/**
	 * Add an info admin notice.
	 *
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $persistent
	 * @param string|null $code
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_info( string $message, bool $dismissible = false, bool $unique = false, bool $persistent = false, string $code = null, bool $log = false ): void {
		static::add( self::INFO, $message, $dismissible, $unique, $persistent, $code, $log );
	}

	/**
	 * Add a persistent admin notice.
	 *
	 * @param string|null $code
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_persistent_error( ?string $code, string $message, bool $dismissible = true, bool $unique = true, bool $log = false ): void {
		static::add_persistent( self::ERROR, $code, $message, $dismissible, $unique, $log );
	}

	/**
	 * Add a persistent admin notice.
	 *
	 * @param string|null $code
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_persistent_warning( ?string $code, string $message, bool $dismissible = true, bool $unique = true, bool $log = false ): void {
		static::add_persistent( self::WARNING, $code, $message, $dismissible, $unique, $log );
	}

	/**
	 * Add a persistent admin notice.
	 *
	 * @param string|null $code
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_persistent_success( ?string $code, string $message, bool $dismissible = true, bool $unique = true, bool $log = false ): void {
		static::add_persistent( self::SUCCESS, $code, $message, $dismissible, $unique, $log );
	}

	/**
	 * Add a persistent admin notice.
	 *
	 * @param string|null $code
	 * @param string $message
	 * @param bool $dismissible
	 * @param bool $unique
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_persistent_info( ?string $code, string $message, bool $dismissible = true, bool $unique = true, bool $log = false ): void {
		static::add_persistent( self::INFO, $code, $message, $dismissible, $unique, $log );
	}

	/**
	 * Add a persistent admin notice.
	 *
	 * @param string $type Valid values are 'error', 'warning', 'success', and 'info'.
	 * @param string|null $code
	 * @param string $message The message to display in the notice.
	 * @param bool $dismissible Whether the notice should be dismissible.
	 * @param bool $unique Whether the notice should be unique. If false, duplicates may be displayed.
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add_persistent( string $type, ?string $code, string $message, bool $dismissible = true, bool $unique = true, bool $log = false ): void {
		static::add( $type, $message, $dismissible, $unique, true, $code, $log );
	}

	/**
	 * Add an admin notice.
	 *
	 * @param string $type Valid values are 'error', 'warning', 'success', and 'info'.
	 * @param string $message The message to display in the notice.
	 * @param bool $dismissible Whether the notice should be dismissible.
	 * @param bool $unique Whether the notice should be unique. If false, duplicates may be displayed.
	 * @param bool $persistent Whether the notice should be persistent. If true, the notice will not be cleared until removed manually.
	 * @param string|null $code A unique code to identify the notice.
	 * @param bool $log
	 *
	 * @return void
	 */
	public static function add( string $type, string $message, bool $dismissible = false, bool $unique = false, bool $persistent = false, string $code = null, bool $log = false ): void {

		if ( empty( $type ) ) {
			_doing_it_wrong( __METHOD__, __( 'No type was provided.' ), '1.0.0' );

			return;
		}

		if ( self::ERROR !== $type && self::WARNING !== $type && self::SUCCESS !== $type && self::INFO !== $type ) {
			_doing_it_wrong( __METHOD__, __( 'Wrong type. Only the following types are allowed: error, warning, success, and info.' ), '1.0.0' );

			return;
		}

		if ( empty( $message ) ) {
			_doing_it_wrong( __METHOD__, __( 'No message was provided.' ), '1.0.0' );

			return;
		}

		if ( $persistent && empty( $code ) ) {
			_doing_it_wrong( __METHOD__, __( 'Persistent notices must contain a code.' ), '1.0.0' );

			return;
		}

		$notices = get_transient( static::$transient_name );

		if ( false === $notices ) {
			$notices = array();
		}

		foreach ( $notices as $notice ) {
			if ( ! $notice['unique'] && ! $unique ) {
				continue;
			}

			if ( $notice['type'] === $type && $notice['message'] === $message && $notice['code'] === $code ) {
				return;
			}
		}

		$notices[] = array(
			'type'        => $type,
			'message'     => $message,
			'dismissible' => $dismissible,
			'unique'      => $unique,
			'persistent'  => $persistent,
			'code'        => $code,
		);

		set_transient( static::$transient_name, $notices );

		if ( $log ) {
			error_log( sprintf( '%s: %s', strtoupper( $type ), $message ) );
		}
	}

	/**
	 * Remove a notice by its code.
	 *
	 * @param string $code
	 *
	 * @return void
	 */
	public static function remove( string $code ): bool {
		$notices = get_transient( static::$transient_name );

		if ( false === $notices ) {
			return false;
		}

		$notices = array_filter( $notices, function ( $notice ) use ( $code ) {
			return $notice['code'] !== $code;
		} );

		return set_transient( static::$transient_name, $notices );
	}

	/**
	 * Display stored admin notices.
	 *
	 * @return void
	 */
	public static function display(): void {
		// Retrieve notices
		$notices = get_transient( static::$transient_name );

		// Display notices
		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				$classes = "notice notice-{$notice['type']}";
				if ( $notice['dismissible'] ) {
					$classes .= ' is-dismissible';
				}

				echo "<div data-notice-type='{$notice['type']}' data-notice-dismissible='{$notice['dismissible']}' data-notice-code='{$notice['code']}' class='{$classes}'><p>{$notice['message']}</p></div>";
			}
		}

		// Clear notices
		set_transient( static::$transient_name, array_filter( $notices, function ( $notice ) {
			return $notice['persistent'];
		} ) );
	}

	/**
	 * Handles my AJAX request.
	 */
	public static function my_ajax_handler() {
		// Handle the ajax request here

		wp_die(); // All ajax handlers die when finished
	}
}
