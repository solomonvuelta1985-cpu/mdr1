# INSTALL PHPWORD - Quick Guide

## Step 1: Install Composer (if not installed)

### Download Composer:
1. Go to: https://getcomposer.org/download/
2. Download: `Composer-Setup.exe` for Windows
3. Run installer
4. Follow wizard (use default settings)

### Verify Installation:
```bash
composer --version
```

---

## Step 2: Install PHPWord

Open Command Prompt in project folder:

```bash
cd c:\xampp\htdocs\mdr1
composer install
```

This will:
- Create `vendor` folder
- Install PHPWord library
- Set up autoloading

---

## Step 3: Verify PHPWord Installation

```bash
# Check if vendor folder exists
dir vendor

# Check if PHPWord is installed
dir vendor\phpoffice\phpword
```

---

## Alternative: Manual Installation (Without Composer)

If you can't install Composer, I can provide a manual installation method.

**Just tell me:** "Use manual installation" and I'll create a different setup.

---

## Ready to Proceed?

Once PHPWord is installed:
1. Tell me "PHPWord installed"
2. I'll create the Terminal Report Generator
3. You'll be able to generate reports immediately!

---

## Troubleshooting

### Issue: "composer: command not found"
- Close and reopen Command Prompt after installing Composer
- Or restart computer

### Issue: "composer install" fails
- Check internet connection
- Try: `composer install --no-scripts`

### Issue: PHP version too old
- XAMPP usually has PHP 7.4+
- Check with: `c:\xampp\php\php.exe -v`
- Update XAMPP if needed

---

**Status: Waiting for PHPWord installation...**
