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
    public $config;
    public $controllers = array(); // Reverse Action Map

    private $msg; // weaving... 
    
    public $report = array(
        'info'=>array(),
        'warn'=>array(),
        'errors'=>array(),
        'tags'=> array() // raw MODX tags (1st layer of nesting only)
        
    );
    // Bloodline will insert markup for the these types of tags (see get_tag_info)
    // Options: comment, lexicon, chunk, snippet, link, docvar, tv, placeholder, setting
    public $markup_tags = array(
        //'chunk','snippet','docvar','tv'
    );
    
	// What [[*docvars]] are available by default?
	public $resource_fields = array(
    	'id','type','contentType','pagetitle','longtitle','description','alias','link_attributes',
    	'published','pub_date','unpub_date','parent','isfolder','introtext','content','richtext',
    	'template','menuindex','searchable','cacheable','createdby','createdon','editedby','editedon',
    	'deleted','deletedon','deletedby','publishedon','publishedby','menutitle','donthit',
    	'content_dispo','hidemenu','context_key','content_type','uri_override','hide_children_in_tree',
    	'show_in_tree','articles_container_settings','articles_container'
	);    
    
    public static $cache_opts = array();
    const CACHE_DIR = 'bloodline';
    
    /**
     *
     * @param object modx
     * @param array
     */
    function __construct(&$modx, &$config = array('markup'=>array(),'format'=>'both')){
        $this->modx =& $modx;
        $this->config =& $config;
        $this->controllers = $this->loadActionMap(); // reverse action map
        self::$cache_opts = array(xPDO::OPT_CACHE_KEY => self::CACHE_DIR);
    }

    /**
     * Close a bloodline tag, usually an HTML comment
     *
     * @param array $info
     * @param string 
     */
    function _close_tag($info) {
        return '<!--BLOODLINE_END::'.$info['type'].':'.$info['id'].'-->';
    }

    /**
     * Open a bloodline tag, usually an HTML comment
     *
     * @param array $info
     * @param string 
     */
    function _open_tag($info) {
        return '<!--BLOODLINE_START::'.$info['type'].':'.$info['id'].'-->';
    }
    
    
    /**
     * Take Bloodline report data and convert it to a nicely formatted HTML report
     * http://www.istockphoto.com/stock-illustration-19742371-oak-tree-silhouette-with-roots.php
     * @return string
     */
    private function _to_html() {
        $out = '';
        
        $props = array(
            'bloodline.info' => '',        
            'bloodline.warnings' => '',        
            'bloodline.errors' => '',
            'bloodline.tags' => ''
        );

        $tpl = file_get_contents(dirname(dirname(dirname(__FILE__))).'/elements/chunks/report.tpl');
        $tag_tpl = file_get_contents(dirname(dirname(dirname(__FILE__))).'/elements/chunks/tag.tpl');
        $log_tpl = file_get_contents(dirname(dirname(dirname(__FILE__))).'/elements/chunks/log.tpl');
        
        $uniqid = uniqid();
        foreach($this->report['info'] as $t) {
            $t['type'] = 'Info';
            $chunk = $this->modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
            $chunk->setCacheable(false);
            $props['bloodline.info'] .= $chunk->process($t, $log_tpl);
        }
        foreach($this->report['warn'] as $t) {
            $t['type'] = 'Warning';
            $chunk = $this->modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
            $chunk->setCacheable(false);
            $props['bloodline.warnings'] .= $chunk->process($t, $log_tpl);
        }

        
        foreach($this->report['errors'] as $t) {
            $t['type'] = 'Error';
            $chunk = $this->modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
            $chunk->setCacheable(false);
            $props['bloodline.errors'] .= $chunk->process($t, $log_tpl);
        }

        foreach($this->report['tags'] as $t) {
            $chunk = $this->modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
            $chunk->setCacheable(false);
            $props['bloodline.tags'] .= $chunk->process($t, $tag_tpl);
        }
        foreach($this->config['markup'] as $m) {
            $props[$m.'.ischecked'] = ' checked="checked"';
        }
        $props[$this->config['format'].'.isselected'] = 'selected="selected"';
        //print $this->modx->makeUrl($this->modx->resource->get('id'),'',$_GET,'full');; exit;
        $props['action_url'] = $this->modx->makeUrl($this->modx->resource->get('id'),'',array('BLOODLINE' => 1),'full');
//        $props['action_url'] = str_replace('&amp;', '&', $props['action_url']);
/*
        $props['persisting_values'] = '';
        foreach ($_GET as $k => $v) {
            $props['persisting_values'] = '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
        }
*/
        $chunk = $this->modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
        $chunk->setCacheable(false);
        $out = $chunk->process($props, $tpl);


        
        return $out;
        foreach ($this->report['info'] as $i) {
            if (empty($i['url'])) {
                $out .= sprintf('%s<br/>',$i['msg']);
            }
            else {
                $out .= sprintf('<a href="%s" target="_blank">%s</a><br/>',$i['url'],$i['msg']);
            }
        }
        
        return '<div id="bloodline"><h2>Bloodline</h2><h3>Page Info</h3>'.$out.'</div>';
    }
    
    /** 
     * Take Bloodline report data and convert it to Javascript
     *
     * @return string (valid JS)
     */
    private function _to_js(){
        return 'var Bloodline = '.json_encode($this->report).';
        console.group("Bloodline");
            console.group("Page Info");
            for(var i=0;i<Bloodline.info.length;i++){
                var obj = Bloodline.info[i];
                console.info(obj.msg + " " + obj.url);
            }
            console.groupEnd();
            
            console.group("Warnings");
            for(var i=0;i<Bloodline.warn.length;i++){
                var obj = Bloodline.warn[i];
                console.warn(obj.msg + " " + obj.url);
            }
            console.groupEnd();
            
            console.group("Errors");        
            for(var i=0;i<Bloodline.errors.length;i++){
                var obj = Bloodline.errors[i];
                console.error(obj.msg + " " + obj.url);
            }
            console.groupEnd();
                    
            console.group("All Tags");
            for(var i=0;i<Bloodline.errors.length;i++){
                var obj = Bloodline.errors[i];
                console.error(obj.msg + " " + obj.url);
            }
            console.groupEnd();
        console.groupEnd();
        ';

    }
	//------------------------------------------------------------------------------
	/**
	 * Check the parameters to see if they are correct.
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str 
	 * @return  int $id of chunk
	 */
	private function _validate_chunk($str) {
        $this->msg = ''; // init
		$chunk = $this->modx->getObject('modChunk', array('name'=>$str));
		if (!$chunk) {
            $this->msg = 'Chunk does not exist '.$str;
			$this->error($this->msg);
			return '';
		}
		return $chunk->get('id');
	}

	//------------------------------------------------------------------------------
	/**
	 * Not sure how to check a docvar in isolation
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str 
	 */
	private function _validate_tv($str) {
        $this->msg = ''; // init
        $page_id = $this->modx->resource->get('id');
   
        // Does this TV exist at all?
        $TV = $this->modx->getObject('modTemplateVar', array('name'=>$str));
		if (!$TV) {
		    $this->msg = 'Template variable [[*'.$str.']] does not exist.';
			$this->error($this->msg);
			return '';
		}
		
		// Is this TV assigned to this Template?
		$TVT = $this->modx->getObject('modTemplateVarTemplate'
			, array('templateid'=>$this->modx->resource->get('template'), 'tmplvarid'=> $TV->get('id') ));
		if (!$TVT) {
		    $this->msg = 'TV not assigned to current template: [[*'.$str.']]';   
			$this->warn($this->msg, $this->get_mgr_url('template',$this->modx->resource->get('template')));
			return $TV->get('id');
		}
        return $this->get_mgr_url('tv',$TV->get('id'));

	}


	//------------------------------------------------------------------------------
	/**
	 * Check the parameters to see if they are correct.
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str 
	 */
	private function _validate_link($str) {
        $this->msg = ''; // init
		if (empty($str)) {
			return;
		}
		$resource = $this->modx->getObject('modResource', $str);
		if (!$resource) {
            $this->msg = 'Linked resource does not exist: [[~'.$str.']]';
			$this->error($this->msg);
			return '';
		}
		return $resource->get('id');
	}

	//------------------------------------------------------------------------------
	/**
	 * Check the parameters to see if they are correct.
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str 
	 */
	private function _validate_lexicon($str) {
        $this->msg = ''; // init	
		// TODO: how to check this?
		//$lexicon = $this->modx->getObject('modLexiconEntry', array('name' => $str));
		//if (!$lexicon) {
		//	$this->errors[] = sprintf( $this->modx->lexicon('lexicon_does_not_exist'), '[[%'.$str.']]');
		//	$this->simple_errors[] = sprintf( $this->modx->lexicon('lexicon_does_not_exist'), '[[%'.$str.']]');			
		//}	
	}

	//------------------------------------------------------------------------------
	/**
	 * Looks up the propset to see if it exists.
	 *
	 * Sets error messages if there are problems.
	 * getOption won't work: e.g. YOUR user may not have this setting, but the user 
	 * who views it might have it.
	 *
	 * @param	string	$str
	 */
	private function _validate_setting($str) {
		$this->msg = ''; // init
		// This will get global settings and any hard-coded ones, e.g. site_url					
		$value = $this->modx->getOption($str);
		
		// Settings defined in the config.inc.php
		if (in_array($str, array('site_url','assets_url','core_path','base_url','assets_path'))) {
            return '';
		}
		//$Setting = $this->modx->getObject('modSystemSetting', array('name'=>$str));
		if ($value !== null) {
            $Setting = $this->modx->getObject('modSystemSetting', array('key'=>$str));
            if (!$Setting) {
                $key = $this->modx->context->get('key');
    			$Setting = $this->modx->getObject('modContextSetting', array('key'=>$str,'context_key'=>$key));
    			if (!$Setting) {			
    				$Setting = $this->modx->getObject('modUserSetting', array('key'=>$str));
    				if (!$Setting) {
    				    $this->msg = 'Setting does not exist: [[++'.$str.']]';
    					$this->error($this->msg);
    					return '';
    				}
    				return $this->get_mgr_url('setting',$Setting->get('id'));
    			}
    			else {
                    if ($value !== $Setting->get('value')) {
                        $this->msg = 'Setting '.$str.' is defined as a Context Setting.';
                        $this->info($this->msg);
                    }
    			}
            }
			return $this->modx->context->get('id');
		}
	}
	
	
	//------------------------------------------------------------------------------
	/**
	 * Looks up the propset to see if it exists.
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str
	 */
	private function _validate_snippet($str) {
        $this->msg = ''; // init
		$Snippet = $this->modx->getObject('modSnippet', array('name'=>$str));
		if (!$Snippet) {
            $this->msg = 'Snippet does not exist: [['.$str.']]';
			$this->error($this->msg);
		}
		return $Snippet->get('id');
	}
    
    //------------------------------------------------------------------------------
    //! Public
    //------------------------------------------------------------------------------

	/**
	 * Given the contents of a MODX tag, get the token: this is the primary 
	 * identifier, e.g. in a Snippet, it is the Snippet name, in a link, it is the 
	 * link id.
	 *
	 * @param	string contents of a tag without an !, e.g. "pagetitle" or "MySnippet? &arg=`one`"
	 * @param	string
	 */
	public function get_token($tag) {
		$tag = trim($tag);

        // Get token
		preg_match('/^[^@?:&`]+/i', $tag, $matches);
		if (!empty($matches)){
			return trim($matches[0]);
		}
        $this->modx->log(xPDO::LOG_LEVEL_ERROR,'Bloodline: could not find valid token in tag '.$tag);
        return '';
	}

	    
    /**
     * Get the absolute manager url for editing an item.
     *
     * @param string $type template,resource, snippet etc. corresponding to the type of thing
     * @param integer $id of the object in question
     * @return string absolute URL for manager editing, e.g. 
     */
    public function get_mgr_url($type, $id) {

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
            case 'docvar':                
            case 'resource':
                $action = 'resource/update';
                break;
            case 'context':
                $action = 'context/update';
                break;
            case 'setting':
                $action = 'system/settings';
                break;
            case 'tv':
                $action = 'element/tv/update';
            default:
                $action  = 'unknown action';
        }
        
        $id = (int) $id;
        $a = $this->modx->getOption($action, $this->controllers);
        if (!$a) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,'Bloodline: unknown controller/action ('.$action.') for type '.$type);
            return '';
        }
        return MODX_MANAGER_URL ."index.php?a={$a['id']}&id={$id}";
    }

    /**
     * Get the full web url (including http) for drilling down into an item (this link feeds into 
     * other Bloodline functionality -- it's not a link you'd ever use outside of this plugin).
     * WARNING: this passes the $_GET array over to MODX without sanitizing it, so you are relying
     * entirely on MODX to filter out nasties from the URL, e.g. imagine a URL parameter like:
     * http://yoursite.com/page?BLOODLINE=1&xss=haha<script>alert('boned')</script>
     * MODX does filter out script tags, but it's not recommended to rely on this.  Bloodline only
     * operates when you are logged into the manager, so it's considered reasonably secure: if 
     * you are already logged into the manager (legitimately or maliciously), why resort to XSS?
     *
     * @param string $type template,resource, snippet etc. corresponding to the type of thing
     * @param integer $id of the object in question
     * @return string absolute URL for drilling into the object referenced 
     */
    public function get_web_url($type, $id) {
        $_GET['type'] = $type;
        $_GET['id'] = (int) $id;
        return $this->modx->makeUrl($this->modx->resource->get('id'),'',$_GET,'full');
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
            'msg' => $this->neutralize($msg),
            'url' => $url
        );
    }
    
    public function get_report($content_type='text/html') {
        return $this->_to_html();
/*
        switch ($content_type) {
            default:
                return '<script type="text/javascript">'.
                $this->_to_js()
                .'</script>
                <pre>
                '
                . print_r($this->report,true)
                . '</pre>';
                //. $this->_to_html();
        }
*/
    }

	//------------------------------------------------------------------------------
	/**
	 * This looks at the first part of a tag to determine the type. Then it looks up
	 * the info about this particular tag.
	 *
	 * See http://rtfm.modx.com/display/revolution20/Tag+Syntax
	 *
	 * Sample output:
	 *
     *   Array
     *   (
     *       [32] => tag_open
     *       [35] => tag_open
     *       [40] => tag_close
     *       [58] => tag_close
     *       [84] => tag_open
     *       [92] => tag_close
     *   )
	 *
	 *     Key = character position of the opening or closing tag.
	 *
	 * @param string MODX $tag including square brackets, e.g. [[$chunk]]
	 * @return string tag type: comment, lexicon, chunk, snippet, link, docvar, tv, placeholder
	 */
	public function get_tag_info($tag) {
		$info = array(
            'raw' => '',
            'hash' => md5($tag), 
            'cached' => '',
            'type' => '',
            'id' => '',
            'msg' => '', // redundant (repeat of errors, warnings, info)
            'mgr_url' => '',
            'web_url' => '',
		);
		
		
		$info['raw'] = $this->neutralize($tag);
		
		$tag = trim($tag,'[]'); // trim the ends off
		$tag = trim($tag);
							
		// Strip the exclamation point 
        $content = ltrim($tag,'!');
        $info['cached'] = ($tag == $content)? 1: 0;

        // The signature character
		$first_char = substr($content, 0, 1);
		
		switch ($first_char) {
			// Comment tag
			case '-':
			case '#':
                $info['type'] = 'comment'; 
				return; // do nothing.
				break;				
			// Lexicon tag
			case '%':
                $info['type'] = 'lexicon'; 
				$content = substr($content, 1); // shift off the %
				$token = $this->get_token($content);
				$this->_validate_lexicon($token);
				break;				
			// Chunk
			case '$':
                $info['type'] = 'chunk'; 
				$content = substr($content, 1); // shift off the $
				$token = $this->get_token($content);
				$info['id'] = $this->_validate_chunk($token);
				$info['msg'] = $this->msg;
				break;
			// Link
			case '~':
    			$info['type'] = 'link'; 
				$content = substr($content, 1); // shift off the ~
				$token = $this->get_token($content);
				$info['id'] = $this->_validate_link($token);
				$info['msg'] = $this->msg;
				break;
			// Doc var
			case '*':
    			$content = substr($content, 1); // shift off the *
                if (in_array($content, $this->resource_fields)) {
                    $info['type'] = 'docvar';
                    $info['id'] = $this->modx->resource->get('id');
			    }
			    else {
                    $info['type'] = 'tv';
    				$token = $this->get_token($content);
    				$info['id'] = $this->_validate_tv($token);
    				$info['msg'] = $this->msg;
			    }
				
				break;
			// Placeholder or System Setting
			case '+':
				$content = substr($content, 1); // shift off first char
				// ++ System Setting
				if (substr($content, 0, 1) == '+') {
				    $info['type'] = 'setting'; 
					$content = substr($content, 1);
					$token = $this->get_token($content);
					$info['id'] = $this->_validate_setting($token);
					$info['msg'] = $this->msg;
				}
				// Placeholder
				else {
				    $info['type'] = 'placeholder'; 
					$token = $this->get_token($content);
					// we don't check the token, 'cuz who knows
				}
				break;
			
			// Snippet
			default:
    			$info['type'] = 'snippet'; 
				$token = $this->get_token($content);
				$info['id'] = $this->_validate_snippet($token);
				$info['msg'] = $this->msg;
		}
		
		if ($info['id']) {
            $info['mgr_url'] = $this->get_mgr_url($info['type'],$info['id']);
            $info['web_url'] = $this->get_web_url($info['type'],$info['id']);
		}
		
		return $info;
	}


	/**
	 * From SyntaxChecker
	 * Get a map of all opening and closing tags ('[[' and ']]');
	 *
	 * Goal is something like this where key is the string position:
	 * Array(
	 *	[12]	=> 'tag_open',
	 *  [20]	=> 'tag_close',
	 * )
	 *
	 * @param string
	 * @return array
	 */
	public function get_tag_map($str) {
		$map = array();
		
		$strlen = strlen ($str);
		
		// Find starting tags;
		$offset = 0;
		while($offset !== false) {
			$offset = strpos($str,'[[',$offset);
			if ($offset === false) {
				break;
			}
			$map[$offset] = 'tag_open';
			$offset++; // advance the pointer
		}

		// Find closing tags;
		$offset = 0;
		while($offset !== false) {
			$offset = strpos($str,']]',$offset);
			if ($offset === false) {
				break;
			}
			$map[$offset] = 'tag_close';
			$offset++; // advance the pointer
		}
		
		ksort($map);
		return $map;
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
            'msg' => $this->neutralize($msg),
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
     * String must be verified (equal numbers of opening [[ and closing tags ]] and 
     * an even number of backticks), otherwise, our regexes will fail.
     * The challenge is always nested tags, e.g. [[~[[*id]]]] 
     * So when we find a tag start '[[', we must traverse through the string until we 
     * find its relevant closing tag.
     *
     * We can only place markup in the outter most depth, not inside nested tags.
     *
     * @param string $str the HTML that you want to add markup to.
     */
    public function markup($str) {    
        //$str = '[[MySnip? &chunk=`[[$dick]]`]]';
        // Gotta strip out those nasty "space-like" characters.
        // I can't remember why these characters were problematic, but I think it was because 
        // they throw off character counts (?)
		$str = str_replace(array("\r","\r\n","\n","\t",chr(202),chr(173),chr(0xC2),chr(0xA0) ), ' ', $str);
        
        $map = $this->get_tag_map($str);
        
        $map_copy = $map;
        
//print '<textarea rows="20" cols="60">'.print_r($map,true).'</textarea>'; exit;
        // Matchup each opening tag to its ending tag.
        // Then get grab the tag and get its info.
        // The $catalog array contains keys (tag start position) => values (tag end position)
        $cache = array(); // tmp save start positions.
        $catalog = array();
        $tags = array();
        foreach ($map as $k => $v) {
            if ($v == 'tag_open') {
                $depth++;
                $cache[$depth] = $k; // start position
            }
            if ($v == 'tag_close') {
                $catalog[$cache[$depth]] = $k;
                $length = ($k + 2) - $cache[$depth]; // tag_close - tag_open
                $tag = substr ($str , $cache[$depth], $length);
                $info = $this->get_tag_info($tag); // get tag info
                $info['depth'] = $depth;
                $this->modx->cacheManager->set('tags/'.$info['hash'], $tag, 0, self::$cache_opts);
                $this->report['tags'][] = $info;
                $depth--;
            }            
        }

//print '<textarea rows="20" cols="60">'.print_r($this->report,true).'</textarea>'; exit;
        
        // 1st Pass: We simplify our tag map so we skip nested tags.
		$indices = array_keys($map);
		$count = count($indices);
		$this_index = $map[$indices[0]];
		$depth=0;
		for ( $i = 1; $i < $count; $i++ ) {
			$next_index = $map[$indices[$i]];
			if ($this_index == 'tag_open' && $next_index == 'tag_open') {
			    unset($map[$indices[$i]]); // <-- $next_index
			    $depth++;
			}
			if ($this_index == 'tag_open' && $next_index == 'tag_close' && $depth) {
			    unset($map[$indices[$i]]); // <-- $next_index
			    $depth--;
			}
			$this_index = $next_index;
		}
        
        // Do the Markup.  We should only markup chunks,snippets
        $str_len = strlen($str);
        $out = '';
        $indices = array_keys($tag_type_map);
//        print '<pre>'.print_r($tag_type_map,true).'</pre>'; exit;
        for($i=0;$i<$str_len;$i++) {
            if (isset($map[$i]) && $map[$i] == 'tag_open') {
                if (in_array($tag_type_map[$i]['type'], $this->config['markup'])) {
                    $out .= $this->_open_tag($tag_type_map[$i]);
                }
            }
            $out .= $str[$i]; 
            if (isset($map[$i-1]) && $map[$i-1] == 'tag_close') {
                if (in_array($close_tag_map[$i-1]['type'], $this->config['markup'])) {
                    $out .= $this->_close_tag($close_tag_map[$i-1]);
                }
            }
        }
        
//        print_r($this->report['tags']); exit;
        return $out;
    }
    
    /**
     * Take a string and make it inert to the MODX parser so we can display it in error messages etc.
     *
     * @param string
     * @return string
     */
    public function neutralize($str) {
        return str_replace(array('[',']'), array('&#91;','&#93;'),$str);
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
    public function warn($msg,$url='') {
        $this->report['warn'][] = array(
            'msg' => $this->neutralize($msg),
            'url' => $url
        );    
    }
}