<?php

namespace App\Tests;


class TestHelper
{
    const TEMP_FILE_NAME = 'acceptance-is-running.tmp';
    const COOKIE_NAME = 'selenium_request';
    const COOKIE_AUTH_NAME = 'selenium_auth';
    const RESPONSE_COOKIE_NAME = 'testing_environment_enabled';

    protected $testsDir;
    protected $tempFile;
    protected $tempFileExists = null;
    protected $hasCookie = null;
    protected $authAs = null;

    /**
     * TestHelper constructor.
     *
     * @param string $testsDir
     */
    public function __construct($testsDir)
    {
        $this->testsDir = $testsDir;
        $this->tempFile = $testsDir.'/_output/'.self::TEMP_FILE_NAME;
    }

    /**
     * @return string
     */
    public function getTestsDir()
    {
        return $this->testsDir;
    }

    /**
     * Whether the Laravel instance is run the Codeception test runner itself
     *
     * @return bool
     */
    public function isRunner()
    {
        return defined('CODECEPT_TEST_RUNNER') && CODECEPT_TEST_RUNNER;
    }

    /**
     * Whether the request is a Selenium request
     *
     * @return bool
     */
    public function isSeleniumRequest()
    {
        if ($this->tempFileExists === null) {
            $this->tempFileExists = file_exists($this->tempFile);
        }

        if ($this->hasCookie === null) {
            $this->hasCookie = isset($_COOKIE[self::COOKIE_NAME]);
        }

        return $this->tempFileExists && $this->hasCookie;
    }

    /**
     * Which user ID to authorize as
     *
     * @return int|null
     */
    public function authAs()
    {
        if ($this->isSeleniumRequest()
            && isset($_COOKIE[self::COOKIE_AUTH_NAME])
        ) {
            return (int)$_COOKIE[self::COOKIE_AUTH_NAME];
        }

        return null;
    }

    /**
     * Creates a temporary file that enables isSeleniumRequest to return true.
     */
    public function enableSeleniumRequests()
    {
        if ($this->tempFileExists) {
            return;
        }

        file_put_contents($this->tempFile, '');

        $this->tempFileExists = true;
    }

    /**
     * Deletes the temporary file that enables isSeleniumRequest to return true.
     */
    public function disableSeleniumRequests()
    {
        if ( ! $this->tempFileExists) {
            return;
        }

        unlink($this->tempFile);

        $this->tempFileExists = false;
    }
}