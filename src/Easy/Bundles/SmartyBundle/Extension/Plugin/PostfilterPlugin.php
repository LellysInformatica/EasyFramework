<?php

namespace Easy\Bundles\SmartyBundle\Extension\Plugin;

/**
 * See {@link http://www.smarty.net/docs/en/plugins.functions.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class PostfilterPlugin extends AbstractPlugin
{

    public function getType()
    {
        return 'postfilter';
    }

}
