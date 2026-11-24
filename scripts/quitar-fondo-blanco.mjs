// Convierte el fondo blanco de una imagen en transparente (incluye el hueco de la cara del casco).
// Uso: node scripts/quitar-fondo-blanco.mjs <entrada> [salida]
// Salida por defecto: src/assets/hero/sauron-casco.png (el modo Sauron usa ese archivo si existe).
import sharp from 'sharp';

const [input, output = 'src/assets/hero/sauron-casco.png'] = process.argv.slice(2);
if (!input) {
  console.error('Uso: node scripts/quitar-fondo-blanco.mjs <entrada> [salida]');
  process.exit(1);
}
const { data, info } = await sharp(input).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
const px = Buffer.from(data);
for (let i = 0; i < px.length; i += 4) {
  const w = Math.min(px[i], px[i + 1], px[i + 2]); // cuanto de "blanco" tiene el pixel
  const a = w >= 246 ? 0 : w <= 214 ? 255 : Math.round(((246 - w) / 32) * 255);
  px[i + 3] = Math.min(px[i + 3], a);
}
await sharp(px, { raw: { width: info.width, height: info.height, channels: 4 } })
  .trim() // recorta el borde transparente
  .png({ compressionLevel: 9 })
  .toFile(output);
console.log('OK', output);
