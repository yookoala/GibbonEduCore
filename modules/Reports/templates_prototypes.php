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

use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\Reports\Domain\ReportPrototypeSectionGateway;
use Gibbon\Module\Reports\Domain\ReportTemplateFontGateway;
use Gibbon\Domain\System\SettingGateway;

if (isActionAccessible($guid, $connection2, '/modules/Reports/templates_prototypes.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs
        ->add(__('Template Builder'), 'templates_manage.php')
        ->add(__('Manage Components'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $prototypeGateway = $container->get(ReportPrototypeSectionGateway::class);
    $fontGateway = $container->get(ReportTemplateFontGateway::class);

    // QUERY
    $criteria = $prototypeGateway->newQueryCriteria(true)
        ->sortBy(['type', 'name'])
        ->fromPOST();

    $templates = $prototypeGateway->queryPrototypes($criteria);
    $fonts = $fontGateway->selectFontList()->fetchKeyPair();
    $customAssetPath = $container->get(SettingGateway::class)->getSettingByScope('Reports', 'customAssetPath');

    // Data TABLE
    $table = DataTable::createPaginated('manageComponents', $criteria);
    $table->setTitle(__('Manage Components'));
    $table->setDescription(__('Place templates in your Custom Asset Path at {path} and scan the directory to update components.', ['path' => '<b><u>'.$customAssetPath.'/templates</u></b>']));

    $table->addHeaderAction('scan', __('Scan Template Directories'))
        ->setIcon('run')
        ->setURL('/modules/Reports/templates_prototypes_scanProcess.php')
        ->directLink(true)
        ->displayLabel();
        
    $table->addColumn('name', __('Name'));
    $table->addColumn('type', __('Type'));
    $table->addColumn('category', __('Category'));
    $table->addColumn('templateFile', __('File'));
    $table->addColumn('status', __('Status'))
        ->format(function ($template) use (&$fonts) {
            $fontsMissing = [];
            $fontsUsed = array_filter(explode(',', $template['fonts']));
            foreach ($fontsUsed as $fontName) {
                if (!isset($fonts[$fontName])) {
                    $fontsMissing[] = $fontName;
                }
            }
            return !empty($fontsMissing)
                ? __('Missing Font').':<br/> '.implode('<br/>', $fontsMissing)
                : '<span class="tag success">'.__('Installed').'</span>';
        });

    $table->addActionColumn()
        ->addParam('gibbonReportPrototypeSectionID')
        ->format(function ($template, $actions) {
            $actions->addAction('view', __('Preview'))
                    ->setURL('/modules/Reports/templates_prototypes_preview.php')
                    ->addParam('TB_iframe', 'true')
                    ->modalWindow(900, 500);

            // $actions->addAction('edit', __('Edit'))
            //         ->addParam('sidebar', 'false')
            //         ->setURL('/modules/Reports/templates_manage_edit.php');
            // $actions->addAction('delete', __('Delete'))
            //         ->setURL('/modules/Reports/templates_manage_delete.php');
        });

    echo $table->render($templates);
}