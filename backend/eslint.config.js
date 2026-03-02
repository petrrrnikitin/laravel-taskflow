import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import globals from 'globals'
import prettierConfig from 'eslint-config-prettier'

export default [
    { ignores: ['public/build/**', 'node_modules/**'] },

    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    prettierConfig,

    {
        languageOptions: {
            globals: { ...globals.browser },
            ecmaVersion: 'latest',
            sourceType: 'module',
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            'no-console': 'warn',
            'no-empty': ['error', { allowEmptyCatch: true }],
        },
    },
]