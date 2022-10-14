<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the Student Application Form is only accessible when public');
$I->loginAsAdmin();

// Change Application Settings ---------------------------------

$I->amOnModulePage('User Admin', 'applicationFormSettings.php');
$originalApplicationSettings = $I->grabAllFormValues();

$I->selectOption('publicApplications', 'N');
$I->submitForm('#content form', [], 'Submit');
$I->see('Your request was completed successfully.', '.success');
$I->seeInFormFields('#content form', ['publicApplications' => 'N']);

// Go To Application  ------------------------------------------

$I->hoverProfilePic();
$I->click('Logout', 'a');
$I->amOnModulePage('Students', 'applicationForm.php');
$I->see('access', '.error');

// Change Application Settings ---------------------------------

$I->loginAsAdmin();
$I->amOnModulePage('User Admin', 'applicationFormSettings.php');

$I->selectOption('publicApplications', 'Y');
$I->submitForm('#content form', [], 'Submit');
$I->see('Your request was completed successfully.', '.success');
$I->seeInFormFields('#content form', $newApplicationSettings);

// Go To Application  ------------------------------------------

$I->hoverProfilePic();
$I->click('Logout', 'a');
$I->amOnModulePage('Students', 'applicationForm.php');
$I->seeBreadcrumb('Application Form');

// Restore Original Settings -----------------------------------

$I->loginAsAdmin();

$I->amOnModulePage('User Admin', 'applicationFormSettings.php');
$I->submitForm('#content form', ['publicApplications' => $originalApplicationSettings['publicApplications']], 'Submit');
$I->see('Your request was completed successfully.', '.success');
$I->seeInFormFields('#content form', $originalApplicationSettings);
