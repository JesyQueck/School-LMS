const fs = require('fs');
const path = require('path');

const base = 'c:\\Users\\USER\\Desktop\\School-LMS\\backend\\resources\\views';

// ============================================
// 1. FIX NAVBAR - Add x-cloak to X icon, fix toggle
// ============================================
const navPath = path.join(base, 'components', 'public', 'navbar.blade.php');
let nav = fs.readFileSync(navPath, 'utf-8');

// Add x-cloak to the X (close) icon so it's hidden until Alpine initializes
nav = nav.replace(
    '<svg x-show="mobileOpen" class="h-6 w-6"',
    '<svg x-show="mobileOpen" x-cloak class="h-6 w-6"'
);

fs.writeFileSync(navPath, nav);
console.log('1. Navbar: Added x-cloak to close icon');

// ============================================
// 2. FIX HERO - Add onerror fallback to images
// ============================================
const heroPath = path.join(base, 'components', 'public', 'hero.blade.php');
let hero = fs.readFileSync(heroPath, 'utf-8');

// Add onerror fallback to all img tags in hero
hero = hero.replace(
    /(<img src="[^"]*" alt="[^"]*" class="w-full h-full object-cover zoom-target" loading="[^"]*")/g,
    '$1 onerror="this.style.display=\'none\';this.parentElement.classList.add(\'bg-gradient-to-br\',\'from-neutral-100\',\'to-neutral-200\',\'dark:from-neutral-800\',\'dark:to-neutral-700\')"'
);

fs.writeFileSync(heroPath, hero);
console.log('2. Hero: Added onerror fallbacks to images');

// ============================================
// 3. FIX NEWS CARD - Add onerror fallback
// ============================================
const newsPath = path.join(base, 'components', 'public', 'news-card.blade.php');
let news = fs.readFileSync(newsPath, 'utf-8');

news = news.replace(
    'class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy">',
    'class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy" onerror="this.style.display=\'none\';this.parentElement.classList.add(\'bg-gradient-to-br\',\'from-neutral-100\',\'to-neutral-200\',\'dark:from-neutral-800\',\'dark:to-neutral-700\')">'
);

fs.writeFileSync(newsPath, news);
console.log('3. News card: Added onerror fallback');

// ============================================
// 4. FIX FACILITY CARD - Add onerror fallback
// ============================================
const facilityPath = path.join(base, 'components', 'public', 'facility-card.blade.php');
let facility = fs.readFileSync(facilityPath, 'utf-8');

facility = facility.replace(
    'class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy">',
    'class="w-full h-full object-cover zoom-target transition-transform duration-700 group-hover:scale-105" loading="lazy" onerror="this.style.display=\'none\';this.parentElement.classList.add(\'bg-gradient-to-br\',\'from-neutral-100\',\'to-neutral-200\',\'dark:from-neutral-800\',\'dark:to-neutral-700\')">'
);

fs.writeFileSync(facilityPath, facility);
console.log('4. Facility card: Added onerror fallback');

// ============================================
// 5. FIX GALLERY ITEM - Add onerror fallback
// ============================================
const galleryPath = path.join(base, 'components', 'public', 'gallery-item.blade.php');
let gallery = fs.readFileSync(galleryPath, 'utf-8');

gallery = gallery.replace(
    'class="w-full h-full object-cover zoom-target" loading="lazy">',
    'class="w-full h-full object-cover zoom-target" loading="lazy" onerror="this.style.display=\'none\';this.parentElement.classList.add(\'bg-gradient-to-br\',\'from-neutral-200\',\'to-neutral-300\',\'dark:from-neutral-700\',\'dark:to-neutral-800\')">'
);

fs.writeFileSync(galleryPath, gallery);
console.log('5. Gallery item: Added onerror fallback');

// ============================================
// 6. FIX HOMEPAGE - Add onerror to inline images
// ============================================
const homePath = path.join(base, 'public', 'home.blade.php');
let home = fs.readFileSync(homePath, 'utf-8');

// Fix all img tags in the homepage that don't have onerror
home = home.replace(
    /(<img src="[^"]*" alt="[^"]*" class="w-full h-full object-cover[^"]*" loading="[^"]*")(?!\s+onerror)/g,
    '$1 onerror="this.style.display=\'none\';this.parentElement.classList.add(\'bg-gradient-to-br\',\'from-neutral-100\',\'to-neutral-200\',\'dark:from-neutral-800\',\'dark:to-neutral-700\')"'
);

fs.writeFileSync(homePath, home);
console.log('6. Homepage: Added onerror fallbacks to inline images');

console.log('\nAll fixes applied successfully!');