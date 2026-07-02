<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Enqueue admin scripts ─────────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', 'sla_admin_scripts' );
function sla_admin_scripts( $hook ) {
    if ( $hook !== 'settings_page_social-link-angkul' ) return;
    wp_enqueue_script( 'jquery-ui-sortable' );
}

// ── Admin menu ────────────────────────────────────────────────────────────────
add_action( 'admin_menu', 'sla_add_menu' );
function sla_add_menu() {
    add_options_page( 'Social Link by Angkul', 'Social Link by Angkul', 'manage_options', 'social-link-angkul', 'sla_settings_page' );
}

// ── Save ─────────────────────────────────────────────────────────────────────
add_action( 'admin_post_sla_save', 'sla_save' );
function sla_save() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    check_admin_referer( 'sla_save_nonce' );

    $icons         = isset( $_POST['sla_icon'] )         ? wp_unslash( $_POST['sla_icon'] )         : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $labels        = isset( $_POST['sla_label'] )        ? wp_unslash( $_POST['sla_label'] )        : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $urls          = isset( $_POST['sla_url'] )          ? wp_unslash( $_POST['sla_url'] )          : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $enabled       = isset( $_POST['sla_enabled'] )      ? wp_unslash( $_POST['sla_enabled'] )      : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $color_modes   = isset( $_POST['sla_color_mode'] )   ? wp_unslash( $_POST['sla_color_mode'] )   : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $colors        = isset( $_POST['sla_color'] )        ? wp_unslash( $_POST['sla_color'] )        : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $show_desktops = isset( $_POST['sla_show_desktop'] ) ? wp_unslash( $_POST['sla_show_desktop'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $show_mobiles  = isset( $_POST['sla_show_mobile'] )  ? wp_unslash( $_POST['sla_show_mobile'] )  : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

    $allowed_icons   = array_keys( sla_icon_data() );
    $allowed_cmodes  = array( 'default', 'brand', 'custom' );
    $allowed_schemes = array( 'http', 'https', 'tel', 'mailto' );

    $items = array();
    $icons = array_slice( $icons, 0, 8, true ); // max 8 items
    foreach ( $icons as $i => $icon ) {
        $i    = (int) $i;
        $url  = isset( $urls[$i] ) ? trim( $urls[$i] ) : '';
        $scheme = strstr( $url, ':', true );
        if ( $url !== '' && ! in_array( $scheme, $allowed_schemes, true ) ) {
            $url = '';
        }
        $raw_mode = isset( $color_modes[$i] ) ? $color_modes[$i] : 'default';
        $mode     = in_array( $raw_mode, $allowed_cmodes, true ) ? $raw_mode : 'default';
        $raw_icon = sanitize_key( $icon );
        $items[] = array(
            'icon'         => in_array( $raw_icon, $allowed_icons, true ) ? $raw_icon : 'link',
            'label'        => sanitize_text_field( isset( $labels[$i] ) ? $labels[$i] : '' ),
            'url'          => esc_url_raw( $url ),
            'enabled'      => isset( $enabled[$i] ),
            'color_mode'   => $mode,
            'color'        => sanitize_hex_color( isset( $colors[$i] ) ? $colors[$i] : '' ),
            'show_desktop' => isset( $show_desktops[$i] ) ? 1 : 0,
            'show_mobile'  => isset( $show_mobiles[$i] )  ? 1 : 0,
        );
    }

    $raw_position = sanitize_key( wp_unslash( isset( $_POST['sla_position'] )     ? $_POST['sla_position']     : 'right' ) );
    $raw_mode     = sanitize_key( wp_unslash( isset( $_POST['sla_display_mode'] ) ? $_POST['sla_display_mode'] : 'parent' ) );

    update_option( 'sla_settings', array(
        'btn_title'    => sanitize_text_field( wp_unslash( isset( $_POST['sla_btn_title'] )    ? $_POST['sla_btn_title']    : '' ) ),
        'btn_subtitle' => sanitize_text_field( wp_unslash( isset( $_POST['sla_btn_subtitle'] ) ? $_POST['sla_btn_subtitle'] : '' ) ),
        'btn_icon'     => sanitize_key( wp_unslash( isset( $_POST['sla_btn_icon'] )     ? $_POST['sla_btn_icon']     : '' ) ),
        'color_start'  => sanitize_hex_color( wp_unslash( isset( $_POST['sla_color_start'] ) ? $_POST['sla_color_start'] : '' ) ),
        'color_end'    => sanitize_hex_color( wp_unslash( isset( $_POST['sla_color_end'] )   ? $_POST['sla_color_end']   : '' ) ),
        'position'     => in_array( $raw_position, array( 'right', 'left' ), true ) ? $raw_position : 'right',
        'bottom'       => absint( isset( $_POST['sla_bottom'] )     ? $_POST['sla_bottom']     : 0 ),
        'btn_radius'   => absint( isset( $_POST['sla_btn_radius'] ) ? $_POST['sla_btn_radius'] : 999 ),
        'display_mode' => in_array( $raw_mode, array( 'parent', 'direct' ), true ) ? $raw_mode : 'parent',
        'items'        => $items,
    ) );

    sla_register_multilingual_strings( get_option( 'sla_settings', array() ) );

    wp_safe_redirect( admin_url( 'options-general.php?page=social-link-angkul&saved=1' ) );
    exit;
}

// ── Menu icon data ────────────────────────────────────────────────────────────
function sla_icon_data() {
    return array(
        'phone'     => array( 'label' => 'Phone',         'color' => '#22C55E', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>' ),
        'whatsapp'  => array( 'label' => 'WhatsApp',      'color' => '#25D366', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.107.549 4.09 1.512 5.814L.057 23.25c-.093.34.225.648.563.545l5.495-1.624A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.012-1.376l-.36-.213-3.724 1.101 1.045-3.62-.235-.372A9.818 9.818 0 1112 21.818z"/></svg>' ),
        'line'      => array( 'label' => 'Line',          'color' => '#00B900', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>' ),
        'facebook'  => array( 'label' => 'Facebook',      'color' => '#1877F2', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.993 4.388 10.952 10.125 11.854V15.47H7.078V12h3.047V9.356c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874V12h3.328l-.532 3.469h-2.796v8.384C19.612 22.952 24 17.993 24 12c0-6.627-5.373-12-12-12z"/></svg>' ),
        'messenger' => array( 'label' => 'FB Messenger',  'color' => '#0084FF', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 4.975 0 11.111c0 3.497 1.745 6.616 4.472 8.652V24l4.086-2.242c1.09.301 2.246.464 3.442.464 6.627 0 12-4.975 12-11.111S18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8.1l3.131 3.26L19.752 8.1l-6.561 6.863z"/></svg>' ),
        'telegram'  => array( 'label' => 'Telegram',      'color' => '#2AABEE', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>' ),
        'instagram' => array( 'label' => 'Instagram',     'color' => '#E1306C', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>' ),
        'tiktok'    => array( 'label' => 'TikTok',        'color' => '#010101', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z"/></svg>' ),
        'x'         => array( 'label' => 'X (Twitter)',   'color' => '#000000', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>' ),
        'email'     => array( 'label' => 'Email',         'color' => '#6B7280', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>' ),
        'map'       => array( 'label' => 'Map',           'color' => '#EF4444', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>' ),
        'calendar'  => array( 'label' => 'Calendar',      'color' => '#8B5CF6', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>' ),
        'discord'   => array( 'label' => 'Discord',       'color' => '#5865F2', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>' ),
        'wechat'    => array( 'label' => 'WeChat',        'color' => '#07C160', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.148 1.72-1.459 1.259-2.268 3.003-1.98 4.967.841 5.902 8.589 5.902 9.464 3.244l1.655.966a.29.29 0 0 0 .146.047c.134 0 .249-.107.249-.248 0-.06-.023-.12-.038-.177l-.34-1.295a.583.583 0 0 1 .185-.579C22.96 16.44 24 14.658 24 12.735c0-3.497-3.186-5.999-7.062-5.877zm-2.318 3.317c.562 0 1.016.463 1.016 1.033s-.454 1.033-1.016 1.033-.016-.463-1.016-1.033.454-1.033 1.016-1.033zm4.5 0c.562 0 1.016.463 1.016 1.033s-.454 1.033-1.016 1.033-1.016-.463-1.016-1.033.454-1.033 1.016-1.033z"/></svg>' ),
        'youtube'   => array( 'label' => 'YouTube',       'color' => '#FF0000', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>' ),
        'linkedin'  => array( 'label' => 'LinkedIn',      'color' => '#0A66C2', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>' ),
        'vk'        => array( 'label' => 'VK',            'color' => '#0077FF', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.391 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.864 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.743c.372 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>' ),
        'link'      => array( 'label' => 'Link / URL',    'color' => '#0066FF', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>' ),
    );
}

// ── Main button icon data (larger set) ───────────────────────────────────────
function sla_btn_icon_data() {
    $base = sla_icon_data();
    $extra = array(
        'chat'       => array( 'label' => 'Chat',           'color' => '#3B82F6', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>' ),
        'headphones' => array( 'label' => 'Support',        'color' => '#6366F1', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>' ),
        'star'       => array( 'label' => 'Star / Review',  'color' => '#F59E0B', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>' ),
        'heart'      => array( 'label' => 'Follow Us',      'color' => '#EC4899', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' ),
        'bell'       => array( 'label' => 'Notification',   'color' => '#F97316', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>' ),
        'users'      => array( 'label' => 'Community',      'color' => '#14B8A6', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>' ),
        'sparkles'   => array( 'label' => 'Special Offer',  'color' => '#A855F7', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/><path d="M5 3l.75 2.25L8 6l-2.25.75L5 9l-.75-2.25L2 6l2.25-.75z"/><path d="M19 15l.75 2.25L22 18l-2.25.75L19 21l-.75-2.25L16 18l2.25-.75z"/></svg>' ),
        'help'       => array( 'label' => 'Help / FAQ',     'color' => '#0EA5E9', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>' ),
        'home'       => array( 'label' => 'Home',           'color' => '#64748B', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>' ),
        'handshake'  => array( 'label' => 'Partner / Deal', 'color' => '#10B981', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"/></svg>' ),
        'globe'      => array( 'label' => 'Website',        'color' => '#0066FF', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>' ),
        'message'    => array( 'label' => 'Message',        'color' => '#6366F1', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>' ),
    );
    return array_merge( $base, $extra );
}

// ── Settings page ─────────────────────────────────────────────────────────────
function sla_settings_page() {
    $s         = get_option( 'sla_settings', array() );
    $title     = isset( $s['btn_title'] )    ? $s['btn_title']    : 'Book appointment';
    $subtitle  = isset( $s['btn_subtitle'] ) ? $s['btn_subtitle'] : 'Reply within 2 hours';
    $btn_icon  = isset( $s['btn_icon'] )     ? $s['btn_icon']     : 'calendar';
    $cs        = isset( $s['color_start'] )  ? $s['color_start']  : '#0066FF';
    $ce        = isset( $s['color_end'] )    ? $s['color_end']    : '#002D73';
    $pos       = isset( $s['position'] )     ? $s['position']     : 'right';
    $bottom       = isset( $s['bottom'] )       ? $s['bottom']       : 24;
    $radius       = isset( $s['btn_radius'] )   ? absint( $s['btn_radius'] ) : 999;
    $display_mode = isset( $s['display_mode'] ) ? $s['display_mode'] : 'parent';
    $items        = isset( $s['items'] )        ? $s['items']        : array();
    $icon_data = sla_icon_data();
    $btn_icons = sla_btn_icon_data();
    $bi_dat    = isset( $btn_icons[$btn_icon] ) ? $btn_icons[$btn_icon] : $btn_icons['calendar'];
    ?>
    <div class="wrap sla-wrap">
        <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:24px">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:linear-gradient(135deg,<?php echo esc_attr($cs); ?>,<?php echo esc_attr($ce); ?>);border-radius:10px;color:#fff">
                <?php echo $bi_dat['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG data ?>
            </span>
            Social Link by Angkul
        </h1>

        <?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-success is-dismissible"><p>✓ Settings saved.</p></div>
        <?php endif; ?>

        <style>
        .sla-wrap *{box-sizing:border-box}
        .sla-panel{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.05)}
        .sla-panel h2{margin:0 0 18px;font-size:15px;font-weight:700;color:#111;display:flex;align-items:center;gap:6px}
        .sla-panel h2 svg{width:16px;height:16px;opacity:.6}
        .sla-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .sla-field{display:flex;flex-direction:column;gap:5px}
        .sla-field label{font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px}
        .sla-field input[type=text],.sla-field input[type=number]{width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;transition:border .15s}
        .sla-field input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .sla-color-row{display:flex;align-items:center;gap:8px}
        .sla-color-row input[type=color]{width:36px;height:36px;padding:2px;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer}
        .sla-color-arrow{font-size:18px;color:#d1d5db}
        .sla-pos-btns{display:flex;gap:8px}
        .sla-pos-btn{display:flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid #e5e7eb;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#374151;transition:all .15s;user-select:none}
        .sla-pos-btn input[type=radio]{display:none}
        .sla-pos-btn.active{border-color:#6366f1;background:#eef2ff;color:#4f46e5}

        /* Icon picker grid */
        .sla-icon-picker{display:grid;grid-template-columns:repeat(auto-fill,minmax(64px,1fr));gap:4px;margin-top:10px}
        .sla-icon-opt{display:flex;flex-direction:column;align-items:center;gap:6px;padding:10px 4px;border:none;border-radius:10px;cursor:pointer;transition:opacity .15s;background:transparent}
        .sla-icon-opt:hover{opacity:.8}
        .sla-icon-opt .ib{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;background:#d1d5db;transition:background .15s}
        .sla-icon-opt:hover .ib,.sla-icon-opt.selected .ib{background:var(--ic)!important}
        .sla-icon-opt .ib svg{width:18px;height:18px}
        .sla-icon-opt .il{font-size:11px;font-weight:600;color:#9ca3af;text-align:center;white-space:nowrap;transition:color .15s;line-height:1.3}
        .sla-icon-opt:hover .il{color:#6b7280}
        .sla-icon-opt.selected .il{color:#4f46e5}

        /* Cards */
        .sla-items-list{display:flex;flex-direction:column;gap:10px;min-height:20px}
        .sla-item-card{background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:12px;transition:box-shadow .15s,border-color .15s;position:relative}
        .sla-item-card:hover{border-color:#d1d5db;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .sla-item-card.sla-dragging{opacity:.35;border-style:dashed}
        .sla-item-card.sla-over{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15)}

        .sla-drag-handle{cursor:grab;color:#d1d5db;padding:2px 4px;flex-shrink:0;display:flex;align-items:center;transition:color .15s}
        .sla-drag-handle:hover{color:#9ca3af}
        .sla-drag-handle svg{width:16px;height:16px}

        .sla-card-bubble{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;cursor:pointer;transition:transform .15s;box-shadow:0 2px 8px rgba(0,0,0,.2)}
        .sla-card-bubble:hover{transform:scale(1.08)}
        .sla-card-bubble svg{width:20px;height:20px}

        .sla-card-body{flex:1;display:flex;flex-direction:column;gap:8px;min-width:0}
        .sla-card-row1{display:flex;align-items:center;gap:10px}
        .sla-card-row1 select{
            flex:0 0 160px;
            padding:0 30px 0 10px;
            height:34px;
            line-height:34px;
            border:1.5px solid #e5e7eb;
            border-radius:8px;
            font-size:13px;
            font-weight:500;
            color:#374151;
            background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
            -webkit-appearance:none;
            -moz-appearance:none;
            appearance:none;
            cursor:pointer;
            transition:border-color .15s,box-shadow .15s
        }
        .sla-card-row1 select:hover{border-color:#c4b5fd}
        .sla-card-row1 select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .sla-card-row1 input[type=text]{flex:1;padding:0 10px;height:34px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px}
        .sla-card-row1 input[type=text]:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.08)}
        .sla-card-row2{display:flex;align-items:center;gap:6px;flex-wrap:wrap}

        /* color mode pills */
        .sla-cmode-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border:1.5px solid #e5e7eb;border-radius:20px;font-size:11px;font-weight:600;cursor:pointer;color:#6b7280;background:#fff;transition:all .15s;user-select:none;white-space:nowrap}
        .sla-cmode-pill input[type=radio]{display:none}
        .sla-cmode-pill.active{border-color:#6366f1;background:#eef2ff;color:#4f46e5}
        .sla-color-swatch{width:14px;height:14px;border-radius:50%;border:1.5px solid rgba(0,0,0,.15);display:inline-block;vertical-align:middle}
        .sla-inline-picker{width:26px;height:20px;padding:0;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;vertical-align:middle;display:none}

        /* enabled toggle */
        .sla-toggle{position:relative;width:36px;height:20px;cursor:pointer}
        .sla-toggle input{opacity:0;width:0;height:0;position:absolute}
        .sla-toggle-track{position:absolute;inset:0;background:#e5e7eb;border-radius:20px;transition:background .2s}
        .sla-toggle input:checked~.sla-toggle-track{background:#6366f1}
        .sla-toggle-thumb{position:absolute;top:2px;left:2px;width:16px;height:16px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .2s}
        .sla-toggle input:checked~.sla-toggle-thumb{transform:translateX(16px)}

        .sla-card-right{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;flex-shrink:0}
        .sla-remove-row{background:none;border:none;cursor:pointer;color:#e5e7eb;padding:4px;line-height:1;transition:color .15s}
        .sla-remove-row:hover{color:#ef4444}
        .sla-remove-row svg{width:16px;height:16px;display:block}

        /* device visibility buttons */
        .sla-dev-btns{display:flex;align-items:center;gap:4px;margin-left:auto}
        .sla-dev-btn{display:flex;align-items:center;justify-content:center;width:28px;height:28px;border:1.5px solid #e5e7eb;border-radius:7px;cursor:pointer;color:#d1d5db;transition:all .15s;user-select:none}
        .sla-dev-btn input{display:none}
        .sla-dev-btn.active{border-color:#6366f1;background:#eef2ff;color:#4f46e5}
        .sla-dev-btn svg{width:14px;height:14px;pointer-events:none}

        .sla-add-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;background:#fff;border:1.5px dashed #d1d5db;border-radius:10px;color:#6b7280;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;margin-top:16px}
        .sla-add-btn:hover{border-color:#6366f1;color:#4f46e5;background:#eef2ff}

        .sla-save-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 28px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(99,102,241,.35);margin-top:8px}
        .sla-save-btn:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(99,102,241,.4)}

        .sla-url-hint{font-size:11px;color:#9ca3af;margin-top:2px}

        /* ── Responsive ── */
        @media(max-width:900px){
            .sla-btn-fields{gap:16px}
            .sla-card-row1 select{flex:0 0 140px}
        }
        @media(max-width:700px){
            .sla-panel{padding:16px}
            .sla-grid-2{grid-template-columns:1fr}
            .sla-btn-fields{flex-direction:column;align-items:stretch;gap:14px}
            .sla-btn-fields .sla-field{max-width:none!important}
            .sla-card-row1{flex-wrap:wrap}
            .sla-card-row1 select{flex:1 1 100%;width:100%}
            .sla-card-row1 input[type=text]{flex:1 1 120px}
            .sla-item-card{padding:10px 12px;gap:8px}
            .sla-card-bubble{width:38px;height:38px}
            .sla-card-bubble svg{width:16px;height:16px}
            .sla-icon-picker{gap:3px}
        }
        @media(max-width:480px){
            .sla-drag-handle{display:none}
            .sla-card-bubble{width:32px;height:32px}
            .sla-icon-picker{grid-template-columns:repeat(4,1fr)}
            .sla-icon-opt{padding:8px 2px}
            .sla-icon-opt .ib{width:36px;height:36px}
            .sla-icon-opt .ib svg{width:15px;height:15px}
            .sla-pos-btns{flex-wrap:wrap}
        }
        </style>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'sla_save_nonce' ); ?>
            <input type="hidden" name="action" value="sla_save">

            <!-- ── Main Button Panel ── -->
            <div class="sla-panel">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Main Button
                </h2>
                <div class="sla-grid-2" style="margin-bottom:16px">
                    <div class="sla-field">
                        <label>Title</label>
                        <input type="text" name="sla_btn_title" value="<?php echo esc_attr($title); ?>" placeholder="Book appointment">
                    </div>
                    <div class="sla-field">
                        <label>Subtitle</label>
                        <input type="text" name="sla_btn_subtitle" value="<?php echo esc_attr($subtitle); ?>" placeholder="Reply within 2 hours">
                    </div>
                </div>

                <div class="sla-btn-fields" style="display:flex;align-items:flex-end;gap:24px;flex-wrap:wrap;margin-bottom:16px">
                    <div class="sla-field">
                        <label>Button Gradient</label>
                        <div class="sla-color-row">
                            <input type="color" name="sla_color_start" value="<?php echo esc_attr($cs); ?>" id="sla-cs">
                            <span class="sla-color-arrow">→</span>
                            <input type="color" name="sla_color_end"   value="<?php echo esc_attr($ce); ?>" id="sla-ce">
                            <div id="sla-grad-preview" style="flex:1;height:36px;border-radius:8px;background:linear-gradient(135deg,<?php echo esc_attr($cs); ?>,<?php echo esc_attr($ce); ?>);min-width:80px"></div>
                        </div>
                    </div>
                    <div class="sla-field">
                        <label>Position</label>
                        <div class="sla-pos-btns">
                            <label class="sla-pos-btn <?php echo $pos==='right'?'active':''; ?>">
                                <input type="radio" name="sla_position" value="right" <?php checked($pos,'right'); ?>>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="15" y1="9" x2="15" y2="15"/></svg> Right
                            </label>
                            <label class="sla-pos-btn <?php echo $pos==='left'?'active':''; ?>">
                                <input type="radio" name="sla_position" value="left" <?php checked($pos,'left'); ?>>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="9" y2="15"/></svg> Left
                            </label>
                        </div>
                    </div>
                    <div class="sla-field" style="max-width:110px">
                        <label>Bottom (px)</label>
                        <input type="number" name="sla_bottom" value="<?php echo esc_attr($bottom); ?>" min="0" max="500">
                    </div>
                    <div class="sla-field" style="max-width:110px">
                        <label>Radius (px)</label>
                        <input type="number" name="sla_btn_radius" value="<?php echo esc_attr($radius); ?>" min="0" max="999">
                    </div>
                    <div class="sla-field">
                        <label>Display Mode</label>
                        <div class="sla-pos-btns">
                            <label class="sla-pos-btn <?php echo $display_mode==='parent'?'active':''; ?>">
                                <input type="radio" name="sla_display_mode" value="parent" <?php checked($display_mode,'parent'); ?>>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> FAB
                            </label>
                            <label class="sla-pos-btn <?php echo $display_mode==='direct'?'active':''; ?>">
                                <input type="radio" name="sla_display_mode" value="direct" <?php checked($display_mode,'direct'); ?>>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg> Direct
                            </label>
                        </div>
                        <span style="font-size:11px;color:#9ca3af">FAB = click to expand · Direct = always visible</span>
                    </div>
                </div>

                <!-- Main icon picker -->
                <div class="sla-field">
                    <label>Main Button Icon</label>
                    <input type="hidden" name="sla_btn_icon" id="sla-btn-icon-val" value="<?php echo esc_attr($btn_icon); ?>">
                    <div class="sla-icon-picker" id="sla-btn-icon-picker">
                        <?php foreach ( $btn_icons as $key => $idat ) : ?>
                        <div class="sla-icon-opt <?php echo $btn_icon===$key?'selected':''; ?>" data-icon="<?php echo esc_attr($key); ?>">
                            <div class="ib" style="--ic:<?php echo esc_attr($idat['color']); ?>"><?php echo $idat['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG data ?></div>
                            <span class="il"><?php echo esc_html($idat['label']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ── Menu Items Panel ── -->
            <div class="sla-panel">
                <h2>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    Menu Items
                </h2>
                <p style="color:#9ca3af;font-size:13px;margin:-8px 0 16px">Drag ⠿ to reorder — each icon has its own independent background color</p>

                <div class="sla-items-list" id="sla-items-list">
                <?php foreach ( $items as $i => $item ) :
                    $en           = ! empty( $item['enabled'] );
                    $ico          = isset( $item['icon'] )         ? $item['icon']         : 'link';
                    $lbl          = isset( $item['label'] )        ? $item['label']        : '';
                    $url          = isset( $item['url'] )          ? $item['url']          : '';
                    $cmode        = isset( $item['color_mode'] )   ? $item['color_mode']   : 'default';
                    $color        = isset( $item['color'] )        ? $item['color']        : '#0066FF';
                    $show_desktop = isset( $item['show_desktop'] ) ? $item['show_desktop'] : 1;
                    $show_mobile  = isset( $item['show_mobile'] )  ? $item['show_mobile']  : 1;
                    $idat  = isset( $icon_data[$ico] )    ? $icon_data[$ico]    : $icon_data['link'];
                    if ( $cmode==='brand' )      $bubble_bg = $idat['color'];
                    elseif ( $cmode==='custom' ) $bubble_bg = $color;
                    else                         $bubble_bg = "linear-gradient(135deg,{$cs},{$ce})";
                ?>
                <div class="sla-item-card">
                    <div class="sla-drag-handle">
                        <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                    </div>
                    <div class="sla-card-bubble" style="background:<?php echo esc_attr($bubble_bg); ?>">
                        <?php echo $idat['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG data ?>
                    </div>
                    <div class="sla-card-body">
                        <div class="sla-card-row1">
                            <select name="sla_icon[<?php echo (int) $i; ?>]" class="sla-icon-sel">
                                <?php foreach ( $icon_data as $val => $iinfo ) : ?>
                                    <option value="<?php echo esc_attr($val); ?>" <?php selected($ico,$val); ?>><?php echo esc_html($iinfo['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="sla_label[<?php echo (int) $i; ?>]" value="<?php echo esc_attr($lbl); ?>" placeholder="Label">
                            <input type="text" name="sla_url[<?php echo (int) $i; ?>]"   value="<?php echo esc_attr($url); ?>"  placeholder="URL หรือ tel:+66...">
                        </div>
                        <div class="sla-card-row2">
                            <span style="font-size:11px;font-weight:600;color:#9ca3af;margin-right:2px">สีพื้น:</span>
                            <?php foreach ( array( 'default' => 'Default', 'brand' => 'Brand', 'custom' => 'Custom' ) as $mval => $mlbl ) : ?>
                            <label class="sla-cmode-pill <?php echo $cmode === $mval ? 'active' : ''; ?>">
                                <input type="radio" name="sla_color_mode[<?php echo (int) $i; ?>]" class="sla-cmode" value="<?php echo esc_attr( $mval ); ?>" <?php checked($cmode,$mval); ?>>
                                <?php echo esc_html( $mlbl ); ?>
                            </label>
                            <?php endforeach; ?>
                            <input type="color" name="sla_color[<?php echo (int) $i; ?>]" class="sla-inline-picker" value="<?php echo esc_attr($color); ?>" <?php echo $cmode === 'custom' ? 'style="display:inline-block"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
                            <div class="sla-dev-btns">
                                <label class="sla-dev-btn <?php echo $show_desktop ? 'active' : ''; ?>" title="Desktop">
                                    <input type="checkbox" name="sla_show_desktop[<?php echo (int) $i; ?>]" class="sla-show-desktop" value="1" <?php checked($show_desktop,1); ?>>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                </label>
                                <label class="sla-dev-btn <?php echo $show_mobile ? 'active' : ''; ?>" title="Mobile">
                                    <input type="checkbox" name="sla_show_mobile[<?php echo (int) $i; ?>]" class="sla-show-mobile" value="1" <?php checked($show_mobile,1); ?>>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="sla-card-right">
                        <label class="sla-toggle">
                            <input type="checkbox" name="sla_enabled[<?php echo (int) $i; ?>]" value="1" <?php checked($en); ?>>
                            <span class="sla-toggle-track"></span>
                            <span class="sla-toggle-thumb"></span>
                        </label>
                        <button type="button" class="sla-remove-row" title="ลบ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>

                <button type="button" class="sla-add-btn" id="sla-add-row">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Item
                </button>
            </div>

            <button type="submit" class="sla-save-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Save Settings
            </button>

            <script>
            jQuery(function($){
                var list      = $('#sla-items-list');
                var iconData  = <?php echo json_encode($icon_data); ?>;
                var gradStart = <?php echo json_encode($cs); ?>;
                var gradEnd   = <?php echo json_encode($ce); ?>;

                /* ── Gradient preview ── */
                $('#sla-cs,#sla-ce').on('input change', function(){
                    var s=$('#sla-cs').val(), e=$('#sla-ce').val();
                    gradStart=s; gradEnd=e;
                    $('#sla-grad-preview').css('background','linear-gradient(135deg,'+s+','+e+')');
                    // update default-mode bubbles
                    list.find('.sla-item-card').each(function(){
                        var mode=$(this).find('.sla-cmode:checked').val();
                        if(mode==='default') updateBubble($(this));
                    });
                });

                /* ── Position buttons ── */
                $('.sla-pos-btn').on('click', function(){
                    $('.sla-pos-btn').removeClass('active');
                    $(this).addClass('active');
                });

                /* ── Main icon picker ── */
                $('#sla-btn-icon-picker').on('click', '.sla-icon-opt', function(){
                    $('#sla-btn-icon-picker .sla-icon-opt').removeClass('selected');
                    $(this).addClass('selected');
                    $('#sla-btn-icon-val').val($(this).data('icon'));
                });

                /* ── Sortable ── */
                list.sortable({
                    handle: '.sla-drag-handle',
                    placeholder: 'sla-item-card sla-over',
                    forcePlaceholderSize: true,
                    tolerance: 'pointer',
                    start: function(e,ui){ ui.item.addClass('sla-dragging'); },
                    stop: function(e,ui){ ui.item.removeClass('sla-dragging'); reindex(); }
                });

                /* ── Reindex ── */
                function reindex() {
                    list.find('.sla-item-card').each(function(i){
                        $(this).find('select.sla-icon-sel').attr('name','sla_icon['+i+']');
                        $(this).find('.sla-cmode').attr('name','sla_color_mode['+i+']');
                        $(this).find('.sla-inline-picker').attr('name','sla_color['+i+']');
                        $(this).find('input[type=text]').eq(0).attr('name','sla_label['+i+']');
                        $(this).find('input[type=text]').eq(1).attr('name','sla_url['+i+']');
                        $(this).find('.sla-card-right input[type=checkbox]').attr('name','sla_enabled['+i+']');
                        $(this).find('.sla-show-desktop').attr('name','sla_show_desktop['+i+']');
                        $(this).find('.sla-show-mobile').attr('name','sla_show_mobile['+i+']');
                    });
                }

                /* ── Bubble helper ── */
                function bubbleBg($card) {
                    var mode  = $card.find('.sla-cmode:checked').val();
                    var icon  = $card.find('.sla-icon-sel').val();
                    var color = $card.find('.sla-inline-picker').val();
                    if (mode==='brand')  return iconData[icon] ? iconData[icon].color : gradStart;
                    if (mode==='custom') return color;
                    return 'linear-gradient(135deg,'+gradStart+','+gradEnd+')';
                }
                function updateBubble($card) {
                    var icon  = $card.find('.sla-icon-sel').val();
                    var $bub  = $card.find('.sla-card-bubble');
                    $bub.css('background', bubbleBg($card));
                    if (iconData[icon]) $bub.html(iconData[icon].svg);
                }

                /* ── Events on cards ── */
                list.on('change', '.sla-icon-sel,.sla-cmode,.sla-inline-picker', function(){
                    var $card = $(this).closest('.sla-item-card');
                    // toggle picker visibility
                    var mode = $card.find('.sla-cmode:checked').val();
                    var $pill = $card.find('.sla-cmode-pill');
                    $pill.each(function(){
                        $(this).toggleClass('active', $(this).find('.sla-cmode').val()===mode);
                    });
                    $card.find('.sla-inline-picker').toggle(mode==='custom');
                    updateBubble($card);
                });
                list.on('input', '.sla-inline-picker', function(){
                    updateBubble($(this).closest('.sla-item-card'));
                });
                list.on('click', '.sla-cmode-pill', function(){
                    $(this).find('.sla-cmode').prop('checked',true).trigger('change');
                });
                list.on('click', '.sla-remove-row', function(){
                    if (list.find('.sla-item-card').length<=1){ alert('ต้องมีอย่างน้อย 1 รายการ'); return; }
                    $(this).closest('.sla-item-card').remove();
                    reindex();
                });
                list.on('change', '.sla-dev-btn input[type=checkbox]', function(){
                    $(this).closest('.sla-dev-btn').toggleClass('active', $(this).is(':checked'));
                });

                /* ── Build new card ── */
                function buildCard() {
                    var firstKey = Object.keys(iconData)[0];
                    var grad     = 'linear-gradient(135deg,'+gradStart+','+gradEnd+')';
                    var selOpts  = Object.keys(iconData).map(function(k){
                        return '<option value="'+k+'">'+iconData[k].label+'</option>';
                    }).join('');

                    var devBtns =
                        '<div class="sla-dev-btns">'+
                            '<label class="sla-dev-btn active" title="Desktop">'+
                                '<input type="checkbox" class="sla-show-desktop" value="1" checked>'+
                                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>'+
                            '</label>'+
                            '<label class="sla-dev-btn active" title="Mobile">'+
                                '<input type="checkbox" class="sla-show-mobile" value="1" checked>'+
                                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>'+
                            '</label>'+
                        '</div>';

                    return $('<div class="sla-item-card">'+
                        '<div class="sla-drag-handle"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg></div>'+
                        '<div class="sla-card-bubble" style="background:'+grad+'">'+iconData[firstKey].svg+'</div>'+
                        '<div class="sla-card-body">'+
                            '<div class="sla-card-row1">'+
                                '<select class="sla-icon-sel">'+selOpts+'</select>'+
                                '<input type="text" placeholder="Label">'+
                                '<input type="text" placeholder="URL หรือ tel:+66...">'+
                            '</div>'+
                            '<div class="sla-card-row2">'+
                                '<span style="font-size:11px;font-weight:600;color:#9ca3af;margin-right:2px">สีพื้น:</span>'+
                                '<label class="sla-cmode-pill active"><input type="radio" class="sla-cmode" value="default" checked> Default</label>'+
                                '<label class="sla-cmode-pill"><input type="radio" class="sla-cmode" value="brand"> Brand</label>'+
                                '<label class="sla-cmode-pill"><input type="radio" class="sla-cmode" value="custom"> Custom</label>'+
                                '<input type="color" class="sla-inline-picker" value="#0066FF">'+
                                devBtns+
                            '</div>'+
                        '</div>'+
                        '<div class="sla-card-right">'+
                            '<label class="sla-toggle">'+
                                '<input type="checkbox" value="1" checked>'+
                                '<span class="sla-toggle-track"></span>'+
                                '<span class="sla-toggle-thumb"></span>'+
                            '</label>'+
                            '<button type="button" class="sla-remove-row" title="ลบ"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1=