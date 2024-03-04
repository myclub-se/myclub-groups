# /bin/bash

# Remove previous compiled information
rm languages/*.json
rm myclub-groups.zip
rm -fR blocks/build

# Install all node.js dependencies
npm install

# Build Gutenberg blocks
npm run build

# Extract and rename translations
wp i18n make-json languages --no-purge
python3 tools/update_translation_files.py

# Create a zip file for the plugin
zip -r myclub-groups.zip . -x ".idea/*" -x "*.git*" -x "node_modules/*" -x "blocks/src/*" -x "package.json" -x "package-lock.json" -x "composer.json" -x "composer.lock" -x "build.sh" -x "compile-plugin.sh"
