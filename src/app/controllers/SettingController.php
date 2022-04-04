<?php

use Phalcon\Mvc\Controller;

class SettingController extends Controller
{
    /**
     * function to populate and update default setting
     *
     * @return void
     */
    public function indexAction()
    {
        $setting=Settings::findFirst();
        $this->view->setting=$setting;
        //checking if user has submitted the form
        if ($this->request->isPost()) {
            //sanitizing form data
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $data=$escaper->sanitize($postData);
            //updating settings
            $setting->assign(
                $data,
                [
                    'title_optimization',
                    'price',
                    'stock',
                    'zipcode'
                ]
            );
            if ($setting->save()) {
                $this->view->message = "Updated successfully!";
            } else {
                $this->view->message = "Not updated: <br>" . implode("<br>", $setting->getMessages());
            }
        }
        
    }
}
