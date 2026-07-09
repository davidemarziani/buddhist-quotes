<?php

/**
 * Plugin Name: Buddhist Quotes
 * Plugin URI: https://github.com/davidemarziani/buddhist-quotes
 * Description: Displays a Buddhist quote in the WordPress dashboard via buddha-api.com.
 * Version: 1.0.0
 * Author: Davide Marziani
 * Author URI: https://github.com/davidemarziani
 * License: GPL2+
 * Text Domain: buddhist-quotes
 */

defined('ABSPATH') || exit;

/**
 * API base endpoint and default mode.
 */
define('BQ_API_BASE_URL', 'https://buddha-api.com/api/');
define('BQ_API_MODE', 'random');

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
    // Perform API request
    $response = wp_remote_get(BQ_API_BASE_URL . BQ_API_MODE, [
        'timeout' => 5,
        'headers' => [
            'Accept' => 'application/json',
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
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'bq_enqueue_dashboard_widget_style');