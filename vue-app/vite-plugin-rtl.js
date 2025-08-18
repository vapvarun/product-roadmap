import { promises as fs } from 'fs';
import path from 'path';

/**
 * Simple RTL CSS generation plugin for Vite
 * Converts LTR CSS to RTL during build
 */
export default function rtlPlugin() {
  return {
    name: 'vite-plugin-rtl',
    apply: 'build',
    async writeBundle(options, bundle) {
      // Process each CSS file in the bundle
      for (const [fileName, asset] of Object.entries(bundle)) {
        if (fileName.endsWith('.css') && asset.type === 'asset') {
          const cssContent = asset.source;
          const rtlContent = convertToRTL(cssContent);
          
          // Generate RTL filename
          const rtlFileName = fileName.replace('.css', '-rtl.css');
          const outputPath = path.join(options.dir, rtlFileName);
          
          // Write RTL CSS file
          await fs.writeFile(outputPath, rtlContent, 'utf-8');
          console.log(`Generated RTL CSS: ${rtlFileName}`);
        }
      }
    }
  };
}

/**
 * Simple CSS to RTL converter
 * Handles common RTL transformations
 */
function convertToRTL(css) {
  let rtlCss = css;
  
  // Add RTL direction to root
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
    
    // Transform origin
    [/transform-origin:\s*([0-9]+%?)\s+/g, (match, p1) => {
      const num = parseFloat(p1);
      const unit = p1.includes('%') ? '%' : '';
      const flipped = 100 - num;
      return `transform-origin: ${flipped}${unit} `;
    }],
    
    // Flexbox
    [/flex-direction:\s*row;/g, 'flex-direction: row-reverse;'],
    [/flex-direction:\s*row-reverse;/g, 'flex-direction: row;'],
  ];
  
  // Apply swap rules
  swapRules.forEach(([pattern, replacement]) => {
    rtlCss = rtlCss.replace(pattern, replacement);
  });
  
  // Handle margin/padding shorthand (e.g., margin: 10px 20px 30px 40px)
  rtlCss = rtlCss.replace(
    /(margin|padding):\s*([0-9]+(?:px|em|%)?)\s+([0-9]+(?:px|em|%)?)\s+([0-9]+(?:px|em|%)?)\s+([0-9]+(?:px|em|%)?);/g,
    '$1: $2 $5 $4 $3;'
  );
  
  // Add direction: rtl to body and html
  if (!rtlCss.includes('direction: rtl')) {
    rtlCss = rtlCss.replace(/body\s*{/, 'body {\n  direction: rtl;\n');
    rtlCss = rtlCss.replace(/html\s*{/, 'html {\n  direction: rtl;\n');
  }
  
  // Add RTL-specific overrides
  rtlCss += `

/* RTL-specific overrides */
.roadmap-app {
  direction: rtl;
}

/* Icons and emojis should not flip */
.dashicons,
.emoji,
[class*="icon-"] {
  direction: ltr;
  display: inline-block;
}

/* Progress bars maintain LTR */
.roadmap-progress,
.roadmap-progress-bar {
  direction: ltr;
}

/* Code blocks maintain LTR */
code,
pre,
.code-block {
  direction: ltr;
  text-align: left;
}
`;
  
  return rtlCss;
}