/**
 * Admin JavaScript for Remote File Uploader for AI1WM
 * Enhanced with detailed debugging and technical data display
 */

(function ($) {
    'use strict';

    let progressInterval = null;
    let downloadStartTime = null;

    console.log('RFU AI1WM: Admin script loaded');

    $(document).ready(function () {
        console.log('RFU AI1WM: DOM ready');

        const $form = $('#rfu-ai1wm-form');
        const $submitButton = $('#rfu-ai1wm-submit');
        const $progressContainer = $('.rfu-ai1wm-progress-container');
        const $progressFill = $('.rfu-ai1wm-progress-bar-fill');
        const $progressPercentage = $('.rfu-ai1wm-progress-percentage');
        const $progressSize = $('.rfu-ai1wm-progress-size');
        const $progressStatus = $('.rfu-ai1wm-progress-status');
        const $messageContainer = $('.rfu-ai1wm-message');

        // Add technical data display elements
        const $technicalData = $('<div class="rfu-ai1wm-technical-data" style="margin-top: 10px; font-size: 12px; color: #666;"></div>');
        $progressContainer.append($technicalData);

        console.log('RFU AI1WM: Elements initialized', {
            form: $form.length,
            submitButton: $submitButton.length,
            progressContainer: $progressContainer.length
        });

        /**
         * Handle form submission
         */
        $form.on('submit', function (e) {
            e.preventDefault();
            console.log('RFU AI1WM: Form submitted');

            const sourceUrl = $('#source_url').val().trim();
            console.log('RFU AI1WM: Source URL:', sourceUrl);

            if (!sourceUrl) {
                console.warn('RFU AI1WM: Empty URL provided');
                showMessage('error', 'Please enter a valid URL.');
                return;
            }

            // Disable submit button
            $submitButton.prop('disabled', true).html('Uploading... <span class="rfu-ai1wm-spinner"></span>');
            console.log('RFU AI1WM: Submit button disabled');

            // Show progress container
            $progressContainer.fadeIn();
            $messageContainer.hide();

            // Reset progress
            downloadStartTime = Date.now();
            updateProgress(0, 0, 0, 'Initializing...', {});
            console.log('RFU AI1WM: Progress reset, start time:', downloadStartTime);

            // Start upload
            startUpload(sourceUrl);
        });

        /**
         * Start file upload
         */
        function startUpload(sourceUrl) {
            console.log('RFU AI1WM: Starting upload for URL:', sourceUrl);
            console.log('RFU AI1WM: AJAX URL:', rfuAi1wm.ajax_url);
            console.log('RFU AI1WM: Nonce:', rfuAi1wm.nonce);

            const ajaxStartTime = Date.now();

            $.ajax({
                url: rfuAi1wm.ajax_url,
                type: 'POST',
                timeout: 0, // Unlimited timeout for large files
                data: {
                    action: 'rfu_ai1wm_upload_file',
                    nonce: rfuAi1wm.nonce,
                    source_url: sourceUrl
                },
                beforeSend: function () {
                    console.log('RFU AI1WM: AJAX request starting...');
                },
                success: function (response) {
                    const ajaxDuration = Date.now() - ajaxStartTime;
                    console.log('RFU AI1WM: AJAX success after', ajaxDuration, 'ms');
                    console.log('RFU AI1WM: Response:', response);

                    if (response.success) {
                        console.log('RFU AI1WM: Download completed successfully');
                        console.log('RFU AI1WM: File details:', {
                            filename: response.data.filename,
                            path: response.data.path,
                            size: response.data.size
                        });

                        stopProgressPolling();
                        updateProgress(100, response.data.size, response.data.size, 'Complete!', {});
                        showMessage('success', 'File uploaded successfully! Filename: ' + response.data.filename + ' (Size: ' + formatBytes(response.data.size) + ')');
                        $submitButton.prop('disabled', false).text('Start Upload');

                        // Reset form after 3 seconds
                        setTimeout(function () {
                            console.log('RFU AI1WM: Resetting form');
                            $form[0].reset();
                            $progressContainer.fadeOut();
                            $messageContainer.fadeOut();
                        }, 3000);
                    } else {
                        console.error('RFU AI1WM: Download failed:', response.data.message);
                        stopProgressPolling();
                        showMessage('error', response.data.message || 'An error occurred.');
                        $submitButton.prop('disabled', false).text('Start Upload');
                        $progressContainer.fadeOut();
                    }
                },
                error: function (xhr, status, error) {
                    const ajaxDuration = Date.now() - ajaxStartTime;
                    console.error('RFU AI1WM: AJAX error after', ajaxDuration, 'ms');
                    console.error('RFU AI1WM: Error details:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error,
                        ajaxStatus: status
                    });

                    stopProgressPolling();

                    // Get detailed error message
                    let errorMessage = 'AJAX error: ';

                    if (xhr.status === 0) {
                        errorMessage += 'Network error - Unable to connect to server. Please check your internet connection.';
                        console.error('RFU AI1WM: Network connectivity issue detected');
                    } else if (xhr.status === 403) {
                        errorMessage += 'Permission denied (403). Please check your WordPress permissions.';
                        console.error('RFU AI1WM: Permission denied');
                    } else if (xhr.status === 404) {
                        errorMessage += 'AJAX endpoint not found (404). Please ensure the plugin is properly activated.';
                        console.error('RFU AI1WM: AJAX endpoint not found');
                    } else if (xhr.status === 500) {
                        errorMessage += 'Server error (500). Check server error logs for details.';
                        console.error('RFU AI1WM: Server error 500');
                    } else if (xhr.status === 503) {
                        errorMessage += 'Service unavailable (503). Server may be overloaded or under maintenance.';
                        console.error('RFU AI1WM: Service unavailable 503');
                    } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage += xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        errorMessage += xhr.responseText.substring(0, 200);
                        console.error('RFU AI1WM: Response text:', xhr.responseText);
                    } else {
                        errorMessage += error + ' (Status: ' + xhr.status + ')';
                    }

                    showMessage('error', errorMessage);
                    $submitButton.prop('disabled', false).text('Start Upload');
                    $progressContainer.fadeOut();
                }
            });

            // Start polling for progress
            startProgressPolling();
        }

        /**
         * Start polling for progress updates
         */
        function startProgressPolling() {
            console.log('RFU AI1WM: Starting progress polling');

            progressInterval = setInterval(function () {
                $.ajax({
                    url: rfuAi1wm.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rfu_ai1wm_get_progress',
                        nonce: rfuAi1wm.nonce
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            const data = response.data;

                            // Log progress data
                            if (data.status === 'downloading') {
                                console.log('RFU AI1WM: Progress update:', {
                                    progress: data.progress + '%',
                                    downloaded: formatBytes(data.downloaded),
                                    total: formatBytes(data.total),
                                    speed: formatBytes(data.speed) + '/s',
                                    eta: formatTime(data.eta),
                                    elapsed: formatTime(data.elapsed_time)
                                });
                            }

                            if (data.status === 'downloading' || data.status === 'starting') {
                                const statusText = data.status === 'starting' ? 'Initializing...' : 'Downloading...';
                                updateProgress(
                                    data.progress,
                                    data.downloaded,
                                    data.total,
                                    statusText,
                                    data
                                );
                            } else if (data.status === 'complete') {
                                console.log('RFU AI1WM: Download complete via progress polling');
                                stopProgressPolling();
                                updateProgress(100, data.total, data.total, 'Complete!', data);
                                showMessage('success', 'File uploaded successfully! Filename: ' + (data.filename || 'Unknown'));
                                $submitButton.prop('disabled', false).text('Start Upload');

                                // Reset form after 3 seconds
                                setTimeout(function () {
                                    $form[0].reset();
                                    $progressContainer.fadeOut();
                                    $messageContainer.fadeOut();
                                }, 3000);
                            } else if (data.status === 'error') {
                                console.error('RFU AI1WM: Error status received:', data.error);
                                stopProgressPolling();
                                const errorMsg = data.error || 'Download failed. Please try again.';
                                showMessage('error', errorMsg);
                                $submitButton.prop('disabled', false).text('Start Upload');
                                $progressContainer.fadeOut();
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        // Silently continue polling on AJAX errors
                        console.warn('RFU AI1WM: Progress polling error (will retry):', {
                            status: status,
                            error: error
                        });
                    }
                });
            }, 1000); // Poll every second
        }

        /**
         * Stop polling for progress updates
         */
        function stopProgressPolling() {
            if (progressInterval) {
                console.log('RFU AI1WM: Stopping progress polling');
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }

        /**
         * Update progress display with enhanced technical data
         */
        function updateProgress(percentage, downloaded, total, status, data) {
            $progressFill.css('width', percentage + '%');
            $progressPercentage.text(percentage + '%');
            $progressSize.text(formatBytes(downloaded) + ' / ' + formatBytes(total));
            $progressStatus.text(status);

            // Update technical data display
            let technicalHtml = '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px;">';

            if (data.speed && data.speed > 0) {
                technicalHtml += '<div><strong>Speed:</strong> ' + formatBytes(data.speed) + '/s</div>';
            }

            if (data.eta && data.eta > 0) {
                technicalHtml += '<div><strong>Time Remaining:</strong> ' + formatTime(data.eta) + '</div>';
            }

            if (data.elapsed_time && data.elapsed_time > 0) {
                technicalHtml += '<div><strong>Elapsed Time:</strong> ' + formatTime(data.elapsed_time) + '</div>';
            }

            if (total > 0) {
                technicalHtml += '<div><strong>Total Size:</strong> ' + formatBytes(total) + '</div>';
            }

            if (data.speed && data.speed > 0 && data.elapsed_time > 0) {
                const avgSpeed = downloaded / data.elapsed_time;
                technicalHtml += '<div><strong>Avg Speed:</strong> ' + formatBytes(avgSpeed) + '/s</div>';
            }

            if (downloaded > 0 && total > 0) {
                const remaining = total - downloaded;
                technicalHtml += '<div><strong>Remaining:</strong> ' + formatBytes(remaining) + '</div>';
            }

            technicalHtml += '</div>';
            $technicalData.html(technicalHtml);

            console.log('RFU AI1WM: UI updated -', status, percentage + '%');
        }

        /**
         * Show message
         */
        function showMessage(type, message) {
            console.log('RFU AI1WM: Showing message -', type + ':', message);

            const icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : 'ℹ');
            const title = type === 'success' ? 'Success!' : (type === 'error' ? 'Error!' : 'Info');

            $messageContainer
                .removeClass('success error info')
                .addClass(type)
                .html('<strong>' + icon + ' ' + title + '</strong> ' + message)
                .fadeIn();
        }

        /**
         * Format bytes to human-readable format
         */
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        /**
         * Format time to human-readable format
         */
        function formatTime(seconds) {
            if (!seconds || seconds <= 0) return '0s';

            if (seconds < 60) {
                return Math.round(seconds) + 's';
            } else if (seconds < 3600) {
                const mins = Math.floor(seconds / 60);
                const secs = Math.round(seconds % 60);
                return mins + 'm ' + secs + 's';
            } else {
                const hours = Math.floor(seconds / 3600);
                const mins = Math.floor((seconds % 3600) / 60);
                return hours + 'h ' + mins + 'm';
            }
        }

        /**
         * Clean up on page unload
         */
        $(window).on('beforeunload', function () {
            console.log('RFU AI1WM: Page unloading, cleaning up');
            stopProgressPolling();
        });

        console.log('RFU AI1WM: All event handlers registered');
    });

})(jQuery);
