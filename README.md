# Ninja API Explorer

یک ابزار قدرتمند و مدرن برای کاوش و تست REST API های WordPress با رابط کاربری شبیه به Swagger.

## ویژگی‌ها

### 🔍 کاوش API
- **نمایش خودکار تمام Route ها**: به صورت داینامیک تمام REST API route های ثبت شده در WordPress را نمایش می‌دهد
- **گروه‌بندی بر اساس Namespace**: Route ها بر اساس namespace هایشان گروه‌بندی شده‌اند
- **فیلترهای پیشرفته**: امکان فیلتر بر اساس namespace، method، public/private routes و جستجو
- **نمایش جزئیات**: اطلاعات کامل هر route شامل parameters، methods و example URLs

### 🧪 تست API
- **تست مستقیم**: امکان تست هر endpoint از طریق رابط کاربری
- **پشتیبانی از تمام HTTP Methods**: GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD
- **مدیریت Headers**: اضافه/حذف headers سفارشی
- **Request Body**: پشتیبانی کامل از JSON body برای POST/PUT/PATCH
- **نمایش Response**: نمایش کامل response شامل status code، headers و body

### 📊 آمار و گزارش
- **آمار کلی**: تعداد routes، endpoints، namespaces و غیره
- **تاریخچه تست‌ها**: ذخیره و نمایش تمام درخواست‌های تست شده
- **آمار عملکرد**: زمان پاسخ، نرخ موفقیت و توزیع کدهای وضعیت
- **Export/Import**: امکان export کردن تنظیمات و import کردن آنها

### 🎨 رابط کاربری مدرن
- **طراحی Responsive**: سازگار با تمام اندازه‌های صفحه
- **رابط کاربری شبیه Swagger**: تجربه کاربری آشنای Swagger UI
- **Dark Mode Support**: پشتیبانی از حالت تاریک
- **انیمیشن‌های نرم**: تجربه کاربری روان و جذاب

## نصب

### روش 1: از طریق WordPress Admin
1. فایل‌های پلاگین را در پوشه `/wp-content/plugins/ninja-api-explorer/` کپی کنید
2. از پنل مدیریت WordPress، پلاگین را فعال کنید
3. به منوی "API Explorer" در پنل مدیریت بروید

### روش 2: از طریق Composer (اختیاری)
```bash
composer require your-username/ninja-api-explorer
```

## استفاده

### صفحه اصلی
- **نمایش تمام Routes**: لیست کامل تمام REST API route های موجود
- **فیلتر و جستجو**: پیدا کردن route های مورد نظر
- **تست سریع**: کلیک روی "Test" برای تست فوری endpoint

### تست API
1. روی دکمه "Test" کنار هر route کلیک کنید
2. URL، method، headers و body را تنظیم کنید
3. روی "Send Request" کلیک کنید
4. نتیجه را مشاهده کنید

### تنظیمات
- **فعال/غیرفعال کردن تست API**
- **تنظیم timeout پیش‌فرض**
- **نمایش/مخفی کردن private routes**
- **تنظیم مدت cache**
- **مدیریت logging**

## معماری

### ساختار MVC
پلاگین از معماری MVC استفاده می‌کند:

```
App/
├── Controllers/          # کنترلرها
│   ├── BaseController.php
│   ├── AdminController.php
│   └── ApiTestController.php
├── Models/              # مدل‌ها
│   ├── ApiRouteModel.php
│   └── ApiEndpointModel.php
├── Views/               # View ها
│   └── admin/
│       ├── main-page.php
│       ├── settings-page.php
│       └── documentation-page.php
├── Services/            # سرویس‌ها
│   └── ApiService.php
└── Helpers/             # Helper ها
    ├── RouteHelper.php
    └── ViewHelper.php
```

### نام‌گذاری PascalCase
تمام کلاس‌ها، متدها و متغیرها از PascalCase استفاده می‌کنند:
```php
class NinjaApiExplorer
{
    public function GetInstance()
    {
        // ...
    }
}
```

## API Reference

### کلاس‌های اصلی

#### `NinjaApiExplorer`
کلاس اصلی پلاگین که به عنوان Bootstrap عمل می‌کند.

#### `ApiService`
سرویس اصلی برای کار با REST API ها:
- `GetAllRegisteredRoutes()`: دریافت تمام route ها
- `GetRouteDetails($routeName)`: دریافت جزئیات route
- `TestEndpoint($url, $method, $headers, $body, $timeout)`: تست endpoint

#### `ApiRouteModel`
مدل برای مدیریت route ها:
- `GetRouteName()`: دریافت نام route
- `GetMethods()`: دریافت methods موجود
- `IsPublic()`: بررسی عمومی بودن route
- `GenerateTestData($method)`: تولید داده‌های تست

