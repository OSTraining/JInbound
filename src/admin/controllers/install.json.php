<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of jInbound.
 *
 * jInbound is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * jInbound is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with jInbound.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('JPATH_PLATFORM') or die;

class JInboundControllerInstall extends JInboundBaseController
{
    public function status()
    {
        $extensions = array();

        // read the package manifest and get the related packages
        $manifestXml = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jinbound.xml';
        if (!JFile::exists($manifestXml)) {
            $this->json(array('error' => JText::_('COM_JINBOUND_INSTALL_CANNOT_FIND_PACKAGE_MANIFEST')));
        }
        if (class_exists('JPackageManifest')) {
            $manifest = new JPackageManifest();
        } else {
            if (class_exists('JInstallerManifestPackage')) {
                $manifest = new JInstallerManifestPackage();
            } else {
                $this->json(array('error' => JText::_('COM_JINBOUND_INSTALL_CANNOT_FIND_MANIFEST_CLASS')));
            }
        }
        $manifest->loadManifestFromXML($manifestXml);
        if (empty($manifest->filelist)) {
            $this->json(array('error' => JText::_('COM_JINBOUND_INSTALL_CANNOT_FIND_FILELIST_IN_PACKAGE_MANIFEST')));
        }
        // check each package and add to return data
        $db    = JFactory::getDbo();
        $where = array();

        foreach ($manifest->filelist as $file) {
            // build WHERE clauses to load all the data in one query
            $where[] = '(a.element LIKE ' . $db->quote($file->id)
                . ' AND a.type LIKE ' . $db->quote($file->type)
                . ('plugin' == $file->type ? ' AND a.folder = ' . $db->quote($file->group) : '')
                . ('component' == $file->type || 'template' == $file->type ? '' : ' AND a.client_id = ' . ('site' == $file->client ? '0' : '1'))
                . ')';
        }

        $query = $db->getQuery(true)
            ->select('a.extension_id,a.element,a.name,a.type,a.folder,a.client_id,a.enabled,a.manifest_cache')
            ->from('#__extensions AS a')
            ->where(implode(' OR ', $where))
            ->order('a.type ASC');

        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
            if (empty($extensions)) {
                throw new Exception(JText::_('COM_JINBOUND_INSTALL_ERROR_NO_EXTENSIONS_FOUND'));
            }
        } catch (Exception $e) {
            $this->json(array('error' => $e->getMessage()));
        }

        // we need to report all extensions found, including those not in the database
        $found = array();

        // loop the listed files and compare to the database data
        foreach ($manifest->filelist as $file) {
            $ext = array(
                'name'         => $file->id
            ,
                'id'           => $file->id
            ,
                'type'         => $file->type
            ,
                'folder'       => $file->group
            ,
                'installed'    => false
            ,
                'version'      => ''
            ,
                'extension_id' => false
            );
            // find this entry and add relevant data
            foreach ($extensions as $extension) {
                if ($extension->element != $file->id || $extension->type != $file->type || ('plugin' == $extension->type && ($extension->folder != $file->group))) {
                    continue;
                }
                $manifest_cache      = json_decode($extension->manifest_cache);
                $ext['installed']    = true;
                $ext['name']         = $extension->name;
                $ext['version']      = $manifest_cache->version;
                $ext['extension_id'] = $extension->extension_id;
                break;
            }
            $found[] = json_decode(json_encode($ext));
        }

        $view = new JInboundBaseView();
        $view->setLayout('install');
        $view->extensions = $found;
        $view->messages   = array();

        if (JInboundHelperForm::needsMigration()) {
            $view->messages[] = JInboundHelperForm::getMigrationWarning();
        }

        $this->json(array('extensions' => $found, 'html' => $view->loadTemplate()));
    }

    private function json($return)
    {
        $view       = new JInboundJsonView();
        $view->data = $return;
        $view->display();
    }
}
