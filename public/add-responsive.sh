#!/bin/bash

# Script to add responsive.css to all pages that don't have it yet
# Run this from the public directory

echo "🔄 Adding responsive.css to all pages..."

# Array of files to update
files=(
    "dashboard.php"
    "profile.php"
    "my-events.php"
    "notifications.php"
    "event-detail.php"
    "payment.php"
    "register-event.php"
    "admin/dashboard.php"
    "admin/events.php"
    "admin/users.php"
    "admin/categories.php"
    "admin/analytics.php"
    "admin/notifications.php"
    "admin/event-participants.php"
    "admin/user-edit.php"
    "admin/user-create.php"
)

# Loop through each file
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        # Check if responsive.css is already included
        if ! grep -q "responsive.css" "$file"; then
            echo "📝 Adding to: $file"
            
            # For admin files, use correct path
            if [[ $file == admin/* ]]; then
                responsive_link='<link rel="stylesheet" href="../assets/css/responsive.css?v=<?= time() ?>">'
            else
                responsive_link='<link rel="stylesheet" href="assets/css/responsive.css?v=<?= time() ?>">'
            fi
            
            # Insert after bootstrap CSS line
            sed -i '' "/bootstrap@5.3.0/a\\
$responsive_link
" "$file" 2>/dev/null || echo "  ⚠️  Could not auto-update $file - add manually"
        else
            echo "✅ Already has responsive.css: $file"
        fi
    else
        echo "❌ File not found: $file"
    fi
done

echo ""
echo "✅ Done! Responsive CSS added to all applicable pages."
echo "🔄 Please refresh your browser (Cmd+Shift+R) to see changes."
