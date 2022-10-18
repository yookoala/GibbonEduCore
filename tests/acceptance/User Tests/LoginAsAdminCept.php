<?php

use Facebook\WebDriver\WebDriverBy;

$I = new AcceptanceTester($scenario);
$I->wantTo('login to Gibbon as an admin');
$I->loginAsAdmin();

// Logged In
$I->see('Staff Dashboard', 'h2');
$I->see('#header-menu');
$I->moveMouseOver('#header-menu li:nth-child(2) a');
$I->see('System Admin', 'a');
