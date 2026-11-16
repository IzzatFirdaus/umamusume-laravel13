import NativePHP from '#plugin';
import { app, BrowserWindow } from 'electron';
import electronUpdater from 'electron-updater';
import fs from 'fs';
import path from 'path';
import { createSplash } from './splash.js';
// Inherit User's PATH in Process & ChildProcess
import fixPath from 'fix-path';
fixPath();

const { autoUpdater } = electronUpdater;

// `MAIN_VITE_NATIVEPHP_BUILD_PATH` is inlined at build time. In a packaged app it
// can resolve to a path that only existed on the build machine (or one inside
// app.asar), so the PHP server script is never found and the app never opens an
// HTTP port. The packaged payload is always shipped at `resources/build`, so fall
// back to that when the inlined path is not present on this machine.
let buildPath = path.resolve(import.meta.dirname, import.meta.env.MAIN_VITE_NATIVEPHP_BUILD_PATH);
if (!fs.existsSync(buildPath) && app.isPackaged) {
    const packagedBuildPath = path.join(process.resourcesPath, 'build');
    if (fs.existsSync(packagedBuildPath)) {
        buildPath = packagedBuildPath;
    }
}
const defaultIcon = path.join(buildPath, 'icon.png');
const certificate = path.join(buildPath, 'cacert.pem');

const executable = process.platform === 'win32' ? 'php.exe' : 'php';
const phpBinary = path.join(buildPath, 'php', executable);
const appPath = path.join(buildPath, 'app');

let splashWindow;

app.whenReady().then(() => {
    try {
        splashWindow = createSplash(appPath, import.meta.dirname);
    } catch (error) {
        console.error('Error creating splash screen:', error);
    }

    NativePHP.bootstrap(app, defaultIcon, phpBinary, certificate, appPath);

    // One update check per launch. electron-updater rejects when the build carries no
    // publish feed (a local unsigned build) or the machine is offline, so the rejection
    // is logged and swallowed: an unreachable feed must never keep the window closed.
    autoUpdater
        .checkForUpdates()
        .then(() => console.log('Checking for updates...'))
        .catch((error) => console.log('Update check skipped:', error?.message ?? error));
});

app.on('browser-window-created', (event, window) => {
    if (splashWindow && window !== splashWindow) {
        window.webContents.on('did-navigate', (evt, url) => {
            if (url.startsWith('http://127.0.0.1') || url.startsWith('http://localhost')) {
                if (splashWindow) {
                    splashWindow.close();
                    splashWindow = null;
                }
            }
        });
    }
});
