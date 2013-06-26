<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_remoteimage
 *
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Generated by AKHelper - http://asikart.com
 */

// no direct access
defined('_JEXEC') or die;


// Init some API objects
// ================================================================================
$date 	= JFactory::getDate( 'now' , JFactory::getConfig()->get('offset') ) ;
$doc 	= JFactory::getDocument() ;
$uri 	= JFactory::getURI() ;
$user	= JFactory::getUser() ;
$app 	= JFactory::getApplication() ;
$lang	= JFactory::getLanguage();
$lang_code = $lang->getTag();
$lang_code = str_replace('-', '_', $lang_code) ;


// Include elFinder and JS
// ================================================================================
JHtml::_('behavior.framework', true);

if( JVERSION >= 3){
	
	// jQuery
	JHtml::_('jquery.framework', true);
	JHtml::_('bootstrap.framework', true);

}else{
	if($this->modal){
		$doc->addStyleSheet('components/com_remoteimage/includes/bootstrap/css/bootstrap.min.css');
	}
	
	// jQuery
	$doc->addScript( 'components/com_remoteimage/includes/js/jquery/jquery.js' );
	$doc->addScriptDeclaration('jQuery.noConflict();');
}


// elFinder includes
$doc->addStylesheet( 'components/com_remoteimage/includes/js/jquery-ui/css/smoothness/jquery-ui-1.8.24.custom.css' );
$doc->addStylesheet( 'components/com_remoteimage/includes/js/elfinder/css/elfinder.min.css' );
$doc->addStylesheet( 'components/com_remoteimage/includes/js/elfinder/css/theme.css' );

$doc->addscript( 'components/com_remoteimage/includes/js/jquery-ui/js/jquery-ui-1.8.24.custom.min.js' );
$doc->addscript( 'components/com_remoteimage/includes/js/elfinder/js/elfinder.min.js' );
JHtml::script( JURI::base().'components/com_remoteimage/includes/js/elfinder/js/i18n/elfinder.'.$lang_code.'.js' );
RMHelper::_('include.core');



// For Site
// ================================================================================
if($app->isSite()) {
	RemoteimageHelper::_('include.isis');
}


// PARAMS
$params         = JComponentHelper::getParams('com_remoteimage') ;
$safemode       = $params->get('Safemode', true) ;
$onlyimage      = $params->get('Onlyimage', false) ;
$tabs           = $this->modal ? true : false ;
$height         = $this->modal ? JRequest::getVar('height', 380) : 520 ;

// System Info
$upload_max = ini_get('upload_max_filesize') ;
$upload_num = ini_get('max_file_uploads') ;
$sysinfo = JText::_('COM_REMOTEIMAGE_UPLOAD_MAX') . ' ' . $upload_max; 
$sysinfo .= ' | ' . JText::_('COM_REMOTEIMAGE_UPLOAD_NUM') . ' ' . $upload_num; 


// Is FormField
$fieldid = JRequest::getVar('fieldid') ;


