<?php
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    foreach ($modules as $module) {
        echo "<li>$module</li>";
    }
} else {
    echo "function not exists";
}
