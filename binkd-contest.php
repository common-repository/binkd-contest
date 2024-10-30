<?php
/*
Plugin Name: Binkd Contest App
Plugin URI: https://promotion.binkd.com/wordpress-contest-plugin.aspx
Description: A plugin that allows you to embed your contests on the Binkd Promotion Platform into your wordpress site.
Version: 0.1.9
Author: Binkd
Author URI: https://promotion.binkd.com/
License: GPL
*/
?>
<?php @ require_once ('binkd-client.php'); ?>
<?php

/*********** Activation Hooks **************/

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'binkd_contest_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'binkd_contest_remove' );

function binkd_contest_install() {
/* Creates new database field */
add_option("binkd_apiKey", '', '', 'yes');
add_option("binkd_iframeWidth", '', '', 'yes');
add_option("binkd_iframeHeight", '', '', 'yes');
}

function binkd_contest_remove() {
/* Deletes the database field */
delete_option('binkd_apiKey');
delete_option('binkd_iframeWidth');
delete_option('binkd_iframeHeight');
}


/************ Load Admin Page *************/

add_action('admin_menu', 'binkd_contest_menu');
    
function binkd_contest_menu()
{

    add_management_page('Binkd Contest', 'Binkd Contest', 'edit_posts', 'contest', 'binkd_contest_admin_page');
		
}

function binkd_contest_admin_page()
{

?>
<script type="text/javascript">
  function appendText(text)
  {    //Insert content

  parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + text);    //Close window
  parent.jQuery("#TB_closeWindowButton").click();
  }
</script>
<div style="position:relative;">
  <div style="position:absolute;background-color:White;top:-33px;padding:20px;left:-170px;height:590px;width:640px;z-index:256;text-align:center;">
    <div style="margin:0 auto;width:400px;">
      <h1>Binkd Promotion Contests</h1>
     
      <div style="width:100%;padding:20px;text-align:left;">
        
        <?php
 
 if (get_option('binkd_apiKey') == '')
 {
 
 ?>

        <p>You must enter your API Key first. Please go to the Binkd Contest options page. (Under the settings tab in the Wordpress menu)</p>
        
        
        <?php
 } else {
 ?>

        <p>
          Please select the contest you wish to embed below or <a href="https://promotion.binkd.com/Account/Select.aspx" target="_blank">Create A Contest</a>
        </p>
        
        <?php    
$api = new binkd_api_client(get_option('binkd_apiKey'));
$arr = $api->get_contestList();

foreach($arr as $item) {  
  echo $item['name'].' - ';
	echo '<a href="javascript:appendText(\'[BINKD_CONTEST_ID='.$item['id'].']\')">Embed In Post</a>'; 
	
	echo '<br /><br />';
}

}
?>

      </div>
    </div>
  </div>
</div>

<?php

}

/*********** Add Media Button ********/

function plugin_media_button($context) {
	$plugin_media_button = ' %s' . "<a href='admin.php?page=contest&amp;iframe&amp;TB_iframe=1&amp;width=640&amp;height=590' onclick='return false;' id='add_contest' class='thickbox' title='$title'><img src='/wp-content/plugins/binkd-contest/binkd.png' alt='$title' /></a>";
	return sprintf($context, $plugin_media_button);
  }
  
  add_filter('media_buttons_context', 'plugin_media_button');


/********* Filter Content **********/

add_filter('the_content','binkd_contest_output');

