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

class JInboundRSSView extends JInboundBaseView
{
    public $url;
    public $feed;

    public $showTitle       = true;
    public $showDescription = true;
    public $showDetails     = true;
    public $feedLimit       = 5;
    public $wordLimit       = 140;

    public function display($tpl = null, $safeparams = false)
    {
        $this->feed = $this->getFeed($this->url);
        return parent::display($tpl, $safeparams);
    }

    /**
     * Method to load the feed html
     *
     */
    public function getFeed($url, $cacheTime = 900)
    {
        if (empty($url)) {
            return false;
        }
        //  get RSS parsed object
        $options  = array('rssUrl' => $url);
        $cacheDir = JPATH_BASE . '/cache';
        if (is_writable($cacheDir)) {
            $options['cache_time'] = $cacheTime;
        }

        jimport('joomla.feed.factory');
        if (class_exists('JFeedFactory')) {
            $feed   = new JFeedFactory;
            $rssDoc = $feed->getFeed($options['rssUrl']);
        } else {
            if (method_exists('JFactory', 'getXMLParser')) {
                $rssDoc = JFactory::getXMLParser('RSS', $options);
            } else {
                $ct     = array_key_exists('cache_time', $options) ? $options['cache_time'] : $cacheTime;
                $rssDoc = JFactory::getFeedParser($options['rssUrl'], $ct);
            }
        }

        $this->feed     = $rssDoc;
        $this->feed_url = $options['rssUrl'];

        return $this->feed;
    }
}
