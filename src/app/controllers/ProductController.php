<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    /**
     * Product listing
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->products=Products::find();
    }

    /**
     * Add product to database
     * Update products detail
     *
     * @return void
     */
    public function addAction()
    {
        /**
         * getting product_id from url 
         * If id is given then select that product and update
         * else, create new object of products model and add product to database
         */
        $id=$this->request->getQuery('id');
        if (isset($id)) {
            $product=Products::findFirst($id);
            $this->view->product=$product;
        } else {
            $product=new Products();
        }
        
        if ($this->request->isPost()) {
            $setting=Settings::findFirst();
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $sanitizedData=$escaper->sanitize($postData);

            //creating an object of event manager
            $eventsManagers=$this->di->get('EventsManager');
            //firing event to check if title optimization is on or not
            $data=$eventsManagers->fire('notification:titleOptimize', $this, $sanitizedData);
            $product->assign(
                $data,
                [
                    'name',
                    'description',
                    'tags',
                    'price',
                    'stock'
                ]
            );
            if ($product->save()) {
                $this->view->message = "Product added";
            } else {
                $this->view->message = "Product not added: <br>" . implode("<br>", $product->getMessages());
            }
        }
    }
}
