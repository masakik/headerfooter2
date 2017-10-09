<?php
/**
 * DokuWiki Plugin headerfooter (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Li Zheng <lzpublic@qq.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_headerfooter extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('PARSER_WIKITEXT_PREPROCESS', 'AFTER', $this, 'handle_parser_wikitext_preprocess');
   
    }
    public function handle_parser_wikitext_preprocess(Doku_Event &$event, $param) {
        global $INFO;
        global $ID;
        global $conf;
        
        //what does this mean???
        if ($INFO['id'] != '') return; // 发现每页会执行两次，当id为空时是真正的文本，否则是菜单。

        //helper array needed for parsePageTemplate
        //so that replacement like shown here is possible: https://www.dokuwiki.org/namespace_templates#replacement_patterns
        $data = array(
            'id'        => $ID, // the id of the page to be created
            'tpl'       => '', // the text used as template
        );

        $headerpath = '';
        $path = dirname(wikiFN($ID));
        if (@file_exists($path.'/_header.txt')) {
            $headerpath = $path.'/_header.txt';
        } else {
            // search upper namespaces for templates
            $len = strlen(rtrim($conf['datadir'], '/'));
            while (strlen($path) >= $len) {
                if (@file_exists($path.'/__header.txt')) {
                    $headerpath = $path.'/__header.txt';
                    break;
                }
                $path = substr($path, 0, strrpos($path, '/'));
            }
        }

        if (!empty($headerpath)) {
            $header = file_get_contents($headerpath);
            if ($header !== false) {
                $data['tpl'] = cleanText($header);
                $header = parsePageTemplate($data);

                if ($this->getConf('separation') == 'paragraph') { // 如果使用段落来分割
                    $header = rtrim($header, " \r\n\\") . "\n\n";
                }
                if (strpos($ID,'sidebar')===false)
                    $event->data = $header . $event->data;
            }
        }


        $footerpath = '';
        $path = dirname(wikiFN($ID));
        if (@file_exists($path.'/_footer.txt')) {
            $footerpath = $path.'/_footer.txt';
        } else {
            // search upper namespaces for templates
            $len = strlen(rtrim($conf['datadir'], '/'));
            while (strlen($path) >= $len) {
                if (@file_exists($path.'/__footer.txt')) {
                    $footerpath = $path.'/__footer.txt';
                    break;
                }
                $path = substr($path, 0, strrpos($path, '/'));
            }
        }

        if (!empty($footerpath)) {
            $footer = file_get_contents($footerpath);
            if ($footer !== false) {
                $data['tpl'] = cleanText($footer);
                $footer = parsePageTemplate($data);

                if ($this->getConf('separation') == 'paragraph') { // 如果使用段落来分割
                    $footer = rtrim($footer, " \r\n\\") . "\n\n";
                }
                if (strpos($ID,'sidebar')===false)
                    $event->data .= $footer;
            }
        }
    }
}
