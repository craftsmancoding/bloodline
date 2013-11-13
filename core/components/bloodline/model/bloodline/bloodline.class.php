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

    public $report = array(
        'info'=>array(),
        'warn'=>array(),
        'errors'=>array(),
        'tags'=> array() // raw MODX tags (1st layer of nesting only)
        
    );
    
    
    function __construct(&$modx, &$config = array()){
        $this->modx =& $modx;
        $this->props =& $config;
        $this->controllers = $this->loadActionMap(); // reverse action map
    }

    private function _to_html() {
        $out = '';
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
    
    private function _to_js(){
        return 'var Bloodline = '.json_encode($this->report).';
        console.group("Page Info");
        for(var i=0;i<Bloodline.info.length;i++){
            var obj = Bloodline.info[i];
            console.info(obj.msg + " " + obj.url);
        }
        console.groupEnd();
        
        for(var i=0;i<Bloodline.warn.length;i++){
            var obj = Bloodline.warn[i];
            console.warn(obj.msg + " " + obj.url);
        }
        
        for(var i=0;i<Bloodline.errors.length;i++){
            var obj = Bloodline.errors[i];
            console.error(obj.msg + " " + obj.url);
        }';
    }
	//------------------------------------------------------------------------------
	/**
	 * Check the parameters to see if they are correct.
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str 
	 */
	private function _validate_chunk($str) {
		$chunk = $this->modx->getObject('modChunk', array('name'=>$str));
		if (!$chunk) {			
			$this->error('Chunk does not exist '.$str);
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Check the parameters to see if they are correct.  Params and TVs...
	 * the problem here is that the [[*docvar]] instances could appear 
	 * ANYWHERE (e.g. in a Chunk), so we have to adjust the checking depending
	 * on where it appears.
	 *
	 * Sets error messages if there are problems.
	 *
	 * @param	string	$str 
	 */
	private function _validate_docvar($str) {
	
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
		if (empty($str)) {
			return;
		}
		$resource = $this->modx->getObject('modResource', $str);
		if (!$resource) {
			$this->error('Linked resource does not exist: [[~'.$str.']]');
		}
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
		
		// This will get global settings and any hard-coded ones, e.g. site_url					
		$Setting = $this->modx->getOption($str); 
		//$Setting = $this->modx->getObject('modSystemSetting', array('name'=>$str));
		if (!$Setting) {
			$Setting = $this->modx->getObject('modContextSetting', array('name'=>$str));
			if (!$Setting) {			
				$Setting = $this->modx->getObject('modUserSetting', array('name'=>$str));
				if (!$Setting) {
					$this->error('Setting does not exist: [[++'.$str.']]');
				}
			}
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
		$Snippet = $this->modx->getObject('modSnippet', array('name'=>$str));
		if (!$Snippet) {
			$this->error('Snippet does not exist: [['.$str.']]');
		}
	}
    
    //------------------------------------------------------------------------------
    //! Public
    //------------------------------------------------------------------------------

	/**
	 * Break down a tag into its component parts.
	 *
	 * array(
	 *		[token]		=>
	 *		[propset]	=>
	 *		[filters]	=>
	 *		[params]	=> 
	 * )
	 *
	 * @param	string contents of a tag without an !, e.g. "pagetitle" or "MySnippet? &arg=`one`"
	 * @param	array
	 */
	public function atomize_tag($tag) {
		$tag = trim($tag);
		
		$parts = array(
			'token' => '',
			'propset' => '',
			'filters' => '',
			'params' => ''
		);
		
		// Get token
		preg_match('/^[^@?:&`]+/i', $tag, $matches);
		if (!empty($matches)){
			$parts['token'] = trim($matches[0]);
			$tag = trim(preg_replace('/^'.$matches[0].'/', '', $tag));
		}
		else {
			// ERROR!! No Token!!
			return;
		}
		
		// Get Propset
		$first_char = substr($tag, 0, 1);
		if ($first_char == '@') {
			$tag = substr($tag, 1); // shift off first char
			preg_match('/^[^?:&`]+/i', $tag, $matches);
			if (isset($matches[0])) {
				$parts['propset'] = trim($matches[0]);
				$this->_validate_propset($parts['propset']);
				$tag = trim(preg_replace('/^'.$matches[0].'/', '', $tag));
			}
		}
		
		// Get Filters
		$first_char = substr($tag, 0, 1);
		if ($first_char == ':') {
			$tag = substr($tag, 1); // shift off first char
			preg_match('/^[^?&]+/i', $tag, $matches);
			//print_r($matches);
			if (isset($matches[0])) {
				$parts['filters'] = trim($matches[0]);
				$this->_validate_filters($parts['filters']);
				$tag = trim(preg_replace('/^'.$matches[0].'/', '', $tag));
			}
		}
		if (!$tag) {
			return $parts;
		}
		// Get Params
		$first_char = substr($tag, 0, 1);
		if ($first_char == '?') {
			$tag = substr($tag, 1); // shift off first char
			$parts['params'] = trim($tag);			
		}
		else {
			// ERROR!!! Missing Question Mark!!!
			$this->error($parts['token']. ' Snippet call is missing a question mark');
			$parts['params'] = $tag;
		}
		//$this->_validate_params($parts);
		
		return $parts;
	}


	//------------------------------------------------------------------------------
	/**
	 * This looks at the first part of a tag to determine the type. Then it looks up
	 * the info about this particular tag.
	 *
	 * See http://rtfm.modx.com/display/revolution20/Tag+Syntax
	 *
	 * @param string MODX $tag including square brackets, e.g. [[$chunk]]
	 */
	public function get_tag_info($tag) {
		$info = array(
            'raw' => '',
            'cached' => '',
            'type' => '',
            'url',
		);
		$info['raw'] = str_replace(array('[',']'), array('&#91;','&#93;'),$tag);
		
		$tag = trim($tag,'[]');
		$tag = trim($tag);
							
		// Strip the exclamation point 
        $content = ltrim($tag,'!');
        $info['cached'] = ($tag == $content)? true: false;

        // The signature character
		$first_char = substr($content, 0, 1);
		
		switch ($first_char) {
			// Comment tag
			case '-':
			case '#':
                $info['type'] = 'Comment'; 
				return; // do nothing.
				break;				
			// Lexicon tag
			case '%':
                $info['type'] = 'Lexicon'; 
				$content = substr($content, 1); // shift off first char
				$parts = $this->atomize_tag($content);
				$this->_validate_lexicon($parts['token']);
				break;				
			// Chunk
			case '$':
                $info['type'] = 'Chunk'; 
				$content = substr($content, 1); // shift off first char
				$parts = $this->atomize_tag($content);
				$this->_validate_chunk($parts['token']);
				break;
			// Link
			case '~':
    			$info['type'] = 'Link'; 
				$content = substr($content, 1); // shift off first char
				$parts = $this->atomize_tag($content);
				$this->_validate_link($parts['token']);
				break;
			// Doc var
			case '*':
			    $info['type'] = 'Document Variable'; 
				$content = substr($content, 1); // shift off first char
				$parts = $this->atomize_tag($content);
				$this->_validate_docvar($parts['token']);
				break;
			// Placeholder or System Setting
			case '+':
				$content = substr($content, 1); // shift off first char
				// ++ System Setting
				if (substr($content, 0, 1) == '+') {
				    $info['type'] = 'System Setting'; 
					$content = substr($content, 1);
					$parts = $this->atomize_tag($content);
					$this->_validate_setting($parts['token']);
				}
				// Placeholder
				else {
				    $info['type'] = 'Placeholder'; 
					$parts = $this->atomize_tag($content);
					// we don't check the token, 'cuz who knows
				}
				break;
			
			// Snippet
			default:
    			$info['type'] = 'Snippet'; 
				$parts = $this->atomize_tag($content);
				$this->_validate_snippet($parts['token']);
		}
		
		$this->report['tags'][] = $info;
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
        switch ($content_type) {
            default:
                return '<script type="text/javascript">'.
                $this->_to_js()
                .'</script>'
                . $this->_to_html();
        }
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
        //$str = '[[MySnip? &chunk=`[[$dick]]`]]';
        // Gotta strip out those nasty "space-like" characters.
		$str = str_replace(array("\r","\r\n","\n","\t",chr(202),chr(173),chr(0xC2),chr(0xA0) ), ' ', $str);
        
        $map = $this->get_tag_map($str);
        
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

        //print_r($map); exit;
        
        // 2nd Pass: We log some data
		$indices = array_keys($map);
		$count = count($indices);
		$this_index = $map[$indices[0]];
		for ( $i = 1; $i < $count; $i++ ) {
			$next_index = $map[$indices[$i]];
			// Leave this in just in case
			if ($this_index == 'tag_open' && $next_index == 'tag_close') {
				$tag_len = $indices[$i] - $indices[$i-1];
				$full_tag_len = $tag_len + 2; // additional 2 characters for closing brackets
				$tag = substr ($str , $indices[$i-1], $full_tag_len );

                //$this->report['tags'][] = $tag;
                $this->get_tag_info($tag);
				//$this->check_tag_contents($tag, $field, $obj); // <-- the magic happens
			
				// Update the map: check these ones off our list
				unset($map[$indices[$i-1]]);
				unset($map[$indices[$i]]);
				
				// blank out that tag with spaces.  This is a cheap trick so we can test nested tags.
				//$whiteout = $this->_generate_whitespace($full_tag_len);
				//$content = substr_replace($content, $whiteout , $indices[$i-1], $full_tag_len );
			}
			
			$this_index = $next_index;
		}
        
//        print_r($this->report['tags']); exit;
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