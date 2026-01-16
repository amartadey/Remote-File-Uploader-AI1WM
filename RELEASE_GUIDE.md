# GitHub Release Publishing Guide

## Prerequisites

1. **GitHub Repository**: `https://github.com/amartadey/Remote-File-Uploader-AI1WM`
2. **GitHub Pages**: Enabled and pointing to `index.html`
3. **Plugin Files**: Ready in your local directory

---

## Step 1: Prepare Your Repository

### 1.1 Create GitHub Repository

1. Go to [GitHub](https://github.com) and sign in
2. Click the **+** icon → **New repository**
3. Repository name: `Remote-File-Uploader-AI1WM`
4. Description: `Upload backup files from remote URLs directly to All-in-One WP Migration backups folder`
5. Set to **Public**
6. ✅ Check "Add a README file"
7. Choose License: **GNU General Public License v2.0**
8. Click **Create repository**

### 1.2 Clone Repository Locally

```bash
git clone https://github.com/amartadey/Remote-File-Uploader-AI1WM.git
cd Remote-File-Uploader-AI1WM
```

---

## Step 2: Add Plugin Files

### 2.1 Copy Plugin Files

Copy all files from your plugin directory to the repository:

```
Remote-File-Uploader-AI1WM/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── includes/
│   └── admin-page.php
├── remote-file-uploader-ai1wm.php
├── uninstall.php
├── README.md
├── readme.txt
├── index.html  (GitHub Pages)
└── .gitignore
```

### 2.2 Create .gitignore

Create a `.gitignore` file:

```gitignore
# WordPress
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.sublime-project
*.sublime-workspace

# Logs
*.log

# Temporary files
*.tmp
*.bak
*~

# Zip files (except releases)
*.zip
```

### 2.3 Commit and Push

```bash
git add .
git commit -m "Initial commit: Remote File Uploader for AI1WM v1.0.1"
git push origin main
```

---

## Step 3: Enable GitHub Pages

1. Go to your repository on GitHub
2. Click **Settings** → **Pages** (left sidebar)
3. Under **Source**, select:
   - Branch: `main`
   - Folder: `/ (root)`
4. Click **Save**
5. Wait 1-2 minutes for deployment
6. Your site will be live at: `https://amartadey.github.io/Remote-File-Uploader-AI1WM/`

---

## Step 4: Create a Release

### 4.1 Create Plugin ZIP File

**Option A: Using Command Line (Recommended)**

```bash
# Navigate to parent directory
cd ..

# Create zip file (excluding git files and index.html)
zip -r remote-file-uploader-ai1wm.zip remote-file-uploader-ai1wm \
  -x "*.git*" \
  -x "*index.html" \
  -x "*.DS_Store" \
  -x "*Thumbs.db"
```

**Option B: Using Windows (Manual)**

1. Go to the plugin folder
2. Select all files EXCEPT:
   - `.git` folder
   - `index.html`
   - `.gitignore`
3. Right-click → **Send to** → **Compressed (zipped) folder**
4. Name it: `remote-file-uploader-ai1wm.zip`

### 4.2 Create GitHub Release

1. Go to your repository on GitHub
2. Click **Releases** (right sidebar)
3. Click **Create a new release**

**Release Details:**

- **Tag version**: `v1.0.1`
  - Click "Choose a tag" → Type `v1.0.1` → Click "Create new tag: v1.0.1 on publish"
- **Release title**: `Remote File Uploader for AI1WM v1.0.1`
- **Description**:

```markdown
## 🎉 Remote File Uploader for AI1WM v1.0.1

### What's New

- ✅ Memory-efficient 512KB chunked processing
- ✅ Enhanced UI with real-time speed, ETA, and time remaining
- ✅ Comprehensive console.log debugging for easy troubleshooting
- ✅ Fixed WordPress cron reliability issues
- ✅ Direct download approach for better performance
- ✅ Improved error handling and logging

### Installation

1. Download `remote-file-uploader-ai1wm.zip` below
2. Go to **Plugins → Add New → Upload Plugin** in WordPress
3. Choose the ZIP file and click **Install Now**
4. Click **Activate Plugin**
5. Navigate to **Tools → Remote File Uploader**

### Requirements

- WordPress 5.0+
- PHP 7.2+
- cURL extension enabled

### Links

- 📖 [Documentation](https://amartadey.github.io/Remote-File-Uploader-AI1WM/)
- 🐛 [Report Issues](https://github.com/amartadey/Remote-File-Uploader-AI1WM/issues)
- 💬 [Discussions](https://github.com/amartadey/Remote-File-Uploader-AI1WM/discussions)

---

**Full Changelog**: https://github.com/amartadey/Remote-File-Uploader-AI1WM/commits/v1.0.1
```

4. **Attach ZIP file**:
   - Drag and drop `remote-file-uploader-ai1wm.zip` to the "Attach binaries" section
   
5. ✅ Check "Set as the latest release"

6. Click **Publish release**

---

## Step 5: Verify Everything Works

### 5.1 Test GitHub Pages

1. Visit: `https://amartadey.github.io/Remote-File-Uploader-AI1WM/`
2. Check that the download button works
3. Verify it downloads the correct ZIP file
4. Check that version badge shows `v1.0.1`

### 5.2 Test Download Link

1. Click the download button on your GitHub Pages site
2. It should download `remote-file-uploader-ai1wm.zip`
3. Extract and verify all files are present

---

## Step 6: Future Releases

### 6.1 Update Version Number

When releasing a new version (e.g., v1.0.2):

1. **Update plugin file** (`remote-file-uploader-ai1wm.php`):
   ```php
   * Version: 1.0.2
   ```

2. **Update README.md** - Add to changelog:
   ```markdown
   ### 1.0.2 (2026-XX-XX)
   - Feature 1
   - Bug fix 2
   ```

3. **Commit changes**:
   ```bash
   git add .
   git commit -m "Bump version to 1.0.2"
   git push origin main
   ```

### 6.2 Create New Release

1. Create new ZIP file (same as Step 4.1)
2. Go to **Releases** → **Create a new release**
3. Tag: `v1.0.2`
4. Title: `Remote File Uploader for AI1WM v1.0.2`
5. Add release notes
6. Upload ZIP file
7. ✅ Set as latest release
8. Publish

### 6.3 Auto-Update Magic ✨

The GitHub Pages site (`index.html`) automatically fetches the latest release via GitHub API:

```javascript
// This code in index.html automatically updates the download link
fetch('https://api.github.com/repos/amartadey/Remote-File-Uploader-AI1WM/releases/latest')
```

**No manual updates needed!** When you publish a new release, the website automatically:
- Updates the version badge
- Updates the download link to the latest ZIP file

---

## Troubleshooting

### GitHub Pages Not Working

**Problem**: Site shows 404  
**Solution**: 
1. Go to Settings → Pages
2. Ensure Source is set to `main` branch, `/ (root)` folder
3. Wait 2-3 minutes for deployment
4. Hard refresh browser (Ctrl+F5)

### Download Link Not Working

**Problem**: Download button doesn't work  
**Solution**:
1. Ensure ZIP file is attached to the release
2. Check browser console for errors (F12)
3. Verify GitHub API is accessible (not blocked by firewall)

### Version Not Updating

**Problem**: Website shows old version  
**Solution**:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Check that release is marked as "Latest"
3. Wait a few minutes for GitHub API to update

---

## Quick Reference Commands

```bash
# Clone repository
git clone https://github.com/amartadey/Remote-File-Uploader-AI1WM.git

# Create release ZIP (Linux/Mac)
zip -r remote-file-uploader-ai1wm.zip remote-file-uploader-ai1wm \
  -x "*.git*" -x "*index.html" -x "*.DS_Store"

# Commit changes
git add .
git commit -m "Your commit message"
git push origin main

# Create tag
git tag v1.0.1
git push origin v1.0.1
```

---

## Summary

✅ **Repository**: https://github.com/amartadey/Remote-File-Uploader-AI1WM  
✅ **GitHub Pages**: https://amartadey.github.io/Remote-File-Uploader-AI1WM/  
✅ **Releases**: https://github.com/amartadey/Remote-File-Uploader-AI1WM/releases  

The GitHub Pages site automatically updates the download link whenever you publish a new release. Just follow Step 6 for future updates!
