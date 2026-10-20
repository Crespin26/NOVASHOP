import { defineConfig } from '@playwright/test'
export default defineConfig({
 testDir:'./tests/e2e',timeout:30000,fullyParallel:false,workers:1,
 reporter:'list',use:{baseURL:'http://127.0.0.1:4191',trace:'retain-on-failure',headless:true,launchOptions:{...(process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE?{executablePath:process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE}:{}),args:['--no-sandbox']}},
 projects:[{name:'desktop-fr',metadata:{language:'fr'},use:{viewport:{width:1440,height:1000}}},{name:'desktop-en',metadata:{language:'en'},use:{viewport:{width:1440,height:1000}}},{name:'mobile-en',metadata:{language:'en'},use:{viewport:{width:390,height:844}}}],
 webServer:{command:'npm run preview -- --host 127.0.0.1 --port 4191 --strictPort',url:'http://127.0.0.1:4191',reuseExistingServer:false},
})