function binkd_contest_output($content)
{

    $iframeCode = '';

    $pos = strpos($content, '[BINKD_CONTEST_ID=');

    if ($pos != false)
    {
        // length of tag string
        $len = 18;
    
        // finds the end of the tag
        $endPos = strpos($content, ']', $pos) + 1;
        
        // Gets the full contest tag
        $contestTag = substr($content, $pos, $endPos - $pos);
    
        // The Id of the contest
        $contestId= substr($contestTag, strpos($contestTag, '=') + 1, strpos($contestTag, ']') - strpos($contestTag, '=') - 1);
    
        if (get_option('binkd_iframeWidth') != '')
        {
          $width = get_option('binkd_iframeWidth');
        }
        else
        {
          $width = '100%';
        }
        
        if (get_option('binkd_iframeHeight') != '')
        {
          $height = get_option('binkd_iframeHeight');
        }
        else
        {
          $height = '820px';
        }
        
        // iframe Url
        $iframeUrl = '';
        
        // Parent url
        $currentUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $parentUrl = urlencode($currentUrl);
                            
        if (strpos($currentUrl, 'binkdEid') != 0 && strpos($currentUrl, 'binkdId') != 0)
        {
            $eid = $_GET['binkdEid'];
            $id = $_GET['binkdId'];
            
            $currentUrl = str_replace('binkdEid='.$eid, '', $currentUrl);
            $currentUrl = str_replace('binkdId='.$id, '', $currentUrl);
                           
            
            // Individual contest page
            
             $iframeUrl = 'https://promotion.binkd.com/Contest.aspx?parentUrl='.$parentUrl.'&id='.$contestId.'&eid='.$eid;
        }
        elseif (strpos($currentUrl, 'internalRedirect') != 0)
        {
             $internalRedirect = $_GET['internalRedirect'];
            
             $iframeUrl = 'https://promotion.binkd.com/Show.aspx?parentUrl='.$parentUrl.'&internalRedirect='.rawurlencode($internalRedirect);
        }
        else
        {
            
            $eidRefer = $_GET['eid'];
            
            // Just the contest
            $iframeUrl = 'https://promotion.binkd.com/Show.aspx?parentUrl='.$parentUrl.'&id='.$contestId.'&eid='.$eidRefer;
        }
               
	      // Create the iframe
        $iframeCode = '<iframe src="'.$iframeUrl.'" width="'.$width.'" height="'.$height.'" name="binkdiFrame" frameborder="0" vspace="0" hspace="0" marginwidth="0" marginheight="0" scrolling="auto" noresize seamless></iframe>';
    
        // Replaces the tag with the appropriate iFrame
        $content = str_replace($contestTag,$iframeCode,$content);
    
    }

    return $content;

}

/*********** Admin **************/

if ( is_admin() ){

/* Call the html code */
add_action('admin_menu', 'binkd_contest_admin_menu');

function binkd_contest_admin_menu() {
add_options_page('Binkd Contest', 'Binkd Contest', 'administrator',
'binkd-contest', 'binkd_contest_html_page');
}
}

function binkd_contest_html_page() {
?>
    <div>

  <h2>Binkd Contest Options</h2>

  <form method="post" action="options.php">

    <?php wp_nonce_field('update-options'); ?>

    <table width="700">
      <tr valign="top">
        <th width="92" scope="row">API Key:</th>
        <td width="606">
          <input name="binkd_apiKey" type="text" style="width:300px;height:30px" id="binkd_apiKey"
          value="<?php echo get_option('binkd_apiKey'); ?>" /><br />
          (ex. bda11d91-7ade-4da1-855d-24adfe39d174)
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <br />
          To get your API Key please visit the <a href="https://promotion.binkd.com/Account/API.aspx" target="_blank">Binkd API Account Page</a>.
        </td>
      </tr>
      <tr valign="top">
        <th width="92" scope="row">Default Width:</th>
        <td width="606">
          <input name="binkd_iframeWidth" type="text" style="width:300px;height:30px" id="binkd_iframeWidth"
          value="<?php echo get_option('binkd_iframeWidth'); ?>" /><br />
          (in pixels, ex. 600)
        </td>
      </tr>
      <tr valign="top">
        <th width="92" scope="row">Default Height:</th>
        <td width="606">
          <input name="binkd_iframeHeight" type="text" style="width:300px;height:30px" id="binkd_iframeHeight"
          value=""<?php echo get_option('binkd_iframeHeight'); ?>" /><br />
          (in pixels, ex. 820)
        </td>
      </tr>
    </table>

    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="binkd_apiKey,binkd_iframeWidth,binkd_iframeHeight" />
    <p>
      <input type="submit" value="<?php _e('Save Changes') ?>" />
    </p>

  </form>
</div>
<?php
}
?>