<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author		Mailjet
 * @link		http://www.mailjet.com/
 *
 */

class WP_Mailjet_Subscribe_Widget extends WP_Widget
{
	protected $api;
	private $lists = FALSE;

	function __construct()
	{
		//No dependency injection possible, so we have to use this:
		$this->api = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));

		$widget_ops = array('classname' => 'WP_Mailjet_Subscribe_Widget', 'description' => __('Allows your visitors to subscribe to one of your lists', 'wp-mailjet'));

		parent::__construct(FALSE, 'Subscribe to our newsletter', $widget_ops);
		add_action('wp_ajax_mailjet_subscribe_ajax_hook', array($this, 'mailjet_subscribe_from_widget'));
		add_action('wp_ajax_nopriv_mailjet_subscribe_ajax_hook', array($this, 'mailjet_subscribe_from_widget'));

		wp_enqueue_script('ajax-example', plugin_dir_url( __FILE__ ) . 'assets/js/ajax.js', array( 'jquery' ));
		wp_localize_script('ajax-example', 'WPMailjet', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-example-nonce')
		));
	}

	/**
	 * Get list of contact lists
	 * 
	 * @param void
     * @return (array) $this->lists 
	 */
	function getLists()
	{
		if ($this->lists === FALSE)		
		{
			$this->lists = $this->api->getContactLists(array('limit' => 0));
			if (isset($this->lists->Status) && $this->lists->Status == 'ERROR')
				$this->lists = array();		
		}

		return $this->lists;
	}

	function form($instance)
	{
		$instance =		wp_parse_args((array)$instance, array('title' => '', 'list_id' => '' , 'button_text' => ''));
		$title =		$instance['title'];
		$list_id =		get_option('mailjet_auto_subscribe_list_id');
		$button_text =	$instance['button_text'];
?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">
			<?php echo __('Title:', 'wp-mailjet') ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</label>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('button_text'); ?>">
			<?php echo __('Button text:', 'wp-mailjet') ?>
			<input class="widefat" id="<?php echo $this->get_field_id('button_text'); ?>" name="<?php echo $this->get_field_name('button_text'); ?>" type="text" value="<?php echo esc_attr($button_text); ?>" />
		</label>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('list_id'); ?>">
			<?php echo __('List:', 'wp-mailjet') ?>
			<select class="widefat" id="<?php echo $this->get_field_id('list_id'); ?>" name="<?php echo $this->get_field_name('list_id'); ?>">
				<?php foreach ($this->getLists() as $list) { ?>
					<option value="<?php echo $list['value']?>"<?php echo ($list['value'] == esc_attr($list_id) ? ' selected="selected"' : '') ?>><?php echo $list['label']?></option>
				<?php } ?>
			</select>
		</label>
	</p>
<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['list_id'] = $new_instance['list_id'];
		update_option('mailjet_auto_subscribe_list_id', $instance['list_id']);
		$instance['button_text'] = $new_instance['button_text'];

		return $instance;
	}

	/**
	 * Subscribe the user from the widget	 
	 *  
	 * @param void
     * @return void
	 */
	public function mailjet_subscribe_from_widget()
	{
		// Get some variables - email, list_id, etc.
		$email = $_POST['email'];
		$list_id = $_POST['list_id'];
					
		// Add the contact to the contact list
		$result = $this->api->addContact(array(
			'Email'		=> $email,
			'ListID'	=> $list_id
		));
		
		// Check what is the response and display proper message
		if(isset($result->Status)) {
			if($result->Status == 'DUPLICATE'){
				echo '<p class="error">';
				echo sprintf(__("The contact %s is already subscribed", 'wp-mailjet'), $email);
				echo '</p>';
				die();
			}
			else if($result->Status == 'ERROR'){
				echo '<p class="error">';
				echo sprintf(__("Sorry %s we couldn't subscribe at this time", 'wp-mailjet'), $email);
				echo '</p>';
				die();
			}			
		}
		
		// Adding was successful
		echo '<p class="success">';
		echo sprintf(__("Thanks for subscribing with %s", 'wp-mailjet'), $email);
		echo '</p>';
		die();
	}

	function widget($args, $instance)
	{			
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$list_id = get_option('mailjet_auto_subscribe_list_id');
		$button_text = $instance['button_text'];

		// If contact list is not selected then we just don't display the widget!
		if(!is_numeric($list_id))
			return FALSE;
		
		if (!empty($title))
			echo $before_title . $title . $after_title;;

		// WIDGET CODE GOES HERE
		echo '
		<form class="subscribe-form">
			<input id="email" name="email" value="" type="email" placeholder="' . __('your@email.com',' wp-mailjet') . '" />
			<input name="action" type="hidden" value="mailjet_subscribe_ajax_hook" />
			<input name="list_id" type="hidden" value="' . $list_id . '" />
			<input name="submit" type="submit" class="mailjet-subscribe" value="' . $button_text . '">
		</form>
		<div class="response">
		</div>';

		echo $after_widget;
	}
	
	function validate_email($email) {
		return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
	}
}