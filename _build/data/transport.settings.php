<?php
/**
 * systemSettings transport file for Bloodline extra
 *
 * Copyright 2013 by Everett Griffiths <http://craftsmancoding.com>
 * Created on 11-11-2013
 *
 * @package bloodline
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $systemSettings */


$systemSettings = array();

$systemSettings[1] = $modx->newObject('modSystemSetting');
$systemSettings[1]->fromArray(array(
    'key' => 'bloodline.url_param',
    'name' => 'Bloodline URL Parameter',
    'description' => 'Front-end output is modified only when this URL parameter is set, e.g. http://yoursite.com/some/page/?BLOODLINE=1  (this is case-sensitive). You must be logged into the manager.',
    'namespace' => 'bloodline',
    'xtype' => 'textfield',
    'value' => 'BLOODLINE',
    'area' => 'default',
), '', true, true);
return $systemSettings;
