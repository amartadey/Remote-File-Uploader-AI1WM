<?php
/**
 * Admin Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Multi-signal AI1WM detection — checks constants, class names, and functions
 * to handle all AI1WM versions (free, unlimited, older builds).
 */
function rfu_ai1wm_detect_ai1wm() {
    if (defined('AI1WM_PLUGIN_BASENAME') || defined('AI1WM_BACKUPS_NAME')) {
        return true;
    }
    foreach (array('Ai1wm_Main', 'Ai1wm_Main_Controller', 'Ai1wm') as $cls) {
        if (class_exists($cls, false)) {
            return true;
        }
    }
    return function_exists('ai1wm_setup');
}

// Server environment checks
$backup_dir   = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ai1wm-backups';
$dir_exists   = file_exists($backup_dir);
$dir_writable = $dir_exists && is_writable($backup_dir);

// Note: disk_free_space() returns the SERVER-LEVEL partition free space.
// On shared hosting this reflects the whole disk, NOT the user's cPanel quota.
// We show it with a clear disclaimer.
$free_bytes      = ($dir_exists && function_exists('disk_free_space')) ? @disk_free_space($backup_dir) : false;
$total_bytes     = ($dir_exists && function_exists('disk_total_space')) ? @disk_total_space($backup_dir) : false;
$curl_enabled    = function_exists('curl_init');
$ai1wm_active    = rfu_ai1wm_detect_ai1wm();

// Format helpers
function rfu_fmt_bytes($bytes, $dec = 1) {
    if ($bytes <= 0) return '0 B';
    $unit = ['B','KB','MB','GB','TB'];
    $i = (int) floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), $dec) . ' ' . $unit[$i];
}
?>

