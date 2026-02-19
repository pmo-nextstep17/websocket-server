<?php
/**
 * Plugin Name: Iframe Clean Viewer
 * Description: Mostra una pagina esterna dentro un iframe con maschere opzionali per nascondere visivamente header e footer del sito incorporato.
 * Version: 1.4.0
 * Author: Codex
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ICV_OPTION_DEFAULT_URL', 'icv_default_url');
define('ICV_OPTION_DEFAULT_HEIGHT', 'icv_default_height');
define('ICV_OPTION_DEFAULT_HIDE_TOP', 'icv_default_hide_top');
define('ICV_OPTION_DEFAULT_HIDE_BOTTOM', 'icv_default_hide_bottom');
define('ICV_OPTION_DEFAULT_BORDER_RADIUS', 'icv_default_border_radius');

/**
 * Sanitizza URL impostato da amministrazione.
 */
function icv_sanitize_admin_url($value)
{
    $url = esc_url_raw(trim((string) $value));

    if ($url === '') {
        return '';
    }

    if (!wp_http_validate_url($url)) {
        add_settings_error(
            'iframe-clean-viewer',
            'icv_invalid_url',
            'URL non valida. Inserisci un URL completo, ad esempio: https://example.com',
            'error'
        );
        return get_option(ICV_OPTION_DEFAULT_URL, '');
    }

    return $url;
}

/**
 * Sanitizza altezza CSS (es: 70vh, 600px, 100%).
 */
function icv_sanitize_height($value)
{
    $height = preg_replace('/[^0-9a-zA-Z.%-]/', '', (string) $value);
    return $height !== '' ? $height : '70vh';
}

/**
 * Sanitizza intero positivo.
 */
function icv_sanitize_positive_int($value)
{
    return max(0, intval($value));
}

/**
 * Restituisce i default globali salvati in amministrazione.
 */
function icv_get_admin_defaults()
{
    return array(
        'url' => get_option(ICV_OPTION_DEFAULT_URL, ''),
        'height' => icv_sanitize_height(get_option(ICV_OPTION_DEFAULT_HEIGHT, '70vh')),
        'hide_top' => icv_sanitize_positive_int(get_option(ICV_OPTION_DEFAULT_HIDE_TOP, 80)),
        'hide_bottom' => icv_sanitize_positive_int(get_option(ICV_OPTION_DEFAULT_HIDE_BOTTOM, 80)),
        'border_radius' => icv_sanitize_positive_int(get_option(ICV_OPTION_DEFAULT_BORDER_RADIUS, 12)),
    );
}

/**
 * Registra impostazioni plugin.
 */
function icv_register_settings()
{
    register_setting('icv_settings_group', ICV_OPTION_DEFAULT_URL, array(
        'type' => 'string',
        'sanitize_callback' => 'icv_sanitize_admin_url',
        'default' => '',
    ));

    register_setting('icv_settings_group', ICV_OPTION_DEFAULT_HEIGHT, array(
        'type' => 'string',
        'sanitize_callback' => 'icv_sanitize_height',
        'default' => '70vh',
    ));

    register_setting('icv_settings_group', ICV_OPTION_DEFAULT_HIDE_TOP, array(
        'type' => 'integer',
        'sanitize_callback' => 'icv_sanitize_positive_int',
        'default' => 80,
    ));

    register_setting('icv_settings_group', ICV_OPTION_DEFAULT_HIDE_BOTTOM, array(
        'type' => 'integer',
        'sanitize_callback' => 'icv_sanitize_positive_int',
        'default' => 80,
    ));

    register_setting('icv_settings_group', ICV_OPTION_DEFAULT_BORDER_RADIUS, array(
        'type' => 'integer',
        'sanitize_callback' => 'icv_sanitize_positive_int',
        'default' => 12,
    ));

    add_settings_section('icv_main_section', 'Impostazioni principali', '__return_false', 'iframe-clean-viewer');

    add_settings_field(ICV_OPTION_DEFAULT_URL, 'URL predefinito iframe', 'icv_render_default_url_field', 'iframe-clean-viewer', 'icv_main_section');
    add_settings_field(ICV_OPTION_DEFAULT_HEIGHT, 'Altezza predefinita iframe', 'icv_render_default_height_field', 'iframe-clean-viewer', 'icv_main_section');
    add_settings_field(ICV_OPTION_DEFAULT_HIDE_TOP, 'Nascondi header (px) predefinito', 'icv_render_default_hide_top_field', 'iframe-clean-viewer', 'icv_main_section');
    add_settings_field(ICV_OPTION_DEFAULT_HIDE_BOTTOM, 'Nascondi footer (px) predefinito', 'icv_render_default_hide_bottom_field', 'iframe-clean-viewer', 'icv_main_section');
    add_settings_field(ICV_OPTION_DEFAULT_BORDER_RADIUS, 'Border radius (px) predefinito', 'icv_render_default_border_radius_field', 'iframe-clean-viewer', 'icv_main_section');
}
add_action('admin_init', 'icv_register_settings');

