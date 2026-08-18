<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model {
    protected $fillable = ['key','value','type','group'];

    /** Get a setting value by key with optional default */
    public static function get(string $key, $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /** Set a setting value */
    public static function set(string $key, $value, string $type = 'string', ?string $group = null): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'group' => $group]);
    }
}