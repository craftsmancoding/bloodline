<?php
/**
 * Bloodline plugin for Bloodline extra
 *
 * Copyright 2013 by Everett Griffiths <http://craftsmancoding.com>
 * Created on 11-11-2013
 *
 * Bloodline is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Bloodline is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Bloodline; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package bloodline
 */

/**
 * Description
 * -----------
 * A plugin to add verbose commenting to your output to help
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package bloodline
 **/
 
// if has mgr context
// and if URL param isset



if ($modx->event->name == 'OnLoadWebDocument') {
        $cacheKey= 'mgr/actions';
        $map = $modx->cacheManager->get($cacheKey, array(
            xPDO::OPT_CACHE_KEY => $modx->getOption('cache_action_map_key', null, 'action_map'),
            xPDO::OPT_CACHE_HANDLER => $modx->getOption('cache_action_map_handler', null, $modx->getOption(xPDO::OPT_CACHE_HANDLER)),
            xPDO::OPT_CACHE_FORMAT => (integer) $modx->getOption('cache_action_map_format', null, $modx->getOption(xPDO::OPT_CACHE_FORMAT, null, xPDOCacheManager::CACHE_PHP)),
        ));
        print_r($map); exit;
    // Overrides for dev work
    $core_path = $modx->getOption('bloodline.core_path','', MODX_CORE_PATH);
    require_once($core_path.'components/bloodline/model/bloodline/bloodline.class.php');
    
    $Bloodline = new Bloodline($modx);
    
    $modx->resource->set('cacheable',false);
    
    // Check Resouce Content...
    
    // Some resources may not have a template set...
    if ($modx->resource->Template) {
        $Bloodline->verify('modTemplate','content',$modx->resource->Template);
        //$template_content = $Bloodline->verify('modT$modx->resource->Template->get('content'));
        //$template_content = 'BARRRF';
        //$modx->resource->Template->set('content',$template_content);
    }
    
    
    
}
