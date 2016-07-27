<?php

namespace PokemonBundle\Controller;

use PokemonBundle\Entity\Point;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PokemonBundle\Base\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $points = $this->areaPokemon(4.433,-2.132);
        print_r($points);
        exit();
        return $this->render('PokemonBundle:Default:index.html.twig');
    }

    /**
     * @Route("/location/{locX}/{locY}")
     */
    public function getLocationPoints($locX,$locY){

        //normalization
        $x = round(floatval($locX),3);
        $y = round(floatval($locY),3);

        //get points
        $points = $this->areaPokemon($x,$y);
        $this->renderApiJson($points);
    }
}
