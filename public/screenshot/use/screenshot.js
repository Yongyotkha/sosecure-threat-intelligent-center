const puppeteer = require('puppeteer');

const args = process.argv.slice(2);
const url = args[0];
const outputPath = args[1];
const delay = parseInt(args[2]) || 3000;

(async () => {
    try {
        const browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--ignore-certificate-errors',
                '--disable-web-security'
            ]
        });
        
        const page = await browser.newPage();
        await page.setViewport({ width: 1024, height: 768 });
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');
        
        console.log('Loading URL: ' + url);
        await page.goto(url, { 
            waitUntil: 'networkidle2', 
            timeout: 30000 
        });
        
        console.log('Waiting ' + delay + 'ms for content to load...');
        await page.waitForTimeout(delay);
        
        console.log('Taking screenshot...');
        await page.screenshot({ 
            path: outputPath,
            type: 'png',
            fullPage: false
        });
        
        await browser.close();
        console.log('SUCCESS: Screenshot saved to ' + outputPath);
        
    } catch (error) {
        console.error('ERROR: ' + error.message);
        process.exit(1);
    }
})();