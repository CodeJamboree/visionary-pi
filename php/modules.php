<?php
// Check if the function exists
if (function_exists('apache_get_modules')) {
    $enabled_modules = apache_get_modules();
    echo "Enabled Apache Modules:\n";
    foreach ($enabled_modules as $module) {
        echo "<li>$module</li>";
    }
} else {
    echo "The apache_get_modules function is not available.";
}
