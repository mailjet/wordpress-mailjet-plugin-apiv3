<?php

declare (strict_types=1);
namespace MailjetWp\Bamarni\Composer\Bin\Config;

use MailjetWp\Composer\Config as ComposerConfig;
use MailjetWp\Composer\Factory;
use MailjetWp\Composer\Json\JsonFile;
use MailjetWp\Composer\Json\JsonValidationException;
use MailjetWp\Seld\JsonLint\ParsingException;
final class ConfigFactory
{
    /**
     * @throws JsonValidationException
     * @throws ParsingException
     */
    public static function createConfig() : ComposerConfig
    {
        $config = Factory::createConfig();
        $file = new JsonFile(Factory::getComposerFile());
        if (!$file->exists()) {
            return $config;
        }
        $file->validateSchema(JsonFile::LAX_SCHEMA);
        $config->merge($file->read());
        return $config;
    }
    private function __construct()
    {
    }
}
