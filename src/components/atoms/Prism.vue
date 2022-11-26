<template>
  <pre ref="pre" :class="preClass" class="prism-pre"><code ref="code" :class="codeClass"><slot></slot></code></pre>
</template>

<script>
import Prism from 'prismjs'
// Plugins
import 'prismjs/plugins/toolbar/prism-toolbar.min'
import 'prismjs/plugins/toolbar/prism-toolbar.css'
import 'prismjs/plugins/show-language/prism-show-language.min'
import 'prismjs/plugins/command-line/prism-command-line.min'
import 'prismjs/plugins/command-line/prism-command-line.css'
import 'prismjs/plugins/line-numbers/prism-line-numbers.min'
import 'prismjs/plugins/line-numbers/prism-line-numbers.css'
// Languages
import 'prismjs/components/prism-asm6502.min'
import 'prismjs/components/prism-bash.min'
import 'prismjs/components/prism-batch.min'
import 'prismjs/components/prism-cmake.min'
import 'prismjs/components/prism-c.min'
import 'prismjs/components/prism-clike.min'
import 'prismjs/components/prism-cpp.min'
import 'prismjs/components/prism-csharp.min'
import 'prismjs/components/prism-css.min'
import 'prismjs/components/prism-coffeescript.min'
import 'prismjs/components/prism-dart.min'
import 'prismjs/components/prism-diff.min'
import 'prismjs/components/prism-docker.min'
import 'prismjs/components/prism-gdscript.min'
import 'prismjs/components/prism-go.min'
import 'prismjs/components/prism-git.min'
import 'prismjs/components/prism-haskell.min'
import 'prismjs/components/prism-ini.min'
import 'prismjs/components/prism-http.min'
import 'prismjs/components/prism-java.min'
import 'prismjs/components/prism-javascript.min'
// import 'prismjs/components/prism-jsdoc'
import 'prismjs/components/prism-json.min'
import 'prismjs/components/prism-kotlin.min'
import 'prismjs/components/prism-latex.min'
import 'prismjs/components/prism-less.min'
import 'prismjs/components/prism-lua.min'
import 'prismjs/components/prism-makefile.min'
import 'prismjs/components/prism-markup.min'
import 'prismjs/components/prism-markdown.min'
import 'prismjs/components/prism-markup-templating.min'
import 'prismjs/components/prism-nginx.min'
import 'prismjs/components/prism-nginx.min'
import 'prismjs/components/prism-objectivec.min'
import 'prismjs/components/prism-php.min'
import 'prismjs/components/prism-perl.min'
import 'prismjs/components/prism-python.min'
import 'prismjs/components/prism-r.min'
import 'prismjs/components/prism-ruby.min'
import 'prismjs/components/prism-regex.min'
import 'prismjs/components/prism-rust.min'
import 'prismjs/components/prism-scss.min'
import 'prismjs/components/prism-sass.min'
import 'prismjs/components/prism-shell-session.min'
import 'prismjs/components/prism-sql.min'
import 'prismjs/components/prism-swift.min'
import 'prismjs/components/prism-toml.min'
import 'prismjs/components/prism-typescript.min'
import 'prismjs/components/prism-visual-basic.min'
import 'prismjs/components/prism-yaml.min'

export default {
  props: {
    language: {
      type: String,
      default: 'none',
    },
    plugins: {
      type: Array,
      default() {
        return []
      },
    },
    code: {
      type: String,
      default: '',
    },
  },
  watch: {
    code() {
      this.render()
    },
    language() {
      this.render()
    },
    plugins() {
      this.render()
    },
  },
  data() {
    return {
      codeText: '',
    }
  },
  computed: {
    preClass() {
      return {
        'command-line': this.hasPlugin('command-line'),
        'line-numbers': this.hasPlugin('line-numbers'),
      }
    },
    codeClass() {
      return {
        [`language-${this.language}`]: true,
      }
    },
  },
  mounted() {
    this.render()
  },
  methods: {
    render() {
      if (!Prism.languages[this.language]) {
        console.warn(`PrismJS: ${this.language} not install`)
      }
      // always update codetext to the code value
      // otherwise see if the text has already been obtained
      // otherwise obtain it from innerHTML
      this.codeText = this.code || this.codeText || this.$refs.code.innerHTML
      this.$nextTick(() => {
        this.$refs.code.textContent = this.codeText.replace(/\s+data-v-\S+="[^"]*"/g, '')
        Prism.highlightElement(this.$refs.code)
      })
    },
    hasPlugin(pluginName) {
      return this.plugins.includes(pluginName)
    },
  },
}
</script>

<style>
pre[class*='language-'].prism-pre {
  background: none;
  padding: 0 1rem 1rem 1rem;
  margin: 0;
}

pre[class*='language-'].prism-pre.line-numbers {
  padding-left: 3rem;
}
</style>