?>
<script type="text/javascript">
	var elFinder ;
	var elSelected = [];
	var el ;
	var RMinModal ;
    var root_uri = '<?php echo JURI::root(); ?>';
    var insert_template_image   = '<?php echo str_replace(",", "\,", $params->get('Integrate_InsertTemplateImage', '<p>{%CONTENT%}</p>')); ?>';
    var insert_template_link    = '<?php echo str_replace(",", "\,", $params->get('Integrate_InsertTemplateLink', '{%CONTENT%}')); ?>';
	
	// Insert Image to Article
	var insertImageToParent = function(){
		var imgs 	= elSelected ;
        var elFinder = window.elFinder;
		var urls    = $('insert-from-url').get('value');
        var fixAll	= $('rm-setwidth').checked ;
		var dW 		= $('rm-width').get('value').toInt() ;
        var tags	= '';
        
        
        // Handle From Urls
        urls = urls.toString().trim();
        
        if( urls ) {
            urls = urls.split("\n");
            
            urls.each( function(e, i){
                var path = e.split('/');
                var ext = path.getLast().split('.').getLast();
                var img_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
                
                if(!e.trim()) {
                    return;
                }
                
                if( img_ext.contains(ext) ) {
                    // Create img element
                    var img = new Element('img', {
                        alt : path.getLast() ,
                        src : e
                    }) ;
                    
                    // Fix Width
                    if( fixAll ) {
                        img.set('width', dW) ;
                    }
                    
                    tags += insert_template_image.replace( '{%CONTENT%}' ,img.outerHTML);
                }else{
                    var a = new Element('a', {
                        href : e,
                        target : '_blank',
                        text : path.getLast()
                    });
                    
                    tags += '&nbsp; ' + insert_template_link.replace( '{%CONTENT%}' ,a.outerHTML) + '&nbsp; ' ;
                }
                
            });
        }else{
            // Insert From Selected
            if( elSelected.length < 1 ) {
                return ;
            }
            
            imgs.each( function(e, i){
            
                if( e.mime.split('/')[0] == 'image' ) {
                    // Create img element
                    var img = new Element('img', {
                        alt : e.name ,
                        src : elFinder.url(e.hash)
                    }) ;
                    
                    // Fix Width
                    if( fixAll ) {
                        img.set('width', dW) ;
                    }
                    
                    tags += insert_template_image.replace( '{%CONTENT%}' ,img.outerHTML);
                }else{
                    var a = new Element('a', {
                        href : elFinder.url(e.hash),
                        target : '_blank',
                        text : e.name
                    });
                    
                    tags += '&nbsp; ' + insert_template_link.replace( '{%CONTENT%}' ,a.outerHTML) + '&nbsp; ' ;
                }
                
            } );
        }
        
        
        if (window.parent) window.parent.jInsertEditorText(tags, '<?php echo JRequest::getVar('insert_id') ; ?>');
        
        setTimeout( function(){
            if (window.parent) window.parent.SqueezeBox.close();
        } , 200);
	}
	
    
    var insertFormField = function(){
        var imgs 	= elSelected ;
        var elFinder = window.elFinder;
        var urls    = $('insert-from-url').get('value');        
        
        // Handle From Urls
        urls = urls.toString().trim();
        
        if( urls ) {
            url = urls.split("\n")[0];
            
        }else{
            // Insert From Selected
            if( elSelected.length < 1 ) {
                return ;
            }
            
            var img = imgs[0];
            url = elFinder.url(img.hash);
            
        }
        
        url = url.replace( root_uri, '');
        console.log(url);
        window.parent.jInsertFieldValue(url,'<?php echo $fieldid; ?>');
        
        setTimeout( function(){
            if (window.parent) window.parent.SqueezeBox.close();
        } , 50);
    }
	
	// Init elFinder
	jQuery(document).ready(function($) {
        
        var elConfig = {
			url : 'index.php?option=com_remoteimage&task=manager' ,
			width : '100%' ,
            height : '<?php echo $height; ?>' ,
			lang : '<?php echo $lang_code; ?>',
            requestType : 'post',
			handlers : {
				select : function(event, elfinderInstance) {
					var selected = event.data.selected;
	
					if (selected.length) {
						elSelected = [];
						jQuery.each(selected, function(i, e){
							elSelected[i] = elfinderInstance.file(e);
						});
					}
	
				}
			}
            ,
            uiOptions : {
                // toolbar configuration
                toolbar : [
                    ['back', 'forward'],
                    ['reload'],
                    ['home', 'up'],
                    ['mkdir', 'mkfile', 'upload'],
                    ['open', 'download', 'getfile'],
                    //['info'],
                    ['quicklook'],
                    ['copy', 'cut', 'paste'],
                    ['rm'],
                    ['duplicate', 'rename', 'edit', 'resize'],
                    ['extract', 'archive'],
                    ['search'],
                    ['view'],
                    ['help']
                ]
            }
			<?php if( $this->modal ): ?>
			,
			getFileCallback : function(file){
				<?php echo $fieldid ? 'insertFormField();' : 'insertImageToParent();'; ?>
			}
			
			<?php endif; ?>
			
		}
        
        <?php if( $onlyimage ): ?>
        elConfig.onlyMimes = ['image'] ;
        <?php endif; ?>
        
		elFinder = $('#elfinder').elfinder(elConfig).elfinder('instance');
        
        elFinder.ui.statusbar.append( '<?php echo $sysinfo; ?>' );
	});
