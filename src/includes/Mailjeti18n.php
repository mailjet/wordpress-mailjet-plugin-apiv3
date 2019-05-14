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

    const DEFAULT_LANGUAGE_DIR = MAILJET_PLUGIN_DIR .'languages'.DIRECTORY_SEPARATOR;
    const CUSTOM_LANGUAGE_DIR = (WP_CONTENT_DIR) .DIRECTORY_SEPARATOR. 'languages'. DIRECTORY_SEPARATOR .'plugins'.DIRECTORY_SEPARATOR;

    /**
     * Load the plugin text domain for translation.
     *
     * @since    5.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain('mailjet-for-wordpress', false, self::DEFAULT_LANGUAGE_DIR);
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ \'mailjet\' text domain loaded ]');
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
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Empty Locale or Translation messages provided ] ');
            return false;
        }

        $filePo = self::getTranslationFile('mailjet-for-wordpress-' . $locale . '.po');
        if ($filePo === false) {
            MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ File could\'t be found ] ');
            return false;
        }

        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Translations PO file loaded ] - ' . $filePo);

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

        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Translations PO and MO file updated ]');

        return true;
    }

    public static function getTranslationsFromFile($locale, $msgId)
    {
        $filePo = self::getTranslationFile('mailjet-for-wordpress-' . $locale . '.po');
        if ($filePo === false) {
            return $msgId;
        }
        MailjetLogger::info('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Translations PO file loaded ] - ' . $filePo);
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

        // de_DE_formal consider as de_DE
        if (in_array($locale, array('de_DE', 'de_DE_formal'))) {
            $locale = 'de_DE';
        }

        // Use en_US as main if locale is some en
        if (in_array($locale, array('en_US', 'en_EN', 'en_GB'))) {
            $locale = 'en_US';
        }

        // Use en_US if locale is not supported
        if (!in_array($locale, array_values(self::getSupportedLocales()))) {
            $locale = 'en_US';
        }

        return $locale;
    }

    public static function getSupportedLocales()
    {
        $customLocales = self::getAllSupportedLanguages();
        if (empty($customLocales)){
            return self::$supportedLocales;
        }
        /*This is needed so that the language language order is preserved and the customer file is loaded with priority!*/
        $result = array_merge(self::$supportedLocales, $customLocales);
        $result = array_unique(array_reverse($result, true));

        return array_reverse($result, true);
    }

    /**
     * Get user language via locale, if the language is not supported returns the default en_US
     * @return string
     */
    public static function getCurrentUserLanguage()
    {
        $locale = self::getLocale();
        $languages = array_flip(self::getSupportedLocales());
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
        if (empty($_COOKIE['pll_language'])) {
            // The user language is not changed via polylang
            return self::getLocale();
        }

        $pll = $_COOKIE['pll_language'];

        switch ($pll) {
            case 'en' : $locale = 'en_US';
                break;
            case 'fr' : $locale = 'fr_FR';
                break;
            case 'de' : $locale = 'de_DE';
                break;
            case 'es' : $locale = 'es_ES';
                break;
            case 'it' : $locale = 'it_IT';
                break;
            // If given pll is not supported get current language
            default : $locale = self::getLocale();
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

    private static function getAllSupportedLanguages()
    {
        $customLanguages = [];
        $customLanguagesDir = (ABSPATH) . 'wp-content/languages/plugins';
        if (!file_exists($customLanguagesDir)) {
            return $customLanguages;
        }

        $dir = new \DirectoryIterator($customLanguagesDir);
        foreach ($dir as $fileInfo) {
            if (strpos($fileInfo->getFilename(), 'mailjet-for-wordpress-') !== 0) {
                continue;
            }

            $fileBasename = $fileInfo->getBasename('.' . $fileInfo->getExtension());
            if (!file_exists($customLanguagesDir . DIRECTORY_SEPARATOR . $fileBasename . '.mo') || !file_exists($customLanguagesDir . DIRECTORY_SEPARATOR . $fileBasename . '.po')) {
                continue;
            }

            $languageCode = str_replace('mailjet-for-wordpress-', '', $fileBasename);
            $customLanguages[$languageCode] = $languageCode;
        }

        return $customLanguages;
    }

    private static function getTranslationFile($filename)
    {
        $customFIleInfo = new \SplFileInfo(self::CUSTOM_LANGUAGE_DIR . $filename);
        if ($customFIleInfo->isFile() && $customFIleInfo->isWritable()){
            return $customFIleInfo->getRealPath();
        }else{
            $defaultFileInfo = new \SplFileInfo( self::DEFAULT_LANGUAGE_DIR . $filename);
            if ($defaultFileInfo->isFile() && $defaultFileInfo->isWritable()){
                return $defaultFileInfo->getRealPath();
            }
        }

        return false;
    }

}
