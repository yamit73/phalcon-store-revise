<?php

use Phalcon\Mvc\Controller;
class UserController extends Controller
{
    public function indexAction()
    {
        $this->view->users=Users::find();
    }

    /**
     * Add role to database
     *
     * @return void
     */
    public function addAction()
    {
        /**
         * getting user_id from url 
         * If id is given then select that user and update
         * else, create new object of users model and add new user to database
         */
        $id=$this->request->getQuery('id');
        if (isset($id)) {
            $user=Users::findFirst($id);
            $this->view->user=$user;
        } else {
            $user=new Users();
        }

        $this->view->roles=Roles::find();
        if ($this->request->isPost()) {
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $data=$escaper->sanitize($postData);
            /**
             * add user to database
             */
            $user->assign($data, ['name', 'email', 'role', 'password']);
             //creating an object of event manager
             $eventsManagers=$this->di->get('EventsManager');
                
             $userData['name']=$user->name;
             $userData['role']=Roles::findFirst($user->role)->name;
             //firing event to create a token
             $this->view->token=$eventsManagers->fire('notification:createToken', $this, $userData);
            if ($user->save()) {
                $this->view->message = "Registered!";
            } else {
                $this->view->message = "Not registered: <br>" . implode("<br>", $user->getMessages());
                $this->signupLogger->error("Not created: <br>" . implode("<br>", $user->getMessages()));
            }
        }
    }
}
