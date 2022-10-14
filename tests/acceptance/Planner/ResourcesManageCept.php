<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('add, edit and delete resources');
$I->loginAsAdmin();
$I->amOnModulePage('Planner', 'resources_manage.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Add Resource');

$addFormValues = array(
    'type'        => 'HTML',
    'name'        => 'HTML Test',
    'description' => 'This is a test.',
);

$I->selectOption('select[name=type]', 'HTML');
$I->setHiddenField('textarea[name=html]', '<p>Testing</p>');
$I->setHiddenField('input[name=tags]', 'TestTag');
$I->selectFromDropdown('category', 2);

$I->submitForm('#content form', $addFormValues, 'Submit');
$I->seeSuccessMessage();

$gibbonResourceID = $I->grabEditIDFromURL();

// Edit ------------------------------------------------
$I->amOnModulePage('Planner', 'resources_manage_edit.php', array('gibbonResourceID' => $gibbonResourceID, 'search' => ' '));
$I->seeBreadcrumb('Edit Resource');

$I->seeInFormFields('#content form', $addFormValues);

$editFormValues = array(
    'name'        => 'HTML Test Too',
    'description' => 'This is also a test.',
);
$I->selectOption('select[name=type]', 'HTML');
$I->setHiddenField('textarea[name=html]', '<p>Testing Also</p>');
$I->setHiddenField('input[name=tags]', 'TestTag');

$I->submitForm('#content form', $editFormValues, 'Submit');
$I->seeSuccessMessage();

$I->seeInFormFields('#content form', $editFormValues);

// Delete ------------------------------------------------
$I->deleteFromDatabase('gibbonResource', ['gibbonResourceID' => $gibbonResourceID]);
