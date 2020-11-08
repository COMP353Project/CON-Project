<?php
// Read REQUEST_URI, suppress errors (gave E_WARNING prior to PHP 5.3.3).
$uriData = @parse_url($_SERVER['REQUEST_URI']);

$path = '';
if ($uriData === false) {
    // Do something?
} else {
    if (isset($uriData['path'])) {
        // We might be in a subdirectory of the webroot.
        // We are only interested in the part starting from this relative root.
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $uriData['path']);

        echo("ORIGINAL PATH: \n\n" . $path . "\n\n");
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_NAME']));
        // Strip the relative path from $path.
        $path = substr($path, strlen($relativePath));
        // Finally, strip any leading/trailing slashes so we end up with a "cleaned" path.
        $path = trim($path, '/');

        echo($path);
    }
}
