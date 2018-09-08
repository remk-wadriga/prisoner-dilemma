<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 04.09.2018
 * Time: 19:58
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FriendController extends AbstractController
{
    private static $firends = [
        [
            'id' => 234823,
            'name' => 'John',
        ],
        [
            'id' => 6998809,
            'name' => 'Vasya',
        ],
        [
            'id' => 899879,
            'name' => 'Petya',
        ],
    ];

    /**
     * @Route("/friends", name="app_get_friends")
     */
    public function friends()
    {
        return $this->json(self::$firends);
    }

    /**
     * @Route("/friend", name="app_create_friend", methods={"POST"})
     */
    public function createFriend(Request $request)
    {
        return $this->json([
            'id' => rand(100, 10000),
            'name' => $request->get('name'),
        ]);
    }

    /**
     * @Route("/friend/{id}", name="app_update_friend", methods={"PUT"})
     */
    public function updateFriend($id, Request $request)
    {
        return $this->json([
            'id' => $id,
            'name' => $request->get('name'),
        ]);
    }

    /**
     * @Route("/friend/{id}", name="app_delete_friend", methods={"DELETE"})
     */
    public function deleteFriend($id)
    {
        return $this->json(['id' => $id]);
    }
}