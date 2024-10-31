<?php
/*
Plugin Name: Recent Google Searches Widget
Plugin URI: http://www.blogseye.com
Description: Widget to display a list of recent search engine queries in a link to the wp search function.
Author: Keith P. Graham
Version: 1.50
Author URI: http://www.cthreepo.com
*/
function widget_kpg_collect_data_rgs() {
	// let's see if we are in a page referred by google or such	
	$ref='';
	if (array_key_exists('HTTP_REFERER',$_SERVER )) $ref=urldecode($_SERVER['HTTP_REFERER']);
	if (empty($ref)) return;
	if (strpos($ref,'?')===false) return;
	$r1=explode('?',$ref);
	if (count($r1)!=2) return;
	$m=$r1[0];
	$r1=explode('&',$r1[1]);
	$q=trim($q);
	$q='';
	for ($j=0;$j<count($r1);$j++) {
		if (substr($r1[$j],0,2)=='q=') {
			$q=substr($r1[$j],2);
			break;
		}
		if (substr($r1[$j],0,2)=='p=') {
			$q=substr($r1[$j],2);
			break;
		}
		if (substr($r1[$j],0,6)=='query=') {
			$q=substr($r1[$j],6);
			break;
		}
		if (substr($r1[$j],0,10)=='searchfor=') {
			$q=substr($r1[$j],10);
			break;
		}
		if (substr($r1[$j],0,8)=='preview=') {
			$q=substr($r1[$j],8);
			break;
		}
		if (substr($r1[$j],0,7)=='client=') {
			$q=substr($r1[$j],7);
			break;
		}
	}
	if (empty($q)) return;
	$query='';
	if (strpos($m,'google.')>0||
		strpos($m,'yahoo.')>0||
		strpos($m,'bing.')>0||
		strpos($m,'msn.')>0||
		strpos($m,'ask.')>0||
		strpos($m,'aol.')>0||
		strpos($m,'startpagina.')>0||
		strpos($m,'alltheweb.')>0||
		strpos($m,'googlesyndication.')>0||
		strpos($m,'iwon.')>0 ) {
		// search engine using q=
		$query=$q;
	} else {
		return;
	}
	$q=$query;
	if (empty($q)) return;
	
	// strip tags, esc_url_raw, remove_accents.
	$q=stripslashes($q);
	$q=urldecode($q);
	$q=strip_tags($q);
	$q=remove_accents($q);
	if (empty($q)) return;
	
	
	
	// if there is a search from the search engines, then we need to add it to our list
	// q has a legit search in it.
	// get the results of a search based on the parsed entry
	$q=str_replace('<',' ',$q); // just in case the striptags missed it
	$q=str_replace('>',' ',$q); // just in case the striptags missed it
	$q=str_replace('_',' ',$q); // underscores should be space
	$q=str_replace('.',' ',$q); // periods should be space 
	$q=str_replace('-',' ',$q); // dashes are wrong
	$q=str_replace('"',' ',$q); // dquotes are wrong
	$q=str_replace("'",' ',$q); // squotes are wrong
	$q=str_replace("`",' ',$q); // lquotes are wrong
	$q=str_replace('  ',' ',$q); // double spaces may have crept in
	$q=str_replace('  ',' ',$q); 
	$q=str_replace('  ',' ',$q); 
	
	$q=trim($q);
	if (empty($q)) return;
	
	
	// this is code to get the options
	$options = (array) get_option('widget_kpg_rgs');
	if (empty($options)) $options=array();
	$history=array();
	$maxlinks=5;
	$badwords=array();
	if (array_key_exists('history',$options)) $history=$options['history'];
    if (array_key_exists('maxlinks',$options)) $maxlinks=$options['maxlinks'];
    if (array_key_exists('badwords',$options)) $badwords=$options['badwords'];
	
	//eliminate a link if it contains a bad word.
	// check to see if any badwords are present
	$list=explode("\n",$badwords);
	for ($j=count($list)-1;$j>=0;$j--) {
		$list[$j]=trim($list[$j]);
		if (!empty($list[$j])) {
			if (strpos(' '.$q.' ',' '.$list[$j].' ')!==false) {
				// have a hit on the badwords list
				return;
			}
		}
	}
	
	
	
	
	
	// end options code
	
	if (empty($maxlinks)||$maxlinks>30||$maxlinks<0) $maxlinks=5;
	// use the string as a key, date as the data
	$q=mysql_real_escape_string($q);
	$history[$q]=time();
	// sort the array on time
	arsort($history);
	// get rid of the oldest
	
	if (count($history)>$maxlinks) array_pop($history); // a few more times in case someone messed with maxlinks
	if (count($history)>$maxlinks) array_pop($history);
	if (count($history)>$maxlinks) array_pop($history);
	if (count($history)>$maxlinks) array_pop($history);
	if (count($history)>$maxlinks) array_pop($history);
	$options['history']=$history;
	update_option('widget_kpg_rgs', $options);

}



