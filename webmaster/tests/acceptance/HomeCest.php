<?php
namespace webmaster\tests\acceptance;

use Yii;
use webmaster\tests\AcceptanceTester;
use yii\helpers\Url;

class HomeCest
{
    public function checkHome(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/index'));
        $I->see('My Company');
        $I->seeLink('About');
        $I->click('About');
        $I->see('This is the About page.');
    }
}
