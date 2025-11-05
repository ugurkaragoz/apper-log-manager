# Apper Log Manager

Apper platformunda kullanıcı hareketlerini merkezi olarak kaydeden log sistemi.

## Kurulum

Bu paket private bir repository'dir. Composer ile kurulum için:

```bash
composer config repositories.apper-log-manager vcs https://github.com/yourusername/apper-log-manager
composer require apper/log-manager
```

## Kullanım

### Laravel'de Kullanım

Service Provider'ı otomatik olarak keşfedilir. Manuel olarak eklemek isterseniz:

```php
// config/app.php
'providers' => [
    // ...
    Apper\LogManager\LogServiceProvider::class,
],
```

### Temel Kullanım

```php
use Apper\LogManager\Loggable;

class YourModel extends Model
{
    use Loggable;
    
    protected $loggableFields = ['name', 'email'];
}
```

### Manuel Log Kaydetme

```php
use Apper\LogManager\LogService;

$logService = app(LogService::class);
$logService->log('user_action', 'User logged in', ['user_id' => 123]);
```

## Lisans

Bu paket MIT lisansı altında yayınlanmıştır.