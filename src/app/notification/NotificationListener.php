<?php
namespace App\Notification;

use DateTimeImmutable;
use Exception;
use OrderController;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use ProductController;
use Settings;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class to handle the events that is attached to notification event
 */
class NotificationListener extends Injectable
{
    /**
     * event handler to optimize the title based on settings
     *  Adding default value to price, stock if they are empty
     * @param Event $event
     * @param ProductController $product
     * @param [type] $data
     * @return void
     */
    public function titleOptimize(Event $event, ProductController $product, $data)
    {
        $setting=Settings::findFirst();

        if (isset($setting->id)) {
            if ($setting->title_optimization=='on' && $data['tags']!='') {
                $data['name'].='+'.$data['tags'];
            }
            if ($data['price']=='') {
                $data['price']=$setting->price;
            }
            if ($data['stock']=='') {
                $data['stock']=$setting->stock;
            }
        }
        return $data;
    }

    /**
     * event handler to add zipcode if it is empty
     *
     * @param Event $event
     * @param OrderController $order
     * @param [type] $data
     * @return void
     */
    public function defaultOrderData(Event $event, OrderController $order, $data)
    {
        $setting=Settings::findFirst();
        if ($data['zipcode']=='' && isset($setting->zipcode)) {
            $data['zipcode']=$setting->zipcode;
        }
        return $data;
    }

    public function beforeHandleRequest(Event $event, \Phalcon\Mvc\Application $application)
    {
        $controller=$this->router->getControllerName() ?? 'index';
        if ($controller != 'login'){
            if ($this->session->get('user_role') !== 'admin') {
                $bearer=$this->request->getQuery('bearer');
                if ($bearer) {
                    try {
                        $key = "example_key";
                        $now = new DateTimeImmutable();
                        /**
                         * parsing token bearer
                         */
                        $parser = new Parser();
                        $tokenObject = $parser->parse($bearer);
    
                        /**
                         * validating token
                         */
                        $validator = new Validator($tokenObject, 100);
                        $validator->validateExpiration($now->getTimestamp())
                                  ->validateNotBefore($now->modify('-1 minute')->getTimestamp());
    
                        /**
                         * decoding token using the same key that is used to encode
                         */
                        $decodedToken = JWT::decode($bearer, new Key($key, 'HS256'));
                        /**
                         * take role from token
                         * controller and action from url
                         */
                        $role=$decodedToken->sub;
                        
                        $action=$this->router->getActionName() ?? 'index';
                        $aclFile=APP_PATH.'/security/acl.cache';
                        /**
                         * check if acl file exixt or not
                         * if exist unserialize its data and use it
                         * else build it
                         */
                        if (is_file($aclFile)==true) {
                            $acl=unserialize(
                                file_get_contents($aclFile)
                            );
                            if ($acl->isAllowed($role, $controller, $action)!==true) {
                                die('<h1 style="color:red;">'.$this->locale->_('access').'</h1>');
                            }
                        } else {
                            $this->response->redirect('secure/buildACL');
                        }
                    } catch (Exception $e) {
                        echo '<h1 style="color:red;">'.$e->getMessage().'</h1>';
                        die;
                    }
                } else {
                     die('<h1 style="color:red;">'.$this->locale->_('token').'</h1>');
                }
            }
        }
        
    }
    /**
     * Function to create JWT token
     *
     * @return void
     */
    public function createToken(Event $event, $user, $data)
    {
        /**
         * Key that will be used to encrypt the data
         */
        $key = "example_key";
        $now = new DateTimeImmutable();
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => $now->getTimestamp(),
            "nbf" => $now->modify('-1 minute')->getTimestamp(),
            "exp" => $now->modify('+1 day')->getTimestamp(),
            'sub' => $data['role'],
            'nam' => $data['name']
        );
        return JWT::encode($payload, $key, 'HS256');
    }
}
