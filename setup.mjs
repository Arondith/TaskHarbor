import {randomBytes} from 'node:crypto';
import {writeFileSync,existsSync} from 'node:fs';
if(existsSync('.env'))console.log('Existing .env preserved.');
else {writeFileSync('.env',`APP_KEY=base64:${randomBytes(32).toString('base64')}\nAPI_TOKEN=${randomBytes(32).toString('hex')}\nDB_PASSWORD=${randomBytes(24).toString('hex')}\nDB_ROOT_PASSWORD=${randomBytes(24).toString('hex')}\n`,{mode:0o600});console.log('Created .env. Use API_TOKEN to open your workspace.');}
