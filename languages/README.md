# Translation Files

This directory contains translation files for the Ninja API Explorer plugin.

## Files

- `ninja-api-explorer.pot` - Template file containing all translatable strings
- `ninja-api-explorer-fa_IR.po` - Persian (Iran) translation file
- `ninja-api-explorer-fa_IR.mo` - Compiled Persian translation file

## How to Add New Translations

### 1. Create a new translation file

Copy the `.pot` file and rename it to match your language code:

```
ninja-api-explorer-[language-code]_[country-code].po
```

For example:
- `ninja-api-explorer-ar_SA.po` for Arabic (Saudi Arabia)
- `ninja-api-explorer-es_ES.po` for Spanish (Spain)
- `ninja-api-explorer-fr_FR.po` for French (France)

### 2. Translate the strings

Open the `.po` file in a text editor or translation tool like:
- [Poedit](https://poedit.net/)
- [Lokalise](https://lokalise.com/)
- [Transifex](https://www.transifex.com/)

Translate all the `msgstr` entries while keeping the `msgid` entries unchanged.

### 3. Compile the translation

Convert the `.po` file to `.mo` format using:

```bash
msgfmt ninja-api-explorer-[language-code]_[country-code].po -o ninja-api-explorer-[language-code]_[country-code].mo
```

Or use online tools like [po2mo.net](https://po2mo.net/).

### 4. Test the translation

1. Upload both `.po` and `.mo` files to this directory
2. Change your WordPress language to the new language
3. Check if the plugin interface is properly translated

## Language Codes

Use standard WordPress language codes:

- `fa_IR` - Persian (Iran)
- `ar_SA` - Arabic (Saudi Arabia)
- `es_ES` - Spanish (Spain)
- `fr_FR` - French (France)
- `de_DE` - German (Germany)
- `it_IT` - Italian (Italy)
- `pt_BR` - Portuguese (Brazil)
- `ru_RU` - Russian (Russia)
- `zh_CN` - Chinese (China)
- `ja_JP` - Japanese (Japan)

## Contributing Translations

If you create a translation for your language, please consider contributing it back to the project by:

1. Creating a pull request with your translation files
2. Updating this README with your language information
3. Testing the translation thoroughly

## Support

For translation-related questions or issues, please:

1. Check the [WordPress Internationalization Documentation](https://developer.wordpress.org/apis/internationalization/)
2. Open an issue on the plugin's GitHub repository
3. Contact the plugin maintainers

## Tools

Recommended tools for translation work:

- **Poedit** - Desktop application for editing translation files
- **Lokalise** - Cloud-based translation management
- **Transifex** - Professional translation platform
- **WordPress.org Translation Platform** - Community translations for WordPress plugins

## Notes

- Always keep the original `msgid` strings unchanged
- Use proper encoding (UTF-8) for special characters
- Test translations in the actual WordPress environment
- Consider cultural differences in terminology and phrasing
- Keep translations consistent with WordPress core translations when possible
