import { createI18n } from 'vue-i18n';
import de from './de.json';
import en from './en.json';

export default createI18n({
    legacy: false,
    locale: 'de',
    fallbackLocale: 'en',
    messages: { de, en },
});
