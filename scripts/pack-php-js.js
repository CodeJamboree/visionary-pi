import fs from 'fs';
import path from 'path';

const main = () => {
  copyDirRecursive('./php', './dist');
  copyDirRecursive('./dist/ui', './dist');

  const deploy = JSON.parse(fs.readFileSync('.deploy.json', 'utf8'));
  replaceTags(deploy, './dist/api/.htaccess')

  fs.rmSync('./dist/ui', { recursive: true, force: true });
}

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

export const replaceTags = (deploy, filePath) => {
  const name = path.basename(filePath);
  if (!name.startsWith(".")) return;
  let text = fs.readFileSync(filePath, 'utf8');
  const tagPattern = /\{\{\s*([a-z\.]+)\s*}}/gi;
  const tags = text.matchAll(tagPattern);
  let modified = false;
  tags.forEach(([tag, name]) => {
    switch (name) {
      case 'database.credentials':
        modified = true;
        const { hostname, database, username, password } = deploy.db;
        text = text.replace(tag, btoa(JSON.stringify({
          hostname,
          database,
          username,
          password
        }, null, '')));
        break;
      case 'web.jwt':
        modified = true;
        text = text.replace(tag, deploy.web.jwt);
        break;
      default:
        console.log('Unrecognized tag', tag);
        return;
        break;
    }
  });
  if (modified) {
    fs.writeFileSync(filePath, text, 'utf8');
  }
}

main();

