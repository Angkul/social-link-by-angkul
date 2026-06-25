<?php
/**
 * Plugin Name:       Social Link by Angkul
 * Description:       Floating Action Button with expandable social/contact menu.
 * Version:           1.0.0
 * Author:            Angkul
 * Author URI:        https://www.ehowme.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       social-link-by-angkul
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SLA_VERSION', '1.0.0' );
define( 'SLA_DIR',     plugin_dir_path( __FILE__ ) );
define( 'SLA_URL',     plugin_dir_url( __FILE__ ) );

// ── Text domain ───────────────────────────────────────────────────────────────
add_action( 'init', 'sla_load_textdomain' );
function sla_load_textdomain() {
    load_plugin_textdomain( 'social-link-by-angkul', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// ── Multilingual helper (Polylang / WPML) ─────────────────────────────────────
function sla_translate_string( $string, $name ) {
    if ( empty( $string ) ) return $string;
    // Polylang
    if ( function_exists( 'pll__' ) ) {
        return pll__( $string );
    }
    // WPML
    return apply_filters( 'wpml_translate_single_string', $string, 'social-link-by-angkul', $name );
}

function sla_register_multilingual_strings( $settings ) {
    $strings = array(
        'Button title'    => isset( $settings['btn_title'] )    ? $settings['btn_title']    : '',
        'Button subtitle' => isset( $settings['btn_subtitle'] ) ? $settings['btn_subtitle'] : '',
    );
    $items = isset( $settings['items'] ) ? $settings['items'] : array();
    foreach ( $items as $i => $item ) {
        if ( ! empty( $item['label'] ) ) {
            $strings[ 'Item ' . ( $i + 1 ) . ' label' ] = $item['label'];
        }
    }
    foreach ( $strings as $name => $value ) {
        if ( empty( $value ) ) continue;
        // Polylang
        if ( function_exists( 'pll_register_string' ) ) {
            pll_register_string( $name, $value, 'Social Link by Angkul' );
        }
        // WPML
        do_action( 'wpml_register_single_string', 'social-link-by-angkul', $name, $value );
    }
}

require_once SLA_DIR . 'includes/settings.php';
require_once SLA_DIR . 'includes/frontend.php';

// ── Settings link on Plugins page ────────────────────────────────────────────
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'sla_action_links' );
function sla_action_links( $links ) {
    $url = admin_url( 'options-general.php?page=social-link-angkul' );
    array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'social-link-by-angkul' ) . '</a>' );
    return $links;
}

register_activation_hook( __FILE__, 'sla_activate' );
function sla_activate() {
    $defaults = array(
        'btn_title'    => 'Book appointment',
        'btn_subtitle' => 'Reply within 2 hours',
        'color_start'  => '#0066FF',
        'color_end'    => '#002D73',
        'position'     => 'right',
        'bottom'       => '24',
        'items'        => array(),
    );
    add_option( 'sla_settings', $defaults );
}
