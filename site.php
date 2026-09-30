<?php
// =====================================================================
//  SITE SETTINGS - edit this file to change name, phone, stockists, images
// =====================================================================
define('SITE_NAME',  ' Sport Direct');
define('SITE_SHORT', ' Delux');
define('SUPPORT_PHONE',     '011-6151 5110');   // shown on the website
define('SUPPORT_PHONE_RAW', '01161515110');     // used for tel: links
define('SUPPORT_WHATSAPP',  '601161515110');    // international format, no + sign
define('SUPPORT_EMAIL',     'hello@sexysonnet.my');
define('FREE_SHIP_OVER', 200);   // RM - free delivery above this
define('SHIP_FEE',       12);    // RM - delivery fee below the free limit
define('DEV_MODE', true);        // true = "forgot password" shows the reset link on screen (no mail server needed). Set false on a real server.


$STOCKISTS = [
  ['name'=>'Pudu',      'address'=>"Lot G-12, Ground Floor, Jalan Pudu, 55100 Kuala Lumpur", 'hours'=>'Daily 10am - 10pm', 'query'=>'Jalan Pudu Kuala Lumpur'],
  ['name'=>'TRX',       'address'=>"Lot 2.15, Level 2, The Exchange TRX, Jalan Delima, 55188 Kuala Lumpur", 'hours'=>'Daily 10am - 10pm', 'query'=>'The Exchange TRX Kuala Lumpur'],
  ['name'=>'One Utama', 'address'=>"Lot LG-30, Lower Ground, One Utama Shopping Centre, 1 Lebuh Bandar Utama, 47800 Petaling Jaya", 'hours'=>'Daily 10am - 10pm', 'query'=>'One Utama Shopping Centre Petaling Jaya'],
];


$IMG = [
  'hero'   => 'https://i.ytimg.com/vi/vV37ZemKxWM/maxresdefault.jpg',
  'men'    => 'https://image-cdn.hypb.st/https%3A%2F%2Fhypebeast.com%2Fimage%2F2026%2F08%2F17%2Fsupreme-fall-winter-2026-collection-13.jpg?q=90&w=800&cbr=1&fit=max',
  'women'  => 'https://image-cdn.hypb.st/https%3A%2F%2Fhypebeast.com%2Fimage%2F2026%2F02%2F26%2Fadidas-Originals-Superstar-Spring-2026-Campaign-Info-samuel-l-jackson-jennie-kendall-jenner-james-harden-baby-keem-tyshawn-jones-0.jpg?w=960&cbr=1&q=90&fit=max',
  'kids'   => 'https://dubhumans.com/content/images/46/1350x1800l80mc0/kids-tshirt-tung-tung-tung-sahur-black-xs-96524606321013.jpg',
  'baby'   => 'https://www.jdsports.my/cdn/shop/files/jd_795435_a.jpg?v=1782195964&width=500',
  'deals'  => 'https://picsum.photos/seed/sonnet-deals/1400/600',
  'about1' => 'https://i.shgcdn.com/a4e7c4a2-52ac-4cae-a0d9-3f9825621729/-/format/auto/-/preview/3000x3000/-/quality/lighter/',
  'about2' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHVnaaxJF1I0Fjus14B2HMI573WToIk4UZltL4bohtW9PXx7QUh2k1igI&s=10',
  'about3' => 'https://www.berjaya.com/wp-content/uploads/2024/08/banner-bts-m.jpg',
  'about4' => 'https://hips.hearstapps.com/hmg-prod/images/dscf9799-crew-inside-supreme-aponte-licensed-1-6862fb025bb28.jpg',
  'about5' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR1oK2FYcUDH3Uc_79JOl-NGrBEcs7m95ptw1NLxgRquPs_tbSVCDd7teY&s=10',
  'about6' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQClbp_tpbkwOTQL1Jx-_hDmA5qct5OIN8x2D1hQDJsD1zLKuPt7h48TJ0&s=10',
  'about7' => 'https://websitedemos.net/sports-wear-store-02/wp-content/uploads/sites/828/2021/08/sport-wear-social-img.jpg',
  'store1' => 'https://larilah.my/images/homepage-storefront.jpg',
  'store2' => 'https://hikingmalaysia.com/wp-content/uploads/2024/01/REV-RUNNR-The-Exchange-TRX-Store-6-1024x683.jpg',
  'store3' => 'https://static.nike.com/a/images/f_auto/2e8d9338-b43d-4ef5-96e1-7fdcfd838f8e/image.jpg',
];
