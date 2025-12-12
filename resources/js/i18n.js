import Vue from 'vue'
import VueI18n from 'vue-i18n'
import messages from '../lang/ru.json'

Vue.use(VueI18n)

function mappedMessages()
{
    return {
        "en-US": {},
        "en-GB": {},
        "ru-RU": {messages},
    }
}

function locale(lang)
{
    return {"us": "en-US", "en": "en-GB", "ru": "ru-RU"}[lang]
}

const locales = ['en']

function setLocale(language)
{
    let languages = Object.getOwnPropertyNames(mappedMessages()),
        matched = languages.find(lang => lang === locale(language))
    if (!matched) {
        matched = languages.find(lang => lang.match(/-[\w+]{2}$/)
            && lang.match(/-[\w+]{2}$/)[0].toLowerCase() === locale(language))
    }
    return matched
}

export const i18n = new VueI18n({
    locale: 'ru-RU',
    fallbackLocale: 'ru-RU',
    messages: mappedMessages(),
    silentTranslationWarn: true,
})

function setI18nLocale(lang)
{
    i18n.locale = setLocale(lang)
    return lang
}

export function loadLanguageAsync(lang)
{
    if (i18n.locale === lang) {
        return Promise.resolve(true)
    }

    if (locales.includes(lang)) {
        return Promise.resolve(setI18nLocale(lang))
    }

    return new Promise(resolve => {
        locales.push(lang)
        resolve(setI18nLocale(lang))
    })
}
