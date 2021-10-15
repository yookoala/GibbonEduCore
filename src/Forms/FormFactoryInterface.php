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
use Gibbon\Forms\Input\Select;
use Gibbon\Forms\Layout\Column;
use Gibbon\Forms\Layout\Element;
use Gibbon\Forms\Layout\Row;
use Gibbon\Forms\Layout\Table;
use Gibbon\Forms\Layout\Trigger;
use Gibbon\Tables\DataTable;

/**
 * FormFactoryInterface
 *
 * Interface for handling form object creationm including pre-defined elements.
 * Replaceable component. Default factory can be extended to add types.
 *
 * @version v23
 * @since   v14
 * @see \Gibbon\Forms\Layout\Row::__call()
 */
interface FormFactoryInterface
{
    /**
     * Create form row.
     *
     * @param string $id  HTML id of the row.
     * @return Row
     */
    public function createRow(string $id = ''): Row;

    /**
     * Create flex column.
     *
     * @param string $id  HTML id of the column.
     * @return Column
     */
    public function createColumn(string $id = ''): Column;

    /**
     * Create table element with the given id attribute.
     *
     * @param string $id  HTML id of the element.
     * @return Table
     */
    public function createTable(string $id = ''): Table;

    /**
     * Create a DataTable element.
     *
     * Create a DataTable element with the given id attribute.
     * Accepts an optional QueryCriteria to refine query.
     *
     * @param string             $id        HTML id of the element.
     * @param QueryCriteria|null $criteria  Optional criteria to refine database search.
     * @return DataTable
     */
    public function createDataTable(string $id, ?QueryCriteria $criteria = null): DataTable;

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
     * Create select element from the given name.
     *
     * @param string $name
     *
     * @return \Gibbon\Forms\Input\Select
     */
    public function createSelect(string $name): Select;
}
