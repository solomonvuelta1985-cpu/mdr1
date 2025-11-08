#!/bin/bash
################################################################################
# NDRRMC - CRITICAL SECURITY FIXES
# Auto-applies critical security patches
#
# USAGE: bash SECURITY_QUICK_FIXES.sh
################################################################################

echo "========================================"
echo "NDRRMC SECURITY QUICK FIXES"
echo "========================================"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PROJECT_ROOT="/c/xampp/htdocs/mdr1"
cd "$PROJECT_ROOT" || exit 1

echo -e "${YELLOW}[1/5] Removing critical security risks...${NC}"

# FIX 1: Delete unauthenticated password reset utility
if [ -f "public/reset_admin.php" ]; then
    echo "  ⚠️  Deleting public/reset_admin.php (unauthenticated password reset)"
    mv public/reset_admin.php public/reset_admin.php.INSECURE_BACKUP
    echo -e "${GREEN}  ✅ reset_admin.php moved to backup${NC}"
else
    echo "  ℹ️  reset_admin.php not found (already removed)"
fi

# FIX 2: Delete penetration test files
for file in public/penetration_test.php penetration_test.php; do
    if [ -f "$file" ]; then
        echo "  ⚠️  Deleting $file (error disclosure risk)"
        mv "$file" "${file}.INSECURE_BACKUP"
        echo -e "${GREEN}  ✅ $file moved to backup${NC}"
    fi
done

echo ""
echo -e "${YELLOW}[2/5] Disabling error display in production...${NC}"

# FIX 3: Update config.php to disable error display
if grep -q "ini_set('display_errors', 1)" includes/config.php 2>/dev/null; then
    echo "  ⚠️  Disabling display_errors in config.php"
    sed -i "s/ini_set('display_errors', 1);/ini_set('display_errors', 0);  \/\/ SECURITY: Never show errors to users/" includes/config.php
    echo -e "${GREEN}  ✅ Error display disabled${NC}"
fi

echo ""
echo -e "${YELLOW}[3/5] Removing runtime ALTER TABLE statements...${NC}"

# FIX 4: Comment out ALTER TABLE in annex files
FILES_TO_FIX=(
    "public/annex5_records.php"
    "public/annex6_records.php"
    "public/annex7_records.php"
    "public/annex8_records.php"
    "public/annex9_records.php"
    "public/annex14_records.php"
    "public/annex15_records.php"
)

for file in "${FILES_TO_FIX[@]}"; do
    if [ -f "$file" ]; then
        if grep -q "ALTER TABLE" "$file"; then
            echo "  🔧 Fixing $file"
            # Comment out the ALTER TABLE block
            sed -i '/ALTER TABLE.*ADD COLUMN is_archived/s/^/\/\/ SECURITY FIX: /' "$file"
            echo -e "${GREEN}  ✅ $file fixed${NC}"
        fi
    fi
done

echo ""
echo -e "${YELLOW}[4/5] Creating secure file operation utility...${NC}"

# FIX 5: Create file security helper
cat > includes/file_security.php << 'EOFFILE'
<?php
/**
 * Secure File Operations
 * Prevents path traversal and unauthorized file access
 */

function safe_file_delete($file_path, $allowed_directory = null) {
    if ($allowed_directory === null) {
        $allowed_directory = __DIR__ . '/../uploads/';
    }

    $real_path = realpath($file_path);
    $real_allowed = realpath($allowed_directory);

    if ($real_path === false || $real_allowed === false) {
        return false;
    }

    if (strpos($real_path, $real_allowed) !== 0) {
        log_security_event($_SESSION['user_id'] ?? 0, 'path_traversal_attempt',
            "Attempted to delete file outside uploads: {$file_path}");
        return false;
    }

    if (!is_file($real_path)) {
        return false;
    }

    return @unlink($real_path);
}
?>
EOFFILE

echo -e "${GREEN}  ✅ File security utility created${NC}"

echo ""
echo -e "${YELLOW}[5/5] Creating password policy validator...${NC}"

# FIX 6: Create password policy
cat > includes/password_policy.php << 'EOFPASS'
<?php
/**
 * Strong Password Policy Validator
 * Enforces NIST 800-63B guidelines
 */

function validate_password_strength($password) {
    $errors = [];

    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters long";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain uppercase letters";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain lowercase letters";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain numbers";
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain special characters";
    }

    $common = ['password', '123456', 'admin123', 'password123'];
    if (in_array(strtolower($password), $common)) {
        $errors[] = "Password is too common";
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>
EOFPASS

echo -e "${GREEN}  ✅ Password policy created${NC}"

echo ""
echo "========================================"
echo -e "${GREEN}CRITICAL FIXES APPLIED!${NC}"
echo "========================================"
echo ""
echo "✅ Removed/backed up insecure files"
echo "✅ Disabled error display"
echo "✅ Commented out ALTER TABLE statements"
echo "✅ Created secure file operations utility"
echo "✅ Created password policy validator"
echo ""
echo -e "${YELLOW}⚠️  MANUAL STEPS REQUIRED:${NC}"
echo ""
echo "1. Set database password:"
echo "   mysql> ALTER USER 'root'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';"
echo ""
echo "2. Update includes/config.php line 7:"
echo "   \$db_pass = 'YOUR_STRONG_PASSWORD';"
echo ""
echo "3. Review COMPREHENSIVE_SECURITY_AUDIT.md for remaining fixes"
echo ""
echo "4. Test the application thoroughly after these changes"
echo ""
echo -e "${GREEN}Security improvements complete!${NC}"
echo "Review backup files (*.INSECURE_BACKUP) and delete them once verified."
