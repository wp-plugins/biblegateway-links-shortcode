<?php
/*
Plugin Name: BibleGateway Links Shortcode
Plugin URI: http://dsgnwrks.pro/plugins/biblegateway-search-shortcode
Description: Shortcode for linking Bible references to a BibleGateway page. Links open in a small popup window. Adds a convenient editor button for inserting the shortcode.
Author URI: http://dsgnwrks.pro
Author: Justin Sternberg
Donate link: http://dsgnwrks.pro/give/
Version: 0.1.2
*/

class DsgnWrks_Bible_Gateway_Shortcode {

	public static $btn         = 'bgbible';
	public static $name        = 'BibleGateway Links Shortcode';
	public static $version     = 'niv';
	public static $opts        = array();
	public static $url_pattern = false;
	public static $services    = array(
		'biblegateway' => array(
			'pattern' => 'http://biblegateway.com/passage/?search=%s&version=%s',
			'name'    => 'BibleGateway',
			'size'    => 'width=800,height=950',
		),
		'mobilebiblegateway' => array(
			'pattern' => 'http://mobile.biblegateway.com/passage/?search=%s&version=%s',
			'name'    => 'Mobile BibleGateway',
			'size'    => 'width=600,height=750',
		),
		'youversion'   => array(
			'pattern' => 'https://www.bible.com/search?q=%s.%s',
			'name'    => 'YouVersion',
		),
	);
	public static $versions    = array(
		'niv' => array(
			'youversion'   => 'niv',
			'biblegateway' => 'NIV',
		),
		'kjv' => array(
			'youversion'   => 'kjv',
			'biblegateway' => 'KJV',
		),
		'esv' => array(
			'youversion'   => 'esv',
			'biblegateway' => 'ESV',
		),
	);

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'settings' ) );
		add_action( 'admin_footer', array( $this, 'quicktag_button_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'tinymce_button_script' ) );
		add_shortcode( 'biblegateway', array( $this, 'bgsearch' ) );
	}

	public function init() {
		add_filter( 'mce_external_plugins', array( $this, 'add_buttons' ) );
		add_filter( 'mce_buttons', array( $this, 'register_buttons' ) );

		register_setting( self::$btn, self::$btn );
		add_settings_section( 'bg_setting_section', '', '__return_null', self::$btn );
		add_settings_field( 'bg_set_version', __( 'Select Bible Version', 'dsgnwrks' ), array( $this, 'bg_set_version' ), self::$btn, 'bg_setting_section' );
		add_settings_field( 'bg_set_service', __( 'Select Bible Search Service', 'dsgnwrks' ), array( $this, 'bg_set_service' ), self::$btn, 'bg_setting_section' );
	}

	/**
	 * Sets up our custom settings page
	 * @since  1.0.0
	 */
	public function settings() {
		// create admin page
		add_submenu_page( 'options-general.php', self::$name, self::$name, 'manage_network_options', self::$btn, array( $this, 'settings_page' ) );
	}

	/**
	 * Our admin page
	 * @since  1.0.0
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php echo self::$name; ?></h2>
			<form class="" method="post" action="options.php">
				<?php settings_fields( self::$btn ); ?>
				<?php do_settings_sections( self::$btn ); ?>
				<p class="submit">
					<input name="submit" type="submit" class="button-primary" value="<?php _e( 'Save' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	public function bg_set_version() {
		?>
		<select name="<?php echo self::$btn; ?>[version]">
			<?php
			foreach ( self::$versions as $key => $value ) {
				echo '<option value="'. $key .'"'. selected( self::opts( 'version' ), $key, false ) .'>'. $value['biblegateway'] .'</option>';
			}
			?>
		</select>
		<?php
	}

	public function bg_set_service() {
		?>
		<select name="<?php echo self::$btn; ?>[service]">
			<?php
			foreach ( self::$services as $key => $value ) {
				echo '<option value="'. $key .'"'. selected( self::opts( 'service' ), $key, false ) .'>'. $value['name'] .'</option>';
			}
			?>
		</select>
		<?php
	}

	public static function opts( $index = '' ) {
		self::$opts = !empty( self::$opts ) ? self::$opts : get_option( self::$btn );

		if ( $index ) {
			$opt = isset( self::$opts[$index] ) ? self::$opts[$index] : false;
			if ( !$opt ) {
				if ( $index == 'service' ) {
					$opt = 'biblegateway';
				} elseif ( $index == 'version' ) {
					$opt = 'niv';
				}
			}
			return $opt;
		}

		return self::$opts;
	}

	public function add_buttons( $plugin_array ) {
		$plugin_array[ self::$btn ] = plugins_url( '/register-bg.js', __FILE__ );
		return $plugin_array;
	}

	public function register_buttons( $buttons ) {
		array_push( $buttons, self::$btn );
		return $buttons;
	}

	public function quicktag_button_script() {
		$current = get_current_screen();

		if ( !isset( $current->parent_base ) || $current->parent_base != 'edit' )
			return;
		wp_enqueue_script( self::$btn );
	}

	public function tinymce_button_script() {
		wp_register_script( self::$btn, plugins_url( '/register-bg-html.js', __FILE__ ) , array( 'quicktags' ), '0.1.0', true );
		wp_localize_script( self::$btn, self::$btn, array(
			'passage_text' => __( 'Scripture Reference:', 'bgbible' ),
			'display_text' => __( 'Optional replacement display text (i.e. "vs. 22"):', 'bgbible' ),
		) );
	}

	public static function service_link() {
		if ( self::$url_pattern !== false )
			return self::$url_pattern;

		// Allow plugins/themes to override the service
		$service = apply_filters( 'dsgnwrks_bible_service', self::opts( 'service' ), self::$services );
		// Allow plugins/themes to override/add with their own service url patterns
		self::$url_pattern = apply_filters( 'dsgnwrks_bible_service_url_pattern', self::$services[ $service ]['pattern'], $service, self::$services, self::opts() );
		return self::$url_pattern;
	}

	public static function version() {
		$version = isset( self::$versions[ self::opts( 'version' ) ][ self::opts( 'service' ) ] )
			? self::$versions[ self::opts( 'version' ) ][ self::opts( 'service' ) ]
			: 'niv';
		return apply_filters( 'dsgnwrks_bible_version', $version );
	}

	public static function bgsearch( $attr ) {
		if ( !isset( $attr['passage'] ) )
			return;

		$size = isset( self::$services[ self::opts( 'service' ) ]['size'] )
			? self::$services[ self::opts( 'service' ) ]['size']
			: 'width=925,height=950';
		$query = urlencode( $attr['passage'] );
		$search = sprintf( self::service_link(), $query, self::version() );
		$display = isset( $attr['display'] ) ? $attr['display'] : $attr['passage'];
		return sprintf( '<a class="bible-gateway" href="%s" onclick="return !window.open(this.href, \'%s\', \'%s\')" target="_blank">%s</a>', $search, esc_js( $attr['passage'] ), $size, esc_html( $display ) );
	}
}
new DsgnWrks_Bible_Gateway_Shortcode();
