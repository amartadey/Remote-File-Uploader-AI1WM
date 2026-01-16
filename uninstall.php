<?php
/**
 * Uninstall script for Remote File Uploader for AI1WM
 * 
 * This file is executed when the plugin is deleted via the WordPress admin.
 */

// Exit if accessed directly or not in uninstall context
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up progress file
$progress_file = WP_CONTENT_DIR . '/rfu-ai1wm-progress.json';
if (file_exists($progress_file)) {
    @unlink($progress_file);
}

// Optional: Remove plugin options if any were stored
// delete_option('rfu_ai1wm_option_name');

// Note: We do NOT delete the ai1wm-backups directory or its contents
// as users may want to keep their backup files even after uninstalling this plugin
