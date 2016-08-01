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
use PokemonBundle\Entity\User;
use Symfony\Component\Yaml\Parser;

class Controller extends BaseController
{
    //generation on ratio 0,002 ~ 220m in range 0.1 ~10 km
    public $areaSide = 0.02;
    public $side = 0.002;
    public $tailLength = 4; //google maps need 7 numbers after point. $side is first 3, left 4 for "tail"
    public $pokemonsOnSector = 4;

    public function resetSectorCache(Point $point){
        $x = $point->getLocationX();
        $y = $point->getLocationY();

        //normalization
        $x = round($x,3)-$this->side/2;
        $y = round($y,3)-$this->side/2;

        $iterations = ($this->side/0.001)+1;

        for($i=0; $i<$iterations; $i++){
            for($j=0; $j<$iterations; $j++){
                $this->sectorPokemon(
                    ($x+$i*0.001),
                    ($y+$j*0.001),
                    $this->pokemonsOnSector,
                    $this->side,
                    $this->tailLength,
                    true
                );
            }
        }
    }

    /**
     * @param User $user
     * @param bool $rewriteCathe
     * @return bool
     */
    public function getProfileInfo($user,$rewriteCathe = false){
        if(!is_object($user))
            $user = $this->getDoctrine()
                         ->getRepository('PokemonBundle:User')
                         ->getOneById($user);

        if(!$user)
            return false;

        //cache
        $cacheKey = 'user_'.$user->getId();
        $cache = $this->get('cache');
        $cache->setNamespace('profileUser.cache');

        if(false !== ($response = $cache->fetch($cacheKey)) && !$rewriteCathe)
            return json_decode($response,true);

        //get info
        $response = [
            'firstName'=>$user->getFirstname(),
            'lastName'=>$user->getLastname(),
            'rate'=>$user->getRate(),
            'point'=>[]
        ];

        //points
        $point = $this->getDoctrine()
             ->getRepository('PokemonBundle:Point')
             ->findBy(['author'=>$user->getId()]);
        if($point) {
            $formater = $this->getSerializePointCallback();
            $response['point'] = array_map($formater, $point);
        }

        //save cache
        $responseJson = json_encode($response);
        $cache->save($cacheKey,$responseJson,43200); //12 hour

        return $response;
    }

    public function getDefaultTemplateParams(){
        $yaml = new Parser();
        $a = $yaml->parse(file_get_contents(__DIR__ . '/../../../app/config/params.yml'));
        return (is_array($a)?$a:[]);
    }

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

    public function getGoogleMapLength($fromX,$fromY,$toX,$toY){
        $DEG_TO_RAD = 0.017453292519943295769236907684886;
        $EARTH_RADIUS_IN_METERS = 6372797.560856;
        $MERT_IN_KM = 1000;
        $latitudeArc = ($fromX - $toX) * $DEG_TO_RAD;
        $longitudeArc = ($fromY - $toY) * $DEG_TO_RAD;
        $latitudeH = sin($latitudeArc * 0.5);
        $latitudeH *= $latitudeH;
        $lontitudeH = sin($longitudeArc * 0.5);
        $lontitudeH *= $lontitudeH;
        $tmp = cos($fromX * $DEG_TO_RAD) * cos($toX * $DEG_TO_RAD);
        return round((($EARTH_RADIUS_IN_METERS * 2.0 * asin(sqrt($latitudeH + $tmp * $lontitudeH))) / $MERT_IN_KM),1);
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
        $areaSide = $this->areaSide;
        $side = $this->side;
        $tailLength = $this->tailLength; //google maps need 7 numbers after point. $side is first 3, left 4 for "tail"
        $pokemonsOnSector = $this->pokemonsOnSector;

        $delimeter = $areaSide/2;
        $startX = $x-$delimeter;
        $startY = $y-$delimeter;

        $response = [];
        $iterator = $areaSide/$side;
        for($i=0;$i<$iterator;$i++){
            for($j=0;$j<$iterator;$j++){
                $timeY = $startY+$j*$side;
                if(!$this->pointInCircle($x,$y,$startX,$timeY,$delimeter))
                    continue;

                foreach ($this->sectorPokemon($startX,$timeY,$pokemonsOnSector,$side,$tailLength) as $a)
                   $response[] = $a;
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
    public function sectorPokemon($x, $y,$count,$side,$tailLength,$ignoreCache = false){
        //normalization
        $x = round($x,3);
        $y = round($y,3);

        //cache
        $cacheKey = 'location_'.$x.'_'.$y;
        $cache = $this->get('cache');
        $cache->setNamespace('pointLocation.cache');

        if(false === ($response = $cache->fetch($cacheKey)) || $ignoreCache) {
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
                        ->setLocationY(floatval($newY))
                        ->setCreateAt(new \DateTime())
                    ;
                    $errors = $this->get('validator')->validate($point);
                    if (count($errors) > 0)
                        continue;

                    $res[] = $point;
                    $manager->persist($point);
                }
                $manager->flush();
            }

            //format $res
            $formater = $this->getSerializePointCallback();
            $response = array_map($formater,$res);
            $response = json_encode($response);
            //save cache
            $cache->save($cacheKey,$response,43200); //12 hour
        }
        return json_decode($response,true);
    }

    public function getSerializePointCallback(){
        return function(Point $a){
            return [
                'id'=>$a->getId(),
                'pokemon'=>str_pad(strval($a->getPokemon()->getId()), 3, '0', STR_PAD_LEFT),
                'name'=>$a->getPokemon()->getName(),
                'image'=>$a->getPokemon()->getImageUrl(),
                'confirmed'=>$a->getConfirm(),
                'locationX'=>$a->getLocationX(),
                'locationY'=>$a->getLocationY(),
                'distance'=>false
            ];
        };
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

        //rarest block
        if($a>6){
            $block = rand(1,2);
            if($block==2)
                $a = rand(1,5);
        }
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

        $a = rand(1,2);
        if($a!=2)
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

    private function pointInCircle($x,$y,$c_x,$c_y,$rad)
    {
        return ($this->pointsDist($x,$y,$c_x,$c_y)<=$rad);
    }

    private function pointsDist($x,$y,$x2,$y2){
        $a = pow(($x-$x2),2)+pow(($y-$y2),2);
        return sqrt($a);
    }
}