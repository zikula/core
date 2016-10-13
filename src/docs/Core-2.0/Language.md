notes:

ZLanguage::getLanguageCode() is replaced by $request->getLocale()

ZLanguage::getEncoding() is replaced by ZikulaKernel->getCharset() // default 'UTF-8'

zikula_settings_module.locale_api provides access to locale metadata
@see \Zikula\SettingsModule\Api\LocaleApi

twig global variable `localeApi` gives access to locale_api for example:

    <!DOCTYPE html>
    <html lang="{{ app.request.locale }}" dir="{{ localeApi.language_direction }}">
