# Hệ thống Giá Bất Động Sản Đa Tiền Tệ
## WordPress Multi-Currency Price System

### 📋 Tổng quan

Hệ thống hiển thị giá bất động sản đa tiền tệ (VND / USD / TWD) + đa ngôn ngữ, được xây dựng cho child theme WordPress, đảm bảo:
- ✅ Không sai số
- ✅ Performance cao
- ✅ Dễ maintain
- ✅ Dễ scale

---

## 🏗️ Kiến trúc hệ thống

### Nguyên tắc cốt lõi

1. **Single Source of Truth**: Giá gốc duy nhất là VND
2. **Backend Calculation**: Mọi phép tính tiền tệ làm bằng PHP
3. **No Database Redundancy**: KHÔNG lưu USD/TWD vào post meta
4. **Frontend Display Only**: JS chỉ switch hiển thị, không tính toán
5. **Independent Calculation**: Tổng giá và đơn giá tính độc lập

---

## 📁 Cấu trúc File

```
viethomecao/
├── functions.php                          # Load modules
├── style.css                              # Child theme info
├── inc/
│   ├── admin/
│   │   └── exchange-rate-options.php      # PHASE 1: Quản lý tỷ giá
│   └── price/
│       ├── price-calculator.php           # PHASE 2: Tính toán giá
│       ├── price-formatter.php            # PHASE 2: Format hiển thị
│       ├── price-shortcode.php            # PHASE 2: Shortcodes
│       └── price-hooks.php                # PHASE 2: Theme integration
├── template-parts/
│   └── property/
│       └── price-block.php                # PHASE 2: HTML template
└── assets/
    ├── js/
    │   └── currency-switcher.js           # PHASE 3: Frontend logic
    └── css/
        └── currency-switcher.css          # PHASE 3: Styles
```

---

## 🎯 PHASE 1 - ADMIN (Quản lý tỷ giá)

### File: `inc/admin/exchange-rate-options.php`

**Chức năng:**
- Tạo trang admin "Settings → Tỷ giá tiền tệ"
- Admin nhập tỷ giá: 1 USD = X VND, 1 TWD = Y VND
- Validate: không cho nhập số ≤ 0
- Lưu vào `wp_options` với key `vhc_exchange_rates`

**Cách dùng:**
```php
// Lấy tất cả tỷ giá
$rates = get_option('vhc_exchange_rates');

// Lấy tỷ giá USD
$usd_rate = VHC_Exchange_Rate_Options::get_rate('usd');

// Lấy tỷ giá TWD
$twd_rate = VHC_Exchange_Rate_Options::get_rate('twd');
```

---

## 💰 PHASE 2 - BACKEND (Logic tính toán)

### 1. Price Calculator (`inc/price/price-calculator.php`)

**Trách nhiệm:**
- Tính toán giá từ VND gốc sang USD/TWD
- KHÔNG làm tròn sớm
- Tổng giá và đơn giá tính ĐỘC LẬP

**Công thức:**
```php
unit_price_vnd = total_price_vnd / area_m2

total_price_usd = total_price_vnd / rate_usd
unit_price_usd  = unit_price_vnd / rate_usd

total_price_twd = total_price_vnd / rate_twd
unit_price_twd  = unit_price_vnd / rate_twd
```

**API:**
```php
// Tính toán đầy đủ
$data = VHC_Price_Calculator::calculate( $post_id );
/*
Array (
    'area_m2' => 100,
    'total_price_vnd' => 5000000000,
    'unit_price_vnd' => 50000000,
    'total_price_usd' => 200000,
    'unit_price_usd' => 2000,
    'total_price_twd' => 5882353,
    'unit_price_twd' => 58824,
    'rate_usd' => 25000,
    'rate_twd' => 850
)
*/

// Lấy giá cụ thể
$price = VHC_Price_Calculator::get_price( $post_id, 'usd', 'total' );

// Kiểm tra có giá không
if ( VHC_Price_Calculator::has_price( $post_id ) ) {
    // ...
}
```

### 2. Price Formatter (`inc/price/price-formatter.php`)

**Trách nhiệm:**
- Format số theo chuẩn từng loại tiền
- Round CHỈ 1 LẦN khi render
- MỌI hiển thị giá phải dùng formatter này

**Config format:**
- **VND**: `5.000.000.000 đ` (0 decimals, after)
- **USD**: `$200,000.00` (2 decimals, before)
- **TWD**: `NT$5,882,353` (0 decimals, before)

**API:**
```php
// Format giá
echo VHC_Price_Formatter::format_price( 5000000000, 'VND', true );
// Output: 5.000.000.000 đ

// Format diện tích
echo VHC_Price_Formatter::format_area( 100.5, true );
// Output: 100.50 m²

// Format giá đầy đủ cho property
echo VHC_Price_Formatter::format_property_price( $post_id, 'USD' );

// Lấy symbol
echo VHC_Price_Formatter::get_currency_symbol( 'TWD' );
// Output: NT$
```

