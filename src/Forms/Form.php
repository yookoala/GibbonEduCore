<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gibbon\Forms;

use Gibbon\Tables\Action;
use Gibbon\Forms\View\FormTableView;
use Gibbon\Forms\FormFactoryInterface;
use Gibbon\Forms\Layout\Row;
use Gibbon\Forms\Layout\Trigger;
use Gibbon\Forms\View\FormRendererInterface;
use Gibbon\Forms\Traits\BasicAttributesTrait;

/**
 * Form
 *
 * @version v23
 * @since   v14
 */
class Form implements OutputableInterface
{
    use BasicAttributesTrait;

    /**
     * Form title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Form description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Form factory associated to the form.
     *
     * @var \Gibbon\Forms\FormFactoryInterface
     */
    protected $factory;

    /**
     * Form renderer.
     *
     * @var \Gibbon\Forms\View\FormRendererInterface
     */
    protected $renderer;

    /**
     * Form rows to render.
     *
     * @var \Gibbon\Forms\Layout\Row[]
     */
    protected $rows = [];

    /**
     * Javascript triggers to render.
     *
     * @var \Gibbon\Forms\Layout\Trigger[]
     */
    protected $triggers = [];

    /**
     * Internal storage of the hidden values.
     *
     * @var array An array of key-value pairs of hidden values.
     *            Each array item (an assoc array itself) has these keys
     *             - name:  String name of hidden value.
     *             - value: String value of the name.
     */
    protected $values = [];

    /**
     * An array of header actions, generally displayed in the header right-hand side.
     *
     * @var \Gibbon\Tables\Action[]
     */
    protected $header = [];

    /**
     * An array of multiple steps information.
     *
     * @var array
     */
    protected $steps = [];

    /**
     * Current step in a multi-part form. Null if not a mult-part form.
     *
     * @var int|null
     */
    protected $step = null;

    /**
     * Create a form with a specific factory and renderer.
     * @param    FormFactoryInterface   $factory
     * @param    FormRendererInterface  $renderer
     * @param    string                 $action
     * @param    string                 $method
     */
    public function __construct(FormFactoryInterface $factory, FormRendererInterface $renderer, $action = '', $method = 'post')
    {
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->setAttribute('action', ltrim($action, '/'));
        $this->setAttribute('method', $method);
        $this->setAttribute('autocomplete', 'on');
        $this->setAttribute('enctype', 'multipart/form-data');
    }

    /**
     * Create a form with the default factory and renderer.
     * @param    string  $id
     * @param    string  $action
     * @param    string  $method
     * @param    string  $class
     *
     * @return   \Gibbon\Forms  Form object
     */
    public static function create($id, $action, $method = 'post', $class = 'smallIntBorder fullWidth standardForm'): Form
    {
        global $container;

        $form = $container->get(Form::class)
            ->setID($id)
            ->setClass($class)
            ->setAction($action)
            ->setMethod($method);

        return $form;
    }

    /**
     * Create table of the given HTML id, action
     *
     * @param string $id      HTML id.
     * @param string $action  String representation of action.
     * @param string $method  HTTP method for form submission (e.g. POST, GET).
     * @param string $class   Space separated string of all the class name for the form.
     *
     * @return \Gibbon\Forms\Form
     */
    public static function createTable($id, $action, $method = 'post', $class = 'smallIntBorder fullWidth'): Form
    {
        global $container;

        $form = static::create($id, $action, $method, $class);
        $form->setRenderer($container->get(FormTableView::class));

        return $form;
    }

    /**
     * Get the form title.
     *
     * @return  string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the form title.
     *
     * @param  string  $title
     *
     * @return self
     */
    public function setTitle(string $title): Form
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the form description.
     *
     * @return  string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the form description.
     *
     * @param  string  $description
     *
     * @return self
     */
    public function setDescription($description): Form
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the current factory.
     *
     * @return  \Gibbon\Forms\FormFactoryInterface The factory that creates this form.
     */
    public function getFactory(): FormFactoryInterface
    {
        return $this->factory;
    }

    /**
     * Set the factory.
     *
     * @param  \Gibbon\Forms\FormFactoryInterface  $factory
     *
     * @return self
     */
    public function setFactory(FormFactoryInterface $factory): Form
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Get the current renderer.
     *
     * @return  \Gibbon\Forms\View\FormRendererInterface The current form renderer.
     */
    public function getRenderer(): FormRendererInterface
    {
        return $this->renderer;
    }

    /**
     * Set the renderer.
     *
     * @param  \Gibbon\Forms\View\FormRendererInterface  $renderer
     *
     * @return self
     */
    public function setRenderer(FormRendererInterface $renderer): Form
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get the current HTTP method for this form (default: post)
     * @return  string
     */
    public function getMethod(): string
    {
        return $this->getAttribute('method');
    }

    /**
     * Set the HTTP method for this form.
     *
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): Form
    {
        $this->setAttribute('method', $method);

        return $this;
    }

    /**
     * Get the current action URL for the form.
     *
     * @return  string
     */
    public function getAction(): string
    {
        return $this->getAttribute('action');
    }

    /**
     * Set the form action URL.
     *
     * @param string $action
     *
     * @return self
     */
    public function setAction(string $action): Form
    {
        $this->setAttribute('action', $action);

        return $this;
    }

    /**
     * Adds a Row object to the form and returns it.
     *
     * @param  string  $id
     *
     * @return  \Gibbon\Forms\Layout\Row
     */
    public function addRow($id = ''): Row
    {
        $section = !empty($this->rows) ? end($this->rows)->getHeading() : '';
        $row = $this->factory->createRow($id)->setHeading($section);
        $this->rows[] = $row;
        return $row;
    }

