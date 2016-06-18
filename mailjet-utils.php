<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author        Mailjet
 * @link        http://www.mailjet.com/
 *
 */
class WP_Mailjet_Utils
{
    public static function custom_notice($type, $message)
    {
        echo '<div class="' . $type . '"><p>' . $message . '</p></div>';
    }
}

/**
 * Replace {{content}} in page to respective message
 * @param $content
 * @return mixed
 */
function mailjet_custom_page_content_filter($content)
{
    $text = str_replace(array('%content%', '{{content}}'), $content, $GLOBALS["mailjet_msg"]);
    return $text;

}