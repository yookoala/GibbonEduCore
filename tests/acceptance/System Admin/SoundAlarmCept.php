<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('trigger an alarm');
$I->loginAsAdmin();
$I->amOnModulePage('System Admin', 'alarm.php');

// Fill in Fields --------------------------------------

$I->selectOption('select[name=alarm]', 'General');
$I->click('Submit');

$I->see('Your request was completed successfully.', '.success');
$I->seeOptionIsSelected('select[name=alarm]', 'General');

// See the alarm overlay and close it ------------------

$I->seeElement('div#TB_window');
$I->click(['link' => 'close'], 'div#TB_window');

// Turn off alarm --------------------------------------

$I->selectOption('select[name=alarm]', 'None');
$I->click('Submit');

$I->see('Your request was completed successfully.', '.success');
