<?php
/**
 * bloodline class file for Bloodline extra
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
 
 
 Overall goal of this is to fill the $report array with info and errors.
 
 Array
 
 */


class Bloodline {

    public $modx;
    public $props;
    public $controllers = array(); // Reverse Action Map

    public $report = array('info'=>array(),'errors'=>array());
    
    
    function __construct(&$modx, &$config = array()){
        $this->modx =& $modx;
        $this->props =& $config;
        $this->controllers = $this->loadActionMap(); // reverse action map
    }

    /**
     * Get the absolute manager url for editing an item.
     *
     * @param string $type template,resource, snippet etc. corresponding to the type of thing
     * @param integer $id of the object in question
     * @return string absolute URL for manager editing, e.g. 
     */
    function get_mgr_url($type, $id) {

        switch ($type) {
            case 'template':
                $action = 'element/template/update';
                break;
            case 'snippet':
                $action = 'element/snippet/update';
                break;
            case 'chunk':
                $action = 'element/chunk/update';
                break;
            case 'tv':
                $action = 'element/tv/update';
                break;
            case 'resource':
                $action = 'resource/update';
                break;
            case 'context':
                $action = 'context/update';
        }
        
        $id = (int) $id;
        $a = $this->modx->getOption($action, $this->controllers);
        if (!$a) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,'Bloodline: unknown controller/action for type '.$type);
            return '';
        }
        return MODX_MANAGER_URL ."index.php?a={$a['id']}&id={$id}";
    }
    
    /**
     * Each error message should have a message (the obvious bit), 
     * and an optional URL (usually in the mgr) where the user can click to resolve/troubleshoot
     *
     * This gets added onto the $report error stack.
     *
     * @param string $msg
     * @param string $url
     * @return void
     */
    public function error($msg,$url='') {
        $this->report['errors'][] = array(
            'msg' => $msg,
            'url' => $url
        );
    }
    
    public function get_report($content_type='text/html') {
        $report = json_encode($this->report);
        switch ($content_type) {
            default:
                return '<script type="text/javascript">
                    var Bloodline = '.$report.';
                </script>';
        }
    }
    
    /**
     * Each info message should have a message (the obvious bit), 
     * and an optional URL (usually in the mgr) where the user can click to edit
     *
     * This gets added onto the $report stack.
     *
     * @param string $msg
     * @param string $url
     * @return void
     */
    public function info($msg,$url='') {
        $this->report['info'][] = array(
            'msg' => $msg,
            'url' => $url
        );    
    }

    /**
     * Guts copied more or less from modmanagerrequest.class.php
     * But I need this reversed: lookup by the action controller, not by
     * its number.
     Normal format is something like this:
     
     Array(
        [3] => Array
        (
            [id] => 3
            [namespace] => core
            [controller] => browser
            [haslayout] => 0
            [lang_topics] => file
            [assets] => 
            [help_url] => 
            [namespace_name] => core
            [namespace_path] => /Users/everett2/Sites/moxycart/html/manager/
            [namespace_assets_path] => {assets_path}
        )
     *
     * @return @array
     */
    public function loadActionMap() {
        $cacheKey = 'mgr/actions';
        $rmap = array();
        $map = $this->modx->cacheManager->get($cacheKey, array(
            xPDO::OPT_CACHE_KEY => $this->modx->getOption('cache_action_map_key', null, 'action_map'),
            xPDO::OPT_CACHE_HANDLER => $this->modx->getOption('cache_action_map_handler', null, $this->modx->getOption(xPDO::OPT_CACHE_HANDLER)),
            xPDO::OPT_CACHE_FORMAT => (integer) $this->modx->getOption('cache_action_map_format', null, $this->modx->getOption(xPDO::OPT_CACHE_FORMAT, null, xPDOCacheManager::CACHE_PHP)),
        ));
        if (!$map) {
            $map = $this->modx->cacheManager->generateActionMap($cacheKey);
        }
        
        if (empty($map) || !is_array($map)) {
            $this->log(xPDO::LOG_LEVEL_ERROR,'Bloodline: unable to load MODX action map.');
            $this->error('Unable to load MODX action map.');
            return false; // fail!
        }
        
        foreach ($map as $a => $v) {
            $rmap[$v['controller']] = $v;
        }
        return $rmap;
    }
        
    /**
     * Markup a given string $str with Bloodline markers.
     * String must be verified, otherwise, our regexes will fail.
     * The challenge is always nested tags, e.g. [[~[[*id]]]] 
     * So when we find a tag start '[[', we must traverse through the string until we 
     * find its relevant closing tag.
     */
    public function markup($str) {
        // Gotta strip out those nasty "space-like" characters.
		$str = str_replace(array("\r","\r\n","\n","\t",chr(202),chr(173),chr(0xC2),chr(0xA0) ), ' ', $str);
        
        //Chunks
        
        return $str;
    }
    
	/**
	 * Basic integrity check: look for mismatched square-brackets.  if ($backticks & 1)... odd number.
	 *
	 * @param	string	$type		Resource|Chunk|Template|TV (used for messaging)
	 * @param	string	$field		the field being checked (so we know what content to load)
	 * @param	object	$obj		either $resource, $template, $tv, or $chunk, depending.
	 * @return  boolean true if everything is ok, false on error.
	 */
    public function verify($type, $field, &$obj) {		
		$content 	= $obj->get($field);
		$id 		= $obj->get('id');
		
		$out = true;
		
		$left_brackets	= substr_count($content, '[[');
		$right_brackets	= substr_count($content, ']]');
		$backticks		= substr_count($content, '`');
		
		if ($left_brackets != $right_brackets) {
			$this->error("Mismatched brackets in $type $field",$this->get_mgr_url($type,$id));
			$out = false;
		}
		
		if($backticks&1) {
			$this->error("Mismatched backticks in $type $field",$this->get_mgr_url($type,$id));
			$out = false;
		}
		return $out;
	}    


}