### 3. Price Shortcode (`inc/price/price-shortcode.php`)

**Shortcodes:**
```php
// Hiển thị đầy đủ (tổng + đơn giá)
[property_price]

// Hiển thị với currency cụ thể
[property_price currency="USD"]

// Chỉ hiển thị tổng giá
[property_price type="total" currency="TWD"]

// Chỉ hiển thị đơn giá
[property_price type="unit"]

// Hiển thị block đầy đủ với switcher
[property_price_block]
[property_price_block show_switcher="yes" default_currency="USD"]
```

### 4. Price Hooks (`inc/price/price-hooks.php`)

**Mục đích:**
- Tích hợp với parent theme bằng hook/filter
- KHÔNG sửa template gốc

**Lưu ý:** File này **COMMENT OUT** mặc định. Chỉ uncomment khi đã biết hook của theme.

### 5. Price Block Template (`template-parts/property/price-block.php`)

**Trách nhiệm:**
- Backend render SẴN tất cả currency (VND/USD/TWD)
- Đưa toàn bộ dữ liệu vào data-attributes
- JS chỉ switch hiển thị

**HTML Structure:**
```html
<div class="property-price-block" 
     data-price-vnd-total="5000000000"
     data-price-vnd-unit="50000000"
     data-price-usd-total="200000"
     data-price-usd-unit="2000"
     data-price-twd-total="5882353"
     data-price-twd-unit="58824">
  
  <!-- Currency Switcher -->
  <div class="currency-switcher">
    <button class="currency-btn" data-currency="VND">VND</button>
    <button class="currency-btn" data-currency="USD">USD</button>
    <button class="currency-btn" data-currency="TWD">TWD</button>
  </div>
  
  <!-- Price Groups (đã render sẵn) -->
  <div class="price-group-vnd">...</div>
  <div class="price-group-usd" style="display:none;">...</div>
  <div class="price-group-twd" style="display:none;">...</div>
</div>
```

---

## 🎨 PHASE 3 - FRONTEND (JS + CSS)

### 1. Currency Switcher JS (`assets/js/currency-switcher.js`)

**Trách nhiệm:**
- Switch hiển thị giá (show/hide price groups)
- Lưu lựa chọn vào localStorage
- TUYỆT ĐỐI không tính toán tiền tệ
- Không AJAX, không reload page

**Luồng hoạt động:**
1. Load currency từ localStorage (mặc định: USD)
2. Áp dụng currency cho tất cả price blocks
3. User click nút currency
4. Hide tất cả price groups
5. Show price group của currency được chọn
6. Lưu lựa chọn vào localStorage
7. Update active state cho buttons

**Global API:**
```javascript
// Lấy currency hiện tại
VHC.currencySwitcher.getCurrency();

// Switch sang currency khác
VHC.currencySwitcher.switchTo('USD');

// Refresh khi load AJAX content
VHC.currencySwitcher.refresh();
```

**Custom Event:**
```javascript
// Lắng nghe khi currency thay đổi
document.addEventListener('vhc:currencyChanged', function(e) {
    console.log('Currency changed to:', e.detail.currency);
});
```

### 2. Currency Switcher CSS (`assets/css/currency-switcher.css`)

**Features:**
- Responsive design
- Active state cho buttons
- Fade animation khi switch
- Print styles
- Mobile optimization

---

## 🚀 Cách sử dụng

### 1. Setup ban đầu

**Bước 1:** Active child theme

**Bước 2:** Nhập tỷ giá
- Vào **Settings → Tỷ giá tiền tệ**
- Nhập: 1 USD = X VND, 1 TWD = Y VND
- Lưu

**Bước 3:** Nhập giá cho property
- Admin chỉ nhập 2 field:
  - `property_price` (hoặc `_property_price`): Tổng giá VND
  - `property_area` (hoặc `_property_area`): Diện tích m²

### 2. Hiển thị giá trong theme

**Trong template PHP:**
```php
// Cách 1: Dùng shortcode
echo do_shortcode( '[property_price_block]' );

// Cách 2: Dùng template
get_template_part( 'template-parts/property/price-block', null, array(
    'post_id' => get_the_ID(),
    'show_switcher' => true,
    'default_currency' => 'VND'
) );

// Cách 3: Tự render
$data = VHC_Price_Calculator::calculate( get_the_ID() );
echo VHC_Price_Formatter::format_price( $data['total_price_usd'], 'USD' );
```

**Trong content/editor:**
```
[property_price currency="USD"]
[property_price_block show_switcher="yes"]
```

### 3. Tích hợp với theme

**Nếu theme có hook riêng:**

