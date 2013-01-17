<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/*
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2012 Add This, LLC                                    |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

/**
 * Plugin Name: AddThis Trending Content Widget
 * Plugin URI: http://www.addthis.com
 * Description: Boost page views by promoting top trending content from your blog or website. Please make sure that you have <a href="http://wordpress.org/extend/plugins/addthis/">AddThis Share Plugin</a> installed on your site.
 * Version: 1.0
 *
 * Author: The AddThis Team
 * Author URI: http://www.addthis.com/blog
 */

define('PLUGIN_DIR_PATH', plugin_dir_url(__FILE__) );

class AddThisTrendingWidget {

    function __construct() {
        add_action('widgets_init', array($this, 'widgets_init'));
        //wp_enqueue_script('minicolor', plugins_url('', basename(dirname(__FILE__))).'/addthis-trending/js/jquery.miniColors.js');
        wp_enqueue_style('minicolor', PLUGIN_DIR_PATH . '/css/jquery.miniColors.css');
        add_action('admin_print_styles-widgets.php', array($this, 'admin_print_styles'));
        add_action( 'admin_enqueue_scripts', array($this, 'add_this_trending_admin_enqueue_scripts') );
        wp_enqueue_script("jquery");
    }

    function widgets_init() {
        register_widget('AddThisTrendingSidebarWidget');
    }
     
    function add_this_trending_admin_enqueue_scripts(){ 
        wp_enqueue_script('minicolor', PLUGIN_DIR_PATH .' /js/jquery.miniColors.js');
        wp_enqueue_script('widgets-php', PLUGIN_DIR_PATH . '/js/widgets-php.js');
    }

    function admin_print_styles() {
        $style_location = apply_filters('addthis_trending_files_uri', PLUGIN_DIR_PATH . '/css/widgets-php.css');
        $js_location = apply_filters('addthis_trending_files_uri', PLUGIN_DIR_PATH . '/js/widgets-php.js');
        wp_enqueue_style('addthis_trending', $style_location, array(), 0);
        wp_enqueue_script('addthis_trending', $js_location, array('jquery'), 0);
        
    }

}

new AddThisTrendingWidget();

/**
 * Basic Trending Options class shared by the Trending Plugin and Widget
 */
class TrendingOptions {

    private static $_instance = null;
    private $_styles = null;

    private function __construct() {
        $this->_styles = array(
            'trending' => array('Trending'),
            'shared' => array('Most Shared'),
            'clicked' => array('Most Clicked'));
        $this->_timePeriod  = array(
            'month' => array('Last Month'),
            'week' => array('Last Week'),
            'day' => array('Last Day'));
        $this->_links   =   array(
            '1' => array('1'),'2' => array('2'),'3' => array('3'),'4' => array('4'),'5' => array('5'),'6' => array('6'),
            '7' => array('7'),'8' => array('8'),'9' => array('9'),'10' => array('10'),'11' => array('11'),'12' => array('12'),
            '13' => array('13'),'14' => array('14'),'15' => array('15'),'16' => array('16'),'17' => array('17'),'18' => array('18'),
            '19' => array('19'), '20' => array('20'));
    }

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new TrendingOptions();
        }
        return self::$_instance;
    }

    public function getStyles() {
        return $this->_styles;
    }

    public function getDefaultStyle() {
        return $this->_defaultStyle;
    }

    public function getTimePeriod(){
        return $this->_timePeriod;
    }
    
    public function getDefaultTime() {
        return $this->_defaultTime;
    }  
    
    public function getLinks() {
        return $this->_links;
    }
}

/**
 * AddThis Trending Plugin and its settings
 */
class AddThisTrendingPlugin {

    private $_trendingOptions = null;

