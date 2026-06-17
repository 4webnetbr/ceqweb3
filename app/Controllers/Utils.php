<?php 
namespace App\Controllers;

use App\Controllers\BaseController;

class Utils extends BaseController
{
    public function upload()
    {
        $uploadDir = "assets/uploads/imagens/";
    
        if ($_FILES['file']['name']) {
            if (!$_FILES['file']['error']) {
              $name = md5(rand(100, 200));
              $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
              $filename = $name.'.'.$ext;
              $destination = $uploadDir.$filename; //change this directory
              $location = $_FILES["file"]["tmp_name"];
              move_uploaded_file($location, $destination);
              echo base_url($uploadDir.$filename); //change this URL
              exit;
            } else {
              echo $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
              exit;
            }
        }
    }

    public function executa_php(){
      passthru('php index.php ServerCeqwebLoop &');

      echo json_encode([]);
    }

}