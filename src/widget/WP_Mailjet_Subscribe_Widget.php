<?php
namespace MailjetPlugin\Widget;

class WP_Mailjet_Subscribe_Widget extends \WP_Widget
{
    /**
     *
     * Unique identifier for your widget.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    5.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'mailjet';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct()
    {
		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );		

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Mailjet Subscription Widget', $this->get_widget_slug() ),
			array(
				'classname'  => 'WP_Mailjet_Subscribe_Widget',
				'description' => __( 'Allows your visitors to subscribe to one of your lists', $this->get_widget_slug() )
			)
		);

var_dump($this->get_settings());

		// Register site styles and scripts
        add_action('admin_print_styles', array($this, 'register_widget_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_widget_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_widget_scripts'));

        // Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

	} // end constructor


    /**
     * Return the widget slug.
     *
     * @since    5.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug()
    {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget($args, $instance)
    {
		// Check if there is a cached output
		$cache = wp_cache_get($this->get_widget_slug(),'widget');

		if (!is_array($cache))
			$cache = array();

		if (!isset($args['widget_id']))
			$args['widget_id'] = $this->id;

		if (isset($cache[$args['widget_id']]))
			return print $cache[$args['widget_id']];
		
		// go on with your widget logic, put everything into a string and …


		extract($args,EXTR_SKIP);

		$widget_string = $before_widget;

		// TODO: Here is where you manipulate your widget's values based on their input fields
		ob_start();
		include(plugin_dir_path(__FILE__) . 'views/widget.php');
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[$args['widget_id']] = $widget_string;

		wp_cache_set($this->get_widget_slug(), $cache,'widget');

		print $widget_string;

	} // end widget
	
	
	public function flush_widget_cache() 
	{
    	wp_cache_delete($this->get_widget_slug(),'widget');
	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update($new_instance, $old_instance)
    {
		$instance = $old_instance;

		// TODO: Here is where you update your widget's old values with the new, incoming values

        $instance['title']    = isset($new_instance['title']) ? wp_strip_all_tags($new_instance['title']) : '';
        $instance['text']     = isset($new_instance['text']) ? wp_strip_all_tags($new_instance['text']) : '';
        $instance['textarea'] = isset($new_instance['textarea']) ? wp_kses_post($new_instance['textarea']) : '';
        $instance['checkbox'] = isset($new_instance['checkbox']) ? 1 : false;
        $instance['select']   = isset($new_instance['select']) ? wp_strip_all_tags($new_instance['select']) : '';


		return $instance;

	} // end update

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form($instance)
    {

		// TODO: Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		// TODO: Store the values of the widget in their own variable

		// Display the admin form
		include(plugin_dir_path(__FILE__) . 'views/admin.php');

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain()
    {

		load_plugin_textdomain($this->get_widget_slug(), false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');

	} // end widget_textdomain



	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles()
    {

		wp_enqueue_style($this->get_widget_slug().'-widget-styles', plugins_url('css/widget.css', __FILE__));

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts()
    {

		wp_enqueue_script($this->get_widget_slug().'-script', plugins_url('js/widget.js', __FILE__), array('jquery'));

	} // end register_widget_scripts

} // end class