function icv_render_default_url_field()
{
    $value = get_option(ICV_OPTION_DEFAULT_URL, '');
    ?>
    <input type="url" class="regular-text" name="<?php echo esc_attr(ICV_OPTION_DEFAULT_URL); ?>" value="<?php echo esc_attr($value); ?>" placeholder="https://example.com" />
    <p class="description">Usato se non specifichi <code>url</code> nello shortcode/widget.</p>
    <?php
}

function icv_render_default_height_field()
{
    $value = icv_sanitize_height(get_option(ICV_OPTION_DEFAULT_HEIGHT, '70vh'));
    ?>
    <input type="text" class="regular-text" name="<?php echo esc_attr(ICV_OPTION_DEFAULT_HEIGHT); ?>" value="<?php echo esc_attr($value); ?>" placeholder="70vh" />
    <p class="description">Default responsive consigliato: <code>70vh</code>.</p>
    <?php
}

function icv_render_default_hide_top_field()
{
    $value = icv_sanitize_positive_int(get_option(ICV_OPTION_DEFAULT_HIDE_TOP, 80));
    ?>
    <input type="number" min="0" class="small-text" name="<?php echo esc_attr(ICV_OPTION_DEFAULT_HIDE_TOP); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
}

function icv_render_default_hide_bottom_field()
{
    $value = icv_sanitize_positive_int(get_option(ICV_OPTION_DEFAULT_HIDE_BOTTOM, 80));
    ?>
    <input type="number" min="0" class="small-text" name="<?php echo esc_attr(ICV_OPTION_DEFAULT_HIDE_BOTTOM); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
}

function icv_render_default_border_radius_field()
{
    $value = icv_sanitize_positive_int(get_option(ICV_OPTION_DEFAULT_BORDER_RADIUS, 12));
    ?>
    <input type="number" min="0" class="small-text" name="<?php echo esc_attr(ICV_OPTION_DEFAULT_BORDER_RADIUS); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
}

function icv_add_settings_page()
{
    add_options_page('Iframe Clean Viewer', 'Iframe Clean Viewer', 'manage_options', 'iframe-clean-viewer', 'icv_render_settings_page');
}
add_action('admin_menu', 'icv_add_settings_page');

/**
 * Restituisce il dominio del sito WordPress corrente.
 */
function icv_get_source_domain()
{
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    return is_string($host) ? strtolower($host) : '';
}

/**
 * Aggiunge parametri utili all'URL di destinazione iframe.
 */
function icv_build_iframe_url($url, $hide_top, $hide_bottom)
{
    $source_domain = icv_get_source_domain();

    $query_args = array(
        'hide_header' => max(0, intval($hide_top)),
        'hide_footer' => max(0, intval($hide_bottom)),
    );

    if ($source_domain !== '') {
        $query_args['source_domain'] = $source_domain;
    }

    $url_with_args = add_query_arg($query_args, $url);
    return esc_url_raw($url_with_args);
}

/**
 * Render markup iframe condiviso tra shortcode e widget.
 */
