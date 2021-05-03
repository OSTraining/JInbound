<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

class JInboundPluginView extends JInboundView
{
    /**
     * @var JForm
     */
    public $filterForm = null;

    /**
     * @var object
     */
    public $data = null;

    /**
     * @var string
     */
    protected $maxmindDBUrl = null;

    /**
     * @var string
     */
    protected $maxmindDB = null;

    /**
     * JInboundPluginView constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $this->maxmindDownloadUrl = empty($config['maxmindDownloadUrl']) ? null : $config['maxmindDownloadUrl'];
        $this->data               = empty($config['data']) ? null : $config['data'];
        $this->maxmindDBUrl       = empty($config['maxmindDBUrl']) ? null : $config['maxmindDBUrl'];
        $this->maxmindDB          = empty($config['maxmindDB']) ? null : $config['maxmindDB'];
    }

    /**
     * @param string $property
     * @param mixed  $default
     *
     * @return JObject|mixed
     */
    public function getState($property = null, $default = null)
    {
        if (!$this->state) {
            $this->state = new JObject();
        }

        if ($property) {
            return $this->state->get($property, $default);
        }

        return $this->state;
    }

    /**
     * @param string $property
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setState($property, $value)
    {
        return $this->getState()->set($property, $value);
    }
}
