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
        $params = $this->getDefaultTemplateParams();

        //demo params
        $x = 44.442;
        $y = 35.504;
        $params['items'] = $this->areaPokemon($x,$y);
        if(!empty($params['items'])){
            //calculate distance
            foreach ($params['items'] as &$point){
                $point['distance'] = $this->getGoogleMapLength($x,$y,$point['locationX'],$point['locationY']);
            }
        }

        return $this->render('PokemonBundle:Front:main.html.twig',$params);
    }

    /**
     * @Route("/location/{locX}/{locY}")
     */
    public function getLocationPoints($locX,$locY){

        //normalization
        $locX = floatval($locX);
        $locY = floatval($locY);
        $x = round($locX,3);
        $y = round($locY,3);

        //get points
        $points = $this->areaPokemon($x,$y);
        if(!empty($points)){
            //calculate distance
            foreach ($points as &$point){
                $point['distance'] = $this->getGoogleMapLength($locX,$locY,$point['locationX'],$point['locationY']);
            }
        }
        $this->renderApiJson($points);
    }
}
