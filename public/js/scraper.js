const path = require('path');
const fs = require('fs');
const os = require('os');
const { createRequire } = require('module');
const { execSync } = require('child_process');

// Resolve deps from project puphpeteer_env (not from public/js)
const puppeteerEnvRoot = path.resolve(__dirname, '../../puphpeteer_env');
const puppeteerModules = path.join(puppeteerEnvRoot, 'node_modules');
if (!fs.existsSync(path.join(puppeteerModules, 'puppeteer-extra'))) {
    console.error(`[Node Error] puppeteer-extra not found in: ${puppeteerModules}`);
    console.error('Run: cd puphpeteer_env && npm install --omit=dev');
    process.exit(1);
}
const requirePup = createRequire(path.join(puppeteerEnvRoot, 'package.json'));

const puppeteer = requirePup('puppeteer-extra');
const StealthPlugin = requirePup('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

const url = process.argv[2];
const outputHtmlPath = process.argv[3];
const screenshotPath = process.argv[4] || '';
const userAgent = process.argv[5] || '';
const waitUntilStr = process.argv[6] || 'load,domcontentloaded';
const executablePath = process.argv[7] || '';

if (!url || !outputHtmlPath) {
    console.error('Usage: node scraper.js <url> <outputHtmlPath> [screenshotPath] [userAgent] [waitUntil] [executablePath]');
    process.exit(1);
}

/**
 * Puppeteer creates /tmp/puppeteer_dev_profile-* when userDataDir is omitted.
 * browser.close() often does NOT remove those dirs if process.exit() runs first.
 * We own a dedicated profile dir and always delete it ourselves.
 */
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
            executablePath: executablePath || process.env.PUPPETEER_EXECUTABLE_PATH || undefined,
            headless: 'new',
            ignoreHTTPSErrors: true,
            userDataDir,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--window-size=1920,1080',
            ],
        });

        const page = await browser.newPage();
        await page.setViewport({ width: 1920, height: 1080 });
        await page.setDefaultNavigationTimeout(45000);
        await page.setJavaScriptEnabled(true);

        await page.setUserAgent(userAgent || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
        });

        // Network Interception (Block ads, fonts, tracking scripts, and media resources)
        await page.setRequestInterception(true);
        page.on('request', (request) => {
            const resourceType = request.resourceType();
            const reqUrl = request.url();

            // Block video, audio, and font files to speed up page loading
            if (resourceType === 'media' || resourceType === 'font') {
                request.abort();
            }
            // Block ads, analytics, and social trackers
            else if (reqUrl.includes('google-analytics') || reqUrl.includes('doubleclick') || reqUrl.includes('facebook.com/tr')) {
                request.abort();
            } else {
                request.continue();
            }
        });

        const waitUntil = waitUntilStr.split(',');

        let response;
        const pageToUse = page;
        try {
            console.log(`[Node] Navigating to: ${url} (waitUntil: ${waitUntilStr})`);
            response = await page.goto(url, {
                timeout: 45000,
                waitUntil: waitUntil,
            });
        } catch (gotoEx) {
            const errMessage = gotoEx.message || '';
            if (errMessage.toLowerCase().includes('frame was detached') ||
                errMessage.toLowerCase().includes('execution context was destroyed')) {

                console.log(`[Node] Frame detached detected. Waiting 6 seconds for redirect to settle...`);
                await new Promise(r => setTimeout(r, 6000));

                try {
                    await page.waitForSelector('body', { timeout: 10000 });
                    console.log(`[Node] Page is responsive. Proceeding with scan.`);
                } catch (e) {
                    console.log(`[Node] Warning: page check failed after redirect: ${e.message}`);
                    throw gotoEx;
                }
            } else {
                throw gotoEx;
            }
        }

        const httpStatus = response ? response.status() : 200;
        console.log(`[Node] HTTP Status: ${httpStatus}`);

        await new Promise(r => setTimeout(r, 5000));

        // SCROLL
        await pageToUse.evaluate(async () => {
            const scrollStep = 800;
            const scrollDelay = 100;
            const totalHeight = Math.max(
                document.body.scrollHeight,
                document.documentElement.scrollHeight
            );
            for (let scrolled = 0; scrolled < totalHeight; scrolled += scrollStep) {
                window.scrollTo(0, scrolled);
                await new Promise(resolve => setTimeout(resolve, scrollDelay));
            }
            window.scrollTo(0, 0);
            await new Promise(resolve => setTimeout(resolve, 200));
        });

        await new Promise(r => setTimeout(r, 2000));

        // DOM STABILITY
        await pageToUse.evaluate(() => {
            return new Promise(resolve => {
                let last = document.body.innerHTML.length;
                let stableCount = 0;
                let attempts = 0;
                const maxAttempts = 60;
                const check = () => {
                    attempts++;
                    const now = document.body.innerHTML.length;
                    if (now === last) {
                        stableCount++;
                        if (stableCount >= 4) return resolve(true);
                    } else {
                        stableCount = 0;
                    }
                    last = now;
                    if (attempts >= maxAttempts) return resolve(true);
                    setTimeout(check, 500);
                };
                check();
            });
        });

        await new Promise(r => setTimeout(r, 1000));

        const content = await pageToUse.content();
        const htmlDir = path.dirname(outputHtmlPath);
        if (!fs.existsSync(htmlDir)) {
            fs.mkdirSync(htmlDir, { recursive: true });
        }
        fs.writeFileSync(outputHtmlPath, content, 'utf8');
        console.log(`[Node] HTML saved to: ${outputHtmlPath}`);

        if (screenshotPath) {
            const screenshotDir = path.dirname(screenshotPath);
            if (!fs.existsSync(screenshotDir)) {
                fs.mkdirSync(screenshotDir, { recursive: true });
            }
            await pageToUse.screenshot({
                path: screenshotPath,
                fullPage: true,
            });
            console.log(`[Node] Screenshot saved to: ${screenshotPath}`);
        }
    } catch (error) {
        console.error(`[Node Error] ${error.stack || error.message}`);
        exitCode = 1;
    } finally {
        // Close browser BEFORE process.exit so Chrome releases profile locks.
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
