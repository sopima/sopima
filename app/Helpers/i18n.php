<?php
declare(strict_types=1);

function __($key, array $replace = []): string
{
    global $lang;
    $text = $lang[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace(':' . $k, (string)$v, $text);
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function load_lang(string $locale = 'de'): void
{
    global $lang;
    $path = BASE_PATH . '/lang/' . $locale . '.php';
    if (!file_exists($path)) {
        $path = BASE_PATH . '/lang/de.php';
    }
    $lang = file_exists($path) ? require $path : [];
}