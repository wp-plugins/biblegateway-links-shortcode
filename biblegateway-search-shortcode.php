<?php
/*
Plugin Name: BibleGateway Links Shortcode
Plugin URI: http://dsgnwrks.pro/plugins/biblegateway-search-shortcode
Description: Shortcode for linking Bible references to a BibleGateway page. Links open in a small popup window. Adds a convenient editor button for inserting the shortcode.
Author URI: http://dsgnwrks.pro
Author: Justin Sternberg
Donate link: http://dsgnwrks.pro/give/
Version: 0.1.0
*/

class DsgnWrks_Bible_Gateway_Shortcode {

    public $btn = 'bgbible';

    public function __construct() {
        add_action( 'admin_init', array( $this, 'init' )  );
        add_action( 'admin_footer', array( $this, 'quicktag_button_script' )  );
        add_action( 'admin_enqueue_scripts', array( $this, 'tinymce_button_script' )  );
        add_shortcode( 'biblegateway', array( $this, 'bgsearch' )  );
    }

    public function init() {
        add_filter( 'mce_external_plugins', array( $this, 'add_buttons' )  );
        add_filter( 'mce_buttons', array( $this, 'register_buttons' )  );
    }

    public function add_buttons( $plugin_array ) {
        $plugin_array[$this->btn] = plugins_url( '/register-bg.js', __FILE__ );
        return $plugin_array;
    }

    public function register_buttons( $buttons ) {
        array_push( $buttons, $this->btn );
        return $buttons;
    }

    public function quicktag_button_script() {
        $current = get_current_screen();

        if ( !isset( $current->parent_base ) || $current->parent_base != 'edit' )
            return;
        wp_enqueue_script( $this->btn );
    }

    public function tinymce_button_script() {
        wp_register_script( $this->btn, plugins_url( '/register-bg-html.js', __FILE__ ) , array( 'quicktags' ), '0.1.0', true );
        wp_localize_script( $this->btn, $this->btn, array(
            'passage_text' => __( 'Scripture Reference:', 'bgbible' ),
            'display_text' => __( 'Optional replacement display text (i.e. "vs. 22"):', 'bgbible' ),
        ) );
    }

    public static function bgsearch( $attr ) {
        if ( !isset( $attr['passage'] ) )
            return;

        $display = isset( $attr['display'] ) ? $attr['display'] : $attr['passage'];
        return '<a class="bible-gateway" href="http://biblegateway.com/passage/?search='. urlencode( $attr['passage'] ) .'&version=NIV" onclick="return !window.open(this.href, \''. esc_js( $attr['passage'] ) .'\', \'width=800,height=950\')" target="_blank">'. esc_html( $display ) .'</a>';
    }
}
new DsgnWrks_Bible_Gateway_Shortcode();
