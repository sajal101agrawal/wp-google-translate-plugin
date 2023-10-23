<?php
/*
Plugin Name: Google Translate Plugin
Version: 1.0.0
Description: Use this plugin to translate your site for free.
Author URI: #
*/

include( plugin_dir_path( __FILE__ ) . 'widget.php');

class google_language_translator {

  public $languages_array;

  public function __construct() {

    $this->languages_array = array (
      'af' => 'Afrikaans',
      'sq' => 'Albanian',
      'am' => 'Amharic',
      'ar' => 'Arabic',
      'hy' => 'Armenian',
      'az' => 'Azerbaijani',
      'eu' => 'Basque',
      'be' => 'Belarusian',
      'bn' => 'Bengali',
      'bs' => 'Bosnian',
      'bg' => 'Bulgarian',
      'ca' => 'Catalan',
      'ceb' => 'Cebuano',
      'ny' => 'Chichewa',
      'zh-CN' => 'Chinese (Simplified)',
      'zh-TW' => 'Chinese (Traditional)',
      'co' => 'Corsican',
      'hr' => 'Croatian',
      'cs' => 'Czech',
      'da' => 'Danish',
      'nl' => 'Dutch',
      'en' => 'English',
      'eo' => 'Esperanto',
      'et' => 'Estonian',
      'tl' => 'Filipino',
      'fi' => 'Finnish',
      'fr' => 'French',
      'fy' => 'Frisian',
      'gl' => 'Galician',
      'ka' => 'Georgian',
      'de' => 'German',
      'el' => 'Greek',
      'gu' => 'Gujarati',
      'ht' => 'Haitian',
      'ha' => 'Hausa',
      'haw' => 'Hawaiian',
      'iw' => 'Hebrew',
      'hi' => 'Hindi',
      'hmn' => 'Hmong',
      'hu' => 'Hungarian',
      'is' => 'Icelandic',
      'ig' => 'Igbo',
      'id' => 'Indonesian',
      'ga' => 'Irish',
      'it' => 'Italian',
      'ja' => 'Japanese',
      'jw' => 'Javanese',
      'kn' => 'Kannada',
      'kk' => 'Kazakh',
      'km' => 'Khmer',
      'ko' => 'Korean',
      'ku' => 'Kurdish',
      'ky' => 'Kyrgyz',
      'lo' => 'Lao',
      'la' => 'Latin',
      'lv' => 'Latvian',
      'lt' => 'Lithuanian',
      'lb' => 'Luxembourgish',
      'mk' => 'Macedonian',
      'mg' => 'Malagasy',
      'ml' => 'Malayalam',
      'ms' => 'Malay',
      'mt' => 'Maltese',
      'mi' => 'Maori',
      'mr' => 'Marathi',
      'mn' => 'Mongolian',
      'my' => 'Myanmar (Burmese)',
      'ne' => 'Nepali',
      'no' => 'Norwegian',
      'ps' => 'Pashto',
      'fa' => 'Persian',
      'pl' => 'Polish',
      'pt' => 'Portuguese',
      'pa' => 'Punjabi',
      'ro' => 'Romanian',
      'ru' => 'Russian',
      'sr' => 'Serbian',
      'sn' => 'Shona',
      'st' => 'Sesotho',
      'sd' => 'Sindhi',
      'si' => 'Sinhala',
      'sk' => 'Slovak',
      'sl' => 'Slovenian',
      'sm' => 'Samoan',
      'gd' => 'Scots Gaelic',
      'so' => 'Somali',
      'es' => 'Spanish',
      'su' => 'Sundanese',
      'sw' => 'Swahili',
      'sv' => 'Swedish',
      'tg' => 'Tajik',
      'ta' => 'Tamil',
      'te' => 'Telugu',
      'th' => 'Thai',
      'tr' => 'Turkish',
      'uk' => 'Ukrainian',
      'ur' => 'Urdu',
      'uz' => 'Uzbek',
      'vi' => 'Vietnamese',
      'cy' => 'Welsh',
      'xh' => 'Xhosa',
      'yi' => 'Yiddish',
      'yo' => 'Yoruba',
      'zu' => 'Zulu',
    );

    $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
    define('PLUGIN_VER', $plugin_data['Version']);

    register_activation_hook( __FILE__, array(&$this,'gtp_activate'));
    register_deactivation_hook( __FILE__, array(&$this,'gtp_deactivate'));

    add_action( 'admin_menu', array( &$this, 'add_my_admin_menus'));
    add_action('admin_init',array(&$this, 'initialize_settings'));
    add_action('wp_head',array(&$this, 'load_css'));
    add_action('wp_footer',array(&$this, 'footer_script'));
    add_shortcode( 'google-translator',array(&$this, 'google_translator_shortcode'));
    add_shortcode( 'gtp', array(&$this, 'google_translator_menu_language'));
    add_filter('widget_text','do_shortcode');
    add_filter('walker_nav_menu_start_el', array(&$this,'menu_shortcodes') , 10 , 2);
    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'gtp_settings_link') );

    if (!is_admin()) {
      add_action('wp_enqueue_scripts',array(&$this, 'flags'));
    }

    // make sure main_lang is set correctly in config.php file
    global $gtp_url_structure, $gtp_seo_active;
    if($gtp_seo_active == '1' and $gtp_url_structure == 'sub_directory') {
        include dirname(__FILE__) . '/url_addon/config.php';

        $default_language = get_option('googletranslateplugin_language');
        if($main_lang != $default_language) { // update main_lang in config.php
            $config_file = dirname(__FILE__) . '/url_addon/config.php';
            if(is_readable($config_file) and is_writable($config_file)) {
                $config = file_get_contents($config_file);
                if(strpos($config, 'main_lang') !== false) {
                    $config = preg_replace('/\$main_lang = \'[a-z-]{2,5}\'/i', '$main_lang = \''.$default_language.'\'', $config);
                    if(is_string($config) and strlen($config) > 10)
                        file_put_contents($config_file, $config);
                }
            }
        }
    }
  }

  public function gtp_activate() {
    add_option('googletranslateplugin_active', 1);
    add_option('googletranslateplugin_language','en');
    add_option('googletranslateplugin_flags', 1);
    add_option('language_display_settings',array ('en' => 1));
    add_option('googletranslateplugin_translatebox','yes');
    add_option('googletranslateplugin_display','Vertical');
    add_option('googletranslateplugin_toolbar','Yes');
    add_option('googletranslateplugin_showbranding','Yes');
    add_option('googletranslateplugin_flags_alignment','flags_left');
    add_option('googletranslateplugin_analytics', 0);
    add_option('googletranslateplugin_analytics_id','');
    add_option('googletranslateplugin_css','');
    add_option('googletranslateplugin_multilanguage',0);
    add_option('googletranslateplugin_floating_widget','yes');
    add_option('googletranslateplugin_flag_size','18');
    add_option('googletranslateplugin_flags_order','');
    add_option('googletranslateplugin_english_flag_choice','');
    add_option('googletranslateplugin_spanish_flag_choice','');
    add_option('googletranslateplugin_portuguese_flag_choice','');
    add_option('googletranslateplugin_floating_widget_text', 'Translate &raquo;');
    add_option('googletranslateplugin_floating_widget_text_allow_translation', 0);
    delete_option('googletranslateplugin_manage_translations',0);
    delete_option('flag_display_settings');
  }

  public function gtp_deactivate() {
    delete_option('flag_display_settings');
    delete_option('googletranslateplugin_language_option');
  }

  public function gtp_settings_link ( $links ) {
    $settings_link = array(
      '<a href="' . admin_url( 'options-general.php?page=google_language_translator' ) . '">Settings</a>',
    );
   return array_merge( $links, $settings_link );
  }

  public function add_my_admin_menus(){
    $p = add_options_page('Google Language Translator', 'Google Language Translator', 'manage_options', 'google_language_translator', array(&$this, 'page_layout_cb'));

    add_action( 'load-' . $p, array(&$this, 'load_admin_js' ));
  }

  public function load_admin_js(){
    add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_admin_js' ));
    add_action('admin_footer',array(&$this, 'footer_script'));
  }

  public function enqueue_admin_js(){
    wp_enqueue_script( 'jquery-ui-core');
    wp_enqueue_script( 'jquery-ui-sortable');
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'scripts-admin', plugins_url('js/scripts-admin.js',__FILE__), array('jquery', 'wp-color-picker'), PLUGIN_VER, true);
    wp_enqueue_script( 'scripts', plugins_url('js/scripts.js',__FILE__), array('jquery', 'wp-color-picker'), PLUGIN_VER, true);
    wp_enqueue_script( 'scripts-google', '//translate.google.com/translate_a/element.js?cb=googletranslatepluginInit', array('jquery'), null, true);

    wp_enqueue_style( 'style.css', plugins_url('css/style.css', __FILE__),'', PLUGIN_VER,'');

    if (get_option ('googletranslateplugin_floating_widget') == 'yes') {
      wp_enqueue_style( 'gtp-toolbar-styles', plugins_url('css/toolbar.css', __FILE__),'', PLUGIN_VER,'' );
    }
  }

  public function flags() {
    wp_enqueue_script( 'scripts', plugins_url('js/scripts.js',__FILE__), array('jquery'), PLUGIN_VER, true);
    wp_enqueue_script( 'scripts-google', '//translate.google.com/translate_a/element.js?cb=googletranslatepluginInit', array('jquery'), null, true);
    wp_enqueue_style( 'google-language-translator', plugins_url('css/style.css', __FILE__), '', PLUGIN_VER, '');

    if (get_option ('googletranslateplugin_floating_widget') == 'yes') {
      wp_enqueue_style( 'gtp-toolbar-styles', plugins_url('css/toolbar.css', __FILE__), '', PLUGIN_VER, '');
    }
  }

  public function load_css() {
    include( plugin_dir_path( __FILE__ ) . '/css/style.php');
  }

  public function google_translator_shortcode() {

    if (get_option('googletranslateplugin_display')=='Vertical' || get_option('googletranslateplugin_display')=='SIMPLE'){
        return $this->googletranslateplugin_vertical();
    }
    elseif(get_option('googletranslateplugin_display')=='Horizontal'){
        return $this->googletranslateplugin_horizontal();
    }
  }

  public function googletranslateplugin_included_languages() {
    $get_language_choices = get_option ('language_display_settings');

    foreach ($get_language_choices as $key=>$value):
      if ($value == 1):
        $items[] = $key;
      endif;
    endforeach;

    $comma_separated = implode(",",array_values($items));
    $lang = ", includedLanguages:'".$comma_separated."'";
    return $lang;
  }

  public function analytics() {
    if ( get_option('googletranslateplugin_analytics') == 1 ) {
      $analytics_id = get_option('googletranslateplugin_analytics_id');
      $analytics = "gaTrack: true, gaId: '".$analytics_id."'";

          if (!empty ($analytics_id) ):
        return ', '.$analytics;
          endif;
    }
  }

  public function menu_shortcodes( $item_output,$item ) {
    if ( !empty($item->description)) {
      $output = do_shortcode($item->description);

      if ( $output != $item->description )
        $item_output = $output;
      }
    return $item_output;
  }

  public function google_translator_menu_language($atts, $content = '') {
    extract(shortcode_atts(array(
      "language" => 'Spanish',
      "label" => 'Spanish',
      "image" => 'no',
      "text" => 'yes',
      "image_size" => '24',
      "label" => html_entity_decode('Espa&ntilde;ol')
    ), $atts));

    $gtp_url_structure = get_option('googletranslateplugin_url_structure');
    $gtp_seo_active = get_option('googletranslateplugin_seo_active');
    $default_language = get_option('googletranslateplugin_language');
    $english_flag_choice = get_option('googletranslateplugin_english_flag_choice');
    $spanish_flag_choice = get_option('googletranslateplugin_spanish_flag_choice');
    $portuguese_flag_choice = get_option('googletranslateplugin_portuguese_flag_choice');
    $language_code = array_search($language,$this->languages_array);
    $language_name = $language;
    $language_name_flag = $language_name;

    if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
      $language_name_flag = 'canada';
    }
    if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
          $language_name_flag = 'united-states';
    }
    if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
      $language_name_flag = 'mexico';
    }
    if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
      $language_name_flag = 'brazil';
    }

    $href = '#';
    if($gtp_seo_active == '1') {
        $current_url = network_home_url(add_query_arg(null, null));
        switch($gtp_url_structure) {
            case 'sub_directory':
                $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                break;
            case 'sub_domain':
                $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                break;
            default:
                break;
        }
    }

    return "<a href='".esc_url($href)."' class='nturl notranslate ".esc_attr($language_code)." ".esc_attr($language_name_flag)." single-language flag' title='".esc_attr($language)."'>".($image=='yes' ? "<span class='flag size".esc_attr($image_size)."'></span>" : '') .($text=='yes' ? htmlspecialchars($label) : '')."</a>";
  }

  public function footer_script() {
    global $vertical;
    global $horizontal;
    global $shortcode_started;
    $layout = get_option('googletranslateplugin_display');
    $default_language = get_option('googletranslateplugin_language');
    $language_choices = $this->googletranslateplugin_included_languages();
    $new_languages_array_string = get_option('googletranslateplugin_flags_order');
    $new_languages_array = explode(",",$new_languages_array_string);
    $new_languages_array_codes = array_values($new_languages_array);
    $new_languages_array_count = count($new_languages_array);
    $english_flag_choice = get_option('googletranslateplugin_english_flag_choice');
    $spanish_flag_choice = get_option('googletranslateplugin_spanish_flag_choice');
    $portuguese_flag_choice = get_option('googletranslateplugin_portuguese_flag_choice');
    $show_flags = get_option('googletranslateplugin_flags');
    $flag_width = get_option('googletranslateplugin_flag_size');
    $get_language_choices = get_option('language_display_settings');
    $floating_widget = get_option ('googletranslateplugin_floating_widget');
    $floating_widget_text = get_option ('googletranslateplugin_floating_widget_text');
    $floating_widget_text_translation_allowed = get_option ('googletranslateplugin_floating_widget_text_allow_translation');
    $is_active = get_option ( 'googletranslateplugin_active' );
    $is_multilanguage = get_option('googletranslateplugin_multilanguage');
    $gtp_url_structure = get_option('googletranslateplugin_url_structure');
    $gtp_seo_active = get_option('googletranslateplugin_seo_active');
    $str = '';

    if( $is_active == 1) {
      if ($floating_widget=='yes') {
        $str.='<div id="gtp-translate-trigger"><span'.($floating_widget_text_translation_allowed != 1 ? ' class="notranslate"' : ' class="translate"').'>'.(empty($floating_widget_text) ? 'Translate &raquo;' : $floating_widget_text).'</span></div>';
        $str.='<div id="gtp-toolbar"></div>';
      } //endif $floating_widget

      if ((($layout=='SIMPLE' && !isset($vertical)) || ($layout=='Vertical' && !isset($vertical)) || (isset($vertical) && $show_flags==0)) || (($layout=='Horizontal' && !isset($horizontal)) || (isset($horizontal) && $show_flags==0))):

      $str.='<div id="flags" style="display:none" class="size'.$flag_width.'">';
      $str.='<ul id="sortable" class="ui-sortable">';
        if (empty($new_languages_array_string)) {
          foreach ($this->languages_array as $key=>$value) {
            $language_code = $key;
            $language_name = $value;
            $language_name_flag = $language_name;
            if (!empty($get_language_choices[$language_code]) && $get_language_choices[$language_code]==1) {
              if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
                $language_name_flag = 'canada';
              }
              if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
                $language_name_flag = 'united-states';
              }
              if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
                $language_name_flag = 'mexico';
              }
              if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
                $language_name_flag = 'brazil';
              }

              $href = '#';
              if($gtp_seo_active == '1') {
                $current_url = network_home_url(add_query_arg(null, null));
                switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                }
              }

              $str .= '<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
            } //empty
          }//foreach
        } else {
          if ($new_languages_array_count != count($get_language_choices)):
            foreach ($get_language_choices as $key => $value) {
              $language_code = $key;
              $language_name = $this->languages_array[$key];
              $language_name_flag = $language_name;

              if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
                $language_name_flag = 'canada';
              }
              if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
                $language_name_flag = 'united-states';
              }
              if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
                $language_name_flag = 'mexico';
              }
              if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
                $language_name_flag = 'brazil';
              }

              $href = '#';
              if($gtp_seo_active == '1') {
                $current_url = network_home_url(add_query_arg(null, null));
                switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                }
              }

              $str.='<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
            } //foreach
          else:
            foreach ($new_languages_array_codes as $value) {
              $language_name = $value;
              $language_code = array_search ($language_name, $this->languages_array);
              $language_name_flag = $language_name;

            if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
              $language_name_flag = 'canada';
            }
            if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
              $language_name_flag = 'united-states';
            }
            if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
              $language_name_flag = 'mexico';
            }
            if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
              $language_name_flag = 'brazil';
            }

            $href = '#';
            if($gtp_seo_active == '1') {
                $current_url = network_home_url(add_query_arg(null, null));
                switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                }
            }

            $str.='<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
          }//foreach
        endif;
      }//endif
      $str.='</ul>';
      $str.='</div>';

      endif; //layout
    }

    $language_choices = $this->googletranslateplugin_included_languages();
    $layout = get_option('googletranslateplugin_display');
    $is_multilanguage = get_option('googletranslateplugin_multilanguage');
    $horizontal_layout = ', layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL';
    $simple_layout = ', layout: google.translate.TranslateElement.InlineLayout.SIMPLE';
    $auto_display = ', autoDisplay: false';
    $default_language = get_option('googletranslateplugin_language');

    if ($is_multilanguage == 1):
      $multilanguagePage = ', multilanguagePage:true';
      if($gtp_seo_active != '1')
          $str.="<div id='gtp-footer'>".(!isset($vertical) && !isset($horizontal) ? '<div id="google_language_translator" class="default-language-'.$default_language.'"></div>' : '')."</div><script>function googletranslatepluginInit() { new google.translate.TranslateElement({pageLanguage: '".$default_language."'".$language_choices . ($layout=='Horizontal' ? $horizontal_layout : ($layout=='SIMPLE' ? $simple_layout : '')) . $auto_display . $multilanguagePage . $this->analytics()."}, 'google_language_translator');}</script>";
      echo $str;
    else:
      if($gtp_seo_active != '1')
          $str.="<div id='gtp-footer'>".(!isset($vertical) && !isset($horizontal) ? '<div id="google_language_translator" class="default-language-'.$default_language.'"></div>' : '')."</div><script>function googletranslatepluginInit() { new google.translate.TranslateElement({pageLanguage: '".$default_language."'".$language_choices . ($layout=='Horizontal' ? $horizontal_layout : ($layout=='SIMPLE' ? $simple_layout : '')) . $auto_display . $this->analytics()."}, 'google_language_translator');}</script>";
      echo $str;
    endif; //is_multilanguage
  }

  public function googletranslateplugin_vertical() {
    global $started;
    global $vertical;
    $vertical = 1;
    $started = false;
    $new_languages_array_string = get_option('googletranslateplugin_flags_order');
    $new_languages_array = explode(",",$new_languages_array_string);
    $new_languages_array_codes = array_values($new_languages_array);
    $new_languages_array_count = count($new_languages_array);
    $get_language_choices = get_option ('language_display_settings');
    $show_flags = get_option('googletranslateplugin_flags');
    $flag_width = get_option('googletranslateplugin_flag_size');
    $default_language_code = get_option('googletranslateplugin_language');
    $english_flag_choice = get_option('googletranslateplugin_english_flag_choice');
    $spanish_flag_choice = get_option('googletranslateplugin_spanish_flag_choice');
    $portuguese_flag_choice = get_option('googletranslateplugin_portuguese_flag_choice');
    $is_active = get_option ( 'googletranslateplugin_active' );
    $language_choices = $this->googletranslateplugin_included_languages();
    $floating_widget = get_option ('googletranslateplugin_floating_widget');
    $gtp_url_structure = get_option('googletranslateplugin_url_structure');
    $gtp_seo_active = get_option('googletranslateplugin_seo_active');

    $default_language = $default_language_code;
    $str = '';

    if ($is_active==1):
      if ($show_flags==1):
      $str.='<div id="flags" class="size'.$flag_width.'">';
      $str.='<ul id="sortable" class="ui-sortable" style="float:left">';

      if (empty($new_languages_array_string)):
        foreach ($this->languages_array as $key=>$value) {
          $language_code = $key;
          $language_name = $value;
          $language_name_flag = $language_name;

          if (!empty($get_language_choices[$language_code]) && $get_language_choices[$language_code]==1) {
            if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
              $language_name_flag = 'canada';
            }
            if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
              $language_name_flag = 'united-states';
            }
            if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
              $language_name_flag = 'mexico';
            }
            if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
              $language_name_flag = 'brazil';
            }

            $href = '#';
            if($gtp_seo_active == '1') {
                $current_url = network_home_url(add_query_arg(null, null));
                switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                }
            }

            $str.="<li id='".$language_name."'><a href='".esc_url($href)."' title='".$language_name."' class='nturl notranslate ".$language_code." flag ".$language_name_flag."'></a></li>";
          } //endif
        }//foreach
      else:
        if ($new_languages_array_count != count($get_language_choices)):
            foreach ($get_language_choices as $key => $value) {
              $language_code = $key;
              $language_name = $this->languages_array[$key];
              $language_name_flag = $language_name;

              if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
                $language_name_flag = 'canada';
              }
              if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
                $language_name_flag = 'united-states';
              }
              if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
                $language_name_flag = 'mexico';
              }
              if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
                $language_name_flag = 'brazil';
              }

              $href = '#';
              if($gtp_seo_active == '1') {
                  $current_url = network_home_url(add_query_arg(null, null));
                  switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                  }
              }

              $str.='<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
            } //foreach
          else:
            foreach ($new_languages_array_codes as $value) {
              $language_name = $value;
              $language_code = array_search ($language_name, $this->languages_array);
              $language_name_flag = $language_name;

              if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
                $language_name_flag = 'canada';
              }
              if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
                $language_name_flag = 'united-states';
              }
              if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
                $language_name_flag = 'mexico';
              }
              if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
                $language_name_flag = 'brazil';
              }

              $href = '#';
              if($gtp_seo_active == '1') {
                  $current_url = network_home_url(add_query_arg(null, null));
                  switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                  }
              }

              $str.='<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
            }//foreach
          endif;
      endif;

      $str.='</ul>';
      $str.='</div>';

      endif; //show_flags

      if($gtp_seo_active == '1') {
          $str .= '<div id="google_language_translator" class="default-language-'.$default_language_code.'">';
          $str .= '<select aria-label="Website Language Selector" class="notranslate"><option value="">Select Language</option>';

          $get_language_choices = get_option ('language_display_settings');
          foreach($get_language_choices as $key => $value) {
              if($value == 1)
                  $str .= '<option value="'.$default_language.'|'.$key.'">'.$this->languages_array[$key].'</option>';
          }
          $str .= '</select></div>';

          $str .= '<script>';
          if($gtp_url_structure == 'sub_directory') {
              $str .= "function dogtpTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(typeof _gaq!='undefined'){_gaq.push(['_trackEvent', 'GTranslate', lang, location.pathname+location.search]);}else {if(typeof ga!='undefined')ga('send', 'event', 'GTranslate', lang, location.pathname+location.search);}var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW' && plang != 'hmn' && plang != 'haw' && plang != 'ceb')plang='$default_language';if(lang == '$default_language')location.href=location.protocol+'//'+location.host+gtp_request_uri;else location.href=location.protocol+'//'+location.host+'/'+lang+gtp_request_uri;}";
          } elseif($gtp_url_structure == 'sub_domain') {
              $str .= "function dogtpTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(typeof _gaq!='undefined'){_gaq.push(['_trackEvent', 'GTranslate', lang, location.hostname+location.pathname+location.search]);}else {if(typeof ga!='undefined')ga('send', 'event', 'GTranslate', lang, location.hostname+location.pathname+location.search);}var plang=location.hostname.split('.')[0];if(plang.length !=2 && plang.toLowerCase() != 'zh-cn' && plang.toLowerCase() != 'zh-tw' && plang != 'hmn' && plang != 'haw' && plang != 'ceb')plang='$default_language';location.href=location.protocol+'//'+(lang == '$default_language' ? '' : lang+'.')+location.hostname.replace('www.', '').replace(RegExp('^' + plang + '[.]'), '')+gtp_request_uri;}";
          }
          $str .= '</script>';

      } else
          $str.='<div id="google_language_translator" class="default-language-'.$default_language_code.'"></div>';

      return $str;

    endif;
  } // End gtp_vertical

  public function googletranslateplugin_horizontal() {
    global $started;
    global $horizontal;
    $horizontal = 1;
    $started = false;
    $new_languages_array_string = get_option('googletranslateplugin_flags_order');
    $new_languages_array = explode(",",$new_languages_array_string);
    $new_languages_array_codes = array_values($new_languages_array);
    $new_languages_array_count = count($new_languages_array);
    $get_language_choices = get_option ('language_display_settings');
    $show_flags = get_option('googletranslateplugin_flags');
    $flag_width = get_option('googletranslateplugin_flag_size');
    $default_language_code = get_option('googletranslateplugin_language');
    $english_flag_choice = get_option('googletranslateplugin_english_flag_choice');
    $spanish_flag_choice = get_option('googletranslateplugin_spanish_flag_choice');
    $portuguese_flag_choice = get_option('googletranslateplugin_portuguese_flag_choice');
    $is_active = get_option ( 'googletranslateplugin_active' );
    $language_choices = $this->googletranslateplugin_included_languages();
    $floating_widget = get_option ('googletranslateplugin_floating_widget');
    $gtp_url_structure = get_option('googletranslateplugin_url_structure');
    $gtp_seo_active = get_option('googletranslateplugin_seo_active');

    $default_language = $default_language_code;
    $str = '';

    if ($is_active==1):
      if ($show_flags==1):
      $str.='<div id="flags" class="size'.$flag_width.'">';
      $str.='<ul id="sortable" class="ui-sortable" style="float:left">';

      if (empty($new_languages_array_string)):
        foreach ($this->languages_array as $key=>$value) {
          $language_code = $key;
          $language_name = $value;
          $language_name_flag = $language_name;

          if (!empty($get_language_choices[$language_code]) && $get_language_choices[$language_code]==1) {
            if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
              $language_name_flag = 'canada';
            }
            if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
              $language_name_flag = 'united-states';
            }
            if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
              $language_name_flag = 'mexico';
            }
            if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
              $language_name_flag = 'brazil';
            }

            $href = '#';
            if($gtp_seo_active == '1') {
                $current_url = network_home_url(add_query_arg(null, null));
                switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                }
            }

            $str.="<li id='".$language_name."'><a href='".esc_url($href)."' title='".$language_name."' class='nturl notranslate ".$language_code." flag ".$language_name_flag."'></a></li>";
          } //endif
        }//foreach
      else:
        if ($new_languages_array_count != count($get_language_choices)):
            foreach ($get_language_choices as $key => $value) {
              $language_code = $key;
              $language_name = $this->languages_array[$key];
              $language_name_flag = $language_name;

              if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
                $language_name_flag = 'canada';
              }
              if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
                $language_name_flag = 'united-states';
              }
              if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
                $language_name_flag = 'mexico';
              }
              if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
                $language_name_flag = 'brazil';
              }

              $href = '#';
              if($gtp_seo_active == '1') {
                  $current_url = network_home_url(add_query_arg(null, null));
                  switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                  }
              }

              $str.='<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
            } //foreach
          else:
            foreach ($new_languages_array_codes as $value) {
              $language_name = $value;
              $language_code = array_search ($language_name, $this->languages_array);
              $language_name_flag = $language_name;

              if ( $language_name == 'English' && $english_flag_choice == 'canadian_flag') {
                $language_name_flag = 'canada';
              }
              if ( $language_name == "English" && $english_flag_choice == 'us_flag') {
                $language_name_flag = 'united-states';
              }
              if ( $language_name == 'Spanish' && $spanish_flag_choice == 'mexican_flag') {
                $language_name_flag = 'mexico';
              }
              if ( $language_name == 'Portuguese' && $portuguese_flag_choice == 'brazilian_flag') {
                $language_name_flag = 'brazil';
              }

              $href = '#';
              if($gtp_seo_active == '1') {
                  $current_url = network_home_url(add_query_arg(null, null));
                  switch($gtp_url_structure) {
                    case 'sub_directory':
                        $href = ($language_code == $default_language) ? $current_url : '/' . $language_code . $_SERVER['REQUEST_URI'];
                        break;
                    case 'sub_domain':
                        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                        $href = ($language_code == $default_language) ? $current_url : str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $language_code . '.' . $domain, $current_url);
                        break;
                    default:
                        break;
                  }
              }

              $str.='<li id="'.$language_name.'"><a href="'.esc_url($href).'" title="'.$language_name.'" class="nturl notranslate '.$language_code.' flag '.$language_name_flag.'"></a></li>';
            }//foreach
          endif;
      endif;
      $str.='</ul>';
      $str.='</div>';

      endif; //show_flags

      if($gtp_seo_active == '1') {
          $str .= '<div id="google_language_translator" class="default-language-'.$default_language_code.'">';
          $str .= '<select aria-label="Website Language Selector" class="notranslate"><option value="">Select Language</option>';

          $get_language_choices = get_option ('language_display_settings');
          foreach($get_language_choices as $key => $value) {
              if($value == 1)
                  $str .= '<option value="'.$default_language.'|'.$key.'">'.$this->languages_array[$key].'</option>';
          }
          $str .= '</select></div>';

          $str .= '<script>';
          if($gtp_url_structure == 'sub_directory') {
              $str .= "function dogtpTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(typeof _gaq!='undefined'){_gaq.push(['_trackEvent', 'GTranslate', lang, location.pathname+location.search]);}else {if(typeof ga!='undefined')ga('send', 'event', 'GTranslate', lang, location.pathname+location.search);}var plang=location.pathname.split('/')[1];if(plang.length !=2 && plang != 'zh-CN' && plang != 'zh-TW' && plang != 'hmn' && plang != 'haw' && plang != 'ceb')plang='$default_language';if(lang == '$default_language')location.href=location.protocol+'//'+location.host+gtp_request_uri;else location.href=location.protocol+'//'+location.host+'/'+lang+gtp_request_uri;}";
          } elseif($gtp_url_structure == 'sub_domain') {
              $str .= "function dogtpTranslate(lang_pair) {if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(typeof _gaq!='undefined'){_gaq.push(['_trackEvent', 'GTranslate', lang, location.hostname+location.pathname+location.search]);}else {if(typeof ga!='undefined')ga('send', 'event', 'GTranslate', lang, location.hostname+location.pathname+location.search);}var plang=location.hostname.split('.')[0];if(plang.length !=2 && plang.toLowerCase() != 'zh-cn' && plang.toLowerCase() != 'zh-tw' && plang != 'hmn' && plang != 'haw' && plang != 'ceb')plang='$default_language';location.href=location.protocol+'//'+(lang == '$default_language' ? '' : lang+'.')+location.hostname.replace('www.', '').replace(RegExp('^' + plang + '[.]'), '')+gtp_request_uri;}";
          }
          $str .= '</script>';
      } else
          $str.='<div id="google_language_translator" class="default-language-'.$default_language_code.'"></div>';

      return $str;

    endif;
  } // End gtp_horizontal

  public function initialize_settings() {
    add_settings_section('gtp_settings','Settings','','google_language_translator');

    $settings_name_array = array (
        'googletranslateplugin_active',
        'googletranslateplugin_language',
        'language_display_settings',
        'googletranslateplugin_flags',
        'googletranslateplugin_translatebox',
        'googletranslateplugin_display',
        'gtp_language_switcher_width',
        'gtp_language_switcher_text_color',
        'gtp_language_switcher_bg_color',
        'googletranslateplugin_toolbar',
        'googletranslateplugin_showbranding',
        'googletranslateplugin_flags_alignment',
        'googletranslateplugin_analytics',
        'googletranslateplugin_analytics_id',
        'googletranslateplugin_css',
        'googletranslateplugin_multilanguage',
        'googletranslateplugin_floating_widget',
        'googletranslateplugin_flag_size',
        'googletranslateplugin_flags_order',
        'googletranslateplugin_english_flag_choice',
        'googletranslateplugin_spanish_flag_choice',
        'googletranslateplugin_portuguese_flag_choice',
        'googletranslateplugin_floating_widget_text',
        'gtp_floating_widget_text_color',
        'googletranslateplugin_floating_widget_text_allow_translation',
        'gtp_floating_widget_position',
        'gtp_floating_widget_bg_color',
        'googletranslateplugin_seo_active',
        'googletranslateplugin_url_structure',
        'googletranslateplugin_url_translation_active',
        'googletranslateplugin_hreflang_tags_active',
    );

    foreach ($settings_name_array as $setting) {
      add_settings_field( $setting,'',$setting.'_cb','google_language_translator','gtp_settings');

      if ($setting == 'googletranslateplugin_floating_widget_text')
          register_setting( 'google_language_translator', $setting, array('sanitize_callback' => 'wp_kses_post'));
      else
          register_setting( 'google_language_translator',$setting);
    }
  }

  public function googletranslateplugin_active_cb() {
    $option_name = 'googletranslateplugin_active' ;
    $new_value = 1;
      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.'');

      $html = '<input type="checkbox" name="googletranslateplugin_active" id="googletranslateplugin_active" value="1" '.checked(1,$options,false).'/> &nbsp; Check this box to activate';
      echo $html;
    }

  public function googletranslateplugin_language_cb() {

    $option_name = 'googletranslateplugin_language';
    $new_value = 'en';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

      <select name="googletranslateplugin_language" id="googletranslateplugin_language">

      <?php

        foreach ($this->languages_array as $key => $value) {
          $language_code = $key;
          $language_name = $value; ?>
            <option value="<?php echo $language_code; ?>" <?php if($options==''.$language_code.''){echo "selected";}?>><?php echo $language_name; ?></option>
          <?php } ?>
      </select>
    <?php
    }

    public function language_display_settings_cb() {
      $default_language_code = get_option('googletranslateplugin_language');
      $option_name = 'language_display_settings';
      $new_value = array(''.$default_language_code.'' => 1);

      if ( get_option( $option_name ) == false ) {
        // The option does not exist, so we update it.
        update_option( $option_name, $new_value );
      }

      $get_language_choices = get_option (''.$option_name.''); ?>

      <script>jQuery(document).ready(function($) { $('.select-all-languages').on('click',function(e) { e.preventDefault(); $('.languages').find('input:checkbox').prop('checked', true); }); $('.clear-all-languages').on('click',function(e) { e.preventDefault();
$('.languages').find('input:checkbox').prop('checked', false); }); }); </script>

      <?php

      foreach ($this->languages_array as $key => $value) {
        $language_code = $key;
        $language_name = $value;
        $language_code_array[] = $key;

        if (!isset($get_language_choices[''.$language_code.''])) {
          $get_language_choices[''.$language_code.''] = 0;
        }

        $items[] = $get_language_choices[''.$language_code.''];
        $language_codes = $language_code_array;
        $item_count = count($items);

        if ($item_count == 1 || $item_count == 27 || $item_count == 53 || $item_count == 79) { ?>
          <div class="languages" style="width:25%; float:left">
        <?php } ?>
          <div><input type="checkbox" name="language_display_settings[<?php echo $language_code; ?>]" value="1"<?php checked( 1,$get_language_choices[''.$language_code.'']); ?>/><?php echo $language_name; ?></div>
        <?php
        if ($item_count == 26 || $item_count == 52 || $item_count == 78 || $item_count == 104) { ?>
          </div>
        <?php }
      } ?>
     <div class="clear"></div>
    <?php
    }

    public function googletranslateplugin_flags_cb() {

      $option_name = 'googletranslateplugin_flags' ;
      $new_value = 1;

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.'');

      $html = '<input type="checkbox" name="googletranslateplugin_flags" id="googletranslateplugin_flags" value="1" '.checked(1,$options,false).'/> &nbsp; Check to show flags';

      echo $html;
    }

    public function googletranslateplugin_floating_widget_cb() {

    $option_name = 'googletranslateplugin_floating_widget' ;
    $new_value = 'yes';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

          <select name="googletranslateplugin_floating_widget" id="googletranslateplugin_floating_widget" style="width:170px">
              <option value="yes" <?php if($options=='yes'){echo "selected";}?>>Yes, show widget</option>
              <option value="no" <?php if($options=='no'){echo "selected";}?>>No, hide widget</option>
          </select>
  <?php }

  public function googletranslateplugin_floating_widget_text_cb() {

    $option_name = 'googletranslateplugin_floating_widget_text' ;
    $new_value = 'Translate &raquo;';

    if ( get_option( $option_name ) === false ) {
      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option (''.$option_name.''); ?>

    <input type="text" name="googletranslateplugin_floating_widget_text" id="googletranslateplugin_floating_widget_text" value="<?php echo esc_attr($options); ?>" style="width:170px"/>

  <?php }

  public function googletranslateplugin_floating_widget_text_allow_translation_cb() {
    $option_name = 'googletranslateplugin_floating_widget_text_allow_translation' ;
    $new_value = 0;

    if ( get_option( $option_name ) === false ) {
      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option (''.$option_name.'');

    $html = '<input type="checkbox" name="googletranslateplugin_floating_widget_text_allow_translation" id="googletranslateplugin_floating_widget_text_allow_translation" value="1" '.checked(1,$options,false).'/> &nbsp; Check to allow';
    echo $html;
  }

  public function gtp_floating_widget_position_cb() {
      $option_name = 'gtp_floating_widget_position';
      $new_value = '';

      if (get_option($option_name) === false):
        update_option($option_name, $new_value);
      endif;

      $options = get_option(''.$option_name.''); ?>

      <select name="gtp_floating_widget_position" id="gtp_floating_widget_position" style="width:170px">
        <option value="bottom_left" <?php if($options=='bottom_left'){echo "selected";}?>>Bottom left</option>
        <option value="bottom_center" <?php if($options=='bottom_center'){echo "selected";}?>>Bottom center</option>
        <option value="bottom_right" <?php if($options=='bottom_right'){echo "selected";}?>>Bottom right</option>
        <option value="top_left" <?php if($options=='top_left'){echo "selected";}?>>Top left</option>
        <option value="top_center" <?php if($options=='top_center'){echo "selected";}?>>Top center</option>
        <option value="top_right" <?php if($options=='top_right'){echo "selected";}?>>Top right</option>
      </select>
  <?php
  }

  public function gtp_floating_widget_text_color_cb() {
    $option_name = 'gtp_floating_widget_text_color';
    $new_value = '#ffffff';

    if (get_option($option_name) === false):
      update_option($option_name, $new_value);
    endif;

    $options = get_option(''.$option_name.''); ?>

    <input type="text" name="gtp_floating_widget_text_color" id="gtp_floating_widget_text_color" class="color-field" value="<?php echo $options; ?>"/>
  <?php
  }

  public function gtp_floating_widget_bg_color_cb() {
    $option_name = 'gtp_floating_widget_bg_color';
    $new_value = '#f89406';

    if (get_option($option_name) === false):
      update_option($option_name, $new_value);
    endif;

    $options = get_option(''.$option_name.''); ?>

    <input type="text" name="gtp_floating_widget_bg_color" id="gtp_floating_widget_bg_color" class="color-field" value="<?php echo $options; ?>"/>
  <?php
  }

  public function gtp_language_switcher_width_cb() {

  $option_name = 'gtp_language_switcher_width' ;
  $new_value = '';

  if ( get_option( $option_name ) === false ) {
    update_option( $option_name, $new_value );
  }

  $options = get_option (''.$option_name.''); ?>

  <select name="gtp_language_switcher_width" id="gtp_language_switcher_width" style="width:110px;">
    <option value="100%" <?php if($options=='100%'){echo "selected";}?>>100%</option>
    <option value="">-------</option>
    <option value="150px" <?php if($options=='150px'){echo "selected";}?>>150px</option>
    <option value="160px" <?php if($options=='160px'){echo "selected";}?>>160px</option>
    <option value="170px" <?php if($options=='170px'){echo "selected";}?>>170px</option>
    <option value="180px" <?php if($options=='180px'){echo "selected";}?>>180px</option>
    <option value="190px" <?php if($options=='190px'){echo "selected";}?>>190px</option>
    <option value="200px" <?php if($options=='200px'){echo "selected";}?>>200px</option>
    <option value="210px" <?php if($options=='210px'){echo "selected";}?>>210px</option>
    <option value="220px" <?php if($options=='220px'){echo "selected";}?>>220px</option>
    <option value="230px" <?php if($options=='230px'){echo "selected";}?>>230px</option>
    <option value="240px" <?php if($options=='240px'){echo "selected";}?>>240px</option>
    <option value="250px" <?php if($options=='250px'){echo "selected";}?>>250px</option>
    <option value="260px" <?php if($options=='260px'){echo "selected";}?>>260px</option>
    <option value="270px" <?php if($options=='270px'){echo "selected";}?>>270px</option>
    <option value="280px" <?php if($options=='280px'){echo "selected";}?>>280px</option>
    <option value="290px" <?php if($options=='290px'){echo "selected";}?>>290px</option>
    <option value="300px" <?php if($options=='300px'){echo "selected";}?>>300px</option>
  </select>
  <?php }

  public function gtp_language_switcher_text_color_cb() {
    $option_name = 'gtp_language_switcher_text_color';
    $new_value = '#32373c';

    if (get_option($option_name) === false):
      update_option($option_name, $new_value);
    endif;

    $options = get_option(''.$option_name.''); ?>

    <input type="text" name="gtp_language_switcher_text_color" id="gtp_language_switcher_text_color" class="color-field" value="<?php echo $options; ?>"/>
  <?php
  }

  public function gtp_language_switcher_bg_color_cb() {
    $option_name = 'gtp_language_switcher_bg_color';
    $new_value = '';

    if (get_option($option_name) === false):
      update_option($option_name, $new_value);
    endif;

    $options = get_option(''.$option_name.''); ?>

    <input type="text" name="gtp_language_switcher_bg_color" id="gtp_language_switcher_bg_color" class="color-field" value="<?php echo $options; ?>"/>
  <?php
  }

  public function googletranslateplugin_translatebox_cb() {

    $option_name = 'googletranslateplugin_translatebox' ;
    $new_value = 'yes';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

          <select name="googletranslateplugin_translatebox" id="googletranslateplugin_translatebox" style="width:190px">
            <option value="yes" <?php if($options=='yes'){echo "selected";}?>>Show language switcher</option>
        <option value="no" <?php if($options=='no'){echo "selected";}?>>Hide language switcher</option>
          </select>
  <?php }

  public function googletranslateplugin_display_cb() {

    $option_name = 'googletranslateplugin_display' ;
    $new_value = 'Vertical';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

          <select name="googletranslateplugin_display" id="googletranslateplugin_display" style="width:170px;">
             <option value="Vertical" <?php if(get_option('googletranslateplugin_display')=='Vertical'){echo "selected";}?>>Vertical</option>
             <option value="Horizontal" <?php if(get_option('googletranslateplugin_display')=='Horizontal'){echo "selected";}?>>Horizontal</option>
             <?php
               $browser_lang = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtok(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']), ',') : '';
           if (!empty($get_http_accept_language)):
             $get_http_accept_language = explode(",",$browser_lang);
           else:
             $get_http_accept_language = explode(",",$browser_lang);
           endif;
               $bestlang = $get_http_accept_language[0];
               $bestlang_prefix = substr($get_http_accept_language[0],0,2);

               if ($bestlang_prefix == 'en'): ?>
           <option value="SIMPLE" <?php if (get_option('googletranslateplugin_display')=='SIMPLE'){echo "selected";}?>>SIMPLE</option>
             <?php endif; ?>
          </select>
  <?php }

  public function googletranslateplugin_toolbar_cb() {

    $option_name = 'googletranslateplugin_toolbar' ;
    $new_value = 'Yes';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

          <select name="googletranslateplugin_toolbar" id="googletranslateplugin_toolbar" style="width:170px;">
             <option value="Yes" <?php if(get_option('googletranslateplugin_toolbar')=='Yes'){echo "selected";}?>>Yes</option>
             <option value="No" <?php if(get_option('googletranslateplugin_toolbar')=='No'){echo "selected";}?>>No</option>
          </select>
  <?php }

  public function googletranslateplugin_showbranding_cb() {

    $option_name = 'googletranslateplugin_showbranding' ;
    $new_value = 'Yes';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

          <select name="googletranslateplugin_showbranding" id="googletranslateplugin_showbranding" style="width:170px;">
             <option value="Yes" <?php if(get_option('googletranslateplugin_showbranding')=='Yes'){echo "selected";}?>>Yes</option>
             <option value="No" <?php if(get_option('googletranslateplugin_showbranding')=='No'){echo "selected";}?>>No</option>
          </select>
  <?php }

  public function googletranslateplugin_flags_alignment_cb() {

    $option_name = 'googletranslateplugin_flags_alignment' ;
    $new_value = 'flags_left';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, 'flags_left' );
      }

      $options = get_option (''.$option_name.''); ?>

      <input type="radio" name="googletranslateplugin_flags_alignment" id="flags_left" value="flags_left" <?php if($options=='flags_left'){echo "checked";}?>/> <label for="flags_left">Align Left</label><br/>
      <input type="radio" name="googletranslateplugin_flags_alignment" id="flags_right" value="flags_right" <?php if($options=='flags_right'){echo "checked";}?>/> <label for="flags_right">Align Right</label>
  <?php }

  public function googletranslateplugin_analytics_cb() {

    $option_name = 'googletranslateplugin_analytics' ;
    $new_value = 0;

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.'');

    $html = '<input type="checkbox" name="googletranslateplugin_analytics" id="googletranslateplugin_analytics" value="1" '.checked(1,$options,false).'/> &nbsp; Activate Google Analytics tracking?';
    echo $html;
  }

  public function googletranslateplugin_analytics_id_cb() {

    $option_name = 'googletranslateplugin_analytics_id' ;
    $new_value = '';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.'');

    $html = '<input type="text" name="googletranslateplugin_analytics_id" id="googletranslateplugin_analytics_id" value="'.$options.'" />';
    echo $html;
  }

  public function googletranslateplugin_flag_size_cb() {

    $option_name = 'googletranslateplugin_flag_size' ;
    $new_value = '18';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.''); ?>

          <select name="googletranslateplugin_flag_size" id="googletranslateplugin_flag_size" style="width:110px;">
             <option value="16" <?php if($options=='16'){echo "selected";}?>>16px</option>
             <option value="18" <?php if($options=='18'){echo "selected";}?>>18px</option>
             <option value="20" <?php if($options=='20'){echo "selected";}?>>20px</option>
             <option value="22" <?php if($options=='22'){echo "selected";}?>>22px</option>
             <option value="24" <?php if($options=='24'){echo "selected";}?>>24px</option>
          </select>
  <?php }

  public function googletranslateplugin_flags_order_cb() {
    $option_name = 'googletranslateplugin_flags_order';
    $new_value = '';

    if ( get_option ( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option ( ''.$option_name.'' ); ?>

    <input type="hidden" id="order" name="googletranslateplugin_flags_order" value="<?php print_r(get_option('googletranslateplugin_flags_order')); ?>" />
   <?php
  }

  public function googletranslateplugin_english_flag_choice_cb() {
    $option_name = 'googletranslateplugin_english_flag_choice';
    $new_value = 'us_flag';

    if ( get_option ( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option ( ''.$option_name.'' ); ?>

    <select name="googletranslateplugin_english_flag_choice" id="googletranslateplugin_english_flag_choice">
      <option value="us_flag" <?php if($options=='us_flag'){echo "selected";}?>>U.S. Flag</option>
      <option value="uk_flag" <?php if ($options=='uk_flag'){echo "selected";}?>>U.K Flag</option>
      <option value="canadian_flag" <?php if ($options=='canadian_flag'){echo "selected";}?>>Canadian Flag</option>
    </select>
   <?php
  }

  public function googletranslateplugin_spanish_flag_choice_cb() {
    $option_name = 'googletranslateplugin_spanish_flag_choice';
    $new_value = 'spanish_flag';

    if ( get_option ( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option ( ''.$option_name.'' ); ?>

    <select name="googletranslateplugin_spanish_flag_choice" id="googletranslateplugin_spanish_flag_choice">
      <option value="spanish_flag" <?php if($options=='spanish_flag'){echo "selected";}?>>Spanish Flag</option>
      <option value="mexican_flag" <?php if ($options=='mexican_flag'){echo "selected";}?>>Mexican Flag</option>
    </select>
   <?php
  }

  public function googletranslateplugin_portuguese_flag_choice_cb() {
    $option_name = 'googletranslateplugin_portuguese_flag_choice';
    $new_value = 'portuguese_flag';

    if ( get_option ( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option ( ''.$option_name.'' ); ?>

    <select name="googletranslateplugin_portuguese_flag_choice" id="googletranslateplugin_spanish_flag_choice">
      <option value="portuguese_flag" <?php if($options=='portuguese_flag'){echo "selected";}?>>Portuguese Flag</option>
      <option value="brazilian_flag" <?php if ($options=='brazilian_flag'){echo "selected";}?>>Brazilian Flag</option>
    </select>
   <?php
  }

  public function googletranslateplugin_seo_active_cb() {
    $option_name = 'googletranslateplugin_seo_active' ;
    $new_value = 0;
    if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option (''.$option_name.'');

    $html = '<input type="checkbox" name="googletranslateplugin_seo_active" id="googletranslateplugin_seo_active" value="1" '.checked(1,$options,false).'/>';
    echo $html;
  }

  public function googletranslateplugin_url_structure_choice_cb() {
      $option_name = 'googletranslateplugin_url_structure' ;

      if ( get_option( $option_name ) === false ) {
          // The option does not exist, so we update it.
          update_option( $option_name, 'sub_domain' );
      }

      $options = get_option (''.$option_name.''); ?>

      <input type="radio" name="googletranslateplugin_url_structure" id="sub_domain" value="sub_domain" <?php if($options=='sub_domain'){echo "checked";}?>/> <label for="sub_domain">Sub-domain (http://<b>es</b>.example.com/)</label><br/><br/>
      <input type="radio" name="googletranslateplugin_url_structure" id="sub_directory" value="sub_directory" <?php if($options=='sub_directory'){echo "checked";}?>/> <label for="sub_directory">Sub-directory (http://example.com/<b>de</b>/)</label>
  <?php }

  public function googletranslateplugin_url_translation_active_cb() {
    $option_name = 'googletranslateplugin_url_translation_active' ;
    $new_value = 0;
    if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option (''.$option_name.'');

    $html = '<input type="checkbox" name="googletranslateplugin_url_translation_active" id="googletranslateplugin_url_translation_active" value="1" '.checked(1,$options,false).'/>';
    echo $html;
  }

  public function googletranslateplugin_hreflang_tags_active_cb() {
    $option_name = 'googletranslateplugin_hreflang_tags_active' ;
    $new_value = 0;
    if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option (''.$option_name.'');

    $html = '<input type="checkbox" name="googletranslateplugin_hreflang_tags_active" id="googletranslateplugin_hreflang_tags_active" value="1" '.checked(1,$options,false).'/>';
    echo $html;
  }

  public function googletranslateplugin_css_cb() {

    $option_name = 'googletranslateplugin_css' ;
    $new_value = '';

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.'');

      $html = '<textarea style="width:100%;" rows="5" name="googletranslateplugin_css" id="googletranslateplugin_css">'.$options.'</textarea>';
    echo $html;
  }

  public function googletranslateplugin_multilanguage_cb() {

    $option_name = 'googletranslateplugin_multilanguage' ;
    $new_value = 0;

      if ( get_option( $option_name ) === false ) {

      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
      }

      $options = get_option (''.$option_name.'');

      $html = '<input type="checkbox" name="googletranslateplugin_multilanguage" id="googletranslateplugin_multilanguage" value="1" '.checked(1,$options,false).'/> &nbsp; Turn on multilanguage mode?';
      echo $html;
  }

  public function googletranslateplugin_exclude_translation_cb() {

    $option_name = 'googletranslateplugin_exclude_translation';
    $new_value = '';

    if (get_option($option_name) === false ) {
      // The option does not exist, so we update it.
      update_option( $option_name, $new_value );
    }

    $options = get_option (''.$option_name.'');

    $html = '<input type="text" name="'.$option_name.'" id="'.$option_name.'" value="'.$options.'" />';

    echo $html;
  }

  public function page_layout_cb() {
    include( plugin_dir_path( __FILE__ ) . '/css/style.php'); add_thickbox(); ?>
      <div id="gtp-settings" class="wrap">
        <div id="icon-options-general" class="icon32"></div>
      <h2><span class="notranslate">Google Language Translator</span></h2>
            <form action="<?php echo admin_url( '/options.php'); ?>" method="post">
              <div class="metabox-holder has-right-sidebar" style="float:left; width:65%">
                <div class="postbox gtp-main-settings" style="width: 100%">
                  <h3 class="notranslate">Main Settings</h3>
                    <?php settings_fields('google_language_translator'); ?>
                      <table style="border-collapse:separate" width="100%" border="0" cellspacing="8" cellpadding="0" class="form-table">
                        <tr>
                          <td style="width:60%" class="notranslate">Plugin Status:</td>
                          <td class="notranslate"><?php $this->googletranslateplugin_active_cb(); ?></td>
                        </tr>

                        <tr class="notranslate">
                          <td>Choose the original language of your website</td>
                          <td><?php $this->googletranslateplugin_language_cb(); ?></td>
                        </tr>

                        <tr class="notranslate">
                          <td colspan="2">What languages will be active? (<a class="select-all-languages" href="#">Select All</a> | <a class="clear-all-languages" href="#">Clear</a>)</td>
                        </tr>

                        <tr class="notranslate languages">
                          <td colspan="2"><?php $this->language_display_settings_cb(); ?></td>
                        </tr>
                      </table>
                </div> <!-- .postbox -->

              <div class="postbox gtp-seo-settings">

                <div class="postbox gtp-layout-settings" style="width: 100%">
                  <h3 class="notranslate">Language Switcher Settings</h3>
                  <table style="border-collapse:separate" width="100%" border="0" cellspacing="8" cellpadding="0" class="form-table">

                  <tr class="notranslate">
                    <td class="choose_flags_intro">Language switcher width:</td>
                    <td class="choose_flags_intro"><?php $this->gtp_language_switcher_width_cb(); ?></td>
                  </tr>

                  <tr class="notranslate">
                    <td class="choose_flags_intro">Language switcher text color:</td>
                    <td class="choose_flags_intro"><?php $this->gtp_language_switcher_text_color_cb(); ?></td>
                  </tr>

                  <tr class="notranslate">
                    <td class="choose_flags_intro">Language switcher background color:</td>
                    <td class="choose_flags_intro"><?php $this->gtp_language_switcher_bg_color_cb(); ?></td>
                  </tr>

                  <tr class="notranslate">
                    <td class="choose_flags_intro">Show flag images?<br/>(Display up to 104 flags above the language switcher)</td>
                    <td class="choose_flags_intro"><?php $this->googletranslateplugin_flags_cb(); ?></td>
                  </tr>

                    <tr class="notranslate">
                      <td>Show or hide the langauge switcher?</td>
                      <td><?php $this->googletranslateplugin_translatebox_cb(); ?></td>
                    </tr>

                    <tr class="notranslate">
                      <td>Layout option:</td>
                      <td><?php $this->googletranslateplugin_display_cb(); ?></td>
                    </tr>

                    <tr class="notranslate">
                      <td>Show Google Toolbar?</td>
                      <td><?php $this->googletranslateplugin_toolbar_cb(); ?></td>
                    </tr>

                    <tr class="notranslate">
                      <td>Show Google Branding? &nbsp;<a href="https://developers.google.com/translate/v2/attribution" target="_blank">Learn more</a></td>
              <td><?php $this->googletranslateplugin_showbranding_cb(); ?></td>
                    </tr>

                    <tr class="alignment notranslate">
                      <td class="flagdisplay">Align the translator left or right?</td>
                      <td class="flagdisplay"><?php $this->googletranslateplugin_flags_alignment_cb(); ?></td>
                    </tr>
                  </table>
                </div> <!-- .postbox -->

                <div class="postbox gtp-floating-widget-settings" style="width: 100%">
                  <h3 class="notranslate">Floating Widget Settings</h3>
                  <table style="border-collapse:separate" width="100%" border="0" cellspacing="8" cellpadding="0" class="form-table">
                    <tr class="floating_widget_show notranslate">
                      <td>Show floating translation widget?</td>
                      <td><?php $this->googletranslateplugin_floating_widget_cb(); ?></td>
                    </tr>

                    <tr class="floating-widget floating-widget-custom-text notranslate hidden">
                      <td>Custom text for the floating widget:</td>
                      <td><?php $this->googletranslateplugin_floating_widget_text_cb(); ?></td>
                    </tr>

                    <tr class="floating-widget floating-widget-text-translate notranslate hidden">
                      <td>Allow floating widget text to translate?:</td>
                      <td><?php $this->googletranslateplugin_floating_widget_text_allow_translation_cb(); ?></td>
                    </tr>

                    <tr class="floating-widget floating-widget-position notranslate hidden">
                      <td>Floating Widget Position:</td>
                      <td><?php $this->gtp_floating_widget_position_cb(); ?></td>
                    </tr>

                    <tr class="floating-widget floating-widget-text-color notranslate hidden">
                      <td>Floating Widget Text Color:</td>
                      <td><?php $this->gtp_floating_widget_text_color_cb(); ?></td>
                    </tr>

                    <tr class="floating-widget floating-widget-color notranslate hidden">
                      <td>Floating Widget Background Color</td>
                      <td><?php $this->gtp_floating_widget_bg_color_cb(); ?></td>
                    </tr>
                  </table>
                </div> <!-- .postbox -->

                <div class="postbox gtp-behavior-settings" style="width: 100%;display:none;">
                  <h3 class="notranslate">Behavior Settings</h3>
                    <table style="border-collapse:separate" width="100%" border="0" cellspacing="8" cellpadding="0" class="form-table">
                      <tr class="multilanguage notranslate">
                      <td>Multilanguage Page option? &nbsp;<a href="#TB_inline?width=200&height=150&inlineId=multilanguage-page-description" title="What is the Multi-Language Page Option?" class="thickbox">Learn more</a><div id="multilanguage-page-description" style="display:none"><p>If you activate this setting, Google will translate all text into a single language when requested by your user, even if text is written in multiple languages. In most cases, this setting is not recommended, although for certain websites it might be necessary.</p></div></td>
                      <td><?php $this->googletranslateplugin_multilanguage_cb(); ?></td>
                    </tr>

                    <tr class="notranslate">
                      <td>Google Analytics:</td>
                      <td><?php $this->googletranslateplugin_analytics_cb(); ?></td>
                    </tr>

                    <tr class="analytics notranslate">
                      <td>Google Analytics ID (Ex. 'UA-11117410-2')</td>
                      <td><?php $this->googletranslateplugin_analytics_id_cb(); ?></td>
                    </tr>
                  </table>
                </div> <!-- .postbox -->


        <?php
          if (isset($_POST['submit'])) {
            if (empty($_POST['submit']) && !check_admin_referer( 'gtp-save-settings', 'gtp-save-settings-nonce' )) {
              wp_die();
            }
          }
          wp_nonce_field('gtp-save-settings, gtp-save-settings-nonce', false);
              submit_button();
        ?>

        </div> <!-- .metbox-holder -->


        </form>
      </div> <!-- .wrap -->

      <?php
      $default_language = get_option('googletranslateplugin_language');
      $gtp_url_structure = get_option('googletranslateplugin_url_structure');
      $gtp_seo_active = get_option('googletranslateplugin_seo_active');

      $pro_version = $enterprise_version = null;
      if($gtp_seo_active == '1' and $gtp_url_structure == 'sub_domain')
         $pro_version = '1';
      if($gtp_seo_active == '1' and $gtp_url_structure == 'sub_directory')
         $enterprise_version = '1';
      ?>
      <script>window.intercomSettings = {app_id: "r70azrgx", 'platform': 'wordpress-gtp', 'translate_from': '<?php echo $default_language; ?>', 'is_sub_directory': <?php echo (empty($pro_version) ? '0' : '1'); ?>, 'is_sub_domain': <?php echo (empty($enterprise_version) ? '0' : '1'); ?>};(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/r70azrgx';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
<?php
  }
}

class gtp_Notices {
    protected $prefix = 'gtp';
    public $notice_spam = 0;
    public $notice_spam_max = 3;

    // Basic actions to run
    public function __construct() {
        // Runs the admin notice ignore function incase a dismiss button has been clicked
        add_action('admin_init', array($this, 'admin_notice_ignore'));
        // Runs the admin notice temp ignore function incase a temp dismiss link has been clicked
        add_action('admin_init', array($this, 'admin_notice_temp_ignore'));

        // Adding notices
        add_action('admin_notices', array($this, 'gtp_admin_notices'));
    }

    // Checks to ensure notices aren't disabled and the user has the correct permissions.
    public function gtp_admin_notice() {

        $gt_settings = get_option($this->prefix . '_admin_notice');
        if (!isset($gt_settings['disable_admin_notices']) || (isset($gt_settings['disable_admin_notices']) && $gt_settings['disable_admin_notices'] == 0)) {
            if (current_user_can('manage_options')) {
                return true;
            }
        }
        return false;
    }

    // Primary notice function that can be called from an outside function sending necessary variables
    public function admin_notice($admin_notices) {

        // Check options
        if (!$this->gtp_admin_notice()) {
            return false;
        }

        foreach ($admin_notices as $slug => $admin_notice) {
            // Call for spam protection

            if ($this->anti_notice_spam()) {
                return false;
            }

            // Check for proper page to display on
            if (isset( $admin_notices[$slug]['pages']) and is_array( $admin_notices[$slug]['pages'])) {

                if (!$this->admin_notice_pages($admin_notices[$slug]['pages'])) {
                    return false;
                }

            }

            // Check for required fields
            if (!$this->required_fields($admin_notices[$slug])) {

                // Get the current date then set start date to either passed value or current date value and add interval
                $current_date = current_time("n/j/Y");
                $start = (isset($admin_notices[$slug]['start']) ? $admin_notices[$slug]['start'] : $current_date);
                $start = date("n/j/Y", strtotime($start));
                $end = ( isset( $admin_notices[ $slug ]['end'] ) ? $admin_notices[ $slug ]['end'] : $start );
                $end = date( "n/j/Y", strtotime( $end ) );
                $date_array = explode('/', $start);
                $interval = (isset($admin_notices[$slug]['int']) ? $admin_notices[$slug]['int'] : 0);
                $date_array[1] += $interval;
                $start = date("n/j/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));
                // This is the main notices storage option
                $admin_notices_option = get_option($this->prefix . '_admin_notice', array());
                // Check if the message is already stored and if so just grab the key otherwise store the message and its associated date information
                if (!array_key_exists( $slug, $admin_notices_option)) {
                    $admin_notices_option[$slug]['start'] = $start;
                    $admin_notices_option[$slug]['int'] = $interval;
                    update_option($this->prefix . '_admin_notice', $admin_notices_option);
                }

                // Sanity check to ensure we have accurate information
                // New date information will not overwrite old date information
                $admin_display_check = (isset($admin_notices_option[$slug]['dismissed']) ? $admin_notices_option[$slug]['dismissed'] : 0);
                $admin_display_start = (isset($admin_notices_option[$slug]['start']) ? $admin_notices_option[$slug]['start'] : $start);
                $admin_display_interval = (isset($admin_notices_option[$slug]['int']) ? $admin_notices_option[$slug]['int'] : $interval);
                $admin_display_msg = (isset($admin_notices[$slug]['msg']) ? $admin_notices[$slug]['msg'] : '');
                $admin_display_title = (isset($admin_notices[$slug]['title']) ? $admin_notices[$slug]['title'] : '');
                $admin_display_link = (isset($admin_notices[$slug]['link']) ? $admin_notices[$slug]['link'] : '');
                $admin_display_dismissible= (isset($admin_notices[$slug]['dismissible']) ? $admin_notices[$slug]['dismissible'] : true);
                $output_css = false;

                // Ensure the notice hasn't been hidden and that the current date is after the start date
                if ($admin_display_check == 0 and strtotime($admin_display_start) <= strtotime($current_date)) {
                    // Get remaining query string
                    $query_str = esc_url(add_query_arg($this->prefix . '_admin_notice_ignore', $slug));

                    // Admin notice display output
                    echo '<div class="update-nag gtp-admin-notice">';
                    echo '<div class="gtp-notice-logo"></div>';
                    echo ' <p class="gtp-notice-title">';
                    echo $admin_display_title;
                    echo ' </p>';
                    echo ' <p class="gtp-notice-body">';
                    echo $admin_display_msg;
                    echo ' </p>';
                    echo '<ul class="gtp-notice-body gtp-red">
                          ' . $admin_display_link . '
                        </ul>';
                    if($admin_display_dismissible)
                        echo '<a href="' . $query_str . '" class="dashicons dashicons-dismiss"></a>';
                    echo '</div>';

                    $this->notice_spam += 1;
                    $output_css = true;
                }

                if ($output_css) {
                    wp_enqueue_style($this->prefix . '-admin-notices', plugins_url(plugin_basename(dirname(__FILE__))) . '/css/gtp-notices.css', array());
                }
            }

        }
    }

    // Spam protection check
    public function anti_notice_spam() {
        if ($this->notice_spam >= $this->notice_spam_max) {
            return true;
        }
        return false;
    }

    // Ignore function that gets ran at admin init to ensure any messages that were dismissed get marked
    public function admin_notice_ignore() {
        // If user clicks to ignore the notice, update the option to not show it again
        if (isset($_GET[$this->prefix . '_admin_notice_ignore'])) {
            $admin_notices_option = get_option($this->prefix . '_admin_notice', array());

            $key = $_GET[$this->prefix . '_admin_notice_ignore'];
            if(!preg_match('/^[a-z_0-9]+$/i', $key))
                return;

            $admin_notices_option[$key]['dismissed'] = 1;
            update_option($this->prefix . '_admin_notice', $admin_notices_option);
            $query_str = remove_query_arg($this->prefix . '_admin_notice_ignore');
            wp_redirect($query_str);
            exit;
        }
    }

    // Temp Ignore function that gets ran at admin init to ensure any messages that were temp dismissed get their start date changed
    public function admin_notice_temp_ignore() {
        // If user clicks to temp ignore the notice, update the option to change the start date - default interval of 14 days
        if (isset($_GET[$this->prefix . '_admin_notice_temp_ignore'])) {
            $admin_notices_option = get_option($this->prefix . '_admin_notice', array());
            $current_date = current_time("n/j/Y");
            $date_array   = explode('/', $current_date);
            $interval     = (isset($_GET['gt_int']) ? intval($_GET['gt_int']) : 14);
            $date_array[1] += $interval;
            $new_start = date("n/j/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));

            $key = $_GET[$this->prefix . '_admin_notice_temp_ignore'];
            if(!preg_match('/^[a-z_0-9]+$/i', $key))
                return;

            $admin_notices_option[$key]['start'] = $new_start;
            $admin_notices_option[$key]['dismissed'] = 0;
            update_option($this->prefix . '_admin_notice', $admin_notices_option);
            $query_str = remove_query_arg(array($this->prefix . '_admin_notice_temp_ignore', 'gt_int'));
            wp_redirect( $query_str );
            exit;
        }
    }

    public function admin_notice_pages($pages) {
        foreach ($pages as $key => $page) {
            if (is_array($page)) {
                if (isset($_GET['page']) and $_GET['page'] == $page[0] and isset($_GET['tab']) and $_GET['tab'] == $page[1]) {
                    return true;
                }
            } else {
                if ($page == 'all') {
                    return true;
                }
                if (get_current_screen()->id === $page) {
                    return true;
                }

                if (isset($_GET['page']) and $_GET['page'] == $page) {
                    return true;
                }
            }
        }

        return false;
    }

    // Required fields check
    public function required_fields( $fields ) {
        if (!isset( $fields['msg']) or (isset($fields['msg']) and empty($fields['msg']))) {
            return true;
        }
        if (!isset( $fields['title']) or (isset($fields['title']) and empty($fields['title']))) {
            return true;
        }
        return false;
    }

    // Special parameters function that is to be used in any extension of this class
    public function special_parameters($admin_notices) {
        // Intentionally left blank
    }
}

if(is_admin()) {
    global $pagenow;

    if(!defined('DOING_AJAX') or !DOING_AJAX)
        new gtp_Notices();
}

global $gtp_url_structure, $gtp_seo_active;

$gtp_url_structure = get_option('googletranslateplugin_url_structure');
$gtp_seo_active = get_option('googletranslateplugin_seo_active');

if($gtp_seo_active == '1' and $gtp_url_structure == 'sub_directory') { // gtranslate redirect rules with PHP (for environments with no .htaccess support (pantheon, flywheel, etc.), usually .htaccess rules override this)

    @list($request_uri, $query_params) = explode('?', $_SERVER['REQUEST_URI']);

    if(preg_match('/^\/(af|sq|am|ar|hy|az|eu|be|bn|bs|bg|ca|ceb|ny|zh-CN|zh-TW|co|hr|cs|da|nl|en|eo|et|tl|fi|fr|fy|gl|ka|de|el|gu|ht|ha|haw|iw|hi|hmn|hu|is|ig|id|ga|it|ja|jw|kn|kk|km|ko|ku|ky|lo|la|lv|lt|lb|mk|mg|ms|ml|mt|mi|mr|mn|my|ne|no|ps|fa|pl|pt|pa|ro|ru|sm|gd|sr|st|sn|sd|si|sk|sl|so|es|su|sw|sv|tg|ta|te|th|tr|uk|ur|uz|vi|cy|xh|yi|yo|zu)\/(af|sq|am|ar|hy|az|eu|be|bn|bs|bg|ca|ceb|ny|zh-CN|zh-TW|co|hr|cs|da|nl|en|eo|et|tl|fi|fr|fy|gl|ka|de|el|gu|ht|ha|haw|iw|hi|hmn|hu|is|ig|id|ga|it|ja|jw|kn|kk|km|ko|ku|ky|lo|la|lv|lt|lb|mk|mg|ms|ml|mt|mi|mr|mn|my|ne|no|ps|fa|pl|pt|pa|ro|ru|sm|gd|sr|st|sn|sd|si|sk|sl|so|es|su|sw|sv|tg|ta|te|th|tr|uk|ur|uz|vi|cy|xh|yi|yo|zu)\/(.*)$/', $request_uri, $matches)) {
        header('Location: ' . '/' . $matches[1] . '/' . $matches[3] . (empty($query_params) ? '' : '?'.$query_params), true, 301);
        exit;
    } // #1 redirect double language codes /es/en/...

    if(preg_match('/^\/(af|sq|am|ar|hy|az|eu|be|bn|bs|bg|ca|ceb|ny|zh-CN|zh-TW|co|hr|cs|da|nl|en|eo|et|tl|fi|fr|fy|gl|ka|de|el|gu|ht|ha|haw|iw|hi|hmn|hu|is|ig|id|ga|it|ja|jw|kn|kk|km|ko|ku|ky|lo|la|lv|lt|lb|mk|mg|ms|ml|mt|mi|mr|mn|my|ne|no|ps|fa|pl|pt|pa|ro|ru|sm|gd|sr|st|sn|sd|si|sk|sl|so|es|su|sw|sv|tg|ta|te|th|tr|uk|ur|uz|vi|cy|xh|yi|yo|zu)$/', $request_uri)) {
        header('Location: ' . $request_uri . '/' . (empty($query_params) ? '' : '?'.$query_params), true, 301);
        exit;
    } // #2 add trailing slash

    $get_language_choices = get_option ('language_display_settings');
    $items = array();
    foreach($get_language_choices as $key => $value) {
        if($value == 1)
            $items[] = $key;
    }
    $allowed_languages = implode('|', $items); // ex: en|ru|it|de

    if(preg_match('/^\/('.$allowed_languages.')\/(.*)/', $request_uri, $matches)) {
        $_GET['glang'] = $matches[1];
        $_GET['gurl'] = rawurldecode($matches[2]);

        require_once dirname(__FILE__) . '/url_addon/gtranslate.php';
        exit;
    } // #3 proxy translation
}

if($gtp_seo_active == '1' and ($gtp_url_structure == 'sub_directory' or $gtp_url_structure == 'sub_domain')) {
    add_action('wp_head', 'gtp_request_uri_var');
    if(isset($_GET['page']) and $_GET['page'] == 'google_language_translator')
        add_action('admin_head', 'gtp_request_uri_var');

    function gtp_request_uri_var() {
        global $gtp_url_structure;

        echo '<script>';
        echo "var gtp_request_uri = '".addslashes($_SERVER['REQUEST_URI'])."';";
        echo "var gtp_url_structure = '".addslashes($gtp_url_structure)."';";
        echo "var gtp_default_lang = '".addslashes(get_option('googletranslateplugin_language'))."';";
        echo '</script>';
    }

    if(get_option('googletranslateplugin_url_translation_active') == '1') {
        add_action('wp_head', 'gtp_url_translation_meta', 1);
        function gtp_url_translation_meta() {
            echo '<meta name="uri-translation" content="on" />';
        }
    }

    if(get_option('googletranslateplugin_hreflang_tags_active') == '1') {
        add_action('wp_head', 'gtp_add_hreflang_tags', 1);

        function gtp_add_hreflang_tags() {
            global $gtp_url_structure;

            $default_language = get_option('googletranslateplugin_language');
            $enabled_languages = array();
            $get_language_choices = get_option ('language_display_settings');
            foreach($get_language_choices as $key => $value) {
                if($value == 1)
                    $enabled_languages[] = $key;
            }

            //$current_url = wp_get_canonical_url();
            $current_url = network_home_url(add_query_arg(null, null));

            if($current_url !== false) {
                // adding default language
                if($default_language === 'iw')
                    echo '<link rel="alternate" hreflang="he" href="'.esc_url($current_url).'" />'."\n";
                elseif($default_language === 'jw')
                    echo '<link rel="alternate" hreflang="jv" href="'.esc_url($current_url).'" />'."\n";
                else
                    echo '<link rel="alternate" hreflang="'.$default_language.'" href="'.esc_url($current_url).'" />'."\n";

                // adding enabled languages
                foreach($enabled_languages as $lang) {
                    $href = '';
                    $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

                    if($gtp_url_structure == 'sub_domain')
                        $href = str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $lang . '.' . $domain, $current_url);
                    elseif($gtp_url_structure == 'sub_directory')
                        $href = str_ireplace('://' . $_SERVER['HTTP_HOST'], '://' . $_SERVER['HTTP_HOST'] . '/' . $lang, $current_url);

                    if(!empty($href) and $lang != $default_language) {
                        if($lang === 'iw')
                            echo '<link rel="alternate" hreflang="he" href="'.esc_url($href).'" />'."\n";
                        elseif($lang === 'jw')
                            echo '<link rel="alternate" hreflang="jv" href="'.esc_url($href).'" />'."\n";
                        else
                            echo '<link rel="alternate" hreflang="'.$lang.'" href="'.esc_url($href).'" />'."\n";
                    }
                }
            }
        }
    }
}


// translate WP REST API posts and categories data in JSON response
if($gtp_seo_active == '1') {
    function gtp_rest_post($response, $post, $request) {
        if(isset($response->data['content']) and is_array($response->data['content']))
            $response->data['content']['gt_translate_keys'] = array(array('key' => 'rendered', 'format' => 'html'));

        if(isset($response->data['excerpt']) and is_array($response->data['excerpt']))
            $response->data['excerpt']['gt_translate_keys'] = array(array('key' => 'rendered', 'format' => 'html'));

        if(isset($response->data['title']) and is_array($response->data['title']))
            $response->data['title']['gt_translate_keys'] = array(array('key' => 'rendered', 'format' => 'text'));

        if(isset($response->data['link']))
            $response->data['gt_translate_keys'] = array(array('key' => 'link', 'format' => 'url'));

        // more fields can be added here

        return $response;
    }

    function gtp_rest_category($response, $category, $request) {
        if(isset($response->data['description']))
            $response->data['gt_translate_keys'][] = array('key' => 'description', 'format' => 'html');

        if(isset($response->data['name']))
            $response->data['gt_translate_keys'][] = array('key' => 'name', 'format' => 'text');

        if(isset($response->data['link']))
            $response->data['gt_translate_keys'][] = array('key' => 'link', 'format' => 'url');

        // more fields can be added here

        return $response;
    }

    add_filter('rest_prepare_post', 'gtp_rest_post', 10, 3);
    add_filter('rest_prepare_category', 'gtp_rest_category', 10, 3);
}

// convert wp_localize_script format into JSON + JS parser
if($gtp_seo_active == '1') {
    function gtp_filter_l10n_scripts() {
        global $wp_scripts;

        $translate_handles = array(
            'agile-store-locator-script',
            'wmc-wizard',
            'wc-address-i18n',
            'wc-checkout',
            'wc-country-select',
            'wc-add-to-cart',
            'wc-password-strength-meter',
            'googlecode_regular',
            'googlecode_property',
            'googlecode_contact',
            'mapfunctions',
            'myhome-min',

        );

        //echo '<!--' . print_r($wp_scripts, true). '-->';
        //return;

        foreach($wp_scripts->registered as $handle => $script) {
            if(isset($script->extra['data']) and in_array($handle, $translate_handles)) {
                $l10n = $script->extra['data'];
                preg_match_all('/var (.+) = ({(.*)});/', $l10n, $matches);
                //echo '<!--' . print_r($matches, true). '-->';

                if(isset($matches[1]) and isset($matches[2])) {
                    $vars = $matches[1];
                    $scripts = $matches[2];
                } else
                    continue;

                foreach($vars as $i => $var_name) {
                    $attribute_ids = $wp_scripts->get_data($handle, 'attribute-ids');
                    $attribute_ids[] = $var_name . '-gtp-l10n-'.$i;
                    $jsons = $wp_scripts->get_data($handle, 'jsons');
                    $jsons[] = $scripts[$i];
                    $jss = $wp_scripts->get_data($handle, 'jss');
                    $jss[] = "var $var_name = JSON.parse(document.getElementById('$var_name-gtp-l10n-$i').innerHTML);";

                    $wp_scripts->add_data($handle, 'attribute-ids', $attribute_ids);
                    $wp_scripts->add_data($handle, 'jsons', $jsons);
                    $wp_scripts->add_data($handle, 'jss', $jss);
                }

                unset($wp_scripts->registered[$handle]->extra['data']);
            }
        }

        //echo '<!--' . print_r($wp_scripts, true). '-->';

    }

    function gtp_add_script_attributes($tag, $handle) {
        global $wp_scripts;

        gtp_filter_l10n_scripts();

        if(isset($wp_scripts->registered[$handle]->extra['attribute-ids'])) {
            $attribute_ids = $wp_scripts->get_data($handle, 'attribute-ids');
            $jsons = $wp_scripts->get_data($handle, 'jsons');
            $jss = $wp_scripts->get_data($handle, 'jss');

            $return = '';
            foreach($attribute_ids as $i => $attribute_id) {
                $json = $jsons[$i];
                $js = $jss[$i];

                $return .= "<script id='$attribute_id' type='application/json'>$json</script>\n<script type='text/javascript'>$js</script>\n";
            }

            return $return . $tag;
        }

        return $tag;
    }

    // filter for woocommerce script params
    function gtp_filter_woocommerce_scripts_data($data, $handle) {
        switch($handle) {
            case 'wc-address-i18n': {
                $data['gt_translate_keys'] = array(
                    array('key' => 'locale', 'format' => 'json'),
                    'i18n_required_text',
                    'i18n_optional_text',
                );

                $locale = json_decode($data['locale']);

                if(isset($locale->default->address_1))
                    $locale->default->address_1->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->address_2))
                    $locale->default->address_2->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->city))
                    $locale->default->city->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->postcode))
                    $locale->default->postcode->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->state))
                    $locale->default->state->gt_translate_keys = array('label', 'placeholder');

                if(isset($locale->default->shipping->address_1))
                    $locale->default->shipping->address_1->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->shipping->address_2))
                    $locale->default->shipping->address_2->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->shipping->city))
                    $locale->default->shipping->city->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->shipping->postcode))
                    $locale->default->shipping->postcode->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->shipping->state))
                    $locale->default->shipping->state->gt_translate_keys = array('label', 'placeholder');

                if(isset($locale->default->billing->address_1))
                    $locale->default->billing->address_1->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->billing->address_2))
                    $locale->default->billing->address_2->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->billing->city))
                    $locale->default->billing->city->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->billing->postcode))
                    $locale->default->billing->postcode->gt_translate_keys = array('label', 'placeholder');
                if(isset($locale->default->billing->state))
                    $locale->default->billing->state->gt_translate_keys = array('label', 'placeholder');

                $data['locale'] = json_encode($locale);
            } break;

            case 'wc-checkout': {
                $data['gt_translate_keys'] = array('i18n_checkout_error');
            } break;

            case 'wc-country-select': {
                $data['gt_translate_keys'] = array('i18n_ajax_error', 'i18n_input_too_long_1', 'i18n_input_too_long_n', 'i18n_input_too_short_1', 'i18n_input_too_short_n', 'i18n_load_more', 'i18n_no_matches', 'i18n_searching', 'i18n_select_state_text', 'i18n_selection_too_long_1', 'i18n_selection_too_long_n');
            } break;

            case 'wc-add-to-cart': {
                $data['gt_translate_keys'] = array('i18n_view_cart', array('key' => 'cart_url', 'format' => 'url'));
            } break;

            case 'wc-password-strength-meter': {
                $data['gt_translate_keys'] = array('i18n_password_error', 'i18n_password_hint', '');
            } break;

            default: break;
        }

        return $data;
    }

    function gtp_woocommerce_geolocate_ip($false) {
        if(isset($_SERVER['HTTP_X_GT_VIEWER_IP']))
            $_SERVER['HTTP_X_REAL_IP'] = $_SERVER['HTTP_X_GT_VIEWER_IP'];
        elseif(isset($_SERVER['HTTP_X_GT_CLIENTIP']))
            $_SERVER['HTTP_X_REAL_IP'] = $_SERVER['HTTP_X_GT_CLIENTIP'];

        return $false;
    }

    //add_action('wp_print_scripts', 'gtp_filter_l10n_scripts', 1);
    //add_action('wp_print_header_scripts', 'gtp_filter_l10n_scripts', 1);
    //add_action('wp_print_footer_scripts', 'gtp_filter_l10n_scripts', 1);

    add_filter('script_loader_tag', 'gtp_add_script_attributes', 100, 2);

    add_filter('woocommerce_get_script_data', 'gtp_filter_woocommerce_scripts_data', 10, 2 );

    add_filter('woocommerce_geolocate_ip', 'gtp_woocommerce_geolocate_ip', 10, 4);
}

