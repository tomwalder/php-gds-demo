<?php
/**
 * Repository for GDS Demo
 *
 * @author Tom Walder
 */
namespace GDS\Demo;
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
     * @var \GDS\Store|null
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
     */
    public function createPost($str_name, $str_message)
    {
        $obj_store = $this->getStore();
        $obj_store->upsert($obj_store->createEntity([
            'posted' => date('Y-m-d H:i:s'),
            'name' => substr($str_name, 0, 30),
            'message' => substr($str_message, 0, 1000),
        ]));

        // Update the cache
        $this->updateCache();
    }

    /**
     * Configure and return a Store
     *
     * @return \GDS\Store
     */
    private function getStore()
    {
        if(NULL === $this->obj_store) {
            $obj_google_client = \GDS\Gateway::createGoogleClient('php-gds-demo', GDS_ACCOUNT, GDS_KEY_FILE);
            $obj_gateway = new \GDS\Gateway($obj_google_client, 'php-gds-demo');
            $this->obj_store = new \GDS\Store($obj_gateway, $this->makeSchema());
        }
        return $this->obj_store;
    }

    /**
     * Build a schema for Guest book entries
     *
     * the posted datetime as an indexed field
     *
     * @return \GDS\Schema
     */
    private function makeSchema()
    {
        return (new \GDS\Schema('Guestbook'))
            ->addDatetime('posted')
            ->addString('name', FALSE)
            ->addString('message', FALSE);
    }

}