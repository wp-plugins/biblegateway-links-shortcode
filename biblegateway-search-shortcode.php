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
	public static $version     = 'niv';
	public static $url_pattern = false;
	public static $services    = array(
		'biblegateway' => 'http://biblegateway.com/passage/?search=%s&version=%s',
		'youversion'   => 'https://www.bible.com/search?q=%s.%s',
	);

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_footer', array( $this, 'quicktag_button_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'tinymce_button_script' ) );
		add_shortcode( 'biblegateway', array( $this, 'bgsearch' ) );
	}

	public function init() {
		add_filter( 'mce_external_plugins', array( $this, 'add_buttons' ) );
		add_filter( 'mce_buttons', array( $this, 'register_buttons' ) );
		// Allow plugins/themes to override the Bible version
		self::$version = apply_filters( 'dsgnwrks_bible_version', self::$version, self::$services );
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

	public static function service() {
		if ( self::$url_pattern !== false )
			return self::$url_pattern;

		// Allow plugins/themes to override the service
		$service = apply_filters( 'dsgnwrks_bible_service', 'biblegateway', self::$services );
		// Allow plugins/themes to override/add with their own service url patterns
		self::$url_pattern = apply_filters( 'dsgnwrks_bible_service_url_pattern', self::$services[ $service ], $service, self::$services );
		return self::$url_pattern;
	}

	public static function bgsearch( $attr ) {
		if ( !isset( $attr['passage'] ) )
			return;

		$query = urlencode( $attr['passage'] );
		$search = sprintf( self::service(), $query, self::$version );
		$display = isset( $attr['display'] ) ? $attr['display'] : $attr['passage'];
		return sprintf( '<a class="bible-gateway" href="%s" onclick="return !window.open(this.href, \'%s\', \'width=925,height=950\')" target="_blank">%s</a>', $search, esc_js( $attr['passage'] ), esc_html( $display ) );
	}
}
new DsgnWrks_Bible_Gateway_Shortcode();
