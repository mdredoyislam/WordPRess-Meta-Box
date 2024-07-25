<?php
/*
Plugin Name: Our MetaBox
Plugin URI: https://redoyit.com/
Description: Used by millions, WordCount is quite possibly the best way in the world to <strong>protect your blog from spam</strong>. WordCount Anti-spam keeps your site protected even while you sleep. To get started: activate the WordCount plugin and then go to your WordCount Settings page to set up your API key.
Version: 5.3
Requires at least: 5.8
Requires PHP: 5.6.20
Author: Md. Redoy Islam
Author URI: https://redoyit.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: ourmetabox
Domain Path: /languages
*/

/*
    function wordcount_activation_hook(){}
    register_activation_hook(__FILE__, "wordcount_activation_hook");

    function wordcount_deactivation_hook(){}
    register_deactivation_hook(__FILE__, "wordcount_deactivation_hook");
*/

define('OMB_ASSETS_DIR', plugin_dir_url(__FILE__).'assets/');

class OurMetabox{

    public function __construct(){
        add_action('plugin_loaded', array($this, 'omb_load_textdomain'));
        add_action('admin_menu', array($this, 'omb_add_metabox'));
        add_action('save_post', array($this, 'omb_save_metabox'));
    }
    private function is_secured($nonce_field, $action, $post_id){
        $nonce = isset($_POST[$nonce_field])? $_POST[$nonce_field]:'';
        if($nonce==''){
            return false;
        }
        if(!wp_verify_nonce($nonce, $action)){
            return false;
        }
        if(!current_user_can('edit_post', $post_id)){
            return false;
        }
        if(wp_is_post_autosave($post_id)){
            return false;
        }
        if(wp_is_post_revision($post_id)){
            return false;
        }
        return true;
    }
    function omb_save_metabox($post_id){
        if(! $this->is_secured('omb_location_field', 'omb_location', $post_id)){
            return $post_id;
        }
        $location = isset($_POST['omb_location'])? $_POST['omb_location']:'';
        $country = isset($_POST['omb_country'])? $_POST['omb_country']:'';
        $is_favorite = isset($_POST['omb_is_favorite'])? $_POST['omb_is_favorite']:0;
        $colors = isset($_POST['omb_clr'])? $_POST['omb_clr']:array();
        $colors2 = isset($_POST['omb_color'])? $_POST['omb_color']:array();
        $divission = isset($_POST['omb_divission'])? $_POST['omb_divission']:array();

        if($location==''){
            return $post_id;
        }
        
        if($country==''){
            return $post_id;
        }

        $location = sanitize_text_field($location);
        $country = sanitize_text_field($country);

        update_post_meta( $post_id, 'omb_location', $location);
        update_post_meta( $post_id, 'omb_country', $country);
        update_post_meta( $post_id, 'omb_is_favorite', $is_favorite);
        update_post_meta( $post_id, 'omb_clr', $colors);
        update_post_meta( $post_id, 'omb_color', $colors2);
        update_post_meta( $post_id, 'omb_divission', $divission);
    }

    function omb_add_metabox(){
        add_meta_box('omb_post_location', 
        __('Location Info', 'ourmetabox'),
            array($this, 'omb_display_metabox'),
            'post',
            'normal',
            'default'
        );
    }

    function omb_display_metabox($post){

        $location = get_post_meta($post->ID, 'omb_location', true);
        $country = get_post_meta($post->ID, 'omb_country', true);

        $is_favorite = get_post_meta($post->ID, 'omb_is_favorite', true);
        $checked = $is_favorite ==1?'checked':'';

        $saved_colors = get_post_meta($post->ID, 'omb_clr', true);
        $saved_color = get_post_meta($post->ID, 'omb_color', true);
        $saved_divission = get_post_meta($post->ID, 'omb_divission', true);

        $label1 = __('Location', 'ourmetabox');
        $label2 = __('Country', 'ourmetabox');
        $label3 = __('Is Favorite', 'ourmetabox');
        $label4 = __('Colors', 'ourmetabox');
        $label5 = __('Divission', 'ourmetabox');

        $colors = array('red', 'green', 'blue', 'yellow', 'magenta', 'pink', 'black');
        $divissions = array('Dhaka', 'Chitagong', 'Comilla', 'Rajshahi', 'Barishal', 'Rangpur', 'Kulna', 'Moymongshing');

        wp_nonce_field('omb_location', 'omb_location_field'); 

        $metabox_html = <<<EOD
            <p>
                <label for="omb_location">{$label1}</label>
                <input type="text" name="omb_location" id="omb_location" value="{$location}" />
                <br>
                <label for="omb_country">{$label2}</label>
                <input type="text" name="omb_country" id="omb_country" value="{$country}" />
            </p>
            <br><br>
            <p>
                <label for="omb_is_favorite">{$label3}</label>
                <input type="checkbox" name="omb_is_favorite" id="omb_is_favorite" value="1" {$checked}/>
            </p>
            <p>
                <label>{$label4} : </label>
        EOD;

        foreach($colors as $color){
            $_color = ucwords($color);
            $checked = in_array($color, $saved_colors, true) ? 'checked' : '';
            $metabox_html .= <<<EOD
            <label for="omb_clr_{$color}">{$_color}</label>
            <input type="checkbox" name="omb_clr[]" id="omb_clr_{$color}" value="{$color}" {$checked}/>
            EOD;
        }

        $metabox_html .= "</p>";

        $metabox_html .= <<<EOD
            <p> <label>{$label4} : </label>
        EOD;
        foreach($colors as $color){
            $_color = ucwords($color);
            $checked = ($color == $saved_color)?"checked='checked'":'';
            $metabox_html .= <<<EOD
            <label for="omb_color_{$color}">{$_color}</label>
            <input type="radio" name="omb_color" id="omb_color_{$color}" value="{$color}" {$checked}/> 
            EOD;
        }

        $metabox_html .= "</p>";


        $metabox_html .= <<<EOD
            <p> <label>{$label5} : </label>
            <select name="omb_divission">
        EOD;
        foreach($divissions as $divission){
            $_divission = ucwords($divission);
            $selected = ($divission == $saved_divission)?'selected':'';
            $metabox_html .= <<<EOD
                <option value="{$divission}" {$selected}>{$_divission}</option>
            EOD;
        }

        $metabox_html .= "</select></p>";


        echo $metabox_html;
    }

    function omb_load_textdomain(){ 
        load_plugin_textdomain('ourmetabox', false, dirname(__FILE__) . '/languages');
    }
}
new OurMetabox();

//add_filter('wp_calculate_image_srcset', '__return_null');