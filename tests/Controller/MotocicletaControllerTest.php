<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MotocicletaControllerTest extends WebTestCase
{
    public function testGetMotocicletas(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/motocicletas');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreateMotocicleta(): void
    {
        $client = static::createClient();
        $data = [
            'modelo' => 'Modelo Test',
            'cilindrada' => 600,
            'marca' => 'Yamaha',
            'tipo' => 'Deportiva',
            'edicionLimitada' => true,
            'extras' => ['Llantas deportivas']
        ];

        $client->request('POST', '/api/motocicletas', [], [], ['CONTENT_TYPE' => 'application/ld+json'], json_encode($data));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testGetMotocicletaById(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/motocicletas/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
    }

    public function testDeleteMotocicleta(): void
    {
        $client = static::createClient();

        // Realizar la eliminación de la motocicleta
        $client->request('DELETE', '/api/motocicletas/1');

        // Verificar que la respuesta tenga el código de estado 204 (No Content)
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }


    public function testUpdateMotocicleta(): void
    {
        $client = static::createClient();
        $data = [
            'cilindrada' => 800,
        ];

        $client->request('PATCH', '/api/motocicletas/2', [], [], ['CONTENT_TYPE' => 'application/merge-patch+json'], json_encode($data));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
