const path = require('path');
module.paths.push(path.join(__dirname, '../../puphpeteer_env/node_modules'));

const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());
const fs = require('fs');

const url = process.argv[2];
const outputHtmlPath = process.argv[3];
const screenshotPath = process.argv[4] || '';
const userAgent = process.argv[5] || '';
const waitUntilStr = process.argv[6] || 'load,domcontentloaded,networkidle2';
const executablePath = process.argv[7] || '';

if (!url || !outputHtmlPath) {
    console.error('Usage: node scraper.js <url> <outputHtmlPath> [screenshotPath] [userAgent] [waitUntil] [executablePath]');
    process.exit(1);
}

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            executablePath: executablePath || process.env.PUPPETEER_EXECUTABLE_PATH || undefined, // fallback to bundled chrome/chromium
            headless: "new",
            ignoreHTTPSErrors: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--window-size=1920,1080',
            ]
        });

        const page = await browser.newPage();
        await page.setViewport({ width: 1920, height: 1080 });
        await page.setDefaultNavigationTimeout(90000);
        await page.setJavaScriptEnabled(true);

        await page.setUserAgent(userAgent || 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
        });

        // Network Interception (Block ads, fonts, tracking scripts, and media resources)
        await page.setRequestInterception(true);
        page.on('request', (request) => {
            const resourceType = request.resourceType();
            const url = request.url();

            // Block video, audio, and font files to speed up page loading
            if (resourceType === 'media' || resourceType === 'font') {
                request.abort();
            } 
            // Block ads, analytics, and social trackers
            else if (url.includes('google-analytics') || url.includes('doubleclick') || url.includes('facebook.com/tr')) {
                request.abort();
            } 
            else {
                request.continue();
            }
        });

        const waitUntil = waitUntilStr.split(',');

        let response;
        let pageToUse = page;
        try {
            console.log(`[Node] Navigating to: ${url} (waitUntil: ${waitUntilStr})`);
            response = await page.goto(url, {
                timeout: 90000,
                waitUntil: waitUntil
            });
        } catch (gotoEx) {
            const errMessage = gotoEx.message || '';
            if (errMessage.toLowerCase().includes('frame was detached') || 
                errMessage.toLowerCase().includes('execution context was destroyed')) {
                
                console.log(`[Node] Frame detached detected. Waiting 6 seconds for redirect to settle...`);
                await new Promise(r => setTimeout(r, 6000)); // wait 6s for redirect to settle
                
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
        fs.writeFileSync(outputHtmlPath, content, 'utf8');
        console.log(`[Node] HTML saved to: ${outputHtmlPath}`);

        if (screenshotPath) {
            await pageToUse.screenshot({
                path: screenshotPath,
                fullPage: true
            });
            console.log(`[Node] Screenshot saved to: ${screenshotPath}`);
        }

        process.exit(0);
    } catch (error) {
        console.error(`[Node Error] ${error.stack || error.message}`);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
