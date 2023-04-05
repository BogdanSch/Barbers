import { createI18n } from 'vue-i18n'
import en from './locales/en'
import es from './locales/es'
import fr from './locales/fr'
import pl from './locales/pl'
import tr from './locales/tr'
import nl from './locales/nl'
import de from './locales/de'
import it from './locales/it'
import pt from './locales/pt'

// 2. Create i18n instance with options
const i18n = createI18n({
  locale: window.slnPWA.locale, // set locale
  fallbackLocale: 'en', // set fallback locale
  messages: {en, es, fr, pl, tr, nl, de, it,pt}, // set locale messages
  // If you need to specify other options, you can set other options
  // ...
})

export default i18n