### Hook ها

#### Actions
- `ninja_api_explorer_before_test`: قبل از تست API
- `ninja_api_explorer_after_test`: بعد از تست API
- `ninja_api_explorer_route_display`: هنگام نمایش route

#### Filters
- `ninja_api_explorer_route_data`: فیلتر داده‌های route
- `ninja_api_explorer_test_response`: فیلتر response تست
- `ninja_api_explorer_settings`: فیلتر تنظیمات

## توسعه

### اضافه کردن Route جدید
```php
add_action('rest_api_init', function() {
    register_rest_route('my-plugin/v1', '/endpoint', array(
        'methods' => 'GET',
        'callback' => 'my_callback_function',
        'permission_callback' => '__return_true'
    ));
});
```

### اضافه کردن Hook سفارشی
```php
// در پلاگین خود
add_action('ninja_api_explorer_before_test', function($url, $method) {
    // کد سفارشی شما
}, 10, 2);
```

### توسعه Controller جدید
```php
class MyCustomController extends BaseController
{
    public function MyCustomMethod()
    {
        // منطق سفارشی شما
    }
}
```

## تنظیمات

### تنظیمات پیش‌فرض
```php
$default_settings = [
    'enable_api_testing' => true,
    'default_timeout' => 30,
    'show_private_routes' => false,
    'cache_duration' => 3600,
    'enable_logging' => false,
    'log_retention_days' => 30
];
```

### فیلتر تنظیمات
```php
add_filter('ninja_api_explorer_settings', function($settings) {
    $settings['my_custom_setting'] = 'my_value';
    return $settings;
});
```

## امنیت

### اعتبارسنجی
- تمام ورودی‌ها اعتبارسنجی و پاک‌سازی می‌شوند
- استفاده از WordPress nonce برای امنیت
- بررسی مجوزهای کاربر

### محدودیت دسترسی
- فقط کاربران با مجوز `manage_options` می‌توانند از پلاگین استفاده کنند
- امکان محدود کردن دسترسی بر اساس IP
- Rate limiting برای جلوگیری از سوءاستفاده

## بهینه‌سازی

### Cache
- Cache کردن route ها برای بهبود عملکرد
- Cache کردن آمار و گزارش‌ها
- امکان تنظیم مدت cache

### Database
- استفاده از جداول سفارشی برای logging
- Index های بهینه برای جستجوی سریع
- پاکسازی خودکار log های قدیمی

## عیب‌یابی

### Debug Mode
برای فعال کردن debug mode:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Log Files
Log های پلاگین در فایل `/wp-content/debug.log` ذخیره می‌شوند.

### مشکلات رایج
1. **Route ها نمایش داده نمی‌شوند**: بررسی کنید که REST API فعال باشد
2. **تست API کار نمی‌کند**: بررسی مجوزهای کاربر و تنظیمات امنیتی
3. **خطای 404**: بررسی URL و وجود route

## مشارکت

مشارکت‌های شما خوشامد است! لطفاً:

1. Fork کنید
2. شاخه جدید بسازید (`git checkout -b feature/amazing-feature`)
3. تغییرات را commit کنید (`git commit -m 'Add amazing feature'`)
4. Push کنید (`git push origin feature/amazing-feature`)
5. Pull Request ایجاد کنید

### راهنمای مشارکت
- از PascalCase برای نام‌گذاری استفاده کنید
- کد خود را کامنت کنید
- تست‌های واحد بنویسید
- از WordPress Coding Standards پیروی کنید

## مجوز

این پلاگین تحت مجوز GPL v2 یا بالاتر منتشر شده است.

## پشتیبانی

- **GitHub Issues**: [گزارش باگ یا درخواست ویژگی](https://github.com/your-username/ninja-api-explorer/issues)
- **Documentation**: [مستندات کامل](https://github.com/your-username/ninja-api-explorer/wiki)
- **Email**: support@yourwebsite.com

## تغییرات

### نسخه 1.0.0
- انتشار اولیه
- رابط کاربری شبیه Swagger
- تست API کامل
- مدیریت route ها
- آمار و گزارش‌گیری
- تنظیمات پیشرفته

## نویسنده

**نام شما**
- GitHub: [@yourusername](https://github.com/yourusername)
- وب‌سایت: [https://yourwebsite.com](https://yourwebsite.com)
- Twitter: [@yourusername](https://twitter.com/yourusername)

---

⭐ اگر این پلاگین برایتان مفید بود، لطفاً ستاره دهید!
