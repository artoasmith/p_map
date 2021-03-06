<?php

namespace PokemonBundle\Controller;

use PokemonBundle\Entity\Point;
use PokemonBundle\Entity\CallbackForm;
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
    public function indexAction(Request $request)
    {
        $params = $this->getDefaultTemplateParams($request);

        $params['items'] = [];
        $params['pokemon'] = array_map(
            function($a){
                return [
                    'id'=>$a->getId(),
                    'name'=>$a->getName(),
                    'image'=>$a->getImageUrl()
                ];
            },
            $this->getDoctrine()->getRepository('PokemonBundle:Pokemon')->findBy([],['name'=>'ASC'])
        );


        return $this->render('PokemonBundle:Front:main.html.twig',$params);
    }

    /**
     * @Route("/sitemap")
     */
    public function sitemapAction(Request $request){
        $parems = $this->getDefaultTemplateParams($request);
        $parems['sitemap'] = $this->getSettingsGroup('sitemap');
        return $this->render('PokemonBundle:Front:sitemap.html.twig',$parems);
    }

    /**
     * @Route("/about")
     */
    public function aboutAction(Request $request)
    {
        $params = $this->getDefaultTemplateParams($request);
        $params['about'] = $this->getSettingsGroup('about');
        return $this->render('PokemonBundle:Front:about.html.twig',$params);
    }

    /**
     * @Route("/contacts")
     */
    public function contactsAction(Request $request)
    {
        $params = $this->getDefaultTemplateParams($request);
        $params['contacts'] = $this->getSettingsGroup('contacts');
        $params['about'] = $this->getSettingsGroup('about');
        return $this->render('PokemonBundle:Front:contacts.html.twig',$params);
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
     * Установить точку на карте
     *
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
              ->setAuthor($user)
              ->setEnabled(true);
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

        $this->renderApiJson(['success'=>'Точка успешно добавлена.', 'point'=>array_shift($response)]);
    }

    /**
     * Подтверждение точки на карте
     *
     * @POST("/pointsConfirm/{id}")
     */
    public function pointsConfirmAction($id){
        /**
         * @var User $user
         * @var Point $point
         */
        $user = $this->getUser();
        if(!$user)
            $this->renderApiJson(['code'=>1,'error'=>'Ошибка авторизации']);

        $point = $this->getDoctrine()
                      ->getRepository('PokemonBundle:Point')
                      ->find($id);
        if(!$point || !$point->getEnabled())
            $this->renderApiJson(['code'=>2,'error'=>'Точка была удалена или отсутствует']);

        if($point->getAuthor() && $point->getAuthor()->getId() == $user->getId())
            $this->renderApiJson(['code'=>3,'error'=>'Нельза подтверждать точку, автором которой являетесь Вы.']);


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

        $length = count($json['confirm'])-count($json['reject']);
        if($length >= 5) // иммено когда нужное подтвержение
            $point->setConfirm(true);
        else
            $point->setConfirm(false);

        $point->setJsonInfo(json_encode($json));

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($point);
        $manager->flush();
        $this->resetSectorCache($point);
        $formater = $this->getSerializePointCallback();
        $response = array_map($formater,[$point]);

        $this->renderApiJson(['success'=>'Данные приняты', 'point'=>array_shift($response)]);
    }

    /**
     * Отрицание существования точки
     *
     * @POST("/pointsReject/{id}")
     */
    public function pointsRejectAction($id){
        /**
         * @var User $user
         * @var Point $point
         */
        $user = $this->getUser();
        if(!$user)
            $this->renderApiJson(['code'=>1,'error'=>'Ошибка авторизации']);

        $point = $this->getDoctrine()
                      ->getRepository('PokemonBundle:Point')
                      ->find($id);
        if(!$point || !$point->getEnabled())
            $this->renderApiJson(['code'=>2,'error'=>'Точка была удалена или отсутствует']);

        if($point->getAuthor() && $point->getAuthor()->getId() == $user->getId())
            $this->renderApiJson(['code'=>3,'error'=>'Нельза подтверждать точку, автором которой являетесь Вы.']);

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
        $response = [
            'success'=>'Данные приняты'
        ];
        $length = count($json['confirm'])-count($json['reject']);
        $point->setEnabled($length>-5);
        $point->setJsonInfo(json_encode($json));
        $formater = $this->getSerializePointCallback();
        $res = array_map($formater,[$point]);
        $response['point'] = ($point->getEnabled()?array_shift($res):'');

        $manager->persist($point);
        $manager->flush();
        $this->resetSectorCache($point);
        $this->renderApiJson($response);
    }

    /**
     * Заказ звонка
     *
     * @POST("/callback")
     */
    public function callbackAction(Request $request){
        $form = $request->request->get('callback');

        $callback = new CallbackForm();
        $callback->setCreateAt(new \DateTime())
                 ->setName((isset($form['name'])?$form['name']:null))
                 ->setPhone((isset($form['phone'])?$form['phone']:null));

        $errors = $this->get('validator')->validate($callback);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => ['Ошибка передачи данных']]);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($callback);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }
}