Mở file `inc/price/price-hooks.php` và uncomment dòng cuối:
```php
new VHC_Price_Hooks();
```

Sau đó điều chỉnh hook/filter phù hợp với theme.

---

## 🔧 Customize

### Thêm currency mới

**Bước 1:** Thêm tỷ giá vào admin
```php
// inc/admin/exchange-rate-options.php
// Thêm field mới trong register_settings()
```

**Bước 2:** Update calculator
```php
// inc/price/price-calculator.php
// Thêm tính toán cho currency mới
```

**Bước 3:** Update formatter
```php
// inc/price/price-formatter.php
// Thêm config format trong get_currency_config()
```

**Bước 4:** Update template
```php
// template-parts/property/price-block.php
// Thêm price group mới
```

**Bước 5:** Update JS
```javascript
// assets/js/currency-switcher.js
// Thêm currency vào validCurrencies array
```

### Custom meta keys

Nếu theme dùng meta key khác:

```php
add_filter( 'vhc_price_meta_keys', function( $keys ) {
    // Thêm meta key của theme vào đầu array
    array_unshift( $keys, 'custom_price_field' );
    return $keys;
} );

add_filter( 'vhc_area_meta_keys', function( $keys ) {
    array_unshift( $keys, 'custom_area_field' );
    return $keys;
} );
```

### Custom format config

```php
add_filter( 'vhc_currency_configs', function( $configs ) {
    // Override VND format
    $configs['VND']['thousand_separator'] = ',';
    $configs['VND']['decimal_separator'] = '.';
    
    return $configs;
} );
```

---

## ✅ Checklist Testing

### Phase 1 - Admin
- [ ] Truy cập Settings → Tỷ giá tiền tệ
- [ ] Nhập tỷ giá USD, TWD
- [ ] Validate: không cho nhập ≤ 0
- [ ] Lưu thành công
- [ ] Lấy tỷ giá bằng code

### Phase 2 - Backend
- [ ] Tạo property test với giá VND + diện tích
- [ ] Tính toán giá USD/TWD đúng công thức
- [ ] Format số theo chuẩn từng currency
- [ ] Shortcode hiển thị đúng
- [ ] Không sai số (kiểm tra với calculator)

### Phase 3 - Frontend
- [ ] Currency mặc định là USD
- [ ] Click nút switch currency
- [ ] Giá thay đổi ngay (không reload)
- [ ] Refresh page vẫn giữ currency đã chọn
- [ ] Responsive trên mobile
- [ ] Works với cache (WP Rocket, LiteSpeed)

---

## 🐛 Troubleshooting

### Giá không hiển thị
1. Kiểm tra meta key có đúng không
2. Thêm filter `vhc_price_meta_keys`
3. Check có nhập đủ giá + diện tích không

### Currency không switch
1. Kiểm tra JS console có lỗi không
2. Verify localStorage có hoạt động không
3. Check class name `.property-price-block` có đúng không

### Tỷ giá sai
1. Vào Settings → Tỷ giá tiền tệ
2. Kiểm tra giá trị đã lưu
3. Clear cache (WordPress, browser)

### Sai số khi tính
1. Verify công thức: `total / rate` và `unit / rate`
2. Check không làm tròn sớm
3. Tổng giá và đơn giá phải tính độc lập

---

## 📚 Best Practices

### DO ✅
- Luôn dùng `VHC_Price_Formatter` để hiển thị giá
- Lưu giá gốc là VND
- Tính USD/TWD runtime
- Validate input trong admin
- Cache-friendly (render sẵn HTML)

### DON'T ❌
- Không lưu USD/TWD vào DB
- Không tính toán bằng JS
- Không dùng AJAX để lấy giá
- Không làm tròn sớm
- Không hardcode tỷ giá

---

## 🔐 Security

- ✅ Sanitize/validate mọi input
- ✅ Escape output
- ✅ Check `manage_options` capability
- ✅ Nonce verification (nếu dùng AJAX)
- ✅ Prevent direct file access

---

## 📈 Performance

- ✅ Không query DB mỗi lần hiển thị giá
- ✅ Backend render sẵn HTML
- ✅ Không AJAX
- ✅ localStorage thay vì cookie
- ✅ Works với cache plugins

---

## 📝 Changelog

### Version 1.0.0 - 2026-02-02
- ✨ Initial release
- ✨ Phase 1: Admin tỷ giá
- ✨ Phase 2: Backend logic
- ✨ Phase 3: Frontend switcher
- ✨ Support VND / USD / TWD
- ✨ Đa ngôn ngữ ready

---

## 👤 Author

Senior WordPress Developer
15+ years experience
Specialized in: ThemeForest themes, Real Estate, Multi-currency, Multi-language

---

## 📄 License

Proprietary - For Viethomecao project only
