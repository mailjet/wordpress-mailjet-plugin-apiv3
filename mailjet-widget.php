<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author		Mailjet
 * @link		http://www.mailjet.com/
 *
 */

class MailjetSubscribeWidget extends WP_Widget
{
	protected $api;
	private $lists = false;

	function __construct()
	{
		//No dependency injection possible, so we have to use this:
		$this->api = new Mailjet(get_option('mailjet_username'), get_option('mailjet_password'));

		$widget_ops = array('classname' => 'MailjetSubscribeWidget', 'description' => __('Allows your visitors to subscribe to one of your lists', 'wp-mailjet'));

		parent::__construct(false, 'Subscribe to our newsletter', $widget_ops);
		add_action('wp_ajax_mailjet_subscribe_ajax_hook', array($this, 'mailjet_subscribe_from_widget'));
		add_action('wp_ajax_nopriv_mailjet_subscribe_ajax_hook', array($this, 'mailjet_subscribe_from_widget'));

		wp_enqueue_script('ajax-example', plugin_dir_url( __FILE__ ) . 'js/ajax.js', array( 'jquery' ));
		wp_localize_script('ajax-example', 'WPMailjet', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-example-nonce')
		));
	}

	function getLists()
	{
		if ($this->lists == false)
		{
			if ($apiLists = $this->api->liststatistics(array('akid' => $this->api->_akid, 'limit' => 0)))
				$this->lists = $apiLists->Data;
			else
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
				<option value="<?php echo $list->ID?>"<?php echo ($list->ID == esc_attr($list_id) ? ' selected="selected"' : '') ?>><?php echo $list->Name?></option>
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

	public function mailjet_subscribe_from_widget()
	{
		$email = $_POST['email'];
		$list_id = $_POST['list_id'];
		
		$params = array(
			'method'	=> 'POST',
			'Action'	=> 'Add',
			'Addresses'	=> array($email),
			'ListID'	=> $list_id
		);		
		$result = $this->api->manycontacts($params);

		if(isset($result->Data['0']->Errors->Items)) {
			if( strpos($result->Data['0']->Errors->Items[0]->ErrorMessage, 'duplicate') !== false ){
				echo '<p class="error">';
				echo sprintf(__("The contact %s is already subscribed", 'wp-mailjet'), $email);
				echo '</p>';
				die();
			}
			else{
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
		return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? false : true;
	}
}