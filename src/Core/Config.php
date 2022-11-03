<?php

namespace Gibbon\Core;

/**
 * Representation of a GibbonEdu configuration.
 */
class Config
{
    /**
     * Gibbon installation guid.
     *
     * @var string
     */
    private $guid = '';

    /**
     * Hostname of database server.
     *
     * @var string|null
     */
    private $databaseServer = null;

    /**
     * Port of the database server.
     *
     * @var int
     */
    private $databasePort = 3306;

    /**
     * Database name.
     *
     * @var string|null
     */
    private $databaseName = null;

    /**
     * Database server username.
     *
     * @var string|null
     */
    private $databaseUsername = null;

    /**
     * Raw database password for the user.
     *
     * @var string|null
     */
    private $databasePassword = null;

    /**
     * How many times of page loaded to refresh cache.
     * Must be larger than 0.
     *
     * Default: 10
     */
    private $caching = 10;

    /**
     * The type of session handler to use.
     * Can be either 'database' or 'native'.
     *
     * @var string
     */
    private $sessionHandler = 'native';

    /**
     * If encryption is needed, the key for the encryption.
     *
     * @var string|null
     */
    private $sessionEncryptionKey = null;

    /**
     * Create config instance for given config file path.
     *
     * @param string $path  The path to a GibbonEdu config file.
     *
     * @return Config The config instance with information from config file, includes
     *   database connection info and guid.
     *
     * @throws \InvalidArgumentException If the path does not exists or is not readable.
     *
     * @todo Use different exception for "file not exists" and "file not readable".
     */
    public static function fromFile(string $path): Config
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new \InvalidArgumentException('Provided path does not exists or is not a file.');
        }
        if (!is_readable($path)) {
            throw new \InvalidArgumentException('Provided path is not readable.');
        }

        $config = (function () use ($path) {
            include($path);
            return [
                // Basic database variables.
                'databaseServer' => $databaseServer ?? null,
                'databasePort' => $databasePort ?? 3306,
                'databaseUsername' => $databaseUsername ?? null,
                'databasePassword' => $databasePassword ?? null,
                'databaseName' => $databaseName ?? null,

                // Gibbon environment GUID.
                'guid' => $guid ?? null,

                // Other configurations.
                'caching' => $caching ?? 10,
                'sessionHandler' => $sessionHandler ?? 'native',
                'sessionEncryptionKey' => $sessionEncryptionKey ?? null,
            ];
        })();

        return (new static())
            ->setDatabaseInfo(
                $config['databaseServer'],
                $config['databasePort'],
                $config['databaseName'],
                $config['databaseUsername'],
                $config['databasePassword']
            )
            ->setGuid($config['guid'])
            ->setCaching($config['caching'])
            ->setSessionHandler(
                $config['sessionHandler'],
                $config['sessionEncryptionKey']
            );
    }

    /**
     * Set the database related variables.
     *
     * @param string $databaseServer
     * @param int    $databasePort
     * @param string $databaseName
     * @param string $databaseUsername
     * @param string $databasePassword
     *
     * @return $this
     */
    public function setDatabaseInfo(
        string $databaseServer,
        int $databasePort,
        string $databaseName,
        string $databaseUsername,
        string $databasePassword = ''
    ): Config
    {
        if ($databasePort < 1) {
            throw new \InvalidArgumentException('$databasePort cannot be smaller than 1');
        }
        $this->databaseServer = $databaseServer;
        $this->databasePort = $databasePort;
        $this->databaseName = $databaseName;
        $this->databaseUsername = $databaseUsername;
        $this->databasePassword = $databasePassword;
        return $this;
    }

    /**
     * Set guid to the given string, or random set the
     * guid.
     *
     * @param string $guid  Gibbon installation ID string.
     *
     * @return $this
     */
    public function setGuid(string $guid): Config
    {
        $this->guid = $guid;
        return $this;
    }

    /**
     * Get the guid string for this config.
     *
     * @return string
     */
    public function getGuid(): string
    {
        return $this->guid;
    }

    /**
     * See getCaching().
     *
     * @param int $caching
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If the $caching parameter is less than 1.
     */
    public function setCaching(int $caching)
    {
        if ($caching < 1) {
            throw new \InvalidArgumentException('$caching cannot be smaller than 1');
        }
        $this->caching = $caching;
        return $this;
    }

    /**
     * Get the number of times of pageload
     * to force referesh cache.
     *
     * @return integer
     */
    public function getCaching(): int
    {
        return $this->caching;
    }

    /**
     * Set the session handler details.
     *
     * @param string       $sessionHandler         Type of session handler. 'database' or 'native'.
     * @param string|null  $sessionEncryptionKey   Optional. Encryption key if encryption is needed.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If $sessionHandler is not of valid value.
     */
    public function setSessionHandler(string $sessionHandler, ?string $sessionEncryptionKey = null)
    {
        if (!in_array($sessionHandler, ['database', 'native'])) {
            throw new \InvalidArgumentException('$sessionHandler can only be either "database" or "native"');
        }
        $this->sessionHandler = $sessionHandler;
        $this->sessionEncryptionKey = $sessionEncryptionKey;
        return $this;
    }

    /**
     * Get the session handler type.
     *
     * @return string  Either 'database' or 'native'.
     */
    public function getSessionHandler(): string
    {
        return $this->sessionHandler;
    }

    /**
     * Get the session encryption key, if any, or null.
     *
     * @return string|null
     */
    public function getSessionEncryptionKey(): ?string
    {
        return $this->sessionEncryptionKey;
    }

    /**
     * Get an assoc array of all the config file required variables
     * for config file rendering or else.
     *
     * @return array Associated array of all config file variables, includes
     *               everything from getDatabaseInfo() and 'guid'.
     */
    public function getVars(): array
    {
        return $this->getDatabaseInfo() + [
            'guid' => $this->getGuid(),
            'caching' => $this->getCaching(),
            'sessionHandler' => $this->getSessionHandler(),
            'sessionEncryptionKey' => $this->getSessionEncryptionKey(),
        ];
    }

    /**
     * Get database related configurations.
     *
     * @return array
     *   An assoc array of database configurations. Include these keys:
     *   - databaseServer: The hostname of the database server.
     *   - databasePort: The port number of the database server.
     *   - databaseName: The database name.
     *   - databaseUsername: The username for database server login.
     *   - databasePassword: The raw password for database server login.
     */
    public function getDatabaseInfo(): array
    {
        return [
            'databaseServer' => $this->databaseServer,
            'databasePort' => $this->databasePort,
            'databaseName' => $this->databaseName,
            'databaseUsername' => $this->databaseUsername,
            'databasePassword' => $this->databasePassword,
        ];
    }

    /**
     * Return the database name, if set. Or null, if not set.
     *
     * @return string|null
     */
    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    /**
     * Check if all database related variables are set.
     *
     * @return boolean
     */
    public function hasDatabaseInfo(): bool
    {
        // Note: presume all attributes are only settible by
        // the method, which only accept string values.
        return !empty($this->databaseServer)
            && !empty($this->databaseName)
            && !empty($this->databaseUsername)
            && !is_null($this->databasePassword);
    }

    /**
     * Validate database related variables.
     *
     * TODO: use exception raise instead of boolean return.
     *
     * @return boolean
     */
    public function validateDatabaseInfo(): bool
    {
        // Check config values for ' " \ / chars which will cause errors in config.php
        $pattern = '/[\'"\/\\\\]/';
        if (
            preg_match($pattern, $this->databaseServer) == true
            || preg_match($pattern, $this->databaseName) == true
            || preg_match($pattern, $this->databaseUsername) == true
        ) {
            return false;
        }
        return true;
    }
}
