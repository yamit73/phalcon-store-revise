<?php
use Phalcon\Mvc\Controller;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;

class SecureController extends Controller
{
    /**
     * Function to build ACL file
     * adding roles, components
     * allow access to roles
     *
     * @return void
     */
    public function buildACLAction()
    {
        $aclFile=APP_PATH.'/security/acl.cache';
        //check if acl file already exist
        if (is_file($aclFile)==!true) {
            $acl=new Memory();
            $roles=Roles::find();
            //add roles to acl file
            foreach ($roles as $value) {
                $acl->addRole($value->name);
            }
            //add components to acl file
            $acl->addComponent('index', 'index');
            $acl->addComponent('order', ['index', 'add']);
            $acl->addComponent('product', ['index', 'add']);
            $acl->addComponent('settings', '*');
            $acl->addComponent('role', '*');
            $acl->addComponent('users', '*');
            $acl->addComponent('login', '*');
            //allow access to roles
            $acl->allow('*', 'login', '*');
            $acl->allow('*', 'index', 'index');
            $acl->allow('admin', '*', '*');
            $acl->allow('manager', 'product', '*');
            $acl->allow('accountant', 'order', '*');
            $acl->allow('guest', 'product', 'index');
            //put all the content to the acl file
            file_put_contents(
                $aclFile,
                serialize($acl)
            );
        } else {
            //if acl file already exist simply use it
            $acl=unserialize(
                file_get_contents($aclFile)
            );
        }
        $this->response->redirect('');
    }
}
