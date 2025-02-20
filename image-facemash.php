<?php
/*
Plugin Name: Image Facemash
Description: Compare images from the media library using an Elo rating system.
Version: 1.0
Author: Zach Engstrom
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

require_once dirname(__FILE__) . '/inc/db.php';
require_once dirname(__FILE__) . '/inc/frontend.php';
require_once dirname(__FILE__) . '/inc/ajax.php';
