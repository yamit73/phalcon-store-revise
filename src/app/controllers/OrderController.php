<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    /**
     * Orders listing
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->orders=$this->modelsManager
                                ->createQuery(
                                    'SELECT Products.name as name, Orders.* FROM Orders Join Products on Orders.product_id=Products.id'
                                )->execute();
    }

    /**
     * Add order to database with event
     *
     * @return void
     */
    public function addAction()
    {
        $products=Products::find();
        $this->view->products=$products;
        $users=Users::find();
        $this->view->products=$users;
        /**
         * getting order_id from url 
         * If id is given then select that order and update
         * else, create new object of orders model and add order to database
         */
        $id=$this->request->getQuery('id');
        if (isset($id)) {
            $order=Orders::findFirst($id);
            $this->view->order=$order;
        } else {
            $order=new Orders();
        }
        if ($this->request->isPost()) {
            
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $sanitizedData=$escaper->sanitize($postData);

            //creating an object of event manager
            $eventsManagers=$this->di->get('EventsManager');
            //firing event to check if zipcede is provided or not
            $data=$eventsManagers->fire('notification:defaultOrderData', $this, $sanitizedData);
            //die(print_r($data));
            $order->assign(
                $data,
                [
                    'customer_name',
                    'address',
                    'zipcode',
                    'product_id',
                    'quantity'
                ]
            );
            if ($order->save()) {
                $this->view->message = "Order placed";
            } else {
                $this->view->message = "Order not placed: <br>" . implode("<br>", $order->getMessages());
            }
        }
        
    }
}
