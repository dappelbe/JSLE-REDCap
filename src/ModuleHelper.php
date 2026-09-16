<?php
namespace UoL\JSLE;

class ModuleHelper
{
    /**
     * Load Composer autoload for the external module if not already loaded.
     *
     * @param string|null $startDir Optional starting dir to resolve vendor/autoload.php; defaults to __DIR__.
     * @return bool true if autoloader was required or already present; false if not found.
     */
    public static function ensureVendorAutoload(?string $startDir = null): bool
    {
        // If the project's classes are already available, skip requiring autoload.
        if (class_exists(\UoL\JSLE\AccessibleRecordsFetcher::class, false) ||
            class_exists(\Composer\Autoload\ClassLoader::class, false)
        ) {
            return true; // autoloader already present
        }

        $dir = $startDir ?? __DIR__;

        // Walk upward a couple levels to find vendor/autoload.php (robust for different layouts)
        $maxLevels = 4;
        $current = $dir;
        for ($i = 0; $i <= $maxLevels; $i++) {
            $candidate = $current . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
            if (file_exists($candidate)) {
                require_once $candidate;
                return true;
            }
            // move up a directory
            $current = dirname($current);
            if ($current === '/' || $current === '.' || $current === '') {
                break;
            }
        }

        // Not found — fail gracefully
        return false;
    }
}
