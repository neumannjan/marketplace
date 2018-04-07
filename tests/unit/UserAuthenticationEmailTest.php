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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        \App\User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    protected function _after()
    {
    }

    protected function _registerUser($status = \App\User::STATUS_ACTIVE)
    {
        $user               = new \App\User();
        $user->username     = self::USERNAME;
        $user->display_name = self::DISPLAY_NAME;
        $user->email        = self::EMAIL;
        $user->password     = Hash::make(self::PASSWORD);
        $user->status       = $status;
        $user->save();

        return $user;
    }

    // tests
    public function testUserAuthenticationEmailSend()
    {
        $i = $this->tester;

        $i->sendPrivateApiRequest([
            'register' => [
                'username' => self::USERNAME,
                'email' => self::EMAIL,
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
                'display_name' => self::DISPLAY_NAME,
            ],
        ]);

        $user = \App\User::first();
        $this->assertNotNull($user);
        $this->assertRegExp('/\S{2,}/', $user->activation_token);

        $i->seeEmailWasSent();
        $i->seeEmailWasSentTo(self::EMAIL);
        $i->seeEmailContains(preg_quote(__('email.register-activate.action'),
            '/'));
        $i->seeEmailContains(preg_quote(route('user.activate', [
            'token' => $user->activation_token,
            'username' => self::USERNAME,
        ]), '/'));

        // there used to be a bug that displayed HTML as text; hence this check
        $i->seeEmailContainsNot('<pre');
    }

    public function testPasswordResetEmailSend()
    {
        $i = $this->tester;
        $this->_registerUser();

        $i->sendPrivateApiRequest([
            'password-email' => [
                'email' => self::EMAIL,
            ],
        ]);

        $i->seeEmailWasSent();
        $i->seeEmailWasSentTo(self::EMAIL);
        $i->seeEmailContains(preg_quote(__('email.password-reset.action'),
            '/'));

        // there used to be a bug that displayed HTML as text; hence this check
        $i->seeEmailContainsNot('<pre');
    }

    public function testPasswordResetEmailContents()
    {
        $i    = $this->tester;
        $user = $this->_registerUser();

        $repo = app(\Illuminate\Auth\Passwords\DatabaseTokenRepository::class, [
            'table' => 'password_resets',
            'hashKey' => 'hashkey',
        ]);

        $token = $repo->create($user);
        $user->sendPasswordResetNotification($token);

        $i->seeEmailWasSent();
        $i->seeEmailWasSentTo(self::EMAIL);
        $i->seeEmailContains(preg_quote(__('email.password-reset.action'),
            '/'));
        $i->seeEmailContains(preg_quote(\App\Http\AppRoutes::passwordReset($token),
            '/'));

        // there used to be a bug that displayed HTML as text; hence this check
        $i->seeEmailContainsNot('<pre');
    }
}