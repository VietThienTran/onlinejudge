<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\caching\Cache;

class Setting extends Component
{

    public $cache = 'cache';

    public $cacheKey = 'settings';

    private $_data = null;

    public function init()
    {
        parent::init();

        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache, false);
        }
    }

    public function get($key)
    {
        if ($this->_data === null) {
            if ($this->cache instanceof Cache) {
                $data = $this->cache->get($this->cacheKey);
                if ($data === false) {
                    $data = $this->getData();
                    $this->cache->set($this->cacheKey, $data);
                }
            } else {
                $data = $this->getData();
            }
            $this->_data = $data;
        }
        return $this->_data[$key];
    }

    public function set($date)
    {
        foreach ($date as $key => $value) {
            Yii::$app->db->createCommand()->update('{{%setting}}', [
                'value' => $value
            ], '`key`=:key', [':key' => $key])->execute();
        }
        return $this->clearCache();
    }

    public function clearCache()
    {
        $this->_data = null;
        if ($this->cache instanceof Cache) {
            return $this->cache->delete($this->cacheKey);
        }
        return true;
    }

    public function getData()
    {
        $settings = Yii::$app->db->createCommand("SELECT * FROM {{%setting}}")->queryAll();
        return \yii\helpers\ArrayHelper::map($settings, 'key', 'value');
    }

    public static function getVersion()
    {
        return '0.9.0';
    }
}