<div class="wrap rfu-ai1wm-wrap">
    <h1 style="display:none;"><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (!$curl_enabled): ?>
    <div class="notice notice-error">
        <p><strong><?php _e('cURL is not enabled.', 'remote-file-uploader-ai1wm'); ?></strong>
        <?php _e('This plugin requires cURL. Please contact your hosting provider.', 'remote-file-uploader-ai1wm'); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($dir_exists && !$dir_writable): ?>
    <div class="notice notice-error">
        <p><strong><?php _e('Backup directory is not writable:', 'remote-file-uploader-ai1wm'); ?></strong>
        <code><?php echo esc_html($backup_dir); ?></code></p>
    </div>
    <?php endif; ?>

    <div class="rfu-ai1wm-container">

        <!-- Hero Banner -->
        <div class="rfu-ai1wm-hero">
            <div class="rfu-ai1wm-hero-icon">☁️</div>
            <div class="rfu-ai1wm-hero-text">
                <h2><?php _e('Remote File Uploader for AI1WM', 'remote-file-uploader-ai1wm'); ?></h2>
                <p><?php _e('Stream a backup file from any public URL directly to this server — no manual downloading needed.', 'remote-file-uploader-ai1wm'); ?></p>
            </div>
        </div>

        <!-- Status Pills -->
        <div class="rfu-ai1wm-status-bar">
            <span class="rfu-ai1wm-status-item <?php echo $curl_enabled ? 'status-ok' : 'status-error'; ?>">
                <span class="rfu-ai1wm-status-dot"></span>
                <?php echo $curl_enabled ? 'cURL Active' : 'cURL Missing'; ?>
            </span>
            <span class="rfu-ai1wm-status-item <?php echo $dir_writable ? 'status-ok' : ($dir_exists ? 'status-error' : 'status-warn'); ?>">
                <span class="rfu-ai1wm-status-dot"></span>
                <?php
                if ($dir_writable) echo 'Backup Dir Ready';
                elseif ($dir_exists) echo 'Dir Not Writable';
                else echo 'Dir Not Created Yet';
                ?>
            </span>
            <span class="rfu-ai1wm-status-item <?php echo $ai1wm_active ? 'status-ok' : 'status-warn'; ?>">
                <span class="rfu-ai1wm-status-dot"></span>
                <?php echo $ai1wm_active ? 'AI1WM Active' : 'AI1WM Not Detected'; ?>
            </span>
            <?php if ($free_bytes !== false): ?>
            <span class="rfu-ai1wm-status-item status-warn" title="<?php _e('Server disk space — NOT your cPanel quota', 'remote-file-uploader-ai1wm'); ?>">
                <span class="rfu-ai1wm-status-dot"></span>
                <?php echo esc_html('Server Free: ' . rfu_fmt_bytes($free_bytes)); ?> &nbsp;<span style="opacity:.7;font-size:11px;">(server disk)</span>
            </span>
            <?php endif; ?>
        </div>

        <!-- Main Upload Card -->
        <div class="rfu-ai1wm-card">
            <div class="rfu-ai1wm-card-header">
                <span class="header-icon">⬆️</span>
                <h2><?php _e('Upload Remote File to AI1WM Backups', 'remote-file-uploader-ai1wm'); ?></h2>
            </div>
            <div class="rfu-ai1wm-card-body">
                <form id="rfu-ai1wm-form" method="post">
                    <table class="form-table" style="margin-top:0;">
                        <tbody>
                            <tr>
                                <th scope="row" style="padding-top:14px;">
                                    <label for="source_url"><?php _e('Remote File URL', 'remote-file-uploader-ai1wm'); ?></label>
                                </th>
                                <td>
                                    <input
                                        type="url"
                                        name="source_url"
                                        id="source_url"
                                        class="regular-text"
                                        placeholder="https://example.com/backup.wpress"
                                        required
                                        autocomplete="off"
                                        spellcheck="false"
                                    />
                                    <div id="rfu-url-hint" class="rfu-ai1wm-url-hint" style="display:none;"></div>
                                    <p class="description">
                                        <?php _e('Accepted: .wpress, .zip — URL must be publicly accessible (no login required).', 'remote-file-uploader-ai1wm'); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Progress Section -->
                    <div class="rfu-ai1wm-progress-container" id="rfu-progress-wrap" style="display:none;">
                        <div class="rfu-ai1wm-progress-bar-wrapper">
                            <div class="rfu-ai1wm-progress-bar">
                                <div class="rfu-ai1wm-progress-bar-fill" id="rfu-progress-fill"></div>
                            </div>
                            <div class="rfu-ai1wm-progress-text">
                                <span class="rfu-ai1wm-progress-percentage" id="rfu-pct">0%</span>
                                <span class="rfu-ai1wm-progress-size" id="rfu-size"></span>
                                <span class="rfu-ai1wm-progress-speed" id="rfu-speed" style="display:none;"></span>
                                <span class="rfu-ai1wm-progress-elapsed" id="rfu-elapsed"></span>
                            </div>
                        </div>
                        <p class="rfu-ai1wm-progress-status" id="rfu-status-text"><?php _e('Initializing…', 'remote-file-uploader-ai1wm'); ?></p>
                        <div class="rfu-ai1wm-technical-data" id="rfu-tech-data" style="display:none;"></div>
                        <div id="rfu-stall-warning" class="rfu-ai1wm-stall-warning" style="display:none;">
                            ⚠️ <?php _e('Download appears stalled — no new data for 30 seconds. The server may be slow or the connection interrupted. Waiting…', 'remote-file-uploader-ai1wm'); ?>
                        </div>
                    </div>

                    <div class="rfu-ai1wm-message" id="rfu-message" style="display:none;"></div>
                    <div id="rfu-restore-action" style="display:none;" class="rfu-ai1wm-restore-action"></div>

                    <p class="submit" style="margin-top:16px; padding-bottom:0;">
                        <button type="submit" class="button button-primary" id="rfu-ai1wm-submit" <?php echo !$curl_enabled ? 'disabled' : ''; ?>>
                            <?php _e('Start Upload', 'remote-file-uploader-ai1wm'); ?>
                        </button>
                        <button type="button" class="button" id="rfu-ai1wm-cancel" style="display:none;">
                            <?php _e('Cancel', 'remote-file-uploader-ai1wm'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="rfu-ai1wm-card rfu-ai1wm-info-card">
            <div class="rfu-ai1wm-card-header">
                <span class="header-icon">ℹ️</span>
                <h2><?php _e('Information & Server Status', 'remote-file-uploader-ai1wm'); ?></h2>
            </div>
            <div class="rfu-ai1wm-card-body">
                <div class="rfu-ai1wm-info-grid">
                    <!-- Column 1: How to use -->
                    <div>
                        <h3><?php _e('How to Use', 'remote-file-uploader-ai1wm'); ?></h3>
                        <ol>
                            <li><?php _e('Copy the direct download URL of your backup file.', 'remote-file-uploader-ai1wm'); ?></li>
                            <li><?php _e('Paste it in the field above and click <strong>Start Upload</strong>.', 'remote-file-uploader-ai1wm'); ?></li>
                            <li><?php _e('The file streams server-side — you do not need to keep this tab open, but monitoring is recommended.', 'remote-file-uploader-ai1wm'); ?></li>
                            <li><?php _e('After completion, open All-in-One WP Migration → Backups to restore.', 'remote-file-uploader-ai1wm'); ?></li>
                        </ol>

                        <h3><?php _e('Requirements', 'remote-file-uploader-ai1wm'); ?></h3>
                        <ul>
                            <li><?php _e('URL must be <strong>publicly accessible</strong> (no password).', 'remote-file-uploader-ai1wm'); ?></li>
                            <li><?php _e('Sufficient <strong>hosting quota</strong> on your account.', 'remote-file-uploader-ai1wm'); ?></li>
                            <li><?php _e('Large files may take several minutes — keep the tab active if possible.', 'remote-file-uploader-ai1wm'); ?></li>
                        </ul>
                    </div>

                    <!-- Column 2: Server info -->
                    <div>
                        <h3><?php _e('Server Status', 'remote-file-uploader-ai1wm'); ?></h3>
                        <table class="rfu-ai1wm-server-table">
                            <tr>
                                <td><?php _e('Backup Directory', 'remote-file-uploader-ai1wm'); ?></td>
                                <td><code><?php echo esc_html($backup_dir); ?></code></td>
                            </tr>
                            <tr>
                                <td><?php _e('Directory Status', 'remote-file-uploader-ai1wm'); ?></td>
                                <td>
                                    <?php if ($dir_writable): ?>
                                        <span style="color:#16a34a;font-weight:600;">✓ <?php _e('Writable', 'remote-file-uploader-ai1wm'); ?></span>
                                    <?php elseif ($dir_exists): ?>
                                        <span style="color:#dc2626;font-weight:600;">✗ <?php _e('Not Writable', 'remote-file-uploader-ai1wm'); ?></span>
                                    <?php else: ?>
                                        <span style="color:#d97706;font-weight:600;">⚡ <?php _e('Will be created on first use', 'remote-file-uploader-ai1wm'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($free_bytes !== false): ?>
                            <tr>
                                <td><?php _e('Server Disk Free', 'remote-file-uploader-ai1wm'); ?></td>
                                <td>
                                    <?php echo esc_html(rfu_fmt_bytes($free_bytes)); ?>
                                    <?php if ($total_bytes): ?>
                                        <span style="color:#9ca3af;"> / <?php echo esc_html(rfu_fmt_bytes($total_bytes)); ?></span>
                                    <?php endif; ?>
                                    <div class="rfu-disk-note">
                                        ⚠️ <?php _e('This is the <strong>server partition</strong> space — not your cPanel hosting quota. Check your cPanel → Disk Usage for your actual available quota.', 'remote-file-uploader-ai1wm'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><?php _e('AI1WM Status', 'remote-file-uploader-ai1wm'); ?></td>
                                <td>
                                    <?php if ($ai1wm_active): ?>
                                        <span style="color:#16a34a;font-weight:600;">✓ <?php _e('Active', 'remote-file-uploader-ai1wm'); ?></span>
                                        &nbsp;—&nbsp;
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=ai1wm_export')); ?>"><?php _e('Open AI1WM →', 'remote-file-uploader-ai1wm'); ?></a>
                                    <?php else: ?>
                                        <span style="color:#d97706;font-weight:600;">⚠ <?php _e('Not detected', 'remote-file-uploader-ai1wm'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e('cURL', 'remote-file-uploader-ai1wm'); ?></td>
                                <td>
                                    <?php if ($curl_enabled): ?>
                                        <span style="color:#16a34a;font-weight:600;">✓ <?php _e('Enabled', 'remote-file-uploader-ai1wm'); ?></span>
                                        <?php if (function_exists('curl_version')): $cv = curl_version(); echo '<span style="color:#9ca3af;font-size:11px;"> v' . esc_html($cv['version']) . '</span>'; endif; ?>
                                    <?php else: ?>
                                        <span style="color:#dc2626;font-weight:600;">✗ <?php _e('Disabled', 'remote-file-uploader-ai1wm'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>

                        <h3><?php _e('Supported Files', 'remote-file-uploader-ai1wm'); ?></h3>
                        <p style="font-size:13px;color:#4b5563;">
                            <?php _e('Primarily designed for <code>.wpress</code> (AI1WM format) and <code>.zip</code> files. Any file type is technically accepted.', 'remote-file-uploader-ai1wm'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