$google_language_translator = new google_language_translator();

function gtp_update_option($old_value, $value, $option_name) {
    if(get_option('googletranslateplugin_seo_active') == '1' and get_option('googletranslateplugin_url_structure') == 'sub_directory') { // check if rewrite rules are in place
        $htaccess_file = get_home_path() . '.htaccess';
        // todo: use insert_with_markers functions instead
        if(is_writeable($htaccess_file)) {
            $htaccess = file_get_contents($htaccess_file);
            if(strpos($htaccess, 'gtranslate.php') === false) { // no config rules
                $rewrite_rules = file_get_contents(dirname(__FILE__) . '/url_addon/rewrite.txt');
                $rewrite_rules = str_replace('gtp_PLUGIN_PATH', str_replace(str_replace(array('https:', 'http:'), array(':', ':'), home_url()), '', str_replace(array('https:', 'http:'), array(':', ':'), plugins_url())) . '/google-language-translator', $rewrite_rules);

                $htaccess = $rewrite_rules . "\r\n\r\n" . $htaccess;
                if(!empty($htaccess)) { // going to update .htaccess
                    file_put_contents($htaccess_file, $htaccess);

                    add_settings_error(
                        'gtp_settings_notices',
                        esc_attr( 'settings_updated' ),
                        '<p style="color:red;">' . __('.htaccess file updated', 'gtp') . '</p>',
                        'updated'
                    );
                }
            }
        } else {
            $rewrite_rules = file_get_contents(dirname(__FILE__) . '/url_addon/rewrite.txt');
            $rewrite_rules = str_replace('gtp_PLUGIN_PATH', str_replace(home_url(), '', plugins_url()) . '/google-language-translator', $rewrite_rules);

            add_settings_error(
                'gtp_settings_notices',
                esc_attr( 'settings_updated' ),
                '<p style="color:red;">' . __('Please add the following rules to the top of your .htaccess file', 'gtp') . '</p><pre style="background-color:#eaeaea;">' . $rewrite_rules . '</pre>',
                'error'
            );
        }

        // update main_lang in config.php
        $config_file = dirname(__FILE__) . '/url_addon/config.php';
        if(is_readable($config_file) and is_writable($config_file)) {
            $config = file_get_contents($config_file);
            if(strpos($config, 'main_lang') !== false) {
                $config = preg_replace('/\$main_lang = \'[a-z-]{2,5}\'/i', '$main_lang = \''.get_option('googletranslateplugin_language').'\'', $config);
                if(is_string($config) and strlen($config) > 10)
                    file_put_contents($config_file, $config);
            }
        } else {
            add_settings_error(
                'gtp_settings_notices',
                esc_attr( 'settings_updated' ),
                '<p style="color:red;">' . __('Cannot update google-language-translator/url_addon/config.php file. Make sure to update it manually and set correct $main_lang.', 'gtp') . '</p>',
                'error'
            );
        }

    } else { // todo: remove rewrite rules
        // do nothing
    }
}

add_action('update_option_googletranslateplugin_seo_active', 'gtp_update_option', 10, 3);
add_action('update_option_googletranslateplugin_url_structure', 'gtp_update_option', 10, 3);
add_action('update_option_googletranslateplugin_language', 'gtp_update_option', 10, 3);

// exclude javascript minification by cache plugins for free version
if($gtp_seo_active != '1') {
    function cache_exclude_js_gtp($excluded_js) {
        if(is_array($excluded_js) or empty($excluded_js))
            $excluded_js[] = 'translate.google.com/translate_a/element.js';

        return $excluded_js;
    }

    // LiteSpeed Cache
    add_filter('litespeed_optimize_js_excludes', 'cache_exclude_js_gtp');

    // WP Rocket
    add_filter('rocket_exclude_js', 'cache_exclude_js_gtp');
    add_filter('rocket_minify_excluded_external_js', 'cache_exclude_js_gtp');
}