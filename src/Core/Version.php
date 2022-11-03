<?php

namespace Gibbon\Core;

/**
 * Representation of a GibbonEdu version.
 */
class Version
{

    /**
     * Version string.
     *
     * @var string
     */
    protected $version = '';

    /**
     * An array of system requirements.
     *
     * @var array
     */
    protected $systemRequirements = [];

    /**
     * Create a Version instance from a given version.php file path.
     *
     * @param string $path
     *
     * @return static
     */
    public static function fromFile($path)
    {
        if (!file_exists($path)) {
            throw new \Exception('Gibbon version.php file missing: ' . $path);
        }

        include $path;

        $instance = new static();
        $instance->version = $version ?? 'version-not-found';
        $instance->systemRequirements = $systemRequirements ?? [];
        return $instance;
    }

    /**
     * Implement magic method for read-only access of
     * protected / private variables.
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, array_keys(get_object_vars($this)))) {
            return $this->{$key};
        }
        return null; // Return null by default.
    }
}
