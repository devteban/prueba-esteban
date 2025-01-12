<?php

namespace App\DataFixtures;

use App\Entity\Motocicleta;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class MotocicletaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Crear MT-07
        $mt07 = new Motocicleta();
        $mt07->setModelo('MT-07')
            ->setCilindrada(689)
            ->setMarca('Yamaha')
            ->setTipo('Naked')
            ->setExtras(['Abs', 'Frenos de disco', 'Suspensión regulable'])
            ->setPeso(164)
            ->setEdicionLimitada(false);

        // Crear MT-09
        $mt09 = new Motocicleta();
        $mt09->setModelo('MT-09')
            ->setCilindrada(847)
            ->setMarca('Yamaha')
            ->setTipo('Naked')
            ->setExtras(['Control de tracción', 'Quick shifter', 'Frenos de disco'])
            ->setPeso(188)
            ->setEdicionLimitada(false);

        // Crear Triumph Street Triple RS
        $streetTripleRs = new Motocicleta();
        $streetTripleRs->setModelo('Triumph Street Triple RS')
            ->setCilindrada(765)
            ->setMarca('Triumph')
            ->setTipo('Naked')
            ->setExtras(['Suspensión Showa', 'Control de tracción', 'Frenos Brembo'])
            ->setPeso(168)
            ->setEdicionLimitada(false);

        // Crear Kawasaki H2R
        $h2r = new Motocicleta();
        $h2r->setModelo('Kawasaki H2R')
            ->setCilindrada(998)
            ->setMarca('Kawasaki')
            ->setTipo('Superbike')
            ->setExtras(['Turboalimentada', 'Suspensión Ohlins', 'Frenos Brembo'])
            ->setPeso(216)
            ->setEdicionLimitada(true);

        // Persistir las motos
        $manager->persist($mt07);
        $manager->persist($mt09);
        $manager->persist($streetTripleRs);
        $manager->persist($h2r);

        // Guardar todos los objetos en la base de datos
        $manager->flush();
    }
}
