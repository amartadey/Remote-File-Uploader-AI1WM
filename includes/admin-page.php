<?php
/**
 * Admin Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap rfu-ai1wm-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="rfu-ai1wm-container">
        <div class="rfu-ai1wm-card">
            <div class="rfu-ai1wm-card-header">
                <h2><?php _e('Upload Remote File to AI1WM Backups', 'remote-file-uploader-ai1wm'); ?></h2>
            </div>
            
            <div class="rfu-ai1wm-card-body">
                <p class="description">
                    <?php _e('Enter the URL of a backup file to download it directly to your All-in-One WP Migration backups folder.', 'remote-file-uploader-ai1wm'); ?>
                </p>
                
                <form id="rfu-ai1wm-form" method="post">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="source_url"><?php _e('Remote File URL', 'remote-file-uploader-ai1wm'); ?></label>
                                </th>
                                <td>
                                    <input 
                                        type="url" 
                                        name="source_url" 
                                        id="source_url" 
                                        class="regular-text" 
                                        placeholder="https://example.com/path/to/backup.wpress"
                                        required
                                    />
                                    <p class="description">
                                        <?php _e('Enter the full URL of the backup file you want to download.', 'remote-file-uploader-ai1wm'); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="rfu-ai1wm-progress-container" style="display: none;">
                        <div class="rfu-ai1wm-progress-bar-wrapper">
                            <div class="rfu-ai1wm-progress-bar">
                                <div class="rfu-ai1wm-progress-bar-fill"></div>
                            </div>
                            <div class="rfu-ai1wm-progress-text">
                                <span class="rfu-ai1wm-progress-percentage">0%</span>
                                <span class="rfu-ai1wm-progress-size">0 / 0 bytes</span>
                            </div>
                        </div>
                        <p class="rfu-ai1wm-progress-status"><?php _e('Initializing...', 'remote-file-uploader-ai1wm'); ?></p>
                    </div>
                    
                    <div class="rfu-ai1wm-message" style="display: none;"></div>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="rfu-ai1wm-submit">
                            <?php _e('Start Upload', 'remote-file-uploader-ai1wm'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <div class="rfu-ai1wm-card rfu-ai1wm-info-card">
            <div class="rfu-ai1wm-card-header">
                <h2><?php _e('Information', 'remote-file-uploader-ai1wm'); ?></h2>
            </div>
            
            <div class="rfu-ai1wm-card-body">
                <h3><?php _e('How to Use', 'remote-file-uploader-ai1wm'); ?></h3>
                <ol>
                    <li><?php _e('Copy the URL of your backup file from your cloud storage or remote server.', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('Paste the URL into the "Remote File URL" field above.', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('Click "Start Upload" to begin downloading the file.', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('Wait for the upload to complete. You can monitor the progress in real-time.', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('Once complete, the file will be available in All-in-One WP Migration for restoration.', 'remote-file-uploader-ai1wm'); ?></li>
                </ol>
                
                <h3><?php _e('Requirements', 'remote-file-uploader-ai1wm'); ?></h3>
                <ul>
                    <li><?php _e('All-in-One WP Migration plugin should be installed (recommended).', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('The remote URL must be publicly accessible.', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('Sufficient disk space on your server.', 'remote-file-uploader-ai1wm'); ?></li>
                    <li><?php _e('cURL extension enabled on your server.', 'remote-file-uploader-ai1wm'); ?></li>
                </ul>
                
                <h3><?php _e('Backup Directory', 'remote-file-uploader-ai1wm'); ?></h3>
                <p>
                    <code><?php echo esc_html(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ai1wm-backups'); ?></code>
                </p>
                
                <h3><?php _e('Supported File Types', 'remote-file-uploader-ai1wm'); ?></h3>
                <p>
                    <?php _e('This plugin works with any file type, but is designed primarily for .wpress backup files used by All-in-One WP Migration.', 'remote-file-uploader-ai1wm'); ?>
                </p>
            </div>
        </div>
    </div>
</div>
