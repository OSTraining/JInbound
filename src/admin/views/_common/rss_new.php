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


defined('_JEXEC') or die;

if (!empty($this->feed) && is_string($this->feed)) {
    echo $this->feed;
} else {
    $lang       = JFactory::getLanguage();
    $direction  = " ";

    if ($lang->isRTL()) {
        $direction = " redirect-rtl";
    } // Feed description
    elseif ($lang->isRTL() && $myrtl == 1) {
        $direction = " redirect-ltr";
    } elseif ($lang->isRTL()) {
        $direction = " redirect-rtl";
    }

    if ($this->feed != false) :
        // Image handling
        $iUrl = isset($this->feed->image) ? $this->feed->image : null;
        $iTitle = isset($this->feed->imagetitle) ? $this->feed->imagetitle : null;
        ?>
        <div
            style="direction: <?php echo $lang->isRTL() ? 'rtl' : 'ltr'; ?>; text-align: <?php echo $lang->isRTL() ? 'right' : 'left'; ?> ! important"
            class="feed">
            <?php

            // Feed description
            if (!is_null($this->feed->title)) : ?>
                <h2 class="<?php echo $direction; ?>">
                    <a href="<?php echo str_replace('&', '&amp;', $this->feed_url); ?>" target="_blank">
                        <?php echo $this->feed->title; ?></a>
                </h2>
            <?php endif; ?>

            <!-- Feed description -->
            <?php if ($this->showDescription) : ?>
                <?php echo $this->feed->description; ?>
            <?php endif; ?>

            <!--  Feed image  -->
            <?php if ($iUrl) : ?>
                <img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/>
            <?php endif; ?>


            <!-- Show items -->
            <?php if (!empty($this->feed)) : ?>
                <ul class="newsfeed">
                    <?php for ($i = 0; $i < 5; $i++) :

                        if (!$this->feed->offsetExists($i)) :
                            break;
                        endif;
                        $uri  = (!empty($this->feed[$i]->uri) || !is_null($this->feed[$i]->uri)) ? $this->feed[$i]->uri : $this->feed[$i]->guid;
                        $uri  = substr($uri, 0, 4) != 'http' ? $this->feed_url : $uri;
                        $text = !empty($this->feed[$i]->content) || !is_null($this->feed[$i]->content) ? $this->feed[$i]->content : $this->feed[$i]->description;
                        ?>
                        <li>
                            <?php if (!empty($uri)) : ?>
                                <h5 class="feed-link">
                                    <a href="<?php echo $uri; ?>" target="_blank">
                                        <?php echo $this->feed[$i]->title; ?></a></h5>
                            <?php else : ?>
                                <h5 class="feed-link"><?php echo $this->feed[$i]->title; ?></h5>
                            <?php endif; ?>

                            <?php if ($this->showDetails && !empty($text)) : ?>
                                <div class="feed-item-description">
                                    <?php
                                    // Strip the images.
                                    $text = JFilterOutput::stripImages($text);

                                    $text = JHtml::_('string.truncate', $text,
                                        $this->wordLimit ? $this->wordLimit : 250);
                                    echo str_replace('&apos;', "'", $text);
                                    ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif;
}
