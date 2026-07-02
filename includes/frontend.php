<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Enqueue ───────────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'sla_enqueue' );
function sla_enqueue() {
    $s     = get_option( 'sla_settings', array() );
    $items = isset( $s['items'] ) ? $s['items'] : array();
    $has   = false;
    foreach ( $items as $item ) {
        if ( ! empty( $item['enabled'] ) && ! empty( $item['url'] ) ) { $has = true; break; }
    }
    if ( ! $has ) return;

    wp_enqueue_style(  'sla-style',  SLA_URL . 'assets/css/frontend.css', array(), SLA_VERSION );
    wp_enqueue_script( 'sla-script', SLA_URL . 'assets/js/frontend.js',   array(), SLA_VERSION, true );
}

// ── Render ────────────────────────────────────────────────────────────────────
add_action( 'wp_footer', 'sla_render' );
function sla_render() {
    $s            = get_option( 'sla_settings', array() );
    $title        = isset( $s['btn_title'] )    ? esc_html( sla_translate_string( $s['btn_title'],    'Button title' ) )    : esc_html__( 'Book appointment',   'social-link-by-angkul' );
    $subtitle     = isset( $s['btn_subtitle'] ) ? esc_html( sla_translate_string( $s['btn_subtitle'], 'Button subtitle' ) ) : esc_html__( 'Reply within 2 hours', 'social-link-by-angkul' );
    $btn_icon     = isset( $s['btn_icon'] )     ? $s['btn_icon']                 : 'calendar';
    $cs           = isset( $s['color_start'] )  ? esc_attr( $s['color_start'] )  : '#0066FF';
    $ce           = isset( $s['color_end'] )    ? esc_attr( $s['color_end'] )    : '#002D73';
    $pos          = isset( $s['position'] )     ? $s['position']                 : 'right';
    $bottom       = isset( $s['bottom'] )       ? absint( $s['bottom'] )         : 24;
    $radius       = isset( $s['btn_radius'] )   ? absint( $s['btn_radius'] )     : 999;
    $display_mode = isset( $s['display_mode'] ) ? $s['display_mode']             : 'parent';
    $items        = isset( $s['items'] )        ? $s['items']                    : array();
    $all_btn_icons = sla_btn_icon_data();
    $bi_svg       = isset( $all_btn_icons[$btn_icon] ) ? $all_btn_icons[$btn_icon]['svg'] : sla_icon('calendar');

    $active = array_filter( $items, function( $item ) {
        return ! empty( $item['enabled'] ) && ! empty( $item['url'] );
    });
    if ( empty( $active ) ) return;

    $side = $pos === 'left' ? 'left:24px;right:auto' : 'right:24px';
    $grad = "linear-gradient(135deg,{$cs},{$ce})";

    // ── Build shared item markup ───────────────────────────────────────────────
    $all_icon_data = sla_icon_data();
    ob_start();
    $sla_item_idx = 0;
    foreach ( $active as $item ) :
        $icon_type = isset( $item['icon'] )       ? $item['icon']       : 'link';
        $sla_item_idx++;
        $cmode     = isset( $item['color_mode'] ) ? $item['color_mode'] : 'default';
        $cval      = isset( $item['color'] )      ? $item['color']      : '';
        $idat      = isset( $all_icon_data[$icon_type] ) ? $all_icon_data[$icon_type] : $all_icon_data['link'];

        if ( $cmode === 'brand' ) {
            $ic_bg = $idat['color'];
        } elseif ( $cmode === 'custom' && ! empty( $cval ) ) {
            $ic_bg = $cval;
        } else {
            $ic_bg = "linear-gradient(135deg,{$cs},{$ce})";
        }
        $target    = ( strpos( $item['url'], 'tel:' ) !== 0 && strpos( $item['url'], 'mailto:' ) !== 0 )
                        ? 'target="_blank" rel="noopener noreferrer"' : '';
        $show_desk = isset( $item['show_desktop'] ) ? (int)$item['show_desktop'] : 1;
        $show_mob  = isset( $item['show_mobile'] )  ? (int)$item['show_mobile']  : 1;
        $vis_class = '';
        if ( ! $show_desk ) $vis_class .= ' sla-hide-desktop';
        if ( ! $show_mob  ) $vis_class .= ' sla-hide-mobile';
    ?>
        <a class="appt-fab__item<?php echo esc_attr( $vis_class ); ?>"
           href="<?php echo esc_url( $item['url'] ); ?>"
           <?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <span class="ic" style="background:<?php echo esc_attr( $ic_bg ); ?>">
                <?php echo sla_icon( $icon_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG data ?>
            </span>
            <span class="lbl"><?php echo esc_html( sla_translate_string( $item['label'], 'Item ' . $sla_item_idx . ' label' ) ); ?></span>
        </a>
    <?php endforeach;
    $items_html = ob_get_clean();

    // ── FAB mode (default) ────────────────────────────────────────────────────
    if ( $display_mode !== 'direct' ) : ?>
    <div class="appt-fab" id="apptFab" data-mode="parent" style="bottom:<?php echo (int) $bottom; ?>px;<?php echo esc_attr( $side ); ?>">

        <button class="appt-fab__main" id="apptFabToggle" aria-expanded="false"
                style="background:<?php echo esc_attr( $grad ); ?>;border-radius:<?php echo $radius; ?>px">
            <span class="appt-fab__ic">
                <?php echo $bi_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded SVG data ?>
            </span>
            <span class="appt-fab__txt">
                <span class="t"><?php echo esc_html( $title ); ?></span>
                <span class="s"><?php echo esc_html( $subtitle ); ?></span>
            </span>
            <span class="appt-fab__close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </span>
        </button>

        <div class="appt-fab__menu">
            <?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped buffer ?>
        </div>

    </div>

    <?php
    // ── Direct mode ───────────────────────────────────────────────────────────
    else : ?>
    <div class="appt-fab appt-fab--direct" id="apptFab" data-mode="direct" style="bottom:<?php echo (int) $bottom; ?>px;<?php echo esc_attr( $side ); ?>">

        <div class="appt-fab__menu">
            <?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped buffer ?>
        </div>

    </div>
    <?php endif;
}

// ── Icon helper (pulls from shared icon data) ─────────────────────────────────
function sla_icon( $type ) {
  