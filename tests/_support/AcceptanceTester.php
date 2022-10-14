<?php


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
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    protected $breadcrumbEnd = '.trailEnd';

   /**
    * Define custom actions here
    */

    public function login($name, $password)
    {
        $I = $this;

        $I->amOnPage('/');
        $I->submitForm('form[id=loginForm]', [
            'username' => $name,
            'password' => $password
        ]);

        // hover the user profile icon
        $I->hoverProfilePic();

        // see the "Logout" link in the menu
        $I->see('Logout', 'a');
    }

    /**
     * Hover over the header profile picture.
     * Will expand the Logout link.
     *
     * @return $this
     */
    public function hoverProfilePic()
    {
        $this->moveMouseOver('#header > div > div > a');
        return $this;
    }

    public function loginAsAdmin()
    {
        $this->login('testingadmin', '7SSbB9FZN24Q');
    }

    public function loginAsTeacher()
    {
        $this->login('testingteacher', 'm86GVNLH7DbV');
    }

    public function loginAsStudent()
    {
        $this->login('testingstudent', 'WKLm9ELHLJL5');
    }

    public function loginAsParent()
    {
        $this->login('testingparent', 'UVSf5t7epNa7');
    }

    public function loginAsSupport()
    {
        $this->login('testingsupport', '84BNQAQfNyKa');
    }

    public function clickNavigation($text)
    {
        return $this->click(['link' => $text], '.linkTop');
    }

    public function seeBreadcrumb($text)
    {
        return $this->see($text, $this->breadcrumbEnd);
    }

    public function seeSuccessMessage($text = 'Your request was completed successfully.')
    {
        return $this->see($text, '.success');
    }

    public function seeErrorMessage($text = '')
    {
        return $this->see($text, '.error');
    }

    public function seeWarningMessage($text = '')
    {
        return $this->see($text, '.warning');
    }

    public function grabValueFromURL($param)
    {
        return $this->grabFromCurrentUrl('/'.$param.'=([^=&\s]+)/');
    }

    public function grabEditIDFromURL()
    {
        return $this->grabFromCurrentUrl('/editID=(\d+)/');
    }

    public function selectFromDropdown($selector, $n)
    {
        $n = intval($n);

        if ($n < 0) {
            $option = $this->grabTextFrom('#content select[name='.$selector.'] option:nth-last-of-type('.abs($n).')');
        } else {
            $option = $this->grabTextFrom('#content select[name='.$selector.'] option:nth-of-type('.$n.')');
        }

        $this->selectOption('#content #'.$selector, $option);
    }

    public function amOnModulePage($module, $page, $params = null)
    {
        if (mb_stripos($page, '.php') === false) {
            $page .= '.php';
        }

        $url = sprintf('/index.php?q=/modules/%1$s/%2$s', $module, $page);

        if (!empty($params)) {
            $url .= '&'.http_build_query($params);
        }

        return $this->amOnPage($url);
    }

    /**
     * Set a hidden input field value.
     *
     * @param string  $selector  CSS selector to use in document.querySelector call.
     * @param mixed   $value
     *
     * @return void
     */
    public function setHiddenField($selector, $value)
    {
        // Set hidden input value
        $this->executeJS(
            'document.querySelector(arguments[0]).value = arguments[1];',
            [$selector, $value]
        );
    }

    /**
     * Forcefully replace a form input field with a blank new plain input field
     * by Javascript. Mimic intrusions to circumvent Javascript / Browser checks.
     *
     * The value would by default be empty string ''.
     *
     * @param string  $selector  CSS selector for element query.
     * @param string  $value     The value to the new input field. Default: ''.
     *
     * @return void
     */
    public function replaceInputField(string $selector, string $value='')
    {
        // Set hidden input value
        $this->executeJS(<<<JAVASCRIPT
// Create a new input element with the same name as
// the old field for submission.
const oldField = document.querySelector(arguments[0]);
const newField = document.createElement('input');
newField.setAttribute('type', 'text');
newField.setAttribute('name', oldField.getAttribute('name'));
newField.setAttribute('value', arguments[1]);
oldField.replaceWith(newField);
JAVASCRIPT,
            [$selector, $value]
        );
    }

    /**
     * Submit a form to restore the value changed by previous submission.
     *
     * @param string|array      $selector            Selector of the form.
     * @param array             $newFormValues       An array of previous submission.
     * @param array             $originalFormValues  An array of form values before previous submission.
     *                                               This function call is suppose to restore these orginal values.
     * @param string|array|null $button              Selector of the button for submission.
     *
     * @return mixed
     */
    public function submitFormRestore($selector, $newFormValues, $originalFormValues, $button = null)
    {
        $changedFields = array_keys($newFormValues);
        $changedOriginalValues = array_filter($originalFormValues, function ($key) use ($changedFields) {
            return in_array($key, $changedFields);
        }, ARRAY_FILTER_USE_KEY);
        return $this->submitForm($selector, $changedOriginalValues, $button);
    }
}
