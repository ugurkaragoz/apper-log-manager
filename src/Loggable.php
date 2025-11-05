<?php

namespace Apper\LogManager;

trait Loggable
{
    /**
     * Boot the Loggable trait for a model.
     */
    protected static function bootLoggable(): void
    {
        // Observer'ı otomatik olarak kaydet
        static::observe(LogObserver::class);
    }

    /**
     * Model için özel log entity type tanımla.
     * Bu metodu override ederek özel isim verebilirsiniz.
     */
    public function getLogEntityType(): string
    {
        return class_basename($this);
    }

    /**
     * Loglanmayacak alanları tanımla.
     * Model'de bu property'yi override edebilirsiniz.
     * 
     * Örnek:
     * protected $excludedFromLog = ['secret_key', 'private_data'];
     */
    // protected $excludedFromLog = [];

    /**
     * Sadece bu alanları logla.
     * Model'de bu property'yi tanımlarsanız sadece bu alanlar loglanır.
     * 
     * Örnek:
     * protected $loggableFields = ['name', 'email', 'status'];
     */
    // protected $loggableFields = [];
}