    public function __construct() {
        $this->_trendingOptions = TrendingOptions::getInstance();
        add_filter('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_trending_settings'));
    }

    function register_trending_settings() {
        register_setting('addthis_trending_settings', 'addthis_trending_settings', array($this, 'save_settings'));
    }

    /**
     * Callback for saving the Trending plugin settings
     * Sanitize the options and save it
     * 
     * @param array $input
     * @return array $options
     */
    function save_settings($input) {
        $options = array();
        $allowedKeys = array('title', 'style', 'time', 'height', 'width', 'links', 'repeated', 'bg_color', 'border');
        foreach ($input as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $value = sanitize_text_field($value);
                $options[$key] = $value;
            }
        }
        return $options;
    }

    /**
     * AddThis Trending Admin menu
     */
    function admin_menu() {
        if (is_admin()) {
            $trending = add_options_page('AddThis Plugin Options', 'AddThis Trending Content', 'manage_options', basename(__FILE__), array($this, 'options'));
        }
    }

    /**
     * AddThis Trending settings page
     */
    function options() {
        wp_enqueue_style('adminstyles', PLUGIN_DIR_PATH.'css/admin-options.css');
        if (version_compare(get_bloginfo('version'), '3.3', '<')) {
            wp_head();
        }
        $style = $this->_trendingOptions->getDefaultStyle();
        $time  = $this->_trendingOptions->getDefaultTime();
        
        $commonTrendingOptions = get_option('addthis_trending_settings');
        $trendingWidgetOptions = get_option('widget_addthis-trending-widget');
        /**
         * Restore from widget settings if possible
         */
        if (($commonTrendingOptions == false || empty($commonTrendingOptions)) && $trendingWidgetOptions != false) {
            $restoreOptions = array('title' => $title, 'style' => $style, 'time' => $time);
            add_option('addthis_trending_settings', $restoreOptions);
        }
        $this->displayOptionsForm();
    }

    /**
     * Display the Trending Options Form
     * @global AddThis_addjs $addthis_addjs
     * @param TrendingOptions $buttonOptions
     * @param string $style
     * @param string $title
     */
    function displayOptionsForm() {
        global $addthis_addjs;
        $getOptionValues    =   get_option('addthis_trending_settings');
        $style              =   $getOptionValues['style'];
        $title              =   $getOptionValues['title'];
        $time               =   $getOptionValues['time'];
        $height             =   ($getOptionValues['height'])?$getOptionValues['height']:'auto';
        $width              =   ($getOptionValues['width'])?$getOptionValues['width']:'auto';
        $links              =   $getOptionValues['links'];
        $repeated           =   $getOptionValues['repeated'];
        $bg_color           =   $getOptionValues['bg_color'];
        $border             =   $getOptionValues['border'];
        
        $bg_color_checked       =   '';
        $bg_color_disabled      =   'disabled="disabled"'; 
        if($bg_color){
            $bg_color_checked   =   'checked="checked"';
            $bg_color_disabled  =   ''; 
        }
         
        $border_checked         =   '';
        $border_disabled        =   'disabled="disabled"'; 
        if($border){
            $border_checked     =   'checked="checked"';      
            $border_disabled    =   ''; 
        }
        ?>
        <p class="top-text"><?php echo $addthis_addjs->getAtPluginPromoText(); ?></p>
        <img alt='addthis' src="//cache.addthis.com/icons/v1/thumbs/32x32/more.png" class="header-img"/>
        <span class="addthis-title">AddThis</span> <span class="addthis-plugin-name">Trending</span>
        <?php 
            $show_options_class = '';
            if(!is_plugin_active('addthis/addthis_social_widget.php')){
                $show_options_class = 'hidden';
                echo "<br><br><span style=\"font-size:13px;\">Please make sure that you have the <a href='http://wordpress.org/support/plugin/addthis' target='_blank' title='Share plugin'>Share plugin</a> installed on your site. </span>";
            }
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('addthis_trending_settings');

            echo '<table class="trending-container '.$show_options_class.'">
                    <tr>
                        <td>
                            <p><h5><label for="title">' . __('Title:', 'addthis') . '</label></h5></p>
                            <input style="width:250px" class="widefat" id="addthis-trending-content-title" name="addthis_trending_settings[title]" type="text" value="'.$title.'" /> ';   
            echo '      </td>
                        <td style="padding-left:40px;">
                            <p>&nbsp;</p>
                            
                        </td>                
                    </tr>                
                    <tr>
                        <td>
                            <p><h5><label for="style">' . __('Feed to display:', 'addthis') . '</label></h5></p>
                            <select id="style" name="addthis_trending_settings[style]">';
            foreach ($this->_trendingOptions->getStyles() as $c => $n) {
                $selected = ($style == $c) ? ' selected="selected" ' : '';
                echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
            }
            echo '</select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td>
                        <p><h5><label for="time">' . __('Time period:') . '</label></h5></p>
                        <select id="time" name="addthis_trending_settings[time]">';
            foreach ($this->_trendingOptions->getTimePeriod() as $c => $n) {
                $selected = ($time == $c) ? ' selected="selected" ' : '';
                echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
            }
            echo '</select>
                            </td><td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="atmore">
                            <table class="hwl">
                                <tr>
                                    <td>
                                        <p><h5><label for="title">' . __('Height:') . '</label></h5></p>
                                        <input style="width:50px" class="widefat trending_height" id="height" name="addthis_trending_settings[height]" type="text" value="'.$height.'" />                                    
                                    </td>
                                    <td>
                                        <p><h5><label for="title">' . __('Width:') . '</label></h5></p>
                                        <input style="width:50px" class="widefat trending_width" id="width" name="addthis_trending_settings[width]" type="text" value="'.$width.'" />    
                                    </td>   
                                    <td>
                                        <p><h5><label for="title">' . __('Links:') . '</label></h5></p>
                                        <select id="links" name="addthis_trending_settings[links]">';
                                        if(!isset($links)) {
                                            $links = 4;
                                        }
                                        foreach ($this->_trendingOptions->getLinks() as $c => $n) {
                                            $selected = ($links == $c) ? ' selected="selected" ' : '';
                                            echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
                                        }
                                  echo '</select>
                                    </td>                                     
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="atmore">
                            <p><h5><label for="title">' . __('Hide repeated text:') . '</label></h5></p>
                            <input style="width:250px" class="widefat" id="repeated" name="addthis_trending_settings[repeated]" type="text" value="'.$repeated.'" />    
                        </td>    
                    </tr>'; 
                    echo '<tr>
                        <td class="atmore">
                            <p><input type="checkbox" ' .$bg_color_checked. ' id="bg_check" /><strong><label for="bg_check">' . __('Background:') . '</label></strong></p>
                             <input style="width:160px" ' .$bg_color_disabled. ' class="widefat" id="bg_color" name="addthis_trending_settings[bg_color]" type="text" value="'.$bg_color.'" />    
                        </td>
                    </tr>
                    <tr>
                        <td class="atmore">
                            <p><input type="checkbox" ' .$border_checked. ' id="border_check"/><strong><label for="title">' . __('Border:') . '</label></strong></p>
                             <input style="width:160px" ' .$border_disabled. ' class="widefat" id="border" name="addthis_trending_settings[border]" type="text" value="'.$border.'" />    
                        </td>
                    </tr>                    
                    <script>jQuery("#bg_color").miniColors(); jQuery("#border").miniColors();</script>
                    <tr>
<td>';
       ?>
           <?php
           echo '
       </td><td>&nbsp;</td>
       </tr>
       <tr>
       <td colspan="2">
       <p><input class="button-secondary" type="submit" name="Submit" value="';
           echo _e('Save');
           echo '" /> </p></td> </tr> </table>
        <div style="float:left; width:540px;">
            <table class="preview_container '.$show_options_class.'">
                <tr><td style="padding-left:10px;">
                    <p><strong>' . __('AddThis Profile ID:', 'addthis') . '</strong>
                    <input style="width:160px" disabled="disabled" class="widefat" type="text" value="' . $addthis_addjs->pubid . '" /></p>
                </td></tr>
                <tr><td><h3 class="helv org">Preview</h3></td></tr>
                <tr><td class="info">
                    This is an example of what it will look like.
                </td></tr>
                <tr>
                    <td>
                        <div id="previewContainer" class="previewbox">
                            <div id="addthis_trendingcontent_demo" class="addthis_trendingcontent_demo" style="border: 1px solid rgb(74, 41, 41);">
                                <h3 id="addthis-trending-preview-title" class="widget-title"></h3>
                                <ul class="addthis-content-list">
                                    <li class="addthis-content-row"><a class="addthis-content-link" href="">Sample link to your most popular content will appear here</a></li>
                                    <li class="addthis-content-row"><a class="addthis-content-link" href="">Sample link to your most popular content will appear here</a></li>
                                </ul>
                                <div class="addthis-content-footer">
                                        Powered by <a id="atlogo-sm" href="http://www.addthis.com/?utm_source=tcw&amp;utm_medium=img&amp;utm_content=AT_main_WT&amp;utm_campaign=AT_main" target="_blank" class="at-whatsthis at-logo">AddThis</a>
                                </div>
                            </div>                    
                        </div>
                    </td>
                </tr>
            </table>   
        </div>
        </form>
        <div style="float:left;width:97%;padding:5px;">To show AddThis Trending Contents in your website, please drag and drop <strong>AddThis Trending Content</strong> widget from <strong> Appearance </strong> > <strong>Widget</strong>. <br/>For detailed instructions on how to get your Profile ID and enable Content Feeds, refer <a href="http://support.addthis.com/customer/portal/articles/381268-content-feeds#public_data" target="_blank">AddThis Support</a>.</div>';
    }
}

