<?php
// src/Controller/TestMercureController.php
namespace App\Controller\frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestMercureController extends AbstractController
{
    #[Route('/test-mercure', name: 'test_mercure')]
    public function test(HubInterface $hub): Response
    {
        $update = new Update(
            'https://example.com/chat', // topic
            json_encode(['message' => 'Hello Mercure 👋'])
        );

        $hub->publish($update);

        return new Response('Update sent');
    }
}
