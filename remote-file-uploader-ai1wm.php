<?php
/**
 * Plugin Name: Remote File Uploader for AI1WM
 * Plugin URI: https://amartadey.github.io/Remote-File-Uploader-AI1WM/
 * Description: Upload backup files from remote URLs directly to All-in-One WP Migration backups folder. Compatible with AI1WM.
 * Version: 1.0.1
 * Author: Amarta Dey
 * Author URI: https://amartadey.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: remote-file-uploader-ai1wm
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RFU_AI1WM_VERSION', '1.0.0');
define('RFU_AI1WM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RFU_AI1WM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RFU_AI1WM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Remote_File_Uploader_AI1WM {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_rfu_ai1wm_upload_file', array($this, 'ajax_upload_file'));
        add_action('wp_ajax_rfu_ai1wm_get_progress', array($this, 'ajax_get_progress'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));

    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('remote-file-uploader-ai1wm', false, dirname(RFU_AI1WM_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_management_page(
            __('Remote File Uploader for AI1WM', 'remote-file-uploader-ai1wm'),
            __('Remote File Uploader', 'remote-file-uploader-ai1wm'),
            'manage_options',
            'remote-file-uploader-ai1wm',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('tools_page_remote-file-uploader-ai1wm' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'rfu-ai1wm-admin',
            RFU_AI1WM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            RFU_AI1WM_VERSION
        );
        
        wp_enqueue_script(
            'rfu-ai1wm-admin',
            RFU_AI1WM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            RFU_AI1WM_VERSION,
            true
        );
        
        wp_localize_script('rfu-ai1wm-admin', 'rfuAi1wm', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rfu_ai1wm_nonce'),
            'strings' => array(
                'uploading' => __('Uploading...', 'remote-file-uploader-ai1wm'),
                'success' => __('File uploaded successfully!', 'remote-file-uploader-ai1wm'),
                'error' => __('An error occurred. Please try again.', 'remote-file-uploader-ai1wm'),
            )
        ));
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        include RFU_AI1WM_PLUGIN_DIR . 'includes/admin-page.php';
    }
    
    /**
     * AJAX handler for file upload
     */
    public function ajax_upload_file() {
        // Log for debugging
        error_log('RFU AI1WM: AJAX upload handler called');
        error_log('RFU AI1WM: POST data: ' . print_r($_POST, true));
        
        check_ajax_referer('rfu_ai1wm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            error_log('RFU AI1WM: Unauthorized access attempt');
            wp_send_json_error(array('message' => __('Unauthorized access.', 'remote-file-uploader-ai1wm')));
        }
        
        $source_url = isset($_POST['source_url']) ? esc_url_raw($_POST['source_url']) : '';
        
        if (empty($source_url)) {
            error_log('RFU AI1WM: Empty URL provided');
            wp_send_json_error(array('message' => __('Please provide a valid URL.', 'remote-file-uploader-ai1wm')));
        }
        
        // Validate URL
        if (!filter_var($source_url, FILTER_VALIDATE_URL)) {
            error_log('RFU AI1WM: Invalid URL format: ' . $source_url);
            wp_send_json_error(array('message' => __('Invalid URL format.', 'remote-file-uploader-ai1wm')));
        }
        
        error_log('RFU AI1WM: Starting download from: ' . $source_url);
        
        // Get AI1WM backup directory
        $backup_dir = $this->get_ai1wm_backup_dir();
        
        if (!$backup_dir) {
            error_log('RFU AI1WM: Backup directory not found or not writable');
            wp_send_json_error(array('message' => __('AI1WM backup directory not found. Please ensure All-in-One WP Migration is installed.', 'remote-file-uploader-ai1wm')));
        }
        
        error_log('RFU AI1WM: Backup directory: ' . $backup_dir);
        
        // Extract filename from URL
        $filename = basename(parse_url($source_url, PHP_URL_PATH));
        
        // Sanitize filename
        $filename = sanitize_file_name($filename);
        
        if (empty($filename)) {
            $filename = 'backup-' . time() . '.wpress';
        }
        
        // Make filename unique if file already exists
        $filename = $this->get_unique_filename($backup_dir, $filename);
        
        $destination_path = trailingslashit($backup_dir) . $filename;
        
        error_log('RFU AI1WM: Destination path: ' . $destination_path);
        
        // Increase limits for large files
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        
        error_log('RFU AI1WM: Memory limit set to: ' . ini_get('memory_limit'));
        error_log('RFU AI1WM: Max execution time: ' . ini_get('max_execution_time'));
        
        // Disable output buffering to prevent timeouts
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Start the download process directly (not in background)
        $result = $this->download_remote_file($source_url, $destination_path, $filename);
        
        if (is_wp_error($result)) {
            error_log('RFU AI1WM: Download failed: ' . $result->get_error_message());
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        error_log('RFU AI1WM: Download completed successfully: ' . $filename);
        
        wp_send_json_success(array(
            'message' => __('File uploaded successfully!', 'remote-file-uploader-ai1wm'),
            'filename' => $filename,
            'path' => $destination_path,
            'size' => filesize($destination_path)
        ));
    }
    

    
    /**
     * Alternative: Direct download with better timeout handling
     */
    public function ajax_upload_file_direct() {
        check_ajax_referer('rfu_ai1wm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access.', 'remote-file-uploader-ai1wm')));
        }
        
        $source_url = isset($_POST['source_url']) ? esc_url_raw($_POST['source_url']) : '';
        
        if (empty($source_url)) {
            wp_send_json_error(array('message' => __('Please provide a valid URL.', 'remote-file-uploader-ai1wm')));
        }
        
        if (!filter_var($source_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => __('Invalid URL format.', 'remote-file-uploader-ai1wm')));
        }
        
        $backup_dir = $this->get_ai1wm_backup_dir();
        
        if (!$backup_dir) {
            wp_send_json_error(array('message' => __('AI1WM backup directory not found. Please ensure All-in-One WP Migration is installed.', 'remote-file-uploader-ai1wm')));
        }
        
        $filename = basename(parse_url($source_url, PHP_URL_PATH));
        $filename = sanitize_file_name($filename);
        
        if (empty($filename)) {
            $filename = 'backup-' . time() . '.wpress';
        }
        
        $filename = $this->get_unique_filename($backup_dir, $filename);
        $destination_path = trailingslashit($backup_dir) . $filename;
        
        // Increase limits
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        
        // Start the upload process
        $result = $this->download_remote_file($source_url, $destination_path, $filename);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => __('File uploaded successfully!', 'remote-file-uploader-ai1wm'),
            'filename' => $filename,
            'path' => $destination_path
        ));
    }
    
    /**
     * Get unique filename by appending number if file exists
     */
    private function get_unique_filename($directory, $filename) {
        $destination_path = trailingslashit($directory) . $filename;
        
        // If file doesn't exist, return original filename
        if (!file_exists($destination_path)) {
            return $filename;
        }
        
        // File exists, so we need to add a number
        $pathinfo = pathinfo($filename);
        $basename = $pathinfo['filename'];
        $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
        
        $counter = 2;
        while (file_exists(trailingslashit($directory) . $basename . '-' . $counter . $extension)) {
            $counter++;
        }
        
        return $basename . '-' . $counter . $extension;
    }
    
    /**
     * Get AI1WM backup directory
     */
    private function get_ai1wm_backup_dir() {
        $backup_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ai1wm-backups';
        
        // Create directory if it doesn't exist
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        // Verify directory is writable
        if (!is_writable($backup_dir)) {
            return false;
        }
        
        return $backup_dir;
    }
    
    /**
     * Download remote file with memory-efficient chunked processing (512KB chunks)
     */
    private function download_remote_file($source_url, $destination_path, $filename = '') {
        error_log('RFU AI1WM: download_remote_file() called');
        
        // Set unlimited execution time
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');
        
        // Create progress file
        $progress_file = WP_CONTENT_DIR . '/rfu-ai1wm-progress.json';
        
        // Initialize progress with start time
        $start_time = microtime(true);
        $this->update_progress($progress_file, 0, 0, 0, 'starting', $filename, '', $start_time);
        
        error_log('RFU AI1WM: Opening destination file: ' . $destination_path);
        
        // Open destination file
        $fp = @fopen($destination_path, 'w+');
        
        if (!$fp) {
            error_log('RFU AI1WM: Failed to open destination file');
            return new WP_Error('file_error', __('Could not open destination file for writing.', 'remote-file-uploader-ai1wm'));
        }
        
        error_log('RFU AI1WM: Initializing cURL');
        
        // Initialize cURL
        $ch = curl_init($source_url);
        
        if (!$ch) {
            fclose($fp);
            error_log('RFU AI1WM: Failed to initialize cURL');
            return new WP_Error('curl_error', __('Could not initialize cURL.', 'remote-file-uploader-ai1wm'));
        }
        
        // Set cURL options for chunked download (512KB buffer)
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 524288); // 512KB chunks for memory efficiency
        
        error_log('RFU AI1WM: cURL buffer size set to 512KB for memory-efficient chunked processing');
        
        // Add User-Agent and headers to prevent HTTP 418 and anti-bot blocking
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1'
        ));
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Enable automatic decompression
        
        // Progress callback with speed and ETA calculation
        $last_update_time = $start_time;
        $last_downloaded = 0;
        
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($progress_file, $filename, $start_time, &$last_update_time, &$last_downloaded) {
            if ($download_size > 0) {
                $current_time = microtime(true);
                $progress = round(($downloaded / $download_size) * 100, 2);
                
                // Calculate speed (bytes per second)
                $elapsed_time = $current_time - $start_time;
                $speed = $elapsed_time > 0 ? $downloaded / $elapsed_time : 0;
                
                // Calculate ETA (seconds remaining)
                $remaining_bytes = $download_size - $downloaded;
                $eta = $speed > 0 ? $remaining_bytes / $speed : 0;
                
                // Update progress every 0.5 seconds to avoid too many writes
                if ($current_time - $last_update_time >= 0.5 || $downloaded == $download_size) {
                    $this->update_progress($progress_file, $progress, $downloaded, $download_size, 'downloading', $filename, '', $start_time, $speed, $eta);
                    $last_update_time = $current_time;
                    $last_downloaded = $downloaded;
                    
                    error_log(sprintf('RFU AI1WM: Progress: %.2f%% (%s / %s) Speed: %s/s ETA: %s', 
                        $progress, 
                        $this->format_bytes($downloaded), 
                        $this->format_bytes($download_size),
                        $this->format_bytes($speed),
                        $this->format_time($eta)
                    ));
                }
            }
        });
        
        error_log('RFU AI1WM: Starting cURL execution');
        
        // Execute cURL
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_info = curl_getinfo($ch);
        
        error_log('RFU AI1WM: cURL execution completed. HTTP Code: ' . $http_code);
        error_log('RFU AI1WM: Total time: ' . $curl_info['total_time'] . 's');
        error_log('RFU AI1WM: Download size: ' . $this->format_bytes($curl_info['size_download']));
        error_log('RFU AI1WM: Average speed: ' . $this->format_bytes($curl_info['speed_download']) . '/s');
        
        curl_close($ch);
        fclose($fp);
        
        if (!$result) {
            @unlink($destination_path);
            error_log('RFU AI1WM: cURL error: ' . $error);
            $this->update_progress($progress_file, 0, 0, 0, 'error', $filename, sprintf(__('Download failed: %s', 'remote-file-uploader-ai1wm'), $error));
            return new WP_Error('download_error', sprintf(__('Download failed: %s', 'remote-file-uploader-ai1wm'), $error));
        }
        
        if ($http_code !== 200) {
            @unlink($destination_path);
            error_log('RFU AI1WM: HTTP error: ' . $http_code);
            $this->update_progress($progress_file, 0, 0, 0, 'error', $filename, sprintf(__('HTTP Error: %d', 'remote-file-uploader-ai1wm'), $http_code));
            return new WP_Error('http_error', sprintf(__('HTTP Error: %d', 'remote-file-uploader-ai1wm'), $http_code));
        }
        
        $final_size = filesize($destination_path);
        $total_time = microtime(true) - $start_time;
        
        error_log('RFU AI1WM: Download complete! Final size: ' . $this->format_bytes($final_size) . ' in ' . $this->format_time($total_time));
        
        // Update progress to complete
        $this->update_progress($progress_file, 100, $final_size, $final_size, 'complete', $filename, '', $start_time, 0, 0);
        
        return true;
    }
    
    /**
     * Format bytes to human-readable format
     */
    private function format_bytes($bytes, $decimals = 2) {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
    }
    
    /**
     * Format time to human-readable format
     */
    private function format_time($seconds) {
        if ($seconds < 60) {
            return round($seconds) . 's';
        } elseif ($seconds < 3600) {
            return floor($seconds / 60) . 'm ' . round($seconds % 60) . 's';
        } else {
            return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
        }
    }
    
    /**
     * Update progress file with enhanced data (speed, ETA, etc.)
     */
    private function update_progress($progress_file, $progress, $downloaded, $total, $status, $filename = '', $error_message = '', $start_time = 0, $speed = 0, $eta = 0) {
        $data = array(
            'progress' => $progress,
            'downloaded' => $downloaded,
            'total' => $total,
            'status' => $status,
            'filename' => $filename,
            'timestamp' => time(),
            'speed' => $speed, // bytes per second
            'eta' => $eta, // seconds remaining
            'start_time' => $start_time,
            'elapsed_time' => $start_time > 0 ? microtime(true) - $start_time : 0
        );
        
        if (!empty($error_message)) {
            $data['error'] = $error_message;
        }
        
        file_put_contents($progress_file, json_encode($data));
    }
    
    /**
     * AJAX handler for getting progress
     */
    public function ajax_get_progress() {
        check_ajax_referer('rfu_ai1wm_nonce', 'nonce');
        
        $progress_file = WP_CONTENT_DIR . '/rfu-ai1wm-progress.json';
        
        if (file_exists($progress_file)) {
            $data = json_decode(file_get_contents($progress_file), true);
            wp_send_json_success($data);
        } else {
            wp_send_json_success(array(
                'progress' => 0,
                'downloaded' => 0,
                'total' => 0,
                'status' => 'idle'
            ));
        }
    }
}

// Initialize plugin
function rfu_ai1wm_init() {
    return Remote_File_Uploader_AI1WM::get_instance();
}

add_action('plugins_loaded', 'rfu_ai1wm_init');

// Activation hook
register_activation_hook(__FILE__, 'rfu_ai1wm_activate');

function rfu_ai1wm_activate() {
    // Create backup directory if it doesn't exist
    $backup_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ai1wm-backups';
    if (!file_exists($backup_dir)) {
        wp_mkdir_p($backup_dir);
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'rfu_ai1wm_deactivate');

function rfu_ai1wm_deactivate() {
    // Clean up progress file
    $progress_file = WP_CONTENT_DIR . '/rfu-ai1wm-progress.json';
    if (file_exists($progress_file)) {
        @unlink($progress_file);
    }
}
