<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 08.09.2018
 * Time: 23:23
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage()
    {
        return $this->json([
            ['name' => 'Strategy 1'],
            ['name' => 'Strategy 2'],
            ['name' => 'Strategy 3'],
            ['name' => 'Strategy 4'],
            ['name' => 'Strategy 5'],
        ]);
    }
}