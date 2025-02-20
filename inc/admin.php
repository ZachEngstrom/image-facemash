<?php

/**
 * Admin settings page for the Image Facemash plugin.
 *
 * Handles the creation and management of the plugin's settings page in the WordPress admin dashboard.
 *
 * @package ImageFacemash
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Registers the admin menu for Image Facemash settings.
 *
 * @return void
 */
add_action('admin_menu', 'image_facemash_admin_menu');
function image_facemash_admin_menu(): void {
  // Add a settings page under the "Settings" menu
  add_options_page(
    'Image Facemash Settings',        // Page title
    'Image Facemash',                 // Menu title
    'manage_options',                 // Capability required
    'image-facemash-settings',        // Menu slug
    'image_facemash_settings_page'    // Callback function
  );
}

/**
 * Initializes and registers the settings for Image Facemash.
 *
 * @return void
 */
add_action('admin_init', 'image_facemash_register_settings');
function image_facemash_register_settings(): void {
  // Register the settings group
  register_setting(
    'image_facemash_settings_group', // Option group
    'image_facemash_options',        // Option name
    'image_facemash_sanitize_options' // Sanitization callback
  );

  // Add settings section
  add_settings_section(
    'image_facemash_main_section',   // Section ID
    'Main Settings',                 // Section title
    null,                            // Callback (none needed here)
    'image-facemash-settings'        // Page slug
  );

  // Add "Items per Page" field
  add_settings_field(
    'items_per_page',                // Field ID
    'Items per Page',                // Field title
    'image_facemash_items_per_page_callback', // Callback function
    'image-facemash-settings',       // Page slug
    'image_facemash_main_section'    // Section ID
  );

  // Add "Elo K-Factor" field
  add_settings_field(
    'elo_k_factor',                  // Field ID
    'Elo K-Factor',                  // Field title
    'image_facemash_elo_k_factor_callback', // Callback function
    'image-facemash-settings',       // Page slug
    'image_facemash_main_section'    // Section ID
  );
}

/**
 * Sanitizes the Image Facemash settings input.
 *
 * @param array $input The input values from the settings form
 * @return array Sanitized values
 */
function image_facemash_sanitize_options(array $input): array {
  $sanitized = [];

  // Sanitize Items per Page (positive integer, default 10)
  $sanitized['items_per_page'] = isset($input['items_per_page'])
    ? max(1, absint($input['items_per_page'])) // Ensure at least 1
    : 10;

  // Sanitize Elo K-Factor (positive integer, default 32)
  $sanitized['elo_k_factor'] = isset($input['elo_k_factor'])
    ? max(1, absint($input['elo_k_factor'])) // Ensure at least 1
    : 32;

  return $sanitized;
}

/**
 * Renders the "Items per Page" settings field.
 *
 * @return void
 */
function image_facemash_items_per_page_callback(): void {
  $options = get_option('image_facemash_options', ['items_per_page' => 10]);
  $value = esc_attr($options['items_per_page']);
?>
  <input type="number"
    name="image_facemash_options[items_per_page]"
    value="<?php echo $value; ?>"
    min="1"
    step="1"
    class="small-text" />
  <p class="description">Number of items per page in the results table (default: 10).</p>
<?php
}

/**
 * Renders the "Elo K-Factor" settings field.
 *
 * @return void
 */
function image_facemash_elo_k_factor_callback(): void {
  $options = get_option('image_facemash_options', ['elo_k_factor' => 32]);
  $value = esc_attr($options['elo_k_factor']);
?>
  <input type="number"
    name="image_facemash_options[elo_k_factor]"
    value="<?php echo $value; ?>"
    min="1"
    step="1"
    class="small-text" />
  <p class="description">Elo K-Factor for rating calculations (default: 32).</p>
<?php
}

/**
 * Renders the Image Facemash settings page.
 *
 * @return void
 */
function image_facemash_settings_page(): void {
?>
  <div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="options.php">
      <?php
      // Output settings fields
      settings_fields('image_facemash_settings_group');
      do_settings_sections('image-facemash-settings');
      submit_button();
      ?>
    </form>
  </div>
<?php
}

/**
 * Retrieves a specific Image Facemash option value.
 *
 * @param string $key The option key to retrieve
 * @param mixed $default The default value if the option is not set
 * @return mixed The option value or default
 */
function image_facemash_get_option(string $key, $default = null) {
  $options = get_option('image_facemash_options', [
    'items_per_page' => 10,
    'elo_k_factor' => 32
  ]);
  return $options[$key] ?? $default;
}
