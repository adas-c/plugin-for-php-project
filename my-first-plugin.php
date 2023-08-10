<?php 

/*
  Plugin Name: Word tracking 
  Version: 1.0
  Author: Adam
  Text Domain: wcpdomain
  Domain Path: /languages
*/

class WordAndTimePlugin {
  function __construct() {
    add_action('admin_menu', array($this, 'adminPage'));
    add_action('admin_init', array($this, 'settings'));
    add_filter('the_content', array($this, 'ifWrap'));
    add_action('init', array($this, 'languages'));
  }

  function languages() {
    load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
  }

  function ifWrap($content) {
    if (is_main_query() && is_single() && (get_option('wcp_wordcount', '1') || get_option('wcp_character', '1') || get_option('wcp_readtime', '1'))) {
      return $this->createHTML($content);
    }
    return $content;
  }

  function createHTML($content) { 
   $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

   if (get_option('wcp_wordcount', '1') || get_option('wcp_readtime', '1')) {
    $wordCount = str_word_count(strip_tags($content));
   }

   if (get_option('wcp_wordcount', '1')) {
    $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . __('words', 'wcpdomain') . '.<br>';
   }

   if (get_option('wcp_character', '1')) {
    $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>';
   }
   
   if (get_option('wcp_readtime', '1')) {
    $html .= 'This post will take about ' . round($wordCount/225) . ' minute(s) to read.<br>';
   }

   $html .= '</p>';

   if (get_option('wcp_location', '0') == '0') {
    return $html . $content;
   } 

   return $content . $html;

  }

  function settings() {
    add_settings_section('wcp_first_section', null, null, 'word-count-setting-page');

    // field for display location
    add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-setting-page', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));
    // field for headline text
    add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-setting-page', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));
    // field for wordcount
    add_settings_field('wcp_wordcount', 'Word Count', array($this, 'wordCountHTML'), 'word-count-setting-page', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    // field for character count
    add_settings_field('wcp_character', 'Character Count', array($this, 'characterCountHTML'), 'word-count-setting-page', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_character', array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
    // field for read time
    add_settings_field('wcp_readtime', 'Read Time', array($this, 'readTimeHTML'), 'word-count-setting-page', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
  }

  function sanitizeLocation($input) {
    if ($input != 0 && $input != 1) {
      add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either beginning or end.');
      return get_option('wcp_location');
    }
    return $input;
  }

  function readTimeHTML() { ?>
    <input type="checkbox" value="1" name="wcp_readtime" <?php checked(get_option('wcp_readtime', '1')); ?>>
  <?php }

  function characterCountHTML() { ?>
    <input type="checkbox" name="wcp_character" value="1" <?php checked(get_option('wcp_character'), '1'); ?>>
  <?php }

  function wordCountHTML() { ?>
    <input type="checkbox" name="wcp_wordcount" value="1" <?php checked(get_option('wcp_wordcount', '1')); ?>>
  <?php }

  function headlineHTML() { ?>
    <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')); ?>">
  <?php }

  function locationHTML() { ?>
    <select name="wcp_location">
      <option <?php selected(get_option('wcp_location'), '0'); ?> value="0">Beginning of post</option>
      <option <?php selected(get_option('wcp_location'), '1'); ?> value="1">End of post</option>
    </select>
  <?php }

  function adminPage() {
    add_options_page('Word Count', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-setting-page', array($this, 'ourHTML'));
  }

  function ourHTML() { ?>
    <div class="wrap">
      <h1>Word Count Settings</h1>
      <form action="options.php" method="POST">
        <?php 
          settings_fields('wordcountplugin');
          do_settings_sections('word-count-setting-page');
          submit_button();
        ?>
      </form>

    </div> 
  <?php }

}

$wordCountAndTimePlugin = new WordAndTimePlugin();

