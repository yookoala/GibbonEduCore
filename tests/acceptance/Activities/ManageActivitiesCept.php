<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete an activity as an admin and test the enrollment and view of a student');
$I->loginAsAdmin();
$I->amOnModulePage('Activities', 'activities_manage.php');

// If see the cookie consent, dismiss it.
if ($I->foundElement('.box-cookie-consent')) {
    $I->click(['link' => 'OK'], '.box-cookie-consent');
}

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add Activity');

$I->fillField('name', 'T1 Test Activity');
$I->submitForm('#content form', [], 'Submit');
$I->seeSuccessMessage();
$ID = $I->grabValueFromURL('editID');

// Edit ------------------------------------------------
$I->amOnModulePage('Activities', 'activities_manage.php');
$I->click("Edit", "//td[contains(text(),'T1 Test Activity')]//..");
$I->seeBreadcrumb('Edit Activity');
$I->fillField('name', 'T2 Test Activity');
$I->submitForm('#content form', [], 'Submit');
$I->seeSuccessMessage();

// Enroll ------------------------------------------------
$I->amOnModulePage('Activities', 'activities_manage.php');
$I->click("Enrolment", "//td[contains(text(),'T2 Test Activity')]//..");
$I->seeBreadcrumb('Activity Enrolment');
$I->clickNavigation('Add');
$I->selectOption('Members[]', 'TestUser, Student (testingstudent)');
$I->submitForm('#content form', [], 'Submig');
$I->seeSuccessMessage();

// View ------------------------------------------------
$I->hoverProfilePic();
$I->click(['link' => 'Logout'], '#header');
$I->loginAsStudent();
$I->amOnModulePage('Activities', 'activities_my.php');
$I->see('T2 Test Activity', 'td');


// Delete ------------------------------------------------
$I->hoverProfilePic();
$I->click(['link' => 'Logout'], '#header');
$I->loginAsAdmin();
$I->amOnModulePage('Activities', 'activities_manage.php');
$I->click("Delete", "//td[contains(text(),'T2 Test Activity')]//..");
$I->click('Yes');
$I->seeSuccessMessage();


