<?php

// Activation hook to create database table
register_activation_hook(__FILE__, 'image_facemash_install');
function image_facemash_install() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        image_id BIGINT(20) UNSIGNED NOT NULL,
        rating INT NOT NULL DEFAULT 1500,
        PRIMARY KEY (id),
        UNIQUE KEY image_id (image_id)
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
