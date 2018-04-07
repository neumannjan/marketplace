<?php

/**
 * Get the path to a versioned webpack asset file
 *
 * @param string $name
 *
 * @return string
 */
function hashedAsset($name)
{
    $manifestPath = public_path('manifest.json');
    $manifest     = json_decode(file_get_contents($manifestPath));

    return url($manifest->$name);
}