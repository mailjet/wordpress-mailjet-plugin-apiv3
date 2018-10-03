<?php

namespace MailjetPlugin\Includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      5.0.0
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class Mailjeti18n
{

    public static $supportedLocales = array(
        'English' => 'en_US',
        'French' => 'fr_FR',
        'German' => 'de_DE',
        'Spanish' => 'es_ES',
        'Italian' => 'it_IT',
    );

    /**
     * Load the plugin text domain for translation.
     *
     * @since    5.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
                'mailjet', false, dirname(dirname(dirname(plugin_basename(__FILE__)))) . '/languages/'
        );
        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'mailjet\' text domain loaded ]');
    }

    /**
     * Provide array with translations in a format [key => message] and a locale to trnaslate to
     *
     * @param string $locale
     * @param array $translations
     * @return bool true - if succesfully updated or added translations | false - if something went wrong and translations were not updated
     */
    public static function updateTranslationsInFile($locale = 'en_US', array $translations = array())
    {
        if (empty($locale) || empty($translations)) {
            \MailjetPlugin\Includes\MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Empty Locale or Translation messages provided ] ');
            return false;
        }

        $filePo = dirname(dirname(dirname((__FILE__)))) . '/languages/mailjet-' . $locale . '.po';
        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Translations PO file loaded ] - ' . $filePo);

        // Parse a po file
        $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($filePo);
        $poParser = new \Sepia\PoParser\Parser($fileHandler);
        $catalog = $poParser->parse();

        foreach ($translations as $keyToTranslate => $textToTranslate) {
            $entry = $catalog->getEntry($keyToTranslate);
            if (!empty($entry)) {
                $catalog->removeEntry($keyToTranslate);
            }

            $catalog->addEntry(new \Sepia\PoParser\Catalog\Entry($keyToTranslate, $textToTranslate));
        }

        // Compile the updated .mo file
        $compiler = new \Sepia\PoParser\PoCompiler();
        $fileHandler->save($compiler->compile($catalog));

        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Translations PO and MO file updated ]');

        return true;
    }

    public static function getTranslationsFromFile($locale, $msgId)
    {
        $filePo = dirname(dirname(dirname((__FILE__)))) . '/languages/mailjet-' . $locale . '.po';
        \MailjetPlugin\Includes\MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Translations PO file loaded ] - ' . $filePo);
        $fileHandler = new \Sepia\PoParser\SourceHandler\FileSystem($filePo);
        $poParser = new \Sepia\PoParser\Parser($fileHandler);
        $catalog = $poParser->parse();
        $entry = $catalog->getEntry($msgId);
        if (empty($entry)) {
            return $msgId;
        }
        return strlen($entry->getMsgStr()) > 0 ? $entry->getMsgStr() : $entry->getMsgId();
    }

    /**
     * Get locale, if it is not supported the default en_US is returned
     * @return string
     */
    public static function getLocale()
    {
        $locale = get_locale();
        if (in_array($locale, array('en_US', 'en_EN', 'en_GB')) && !in_array($locale, array_values(self::$supportedLocales))) {
            $locale = 'en_US';
        }

        if (in_array($locale, array('de_DE', 'de_DE_formal'))) {
            $locale = 'de_DE';
        }
        return $locale;
    }

    public static function getSupportedLocales()
    {
        return self::$supportedLocales;
    }

    /**
     * Get user language via locale, if the language is not supported returns the default en_US
     * @return string
     */
    public static function getCurrentUserLanguage()
    {
        $locale = self::getLocale();
        $languages = array_flip(self::$supportedLocales);
        if (!isset($languages[$locale])) {
            // return English if the language is not supported
            $locale = 'en_US';
        }
        return $languages[$locale];
    }


    public static function getMailjetSupportLinkByLocale()
    {
        $locale = self::getLocale();
        $supportedLocales = array_flip(self::getSupportedLocales());
        if (!isset($supportedLocales[$locale])) {
            // return English if the language is not supported
            $locale = 'en_US';
        }
        return "https://app.mailjet.com/support?lc=" . $locale;
    }

    /**
     * Get user locale depends on polylang cookie
     * @param str $pll
     */
    public static function getLocaleByPll()
    {
        if(empty($_COOKIE['pll_language'])) {
            // The user language is not changed via polylang
            return false;
        }
        
        $pll = $_COOKIE['pll_language'];

        switch($pll) {
            case 'en' : $locale = 'en_US';break;
            case 'fr' : $locale = 'fr_FR';break;
            case 'de' : $locale = 'de_DE';break;
            case 'es' : $locale = 'es_ES';break;
            case 'it' : $locale = 'it_IT';break;
            // If given pll is not supported get current language
            default : $locale = self::getCurrentUserLanguage();
        }
        return $locale;
    }

    public static function getMailjetUserGuideLinkByLocale()
    {
        $locale = self::getLocale();
        $supportedLocales = array_flip(self::getSupportedLocales());
        if (!isset($supportedLocales[$locale])) {
            // return English if the language is not supported
            $locale = 'en_US';
        }

        switch ($locale) {
            case 'fr_FR':
                $link = 'https://www.mailjet.com/guides/wordpress-user-guide-fr/';
                break;
            case 'de_DE':
                $link = 'https://www.mailjet.com/guides/wordpress-user-guide-de/';
                break;
            case 'es_ES':
                $link = 'https://www.mailjet.com/guides/wordpress-user-guide-es/';
                break;
            case 'it_IT':
                $link = 'https://www.mailjet.com/guides/wordpress-user-guide-it/';
                break;
            default:
                $link = 'https://www.mailjet.com/guides/wordpress-user-guide/';
        }

        return $link;
    }
}
