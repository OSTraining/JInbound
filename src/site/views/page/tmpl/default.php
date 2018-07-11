<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 */

defined('JPATH_PLATFORM') or die;

?>
<div class="jinbound_component">
    <?php

    echo $this->loadTemplate('layout');

    ?>

    <?php if (!empty($this->item->ga) && !empty($this->item->ga_code)) : ?>
        <?php $code = JInboundHelperFilter::escape_js($this->item->ga_code); ?>
        <?php if (1 === (int)$this->item->ga) : ?>
            <script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', '<?php echo $code; ?>']);
                _gaq.push(['_trackPageview']);

                (function() {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                })();
            </script>
        <?php elseif (2 === (int)$this->item->ga) : ?>
            <script type="text/javascript">
                (function(i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function() {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o), m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
                ga('create', '<?php echo $code; ?>', 'auto');
                ga('send', 'pageview');
            </script>
        <?php elseif (3 === (int)$this->item->ga) : ?>
            <!-- Google Tag Manager -->
            <noscript>
                <iframe
                    src="//www.googletagmanager.com/ns.html?id=<?php echo JInboundHelperFilter::escape($this->item->ga_code); ?>"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
            </noscript>
            <script>(function(w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
                    var f = d.getElementsByTagName(s)[0], j = d.createElement(s),
                        dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src = '//www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', '<?php echo $code; ?>');</script><!-- End Google Tag Manager -->
        <?php endif; ?>
    <?php endif; ?>

    <?php if (JInboundHelper::config("debug", 0)) : ?>
        <pre><?php echo htmlspecialchars(print_r($this->item, 1)); ?></pre>
        <pre><?php echo htmlspecialchars(print_r($this->form, 1)); ?></pre>
    <?php endif; ?>
</div>
