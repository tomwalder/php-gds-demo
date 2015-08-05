<?php
/**
 * Repository for GDS Demo
 *
 * @author Tom Walder
 */
namespace GDS\Demo;
use GDS\Schema;
use GDS\Store;

class Repository
{

    /**
     * Memcache instance
     *
     * @var \Memcached|null
     */
    private $obj_cache = NULL;

    /**
     * GDS Store instance
     *
     * @var Store|null
     */
    private $obj_store = NULL;

    /**
     * @return \Memcached|null
     */
    private function getCache()
    {
        if(NULL === $this->obj_cache) {
            $this->obj_cache = new \Memcached();
        }
        return $this->obj_cache;
    }

    /**
     * Get the most recent posts. From memcache ideally.
     *
     * @return array
     */
    public function getRecentPosts()
    {
        $arr_posts = $this->getCache()->get('recent');
        if(is_array($arr_posts)) {
            return $arr_posts;
        } else {
            return $this->updateCache();
        }
    }

    /**
     * Update the cache from Datastore
     *
     * @return array
     */
    private function updateCache()
    {
        $obj_store = $this->getStore();
        $arr_posts = $obj_store->query("SELECT * FROM Guestbook ORDER BY posted DESC")->fetchPage(POST_LIMIT);
        $this->getCache()->set('recent', $arr_posts);
        return $arr_posts;
    }

    /**
     * Insert the entity (plus limit the data to the same values as the form)
     *
     * @param $str_name
     * @param $str_message
     * @param $str_ip
     */
    public function createPost($str_name, $str_message, $str_ip)
    {
        $obj_store = $this->getStore();
        $obj_store->upsert($obj_store->createEntity([
            'posted' => date('Y-m-d H:i:s'),
            'name' => $str_name,
            'message' => $str_message,
            'ip' => $str_ip
        ]));

        // Update the cache
        $this->updateCache();
    }

    /**
     * Configure and return a Store
     *
     * @return Store
     */
    private function getStore()
    {
        if(NULL === $this->obj_store) {
            $this->obj_store = new Store($this->makeSchema());
        }
        return $this->obj_store;
    }

    /**
     * Build a schema for Guest book entries
     *
     * the posted datetime as an indexed field
     *
     * @return Schema
     */
    private function makeSchema()
    {
        return (new Schema('Guestbook'))
            ->addDatetime('posted')
            ->addString('name', FALSE)
            ->addString('message', FALSE)
            ->addString('ip')
        ;
    }

}