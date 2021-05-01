<?php
namespace common\services\cache;
use Yii;
use yii\db\ActiveRecord;

class CacheCommonSrv
{
    public $cache;
    public $key;

    public function __construct()
    {
        $this->cache = Yii::$app->redis;
    }

    public function get()
    {
        $data = $this->cache->get($this->key);
        return unserialize($data);
    }

    public function set($data)
    {
        $this->cache->set($this->key, serialize($data));
    }

    public function getKey()
    {

    }
    
    public function flushAllCache()
    {
        $this->cache->executeCommand('FLUSHDB');
    }

    public function generateKeyFromArray($data)
    {
        $key = '';
        if (isset($data['table'])){
            $key .= 'table:' . $data['table'] . ';';
        }
        $key .= 'fields:';
        foreach ($data as $row)
        {
            if (isset($row['field']) && isset($row['value'])){
                $key .= $row['field'] . '-' . $row['value'] . '.';
            }
        }

        $this->key = $key;
        return $key;
    }

    public function flushRecordsByKeyPart($data)
    {
        $key_part = $data;
        
        if ( !is_string($data)) {
            $key_part = $this->generateKeyFromArray($data);
        }
        
        $keys = $this->cache->executeCommand('KEYS', ['*' . $key_part . '*']);
        $del_keys_count = $this->cache->executeCommand('DEL', $keys);

        if ($del_keys_count == count($keys)) return true;
        return $del_keys_count;
    }
    
    public function getExecutedRecordsByKeyPart(string $key_part)
    {
        $keys = $this->cache->executeCommand('KEYS', ['*' . $key_part . '*']);
        $data = [];
        
        foreach ($keys as $record) {
            $data[] = unserialize($this->cache->executeCommand('get', [$record]));
        }
        
        return $data;
    }
    
    public function generateKeyFromSqlQuery(ActiveRecord $query)
    {

    }
}