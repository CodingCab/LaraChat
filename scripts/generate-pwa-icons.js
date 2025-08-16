import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Simple SVG icon template
const createSvgIcon = (size) => {
    return `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad${size}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#0ea5e9;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#0284c7;stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="${size}" height="${size}" fill="url(#grad${size})" rx="${size * 0.1}" />
    <circle cx="${size/2}" cy="${size/2}" r="${size * 0.35}" fill="white" />
    <text x="${size/2}" y="${size/2}" font-family="Arial, sans-serif" font-size="${size * 0.3}" font-weight="bold" fill="#0ea5e9" text-anchor="middle" dominant-baseline="middle">LC</text>
</svg>`;
};

// Sizes for PWA icons
const sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Create icons directory if it doesn't exist
const iconsDir = path.join(__dirname, '..', 'public', 'icons');
if (!fs.existsSync(iconsDir)) {
    fs.mkdirSync(iconsDir, { recursive: true });
}

// Generate SVG icons for each size
sizes.forEach(size => {
    const svgContent = createSvgIcon(size);
    const filePath = path.join(iconsDir, `icon-${size}x${size}.svg`);
    fs.writeFileSync(filePath, svgContent);
    console.log(`Generated: icon-${size}x${size}.svg`);
});

console.log('\nSVG icons generated successfully!');
console.log('Note: To convert these to PNG, you can use an online converter or install a package like sharp or svg2png.');
console.log('\nFor now, the SVG files can be used directly by updating the manifest.json to use .svg extensions.');