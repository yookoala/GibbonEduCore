<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('update Third Party Settings');
$I->loginAsAdmin();
$I->amOnModulePage('System Admin', 'thirdPartySettings.php');

// Grab Original Settings --------------------------------------

$originalFormValues = $I->grabAllFormValues();
$I->seeInFormFields('#content form', $originalFormValues);

// Make Changes ------------------------------------------------

$newFormValues = array(
    'enablePayments'     => 'Y',
    'paymentGateway'     => 'PayPal',
    'paymentAPIUsername'  => 'paypalUsernameTest',
    'paymentAPIPassword'  => 'paypalPasswordTest',
    'paymentAPISignature' => 'signatureTest',
    'smsGateway'         => 'OneWaySMS',
    'smsSenderID'        => 'smsSenderIDTest',
    'smsUsername'        => 'smsUsernameTest',
    'smsPassword'        => 'smsPasswordTest',
    'smsURL'             => 'http://sms.test',
    'smsURLCredit'       => 'http://credit.test',
    'enableMailerSMTP'   => 'Y',
    'mailerSMTPHost'     => 'http://mail.test',
    'mailerSMTPPort'     => '42',
    'mailerSMTPUsername' => 'smtpUsernameTest',
    'mailerSMTPPassword' => 'smtpPasswordTest',
);

$I->saveFullsizeScreenshot('ThirdPartySettingsCept-before');

// Change payment method.
$I->selectOption('select[name=enablePayments]', 'Y');
$I->selectOption('select[name=paymentGateway]', 'Please select...'); // flip back-and-forth to show the fields.
$I->selectOption('select[name=paymentGateway]', $newFormValues['paymentGateway']);
$I->removeAttribute('input[name=paymentAPIUsername]', 'disabled');
//$I->fillField('input[name=paymentAPIUsername]', $newFormValues['paymentAPIUsername']);
$I->removeAttribute('input[name=paymentAPIPassword]', 'disabled');
//$I->fillField('input[name=paymentAPIPassword]', $newFormValues['paymentAPIPassword']);
$I->removeAttribute('input[name=paymentAPISignature]', 'disabled');
//$I->fillField('input[name=paymentAPISignature]', $newFormValues['paymentAPISignature']);

// Change SMS gateway.
$I->selectOption('select[name=smsGateway]', $newFormValues['smsGateway']);
$I->fillField('input[name=smsSenderID]', $newFormValues['smsSenderID']);
$I->fillField('input[name=smsUsername]', $newFormValues['smsUsername']);
$I->fillField('input[name=smsPassword]', $newFormValues['smsPassword']);
$I->fillField('input[name=smsURL]', $newFormValues['smsURL']);
$I->fillField('input[name=smsURLCredit]', $newFormValues['smsURLCredit']);

// Change SMTP settings.
$I->selectOption('select[name=enableMailerSMTP]', $newFormValues['enableMailerSMTP']);
$I->fillField('input[name=mailerSMTPHost]', $newFormValues['mailerSMTPHost']);
$I->fillField('input[name=mailerSMTPPort]', $newFormValues['mailerSMTPPort']);
$I->fillField('input[name=mailerSMTPUsername]', $newFormValues['mailerSMTPUsername']);
$I->fillField('input[name=mailerSMTPPassword]', $newFormValues['mailerSMTPPassword']);

// Submit form.
$I->submitForm('#content form', $newFormValues, 'Submit');

// Verify Results ----------------------------------------------

$I->see('Your request was completed successfully.', '.success');
$I->seeInFormFields('#content form', $newFormValues);

// Reset ----------------------------------------------

$resetFormValues = $originalFormValues;
$resetFormValues['googleOAuth'] = 'Y';
//$resetFormValues['enablePayments'] = 'Y';
$resetFormValues['enableMailerSMTP'] = 'Y';
$resetFormValues['smsGateway'] = 'OneWaySMS';

unset($resetFormValues['enablePayments']);
unset($resetFormValues['paymentGateway']);
unset($resetFormValues['paymentAPIUsername']);
unset($resetFormValues['paymentAPIPassword']);
unset($resetFormValues['paymentAPISignature']);
unset($resetFormValues['paymentAPIKey']);
unset($resetFormValues['smsGateway']);
unset($resetFormValues['smsSenderID']);
unset($resetFormValues['smsUsername']);
unset($resetFormValues['smsPassword']);
unset($resetFormValues['smsURL']);
unset($resetFormValues['smsURLCredit']);
unset($resetFormValues['enableMailerSMTP']);
unset($resetFormValues['mailerSMTPHost']);
unset($resetFormValues['mailerSMTPPort']);
unset($resetFormValues['mailerSMTPSecure']);
unset($resetFormValues['mailerSMTPUsername']);
unset($resetFormValues['mailerSMTPPassword']);
unset($resetFormValues['googleOAuth']);



$I->resizeWindowToDocument();
$I->submitFormRestore('#content form', $newFormValues, $resetFormValues, 'Submit');

// Restore Original Settings -----------------------------------

$I->submitFormRestore('#content form', $newFormValues, $originalFormValues, 'Submit');
$I->see('Your request was completed successfully.', '.success');
$I->seeInFormFields('#content form', $originalFormValues);
