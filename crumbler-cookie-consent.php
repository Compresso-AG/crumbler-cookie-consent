<?php
/**
 * Plugin Name:       Crumbler – Cookie Consent
 * Plugin URI:        https://crumbler.ch
 * Description:       Connects your website to the Crumbler cookie consent service: consent banner, automatic script & iframe blocking, cookie declaration and Google Consent Mode v2.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Compresso AG
 * Author URI:        https://compresso.ch
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       crumbler-cookie-consent
 * Domain Path:       /languages
 *
 * @package Crumbler_Cookie_Consent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * Registers the settings page, enqueues the Crumbler widget script and
 * provides the cookie declaration block and shortcode.
 */
class Crumbler_Cookie_Consent {

	const OPTION_PREFIX = 'crumbler_cc_';
	const SERVICE_URL   = 'https://cmp.compresso.ch';
	const WIDGET_URL    = 'https://cmp.compresso.ch/widget/cmp.min.js';
	const VERSION       = '1.0.0';

	/**
	 * Hook suffix of the settings page (used to scope admin assets).
	 *
	 * @var string
	 */
	private $settings_hook = '';

	/**
	 * Wire up the WordPress hooks used by the plugin.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_widget_script' ), 1 );
		add_action( 'init', array( $this, 'register_block_and_shortcode' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add settings page under Settings menu
	 */
	public function add_settings_page() {
		$this->settings_hook = add_options_page(
			'Crumbler',
			'Crumbler',
			'manage_options',
			'crumbler-cookie-consent',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue the branded admin stylesheet, but only on this plugin's settings page.
	 *
	 * @param string $hook The current admin page hook suffix.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( $this->settings_hook !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'crumbler-cookie-consent-admin',
			plugins_url( 'assets/admin.css', __FILE__ ),
			array(),
			self::VERSION
		);
	}

	/**
	 * Add quick link to settings from plugins page.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=crumbler-cookie-consent' ) . '">' . esc_html__( 'Settings', 'crumbler-cookie-consent' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Register all settings
	 */
	public function register_settings() {
		// Section: General.
		add_settings_section(
			'crumbler_cc_general',
			__( 'General Settings', 'crumbler-cookie-consent' ),
			array( $this, 'render_section_general' ),
			'crumbler-cookie-consent'
		);

		// Field: Enabled.
		register_setting(
			'crumbler-cookie-consent',
			self::OPTION_PREFIX . 'enabled',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		add_settings_field(
			self::OPTION_PREFIX . 'enabled',
			__( 'Enable Widget', 'crumbler-cookie-consent' ),
			array( $this, 'render_field_enabled' ),
			'crumbler-cookie-consent',
			'crumbler_cc_general'
		);

		// Field: Site Key.
		register_setting(
			'crumbler-cookie-consent',
			self::OPTION_PREFIX . 'site_key',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_site_key' ),
			)
		);
		add_settings_field(
			self::OPTION_PREFIX . 'site_key',
			__( 'Site Key', 'crumbler-cookie-consent' ),
			array( $this, 'render_field_site_key' ),
			'crumbler-cookie-consent',
			'crumbler_cc_general'
		);

		// Field: Language.
		register_setting(
			'crumbler-cookie-consent',
			self::OPTION_PREFIX . 'language',
			array(
				'type'              => 'string',
				'default'           => 'auto',
				'sanitize_callback' => array( $this, 'sanitize_language' ),
			)
		);
		add_settings_field(
			self::OPTION_PREFIX . 'language',
			__( 'Language', 'crumbler-cookie-consent' ),
			array( $this, 'render_field_language' ),
			'crumbler-cookie-consent',
			'crumbler_cc_general'
		);

		// Section: Advanced.
		add_settings_section(
			'crumbler_cc_advanced',
			__( 'Advanced Settings', 'crumbler-cookie-consent' ),
			array( $this, 'render_section_advanced' ),
			'crumbler-cookie-consent'
		);

		// Field: Custom Widget URL.
		register_setting(
			'crumbler-cookie-consent',
			self::OPTION_PREFIX . 'widget_url',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		add_settings_field(
			self::OPTION_PREFIX . 'widget_url',
			__( 'Widget URL', 'crumbler-cookie-consent' ),
			array( $this, 'render_field_widget_url' ),
			'crumbler-cookie-consent',
			'crumbler_cc_advanced'
		);

		// Field: Disable for logged-in users.
		register_setting(
			'crumbler-cookie-consent',
			self::OPTION_PREFIX . 'hide_for_admins',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		add_settings_field(
			self::OPTION_PREFIX . 'hide_for_admins',
			__( 'Hide for Admins', 'crumbler-cookie-consent' ),
			array( $this, 'render_field_hide_for_admins' ),
			'crumbler-cookie-consent',
			'crumbler_cc_advanced'
		);
	}

	/**
	 * Sanitize site key (must be UUID format).
	 *
	 * @param string $value The submitted site key value.
	 * @return string The sanitized site key, or the previous value if invalid.
	 */
	public function sanitize_site_key( $value ) {
		$value = sanitize_text_field( trim( $value ) );
		if ( $value && ! preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value ) ) {
			add_settings_error(
				self::OPTION_PREFIX . 'site_key',
				'invalid_site_key',
				__( 'The Site Key must be in UUID format (e.g. af232e06-59c2-4810-b09d-7a2b25632d1b).', 'crumbler-cookie-consent' )
			);
			return get_option( self::OPTION_PREFIX . 'site_key', '' );
		}
		return $value;
	}

	/**
	 * Sanitize language selection.
	 *
	 * @param string $value The submitted language value.
	 * @return string A valid language code, or 'auto' as fallback.
	 */
	public function sanitize_language( $value ) {
		$allowed = array( 'auto', 'de', 'fr', 'it', 'en' );
		return in_array( $value, $allowed, true ) ? $value : 'auto';
	}

	// =========================================================================
	// Render: Sections
	// =========================================================================

	/**
	 * Render the description for the General settings section.
	 */
	public function render_section_general() {
		echo '<p>' . esc_html__( 'Connect your website to the Crumbler cookie consent service.', 'crumbler-cookie-consent' ) . '</p>';
	}

	/**
	 * Render the description for the Advanced settings section.
	 */
	public function render_section_advanced() {
		echo '<p>' . esc_html__( 'Optional settings for special requirements.', 'crumbler-cookie-consent' ) . '</p>';
	}

	// =========================================================================
	// Render: Fields
	// =========================================================================

	/**
	 * Render the "Enable Widget" checkbox field.
	 */
	public function render_field_enabled() {
		$value = get_option( self::OPTION_PREFIX . 'enabled', false );
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( self::OPTION_PREFIX . 'enabled' ) . '" value="1" ' . checked( 1, $value, false ) . '>';
		echo ' ' . esc_html__( 'Show Cookie Consent Widget on the website', 'crumbler-cookie-consent' );
		echo '</label>';
	}

	/**
	 * Render the "Site Key" text field.
	 */
	public function render_field_site_key() {
		$value = get_option( self::OPTION_PREFIX . 'site_key', '' );
		echo '<input type="text" name="' . esc_attr( self::OPTION_PREFIX . 'site_key' ) . '" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="af232e06-59c2-4810-b09d-7a2b25632d1b">';
		echo '<p class="description">' . sprintf(
			/* translators: %s: link to Crumbler Dashboard */
			esc_html__( 'You can find the Site Key in the %s under the respective site.', 'crumbler-cookie-consent' ),
			'<a href="https://cmp.compresso.ch" target="_blank">Crumbler Dashboard</a>'
		) . '</p>';
	}

	/**
	 * Render the "Language" select field.
	 */
	public function render_field_language() {
		$value   = get_option( self::OPTION_PREFIX . 'language', 'auto' );
		$options = array(
			'auto' => __( 'Automatic (browser language)', 'crumbler-cookie-consent' ),
			'de'   => 'Deutsch',
			'fr'   => 'Fran&ccedil;ais',
			'it'   => 'Italiano',
			'en'   => 'English',
		);
		echo '<select name="' . esc_attr( self::OPTION_PREFIX . 'language' ) . '">';
		foreach ( $options as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'When set to "Automatic", the widget detects the visitor\'s browser language.', 'crumbler-cookie-consent' ) . '</p>';
	}

	/**
	 * Render the "Widget URL" text field.
	 */
	public function render_field_widget_url() {
		$value = get_option( self::OPTION_PREFIX . 'widget_url', '' );
		echo '<input type="url" name="' . esc_attr( self::OPTION_PREFIX . 'widget_url' ) . '" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . esc_attr( self::WIDGET_URL ) . '">';
		echo '<p class="description">' . esc_html__( 'Only change if you run your own widget instance. Leave empty for default.', 'crumbler-cookie-consent' ) . '</p>';
	}

	/**
	 * Render the "Hide for Admins" checkbox field.
	 */
	public function render_field_hide_for_admins() {
		$value = get_option( self::OPTION_PREFIX . 'hide_for_admins', false );
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( self::OPTION_PREFIX . 'hide_for_admins' ) . '" value="1" ' . checked( 1, $value, false ) . '>';
		echo ' ' . esc_html__( 'Hide widget for logged-in administrators', 'crumbler-cookie-consent' );
		echo '</label>';
	}

	// =========================================================================
	// Service status check
	// =========================================================================

	/**
	 * Query the Crumbler service for the real status of this site/domain.
	 *
	 * The result is cached in a transient so we do not hit the service on every
	 * settings page load. Returns an array with a 'state' of:
	 *   active        – domain is set up and active (widget is served)
	 *   inactive      – site key unknown or domain not active (HTTP 404)
	 *   host_mismatch – domain not authorised for this site key (HTTP 403)
	 *   unknown       – service unreachable / could not verify
	 *
	 * @return array
	 */
	private function get_service_status() {
		$site_key = get_option( self::OPTION_PREFIX . 'site_key', '' );
		$host     = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( empty( $site_key ) || empty( $host ) ) {
			return array(
				'state' => 'unknown',
				'host'  => (string) $host,
			);
		}

		$cache_key = 'crumbler_cc_status_' . md5( $site_key . '|' . $host );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$url      = $this->get_api_base_url() . '/api/public/config?siteKey=' . rawurlencode( $site_key ) . '&host=' . rawurlencode( $host );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 5,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			$status = array(
				'state' => 'unknown',
				'host'  => $host,
			);
		} else {
			$code = (int) wp_remote_retrieve_response_code( $response );
			if ( 200 === $code ) {
				$body   = json_decode( wp_remote_retrieve_body( $response ), true );
				$status = array(
					'state'     => 'active',
					'host'      => $host,
					'site_name' => ( is_array( $body ) && ! empty( $body['site_name'] ) ) ? $body['site_name'] : '',
				);
			} elseif ( 403 === $code ) {
				$status = array(
					'state' => 'host_mismatch',
					'host'  => $host,
				);
			} elseif ( 404 === $code ) {
				$status = array(
					'state' => 'inactive',
					'host'  => $host,
				);
			} else {
				$status = array(
					'state' => 'unknown',
					'host'  => $host,
				);
			}
		}

		set_transient( $cache_key, $status, 5 * MINUTE_IN_SECONDS );
		return $status;
	}

	/**
	 * Flush the cached service status when the user clicks "Re-check now".
	 */
	private function maybe_flush_status_cache() {
		if ( ! isset( $_GET['crumbler_recheck'] ) ) {
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'crumbler_cc_recheck' ) ) {
			return;
		}
		$site_key = get_option( self::OPTION_PREFIX . 'site_key', '' );
		$host     = wp_parse_url( home_url(), PHP_URL_HOST );
		delete_transient( 'crumbler_cc_status_' . md5( $site_key . '|' . $host ) );
	}

