import fs from 'fs';

function copyDirRecursive(src, dest) {
  try {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true });
    }

    const entries = fs.readdirSync(src);

    for (const entry of entries) {
      const srcPath = `${src}/${entry}`;
      const destPath = `${dest}/${entry}`;

      const stat = fs.statSync(srcPath);

      if (stat.isDirectory()) {
        copyDirRecursive(srcPath, destPath);
      } else {
        fs.copyFileSync(srcPath, destPath);
      }
    }
  } catch (err) {
    console.error('Error copying files:', err);
  }
}

copyDirRecursive('./php', './dist');
copyDirRecursive('./dist/ui', './dist');
fs.rmSync('./dist/ui', { recursive: true, force: true });