</script>

<style type="text/css">
    <?php if( $this->modal ): ?>
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    <?php endif; ?>
</style>

<div id="remoteimage-manager" class="<?php echo (JVERSION >= 3) ? 'joomla30' : 'joomla25' ?>">
		
    <?php echo $tabs ? AKHelper::_('panel.startTabs', 'RMTabs', array( 'active' => 'panel-elfinder' )) : null ; ?>
        
        <?php echo $tabs ? AKHelper::_('panel.addPanel' , 'RMTabs', JText::_('COM_REMOTEIMAGE_MANAGER'), 'panel-elfinder') : null ; ?>
		<!-- elFinder Body -->
		<div class="row-fluid">
			<div id="elfinder" class="span12 rm-finder">
				
			</div>
		</div>
        <?php echo $tabs ? AKHelper::_('panel.endPanel') : null ; ?>
        
        <?php if( $this->modal ): ?>
        <!--Insert From URL-->
            <?php echo $tabs ? AKHelper::_('panel.addPanel' , 'RMTabs', JText::_('COM_REMOTEIMAGE_INSERT_FROM_URL'), 'panel-url') : null ; ?>
                <?php echo JText::_('COM_REMOTEIMAGE_INSERT_FROM_URL_DESC'); ?>
                <br /><br />
                <textarea name="insert-from-url" id="insert-from-url" cols="30" class="span9" rows="10"></textarea>
            <?php echo $tabs ? AKHelper::_('panel.endPanel') : null ; ?>
        <?php endif; ?>
    <?php echo $tabs ? AKHelper::_('panel.endTabs') : null ; ?>    
		
		
		<?php if( $this->modal || AKDEBUG ): ?>
		<div class="row-fluid">
			<div id="rm-insert-panel" class="span12 form-actions">
				<div class="form-inline pull-left">
					<label for="rm-width" id="rm-width-lbl" class=""><?php echo JText::_('COM_REMOTEIMAGE_MAX_WIDTH'); ?></label>
					<input type="text" id="rm-width" class="input input-mini" value="<?php echo $this->params->get('Image_DefaultWidth_Midium', 640); ?>" />
					&nbsp;&nbsp;
					<!--<span class="rm-width-height-x">X</span>
					<input type="text" id="rm-height" class="input input-mini" value="<?php echo $this->params->get('Image_DefaultHeight_Midium', 640); ?>" />
					-->
					<label for="rm-setwidth">
						<input type="checkbox" id="rm-setwidth" name="rm-setwidth" value="1" />
						<?php echo JText::_('COM_REMOTEIMAGE_FIX_ALL_IMAGE_WIDTH'); ?>
					</label>
				</div>
				
				
				<div class="btns pull-right fltrt">
					
					<button id="rm-insert-button" class="btn btn-primary" onclick="<?php echo $fieldid ? 'insertFormField();' : 'insertImageToParent();'; ?>">
						<?php echo JText::_('COM_REMOTEIMAGE_INSERT_IMAGES'); ?>
					</button>
					
					<button id="rm-cancel-button" class="btn" onclick="window.parent.SqueezeBox.close();">
						<?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?>
					</button>
					
				</div>
			</div>
		</div>
		<?php endif; ?>

</div>