import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
    const libName = process.env.LIB_NAME || 'PegRNAVisualization';
    const entryFile = process.env.ENTRY_FILE || 'src/index.jsx';
    const outFileName = process.env.OUT_FILE_NAME || 'pegrna-visualization';

    return {
        plugins: [react()],
        define: {
            // Define process.env.NODE_ENV to prevent "process is not defined" in browser
            'process.env.NODE_ENV': JSON.stringify('production')
        },
        build: {
            lib: {
                entry: resolve(__dirname, entryFile),
                name: libName,
                fileName: (format) => `${outFileName}.${format}.js`,
                formats: ['iife']
            },
            rollupOptions: {
                output: {
                    // Export only the default export to avoid warning
                    exports: 'named',
                    // Global variable name for the bundle
                    name: libName,
                    // Ensure React is bundled
                    inlineDynamicImports: true
                }
            },
            // Output to dist folder
            outDir: 'dist',
            // Generate source maps for debugging
            sourcemap: false,
            // Use esbuild for minification (built-in, no extra dependency)
            minify: 'esbuild',
            // Don't empty outDir so we can build multiple files
            emptyOutDir: false
        }
    };
});
