<?php
/**
 * Plugin Name: Portal Bíblico Integrado
 * Description: Integra o Portal Bíblico existente ao WordPress por uma área responsiva e compatível com Bootstrap.
 * Version: 1.0.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_shortcode')) {
    function add_shortcode(string $tag, callable $callback): void {}
}
if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback): void {}
}
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], $version = false, string $media = 'all'): void {}
}
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url(string $file): string { return ''; }
}
if (!function_exists('shortcode_atts')) {
    function shortcode_atts(array $pairs, array $atts, string $shortcode = ''): array { return array_merge($pairs, $atts); }
}
if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url): string { return filter_var($url, FILTER_SANITIZE_URL) ?: ''; }
}
if (!function_exists('esc_url')) {
    function esc_url(string $url): string { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

final class Portal_Biblico_Integrado
{
    public static function init(): void
    {
        add_shortcode('portal_biblico', [__CLASS__, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets(): void
    {
        wp_enqueue_style(
            'portal-biblico-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
            [],
            '5.3.3'
        );
        wp_enqueue_style(
            'portal-biblico-integrado',
            plugin_dir_url(__FILE__) . 'assets/portal-biblico.css',
            ['portal-biblico-bootstrap'],
            '1.0.0'
        );
    }

    public static function render_shortcode($atts = []): string
    {
        $atts = shortcode_atts([
            'url' => '',
            'altura' => '820px',
        ], $atts, 'portal_biblico');

        $url = esc_url_raw(trim((string) $atts['url']));
        if ($url === '') {
            return '<div class="alert alert-warning" role="alert">Configure o atributo <code>url</code> do shortcode.</div>';
        }

        $altura = preg_replace('/[^0-9a-z%.-]/i', '', (string) $atts['altura']);
        if ($altura === '') {
            $altura = '820px';
        }

        ob_start();
        ?>
        <section class="portal-biblico-integrado container-fluid px-0" aria-label="Portal Bíblico">
            <div class="ratio portal-biblico-frame-wrap">
                <iframe
                    class="portal-biblico-frame"
                    src="<?= esc_url($url) ?>"
                    title="Portal Bíblico"
                    loading="lazy"
                    style="height: <?= esc_attr($altura) ?>;"
                ></iframe>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}

Portal_Biblico_Integrado::init();