function widget_kpg_rgs($args) {
	extract( $args );
	
	$options=array();
	$options = (array) get_option('widget_kpg_rgs');
	if (empty($options)) $options=array();
	$title="";
	$history=array();
	$kpg_rgs_nofollow="N";
	$kpg_rgs_usegoogle="N";
	$maxlinks=5;
	$badwords='';
	if (array_key_exists('title',$options)) $title = $options['title'];
	if (array_key_exists('kpg_rgs_nofollow',$options)) $kpg_rgs_nofollow=$options['kpg_rgs_nofollow'];
	if (array_key_exists('history',$options)) $history=$options['history'];
    if (array_key_exists('maxlinks',$options)) $maxlinks=$options['maxlinks'];
    if (array_key_exists('kpg_rgs_usegoogle',$options)) $kpg_rgs_usegoogle=$options['kpg_rgs_usegoogle'];
    if (array_key_exists('badwords',$options)) $badwords=$options['badwords'];

	// repair the old format
	$up=false;
	foreach ($history as $key=>$data) {
		if ($key=='0'||$key=='1'||$key=='2'||$key=='3'||$key=='4') {
		    unset($history[$key]);
			$history[$data]=time();
			$up=true;
		}
	}
	if ($up) {
		$options['history']=$history;
		update_option('widget_kpg_rgs', $options);
	}
	
	echo "\n\n<!-- Recent Google Search Widget -->\n\n";

	if (count($history)>0) {
		echo $args['before_widget'];
		if ($title!='') echo $before_title . $title . $after_title; 
		// display the recent searches
		echo "<ul>";
		//echo "<pre>";
		//print_r($options);
		//echo "</pre>";
		//echo " opt: '$kpg_rgs_usegoogle' <br/>";
		foreach ($history as $key=>$data) {
			$ll=urlencode(stripslashes($key));
			$nofollow="";
			if ($kpg_rgs_nofollow=='Y') {
				$nofollow='rel="nofollow"';
			}
			if ($kpg_rgs_usegoogle=='Y') {
				// custom search string
				$q='http://www.google.com/#q=site:'.parse_url(site_url(),PHP_URL_HOST)." ".$key;
		?>
			<li><a href="<?php echo $q; ?>" <?php echo $nofollow; ?>><?php echo $key; ?></a></li>
		<?php
			
			} else {
		?>
			<li><a href="<?php echo site_url(); ?>?s=<?php echo $ll; ?>" <?php echo $nofollow; ?>><?php echo $key; ?></a></li>	
		<?php
			}
		}
		echo "</ul>";
		echo $args['after_widget'];
	}
	return;
}


function widget_kpg_rgs_control() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	// this is code to get the options
	$options=array();
	$options = (array) get_option('widget_kpg_rgs');
	if (empty($options)) $options=array();
	$title="";
	$history=array();
	$kpg_rgs_nofollow="N";
	$kpg_rgs_usegoogle="N";
	$maxlinks=5;
	$badwords='';
	if (array_key_exists('title',$options)) $title = $options['title'];
	if (array_key_exists('kpg_rgs_nofollow',$options)) $kpg_rgs_nofollow=$options['kpg_rgs_nofollow'];
	if (array_key_exists('kpg_rgs_usegoogle',$options)) $kpg_rgs_usegoogle=$options['kpg_rgs_usegoogle'];
	if (array_key_exists('history',$options)) $history=$options['history'];
    if (array_key_exists('maxlinks',$options)) $maxlinks=$options['maxlinks'];
    if (array_key_exists('badwords',$options)) $badwords=$options['badwords'];
	// end options code
	
	if (array_key_exists('kpg_rgs_submit',$_POST)) {
		$title=strip_tags(stripslashes($_POST['kpg_rgs_title']));
		$maxlinks=$_POST['kpg_rgs_maxlinks'];
		$kpg_rgs_nofollow=$_POST['kpg__rgs_nofollow'];
		$kpg_rgs_usegoogle=$_POST['kpg_rgs_usegoogle'];
		$badwords=$_POST['badwords'];
		if (empty($maxlinks)||$maxlinks>30||$maxlinks<0) $maxlinks=5;
		if (empty($kpg_rgs_nofollow)) $kpg_rgs_nofollow='N';
		if (empty($kpg_rgs_usegoogle)) $kpg_rgs_usegoogle='N';
		if ($kpg_rgs_nofollow!='Y') $kpg_rgs_nofollow='N';
		if ($kpg_rgs_usegoogle!='Y') $kpg_rgs_usegoogle='N';
		$options['title']=$title;
		$options['maxlinks'] = $maxlinks;
		$options['kpg_rgs_nofollow'] = $kpg_rgs_nofollow;
		$options['kpg_rgs_usegoogle'] = $kpg_rgs_usegoogle;
		$options['badwords'] = $badwords;
		update_option('widget_kpg_rgs', $options);
	}
