<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Model\Source as SourceModel;

class Options
{
    /**
     * @param $settings
     * @return bool
     */
    static public function savePluginSettings($settings)
    {
        DataHelper::prepareJson($settings);

        if (Validator::validateOptions($settings)) {
            if (isset($settings['general']['sources'])) {
                update_option(CGUSF_PREFIX .'sources', $settings['general']['sources']);
            }
            update_option(CGUSF_PREFIX .'license_key', $settings['general']['license_key']);
            update_option(CGUSF_PREFIX .'facebook_app_id', $settings['general']['facebook_app_id']);
            update_option(CGUSF_PREFIX .'facebook_app_secret', $settings['general']['facebook_app_secret']);
            update_option(CGUSF_PREFIX .'youtube_api_key', $settings['general']['youtube_api_key']);
            update_option(CGUSF_PREFIX .'cache_time', $settings['feeds']['cache_time']);
            update_option(CGUSF_PREFIX .'cache_unit', $settings['feeds']['cache_unit']);
            update_option(CGUSF_PREFIX .'custom_css', $settings['feeds']['custom_css']);
            update_option(CGUSF_PREFIX .'strings', $settings['strings']);
            update_option(CGUSF_PREFIX .'locale', $settings['feeds']['locale']);
            update_option(CGUSF_PREFIX .'timezone', $settings['feeds']['timezone']);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    static public function getPluginSettings()
    {
        $sources = SourceModel::getSourceCollection();

        return [
            'general' => [
                'sources' => $sources,
                'license_key' => get_option(CGUSF_PREFIX . 'license_key'),
                'facebook_app_id' => get_option(CGUSF_PREFIX . 'facebook_app_id'),
                'facebook_app_secret' => get_option(CGUSF_PREFIX . 'facebook_app_secret'),
                'youtube_api_key' => get_option(CGUSF_PREFIX . 'youtube_api_key'),
            ],
            'feeds' => [
                'locale' => get_option(CGUSF_PREFIX .'locale'),
                'timezone' => get_option(CGUSF_PREFIX .'timezone'),
                'cache_time' => get_option(CGUSF_PREFIX .'cache_time'),
                'cache_unit' => get_option(CGUSF_PREFIX .'cache_unit'),
                'custom_css' => get_option(CGUSF_PREFIX .'custom_css'),
                'locales' => self::getLocales(),
            ],
            'strings' => get_option(CGUSF_PREFIX .'strings'),
            'logs' => get_option(CGUSF_PREFIX .'logs'),
        ];
    }

    /**
     * @return string[]
     */
    static public function getLocales()
    {
        return [
            'af_ZA' => 'Afrikaans',
            'ar_AR' => 'Arabic',
            'az_AZ' => 'Azerbaijani',
            'be_BY' => 'Belarusian',
            'bg_BG' => 'Bulgarian',
            'bn_IN' => 'Bengali',
            'bs_BA' => 'Bosnian',
            'ca_ES' => 'Catalan',
            'cs_CZ' => 'Czech',
            'cy_GB' => 'Welsh',
            'da_DK' => 'Danish',
            'de_DE' => 'German',
            'el_GR' => 'Greek',
            'en_GB' => 'English (UK)',
            'en_PI' => 'English (Pirate)',
            'en_US' => 'English (US)',
            'eo_EO' => 'Esperanto',
            'es_ES' => 'Spanish (Spain)',
            'es_LA' => 'Spanish',
            'et_EE' => 'Estonian',
            'eu_ES' => 'Basque',
            'fa_IR' => 'Persian',
            'fb_LT' => 'Leet Speak',
            'fi_FI' => 'Finnish',
            'fo_FO' => 'Faroese',
            'fr_CA' => 'French (Canada)',
            'fr_FR' => 'French (France)',
            'fy_NL' => 'Frisian',
            'ga_IE' => 'Irish',
            'gl_ES' => 'Galician',
            'he_IL' => 'Hebrew',
            'hi_IN' => 'Hindi',
            'hr_HR' => 'Croatian',
            'hu_HU' => 'Hungarian',
            'hy_AM' => 'Armenian',
            'id_ID' => 'Indonesian',
            'is_IS' => 'Icelandic',
            'it_IT' => 'Italian',
            'ja_JP' => 'Japanese',
            'ka_GE' => 'Georgian',
            'km_KH' => 'Khmer',
            'ko_KR' => 'Korean',
            'ku_TR' => 'Kurdish',
            'la_VA' => 'Latin',
            'lt_LT' => 'Lithuanian',
            'lv_LV' => 'Latvian',
            'mk_MK' => 'Macedonian',
            'ml_IN' => 'Malayalam',
            'ms_MY' => 'Malay',
            'nb_NO' => 'Norwegian (bokmal)',
            'ne_NP' => 'Nepali',
            'nl_NL' => 'Dutch',
            'nn_NO' => 'Norwegian (nynorsk)',
            'pa_IN' => 'Punjabi',
            'pl_PL' => 'Polish',
            'ps_AF' => 'Pashto',
            'pt_BR' => 'Portuguese (Brazil)',
            'pt_PT' => 'Portuguese (Portugal)',
            'ro_RO' => 'Romanian',
            'ru_RU' => 'Russian',
            'sk_SK' => 'Slovak',
            'sl_SI' => 'Slovenian',
            'sq_AL' => 'Albanian',
            'sr_RS' => 'Serbian',
            'sv_SE' => 'Swedish',
            'sw_KE' => 'Swahili',
            'ta_IN' => 'Tamil',
            'te_IN' => 'Telugu',
            'th_TH' => 'Thai',
            'tl_PH' => 'Filipino',
            'tr_TR' => 'Turkish',
            'uk_UA' => 'Ukrainian',
            'vi_VN' => 'Vietnamese',
            'zh_CN' => 'Simplified Chinese (China)',
            'zh_HK' => 'Traditional Chinese (Hong Kong)',
            'zh_TW' => 'Traditional Chinese (Taiwan)',
        ];
    }
}