<?php

namespace App\Controller;

use App\Core\AbstractController;
use App\Core\Response;

class HomeController extends AbstractController
{
   public function index(): Response
   {
      return $this->views("home.view", ["name" => "home.view.php"]);
   }
}