?>
<div style="text-align:right">
			
	<label for="kpg_rgs_title" style="line-height:25px;display:block;">
		<?php _e('Widget title:', 'widgets'); ?> 
		<input style="width: 200px;" type="text" id="kpg_rgs_title" name="kpg_rgs_title" value="<?php echo $title; ?>" />
	</label>
  <label for="kpg_rgs_maxlinks" style="line-height:25px;display:block;">
  <?php _e('Links to display (max 30):', 'widgets'); ?>
	<input style="width: 200px;" type="text" name="kpg_rgs_maxlinks" 
						value="<?php echo $maxlinks; ?>" />
  </label>
  <label for="kpg__rgs_nofollow" style="line-height:25px;display:block;">
  <?php _e('Use NoFollow on links:', 'widgets'); ?>
  <input type="checkbox" name="kpg__rgs_nofollow" 
						value="Y" <?php if ($kpg_rgs_nofollow=='Y'){ echo "checked=\"true\""; }?>" />
  </label>
  <label for="kpg_rgs_usegoogle" style="line-height:25px;display:block;">
  <?php _e('Use Google to search:', 'widgets'); ?>
  <input type="checkbox" name="kpg_rgs_usegoogle" 
						value="Y" <?php if ($kpg_rgs_usegoogle=='Y'){ echo "checked=\"true\""; }?>" />
  </label>
  <label for="badwords" style="line-height:25px;display:block;">
  List of blacklist words. One of these words will eliminate a link.<br/>
  <textarea name="badwords" cols="15" rows="9"><?php echo $badwords; ?></textarea> 
						
  </label>
			
			<input type="hidden" name="kpg_rgs_submit" id="kpg_rgs_submit" value="1" />
			
			</div>
	<small>note: the widget will not display on a page until there has actually been a user arriving by a search engine query.</small>
<?php
}

// admin menu panel
function  widget_kpg_rgs_admin_control() {
// this is the display of information about the page.
?>
<h2>Recent Google Searches</h2>
<h4>The Recent Google Searches Widget is installed and working correctly.</h4>
<div style="position:relative;float:right;width:35%;background-color:ivory;border:#333333 medium groove;padding:4px;margin-left:4px;">
    <p>This plugin is free and I expect nothing in return. If you would like to support my programming, you can buy my book of short stories.</p>
    <p>Some plugin authors ask for a donation. I ask you to spend a very small amount for something that you will enjoy. eBook versions for the Kindle and other book readers start at 99&cent;. The book is much better than you might think, and it has some very good science fiction writers saying some very nice things. <br/>
      <a target="_blank" href="http://www.blogseye.com/buy-the-book/">Error Message Eyes: A Programmer's Guide to the Digital Soul</a></p>
    <p>A link on your blog to one of my personal sites would also be appreciated.</p>
    <p><a target="_blank" href="http://www.WestNyackHoney.com">West Nyack Honey</a> (I keep bees and sell the honey)<br />
      <a target="_blank" href="http://www.cthreepo.com/blog">Wandering Blog</a> (My personal Blog) <br />
      <a target="_blank" href="http://www.cthreepo.com">Resources for Science Fiction</a> (Writing Science Fiction) <br />
      <a target="_blank" href="http://www.jt30.com">The JT30 Page</a> (Amplified Blues Harmonica) <br />
      <a target="_blank" href="http://www.harpamps.com">Harp Amps</a> (Vacuum Tube Amplifiers for Blues) <br />
      <a target="_blank" href="http://www.blogseye.com">Blog&apos;s Eye</a> (PHP coding) <br />
      <a target="_blank" href="http://www.cthreepo.com/bees">Bee Progress Beekeeping Blog</a> (My adventures as a new beekeeper) </p>
  </div>
<p>All options are set through the Widget Admin Panel</p>
<p>The Recent Google Searches Widget collects the query string from Google, Bing and Yahoo. It lists the last 5 as a sidebar widget so that users might click on them and find information using the WordPress search. In this way a user might find more pages that satisfy his search and other users may be interested in the same things that previous searchers used as queries.</p>

<p>The search engines will see the widget when they spider your site. They will then send you new traffic based on the traffic that you have received. This sets up a possitive feed back loop. I experienced a doubling of traffic within a week at one site.</p>
<p>There is a danger that your site will be ranked high for a popular keyword, but one that has little to do with your site and as a result the traffic will not be related to your core keywords. I would suggest adding content to match and give the searching public what they want.</p>
<h4>For questions and support please check my website <a href="http://www.blogseye.com/i-make-plugins/exit-screen-plugin/">BlogsEye.com</a>.</h4>
<?php
}


function widget_kpg_rgs_init() {
	register_sidebar_widget(array('Recent Gooogle Searches Widget', 'widgets'), 'widget_kpg_rgs');
	register_widget_control(array('Recent Gooogle Searches Widget', 'widgets'), 'widget_kpg_rgs_control');
}
function widget_kpg_rgs_admin_menu() {
   add_options_page('Recent Gooogle Searches', 'Recent Gooogle Searches', 'manage_options','Recent-Gooogle-Searches','widget_kpg_rgs_admin_control');
}


// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_kpg_rgs_init');
add_action('init', 'widget_kpg_collect_data_rgs');
add_action('admin_menu', 'widget_kpg_rgs_admin_menu');

?>
