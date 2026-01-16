=== Remote File Uploader for AI1WM ===
Contributors: yourname
Tags: backup, migration, all-in-one-wp-migration, remote upload, cloud transfer
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Upload backup files from remote URLs directly to All-in-One WP Migration backups folder.

== Description ==

Remote File Uploader for AI1WM is a companion plugin that allows you to transfer backup files from remote URLs (cloud storage, other servers, etc.) directly to your All-in-One WP Migration backups folder without manually downloading and re-uploading files.

**Key Features:**

* Upload files from any publicly accessible URL
* Real-time progress tracking with visual progress bar
* Automatic file naming from source URL
* Works with any file type (designed for .wpress backups)
* Clean, user-friendly admin interface
* No file size limitations (server-dependent)
* Secure AJAX-based uploads with nonce verification

**Use Cases:**

* Transfer backups from cloud storage (Dropbox, Google Drive, etc.)
* Move backups between servers
* Download backups from remote backup services
* Migrate sites using backup files hosted elsewhere

**Important Notes:**

* This plugin is compatible with All-in-One WP Migration but is not affiliated with or endorsed by ServMask Inc.
* Files are downloaded to `wp-content/ai1wm-backups` directory
* Requires cURL extension enabled on your server
* Remote URLs must be publicly accessible

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/remote-file-uploader-ai1wm` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Tools > Remote File Uploader to use the plugin.
4. Enter the URL of your backup file and click "Start Upload".

== Frequently Asked Questions ==

= Does this plugin require All-in-One WP Migration to be installed? =

While it's designed to work with All-in-One WP Migration and saves files to the AI1WM backups folder, the plugin will create the directory if it doesn't exist. However, to restore the backups, you'll need AI1WM installed.

= What file types are supported? =

The plugin can download any file type, but it's primarily designed for .wpress backup files used by All-in-One WP Migration.

= Is there a file size limit? =

The plugin itself has no file size limit. However, your server's configuration (disk space, memory limit, execution time) may impose practical limits.

= Can I upload files from password-protected URLs? =

Currently, the plugin only supports publicly accessible URLs. Authentication support may be added in future versions.

= Is this plugin affiliated with All-in-One WP Migration? =

No, this is an independent plugin that is compatible with All-in-One WP Migration. It is not affiliated with or endorsed by ServMask Inc.

= Where are the files saved? =

Files are saved to `wp-content/ai1wm-backups` directory, which is the standard backup location for All-in-One WP Migration.

== Screenshots ==

1. Main upload interface with URL input field
2. Real-time progress tracking during file download
3. Success message after upload completion
4. Information panel with usage instructions

== Changelog ==

= 1.0.0 =
* Initial release
* Remote URL upload functionality
* Real-time progress tracking
* Clean admin interface
* Security features (nonce verification, capability checks)

== Upgrade Notice ==

= 1.0.0 =
Initial release of Remote File Uploader for AI1WM.

== Technical Details ==

**Server Requirements:**
* WordPress 5.0 or higher
* PHP 7.2 or higher
* cURL extension enabled
* Sufficient disk space for backup files

**Security Features:**
* WordPress nonce verification
* Capability checks (manage_options)
* URL validation and sanitization
* Secure AJAX handlers

**Developer Notes:**
* Follows WordPress Coding Standards
* Uses WordPress HTTP API and cURL
* Implements proper error handling
* Includes progress tracking via JSON file
* Clean uninstall (removes progress files)

== Privacy Policy ==

This plugin does not collect, store, or transmit any user data. All file transfers occur directly between the remote server and your WordPress installation.

== Support ==

For support, please visit the plugin's support forum or contact the developer.

== Credits ==

Developed independently as a companion tool for All-in-One WP Migration users.
