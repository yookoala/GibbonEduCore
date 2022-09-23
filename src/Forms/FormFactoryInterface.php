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

use Gibbon\Domain\QueryCriteria;
use Gibbon\Forms\Input\Button;
use Gibbon\Forms\Input\Checkbox;
use Gibbon\Forms\Input\Currency;
use Gibbon\Forms\Input\Editor;
use Gibbon\Forms\Input\FileUpload;
use Gibbon\Forms\Input\Number;
use Gibbon\Forms\Input\Password;
use Gibbon\Forms\Input\Select;
use Gibbon\Forms\Input\TextField;
use Gibbon\Forms\Layout\Column;
use Gibbon\Forms\Layout\Details;
use Gibbon\Forms\Layout\Element;
use Gibbon\Forms\Layout\Grid;
use Gibbon\Forms\Layout\Heading;
use Gibbon\Forms\Layout\Label;
use Gibbon\Forms\Layout\Row;
use Gibbon\Forms\Layout\Table;
use Gibbon\Forms\Layout\TableCell;
use Gibbon\Forms\Layout\Trigger;
use Gibbon\Forms\Layout\WebLink;
use Gibbon\Tables\DataTable;

interface FormFactoryInterface
{
    /********************************************************
     * Layout elements related methods.
     ********************************************************/

    /**
     * Create flex column.
     *
     * @param string $id  HTML id of the column.
     * @return \Gibbon\Forms\Layout\Column
     */
    public function createColumn(string $id = ''): Column;

    /**
     * Create form row.
     *
     * @param string $id  HTML id of the row.
     * @return \Gibbon\Forms\Layout\Row
     */
    public function createRow(string $id = ''): Row;

    /**
     * Create a table.
     *
     * @param string $id  HTML id of the table element.
     * @return Table
     */
    public function createTable(string $id = ''): Table;

    /**
     * Create a data table.
     *
     * @param string              $id        HTML id of the table element.
     * @param QueryCriteria|null  $criteria  The query criteria. Default: null.
     * @return Table
     */
    public function createDataTable(string $id, ?QueryCriteria $criteria): DataTable;

    /**
     * Create a table cell.
     *
     * @param string $content  Content of the table cell.
     *
     * @return TableCell
     */
    public function createTableCell(string $content = ''): TableCell;

    /**
     * Create a grid layout element.
     *
     * @param string $id       HTML id of the element.
     * @param int    $columns  Number of columns of the grid. Default: 1.
     *
     * @return Grid
     */
    public function createGrid(string $id = '', int $columns = 1): Grid;

    /**
     * Create a details element.
     *
     * @param string $id  HTML id of the element.
     *
     * @return Details
     */
    public function createDetails(string $id = ''): Details;

    /**
     * Create javascript trigger for certain event of DOM object
     * specified by the selector.
     *
     * @param string $selector
     *
     * @return \Gibbon\Forms\Layout\Trigger
     */
    public function createTrigger(string $selector = ''): Trigger;

    /**
     * Create output element from given content.
     *
     * @param string $content
     *
     * @return \Gibbon\Forms\Layout\Element
     */
    public function createContent(string $content = ''): Element;

    /**
     * Create a weblink element.
     *
     * @param string $content  Content of the table cell.
     *
     * @return WebLink
     */
    public function createWebLink(string $content = ''): WebLink;

    /**
     * Create label element.
     *
     * @param string $for    The for attribute. Supposed to be an HTML element id.
     * @param string $label  The label string.
     *
     * @return Label
     */
    public function createLabel(string $for, string $label): Label;

    /**
     * Create heading element.
     *
     * @param string       $id       The HTML id of the heading element.
     * @param string|null  $content  The heading content. Default: null.
     * @param string|null  $tag      The tag name of the element. Default: null.
     *
     * @return Heading
     */
    public function createHeading(string $id = '', ?string $content = null, ?string $tag = null): Heading;

    /**
     * Create subheading element.
     *
     * @param string       $id       The HTML id of the heading element.
     * @param string|null  $content  The heading content. Default: null.
     * @param string       $tag      The tag name of the element. Default: "h4".
     *
     * @return Heading
     */
    public function createSubHeading(string $id = '', ?string $content = null, ?string $tag = 'h4'): Heading;


    /********************************************************
     * Input elements related methods.
     ********************************************************/

    /**
     * Create an HTML button element.
     *
     * @param string       $label    Text displayed in the button. Default: 'Button'.
     * @param string       $onClick  The onClick attribute of the element.
     * @param string|null  $id       The id attribute of the element. Default: null.
     *
     * @return \Gibbon\Forms\Input\Button
     */
    public function createButton(string $label = 'Button', string $onClick = '', ?string $id = null): Button;

    /**
     * Create a checkbox input element.
     *
     * @param string $name  The name attribute of the element.
     *
     * @return Checkbox
     */
    public function createCheckbox(string $name): Checkbox;

    /**
     * Create a currency selection element.
     *
     * @param string $name  The name attribute of the element.
     * @return Currency
     */
    public function createCurrency(string $name): Currency;

    /**
     * Create WYISIWYG editor element.
     *
     * @param string $name
     *
     * @return Editor
     */
    public function createEditor(string $name): Editor;

    /**
     * Create select element from the given name.
     *
     * @param string $name
     *
     * @return FileUpload
     */
    public function createFileUpload(string $name): FileUpload;

    /**
     * Create a number input element.
     *
     * @param string $name  The name attribute of the element.
     * @return Number
     */
    public function createNumber(string $name): Number;

    /**
     * Create a password input element.
     *
     * @param string $name  The name attribute of the element.
     * @return Password
     */
    public function createPassword(string $name): Password;

    /**
     * Create select element from the given name.
     *
     * @param string $name
     *
     * @return Select
     */
    public function createSelect(string $name): Select;

    /**
     * Create a textfield input element.
     *
     * @param string $name  The name attribute of the element.
     *
     * @return TextField
     */
    public function createTextField(string $name): TextField;

    /**
     * Create a input element for URL web link input.
     *
     * @param string $name  The name attribute of the element.
     *
     * @return TextField
     */
    public function createURL(string $name): TextField;
}
