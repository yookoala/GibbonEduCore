<?php

use Facebook\WebDriver\WebDriverBy;

$I = new AcceptanceTester($scenario);
$I->wantTo('login to Gibbon as an admin');
$I->loginAsAdmin();

// Logged In
$I->see('Staff Dashboard', 'h2');
$I->see('ADMIN', 'a');
$I->moveMouseOver(['link' => 'ADMIN']);
$I->see('System Admin', 'a');
