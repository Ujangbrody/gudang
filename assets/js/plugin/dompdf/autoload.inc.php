<?php







spl_autoload_register(function($class)
{
    if (strpos($class, 'Sabberworm') !== false) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = realpath(__DIR__ . '/lib/php-css-parser/lib/' . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php');
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});


require_once __DIR__ . '/lib/php-font-lib/src/FontLib/Autoloader.php';


require_once __DIR__ . '/lib/php-svg-lib/src/autoload.php';



require_once __DIR__ . '/src/Autoloader.php';

Dompdf\Autoloader::register();
