<?php
/**
 * Created by PhpStorm.
 * User: artoa
 * Date: 27.07.2016
 * Time: 11:48
 */

namespace PokemonBundle\Base;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PokemonBundle\Entity\Pokemon;
use PokemonBundle\Entity\Point;

class Controller extends BaseController
{
    public function renderApiJson($value, $httpStatus = 204, $httpMessage = 'Message', $contentType = 'application/json'){
        @ob_clean(); // clear output buffer to avoid rendering anything else
        @header("Content-type: $contentType");
       // @header('HTTP/1.1 '.$httpStatus.' '.$httpMessage);
        @header("Access-Control-Allow-Headers: origin, content-type, accept");
        @header("Access-Control-Allow-Origin: *");
        @header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, PATCH, OPTIONS");

        echo json_encode($value);
        exit();
    }

    /**
     * @param $x
     * @param $y
     * @return array
     */
    public function areaPokemon($x,$y){
        //normalization
        $x = round($x,3);
        $y = round($y,3);

        //generation on ratio 0,002 ~ 220m in range 0.1 ~10 km
        $areaSide = 0.1;
        $side = 0.002;
        $tailLength = 4; //google maps need 7 numbers after point. $side is first 3, left 4 for "tail"
        $pokemonsOnSector = 4;

        $delimeter = $areaSide/2;
        $startX = $x-$delimeter;
        $endX = $x+$delimeter;

        $startY = $y-$delimeter;
        $endY = $y+$delimeter;

        $response = [];
        while($startX<$endX){
            while($startY<$endY){
                foreach ($this->sectorPokemon($startX,$startY,$pokemonsOnSector,$side,$tailLength) as $a)
                    $response[] = $a;
                $startY+=$side;
            }
            $startX += $side;
        }
        return $response;
    }

    /**
     * Return points array on sector
     *
     * @param $x
     * @param $y
     * @param $count
     * @param $side
     * @param $tailLength
     * @return array
     */
    public function sectorPokemon($x, $y,$count,$side,$tailLength){
        //normalization
        $x = round($x,3);
        $y = round($y,3);

        //cache
        $cacheKey = 'location_'.$x.'_'.$y;
        $cache = $this->get('cache');
        $cache->setNamespace('pointLocation.cache');

        if(false === ($response = $cache->fetch($cacheKey))) {
            //check if already generated
            $res = $this->getSectorPoints($x, $y, $side);

            //generate
            if (empty($res)) {
                $pokemonArray = $this->generatePokemon($count);
                $manager = $this->getDoctrine()->getManager();
                foreach ($pokemonArray as $pokemon) {
                    $newX = number_format($x, 3, '.', '') . $this->getTailRand($tailLength);
                    $newY = number_format($y, 3, '.', '') . $this->getTailRand($tailLength);

                    $point = new Point();
                    $point->setPokemon($pokemon)
                        ->setLocationX(floatval($newX))
                        ->setLocationY(floatval($newY));
                    $errors = $this->get('validator')->validate($point);
                    if (count($errors) > 0)
                        continue;

                    $res[] = $point;
                    $manager->persist($point);
                }
                $manager->flush();
            }

            //format $res
            $formater = function(Point $a){
                return [
                    'id'=>$a->getId(),
                    'pokemon'=>$a->getPokemon()->getId(),
                    'image'=>$a->getPokemon()->getImageUrl(),
                    'locationX'=>$a->getLocationX(),
                    'locationY'=>$a->getLocationY()
                ];
            };
            $response = array_map($formater,$res);
            $response = json_encode($response);
            //save cache
            $cache->save($cacheKey,$response,43200); //12 hour
        }
        return json_decode($response,true);
    }
    public function getSectorPoints($x, $y, $side){
        $query = sprintf('SELECT p.id FROM point as p WHERE p.`locationX` >= %f AND p.`locationX` < %f AND p.`locationY` >= %f AND p.`locationY` < %f', $x,($x+$side),$y,($y+$side));

        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas=$stmt->fetchAll();
        if(empty($ideas))
            return [];

        $repository = $this->getDoctrine()
            ->getRepository('PokemonBundle:Point');
        return $repository->findById(array_map(function($a){return $a['id'];},$ideas));
    }

    public function generatePokemon($count){
        if($count<=0)
            return [];

        $rareList = $this->getRareList($count);
        // get pokemons

        $query = sprintf('SELECT p.id FROM pokemon as p WHERE p.rare IN (%s) ORDER BY RAND() LIMIT 0, %d', implode(', ',$rareList),count($rareList));

        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas=$stmt->fetchAll();
        if(empty($ideas))
            return [];

        $repository = $this->getDoctrine()
            ->getRepository('PokemonBundle:Pokemon');

        $pokemons = $repository->findById(array_map(function($a){return $a['id'];},$ideas));
        return $pokemons;
    }

    public function getRareList($count){
        if($count<=0)
            return [];

        // deform count
        $count = $this->deformationCount($count);

        //get rare list
        $a = rand(1,12); //1-12
        $ready = 1;
        $middle = 0; //3-5
        if($a<5 && $count>3) {
            $middle = ($count - 1);
            $ready++;
        }
        $smallCount = $count-$ready; //1-2

        $rareList = [$a];
        if($middle>0)
            $rareList[] = rand(3,5);

        if($smallCount>0){
            for($i=0;$i<$smallCount;$i++){
                $rareList[] = rand(1,2);
            }
        }
        return $rareList;
    }


    private function deformationCount($count){
        if($count<=1)
            return $count;

        $a = rand(1,3);
        if($a!=3)
            return $count;

        $count--;
        return $this->deformationCount($count);

    }

    /**
     * @param $length integer
     * @return string
     */
    private function getTailRand($length)
    {
        if($length<=0)
            return '';
        $a = rand(2,pow(10,$length)) - 1; // if length=3 ? $a = 1-999
        $a = strval($a);
        while(strlen($a)<$length){
            $a = '0'.$a;
            $length--;
        }
        return $a;
    }
}