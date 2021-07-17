<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

namespace Gibbon\Forms;

use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Gibbon\Forms\View\FormRendererInterface;
use Gibbon\Forms\FormFactoryInterface;

/**
 * @covers Form
 */
class FormTest extends TestCase
{
    protected $isMockingContainer = false;

    protected function setUp(): void
    {
        global $container;
        if (!isset($container)) {
            $this->isMockingContainer = true; // remember to tearDown
            $container = new Container();

            // add dependencies for Gibbon\Forms\Form to mock container
            $container->add(FormFactory::class);
            $container->add(FormRenderer::class);

            // register Gibbon\Forms\Form
            $container->add(Form::class)
                ->addArgument(FormFactory::class)
                ->addArgument(FormRenderer::class);
        }
    }

    protected function tearDown(): void
    {
        global $container;

        // remove the locally created mock container from
        // global namespace.
        if (isset($container) && $this->isMockingContainer) {
            unset($container);
        }
    }

    public function testCanBeCreatedStatically()
    {
        $this->assertInstanceOf(
            Form::class,
            Form::create('testID', 'testAction')
        );
    }

    public function testGetOutputUsesRendererToRenderForm()
    {
        $form = Form::create('testID', 'testAction');
        $mockFormRenderering = 'renderer rendered form ' . rand(0, 100);
        $mockRenderer = $this->createMock(FormRenderer::class);
        $mockRenderer->method('renderForm')->willReturn($mockFormRenderering);
        $form->setRenderer($mockRenderer);
        $this->assertSame($mockFormRenderering, $form->getOutput());
    }

    public function testCanAddRow()
    {
        $form = Form::create('testID', 'testAction');

        $this->assertTrue(count($form->getRows()) == 0);
        $row = $form->addRow();
        $row->addContent('Test');

        $this->assertTrue(count($form->getRows()) > 0);
        $this->assertSame($row, $form->getRow());
    }

    public function testCanAddHiddenValue()
    {
        $form = Form::create('testID', 'testAction');

        $this->assertTrue(count($form->getHiddenValues()) == 0);
        $form->addHiddenValue('name', 'value');

        $this->assertTrue(count($form->getHiddenValues()) > 0);
    }

    public function testCanAddTrigger()
    {
        $form = Form::create('testID', 'testAction');

        $this->assertTrue(count($form->getTriggers()) == 0);
        $form->addTrigger('selector', 'trigger');

        $this->assertTrue(count($form->getTriggers()) > 0);
    }

    public function testCanSetFactory()
    {
        $form = Form::create('testID', 'testAction');

        $newFactory = FormFactory::create();
        $form->setFactory($newFactory);

        $this->assertSame($newFactory, $form->getFactory());
    }

    public function testCanSetRenderer()
    {
        $form = Form::create('testID', 'testAction');

        $newRenderer = FormRenderer::create();
        $form->setRenderer($newRenderer);

        $this->assertSame($newRenderer, $form->getRenderer());
    }

    public function testEachNewFormHasAFactory()
    {
        $form = Form::create('testID', 'testAction');

        $this->assertInstanceOf(
            FormFactoryInterface::class,
            $form->getFactory()
        );
    }

    public function testEachNewFormHasARenderer()
    {
        $form = Form::create('testID', 'testAction');

        $this->assertInstanceOf(
            FormRendererInterface::class,
            $form->getRenderer()
        );
    }

    public function testEachNewFormHasBasicAttributes()
    {
        $form = Form::create('testID', 'testAction');

        $this->assertSame('testID', $form->getID());
        $this->assertSame('testAction', $form->getAction());
        $this->assertSame('post', $form->getMethod());
    }
}
