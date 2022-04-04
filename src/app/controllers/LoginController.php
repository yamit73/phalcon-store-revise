<?php

use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        if ($this->request->hasPost('email') && $this->request->hasPost('password')) {
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $data=$escaper->sanitize($postData);
            //getting user details form DB
            $user=Users::findFirst(
                [
                    'email = :email: AND password = :password:',
                    'bind' => [
                        'email' => $this->request->getPost('email'),
                        'password' => $this->request->getPost('password'),
                    ],
                ]
            );
            
            if ($user) {
                $role=Roles::findFirst($user->role)->name;
                if ($role==='admin') {
                    //Setting session
                    $this->session->set('user_id', $user->id);
                    $this->session->set('user_name', $user->name);
                    $this->session->set('user_role', $role);
                    $this->response->redirect('');
                } else {
                    echo('<h1 class="text-center text-danger">Please use your token</h1>');
                    die;
                }
            } else {
                $this->loginLogger->error("Wrong credential");
            }
            
        } else {
            $this->view->message='input Field should not be empty';
            $this->loginLogger->error("input Field should not be empty");
        }
    }
    public function logoutAction()
    {
        $this->session->destroy();
        $this->response->redirect('login');
    }
}