add_action('init', 'initialize_addthis_trending_plugin');
register_deactivation_hook(__FILE__, 'addthis_trending_remove');

/**
 * Deactivation callback
 */
function addthis_trending_remove() {
    delete_option('addthis_trending_settings');
}

/**
 * Plugin initialization callback
 */
function initialize_addthis_trending_plugin() {
    new AddThisTrendingPlugin();
}

class AddThisTrendingSidebarWidget extends WP_Widget {

    function AddThisTrendingSidebarWidget() {
        $widget_ops = array('classname' => 'attrendingwidget', 'description' => 'Connect fans and trendingers with your profiles on top social services');

        /* Widget control settings. */
        $control_ops = array('width' => 260);

        /* Create the widget. */
        $this->WP_Widget('addthis-trending-widget', 'AddThis Trending Content', $widget_ops, $control_ops);
    }

    /**
     * Echo's out the content of our widget
     */
    function widget($args, $instance) {
        extract($args);
        $bg_code    =   '';
        $border_code = '';
        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget;
        if ($title)
            echo $before_title . $title . $after_title;
        unset($instance['profile']);
        unset($instance['title']);
        $styles = TrendingOptions::getInstance()->getStyles();
        $class = $styles[$instance['style']][1];
        echo '<div class="' . $class . ' addthis_toolbox">';
        echo '<div id="addthis_trendingcontent" class="'. $args['widget_id']. '"></div>';
        if('on' == $instance['bg_check'])
            $bg_code    =   ('' != $instance['bg_color'])?'background: "'.$instance['bg_color'].'",':'';
        
        if('on' == $instance['border_check'])
            $border_code    =   ('' != $instance['border_check'])?'border: "'.$instance['border'].'",':'';
        
        echo '<script type="text/javascript"> 
                jQuery(document).ready(function() {
                    addthis.box(".'.$args['widget_id'].'", {
                    feed_title : "",
                    feed_type : "'.$instance['style'].'",
                    feed_period : "'.$instance['time'].'",
                    num_links : '.$instance['links'].',
                    height : "'.$instance['height'].'",
                    width : "'.$instance['width'].'",
                    '.$bg_code.'
                    '.$border_code.'
                    remove : "'.$instance['repeated'].'"    
                    });
                 });   
                </script>';

        // end the div
        echo '</div>';

        echo $after_widget;
    }

