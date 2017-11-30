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

    protected $amOnPageCalled = false;

    public function loginAsUser(\App\User $user)
    {
        if (!$this->amOnPageCalled) {
            $this->_amOnPage('/');
            $this->amOnPageCalled = true;
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
        if (!$this->amOnPageCalled) {
            $this->_amOnPage('/');
            $this->amOnPageCalled = true;
        }

        $this->setCookie(\App\Tests\TestHelper::COOKIE_NAME, 'ok');

        $this->_amOnPage($page);

        // assert that a response cookie is present
        $this->seeCookie(\App\Tests\TestHelper::RESPONSE_COOKIE_NAME);
    }

    public function amOnRoute($route, array $parameters = [])
    {
        $this->amOnPage(route($route, $parameters, false));
    }
}
