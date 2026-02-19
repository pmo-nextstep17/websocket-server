<?php
/**
 * Plugin Name: Iframe Clean Viewer
 * Description: Mostra una pagina esterna dentro un iframe con maschere opzionali per nascondere visivamente header e footer del sito incorporato.
 * Version: 1.3.0
 * Author: Codex
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ICV_OPTION_DEFAULT_URL', 'icv_default_url');

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
 * Registra impostazioni plugin.
 */
function icv_register_settings()
{
    register_setting(
        'icv_settings_group',
        ICV_OPTION_DEFAULT_URL,
        array(
            'type' => 'string',
            'sanitize_callback' => 'icv_sanitize_admin_url',
            'default' => '',
        )
    );

    add_settings_section(
        'icv_main_section',
        'Impostazioni principali',
        '__return_false',
        'iframe-clean-viewer'
    );

    add_settings_field(
        ICV_OPTION_DEFAULT_URL,
        'URL predefinito iframe',
        'icv_render_default_url_field',
        'iframe-clean-viewer',
        'icv_main_section'
    );
}
add_action('admin_init', 'icv_register_settings');

/**
 * Campo URL nelle impostazioni admin.
 */
function icv_render_default_url_field()
{
    $value = get_option(ICV_OPTION_DEFAULT_URL, '');
    ?>
    <input
        type="url"
        class="regular-text"
        name="<?php echo esc_attr(ICV_OPTION_DEFAULT_URL); ?>"
        value="<?php echo esc_attr($value); ?>"
        placeholder="https://example.com"
    />
    <p class="description">Questo URL verrà usato nello shortcode quando non passi l'attributo <code>url</code>.</p>
    <?php
}

/**
 * Crea pagina impostazioni plugin in admin.
 */
function icv_add_settings_page()
{
    add_options_page(
        'Iframe Clean Viewer',
        'Iframe Clean Viewer',
        'manage_options',
        'iframe-clean-viewer',
        'icv_render_settings_page'
    );
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
 *
 * Parametri aggiunti:
 * - source_domain: dominio d'origine (sito WordPress chiamante)
 * - hide_header: valore maschera superiore in px
 * - hide_footer: valore maschera inferiore in px
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

/**
 * Render pagina impostazioni plugin.
 */
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
 * Attributi:
 * - url (opzionale): URL del sito da aprire. Se omesso usa URL salvato in amministrazione.
 * - height: altezza iframe (default: 80vh).
 * - hide_top: altezza maschera superiore (default: 80px).
 * - hide_bottom: altezza maschera inferiore (default: 80px).
 * - border_radius: border radius contenitore (default: 12px).
 */
function icv_render_iframe_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'url' => '',
            'height' => '80vh',
            'hide_top' => '80',
            'hide_bottom' => '80',
            'border_radius' => '12',
        ),
        $atts,
        'iframe_clean_viewer'
    );

    $configured_url = get_option(ICV_OPTION_DEFAULT_URL, '');
    $resolved_url = trim((string) $atts['url']) !== '' ? $atts['url'] : $configured_url;
    $url = esc_url_raw(trim((string) $resolved_url));

    if (empty($url)) {
        return '<p><strong>Iframe Clean Viewer:</strong> configura un URL in <em>Impostazioni &gt; Iframe Clean Viewer</em> oppure passa l\'attributo <code>url</code> nello shortcode.</p>';
    }

    if (!wp_http_validate_url($url)) {
        return '<p><strong>Iframe Clean Viewer:</strong> URL non valida.</p>';
    }

    $height = preg_replace('/[^0-9a-zA-Z.%-]/', '', (string) $atts['height']);
    $hide_top = max(0, intval($atts['hide_top']));
    $hide_bottom = max(0, intval($atts['hide_bottom']));
    $border_radius = max(0, intval($atts['border_radius']));

    $iframe_url = icv_build_iframe_url($url, $hide_top, $hide_bottom);

    return icv_render_iframe_markup($iframe_url, $height, $hide_top, $hide_bottom, $border_radius);
}
add_shortcode('iframe_clean_viewer', 'icv_render_iframe_shortcode');

/**
 * Widget class per mostrare iframe nelle aree widget.
 */
class ICV_Iframe_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'icv_iframe_widget',
            'Iframe Clean Viewer',
            array('description' => 'Mostra un iframe configurabile con maschere header/footer.')
        );
    }

    public function widget($args, $instance)
    {
        $title = isset($instance['title']) ? sanitize_text_field($instance['title']) : '';
        $widget_url = isset($instance['url']) ? esc_url_raw(trim((string) $instance['url'])) : '';
        $configured_url = get_option(ICV_OPTION_DEFAULT_URL, '');
        $url = $widget_url !== '' ? $widget_url : $configured_url;

        if ($url === '' || !wp_http_validate_url($url)) {
            return;
        }

        $height = isset($instance['height']) ? preg_replace('/[^0-9a-zA-Z.%-]/', '', (string) $instance['height']) : '80vh';
        $hide_top = isset($instance['hide_top']) ? max(0, intval($instance['hide_top'])) : 80;
        $hide_bottom = isset($instance['hide_bottom']) ? max(0, intval($instance['hide_bottom'])) : 80;
        $border_radius = isset($instance['border_radius']) ? max(0, intval($instance['border_radius'])) : 12;

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
        $defaults = array(
            'title' => '',
            'url' => '',
            'height' => '80vh',
            'hide_top' => '80',
            'hide_bottom' => '80',
            'border_radius' => '12',
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
            <small>Se lasci vuoto, usa l'URL salvato in Impostazioni &gt; Iframe Clean Viewer.</small>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('height')); ?>">Altezza iframe (es. 80vh o 600px):</label>
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

        $instance['height'] = preg_replace('/[^0-9a-zA-Z.%-]/', '', (string) ($new_instance['height'] ?? '80vh'));
        $instance['hide_top'] = max(0, intval($new_instance['hide_top'] ?? 80));
        $instance['hide_bottom'] = max(0, intval($new_instance['hide_bottom'] ?? 80));
        $instance['border_radius'] = max(0, intval($new_instance['border_radius'] ?? 12));

        return $instance;
    }
}

/**
 * Registra widget.
 */
function icv_register_widget()
{
    register_widget('ICV_Iframe_Widget');
}
add_action('widgets_init', 'icv_register_widget');

/**
 * Stili inline minimi per evitare file aggiuntivi.
 */
function icv_enqueue_inline_styles()
{
    $css = '
    .icv-wrapper {
        position: relative;
        width: 100%;
        height: var(--icv-height, 80vh);
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
    ';

    wp_register_style('icv-inline-style', false);
    wp_enqueue_style('icv-inline-style');
    wp_add_inline_style('icv-inline-style', $css);
}
add_action('wp_enqueue_scripts', 'icv_enqueue_inline_styles');
