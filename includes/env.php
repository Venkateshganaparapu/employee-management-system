<?php
// includes/env.php
// Minimal .env file loader — no external libraries required.
// Loads key=value pairs from the project root .env file into $_ENV and putenv().
// Safe to call multiple times (only loads once).

if (!defined('ENV_LOADED')) {
    define('ENV_LOADED', true);

    // Locate .env — support running from subdirectories (e.g. /api/)
    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            // Only process lines that look like KEY=VALUE
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes from value
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Don't override values already set in the environment
            if (!array_key_exists($key, $_ENV) && getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

/**
 * Get an environment variable with an optional default.
 *
 * @param  string $key
 * @param  mixed  $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed {
    $val = $_ENV[$key] ?? getenv($key);
    if ($val === false || $val === null) {
        return $default;
    }
    // Cast common boolean-like strings
    return match (strtolower((string)$val)) {
        'true',  '1', 'yes', 'on'  => true,
        'false', '0', 'no',  'off' => false,
        default => $val,
    };
}
