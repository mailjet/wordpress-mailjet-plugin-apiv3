<?php

namespace MailjetWp\MailjetPlugin\WidgetFormBuilder;

use MailjetWp\MailjetPlugin\Includes\Mailjeti18n;
use MailjetWp\MailjetPlugin\Includes\MailjetLogger;

/**
 * Plugin Name: Mailjet FormBuilder Widget
 */
class WP_Mailjet_FormBuilder_Widget extends \WP_Widget
{
    public const WIDGET_OPTIONS_NAME = 'mailjet_form_builder_widget_options';
    protected $widget_slug = 'wp_mailjet_form_builder_widget';

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct()
    {
        // load plugin text domain
        add_action('init', [$this, 'widget_textdomain']);
        // Build widget
        $widget_options = [
            'classname' => 'WP_Mailjet_FormBuilder_Widget',
            'description' => __('Allows your visitors to subscribe to one of your lists', 'mailjet-for-wordpress')
        ];
        parent::__construct(
            $this->get_widget_slug(),
            __('Mailjet Form Builder Widget', 'mailjet-for-wordpress'),
            $widget_options
        );

        add_action('admin_enqueue_scripts', array($this, 'register_widget_scripts'));
        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_front_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_front_scripts'));
    }

    /**
     * @return string
     */
    public function get_widget_slug(): string
    {
        return $this->widget_slug;
    }

    /**
     * Outputs the content of the widget.
     *
     * @param array $args args  The array of form elements
     * @param array $instance instance The current instance of the widget
     */
    public function widget($args, $instance)
    {
        if (get_option(self::WIDGET_OPTIONS_NAME) === false) {
            add_option(self::WIDGET_OPTIONS_NAME, $instance);
        }
        // Check if there is a cached output
        $cache = wp_cache_get($this->get_widget_slug(), 'widget');
        if (!is_array($cache)) {
            $cache = [];
        }
        if (!isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }
        if (isset($cache[$args['widget_id']])) {
            return print $cache[$args['widget_id']];
        }
        // Show front widget form
        // go on with your widget logic, put everything into a string and …
        extract($args, EXTR_SKIP);
        ob_start();
        $front_widget_file = apply_filters('mailjet_widget_form_filename', plugin_dir_path(__FILE__) . 'views/widget.php');
        include $front_widget_file;
        $widget_string = ob_get_clean();
        $cache[$args['widget_id']] = $widget_string;
        wp_cache_set($this->get_widget_slug(), $cache, 'widget');
        print $widget_string;
    }

    /**
     * @return void
     */
    public function flush_widget_cache(): void
    {
        wp_cache_delete($this->get_widget_slug(), 'widget');
    }
    /**
     * Processes the widget's options to be saved.
     *
     * @param array $new_instance new_instance The new instance of values to be generated via the update.
     * @param array $old_instance old_instance The previous instance of values before the update.
     */
    public function update($new_instance, $old_instance): array
    {
        // Here is where you update your widget's old values with the new, incoming values
        $instance = $old_instance;
        $instance['form_builder_code'] = wp_kses($new_instance['form_builder_code'] ?? '', [
            'iframe' => [
                'align' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'name' => true,
                'src' => true,
                'id' => true,
                'class' => true,
                'style' => true,
                'scrolling' => true,
                'marginwidth' => true,
                'marginheight' => true,
                'data' => true,
                'data-w-type' => true,
                'data-w-token' => true,
            ],
            'script' => [
                'type' => true,
                'src' => true,
                'height' => true,
                'width' => true,
            ]
        ]);
        update_option(self::WIDGET_OPTIONS_NAME, $instance);
        return $instance;
    }

    /**
     * Generates the administration form for the widget.
     *
     * @param array $instance instance The array of keys and values for the widget.
     */
    public function form($instance)
    {
        wp_enqueue_style(
            $this->get_widget_slug() . '-widget-styles',
            plugins_url('css/widget.css',__FILE__),
            [],
            MAILJET_VERSION,
            'all'
        );

        wp_enqueue_script($this->get_widget_slug() . '-script');
        $admin_locale = Mailjeti18n::getLocale();
        $languages = Mailjeti18n::getSupportedLocales();
        $pages = get_pages();
        $instance = wp_parse_args((array) $instance);

        include plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'admin.php';
    }

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain(): void
    {
        load_plugin_textdomain('mailjet-for-wordpress', false, dirname(plugin_basename(__FILE__), 3) . '/languages/');
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ mailjet text domain loaded ] - ' . dirname(plugin_basename(__FILE__), 3) . '/languages/');
    }

    /**
     * @return void
     */
    public function register_widget_front_styles(): void
    {
        wp_register_style(
            $this->get_widget_slug() . '-widget-front-styles',
            plugins_url('css/front-widget.css', __FILE__),
            [],
            MAILJET_VERSION,
            'all');
    }
    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts(): void
    {
        wp_localize_script($this->get_widget_slug() . '-script', 'myAjax', ['ajaxurl' => admin_url('admin-ajax.php')]);
        wp_enqueue_script($this->get_widget_slug() . '-script');
        wp_enqueue_style($this->get_widget_slug() . '-widget-styles', plugins_url('css/widget.css', __FILE__), [], MAILJET_VERSION, 'all');
    }

    /**
     * @return void
     */
    public function register_widget_front_scripts(): void
    {
        wp_enqueue_script('jquery');
        wp_register_script($this->get_widget_slug() . '-front-script', plugins_url('js/front-widget.js', __FILE__), ['jquery'], false, true);
        wp_localize_script($this->get_widget_slug() . '-front-script', 'mjWidget', ['ajax_url' => admin_url('admin-ajax.php')]);
        wp_enqueue_script($this->get_widget_slug() . '-front-script');
        wp_enqueue_style($this->get_widget_slug() . '-widget-front-styles', plugins_url('css/front-widget.css', __FILE__));
    }
}