    /**
     * Update this instance
     */
    function update($new_instance, $old_instance) {
        $instance = array();
        $styles = TrendingOptions::getInstance()->getStyles();
        $time   = TrendingOptions::getInstance()->getTimePeriod();
        global $addthis_addjs;
        if (isset($new_instance['profile']) && substr($new_instance['profile'], 0, 2) != 'wp-') {
            $addthis_addjs->setProfileId($new_instance['profile']);
        }

        $style = $new_instance['style'];
        if (isset($styles[$style])) {
            $instance['style'] = $style;
        } else {
            $instance['style'] = isset($styles[$style]);
        }
        
        $timep = $new_instance['time'];
        if(isset($timep[$time])) {
            $instance['time'] = $timep;
        } else {
            $instance['time'] = isset($timep[$time]);
        }
        print_r($new_instance);
        $title                  =   sanitize_text_field($new_instance['title']);
        $instance['title']      =   $title;
        
        $height                 =   sanitize_text_field($new_instance['height']);
        $instance['height']     =   ($height)?$height:'auto';
        
        $width                  =   sanitize_text_field($new_instance['width']);
        $instance['width']      =   ($width)?$width:'auto';
        
        $links                  =   $new_instance['links'];
        $instance['links']      =   $links;    
        
        $repeated               =   sanitize_text_field($new_instance['repeated']);
        $instance['repeated']   =   $repeated;    
        
        $bg_color               =   $new_instance['bg_color']; 
        
        if('on' != $new_instance['bg_check']){ 
            $instance['bg_color']   =   'none'; 
        }else{
            $instance['bg_color']   =   $bg_color;     
        }
        
        $border                 =   $new_instance['border'];
        
        if('on' != $new_instance['border_check']){ 
            $instance['border']   =   'none'; 
        }else{
            $instance['border']   =   $border;     
        }

        $instance['bg_check']      = $new_instance['bg_check']; 
        $instance['border_check']  = $new_instance['border_check']; 
        return $instance;
    }

