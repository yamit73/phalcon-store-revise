<?php
namespace App\Components;

use Phalcon\Di\Injectable;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;

/**
 * Class that is used to translate the page
 */
class Locale extends Injectable
{
    /**
     * function to translate the strings
     *
     * @return NativeArray
     */
    public function getTranslator(): NativeArray
    {
        // Ask browser what is the best language
        $language = $this->request->getQuery('lan');
        $messages = [];
        /**
         * Select file of the language based on url parameter
         */
        $translationFile = APP_PATH.'/messages/' . $language . '.php';

        /**
         * Check if file exist
         */
        if (true !== file_exists($translationFile)) {
            $translationFile = APP_PATH.'/messages/en.php';
        }
        
        require $translationFile;

        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $cache=$this->cache;
        /**
         * Check if message for selected file exist in cache
         * if, exist pick from cache
         * else, pick from file then put it in cache
         */
        if ($cache->has($language.'_messages')) {
            $messages=(array)$cache->get($language.'_messages');
        } else {
            $cache->set($language.'_messages',$messages);
        }
        /**
         * Return message
         */
        return $factory->newInstance(
            'array',
            [
                'content' => $messages,
            ]
        );
    }
}