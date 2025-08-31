// vite.config.js
import { resolve } from 'path';

export default {
  build: {
    outDir: 'build',
    emptyOutDir: false,
    manifest: true,
    rollupOptions: {
      input: {
        'assets/src/scss/main.scss':       resolve(__dirname, 'assets/src/scss/main.scss'),
        'assets/src/scss/editor.scss':     resolve(__dirname, 'assets/src/scss/editor.scss'),
        'assets/src/scss/admin-dark.scss': resolve(__dirname, 'assets/src/scss/admin-dark.scss'),
        'assets/src/js/main.js':           resolve(__dirname, 'assets/src/js/main.js'),
      }
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        // чтобы не было предупреждения про legacy JS API
        api: 'modern',
        // на всякий случай явно добавим поиск модулей
        includePaths: ['node_modules']
      }
    }
  }
};