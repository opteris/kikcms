<?php

use Codeception\Actor;
use KikCMS\Models\User;
use KikCmsCore\Services\DbService;
use Phalcon\Cache\Backend;
use Website\Models\TestPerson;


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
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;

    const TEST_USERNAME = 'test@test.com';

    public function login(string $username = self::TEST_USERNAME, $password = 'TestUserPass', bool $addUser = true)
    {
        $I = $this;

        if($addUser){
            $this->addUser();
        }

        $I->amOnPage('/cms');

        $I->submitForm('#login-form form', [
            'username' => $username,
            'password' => $password,
            'remember' => null,
        ]);
    }

    /**
     * @param $name
     * @return object
     */
    public function getService($name): object
    {
        return $this->getApplication()->di->get($name);
    }

    /**
     * @return Backend
     */
    public function getCache(): Backend
    {
        return $this->getApplication()->di->get('cache');
    }

    /**
     * @return DbService
     */
    public function getDbService(): DbService
    {
        return $this->getApplication()->di->get('dbService');
    }

    public function addUser()
    {
        $this->getDbService()->truncate(User::class);

        $this->getDbService()->insert(User::class, [
            User::FIELD_PASSWORD => '$2y$10$I1eyBL8OVtc8QP6YaiMC5uAkUyH7LMJmUlrzUTOC5vvX/kXJrk1.y',
            User::FIELD_EMAIL    => self::TEST_USERNAME,
            User::FIELD_ROLE     => 'developer',
            User::FIELD_ID       => 1,
        ]);
    }

    public function cleanDb()
    {
        $this->getDbService()->truncate(User::class);
        $this->getDbService()->truncate(TestPerson::class);
    }
}
