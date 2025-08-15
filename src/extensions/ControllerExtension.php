<?php

namespace SilverCommerce\Settings\Extensions;

use NumberFormatter;
use SilverStripe\Core\Environment;
use SilverStripe\i18n\i18n;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\DevelopmentAdmin;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Dev\DevBuildController;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\DatabaseAdmin;

class ControllerExtension extends Extension
{
    /**
     * Customise the default silverstripe locale config
     */
    public function onBeforeInit()
    {
        $tests = Environment::getEnv('UNIT_TESTS_RUNNING');
        $disallowed_controllers = [
            DevelopmentAdmin::class,
            DevBuildController::class,
            DatabaseAdmin::class
        ];

        // Don't run this during dev/build or dev/tasks or when unit tests are running
        if (!$tests && !in_array(get_class($this->owner), $disallowed_controllers)) {
            // Set global local based on Site Config
            $config = SiteConfig::current_site_config();
            $locale = $config->SiteLocale;

            // Fallback to default locale if not set
            if (empty($locale)) {
                $locale = i18n::config()->default_locale;
            }

            i18n::set_locale($locale);

            // Now find and set the desired currency symbol
            $number_format = new NumberFormatter(
                (string)$config->SiteLocale,
                NumberFormatter::CURRENCY
            );
            $symbol = $number_format->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
            DBCurrency::config()->currency_symbol = $symbol;
        }
    }
}