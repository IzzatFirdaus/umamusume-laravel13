import NativePHP from '#plugin';
import { app, BrowserWindow } from 'electron';
import electronUpdater from 'electron-updater';
import fs from 'fs';
import path from 'path';
import { createSplash } from './splash.js';
// Inherit User's PATH in Process & ChildProcess
import fixPath from 'fix-path';
fixPath();

// A packaged GUI app has no attached console, so its main-process output is
// discarded and a startup failure leaves no trace. Mirror console output to a
// file under userData so the acceptance workflow (and a user) can see exactly
// how far the Electron/PHP bootstrap got.
try {
    const mainLog = path.join(app.getPath('userData'), 'nativephp-main.log');
    const write = (prefix, args) => {
        try {
            const line = `${new Date().toISOString()} ${prefix}${args
                .map((a) => (typeof a === 'string' ? a : JSON.stringify(a)))
                .join(' ')}`;
            fs.appendFileSync(mainLog, line + '\n');
        } catch {
            /* ignore logging failures */
        }
    };
    const origLog = console.log.bind(console);
    const origErr = console.error.bind(console);
    console.log = (...a) => {
        origLog(...a);
        write('', a);
    };
    console.error = (...a) => {
        origErr(...a);
        write('ERR ', a);
    };
    // php.ts prints PHP stdout/stderr only when SHELL_VERBOSITY > 0.
    if (!process.env.SHELL_VERBOSITY) {
        process.env.SHELL_VERBOSITY = '1';
    }
    process.on('unhandledRejection', (reason) => {
        console.error(
            '[main] unhandledRejection:',
            reason && reason.stack ? reason.stack : reason,
        );
    });
    process.on('uncaughtException', (err) => {
        console.error(
            '[main] uncaughtException:',
            err && err.stack ? err.stack : err,
        );
    });
    console.log('[main] module loaded');
} catch {
    /* ignore */
}

const { autoUpdater } = electronUpdater;

// `MAIN_VITE_NATIVEPHP_BUILD_PATH` is inlined at build time. It is absent in a
// packaged build, where `path.resolve(dirname, undefined)` throws at module load
// and kills the main process before `whenReady` - so the PHP server never starts.
// Resolve only when the value exists, and fall back to the shipped payload.
const inlinedBuildPath = import.meta.env.MAIN_VITE_NATIVEPHP_BUILD_PATH;
const fallbackBuildPath = app.isPackaged
    ? path.join(process.resourcesPath, 'build')
    : path.resolve(import.meta.dirname, '..', '..', '..', 'build');

let buildPath = inlinedBuildPath
    ? path.resolve(import.meta.dirname, inlinedBuildPath)
    : fallbackBuildPath;

if (!fs.existsSync(buildPath) && fs.existsSync(fallbackBuildPath)) {
    buildPath = fallbackBuildPath;
}

console.log('[main] buildPath:', buildPath);
const defaultIcon = path.join(buildPath, 'icon.png');
const certificate = path.join(buildPath, 'cacert.pem');

const executable = process.platform === 'win32' ? 'php.exe' : 'php';
const phpBinary = path.join(buildPath, 'php', executable);
const appPath = path.join(buildPath, 'app');

let splashWindow;

app.whenReady().then(() => {
    console.log('[main] app ready; bootstrapping NativePHP');
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
