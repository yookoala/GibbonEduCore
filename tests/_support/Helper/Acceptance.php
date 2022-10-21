<?php
namespace Helper;

use Facebook\WebDriver\WebDriverBy;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    /**
     * Grab all values from a form (generally for the purposes of restoring later)
     * Itterates over an array of DOMElement objects
     *
     * @param    string  $selector
     * @param    bool    $includeHidden  Include hidden fields. Default: false
     *
     * @return   array
     */
    public function grabAllFormValues($selector = '#content form', bool $includeHidden = false) {
        /** @var \Codeception\Module\WebDriver */
        $module = $this->getModule('WebDriver');
        /** @var \Facebook\WebDriver\Remote\RemoteWebElement[] */
        $elements = $module->_findElements("$selector input, $selector textarea, $selector select");

        $typeCounts = array();
        $formValues = array();
        foreach ($elements as $element) {
            $type = ($element->getTagName() == 'input')? $element->getAttribute('type') : $element->getTagName();

            if ($type == 'submit' || $type == 'button' || $type == 'file') continue;

            $name = $element->getAttribute('name');
            $value = $element->getAttribute('value') ?? '';

            if (empty($name) || $name == 'address') continue;
            if ($element->getAttribute('readonly') === true) continue;

            switch($type) {
                case 'checkbox':    if ($element->getAttribute('checked')) {
                                        $value = $element->getAttribute('value') ?? 'on';
                                    }
                                    $formValues[$name] = $value;
                                    break;

                case 'radio':       if ($element->getAttribute('checked')) {
                                        $value = $element->getAttribute('value') ?? '';
                                        $formValues[$name] = $value;
                                    }
                                    break;

                case 'textarea':    $value = $element->getText();
                                    $formValues[$name] = $value;
                                    break;

                case 'select':      $optionTags = $element->findElements(WebDriverBy::tagName('option'));
                                    $length = sizeof($optionTags);
                                    for ($i = 0; $i < $length; $i++ ) {
                                        if ($optionTags[$i]->getAttribute('selected') === "selected") {
                                            $value = $optionTags[$i]->getAttribute('value');
                                        }
                                    }
                                    $formValues[$name] = $value;
                                    break;

                case 'hidden':
                                    if ($includeHidden) {
                                        $formValues[$name] = $value;
                                    }
                default:            $formValues[$name] = $value;
                                    break;
            }
        }

        return $formValues;
    }

    public function seeFieldIsNotEmpty($selector)
    {
        /** @var \Codeception\Module\WebDriver */
        $module = $this->getModule('WebDriver');
        $value = $module->grabValueFrom($selector);
        $this->assertFalse(empty($value));
    }

    /**
     * Save a screenshot of the current browser state.
     *
     * @param string $filename
     * @return void
     */
    public function saveScreenshot(string $filename)
    {
        /** @var \Codeception\Module\WebDriver */
        $module = $this->getModule('WebDriver');
        $module->_saveScreenshot(codecept_output_dir().'/'.$filename);
    }
}
