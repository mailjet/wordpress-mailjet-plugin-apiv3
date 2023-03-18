<?php

namespace MailjetWp\MailjetPlugin\Admin;

use MailjetWp\MailjetPlugin\Includes\Mailjeti18n;/**
 * The admin-specific functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 * @package    Mailjet
 * @subpackage Mailjet/admin
 * @author     Your Name <email@example.com>
 */
class MailjetAdmin
{
    /**
     * The ID of this plugin.
     *
     * @since    5.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;
    /**
     * The version of this plugin.
     *
     * @since    5.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    /**
     * Initialize the class and set its properties.
     *
     * @since    5.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    5.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mailjet-admin.css', array(), $this->version, 'all');
    }
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    5.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mailjet-admin.js', array('jquery'), $this->version, \false);
    }

    /**
     * @return void
     */
    public function mailjetPluginNotification(): void
    {
        if (function_exists('get_current_screen')) {
            $currentScreen = get_current_screen();
            if ($currentScreen &&
                ($currentScreen->id === 'dashboard' ||
                    $currentScreen->id === 'admin_page_mailjet_dashboard_page' ||
                    $currentScreen->id === 'admin_page_mailjet_connect_account_page'
                )
            ) {
                echo sprintf('<div class="notice notice-warning"><p>%s</p></div>', $this->getWarningTranslation(get_locale()));
            }
        }
    }

    /**
     * @param string $locale
     * @return string
     */
    private function getWarningTranslation(string $locale): string
    {
        switch ($locale) {
            case 'en_US':
                return 'Mailjet\'s Subscription Widget is on its way out, but a new way to integrate your forms is coming shortly. Stay tuned!';
            case 'fr_FR':
                return 'Le widget d\'inscription de Mailjet tire bientôt sa révérence, mais une nouvelle méthode d\'intégration des formulaires est disponible. Découvrez-le !';
            case 'de_DE':
                return 'Wir haben die Weiterentwicklung unseres Abonnement-Widget eingestellt. Von nun an bieten wir unseren Nutzern jedoch eine Alternative, um Formulare zu integrieren. Probieren Sie es aus!';
            case 'es_ES':
                return 'El Widget de suscripción de Mailjet está a punto de despedirse, así que tendrás formas nuevas de integrar tus formularios. No te las pierdas.';
            case 'it_IT':
                return 'Il widget di iscrizione di Mailjet è in fase di dismissione, ma è disponibile un nuovo modo per integrare i vostri moduli. Scopritelo!';
            case 'da_DK':
                return 'Mailjets tilmeldingswidget er ved at blive udfaset, men der er en ny måde at integrere dine formularer på. Tjek det ud!';
            case 'nl_NL':
                return 'De abonnementswidget van Mailjet verdwijnt, maar er is een nieuwe manier om je formulieren te integreren. Probeer het uit!';
            case 'pt_BR':
                return 'O Widget de Assinatura do Mailjet está sendo descontinuado, mas uma nova maneira de integrar os seus formulários chegará está disponível. Confira!';
            case 'pt_PT':
                return 'O widget de subscrição da Mailjet está a caminho, mas uma nova forma de integrar os seus formulários está disponível. Veja!';
            case 'sv_SE':
                return 'Mailjets prenumerationswidget är på väg ut, men det finns ett nytt sätt att integrera dina formulär. Kolla in det!';
            default:
                return 'Mailjet\'s Subscription Widget is on its way out, but a new way to integrate your forms is coming shortly. Stay tuned!';
        }
    }
}
