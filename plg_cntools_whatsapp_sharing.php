<?php
/**
 * @Copyright
 * @package     Content - WhatsApp Sharing - Plug In
 * @author      Clemens Neubauer {@link https://github.com/cn-tools/}
 * @date        Created on 11-June-2015
 * @link        Project Site {@link https://github.com/cn-tools/plg_cntools_whatsapp_sharing}
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

class PlgContentPlg_CNTools_WhatsApp_Sharing extends JPlugin {
    public function __construct(&$subject, $config) {
        $view = JFactory::getApplication()->input->getWord('view');

        if($view != 'article') {
            return;
        }

        parent::__construct($subject, $config);
    }

    public function onContentBeforeDisplay($context, &$article, &$params, $page = 0) {
		if($context == 'com_content.article' and $this->params->get('position') == '1') {
            $this->loadHeader();
            $html = $this->getWhatsAppButton($article);

            return $html;
        }
    }

    public function onContentAfterDisplay($context, &$article, &$params, $page = 0) {
		if($context == 'com_content.article' and $this->params->get('position') == '2') {
            $this->loadHeader();
            $html = $this->getWhatsAppButton($article);

            return $html;
        }
    }

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{	
		if($context == 'mod_custom.content' and JString::strpos($article->text, '{whatsapp}') !== false and $this->params->get('position') == '3')
		{
            $this->loadHeader();
			$article->text = preg_replace( "#{whatsapp}#s", $this->getWhatsAppButton($article), $article->text );	
		}
	} 
	
	private function loadHeader() {
        $document = JFactory::getDocument();
		$script = '/* <![CDATA[ */ if(typeof wabtn4fg==="undefined"){wabtn4fg=1;h=document.head||document.getElementsByTagName("head")[0],s=document.createElement("script");s.type="text/javascript";s.src="'.JURI::base().'plugins/content/plg_cntools_whatsapp_sharing/whatsapp-button.js";h.appendChild(s);} /* ]]> */';
		$document->addScriptDeclaration($script);
	}

    private function getWhatsAppButton($article) {
    	$buttonSize = $this->params->get('btnSize');
				
		// Find InfoText for sharing
		$dataText = JText::_('PLG_CNTOOLS_WHATSAPP_SHARING_DATATXT_TXT');
		if (($dataText == 'PLG_CNTOOLS_WHATSAPP_SHARING_DATATXT_TXT') || ($dataText == '')){
			$dataText = trim($this->params->get('dataTxt'));
		}
    	
    	// Find Button-Text for sharing
		$buttonText = JText::_('PLG_CNTOOLS_WHATSAPP_SHARING_BTNTXT_TXT');
		if (($buttonText == 'PLG_CNTOOLS_WHATSAPP_SHARING_BTNTXT_TXT') || ($buttonText == '')){
			$buttonText = trim($this->params->get('buttonTxt'));
		}
		if ($buttonText == ''){
			$buttonText = 'WhatsApp';
		}
		
		// Find URL for sharing
		$url = JText::_('PLG_CNTOOLS_WHATSAPP_SHARING_FIXURL_TXT');
		if (($url == 'PLG_CNTOOLS_WHATSAPP_SHARING_FIXURL_TXT') || ($url == '')){
			$url = $this->params->get('fixURL');
    	}
    	if ($url == ''){
			$url = JURI::current();
		}
		if ((strstr(substr($url, 0, 7), 'http://')) or (strstr(substr($url, 0, 8), 'https://'))) {
			//do nothing
		} else {
			$url = 'http://'.$url;
		}
		
		$lTrackingCode = '';
		if ($this->params->get('usega'))
		{
			if ($this->params->get('label') == '1')
			{
				$lLabel = $article->id.'-'.$article->title;
			} elseif ($this->params->get('label') == '2') {
				$lLabel = $article->alias;
			} else {
				$lLabel = str_replace(JURI::base(), '', JURI::current()); //remove base domain
			}
			$lLabel = str_replace( array('/', ':', ' ', '?', '#', '&') , array('_', '_', '_', '_', '_', '_') , $lLabel);
			
			$lTrackingCode .= 'onclick="';
			$lTrackingCode .= "ga('sende', 'event', '".$this->params->get('category', 'plg_cntools_whatsapp_sharing')."', '".$this->params->get('action', 'share')."', '".$lLabel."', 1";
			
			if ($this->params->get('nonInteraction'))
			{
				$lTrackingCode .= ", 'nonInteraction': 1";
			}
			$lTrackingCode .= ');" ';
		}
		
		$html = '<div id="plg_cntools_whatsapp_sharing_articleid'.$article->id.'" class="plg_cntools_whatsapp_sharing'.$this->params->get('divstyle').'" >';
		$html .= '<a href="whatsapp://send" '.$lTrackingCode.'data-text="'.$dataText.'" data-href="'.$url.'" class="wa_btn '.$buttonSize.'" style="display:none">'.$buttonText.'</a>';
		$html .= '</div><br class="clear" />';

        return $html;
    }
}
