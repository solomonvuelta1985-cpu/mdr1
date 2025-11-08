#!/bin/bash
# Fix ALTER TABLE vulnerability in all annex files

FILES=$(grep -rl "ALTER TABLE.*ADD COLUMN is_archived" /c/xampp/htdocs/mdr1/public/*.php)

for file in $FILES; do
    echo "Fixing: $file"
   sed -i 's/\$pdo->exec("ALTER TABLE/\/\/ SECURITY FIX - DO NOT AUTO-ALTER: \/\/ $pdo->exec("ALTER TABLE/g' "$file"
    
    # Add security warning before the exec line
    sed -i '/SHOW COLUMNS.*is_archived/a\    if ($column_check->rowCount() == 0) {\n        \/\/ SECURITY: Do NOT modify database schema from web application\n        error_log("CRITICAL: Missing is_archived column. Run migrations manually");\n        set_flash('"'"'Database schema error. Contact administrator.'"'"', '"'"'error'"'"');\n        header('"'"'Location: dashboard.php'"'"');\n        exit;\n    }\n    if (false) { \/\/ DISABLED FOR SECURITY' "$file"
done

echo "ALTER TABLE fix applied to all files"
