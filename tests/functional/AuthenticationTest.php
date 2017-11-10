<?php

/**
 * TODO convert to acceptance
 */
class AuthenticationTest extends \Codeception\Test\Unit
{
    const USERNAME = 'testuser';
    const DISPLAY_NAME = 'Test User';
    const EMAIL = 'test@email.com';
    const PASSWORD = 'testpassword99';

    /**
     * @var \FunctionalTester
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
    protected function _register($status = \App\User::STATUS_ACTIVE)
    {
        $user = new \App\User([
            'username' => self::USERNAME,
            'email' => self::EMAIL,
            'display_name' => self::DISPLAY_NAME,
            'status' => $status,
            'password' => Hash::make(self::PASSWORD),
        ]);

        $user->save();

        return $user;
    }

    public function testUserRegistrationAndActivation()
    {
        $i = $this->tester;

        $i->amOnRoute('register');
        $i->seeResponseCodeIs(200);
        $i->see(__('validation.attributes.password_confirmation'));

        $i->submitForm('#form-register', [
            'username' => self::USERNAME,
            'email' => self::EMAIL,
            'display_name' => self::DISPLAY_NAME,
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        $i->seeResponseCodeIs(200);
        $i->see(__('flash.success.register', [
            'email' => self::EMAIL
        ]));

        $user = \App\User::first();
        $this->assertEquals(self::USERNAME, $user->username);
        $this->assertEquals(self::EMAIL, $user->email);
        $this->assertEquals(self::DISPLAY_NAME, $user->display_name);
        $this->assertEquals(\App\User::STATUS_INACTIVE, $user->status);
        $this->assertRegExp('/\S{2,}/', $user->activation_token);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));

        $i->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => $user->username
        ]);
        $i->see(__('flash.success.activate'));

        $user = \App\User::first();
        $this->assertEquals(\App\User::STATUS_ACTIVE, $user->status);
    }

    public function testUserActivationTokenInvalidRejection()
    {
        $i = $this->tester;

        $user = $this->_register(\App\User::STATUS_INACTIVE);

        $i->amOnRoute('user.activate', [
            'token' => 'wrong',
            'username' => $user->username
        ]);

        $i->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $this->assertEquals(\App\User::STATUS_INACTIVE, $user->status);
    }

    public function testUserActivationNameInvalidRejection()
    {
        $i = $this->tester;

        $user = $this->_register(\App\User::STATUS_INACTIVE);

        $i->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => 'wrong'
        ]);

        $i->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $this->assertEquals(\App\User::STATUS_INACTIVE, $user->status);
    }

    public function testActiveUserActivationRejection()
    {
        $i = $this->tester;

        $user = $this->_register(\App\User::STATUS_ACTIVE);

        $i->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => $user->username
        ]);

        $i->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $this->assertEquals(\App\User::STATUS_ACTIVE, $user->status);
    }

    public function testBannedUserActivationRejection()
    {
        $i = $this->tester;

        $user = $this->_register(\App\User::STATUS_BANNED);

        $i->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => $user->username
        ]);

        $i->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $this->assertEquals(\App\User::STATUS_BANNED, $user->status);
    }

    public function testUserLoginWithUsername()
    {
        $i = $this->tester;

        $user = $this->_register();

        $i->amOnRoute('login');
        $i->submitForm('#form-login', [
            'login' => self::USERNAME,
            'password' => self::PASSWORD
        ]);

        $i->seeResponseCodeIs(200);
        $i->seeAuthentication();
    }

    public function testUserLoginWithEmail()
    {
        $i = $this->tester;

        $user = $this->_register();

        $i->amOnRoute('login');
        $i->submitForm('#form-login', [
            'login' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $i->seeResponseCodeIs(200);
        $i->seeAuthentication();
    }

    public function testUserLoginWrongCredentials()
    {
        $i = $this->tester;

        $this->_register();

        $i->amOnRoute('login');
        $i->submitForm('#form-login', [
            'login' => self::EMAIL,
            'password' => 'wroooooong10'
        ]);

        $i->seeResponseCodeIs(200);
        $i->seeCurrentRouteIs('login');
        $i->dontSeeAuthentication();
    }

    public function testInactiveUserLoginRejection()
    {
        $i = $this->tester;

        $this->_register(\App\User::STATUS_INACTIVE);

        $i->amOnRoute('login');
        $i->submitForm('#form-login', [
            'login' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $i->seeResponseCodeIs(200);
        $i->seeCurrentRouteIs('login');
        $i->see(__('auth.inactive'));
        $i->dontSeeAuthentication();
    }

    public function testBannedUserLoginRejection()
    {
        $i = $this->tester;

        $this->_register(\App\User::STATUS_BANNED);

        $i->amOnRoute('login');
        $i->submitForm('#form-login', [
            'login' => self::EMAIL,
            'password' => self::PASSWORD
        ]);

        $i->seeResponseCodeIs(200);
        $i->seeCurrentRouteIs('login');
        $i->see(__('auth.banned'));
        $i->dontSeeAuthentication();
    }

    public function testBannedUserSessionExpiration()
    {
        $i = $this->tester;

        $user = $this->_register();

        $i->amLoggedAs($user);
        $i->amOnRoute('index');

        $user->status = \App\User::STATUS_BANNED;
        $user->save();

        $i->seeAuthentication();

        $i->amOnRoute('index');
        $i->dontSeeAuthentication();
    }

    public function testBannedUserSessionExpirationImmediate()
    {
        $i = $this->tester;

        $user = $this->_register(\App\User::STATUS_BANNED);

        $i->amLoggedAs($user);
        $i->amOnRoute('index');
        $i->dontSeeAuthentication();
    }
}