function icv_render_iframe_markup($url, $height, $hide_top, $hide_bottom, $border_radius)
{
    $wrapper_id = 'icv-' . wp_generate_password(8, false, false);

    ob_start();
    ?>
    <div id="<?php echo esc_attr($wrapper_id); ?>" class="icv-wrapper" style="--icv-height: <?php echo esc_attr($height); ?>; --icv-hide-top: <?php echo esc_attr($hide_top); ?>px; --icv-hide-bottom: <?php echo esc_attr($hide_bottom); ?>px; --icv-radius: <?php echo esc_attr($border_radius); ?>px;">
        <iframe
            src="<?php echo esc_url($url); ?>"
            class="icv-iframe"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen
        ></iframe>
        <div class="icv-mask icv-mask-top" aria-hidden="true"></div>
        <div class="icv-mask icv-mask-bottom" aria-hidden="true"></div>
    </div>
    <?php

    return ob_get_clean();
}

function icv_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Iframe Clean Viewer</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('icv_settings_group');
            do_settings_sections('iframe-clean-viewer');
            submit_button('Salva impostazioni');
            ?>
        </form>
    </div>
    <?php
}

/**
 * Shortcode: [iframe_clean_viewer]
 *
 * Attributi dello shortcode fanno overwrite dei default salvati in admin.
 */
function icv_render_iframe_shortcode($atts)
{
    $admin_defaults = icv_get_admin_defaults();

    $atts = shortcode_atts(
        array(
            'url' => $admin_defaults['url'],
            'height' => $admin_defaults['height'],
            'hide_top' => (string) $admin_defaults['hide_top'],
            'hide_bottom' => (string) $admin_defaults['hide_bottom'],
            'border_radius' => (string) $admin_defaults['border_radius'],
        ),
        $atts,
        'iframe_clean_viewer'
    );

    $url = esc_url_raw(trim((string) $atts['url']));

    if ($url === '') {
        return '<p><strong>Iframe Clean Viewer:</strong> configura i default in <em>Impostazioni &gt; Iframe Clean Viewer</em> o passa i parametri nello shortcode.</p>';
    }

    if (!wp_http_validate_url($url)) {
        return '<p><strong>Iframe Clean Viewer:</strong> URL non valida.</p>';
    }

    $height = icv_sanitize_height($atts['height']);
    $hide_top = icv_sanitize_positive_int($atts['hide_top']);
    $hide_bottom = icv_sanitize_positive_int($atts['hide_bottom']);
    $border_radius = icv_sanitize_positive_int($atts['border_radius']);

    $iframe_url = icv_build_iframe_url($url, $hide_top, $hide_bottom);

    return icv_render_iframe_markup($iframe_url, $height, $hide_top, $hide_bottom, $border_radius);
}
add_shortcode('iframe_clean_viewer', 'icv_render_iframe_shortcode');

