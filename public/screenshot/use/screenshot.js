const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');
const os = require('os');
const { execSync } = require('child_process');

const args = process.argv.slice(2);
const url = args[0];
const outputPath = args[1];
const delay = parseInt(args[2], 10) || 3000;

function removeDirRecursive(dir) {
    if (!dir || !fs.existsSync(dir)) {
        return;
    }
    try {
        fs.rmSync(dir, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
        return;
    } catch (e) {
        // fall through
    }
    try {
        if (process.platform === 'win32') {
            execSync(`rmdir /s /q "${dir}"`, { stdio: 'ignore' });
        } else {
            execSync(`rm -rf "${dir}"`, { stdio: 'ignore' });
        }
    } catch (_) {
        console.error(`[Node Warning] Failed to remove Chrome profile: ${dir}`);
    }
}

(async () => {
    let browser;
    let exitCode = 0;
    const userDataDir = fs.mkdtempSync(path.join(os.tmpdir(), 'wdfm_chrome_'));

    try {
        browser = await puppeteer.launch({
            headless: true,
            userDataDir,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--ignore-certificate-errors',
                '--disable-web-security',
            ],
        });

        const page = await browser.newPage();
        await page.setViewport({ width: 1024, height: 768 });
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');

        console.log('Loading URL: ' + url);
        await page.goto(url, {
            waitUntil: 'networkidle2',
            timeout: 30000,
        });

        console.log('Waiting ' + delay + 'ms for content to load...');
        await page.waitForTimeout(delay);

        console.log('Taking screenshot...');
        await page.screenshot({
            path: outputPath,
            type: 'png',
            fullPage: false,
        });

        console.log('SUCCESS: Screenshot saved to ' + outputPath);
    } catch (error) {
        console.error('ERROR: ' + error.message);
        exitCode = 1;
    } finally {
        if (browser) {
            try {
                await browser.close();
            } catch (closeErr) {
                console.error(`[Node Warning] browser.close failed: ${closeErr.message}`);
            }
        }
        removeDirRecursive(userDataDir);
    }

    process.exit(exitCode);
})();
