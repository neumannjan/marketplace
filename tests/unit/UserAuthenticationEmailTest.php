<?php


class UserAuthenticationEmailTest extends \Codeception\Test\Unit
{
    const USERNAME = 'testuser';
    const DISPLAY_NAME = 'Test User';
    const EMAIL = 'test@email.com';
    const PASSWORD = 'testpassword99';

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \App\User::truncate();
    }

    protected function _after()
    {
    }

    // tests
    public function testUserAuthenticationEmailSend()
    {
        $i = $this->tester;

        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->request->set('username', self::USERNAME);
        $request->request->set('email', self::EMAIL);
        $request->request->set('password', self::PASSWORD);
        $request->request->set('password_confirmation', self::PASSWORD);
        $request->request->set('display_name', self::DISPLAY_NAME);

        $controller = new \App\Http\Controllers\Auth\RegisterController();
        $controller->register($request);

        $user = \App\User::first();
        $this->assertNotNull($user);
        $this->assertRegExp('/\S{2,}/', $user->activation_token);

        $i->seeEmailWasSent();
        $i->seeEmailWasSentTo(self::EMAIL);
        $i->seeEmailContains(preg_quote(__('email.register-activate.action'), '/'));
        $i->seeEmailContains(preg_quote(route('user.activate', [
            'token' => $user->activation_token,
            'username' => self::USERNAME
        ]), '/'));
    }
}