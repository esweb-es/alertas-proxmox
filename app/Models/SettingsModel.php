<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['class', 'key', 'value', 'type', 'context'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene una configuración por su clase y clave
     */
    public function getSetting($class, $key)
    {
        return $this->where('class', $class)->where('key', $key)->first();
    }

    /**
     * Obtiene todas las configuraciones de una clase
     */
    public function getClassSettings($class)
    {
        $settings = $this->where('class', $class)->findAll();
        $config = [];
        foreach ($settings as $setting) {
            $config[$setting->key] = $setting->value;
        }
        return $config;
    }

    /**
     * Guarda o actualiza una configuración
     */
    public function setSetting($class, $key, $value, $type = 'string')
    {
        $setting = $this->getSetting($class, $key);

        if ($setting) {
            return $this->update($setting->id, [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->insert([
            'class' => $class,
            'key'   => $key,
            'value' => $value,
            'type'  => $type,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
