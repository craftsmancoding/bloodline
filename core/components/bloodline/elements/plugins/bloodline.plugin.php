<?php
/**
 * @name Bloodline
 * @description Add ?BLOODLINE=1 to your URLs to trigger verbose info to help debug problems, profile speed issues, and quickly find page components.
 * @Events OnLoadWebDocument
 *
 * Plugin for MODX Revolution
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
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package bloodline
 **/
 
if ($modx->event->name == 'OnLoadWebDocument') {
    // You must be logged into the manager for this to work
    if (!isset($_GET['BLOODLINE']) || !isset($modx->user) || !$modx->user->hasSessionContext('mgr')) {
        return;
    }

    set_time_limit(0);

    // Override path for dev work
    $core_path = $modx->getOption('bloodline.core_path','', MODX_CORE_PATH);
    require_once($core_path.'components/bloodline/model/bloodline/bloodline.class.php');
    $valid = true;
    $content = '';
    
    // If a specific element is defined, we override everything
    $config             = array();
    $obj_id             = (isset($_GET['obj_id']))? $_GET['obj_id']: null;
    $field             = (isset($_GET['field']))? $_GET['field']: null;    
    $type               = (isset($_GET['type']))? $_GET['type']: null;
    $hash               = (isset($_GET['hash']))? $_GET['hash']: null;
    $config['markup']   = (isset($_GET['markup']))? $_GET['markup']: array();
    $config['format']   = (isset($_GET['format']))? $_GET['format']: 'both'; // js|html|both
    
    $Bloodline = new Bloodline($modx,$config);

    // Gather info...    
    $ctx = ($modx->context->get('id'))? $modx->context->get('id'):'web';
    $Bloodline->info('Context '.$modx->context->get('key'),MODX_MANAGER_URL ."index.php?a=5&id=0&key=$ctx");
    if (isset($modx->resource->Template)) {
        $Bloodline->info('Template '.$modx->resource->Template->get('name'). ' ('.$modx->resource->Template->get('id').')'
            ,$Bloodline->get_mgr_url('template', $modx->resource->Template->get('id')));    
    }
    $Bloodline->info('Resource '.$modx->resource->get('pagetitle'). ' ('.$modx->resource->get('id').')'
        ,$Bloodline->get_mgr_url('resource', $modx->resource->get('id')));

    
    if ($type) {
        switch ($type) {
            case 'tv':
                $content = $modx->resource->getTVValue($field);
                break;
            case 'docvar':
                $content = $modx->resource->get($field);
                break;
            case 'setting':
                $t['map_url'] = '';
                break;
            case 'chunk':
                $Chunk = $modx->getObject('modChunk',$obj_id);
                $content = $Chunk->getContent();
                break;
            case 'snippet':   
                break;
            case 'placeholder':
                break;
            case 'link':
                break;
        }
        // Fake template for presentation
        $modx->resource = $modx->newObject('modResource');
        $modx->resource->Template = $modx->newObject('modTemplate');
        $modx->resource->Template->set('content',$content);
        
    }
    // Drill Down uses a hash
    elseif ($hash) {
        $tag = $modx->cacheManager->get('tags/'.$hash, $Bloodline::$cache_opts); 
        $Bloodline->info('Tag '.$Bloodline->neutralize($tag),'');
            
        // Fake template for presentation
        $modx->resource->Template = $modx->newObject('modTemplate');
        $modx->resource->Template->set('content',$tag);
        $content = $tag;
        
    }
    // Most resources have a template set...
    elseif (isset($modx->resource->Template)) {
        $content = $modx->resource->Template->get('content');
    }
    // No template: resource only.
    else {
        $content = $modx->resource->get('content');
    }
    
    // TODO: if ($profile) { }
    // set start time
    $mtime = explode(" ", microtime());
    $tstart = $mtime[1] + $mtime[0];

    // This is necessary so our markup shows.
    $modx->resource->set('cacheable',false);    
    $tmp_content = $content; // we need a copy b/c the parser operates on a reference
    $maxIterations= intval($modx->getOption('parser_max_iterations',10));
    $modx->parser->processElementTags('', $tmp_content, false, false, '[[', ']]', array(), $maxIterations);

    // how long did it take?
    $mtime = explode(" ", microtime());
    $tend = $mtime[1] + $mtime[0];

    $totalTime = ($tend - $tstart);
    $totalTime = sprintf("%2.4f s", $totalTime);
    $Bloodline->info('Parse time: '.$totalTime);
    
    
    $valid = $Bloodline->verify($content);
    
    //---------------
    // Reporting
    //---------------
    if($valid) {
        // Get the tag map etc.
        $content = $Bloodline->markup($content);
//print '<textarea rows="20" cols="60">'.print_r($Bloodline->report['tags'],true).'</textarea>'; exit;
    }
    
    $content = $content . $Bloodline->get_report($modx->resource->get('contentType'));
    $modx->resource->Template->set('content',$content);
}
