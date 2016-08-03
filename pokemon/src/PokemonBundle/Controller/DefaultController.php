<?php

namespace PokemonBundle\Controller;

use PokemonBundle\Entity\Point;
use PokemonBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PokemonBundle\Base\Controller;
use FOS\RestBundle\Controller\Annotations\Post;
use PokemonBundle\Form\Type\PointType;
use Symfony\Component\HttpFoundation\Request;

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
        $params['items'] = [];
        $params['pokemon'] = array_map(
            function($a){
                return [
                    'id'=>$a->getId(),
                    'name'=>$a->getName(),
                    'image'=>$a->getImageUrl()
                ];
            },
            $this->getDoctrine()->getRepository('PokemonBundle:Pokemon')->findAll()
        );
        return $this->render('PokemonBundle:Front:main.html.twig',$params);
    }


    /**
     * @Route("/location/{locX}/{locY}")
     */
    public function getLocationPoints(Request $request,$locX,$locY){

        //pagenation
        $page = $request->query->get('page');
        $page = ($page>1?intval($page):1);

        //normalization
        $locX = floatval($locX);
        $locY = floatval($locY);
        $x = round($locX,3);
        $y = round($locY,3);

        //get points
        $points = $this->areaPokemon($x,$y,$page);
        if(!empty($points['points'])){
            //calculate distance
            foreach ($points['points'] as &$point){
                $point['distance'] = $this->getGoogleMapLength($locX,$locY,$point['locationX'],$point['locationY']);
            }
        }



        $this->renderApiJson($points);
    }

    /**
     * @POST("/points")
     */
    public function pointsAction(Request $request){
        /**
         * @var User $user
         * @var Point $point
         */
        $user = $this->getUser();
        if(!$user)
            $this->renderApiJson(['error'=>'Ошибка авторизации']);

        //cache
        $cacheKey = 'pointBlock_'.$user->getId();
        $cache = $this->get('cache');
        $cache->setNamespace('point.cache');

        if(false !== ($response = $cache->fetch($cacheKey)))
            $this->renderApiJson(['error'=>'Вы уже создавали точку, попробуйте повторить действие позже']);

        $point = $this->createForm(new PointType(), new Point())->handleRequest($request)->getData();
        $point->setCreateAt(new \DateTime())
              ->setAuthor($user);
        $errors = $this->get('validator')->validate($point);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных']);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($point);
        $manager->flush();

        $formater = $this->getSerializePointCallback();
        $response = array_map($formater,[$point]);
        $this->resetSectorCache($point);
        $cache->save($cacheKey,'1',60); // 1 min

        $this->renderApiJson(['success'=>true, 'point'=>array_shift($response)]);
    }

    /**
     * @POST("/pointsConfirm/{id}")
     */
    public function pointsConfirmAction($id){
        /**
         * @var User $user
         * @var Point $point
         */
        $user = $this->getUser();
        if(!$user)
            $this->renderApiJson(['error'=>'Ошибка авторизации']);

        $point = $this->getDoctrine()
                      ->getRepository('PokemonBundle:Point')
                      ->getOneById($id);
        if(!$point)
            $this->renderApiJson(['error'=>'Точка была удалена или отсутствует']);

        if($point->getAuthor() && $point->getAuthor()->getId() == $user->getId())
            $this->renderApiJson(['error'=>'Нельза подтверждать точку, автором которой являетесь Вы.']);


        $json = $point->getJsonInfo();
        if(!$json)
            $json = json_decode($json);
        if(!isset($json['confirm']) && !isset($json['reject']))
            $json = [
                'confirm'=>[],
                'reject'=>[]
            ];

        $json['confirm'][] = $user->getId();
        $json['confirm'] = array_unique($json['confirm']);

        if(false !== ($key=array_search($user->getId(),$json['reject'])))
            unset($json['reject'][$key]);

        if(count($json['confirm']) == 5) // иммено когда нужное подтвержение
            $point->setConfirm(true);

        $point->setJsonInfo(json_encode($json));

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($point);
        $manager->flush();
        $this->resetSectorCache($point);
        $formater = $this->getSerializePointCallback();
        $response = array_map($formater,[$point]);

        $this->renderApiJson(['success'=>true, 'point'=>array_shift($response)]);
    }

    /**
     * @POST("/pointsReject/{id}")
     */
    public function pointsRejectAction($id){
        /**
         * @var User $user
         * @var Point $point
         */
        $user = $this->getUser();
        if(!$user)
            $this->renderApiJson(['error'=>'Ошибка авторизации']);

        $point = $this->getDoctrine()
                      ->getRepository('PokemonBundle:Point')
                      ->getOneById($id);
        if(!$point)
            $this->renderApiJson(['error'=>'Точка была удалена или отсутствует']);

        if($point->getAuthor() && $point->getAuthor()->getId() == $user->getId())
            $this->renderApiJson(['error'=>'Нельза подтверждать точку, автором которой являетесь Вы.']);

        $json = $point->getJsonInfo();
        if(!$json)
            $json = json_decode($json);
        if(!isset($json['confirm']) && !isset($json['reject']))
            $json = [
                'confirm'=>[],
                'reject'=>[]
            ];

        $json['reject'][] = $user->getId();
        $json['reject'] = array_unique($json['reject']);

        if(false !== ($key=array_search($user->getId(),$json['confirm'])))
            unset($json['confirm'][$key]);

        $manager = $this->getDoctrine()->getManager();
        if(count($json['reject'])<5){    //update
            $point->setJsonInfo(json_encode($json));
            $manager->persist($point);
        } else {                        //remove
            $manager->remove($point);
        }
        $manager->flush();
        $this->resetSectorCache($point);
        $this->renderApiJson(['success'=>true]);
    }
}
