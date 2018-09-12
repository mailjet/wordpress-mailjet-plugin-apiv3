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
        if(empty($entry)) {
            return $msgId;
        }
        return strlen($entry->getMsgStr()) > 0 ? $entry->getMsgStr() : $entry->getMsgId();
    }

    public static function getLocale()
    {
        $locale = get_locale();
        if (in_array($locale, array('en_US', 'en_EN', 'en_GB'))) {
            $locale = 'en_US';
        }
        if (in_array($locale, array('de_DE', 'de_DE_formal'))) {
            $locale = 'de_DE';
        }
        return $locale;
    }

    public static function getSupportedLocales()
    {
        return array(
            'English' => 'en_US',
            'French' => 'fr_FR',
            'German' => 'de_DE',
            'Spanish' => 'es_ES',
        );
    }

}
