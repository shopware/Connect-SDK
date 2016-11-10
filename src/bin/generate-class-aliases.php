<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$directoryIterator = new RecursiveDirectoryIterator(__DIR__ . '/../main');
$iterator = new RecursiveIteratorIterator($directoryIterator);
$phpFileIterator = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($phpFileIterator as $file) {
    $file = realpath($file[0]);
    require_once $file;
}

$classes = get_declared_classes();
$content = "<?php\n";
foreach ($classes as $class) {
    if (strpos($class, 'Bepado\SDK') === 0) {
        $newClass = str_replace('Bepado\SDK', 'Shopware\Connect', $class);
        $oldClass = $class;
    } else if (strpos($class, 'Shopware\Connect') === 0) {
        $oldClass = str_replace('Shopware\Connect', 'Bepado\SDK', $class);
        $newClass = $class;
    } else {
        continue;
    }
    $content .= sprintf("class_alias('%s', '%s');\n", $newClass, $oldClass);
}
echo $content;
