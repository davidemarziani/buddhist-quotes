<?php

/**
 * Plugin Name: Buddhist Quotes
 * Plugin URI: https://github.com/davidemarziani/buddhist-quotes
 * Description: Displays a Buddhist quote in the WordPress dashboard via api.davidemarziani.com.
 * Version: 1.1.0
 * Author: Davide Marziani
 * Author URI: https://github.com/davidemarziani
 * License: GPL2+
 * Text Domain: buddhist-quotes
 */

defined('ABSPATH') || exit;

/**
 * API endpoint. Single route, mirrors the old buddha-api.com /api/random
 * shape (text / byName / byImage), so parsing below is unchanged.
 */
define('BQ_API_BASE_URL', 'https://api.davidemarziani.com/buddhist-quotes/');

/**
 * Option name storing the shared API key (set via Settings > Buddhist Quotes).
 */
define('BQ_API_KEY_OPTION', 'bq_api_key');

/**
 * Registers the "API Key" setting and its admin page under Settings.
 */
function bq_register_settings()
{
    register_setting('bq_settings_group', BQ_API_KEY_OPTION, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ]);

    add_settings_section(
        'bq_settings_section',
        esc_html__('API Configuration', 'buddhist-quotes'),
        '__return_false',
        'bq_settings'
    );

    add_settings_field(
        'bq_api_key_field',
        esc_html__('API Key', 'buddhist-quotes'),
        'bq_api_key_field_render',
        'bq_settings',
        'bq_settings_section'
    );
}
add_action('admin_init', 'bq_register_settings');

/**
 * Adds the "Buddhist Quotes" entry under Settings.
 */
function bq_add_settings_page()
{
    add_options_page(
        esc_html__('Buddhist Quotes', 'buddhist-quotes'),
        esc_html__('Buddhist Quotes', 'buddhist-quotes'),
        'manage_options',
        'bq_settings',
        'bq_settings_page_render'
    );
}
add_action('admin_menu', 'bq_add_settings_page');

/**
 * Renders the API Key field.
 */
function bq_api_key_field_render()
{
    $value = get_option(BQ_API_KEY_OPTION, '');
    echo '<input type="password" name="' . esc_attr(BQ_API_KEY_OPTION) . '" value="' . esc_attr($value) . '" class="regular-text" autocomplete="off">';
    echo '<p class="description">' . esc_html__('Shared secret for api.davidemarziani.com (sent as the X-Api-Key header).', 'buddhist-quotes') . '</p>';
}

/**
 * Renders the settings page.
 */
function bq_settings_page_render()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap"><h1>' . esc_html__('Buddhist Quotes', 'buddhist-quotes') . '</h1><form action="options.php" method="post">';
    settings_fields('bq_settings_group');
    do_settings_sections('bq_settings');
    submit_button();
    echo '</form></div>';
}

/**
 * Registers the dashboard widget.
 */
function bq_add_dashboard_widgets()
{
    wp_add_dashboard_widget(
        'bq_dashboard_widget',
        esc_html__('Buddhist Quotes', 'buddhist-quotes'),
        'bq_dashboard_widget_render'
    );
}
add_action('wp_dashboard_setup', 'bq_add_dashboard_widgets');

/**
 * Renders the dashboard widget content.
 */
function bq_dashboard_widget_render()
{
    // Require an API key before even trying the request
    $api_key = get_option(BQ_API_KEY_OPTION, '');
    if (empty($api_key)) {
        printf(
            /* translators: %s: URL of the plugin settings page */
            esc_html__('Please set an API key in %s.', 'buddhist-quotes'),
            '<a href="' . esc_url(admin_url('options-general.php?page=bq_settings')) . '">' . esc_html__('Settings > Buddhist Quotes', 'buddhist-quotes') . '</a>'
        );
        return;
    }

    // Perform API request
    $response = wp_remote_get(BQ_API_BASE_URL, [
        'timeout' => 5,
        'headers' => [
            'Accept'    => 'application/json',
            'X-Api-Key' => $api_key,
        ],
    ]);

    // Request error
    if (is_wp_error($response)) {
        echo esc_html__('Unable to retrieve quote at the moment.', 'buddhist-quotes');
        return;
    }

    // Invalid HTTP status
    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        echo esc_html__('Unable to retrieve quote at the moment.', 'buddhist-quotes');
        return;
    }

    // Empty response body
    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        echo esc_html__('No quote available.', 'buddhist-quotes');
        return;
    }

    // Decode JSON
    $data = json_decode($body);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo esc_html__('Invalid response from quote service.', 'buddhist-quotes');
        return;
    }

    // Missing quote text
    if (empty($data->text)) {
        echo esc_html__('No quote available.', 'buddhist-quotes');
        return;
    }

    // Extract fields
    $quote = $data->text;
    $name  = $data->byName ?? '';
    $image = $data->byImage ?? '';

    // Output widget HTML
    echo '<div class="bq-quote-widget">';
    echo '<p class="bq-quote">' . esc_html($quote) . '</p>';
    echo '<div class="bq-quote-author">';

    if ($image) {
        echo '<img src="' . esc_url($image) . '" alt="' . esc_attr($name) . '">';
    }

    if ($name) {
        echo '<strong>' . esc_html($name) . '</strong>';
    }

    echo '</div></div>';
}

/**
 * Enqueues dashboard style
 */
function bq_enqueue_dashboard_widget_style($hook)
{
    if ($hook !== 'index.php') {
        return;
    }

    wp_enqueue_style(
        'bq-dashboard-widget-style',
        plugin_dir_url(__FILE__) . 'assets/css/buddhist-quotes.css',
        [],
        '1.1.0'
    );
}
add_action('admin_enqueue_scripts', 'bq_enqueue_dashboard_widget_style');