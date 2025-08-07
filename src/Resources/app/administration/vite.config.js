import { defineConfig } from 'vite';
import shopware from '@shopware-ag/vite-plugin-shopware';

export default defineConfig({
  plugins: [
    shopware({
      sourcePath: __dirname,
      admin: {
        entry: './src/main.ts',
      },
    }),
  ],
  // Ensure Vite outputs to the correct directory relative to the plugin
  build: {
    outDir: './dist/administration',
  },
  resolve: {
    extensions: ['.ts', '.js'],
  },
});