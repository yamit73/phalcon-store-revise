<?php

use Phalcon\Mvc\Controller;

class RoleController extends Controller
{
    public function indexAction()
    {
        
    }

    /**
     * Add role to database
     *
     * @return void
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $role=new Roles();
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $data=$escaper->sanitize($postData);
            /**
             * Insert data into database
             */
            $role->assign($data, ['name']);
            if ($role->save()) {
                $this->view->message = "Role added";
            } else {
                $this->view->message = "Role not added: <br>" . implode("<br>", $role->getMessages());
            }
        }
    }
}
