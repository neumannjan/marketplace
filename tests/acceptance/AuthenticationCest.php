<?php


class AuthenticationCest
{
    const USERNAME = 'testuser';
    const DISPLAY_NAME = 'Test User';
    const EMAIL = 'test@email.com';
    const PASSWORD = 'testpassword99';

    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->useTestEnvironment();
        \App\User::truncate();
    }

    public function _after(AcceptanceTester $I)
    {
    }

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

    // tests
    public function userRegistrationAndActivation(AcceptanceTester $I)
    {
        $I->amOnPage('/register');
        $I->wait(1);
        $I->see(__('validation.attributes.password_confirmation'));

        $I->fillField(['name' => 'username'], self::USERNAME);
        $I->fillField(['name' => 'email'], self::EMAIL);
        $I->fillField(['name' => 'display_name'], self::DISPLAY_NAME);
        $I->fillField(['name' => 'password'], self::PASSWORD);
        $I->fillField(['name' => 'password_confirmation'], self::PASSWORD);

        $I->click('#submit');

        $I->wait(1);
        $I->dontSeeElement('input', ['name' => 'password_confirmation']);
        $I->waitForElement('.alert', 5);
        $I->see(__('flash.success.register', [
            'email' => self::EMAIL
        ]));

        $user = \App\User::first();
        $I->assertEquals(self::USERNAME, $user->username);
        $I->assertEquals(self::EMAIL, $user->email);
        $I->assertEquals(self::DISPLAY_NAME, $user->display_name);
        $I->assertEquals(\App\User::STATUS_INACTIVE, $user->status);
        $I->assertRegExp('/\S{2,}/', $user->activation_token);
        $I->assertTrue(Hash::check(self::PASSWORD, $user->password));

        // activate

        $I->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => $user->username
        ]);
        $I->see(__('flash.success.activate'));

        $user = \App\User::first();
        $I->assertEquals(\App\User::STATUS_ACTIVE, $user->status);
    }

    public function userActivationTokenInvalidRejection(AcceptanceTester $I)
    {
        $user = $this->_register(\App\User::STATUS_INACTIVE);

        $I->amOnRoute('user.activate', [
            'token' => 'wrong',
            'username' => $user->username
        ]);

        $I->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $I->assertEquals(\App\User::STATUS_INACTIVE, $user->status);
    }

    public function userActivationNameInvalidRejection(AcceptanceTester $I)
    {
        $user = $this->_register(\App\User::STATUS_INACTIVE);

        $I->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => 'wrong'
        ]);

        $I->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $I->assertEquals(\App\User::STATUS_INACTIVE, $user->status);
    }

    public function activeUserActivationRejection(AcceptanceTester $I)
    {
        $user = $this->_register(\App\User::STATUS_ACTIVE);

        $I->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => $user->username
        ]);

        $I->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $I->assertEquals(\App\User::STATUS_ACTIVE, $user->status);
    }

    public function bannedUserActivationRejection(AcceptanceTester $I)
    {
        $user = $this->_register(\App\User::STATUS_BANNED);

        $I->amOnRoute('user.activate', [
            'token' => $user->activation_token,
            'username' => $user->username
        ]);

        $I->see(__('flash.danger.activate-link-invalid'));

        $user = \App\User::first();
        $I->assertEquals(\App\User::STATUS_BANNED, $user->status);
    }

    public function loginWithUsername(AcceptanceTester $I)
    {
        $this->_register(\App\User::STATUS_ACTIVE);

        $I->amOnPage('/login');

        $I->fillField(['name' => 'login'], self::USERNAME);
        $I->fillField(['name' => 'password'], self::PASSWORD);

        $I->click('#submit');

        $I->wait(1);
        $I->dontSeeElement('input', ['name' => 'password']);
        $I->seeElement('.fa-sign-out');
    }

    public function loginWithEmail(AcceptanceTester $I)
    {
        $this->_register(\App\User::STATUS_ACTIVE);

        $I->amOnPage('/login');

        $I->fillField(['name' => 'login'], self::EMAIL);
        $I->fillField(['name' => 'password'], self::PASSWORD);

        $I->click('#submit');

        $I->wait(1);
        $I->dontSeeElement('input', ['name' => 'password']);
        $I->seeElement('.fa-sign-out');
    }

    public function loginWrongCredentials(AcceptanceTester $I)
    {
        $this->_register(\App\User::STATUS_ACTIVE);

        $I->amOnPage('/login');

        $I->fillField(['name' => 'login'], self::EMAIL);
        $I->fillField(['name' => 'password'], 'wrooooooong9');

        $I->click('#submit');

        $I->wait(1);
        $I->seeElement('input', ['name' => 'login']);
        $I->see(__('auth.failed'));
        $I->dontSeeElement('.fa-sign-out');
    }

    public function inactiveUserLoginRejection(AcceptanceTester $I)
    {
        $this->_register(\App\User::STATUS_INACTIVE);

        $I->amOnPage('/login');

        $I->fillField(['name' => 'login'], self::EMAIL);
        $I->fillField(['name' => 'password'], self::PASSWORD);

        $I->click('#submit');

        $I->wait(1);
        $I->seeElement('input', ['name' => 'login']);
        $I->see(__('auth.inactive'));
        $I->dontSeeElement('.fa-sign-out');
    }

    public function bannedUserLoginRejection(AcceptanceTester $I)
    {
        $this->_register(\App\User::STATUS_BANNED);

        $I->amOnPage('/login');

        $I->fillField(['name' => 'login'], self::EMAIL);
        $I->fillField(['name' => 'password'], self::PASSWORD);

        $I->click('#submit');

        $I->wait(1);
        $I->seeElement('input', ['name' => 'login']);
        $I->see(__('auth.banned'));
        $I->dontSeeElement('.fa-sign-out');
    }

    public function bannedUserSessionExpiration(AcceptanceTester $I)
    {
        $user = $this->_register(\App\User::STATUS_ACTIVE);

        $I->loginAsUser($user);

        $I->amOnPage('/');
        $I->seeElement('.fa-sign-out');

        $user->status = \App\User::STATUS_BANNED;
        $user->save();

        $I->amOnPage('/');
        $I->see(__('flash.warning.session-expired'));
        $I->dontSeeElement('.fa-sign-out');
    }
}