# Remote File Uploader for AI1WM

A WordPress plugin that allows you to upload backup files from remote URLs directly to the All-in-One WP Migration backups folder.

## Description

Remote File Uploader for AI1WM is a companion plugin that simplifies the process of transferring backup files from remote locations (cloud storage, other servers, etc.) directly to your WordPress site's All-in-One WP Migration backups folder.

Instead of manually downloading large backup files to your computer and then re-uploading them to your WordPress site, this plugin allows you to provide a URL and let the server handle the transfer directly.

## Features

- ✅ Upload files from any publicly accessible URL
- ✅ Real-time progress tracking with visual progress bar
- ✅ Memory-efficient 512KB chunked processing
- ✅ Enhanced UI showing speed, ETA, and time remaining
- ✅ Comprehensive debugging with console.log
- ✅ Automatic file naming from source URL
- ✅ Works with any file type (designed for .wpress backups)
- ✅ Clean, user-friendly admin interface
- ✅ No file size limitations (server-dependent)
- ✅ Secure AJAX-based uploads with nonce verification
- ✅ Follows WordPress coding standards and best practices

## Installation

### Method 1: Download from GitHub

1. Go to [Releases](https://github.com/amartadey/Remote-File-Uploader-AI1WM/releases)
2. Download the latest `remote-file-uploader-ai1wm.zip`
3. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
4. Choose the downloaded ZIP file and click **Install Now**
5. Click **Activate Plugin**
6. Navigate to **Tools → Remote File Uploader** to use the plugin

### Method 2: Manual Installation

1. Download the plugin files
2. Upload the `remote-file-uploader-ai1wm` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **Tools → Remote File Uploader** to use the plugin

## Usage

1. Navigate to **Tools → Remote File Uploader** in your WordPress admin
2. Enter the full URL of your backup file in the "Remote File URL" field
3. Click **Start Upload**
4. Monitor the real-time progress bar with technical data:
   - Download speed
   - Time remaining (ETA)
   - Elapsed time
   - File size information
5. Once complete, the file will be available in All-in-One WP Migration for restoration

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- cURL extension enabled
- Sufficient disk space for backup files
- All-in-One WP Migration plugin (recommended)

## File Structure

```
remote-file-uploader-ai1wm/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── includes/
│   └── admin-page.php
├── languages/
│   └── (translation files)
├── remote-file-uploader-ai1wm.php
├── uninstall.php
├── readme.txt
└── README.md
```

## Technical Details

### Memory Efficiency

- **Buffer Size:** 512KB (524,288 bytes) for chunked processing
- **Memory Limit:** 512MB (configurable)
- **Execution Time:** Unlimited for large files
- **Progress Updates:** Every 0.5 seconds

### Security Features

- WordPress nonce verification for all AJAX requests
- Capability checks (`manage_options` required)
- URL validation and sanitization
- Secure file handling

### How It Works

1. User submits a remote URL via the admin interface
2. Plugin validates the URL and checks permissions
3. cURL downloads the file with 512KB chunked processing
4. Progress is tracked with speed and ETA calculations
5. File is saved to `wp-content/ai1wm-backups` directory
6. Success/error message is displayed to the user

### Debugging

The plugin includes comprehensive debugging:
- **Browser Console:** All actions logged with `RFU AI1WM:` prefix
- **Server Logs:** Progress logged to WordPress debug.log
- **Technical UI:** Real-time display of speed, ETA, and file size

## FAQ

**Q: Does this plugin require All-in-One WP Migration?**  
A: While designed to work with AI1WM, the plugin will create the backup directory if it doesn't exist. However, you'll need AI1WM to restore the backups.

**Q: What file types are supported?**  
A: Any file type can be downloaded, but the plugin is designed primarily for .wpress backup files.

**Q: Is there a file size limit?**  
A: The plugin has no built-in limit, but your server configuration may impose practical limits.

**Q: Can I upload from password-protected URLs?**  
A: Currently, only publicly accessible URLs are supported.

**Q: Is this affiliated with All-in-One WP Migration?**  
A: No, this is an independent plugin compatible with AI1WM. It is not affiliated with or endorsed by ServMask Inc.

## Legal Considerations

This plugin is designed as a legitimate companion tool for All-in-One WP Migration users. It:

- Does NOT bypass any paid features of AI1WM
- Does NOT circumvent file size limitations
- Does NOT interfere with AI1WM's business model
- Simply provides a convenient way to transfer files you already own

The plugin is licensed under GPL v2 or later, consistent with WordPress licensing requirements.

## Changelog

### 1.0.1 (2026-01-16)
- Implemented memory-efficient 512KB chunked processing
- Added enhanced UI with speed, ETA, and time remaining
- Comprehensive console.log debugging throughout
- Fixed WordPress cron reliability issues
- Direct download approach for better performance

### 1.0.0
- Initial release
- Remote URL upload functionality
- Real-time progress tracking
- Clean admin interface
- Security features

## Support

For issues, questions, or feature requests:
- **GitHub Issues:** [Report an issue](https://github.com/amartadey/Remote-File-Uploader-AI1WM/issues)
- **GitHub Discussions:** [Ask a question](https://github.com/amartadey/Remote-File-Uploader-AI1WM/discussions)

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

**Author:** Amarta Dey  
**Website:** [amartadey.com](https://amartadey.com)  
**Plugin Page:** [GitHub Pages](https://amartadey.github.io/Remote-File-Uploader-AI1WM/)

Developed as an independent companion tool for All-in-One WP Migration users.

---

**Note:** This plugin is not affiliated with, endorsed by, or connected to ServMask Inc. or the All-in-One WP Migration plugin. "All-in-One WP Migration" is a trademark of ServMask Inc.
