<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('login to Gibbon as support staff');
$I->loginAsSupport();

// Logged In
$I->hoverProfilePic();
$I->see('Logout', 'a');
