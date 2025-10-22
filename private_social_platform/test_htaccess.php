<?php
echo "This is a test file to check if .htaccess is working<br>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Query string: " . ($_SERVER['QUERY_STRING'] ?? 'none') . "<br>";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Apache modules: ";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo in_array('mod_rewrite', $modules) ? 'mod_rewrite ENABLED' : 'mod_rewrite NOT FOUND';
} else {
    echo "Cannot check (not Apache or function disabled)";
}
?>