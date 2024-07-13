<?php

/**
 * Gets the real path of a file with all shell expansions resolved.
 *
 * Note that I tried calling PHP's realpath(), but this didn't expand
 * the ~ character to the home directory as I expected.
 *
 * @return void
 */
function getRealPath( string $path ) : string
{
    exec("echo $path", $output, $resultCode);

    if ($resultCode !== 0) {
        throw new \Exception("Failed to get expanded path for $path");
    }

    return $output[0];
}
