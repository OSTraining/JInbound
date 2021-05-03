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

?>

<!-- default_edit_field -->
<div class="row-fluid" data-id="<?php echo $this->_currentField->id; ?>">
    <div class="span2"><?php echo $this->_currentField->label; ?></div>
    <div class="span9 offset1"><?php echo $this->_currentField->input; ?></div>
</div>
<?php if ('attrs' === substr($this->_currentField->id, -5)) : ?>
    <div class="well row-fluid">
        <h3>Common Attributes</h3>

        <h4>checkbox</h4>
        <ul class="unstyled">
            <li><span class="label">checked</span> expects "true", "checked", or "1" to be true</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
            <li><span class="label">onclick</span> javascript onclick</li>
            <li><span class="label">onchange</span> javascript onchange</li>
        </ul>

        <h4>checkboxes</h4>
        <ul class="unstyled">
            <li><span class="label">checked</span> comma-separated list of option values to be checked by default</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
        </ul>

        <h4>color</h4>
        <ul class="unstyled">
            <li><span class="label">control</span> hue (default), saturation, brightness, wheel or simple</li>
            <li><span class="label">position</span> right (default), left, top or bottom</li>
            <li><span class="label">colors</span> comma separated list of colors, only used when <span class="label">control</span>
                = "simple"
            </li>
            <li><span class="label">split</span> number of colors before each split, default 5, only used when <span
                    class="label">control</span> = "simple"
            </li>
            <li><span class="label">onchange</span> javascript onchange</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
        </ul>

        <h4>email</h4>
        <h5>see also <span class="label">text</span></h5>
        <ul class="unstyled">
            <li><span class="label">multiple</span> sets if present</li>
        </ul>

        <h4>integer</h4>
        <h5>see also <span class="label">list</span></h5>
        <ul class="unstyled">
            <li><span class="label">first</span> integer, first number in sequence</li>
            <li><span class="label">last</span> integer, last number in sequence</li>
            <li><span class="label">step</span> integer, increment amount</li>
        </ul>

        <h4>list</h4>
        <ul class="unstyled">
            <li><span class="label">size</span> number of rows to show at once</li>
            <li><span class="label">multiple</span> sets if present</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
            <li><span class="label">onchange</span> javascript onchange</li>
        </ul>

        <h4>meter</h4>
        <h5>see also <span class="label">number</span></h5>
        <ul class="unstyled">
            <li><span class="label">width</span> width of control, in css syntax (100px, 50%, etc)</li>
            <li><span class="label">color</span> background-color in css syntax</li>
            <li><span class="label">active</span></li>
            <li><span class="label">animated</span></li>
        </ul>

        <h4>number</h4>
        <ul class="unstyled">
            <li><span class="label">max</span> maximum value allowed</li>
            <li><span class="label">min</span> minimum value allowed</li>
            <li><span class="label">step</span> increment amount</li>
            <li><span class="label">autocomplete</span> on (default), off</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
            <li><span class="label">onchange</span> javascript onchange</li>
        </ul>

        <h4>radio</h4>
        <ul class="unstyled">
            <li><span class="label">autofocus</span> automatically focus this element</li>
        </ul>

        <h4>range</h4>
        <h5>see also <span class="label">number</span></h5>

        <h4>tel</h4>
        <h5>see also <span class="label">text</span></h5>

        <h4>text</h4>
        <ul class="unstyled">
            <li><span class="label">maxLength</span> max number of characters</li>
            <li><span class="label">autocomplete</span> on (default), off</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
            <li><span class="label">spellcheck</span></li>
            <li><span class="label">inputmode</span></li>
            <li><span class="label">dirname</span></li>
            <li><span class="label">pattern</span></li>
            <li><span class="label">onchange</span> javascript onchange</li>
        </ul>

        <h4>textarea</h4>
        <ul class="unstyled">
            <li><span class="label">rows</span></li>
            <li><span class="label">cols</span></li>
            <li><span class="label">autocomplete</span> on (default), off</li>
            <li><span class="label">autofocus</span> automatically focus this element</li>
            <li><span class="label">spellcheck</span></li>
            <li><span class="label">onchange</span> javascript onchange</li>
            <li><span class="label">onclick</span> javascript onclick</li>
        </ul>

        <h4>url</h4>
        <h5>see also <span class="label">text</span></h5>

        <h3>jInbound-specific Attributes</h3>
        <p>Some special attributes are recognized by jInbound on certain field types.</p>
        <ul class="unstyled">
            <li><span class="label">transpose</span> boolean to switch the key and value placement on list options.</li>
            <li><span class="label">blank</span> adds an option with an empty value and the text supplied to the
                beginning of list type inputs.
            </li>
        </ul>
    </div>
<?php endif; ?>
