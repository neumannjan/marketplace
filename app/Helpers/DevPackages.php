<?php

namespace App\Helpers;


/**
 * Composer helper to determine whether dev packages are available
 */
class DevPackages
{
    const FILENAME = 'dev-packages.php';

    /** @var null|bool */
    private $available = null;

    /**
     * Get whether composer dev packages are available.
     * Allows us to use dev packages safely without having to call functions such as `class_exists`.
     * See the source code of {@see \App\Console\Kernel::commands} to see how it is used.
     * @return boolean
     */
    public function available()
    {
        if ($this->available !== null) {
            return $this->available;
        }

        $fileName = self::FILENAME;
        $file = app()->bootstrapPath("cache/$fileName");

        return $this->available = file_exists($file) ? include $file : false;
    }
}