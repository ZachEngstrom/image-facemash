<?php

/**
 * Plugin Name: Image Facemash
 * Plugin URI: https://gitlab.com/engza/wp-plugin-facemash
 * Description: Compare and rank images using an Elo rating system with a user-friendly interface.
 * Version: 1.0.0
 * Author: Zach Engstrom
 * Author URI: https://zachengstrom.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: image-facemash
 *
 * @package ImageFacemash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

require_once dirname(__FILE__) . '/inc/db.php';
require_once dirname(__FILE__) . '/inc/admin.php';
require_once dirname(__FILE__) . '/inc/frontend.php';
require_once dirname(__FILE__) . '/inc/ajax.php';

/**
 * Adds a settings link to the plugin action links in the Plugins list table.
 *
 * @param array $links Existing action links for the plugin
 * @return array Modified action links with settings added
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'image_facemash_add_settings_link');
function image_facemash_add_settings_link(array $links): array {
  // Create the settings page URL
  $settings_url = admin_url('options-general.php?page=image-facemash-settings');

  // Add the "Settings" link before existing links
  $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'image-facemash') . '</a>';
  array_unshift($links, $settings_link);

  return $links;
}
