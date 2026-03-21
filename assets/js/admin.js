/**
 * Admin JavaScript for Remote File Uploader for AI1WM
 */

(function ($) {
    'use strict';

    let progressInterval  = null;
    let elapsedInterval   = null;
    let downloadStartTime = null;
    let isFinished        = false; // Guard: prevents in-flight poll callbacks from acting after upload ends
    let lastDownloaded    = 0;     // For stall detection
    let lastChangeTime    = null;  // Timestamp of last byte-count change

    console.log('RFU AI1WM: Admin script loaded');

    $(document).ready(function () {
        console.log('RFU AI1WM: DOM ready');

        const $form              = $('#rfu-ai1wm-form');
        const $submitButton      = $('#rfu-ai1wm-submit');
        const $cancelButton      = $('#rfu-ai1wm-cancel');
        const $progressContainer = $('.rfu-ai1wm-progress-container');
        const $progressFill      = $('.rfu-ai1wm-progress-bar-fill');
        const $progressPercentage = $('.rfu-ai1wm-progress-percentage');
        const $progressSize      = $('.rfu-ai1wm-progress-size');
        const $progressSpeed     = $('.rfu-ai1wm-progress-speed');
        const $progressElapsed   = $('.rfu-ai1wm-progress-elapsed');
        const $progressStatus    = $('.rfu-ai1wm-progress-status');
        const $messageContainer  = $('.rfu-ai1wm-message');
        const $stallWarning      = $('#rfu-stall-warning');
        const $restoreAction     = $('#rfu-restore-action');
        const $urlInput          = $('#source_url');
        const $urlHint           = $('#rfu-url-hint');

        console.log('RFU AI1WM: Elements initialized', {
            form: $form.length,
            submitButton: $submitButton.length,
            progressContainer: $progressContainer.length
        });

        // ── URL input validation ───────────────────────────────────────
        $urlInput.on('input blur', function () {
            const val = $(this).val().trim();
            $urlHint.hide().removeClass('hint-warn hint-error').text('');

            if (!val) return;

            // Detect local/staging URLs that are not publicly reachable
            const localPatterns = [
                /^https?:\/\/localhost/i,
                /^https?:\/\/127\./,
                /^https?:\/\/192\.168\./,
                /^https?:\/\/10\.\d+\.\d+\.\d+/,
                /^https?:\/\/172\.(1[6-9]|2\d|3[01])\./,
                /\.test\//i,
                /\.local\//i,
                /\.dev\//i,
            ];
            for (const pattern of localPatterns) {
                if (pattern.test(val)) {
                    $urlHint
                        .addClass('hint-warn')
                        .html('⚠ This URL looks like a local or private network address. It will not be reachable from this server.')
                        .show();
                    return;
                }
            }

            // Warn if the URL is plain http:// (will auto-upgrade, but inform user)
            if (/^http:\/\//i.test(val)) {
                $urlHint
                    .addClass('hint-warn')
                    .html('ℹ This URL uses HTTP. It will be automatically upgraded to HTTPS before downloading.')
                    .show();
                return;
            }
        });

        // ── Form submit ───────────────────────────────────────────────
        $form.on('submit', function (e) {
            e.preventDefault();
            console.log('RFU AI1WM: Form submitted');

            const sourceUrl = $urlInput.val().trim();
            console.log('RFU AI1WM: Source URL:', sourceUrl);

            if (!sourceUrl) {
                showMessage('error', 'Please enter a valid URL.');
                return;
            }

            // Reset state
            isFinished        = false;
            lastDownloaded    = 0;
            lastChangeTime    = Date.now();
            downloadStartTime = Date.now();

            // UI state
            $submitButton.prop('disabled', true).html('Uploading... <span class="rfu-ai1wm-spinner"></span>');
            $cancelButton.show();
            $progressContainer.fadeIn();
            $messageContainer.hide();
            $restoreAction.hide().empty();
            $stallWarning.hide();
            $urlHint.hide();

            updateProgress(0, 0, 0, 'Initializing...', {});
            console.log('RFU AI1WM: Progress reset, start time:', downloadStartTime);

            startUpload(sourceUrl);
        });

        // ── Cancel button ─────────────────────────────────────────────
        // Note: WordPress doesn't support killing the PHP process, so
        // "cancel" from JS side just resets the UI. The server-side
        // download will complete or time out on its own. We inform the user.
        $cancelButton.on('click', function () {
            if (!confirm('Cancelling will reset the UI, but the server may continue downloading in the background until it completes or times out. Are you sure?')) {
                return;
            }
            stopAll();
            showMessage('info', 'Upload cancelled in the browser. The server-side download may still be running in the background.');
            $progressContainer.fadeOut();
        });

        // ── Upload AJAX ───────────────────────────────────────────────
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

                        stopAll();
                        updateProgress(100, response.data.size, response.data.size, 'Complete!', {});
                        showMessage('success', '✓ File uploaded successfully! <strong>' + response.data.filename + '</strong> (' + formatBytes(response.data.size) + ')');

                        // Show "Restore Now" button if AI1WM is active
                        if (rfuAi1wm.ai1wm_active && rfuAi1wm.ai1wm_restore_url) {
                            $restoreAction.html(
                                '✓ The backup is now in your AI1WM backups folder.' +
                                ' <a href="' + rfuAi1wm.ai1wm_restore_url + '" class="button button-primary">Restore with AI1WM →</a>'
                            ).show();
                        }

                        $submitButton.prop('disabled', false).text('Start Upload');
                        $cancelButton.hide();

                        // Auto-reset form after 8 seconds
                        setTimeout(function () {
                            console.log('RFU AI1WM: Resetting form');
                            $form[0].reset();
                            $progressContainer.fadeOut();
                            $messageContainer.fadeOut();
                            $restoreAction.fadeOut();
                        }, 8000);

                    } else {
                        console.error('RFU AI1WM: Download failed:', response.data.message);
                        stopAll();
                        showMessage('error', response.data.message || 'An error occurred.');
                        $submitButton.prop('disabled', false).text('Start Upload');
                        $cancelButton.hide();
                        $progressContainer.fadeOut();
                    }
                },
                error: function (xhr, status, error) {
                    const ajaxDuration = Date.now() - ajaxStartTime;
                    console.error('RFU AI1WM: AJAX error after', ajaxDuration, 'ms');
                    console.error('RFU AI1WM: Error details:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        ajaxStatus: status
                    });

                    stopAll();

                    let errorMessage = 'Upload error: ';
                    if (xhr.status === 0) {
                        errorMessage += 'Network error — check your internet connection.';
                    } else if (xhr.status === 403) {
                        errorMessage += 'Permission denied (403). Check WordPress permissions.';
                    } else if (xhr.status === 404) {
                        errorMessage += 'AJAX endpoint not found (404). Ensure the plugin is activated.';
                    } else if (xhr.status === 500) {
                        errorMessage += 'Server error (500). Check the server error log for details.';
                    } else if (xhr.status === 503) {
                        errorMessage += 'Server unavailable (503). The server may be overloaded — try again shortly.';
                    } else {
                        errorMessage += error + ' (HTTP ' + xhr.status + ')';
                    }

                    showMessage('error', errorMessage);
                    $submitButton.prop('disabled', false).text('Start Upload');
                    $cancelButton.hide();
                    $progressContainer.fadeOut();
                }
            });

            // Start progress polling and elapsed timer
            startProgressPolling();
            startElapsedTimer();
        }

        // ── Elapsed time counter ──────────────────────────────────────
        function startElapsedTimer() {
            elapsedInterval = setInterval(function () {
                if (isFinished) return;
                const elapsed = Math.floor((Date.now() - downloadStartTime) / 1000);
                $progressElapsed.text('Elapsed: ' + formatTime(elapsed));

                // Stall detection: if bytes haven't changed for 30s, show warning
                if (lastChangeTime && (Date.now() - lastChangeTime) > 30000 && lastDownloaded > 0) {
                    $stallWarning.show();
                } else {
                    $stallWarning.hide();
                }
            }, 1000);
        }

        // ── Progress polling ──────────────────────────────────────────
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
                        if (isFinished) return;

                        if (response.success && response.data) {
                            const data = response.data;

                            if (data.status === 'downloading') {
                                // Stall detection: track when bytes last changed
                                if (data.downloaded !== lastDownloaded) {
                                    lastDownloaded = data.downloaded;
                                    lastChangeTime = Date.now();
                                }

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
                                stopAll();
                                updateProgress(100, data.total, data.total, 'Complete!', data);
                                showMessage('success', 'File uploaded successfully! Filename: ' + (data.filename || 'Unknown'));
                                $submitButton.prop('disabled', false).text('Start Upload');
                                $cancelButton.hide();

                                setTimeout(function () {
                                    $form[0].reset();
                                    $progressContainer.fadeOut();
                                    $messageContainer.fadeOut();
                                }, 8000);
                            } else if (data.status === 'error') {
                                console.error('RFU AI1WM: Error status received:', data.error);
                                stopAll();
                                showMessage('error', data.error || 'Download failed. Please try again.');
                                $submitButton.prop('disabled', false).text('Start Upload');
                                $cancelButton.hide();
                                $progressContainer.fadeOut();
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        if (isFinished) return;
                        console.warn('RFU AI1WM: Progress polling error (will retry):', { status, error });
                    }
                });
            }, 1000);
        }

        // ── Stop everything ───────────────────────────────────────────
        function stopAll() {
            isFinished = true;
            if (progressInterval) {
                console.log('RFU AI1WM: Stopping progress polling');
                clearInterval(progressInterval);
                progressInterval = null;
            }
            if (elapsedInterval) {
                clearInterval(elapsedInterval);
                elapsedInterval = null;
            }
            $stallWarning.hide();
        }

        // ── Progress display ──────────────────────────────────────────
        function updateProgress(percentage, downloaded, total, status, data) {
            const unknownSize = !total || total === 0;

            if (unknownSize && status === 'Downloading...') {
                // Indeterminate mode: server did not send Content-Length
                $progressFill.addClass('indeterminate');
                $progressPercentage.text('Downloading...');
                $progressSize.text(formatBytes(downloaded) + ' downloaded');
            } else {
                $progressFill.removeClass('indeterminate');
                $progressFill.css('width', percentage + '%');
                $progressPercentage.text(percentage + '%');
                $progressSize.text(downloaded > 0 ? formatBytes(downloaded) + (total > 0 ? ' / ' + formatBytes(total) : '') : '');
            }

            $progressStatus.text(status);

            // Speed badge
            if (data && data.speed && data.speed > 0) {
                $progressSpeed.text(formatBytes(data.speed) + '/s');
            } else {
                $progressSpeed.text('');
            }

            // ETA — only show when known
            if (!unknownSize && data && data.eta && data.eta > 0) {
                $progressElapsed.text('ETA: ' + formatTime(data.eta));
            }

            // Technical data grid
            let html = '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:5px;">';
            if (data && data.speed && data.speed > 0) {
                html += '<div><strong>Speed:</strong> ' + formatBytes(data.speed) + '/s</div>';
            }
            if (!unknownSize && data && data.eta && data.eta > 0) {
                html += '<div><strong>ETA:</strong> ' + formatTime(data.eta) + '</div>';
            }
            if (data && data.elapsed_time && data.elapsed_time > 0) {
                html += '<div><strong>Elapsed:</strong> ' + formatTime(data.elapsed_time) + '</div>';
            }
            if (total > 0) {
                html += '<div><strong>Total Size:</strong> ' + formatBytes(total) + '</div>';
            }
            if (downloaded > 0 && total > 0) {
                html += '<div><strong>Remaining:</strong> ' + formatBytes(total - downloaded) + '</div>';
            }
            html += '</div>';
            $('.rfu-ai1wm-technical-data').html(html);

            console.log('RFU AI1WM: UI updated -', status, percentage + '%');
        }

        // ── Message display ───────────────────────────────────────────
        function showMessage(type, message) {
            console.log('RFU AI1WM: Showing message -', type + ':', message);
            const icon  = type === 'success' ? '✓' : (type === 'error' ? '✗' : 'ℹ');
            const title = type === 'success' ? 'Success!' : (type === 'error' ? 'Error' : 'Info');

            $messageContainer
                .removeClass('success error info')
                .addClass(type)
                .html('<strong>' + icon + ' ' + title + '</strong> ' + message)
                .fadeIn();
        }

        // ── Helpers ───────────────────────────────────────────────────
        function formatBytes(bytes, decimals = 2) {
            if (!bytes || bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function formatTime(seconds) {
            if (!seconds || seconds <= 0) return '0s';
            if (seconds < 60) return Math.round(seconds) + 's';
            if (seconds < 3600) {
                return Math.floor(seconds / 60) + 'm ' + Math.round(seconds % 60) + 's';
            }
            return Math.floor(seconds / 3600) + 'h ' + Math.floor((seconds % 3600) / 60) + 'm';
        }

        // ── Page unload cleanup ───────────────────────────────────────
        $(window).on('beforeunload', function () {
            stopAll();
        });

        console.log('RFU AI1WM: All event handlers registered');
    });

})(jQuery);