    /**
     *  The form with the widget options
     */
    function form($instance) { 
        global $addthis_addjs; 
        $title              =   $instance['title'];
        $style              =   $instance['style'];
        $time               =   $instance['time'];
        $height             =   $instance['height'];
        $width              =   $instance['width'];
        $links              =   $instance['links'];
        $repeated           =   $instance['repeated'];
        $bg_color           =   $instance['bg_color'];
        $border             =   $instance['border'];        
        $bg_check           =   $instance['bg_check'];        
        $border_check       =   $instance['border_check'];        
        
        $addthis_trending_options = get_option('addthis_trending_settings');  
        $trendingOptions = TrendingOptions::getInstance(); 
        
        $title     = ($title) ? $title : $addthis_trending_options['title'];
        $style     = ($style) ? $style : $addthis_trending_options['style'];
        $time      = ($time) ? $time : $addthis_trending_options['time'];
        $height    = ($height) ? $height : $addthis_trending_options['height'];
        $width     = ($width) ? $width : $addthis_trending_options['width'];
        $links     = ($links) ? $links : $addthis_trending_options['links'];
        $repeated  = ($repeated) ? $repeated : $addthis_trending_options['repeated'];
        $bg_color  = ($bg_color) ? $bg_color : $addthis_trending_options['bg_color'];
        $border    = ($border) ? $border : $addthis_trending_options['border'];
        $bg_check  = ($bg_check) ? $bg_check : $addthis_trending_options['bg_check'];
        $border_check  = ($border_check) ? $border_check : $addthis_trending_options['border_check'];

        $bg_color_checked       =   '';
        $bg_color_disabled      =   'disabled="disabled"'; 
        if($bg_color && ('on' == $bg_check)){
            $bg_color_checked   =   'checked="checked"';
            $bg_color_disabled  =   ''; 
        }
        $border_checked         =   '';
        $border_disabled        =   'disabled="disabled"'; 
        if($border && ('on' == $border_check)){
            $border_checked     =   'checked="checked"';      
            $border_disabled    =   ''; 
        }
        
        // enable colorpicker if already color is seleccted
        
        $enable_bg_cp           =   '';
        $enable_border_cp       =   '';
        // bg color picker
        if($bg_color_checked){
            $enable_bg_cp       =   'enablePicker("'.$this->get_field_name('bg_check').'", "'.$this->get_field_id('bg_color').'", 1);';
        }
        
        if($border_checked){
            $enable_border_cp   =   'enablePicker("'.$this->get_field_name('border_check').'", "'.$this->get_field_id('border').'", 1);';
        }
            
        echo $addthis_addjs->getAtPluginPromoText(); 
        echo '
        <table width="100%" class="trending-table">
            <tr>
                <td>
                    <h5><label for="title">' . __('Title:', 'addthis') . '</label></h5>
                    <input style="width:250px" class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$title.'" /> 
                </td>
            </tr>          
            <tr>
                <td style="width:290px">
                    <p><strong><label for="' . $this->get_field_id('style') . '">' . __('Feed to display:', 'addthis') . '</label></strong></p>
                    <select id="toolbox-style" name="' . $this->get_field_name('style') . '">';
        foreach ($trendingOptions->getStyles() as $c => $n) {
            $selected = ($style == $c) ? ' selected="selected" ' : '';
            echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
        }
        echo '</select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                <p><strong><label for="' . $this->get_field_id('time') . '">' . __('Time period:') . '</label></strong></p>
                    <select id="time" name="'. $this->get_field_name('time'). '">';
            foreach ($trendingOptions->getTimePeriod() as $c => $n) {
                $selected = ($time == $c) ? ' selected="selected" ' : '';
                echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
            }
            echo '</select>
                    </td>
                    </tr>
            ';
          echo ' <tr>
                    <td colspan="2" class="'.$this->get_field_id('atmore').'_atmore" style="padding-top:0px;">
                        <table class="hwl">
                            <tr>
                                <td class="'.$this->get_field_id('atmore').'_atmore">
                                    <p><strong><label for="title">' . __('Height:') . '</label></strong></p>
                                    <input style="width:50px" onblur="javascript:valDimension(this, \''.$this->get_field_id('savewidget').'\');" class="widefat trending_height" id="'.$this->get_field_id('height').'" name="' .$this->get_field_name('height') .'" type="text" value="'.$height.'" />    
                                </td>
                                <td class="'.$this->get_field_id('atmore').'_atmore">
                                    <p><strong><label for="title">' . __('Width:') . '</label></strong></p>
                                    <input style="width:50px" onblur="javascript:valDimension(this, \''.$this->get_field_id('savewidget').'\');" class="widefat trending_width" id="'.$this->get_field_id('width').'" name="'. $this->get_field_name('width') .'" type="text" value="'.$width.'" />    
                                </td>  
                                <td class="'.$this->get_field_id('atmore').'_atmore">
                                    <p><strong><label for="title">' . __('Links:') . '</label></strong></p>
                                    <select id="links" name="'. $this->get_field_name('links') .'">';
                                    foreach ($trendingOptions->getLinks() as $c => $n) {
                                        $selected = ($links == $c) ? ' selected="selected" ' : '';
                                        echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
                                    }
                              echo '</select></td>                                    
                            </tr>
                        </table>
                    </td>
                </tr>';
         echo '
                <tr>
                    <td class="'.$this->get_field_id('atmore').'_atmore">
                        <p><strong><label for="title">' . __('Hide repeated text:') . '</label></strong></p>
                        <input style="width:160px" class="widefat" id="repeated" name="'. $this->get_field_name('repeated') .'" type="text" value="'.$repeated.'" />    
                    </td>    
                </tr>
                <tr>
                    <td class="'.$this->get_field_id('atmore').'_atmore">
                        <p><input type="checkbox" name="' .$this->get_field_name('bg_check').'" id="'. $this->get_field_id('bg_check') . '" '.$bg_color_checked.' onclick="javascript:enablePicker(this, \''.$this->get_field_id('bg_color').'\');"/><strong><label for="title" style="padding-left:5px;">' . __('Background:') . '</label></strong></p>
                         <input style="width:160px" '.$bg_color_disabled.' class="widefat" id="'.$this->get_field_id('bg_color').'" name="'. $this->get_field_name('bg_color') .'" type="text" value="'.$bg_color.'" />    
                    </td>
                </tr>
                <tr>
                    <td class="'.$this->get_field_id('atmore').'_atmore">
                        <p><input type="checkbox" name="' .$this->get_field_name('border_check').'" id="'. $this->get_field_id('border_check') . '" '.$border_checked.' onclick="javascript:enablePicker(this, \''.$this->get_field_id('border').'\');"/><strong><label for="title"  style="padding-left:5px;">' . __('Border:') . '</label></strong></p>
                         <input style="width:160px" '.$border_disabled.' class="widefat" id="'.$this->get_field_id('border').'" name="'. $this->get_field_name('border') .'" type="text" value="'.$border.'" />    
                    </td>
                </tr>                    
                <script>'.$enable_bg_cp.' '.$enable_border_cp.' </script>';
                echo '</td></tr><tr><td colspan="2">';    
                echo "</td></tr></table>";                
    }

}

// Setup our shared resources early 
add_action('init', 'addthis_trending_early', 1);

function addthis_trending_early() {
    global $addthis_addjs;
    if (!isset($addthis_addjs)) {
        require('includes/addthis_addjs.php');
        $addthis_options = get_option('addthis_settings');
        $addthis_addjs = new AddThis_addjs($addthis_options);
    } elseif (!method_exists($addthis_addjs, 'getAtPluginPromoText')) {
        require('includes/addthis_addjs_extender.php');
        $addthis_addjs = new AddThis_addjs_extender($addthis_options);
    }
}
