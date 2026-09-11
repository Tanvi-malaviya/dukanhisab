<?php
// Script to fix storage/fonts directory and file permissions programmatically
$fontsDir = __DIR__ . '/../storage/fonts';

echo "<h2>DukanHisab Font Permissions Fixer</h2>";

if (!file_exists($fontsDir)) {
    if (mkdir($fontsDir, 0777, true)) {
        echo "<p style='color:green;'>✓ Created storage/fonts directory with 777 permissions.</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to create storage/fonts directory.</p>";
    }
} else {
    echo "<p style='color:blue;'>i storage/fonts directory already exists.</p>";
}

if (is_dir($fontsDir)) {
    // Attempt to chmod the directory to 777
    if (chmod($fontsDir, 0777)) {
        echo "<p style='color:green;'>✓ Changed storage/fonts directory permissions to 777.</p>";
    } else {
        echo "<p style='color:orange;'>! Warning: Failed to chmod storage/fonts to 777. Checking current owner/permissions.</p>";
    }

    // List and try to fix permissions of existing files or delete .ufm files
    $files = glob("$fontsDir/*");
    $ufmDeleted = 0;
    $errors = 0;

    foreach ($files as $file) {
        $filename = basename($file);
        // If it's a .ufm file, delete it so DomPDF can regenerate it with correct web-server permissions
        if (pathinfo($file, PATHINFO_EXTENSION) === 'ufm') {
            if (unlink($file)) {
                $ufmDeleted++;
            } else {
                echo "<p style='color:red;'>✗ Failed to delete UFM file: $filename</p>";
                $errors++;
            }
        } else {
            // It's a .ttf file, make sure it is readable
            if (chmod($file, 0666)) {
                // Success
            } else {
                echo "<p style='color:orange;'>! Warning: Failed to chmod file: $filename</p>";
            }
        }
    }

    if ($ufmDeleted > 0) {
        echo "<p style='color:green;'>✓ Deleted $ufmDeleted cached .ufm font metrics files. They will be automatically regenerated with correct permissions when you load the PDF.</p>";
    }
    
    // Check writability
    if (is_writable($fontsDir)) {
        echo "<p style='color:green; font-weight:bold;'>✓ SUCCESS: storage/fonts is writable by the web server process!</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>✗ ERROR: storage/fonts is STILL NOT writable by the web server process.</p>";
    }
} else {
    echo "<p style='color:red;'>✗ storage/fonts is not a directory.</p>";
}
