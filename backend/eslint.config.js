import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import globals from 'globals'

export default [
    { ignores: ['public/build/**', 'node_modules/**'] },

    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],

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
            'vue/html-indent': ['warn', 4],
            'vue/max-attributes-per-line': 'off',
            'vue/singleline-html-element-content-newline': 'off',
            'vue/html-self-closing': ['warn', {
                html: { void: 'any', normal: 'never', component: 'always' },
                svg: 'always',
                math: 'always',
            }],
        },
    },
]
