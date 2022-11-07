#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Run the build.
npm run build

echo "🌎  Generating .pot file..."
# Update pot.
npm run make-pot

cd ..
echo "Generating assets... 👷‍♂️"

# Generate the plugin zip file.
echo "Creating archive... 🎁"

zip -r edd-fix-order-numbers.zip \
	edd-fix-order-numbers/* \
	-x *node_modules* \
	-x *.browserslistrc* \
	-x *.eslintrc* \
	-x *babel.config.js* \
	-x *webpack.config.js* \
	-x *package-lock.json* \
	-x *package.json* \
	-x *phpcs.xml* \
	-x *edd-fix-order-numbers.zip* \
	-x *bin* \
	-x *src*

mv edd-fix-order-numbers.zip edd-fix-order-numbers

echo "Done. You've built EDD Fix Order Numbers Plugin! 🎉 "
