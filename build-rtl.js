#!/usr/bin/env node

/**
 * Script to generate RTL CSS files from existing CSS files
 * Run this after building the Vue app
 */

const fs = require('fs').promises;
const path = require('path');

/**
 * Simple CSS to RTL converter
 * Handles common RTL transformations
 */
function convertToRTL(css) {
  let rtlCss = css;
  
  // Add RTL comment
  rtlCss = `/* RTL Auto-generated CSS */\n` + rtlCss;
  
  // Swap left/right properties
  const swapRules = [
    // Margins
    [/margin-left:\s*([^;]+);/g, 'margin-rtl-temp: $1;'],
    [/margin-right:\s*([^;]+);/g, 'margin-left: $1;'],
    [/margin-rtl-temp:\s*([^;]+);/g, 'margin-right: $1;'],
    
    // Padding
    [/padding-left:\s*([^;]+);/g, 'padding-rtl-temp: $1;'],
    [/padding-right:\s*([^;]+);/g, 'padding-left: $1;'],
    [/padding-rtl-temp:\s*([^;]+);/g, 'padding-right: $1;'],
    
    // Positioning
    [/left:\s*([^;]+);/g, 'rtl-temp-left: $1;'],
    [/right:\s*([^;]+);/g, 'left: $1;'],
    [/rtl-temp-left:\s*([^;]+);/g, 'right: $1;'],
    
    // Float
    [/float:\s*left;/g, 'float: rtl-temp;'],
    [/float:\s*right;/g, 'float: left;'],
    [/float:\s*rtl-temp;/g, 'float: right;'],
    
    // Text alignment
    [/text-align:\s*left;/g, 'text-align: rtl-temp;'],
    [/text-align:\s*right;/g, 'text-align: left;'],
    [/text-align:\s*rtl-temp;/g, 'text-align: right;'],
    
    // Border radius
    [/border-top-left-radius:\s*([^;]+);/g, 'border-rtl-temp-1: $1;'],
    [/border-top-right-radius:\s*([^;]+);/g, 'border-top-left-radius: $1;'],
    [/border-rtl-temp-1:\s*([^;]+);/g, 'border-top-right-radius: $1;'],
    
    [/border-bottom-left-radius:\s*([^;]+);/g, 'border-rtl-temp-2: $1;'],
    [/border-bottom-right-radius:\s*([^;]+);/g, 'border-bottom-left-radius: $1;'],
    [/border-rtl-temp-2:\s*([^;]+);/g, 'border-bottom-right-radius: $1;'],
    
    // Borders
    [/border-left:\s*([^;]+);/g, 'border-rtl-temp: $1;'],
    [/border-right:\s*([^;]+);/g, 'border-left: $1;'],
    [/border-rtl-temp:\s*([^;]+);/g, 'border-right: $1;'],
    
    // Flexbox
    [/flex-direction:\s*row;/g, 'flex-direction: row-reverse;'],
    [/justify-content:\s*flex-start;/g, 'justify-content: flex-end;'],
    [/justify-content:\s*flex-end;/g, 'justify-content: flex-start;'],
  ];
  
  // Apply swap rules
  swapRules.forEach(([pattern, replacement]) => {
    rtlCss = rtlCss.replace(pattern, replacement);
  });
  
  // Handle margin/padding shorthand (e.g., margin: 10px 20px 30px 40px)
  rtlCss = rtlCss.replace(
    /(margin|padding):\s*([0-9]+(?:px|em|rem|%)?)\s+([0-9]+(?:px|em|rem|%)?)\s+([0-9]+(?:px|em|rem|%)?)\s+([0-9]+(?:px|em|rem|%)?);/g,
    '$1: $2 $5 $4 $3;'
  );
  
  // Add RTL-specific overrides
  rtlCss += `

/* RTL-specific overrides */
.roadmap-app,
.roadmap-meta-box {
  direction: rtl;
}

/* Icons and emojis should not flip */
.dashicons,
.dashicons-before:before,
.emoji {
  direction: ltr;
  display: inline-block;
}

/* Progress bars maintain LTR */
.roadmap-progress,
.roadmap-progress-bar,
progress {
  direction: ltr;
}

/* Code blocks maintain LTR */
code,
pre,
.code-block {
  direction: ltr;
  text-align: left;
}

/* Input placeholders */
input::-webkit-input-placeholder,
textarea::-webkit-input-placeholder {
  text-align: right;
}

input::-moz-placeholder,
textarea::-moz-placeholder {
  text-align: right;
}

input::placeholder,
textarea::placeholder {
  text-align: right;
}

/* Select dropdowns */
select {
  background-position: left 10px center;
}

/* Admin specific RTL fixes */
.roadmap-meta-field {
  direction: rtl;
}

.roadmap-meta-field label {
  float: right;
  text-align: right;
}

.roadmap-meta-field input,
.roadmap-meta-field select,
.roadmap-meta-field textarea {
  float: left;
}

/* Fix WordPress admin RTL issues */
body.rtl .roadmap-pro-field-wrapper {
  direction: rtl;
}

body.rtl .roadmap-pro-badge {
  left: 5px;
  right: auto;
}
`;
  
  return rtlCss;
}

/**
 * Process CSS files and generate RTL versions
 */
async function generateRTLFiles() {
  const cssDir = path.join(__dirname, 'assets', 'css');
  
  // List of CSS files to process
  const cssFiles = [
    'roadmap-app.css',
    'meta-box.css'
  ];
  
  console.log('Generating RTL CSS files...\n');
  
  for (const cssFile of cssFiles) {
    const inputPath = path.join(cssDir, cssFile);
    const outputPath = path.join(cssDir, cssFile.replace('.css', '-rtl.css'));
    
    try {
      // Read the original CSS file
      const cssContent = await fs.readFile(inputPath, 'utf-8');
      
      // Convert to RTL
      const rtlContent = convertToRTL(cssContent);
      
      // Write the RTL CSS file
      await fs.writeFile(outputPath, rtlContent, 'utf-8');
      
      console.log(`✓ Generated: ${cssFile.replace('.css', '-rtl.css')}`);
    } catch (error) {
      console.error(`✗ Error processing ${cssFile}:`, error.message);
    }
  }
  
  console.log('\nRTL CSS generation complete!');
}

// Run the script
generateRTLFiles().catch(console.error);