<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Tests\TestHelper;

class Acceptance extends \Codeception\Module
{

    /** @var TestHelper */
    protected $testHelper;

    /**
     * @inheritDoc
     */
    public function _beforeSuite($settings = [])
    {
        $this->testHelper = resolve(\App\Tests\TestHelper::class);
        $this->testHelper->enableSeleniumRequests();
    }

    /**
     * @inheritDoc
     */
    public function _afterSuite()
    {
        $this->testHelper->disableSeleniumRequests();
    }

}
