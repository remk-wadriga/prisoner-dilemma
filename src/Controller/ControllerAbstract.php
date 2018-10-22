<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 22.10.2018
 * Time: 13:15
 */

namespace App\Controller;

use App\Entity\User;
use Mcfedr\JsonFormBundle\Controller\JsonController;

abstract class ControllerAbstract extends JsonController
{
    public function getUser(): User
    {
        return parent::getUser();
    }
}