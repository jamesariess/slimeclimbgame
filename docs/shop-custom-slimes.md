# Custom Shop Slimes

Shop items are stored in MySQL table `shop_items`.

Admin page:
- `admin_content.php`

Visual options:
- `css_slime` uses the built-in animated CSS slime.
- `image` uses an uploaded file from `assets/images/shop-slimes/`.

Supported uploads:
- PNG
- JPG
- WEBP
- GIF
- Max size: 2MB

Recommended art:
- Transparent PNG or WEBP
- 512x512 or 1024x1024
- Center the slime with a little empty space around it

Animation styles:
- `float`
- `bounce`
- `pulse`

The animation is applied by CSS to the image wrapper, so custom image slimes still float/glow in the Shop and Inventory.