	// =========================================================================
	// Render: Settings Page
	// =========================================================================

	/**
	 * Render the plugin settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->maybe_flush_status_cache();

		$site_key = get_option( self::OPTION_PREFIX . 'site_key', '' );
		$enabled  = get_option( self::OPTION_PREFIX . 'enabled', false );
		?>
		<div class="wrap crumbler-cc-wrap">
			<h1 class="screen-reader-text">Crumbler &ndash; Cookie Consent</h1>

			<div class="crumbler-cc-header">
				<img class="crumbler-cc-header__logo" src="<?php echo esc_url( plugins_url( 'assets/crumbler-logo.svg', __FILE__ ) ); ?>" alt="" width="52" height="52">
				<div class="crumbler-cc-header__text">
					<span class="crumbler-cc-header__wordmark">CRUMBLER</span>
					<span class="crumbler-cc-header__tagline"><?php esc_html_e( 'Cookie Consent', 'crumbler-cookie-consent' ); ?></span>
				</div>
			</div>

			<?php settings_errors(); ?>

			<?php if ( $enabled && empty( $site_key ) ) : ?>
				<div class="notice notice-warning">
					<p><strong><?php esc_html_e( 'Warning:', 'crumbler-cookie-consent' ); ?></strong> <?php esc_html_e( 'The widget is enabled but no Site Key has been entered. The widget will not work without a Site Key.', 'crumbler-cookie-consent' ); ?></p>
				</div>
				<?php
			elseif ( $enabled && ! empty( $site_key ) ) :
				$status  = $this->get_service_status();
				$recheck = wp_nonce_url(
					add_query_arg(
						array(
							'page'             => 'crumbler-cookie-consent',
							'crumbler_recheck' => '1',
						),
						admin_url( 'options-general.php' )
					),
					'crumbler_cc_recheck'
				);
				?>
				<?php if ( 'active' === $status['state'] ) : ?>
					<div class="notice notice-success">
						<p>
						<?php
						if ( ! empty( $status['site_name'] ) ) {
							printf(
								/* translators: 1: site name, 2: domain */
								esc_html__( 'Connected to Crumbler: %1$s is set up and active for %2$s. The widget is being served.', 'crumbler-cookie-consent' ),
								'<strong>' . esc_html( $status['site_name'] ) . '</strong>',
								'<code>' . esc_html( $status['host'] ) . '</code>'
							);
						} else {
							printf(
								/* translators: %s: domain */
								esc_html__( 'Connected to Crumbler: this domain (%s) is set up and active. The widget is being served.', 'crumbler-cookie-consent' ),
								'<code>' . esc_html( $status['host'] ) . '</code>'
							);
						}
						?>
						</p>
					</div>
				<?php elseif ( 'host_mismatch' === $status['state'] ) : ?>
					<div class="notice notice-warning">
						<p>
						<?php
							printf(
								/* translators: %s: domain */
								esc_html__( 'This domain (%s) is not authorised for the entered Site Key. Add it to the allowed domains of this site in your Crumbler dashboard. Until then the widget will not be displayed.', 'crumbler-cookie-consent' ),
								'<code>' . esc_html( $status['host'] ) . '</code>'
							);
						?>
						<a href="<?php echo esc_url( $recheck ); ?>"><?php esc_html_e( 'Re-check now', 'crumbler-cookie-consent' ); ?></a></p>
					</div>
				<?php elseif ( 'inactive' === $status['state'] ) : ?>
					<div class="notice notice-warning">
						<p><?php esc_html_e( 'The Site Key is unknown to Crumbler, or this domain is not active yet (for example the subscription or trial is not set up). The widget will not be displayed until the domain is added and active in your Crumbler dashboard.', 'crumbler-cookie-consent' ); ?>
						<a href="<?php echo esc_url( $recheck ); ?>"><?php esc_html_e( 'Re-check now', 'crumbler-cookie-consent' ); ?></a></p>
					</div>
				<?php else : ?>
					<div class="notice notice-info">
						<p><?php esc_html_e( 'The Crumbler service status could not be verified right now (service unreachable). The widget script is still embedded on all pages.', 'crumbler-cookie-consent' ); ?>
						<a href="<?php echo esc_url( $recheck ); ?>"><?php esc_html_e( 'Re-check now', 'crumbler-cookie-consent' ); ?></a></p>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'crumbler-cookie-consent' );
				do_settings_sections( 'crumbler-cookie-consent' );
				submit_button( __( 'Save Settings', 'crumbler-cookie-consent' ) );
				?>
			</form>

			<?php if ( ! empty( $site_key ) ) : ?>
				<h2><?php esc_html_e( 'Integration Code', 'crumbler-cookie-consent' ); ?></h2>
				<div class="crumbler-cc-card">
					<p><?php esc_html_e( 'The plugin automatically adds the following code to the <head> section:', 'crumbler-cookie-consent' ); ?></p>
					<pre><code>&lt;script src="<?php echo esc_html( $this->get_widget_url() ); ?>"&gt;&lt;/script&gt;</code></pre>
				</div>

				<h2><?php esc_html_e( 'Cookie Declaration', 'crumbler-cookie-consent' ); ?></h2>
				<div class="crumbler-cc-card">
					<p><?php esc_html_e( 'The cookie declaration displays all detected services and cookies, grouped by category. You can embed it in two ways:', 'crumbler-cookie-consent' ); ?></p>

					<h3><?php esc_html_e( 'Gutenberg Block', 'crumbler-cookie-consent' ); ?></h3>
					<p>
					<?php
						printf(
							/* translators: %s: bold block name */
							esc_html__( 'Add the %s block via the block editor. The language can optionally be overridden in the block settings.', 'crumbler-cookie-consent' ),
							'<strong>&laquo;' . esc_html__( 'Cookie Declaration', 'crumbler-cookie-consent' ) . '&raquo;</strong>'
						);
					?>
					</p>

					<h3><?php esc_html_e( 'Shortcode', 'crumbler-cookie-consent' ); ?></h3>
					<p><?php esc_html_e( 'Use the following shortcode on any page or post:', 'crumbler-cookie-consent' ); ?></p>
					<pre><code>[crumbler_cookies]</code></pre>
					<p><?php esc_html_e( 'Optionally with a language parameter:', 'crumbler-cookie-consent' ); ?></p>
					<pre><code>[crumbler_cookies lang="fr"]</code></pre>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	// =========================================================================
	// Frontend: Output Widget Script
	// =========================================================================

	/**
	 * Enqueue the widget script in wp_head.
	 */
	public function enqueue_widget_script() {
		// Check if enabled.
		if ( ! get_option( self::OPTION_PREFIX . 'enabled', false ) ) {
			return;
		}

		// Check site key.
		$site_key = get_option( self::OPTION_PREFIX . 'site_key', '' );
		if ( empty( $site_key ) ) {
			return;
		}

		// Hide for admins if configured.
		if ( get_option( self::OPTION_PREFIX . 'hide_for_admins', false ) && current_user_can( 'manage_options' ) ) {
			return;
		}

		// Don't load in admin area.
		if ( is_admin() ) {
			return;
		}

		$url = $this->get_widget_url();
		wp_enqueue_script( 'crumbler-cookie-consent-widget', $url, array(), '1.0.0', false );
	}

	/**
	 * Build the full widget URL with parameters.
	 *
	 * @return string The widget URL including query parameters.
	 */
	private function get_widget_url() {
		$base_url = get_option( self::OPTION_PREFIX . 'widget_url', '' );
		if ( empty( $base_url ) ) {
			$base_url = self::WIDGET_URL;
		}

		$params = array(
			'key' => get_option( self::OPTION_PREFIX . 'site_key', '' ),
		);

		$language = get_option( self::OPTION_PREFIX . 'language', 'auto' );
		if ( 'auto' !== $language ) {
			$params['lang'] = $language;
		}

		return $base_url . '?' . http_build_query( $params );
	}

	// =========================================================================
	// Cookie Declaration: Gutenberg Block + Shortcode
	// =========================================================================

	/**
	 * Register Gutenberg block and shortcode for cookie declaration.
	 */
	public function register_block_and_shortcode() {
		// Shortcode example: crumbler_cookies, optionally with a lang attribute.
		add_shortcode( 'crumbler_cookies', array( $this, 'render_cookie_declaration' ) );

		// Frontend script for the cookie declaration (shared by block + shortcode).
		wp_register_script(
			'crumbler-cookie-consent-cookie-declaration',
			plugins_url( 'assets/cookie-declaration.js', __FILE__ ),
			array(),
			'1.0.0',
			true
		);

		// Gutenberg block (only if the block editor is available).
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Registered from block.json metadata (modern block registration).
		register_block_type(
			__DIR__ . '/blocks/cookie-declaration',
			array(
				'render_callback' => array( $this, 'render_cookie_declaration' ),
			)
		);

		// Load translations for the editor script registered via block.json.
		wp_set_script_translations(
			'crumbler-cookie-declaration-editor-script',
			'crumbler-cookie-consent',
			plugin_dir_path( __FILE__ ) . 'languages'
		);
	}

	/**
	 * Render callback for both shortcode and Gutenberg block.
	 *
	 * The shortcode accepts an optional lang attribute; the block passes an
	 * attributes array with an optional 'lang' key.
	 *
	 * @param array $atts Shortcode or block attributes.
	 * @return string The cookie declaration markup.
	 */
	public function render_cookie_declaration( $atts = array() ) {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}
		$atts = shortcode_atts( array( 'lang' => '' ), $atts );

		$site_key = get_option( self::OPTION_PREFIX . 'site_key', '' );
		if ( empty( $site_key ) ) {
			return '<!-- Crumbler: No Site Key configured -->';
		}

		$lang    = $this->resolve_cookie_declaration_language( $atts['lang'] );
		$api_url = $this->get_api_base_url() . '/api/public/cookies';

		// Enqueue frontend script.
		wp_enqueue_script( 'crumbler-cookie-consent-cookie-declaration' );

		$declaration_url = $this->get_api_base_url() . '/cookies?key=' . rawurlencode( $site_key ) . '&lang=' . rawurlencode( $lang );

		$output  = '<div class="crumbler-cookies"';
		$output .= ' data-site-key="' . esc_attr( $site_key ) . '"';
		$output .= ' data-lang="' . esc_attr( $lang ) . '"';
		$output .= ' data-api-url="' . esc_attr( $api_url ) . '">';
		$output .= '<noscript><a href="' . esc_url( $declaration_url ) . '">' . esc_html__( 'View Cookie Declaration', 'crumbler-cookie-consent' ) . '</a></noscript>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Resolve the language for the cookie declaration.
	 *
	 * @param string $override Optional language code that takes precedence.
	 * @return string A valid language code.
	 */
	private function resolve_cookie_declaration_language( $override = '' ) {
		$allowed = array( 'de', 'fr', 'it', 'en' );

		if ( ! empty( $override ) && in_array( $override, $allowed, true ) ) {
			return $override;
		}

		$lang = get_option( self::OPTION_PREFIX . 'language', 'auto' );
		if ( 'auto' !== $lang && in_array( $lang, $allowed, true ) ) {
			return $lang;
		}

		// Auto-detect from WordPress locale.
		$wp_lang = substr( get_locale(), 0, 2 );
		if ( in_array( $wp_lang, $allowed, true ) ) {
			return $wp_lang;
		}

		return 'de';
	}

	/**
	 * Get the CMP base URL (derived from custom widget URL or default).
	 *
	 * @return string The API base URL.
	 */
	private function get_api_base_url() {
		$widget_url = get_option( self::OPTION_PREFIX . 'widget_url', '' );
		if ( empty( $widget_url ) ) {
			return self::SERVICE_URL;
		}

		$parsed = wp_parse_url( $widget_url );
		if ( ! $parsed || ! isset( $parsed['host'] ) ) {
			return self::SERVICE_URL;
		}

		$base = ( isset( $parsed['scheme'] ) ? $parsed['scheme'] : 'https' ) . '://' . $parsed['host'];
		if ( isset( $parsed['port'] ) ) {
			$base .= ':' . $parsed['port'];
		}

		return $base;
	}
}

// Initialize plugin.
new Crumbler_Cookie_Consent();
