<?php
/**
 * @package    JInbound-Pro
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2018 Open Source Training, LLC. All rights reserved
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of JInbound-Pro.
 *
 * JInbound-Pro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * JInbound-Pro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JInbound-Pro.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

// Joomlashack Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    }
}

if (!defined('JINB_LOADED')) {
    define('JINB_LOADED', 1);

    define('JINB_ADMIN', JPATH_ADMINISTRATOR . '/components/com_jinbound');
    define('JINB_SITE', JPATH_SITE . '/components/com_jinbound');
    define('JINB_HELPERS', JINB_ADMIN . '/helpers');
    define('JINB_LIBRARY', JINB_ADMIN . '/libraries');
    define('JINB_MEDIA', JPATH_SITE . '/media/jinbound');

    // Register all helpers for autoloading
    $helpers = glob(JINB_HELPERS . '/*.php');
    foreach ($helpers as $file) {
        $baseName = basename($file, '.php');
        if ($baseName == 'jinbound') {
            $className = 'JInbound';
        } else {
            $className = 'JInboundHelper' . ucfirst($baseName);
        }
        JLoader::register($className, $file);
    }

    // Register all library classes for autoloading
    $libraryClasses = array(
        'JInboundAdminModel'     => JINB_LIBRARY . '/models/basemodeladmin.php',
        'JInboundBaseController' => JINB_LIBRARY . '/controllers/basecontroller.php',
        'JInboundBasemodel'      => JINB_LIBRARY . '/models/basemodel.php',
        'JInboundBaseView'       => JINB_LIBRARY . '/views/baseview.php',
        'JInboundView'           => JINB_LIBRARY . '/views/baseview.php',
        'JInboundCsvView'        => JINB_LIBRARY . '/views/csvview.php',
        'JInboundFieldView'      => JINB_LIBRARY . '/views/fieldview.php',
        'JInboundFormController' => JINB_LIBRARY . '/controllers/basecontrollerform.php',
        'JInboundInflector'      => JINB_LIBRARY . '/inflector.php',
        'JInboundItemView'       => JINB_LIBRARY . '/views/baseviewitem.php',
        'JInboundJsonListView'   => JINB_LIBRARY . '/views/jsonviewlist.php',
        'JInboundJsonView'       => JINB_LIBRARY . '/views/jsonview.php',
        'JInboundListModel'      => JINB_LIBRARY . '/models/basemodellist.php',
        'JInboundListView'       => JINB_LIBRARY . '/views/baseviewlist.php',
        'JInboundPageController' => JINB_LIBRARY . '/controllers/basecontrollerpage.php',
        'JInboundPluginView'     => JINB_LIBRARY . '/views/pluginview.php',
        'JInboundRssView'        => JINB_LIBRARY . '/views/rssview.php',
        'JInboundTable'          => JINB_LIBRARY . '/table.php'
    );

    foreach ($libraryClasses as $className => $file) {
        JLoader::register($className, $file);
    }

    // Tables and forms used front and back
    JTable::addIncludePath(JINB_ADMIN . '/tables');
    JForm::addFormPath(JINB_ADMIN . '/models/forms');
    JForm::addFieldPath(JINB_ADMIN . '/models/fields');

    switch (JFactory::getApplication()->getName()) {
        case 'administrator':
            // Add standard model paths
            JModelLegacy::addIncludePath(JINB_ADMIN . '/models', 'JInboundModel');

            // Additional helper/core requirements for admin
            JHtml::addIncludePath(JINB_ADMIN . '/helpers/html');
            JFactory::getLanguage()->load('com_categories', JPATH_ADMINISTRATOR);

            break;

        case 'site':
            // Add standard model paths
            JModelLegacy::addIncludePath(JINB_SITE . '/models', 'JInboundModel');

            JFactory::getLanguage()->load('com_jinbound', JINB_ADMIN);
            break;
    }
}
