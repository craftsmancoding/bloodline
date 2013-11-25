<?php
/**
 * Bloodline
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
 * Description
 * -----------
 * Adds verbose commenting to your output to help debug problems, profile speed issues, 
 * and quickly find the component elements.
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

    // Override path for dev work
    $core_path = $modx->getOption('bloodline.core_path','', MODX_CORE_PATH);
    require_once($core_path.'components/bloodline/model/bloodline/bloodline.class.php');

    // If a specific element is defined, we override everything
    $config = array();
    $id = $modx->getOption('id',$_GET);
    $type = $modx->getOption('type',$_GET);
    $hash = $modx->getOption('hash',$_GET);
    $config['markup'] = $modx->getOption('markup',$_GET,array());
    $config['format'] = $modx->getOption('format',$_GET,'both'); // js|html|both
    
    $Bloodline = new Bloodline($modx,$config);

    // This is necessary so our markup shows.
    $modx->resource->set('cacheable',false);
    
    

    // Drill Down
    if ($hash) {
        $tag = $modx->cacheManager->get('tags/'.$hash, $Bloodline::$cache_opts); 
        $ctx = ($modx->context->get('id'))? $modx->context->get('id'):'null';
        $Bloodline->info('Context '.$modx->context->get('key'). ' ('.$ctx.')'
            ,$Bloodline->get_mgr_url('context', $modx->context->get('id')));
        $Bloodline->info('Resource '.$modx->resource->get('pagetitle'). ' ('.$modx->resource->get('id').')'
            ,$Bloodline->get_mgr_url('resource', $modx->resource->get('id')));
        $Bloodline->info('Tag '.$Bloodline->neutralize($tag)
            ,'');
            
        // Fake template for presentation
        $modx->resource->Template = $modx->newObject('modTemplate');
        $modx->resource->Template->set('content',$tag);

        if($Bloodline->verify('resource','content',$modx->resource->Template)) {
//            print $modx->resource->Template->get('content'); exit;
            $content = $Bloodline->markup($modx->resource->Template->get('content'));
            $content = $content . $Bloodline->get_report($modx->resource->get('contentType'));
            $modx->resource->Template->set('content',$content);
        }
    }
    // Most resources have a template set...
    elseif ($modx->resource->Template) {
        $ctx = ($modx->context->get('id'))? $modx->context->get('id'):'null';
        $Bloodline->info('Context '.$modx->context->get('key'). ' ('.$ctx.')'
            ,$Bloodline->get_mgr_url('context', $modx->context->get('id')));
        $Bloodline->info('Template '.$modx->resource->Template->get('name'). ' ('.$modx->resource->Template->get('id').')'
            ,$Bloodline->get_mgr_url('template', $modx->resource->Template->get('id')));
        $Bloodline->info('Resource '.$modx->resource->get('pagetitle'). ' ('.$modx->resource->get('id').')'
            ,$Bloodline->get_mgr_url('resource', $modx->resource->Template->get('id')));
        
        if($Bloodline->verify('template','content',$modx->resource->Template)) {
            $content = $Bloodline->markup($modx->resource->Template->get('content'));
            $content = $content . $Bloodline->get_report($modx->resource->get('contentType'));
            //$content = $content . "\n".'<pre>'.print_r($Bloodline->report,true).'</pre>';
            $modx->resource->Template->set('content',$content);
        }
        // TODO: print errors        
    }
    // No template: resource only.
    else {
        $Bloodline->info('Context '.$modx->context->get('key'). '('.$modx->context->get('id').')'
            ,$Bloodline->get_mgr_url('context', $modx->context->get('id')));
        $Bloodline->info('No Template');
        $Bloodline->info('Resource '.$modx->resource->get('pagetitle'). ' ('.$modx->resource->get('id').')'
            ,$Bloodline->get_mgr_url('template', $modx->resource->get('id')));

        if($Bloodline->verify('resource','content',$modx->resource)) {
            $content = $Bloodline->markup($modx->resource->get('content'));
            $content = $content . $Bloodline->get_report($modx->resource->get('contentType'));
            //$content = $content . "\n".'<pre>'.print_r($Bloodline->report,true).'</pre>';
            $modx->resource->set('content',$content);
            $modx->resource->_content = $content;
        }
        // TODO: print errors
    }
}
