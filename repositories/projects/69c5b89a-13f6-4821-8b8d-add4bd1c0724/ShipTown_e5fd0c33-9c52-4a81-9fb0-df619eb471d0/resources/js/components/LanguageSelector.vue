<template>
    <div class="language-dropdown">
        <b-dropdown
            id="dropdown-1"
            :text="`${languages[currentLanguage].flag} ${languages[currentLanguage].label}`"
            toggle-class="minimal-dropdown-toggle bg-primary text-white"
            variant="link"
            class="minimal-dropdown"
        >
            <b-dropdown-item
                v-for="(lang, code) in filteredLanguages"
                :key="code"
                @click="switchLanguage(code)"
            >
                {{ lang.flag }} {{ lang.label }}
            </b-dropdown-item>
        </b-dropdown>
    </div>
</template>

<script>
import helpers from "../helpers";

export default {
    data() {
        return {
            languages: {
                en: { label: "English", flag: "🇺🇸" },
                pl: { label: "Polski", flag: "🇵🇱" },
                hr: { label: "Hrvatski", flag: "🇭🇷" },
                es: { label: "Español", flag: "🇪🇸" },
                ga: { label: "Gaeilge", flag: "🇮🇪" },
                blank: { label: "Blank", flag: "🇮" },
            },
            currentLanguage: helpers.getCookie('backend_locale', localStorage.getItem("selectedLanguage") || "en"),
        };
    },
    watch: {
        currentLanguage(lang) {
            this.$i18n.locale = lang;
            localStorage.setItem("selectedLanguage", lang);
            helpers.setCookie('backend_locale', lang, 365);
        },
    },
    mounted() {
        this.$i18n.locale = this.currentLanguage;
        helpers.setCookie('backend_locale', this.currentLanguage, 365);
    },
    methods: {
        switchLanguage(lang) {
            this.currentLanguage = lang;
        },
    },
    computed: {
        filteredLanguages()
        {
            const filtered = { ...this.languages };
            if (process.env.MIX_APP_ENV !== 'local') {
                delete filtered.blank;
            }
            return filtered;
        }
    }
};
</script>

<style scoped>
.language-dropdown {
    display: flex;
    justify-content: flex-start;
    padding: 10px 10px;
}

.minimal-dropdown-toggle {
    font-size: 16px;
    font-weight: 500;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.minimal-dropdown-toggle:hover {
    background-color: #0056b3; /* Darker shade of primary */
    transition: background-color 0.2s ease-in-out;
}

.b-dropdown-item {
    font-size: 14px;
    padding: 5px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.b-dropdown-item:hover {
    background-color: #f8f9fa;
    color: #0056b3;
    transition: color 0.2s ease-in-out;
}
</style>
