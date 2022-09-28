<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

namespace Gibbon\Forms;

use PHPUnit\Framework\TestCase;
use Gibbon\Forms\View\FormRendererInterface;
use Gibbon\Forms\FormFactoryInterface;
use Gibbon\Forms\Layout\Row;
use Gibbon\Forms\Layout\Trigger;
use Psr\Container\ContainerInterface;

/**
 * @covers Form
 */
class FormTest extends TestCase
{
    public function testCanBeCreatedStatically()
    {
        global $container;

        // Mock a form object for the container to initialize
        // with all the method call expectations.
        $mockForm = $this->createMock(Form::class);
        $mockForm->expects($this->once())
            ->method('setID')
            ->with($this->equalTo('testID'))
            ->willReturn($mockForm);
        $mockForm->expects($this->once())
            ->method('setClass')
            ->with($this->equalTo('non-standard-class'))
            ->willReturn($mockForm);
        $mockForm->expects($this->once())
            ->method('setAction')
            ->with($this->equalTo('testAction'))
            ->willReturn($mockForm);
        $mockForm->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo('my-method'))
            ->willReturn($mockForm);

        // Create a stub of container.
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturn($mockForm);

        // Create a form for test
        $form = Form::create('testID', 'testAction', 'my-method', 'non-standard-class');

        $this->assertInstanceOf(
            Form::class,
            $form
        );
    }

    public function testCanOutputToHtml()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer, 'testID', 'testAction');

        $renderer->expects($this->once())
            ->method('renderForm')
            ->with($this->equalTo($form))
            ->willReturn('my-output');

        // Check if getOutput calls renderForm with the form itself.
        // Check if getOutput returns the result of renderForm.
        $this->assertEquals(
            'my-output',
            $form->getOutput()
        );
    }

    public function testCanAddRow()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer, 'testID', 'testAction');
        $rowToReturn = new Row($factory, 'rowID');

        $factory->expects($this->once())
            ->method('createRow')
            ->with($this->equalTo('rowID'))
            ->willReturn($rowToReturn);

        $this->assertTrue(count($form->getRows()) == 0);

        // Note: only rows with content will be returned by getRows
        $row = $form->addRow('rowID');
        $row->addContent('Test');
        $this->assertSame(
            $rowToReturn,
            $row
        );
        $this->assertEquals(1, count($form->getRows()));
        $this->assertSame($row, $form->getRow());
    }

    public function testCanAddHiddenValue()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer, 'testID', 'testAction');

        $this->assertTrue(count($form->getHiddenValues()) == 0);
        $form->addHiddenValue('name', 'value');

        $this->assertTrue(count($form->getHiddenValues()) > 0);
    }

    public function testCanAddTrigger()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer, 'testID', 'testAction');
        $trigger = new Trigger('some-selector');

        $this->assertTrue(count($form->getTriggers()) == 0);
        $result = $form->addTrigger('other-selector', $trigger);
        $this->assertSame(
            $trigger,
            $result
        );

        $this->assertTrue(count($form->getTriggers()) > 0);
    }

    public function testCanSetFactory()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer, 'testID', 'testAction');

        $newFactory = $this->createMock(FormFactoryInterface::class);
        $form->setFactory($newFactory);

        $this->assertSame($newFactory, $form->getFactory());
    }

    public function testCanSetRenderer()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer, 'testID', 'testAction');

        $newRenderer = $this->createMock(FormRendererInterface::class);
        $form->setRenderer($newRenderer);

        $this->assertSame($newRenderer, $form->getRenderer());
    }

    public function testEachNewFormHasBasicAttributes()
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = new Form($factory, $renderer);
        $this->assertSame('', $form->getAction());
        $this->assertSame('post', $form->getMethod());
    }
}