    /**
     * Get the last added Row object, null if none exist.
     *
     * @return  \Gibbon\Forms\Layout\Row|null
     */
    public function getRow(): ?Row
    {
        return (!empty($this->rows))? end($this->rows) : null;
    }

    /**
     * Get an array of all Row objects in the form.
     *
     * @return  \Gibbon\Forms\Layout\Row[]
     */
    public function getRows(): array
    {
        return array_filter($this->rows, function ($item) {
            return !empty($item->getElements());
        });
    }

    /**
     * Get rows grouped by the rows' heading.
     *
     * @return array
     */
    public function getRowsByHeading(): array
    {
        return array_reduce($this->rows, function ($group, $row) {
            $group[$row->getHeading()][] = $row;
            return $group;
        }, []);
    }

    /**
     * Get the count of rows which has the given heading.
     *
     * @param string $heading
     *
     * @return int
     */
    public function hasHeading(string $heading): int
    {
        return count(array_filter($this->rows, function ($row) use ($heading) {
            return $row->getHeading() == $heading;
        }));
    }

    /**
     * Adds an input type=hidden value to the form.
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return self
     */
    public function addHiddenValue(string $name, string $value): Form
    {
        $this->values[] = array('name' => $name, 'value' => $value);

        return $this;
    }

    /**
     * Adds a key => value array of input type=hidden values.
     *
     * @param  array  $array
     *
     * @return self
     */
    public function addHiddenValues(array $array): Form
    {
        foreach ($array as $name => $value) {
            $this->addHiddenValue($name, $value);
        }

        return $this;
    }

    /**
     * Get an array of all hidden values.
     *
     * @return  array
     */
    public function getHiddenValues(): array
    {
        return $this->values;
    }

    /**
     * Get the value of the autocomplete HTML form attribute.
     *
     * @return  string
     */
    public function getAutocomplete(): string
    {
        return $this->getAttribute('autocomplete');
    }

    /**
     * Turn autocomplete for the form On or Off.
     *
     * @param  string  $value
     *
     * @return self
     */
    public function setAutocomplete($value): Form
    {
        if (is_bool($value)) {
            $value = $value? 'on' : 'off';
        }

        $this->setAttribute('autocomplete', $value);

        return $this;
    }

    /**
     * Add a confirmation message to display before form submission.
     *
     * @param string $message
     *
     * @return self
     */
    public function addConfirmation($message): Form
    {
        $this->setAttribute('onsubmit', "return confirm(\"".__($message)."\")");

        return $this;
    }

    /**
     * Adds a Trigger object that injects javascript to respond to form events.
     *
     * @param  string                        $selector
     * @param  \Gibbon\Forms\Layout\Trigger  $trigger
     *
     * @return \Gibbon\Forms\Layout\Trigger
     */
    public function addTrigger(string $selector, Trigger $trigger): Trigger
    {
        $this->triggers[$selector] = $trigger;

        return $trigger;
    }

    /**
     * Get an array of all Trigger objects.
     *
     * @return  \Gibbon\Forms\Layout\Trigger[]
     */
    public function getTriggers(): array
    {
        return $this->triggers;
    }

    /**
     * Adds a visibility trigger to the form by class name.
     *
     * @param   string  $class Element name
     *
     * @return  \Gibbon\Forms\Layout\Trigger
     */
    public function toggleVisibilityByClass($class): Trigger
    {
        $selector = '.'.$class;

        return $this->addTrigger($selector, $this->factory->createTrigger($selector));
    }

    /**
     * Adds a visibility trigger to the form by element ID.
     *
     * @param   string  $id CSS Element ID
     *
     * @return  \Gibbon\Forms\Layout\Trigger Trigger
     */
    public function toggleVisibilityByID($id)
    {
        $selector = '#'.$id;

        return $this->addTrigger($selector, $this->factory->createTrigger($selector));
    }

    /**
     * Enables displaying a multi-part form progress indicator.
     *
     * @param array $steps
     * @param int $currentStep
     *
     * @return self
     */
    public function setMultiPartForm(array $steps, int $currentStep = 1): Form
    {
        $this->steps = $steps;
        $this->step = $currentStep;

        return $this;
    }

    /**
     * Get steps in a multipart form.
     *
     * @return array
     */
    public function getMultiPartSteps(): array
    {
        return $this->steps;
    }

    /**
     * Get current step number in a multipart form.
     * If this is not a multi-part form, returns null.
     *
     * @return int|null Step
     */
    public function getCurrentStep(): int
    {
        return $this->step;
    }

    /**
     * Loads an array of $key => $value pairs into any form elements with a matching name.
     * @param   array  &$data
     * @return  self
     */
    public function loadAllValuesFrom(&$data): Form
    {
        foreach ($this->getRows() as $row) {
            $row->loadFrom($data);
        }

        return $this;
    }

    /**
     * Loads the state for several form elements by calling $method with values from $data.
     *
     * @param string $method
     * @param array $data
     *
     * @return self
     */
    public function loadStateFrom($method, $data): Form
    {
        foreach ($this->getRows() as $row) {
            $row->loadState($method, $data);
        }

        return $this;
    }

    /**
     * Add an action to the form, generally displayed in the header right-hand side.
     *
     * @param string $name
     * @param string $label
     *
     * @return \Gibbon\Tables\Action
     */
    public function addHeaderAction($name, $label = '')
    {
        $this->header[$name] = new Action($name, $label);

        return $this->header[$name];
    }

    /**
     * Get all header content in the table.
     *
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * Renders the form to HTML.
     *
     * @return  string
     */
    public function getOutput(): string
    {
        return $this->renderer->renderForm($this);
    }
}
