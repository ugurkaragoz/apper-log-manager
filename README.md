# Apper Log Manager

Apper platformunda kullanıcı hareketlerini merkezi olarak kaydeden otomatik CRUD log sistemi.

## Özellikler

✅ **Otomatik CRUD Loglama**: Model'e trait ekleyerek tüm CRUD işlemlerini otomatik loglar  
✅ **Değişiklik Takibi**: Güncelleme işlemlerinde eski ve yeni değerleri karşılaştırarak loglar  
✅ **Soft Delete Desteği**: Soft delete ve restore işlemlerini destekler  
✅ **Özelleştirilebilir**: Hangi alanların loglanacağını belirleyebilirsiniz  
✅ **Toplu Loglama**: Batch insert ile performans  
✅ **Kullanıcı Takibi**: Hangi kullanıcı, hangi IP'den, ne zaman yaptı  

## Kurulum

Bu paket private bir repository'dir. Composer ile kurulum için:

```bash
composer config repositories.apper-log-manager vcs https://github.com/ugurkaragoz/apper-log-manager
composer require apper/log-manager
```

### 1. Config Dosyasını Yayınlayın

```bash
php artisan vendor:publish --tag=log-manager-config
```

### 2. Environment Ayarları (.env)

```env
LOG_MANAGER_ENABLED=true
LOG_MANAGER_TABLE=logs
LOG_MANAGER_TOOL_NAME="${APP_NAME}"
LOG_MANAGER_AUTO_LOG_CRUD=true
LOG_MANAGER_SILENT_FAILURES=true
```

## Veritabanı Gereksinimi

Bu paket, merkezi veritabanında aşağıdaki yapıya sahip `logs` tablosunu kullanır:

```sql
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `tool_name` varchar(50) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `url` varchar(255) DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logs_user_id_index` (`user_id`),
  KEY `logs_tool_name_action_type_index` (`tool_name`,`action_type`),
  KEY `logs_created_at_index` (`created_at`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Not:** Tablo zaten merkezi veritabanınızda mevcutsa, paket otomatik olarak bu tabloyu kullanacaktır. Migration gerekmez.

## Kullanım

### 1. Otomatik CRUD Loglama

Model'inize `Loggable` trait'ini ekleyin:

```php
use Apper\LogManager\Loggable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Loggable;
}
```

Bu kadar! Artık tüm CRUD işlemleri otomatik olarak loglanacak:
- ✅ `created` - Yeni kayıt oluşturma
- ✅ `updated` - Güncelleme (sadece değişen alanlar)
- ✅ `deleted` - Silme
- ✅ `restored` - Geri yükleme (soft delete)
- ✅ `force_deleted` - Kalıcı silme

### 2. Özelleştirmeler

#### Sadece Belirli Alanları Logla

```php
class User extends Model
{
    use Loggable;
    
    // Sadece bu alanlar loglanır
    protected $loggableFields = ['name', 'email', 'status'];
}
```

#### Bazı Alanları Hariç Tut

```php
class User extends Model
{
    use Loggable;
    
    // Bu alanlar loglanmaz
    protected $excludedFromLog = ['secret_key', 'api_token', 'private_data'];
}
```

#### Entity Type'ı Özelleştir

```php
class Product extends Model
{
    use Loggable;
    
    public function getLogEntityType(): string
    {
        return 'Ürün'; // Türkçe isim
    }
}
```

### 3. Manuel Log Kaydetme

```php
use Apper\LogManager\LogService;

// Basit log
LogService::record('custom_action', 'EntityType', $entityId, ['key' => 'value']);

// Action log
LogService::logAction('email_sent', [
    'to' => 'user@example.com',
    'subject' => 'Welcome'
]);

// Login/Logout
LogService::logLogin();
LogService::logLogout();

// Toplu log
LogService::recordBatch([
    ['action_type' => 'view', 'entity_type' => 'Product', 'entity_id' => 1],
    ['action_type' => 'view', 'entity_type' => 'Product', 'entity_id' => 2],
]);
```

### 4. Log Sorgulama

```php
use Apper\LogManager\Models\Log;

// Belirli bir entity'nin logları
$logs = Log::forEntity('Product', 1)->get();

// Belirli action type
$createdLogs = Log::ofAction('created')->get();

// Kullanıcıya göre
$userLogs = Log::byUser(auth()->id())->get();

// Son 7 günün logları
$recentLogs = Log::lastDays(7)->get();

// Tarih aralığı
$logs = Log::betweenDates('2024-01-01', '2024-12-31')->get();

// Kombine sorgular
$logs = Log::forEntity('Product')
    ->ofAction('updated')
    ->byUser(1)
    ->lastDays(30)
    ->orderBy('created_at', 'desc')
    ->get();
```

### 5. Log Verisi Yapısı

```json
{
  "id": 1,
  "user_id": 123,
  "team_id": 5,
  "tool_name": "Budget",
  "action_type": "updated",
  "entity_type": "Product",
  "entity_id": 42,
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "url": "https://example.com/products/42",
  "payload": {
    "changes": {
      "price": {
        "old": 100,
        "new": 150
      },
      "stock": {
        "old": 50,
        "new": 45
      }
    },
    "model_class": "App\\Models\\Product",
    "timestamp": "2024-11-05T10:30:00+00:00"
  },
  "created_at": "2024-11-05 10:30:00",
  "updated_at": "2024-11-05 10:30:00"
}
```

## Örnekler

### E-ticaret Ürün Yönetimi

```php
class Product extends Model
{
    use Loggable;
    
    protected $loggableFields = [
        'name',
        'price',
        'stock',
        'status',
        'category_id'
    ];
}

// Kullanım
$product = Product::find(1);
$product->price = 150; // Eski fiyat: 100
$product->save();

// Log otomatik oluşur:
// action_type: "updated"
// payload: {"changes": {"price": {"old": 100, "new": 150}}}
```

### Kullanıcı Aktivite Takibi

```php
class User extends Model
{
    use Loggable;
    
    protected $excludedFromLog = [
        'password',
        'remember_token',
        'two_factor_secret'
    ];
}
```

### Dashboard İstatistikleri

```php
// Bugünkü toplam işlem
$todayCount = Log::whereDate('created_at', today())->count();

// En aktif kullanıcılar
$activeUsers = Log::select('user_id', DB::raw('count(*) as total'))
    ->groupBy('user_id')
    ->orderBy('total', 'desc')
    ->limit(10)
    ->get();

// Action type dağılımı
$actionStats = Log::select('action_type', DB::raw('count(*) as total'))
    ->groupBy('action_type')
    ->get();
```

## Konfigürasyon

`config/log-manager.php` dosyasından tüm ayarları özelleştirebilirsiniz:

```php
return [
    'enabled' => true,
    'table' => 'logs',
    'tool_name' => 'MyApp',
    'log_guests' => false,
    'silent_failures' => true,
    'auto_log_crud' => true,
    'excluded_attributes' => [
        'password',
        'remember_token',
        'api_token',
    ],
];
```

## Performans İpuçları

1. **Index'leri kullanın**: Migration'da gerekli index'ler tanımlı
2. **Batch insert**: Çok sayıda log için `recordBatch()` kullanın
3. **Eski logları temizleyin**: Periyodik olarak eski logları silin
4. **Partition kullanın**: Büyük log tablolarında partition düşünün

## Lisans

Bu paket MIT lisansı altında yayınlanmıştır.