class ICV_Iframe_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct('icv_iframe_widget', 'Iframe Clean Viewer', array('description' => 'Mostra un iframe configurabile con maschere header/footer.'));
    }

    public function widget($args, $instance)
    {
        $admin_defaults = icv_get_admin_defaults();

        $title = isset($instance['title']) ? sanitize_text_field($instance['title']) : '';
        $widget_url = isset($instance['url']) ? esc_url_raw(trim((string) $instance['url'])) : '';
        $url = $widget_url !== '' ? $widget_url : $admin_defaults['url'];

        if ($url === '' || !wp_http_validate_url($url)) {
            return;
        }

        $height = isset($instance['height']) && (string) $instance['height'] !== '' ? icv_sanitize_height($instance['height']) : $admin_defaults['height'];
        $hide_top = isset($instance['hide_top']) && (string) $instance['hide_top'] !== '' ? icv_sanitize_positive_int($instance['hide_top']) : $admin_defaults['hide_top'];
        $hide_bottom = isset($instance['hide_bottom']) && (string) $instance['hide_bottom'] !== '' ? icv_sanitize_positive_int($instance['hide_bottom']) : $admin_defaults['hide_bottom'];
        $border_radius = isset($instance['border_radius']) && (string) $instance['border_radius'] !== '' ? icv_sanitize_positive_int($instance['border_radius']) : $admin_defaults['border_radius'];

        echo $args['before_widget'];

        if ($title !== '') {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        $iframe_url = icv_build_iframe_url($url, $hide_top, $hide_bottom);

        echo icv_render_iframe_markup($iframe_url, $height, $hide_top, $hide_bottom, $border_radius); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $admin_defaults = icv_get_admin_defaults();
        $defaults = array(
            'title' => '',
            'url' => $admin_defaults['url'],
            'height' => $admin_defaults['height'],
            'hide_top' => (string) $admin_defaults['hide_top'],
            'hide_bottom' => (string) $admin_defaults['hide_bottom'],
            'border_radius' => (string) $admin_defaults['border_radius'],
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Titolo:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('url')); ?>">URL (opzionale):</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('url')); ?>" name="<?php echo esc_attr($this->get_field_name('url')); ?>" type="url" value="<?php echo esc_attr($instance['url']); ?>" placeholder="https://example.com">
            <small>Se vuoto, usa l'URL default in Impostazioni.</small>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('height')); ?>">Altezza iframe:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('height')); ?>" name="<?php echo esc_attr($this->get_field_name('height')); ?>" type="text" value="<?php echo esc_attr($instance['height']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('hide_top')); ?>">Nascondi top (px):</label>
            <input class="small-text" id="<?php echo esc_attr($this->get_field_id('hide_top')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_top')); ?>" type="number" min="0" value="<?php echo esc_attr($instance['hide_top']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('hide_bottom')); ?>">Nascondi bottom (px):</label>
            <input class="small-text" id="<?php echo esc_attr($this->get_field_id('hide_bottom')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_bottom')); ?>" type="number" min="0" value="<?php echo esc_attr($instance['hide_bottom']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('border_radius')); ?>">Border radius (px):</label>
            <input class="small-text" id="<?php echo esc_attr($this->get_field_id('border_radius')); ?>" name="<?php echo esc_attr($this->get_field_name('border_radius')); ?>" type="number" min="0" value="<?php echo esc_attr($instance['border_radius']); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');

        $url = esc_url_raw(trim((string) ($new_instance['url'] ?? '')));
        $instance['url'] = wp_http_validate_url($url) ? $url : '';

        $instance['height'] = icv_sanitize_height($new_instance['height'] ?? '70vh');
        $instance['hide_top'] = icv_sanitize_positive_int($new_instance['hide_top'] ?? 80);
        $instance['hide_bottom'] = icv_sanitize_positive_int($new_instance['hide_bottom'] ?? 80);
        $instance['border_radius'] = icv_sanitize_positive_int($new_instance['border_radius'] ?? 12);

        return $instance;
    }
}

function icv_register_widget()
{
    register_widget('ICV_Iframe_Widget');
}
add_action('widgets_init', 'icv_register_widget');

function icv_enqueue_inline_styles()
{
    $css = '
    .icv-wrapper {
        position: relative;
        width: 100%;
        height: var(--icv-height, 70vh);
        min-height: 320px;
        max-height: 100vh;
        overflow: hidden;
        border-radius: var(--icv-radius, 12px);
        background: #fff;
        box-shadow: 0 6px 24px rgba(0,0,0,.08);
    }

    .icv-iframe {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
    }

    .icv-mask {
        position: absolute;
        left: 0;
        width: 100%;
        pointer-events: none;
        z-index: 2;
        background: #fff;
    }

    .icv-mask-top {
        top: 0;
        height: var(--icv-hide-top, 80px);
        box-shadow: 0 6px 12px rgba(0,0,0,.04);
    }

    .icv-mask-bottom {
        bottom: 0;
        height: var(--icv-hide-bottom, 80px);
        box-shadow: 0 -6px 12px rgba(0,0,0,.04);
    }

    @media (max-width: 782px) {
        .icv-wrapper {
            height: min(var(--icv-height, 70vh), 75vh);
            min-height: 260px;
        }

        .icv-mask-top {
            height: min(var(--icv-hide-top, 80px), 64px);
        }

        .icv-mask-bottom {
            height: min(var(--icv-hide-bottom, 80px), 64px);
        }
    }
    ';

    wp_register_style('icv-inline-style', false);
    wp_enqueue_style('icv-inline-style');
    wp_add_inline_style('icv-inline-style', $css);
}
add_action('wp_enqueue_scripts', 'icv_enqueue_inline_styles');
