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
