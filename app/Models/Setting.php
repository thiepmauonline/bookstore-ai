<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Cấu hình cửa hàng dạng key-value, được cache để không truy vấn lại mỗi request.
 */
class Setting extends Model
{
    public const CACHE_KEY = 'settings.all';

    /** Các khóa cấu hình và giá trị mặc định. */
    public const DEFAULTS = [
        'store_name' => 'Bookstore AI',
        'hotline' => '0123 456 789',
        'email' => 'support@bookstore-ai.edu.vn',
        'address' => '123 Đường Đại Học, Quận Cầu Giấy, Hà Nội',
        'working_hours' => '8:00 - 21:00, Thứ 2 - Chủ nhật',
    ];

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** @return array<string, string|null> */
    public static function allValues(): array
    {
        $stored = Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->pluck('value', 'key')->all()
        );

        return array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::allValues()[$key] ?? $default;
    }
}
