<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions {
        amOnPage as protected _amOnPage;
    }

    /*
     * Define custom actions here
     */

    protected $usingTestEnvironment = false;

    public function useTestEnvironment()
    {
        if (!$this->usingTestEnvironment) {
            $this->usingTestEnvironment = true;
        }
    }

    public function loginAsUser(\App\User $user)
    {
        if (!$this->usingTestEnvironment) {
            $this->useTestEnvironment();
        }

        $this->setCookie(\App\Tests\TestHelper::COOKIE_AUTH_NAME, (string)$user->id);
        $this->amOnPage('/');
        $this->resetCookie(\App\Tests\TestHelper::COOKIE_AUTH_NAME);
    }

    /**
     * @inheritDoc
     */
    public function amOnPage($page)
    {
        if ($this->usingTestEnvironment) {
            $this->setCookie(\App\Tests\TestHelper::COOKIE_NAME, 'ok');
        }

        $this->_amOnPage($page);

        // assert that a response cookie is present
        if ($this->usingTestEnvironment) {
            $this->seeCookie(\App\Tests\TestHelper::RESPONSE_COOKIE_NAME);
        }
    }

    public function amOnRoute($route, array $parameters = [])
    {
        $this->amOnPage(route($route, $parameters, false));
    }
}
