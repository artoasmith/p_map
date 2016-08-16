<?php

namespace PokemonBundle\Controller;

use PokemonBundle\Entity\Point;
use PokemonBundle\Entity\User;
use PokemonBundle\Entity\Blog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PokemonBundle\Base\Controller;
use FOS\RestBundle\Controller\Annotations\Post;
use PokemonBundle\Form\Type\PointType;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller
{
    /**
     * @Route("/blog")
     */
    public function blogListAction(Request $request)
    {
        $params = $this->getDefaultTemplateParams();

        $page = $request->query->get('page');
        $page = ($page>1?intval($page):1);

        $count = $this->getSettingsByCode('blog_page_element_count');
        $count = ($count?intval($count):10);

        $query = "SELECT COUNT(1) as cnt FROM blog as b WHERE b.`enabled`=1";
        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas=$stmt->fetchAll();
        $total = 0;
        if($ideas)
            $total = array_shift($ideas)['cnt'];

        $totalPages = ceil($total/$count);
        if($totalPages>0){
            if($page>$totalPages)
                $page = $totalPages;

            $offset = $page*$count - $count;
            $params['posts'] = $this->getDoctrine()
                          ->getRepository('PokemonBundle:Blog')
                          ->findBy(['enabled'=>true],['createAt'=>'DESC','id'=>'DESC'],$count,$offset);

            $params['page_navi'] = $this->getNaviPager($totalPages,$page);
        }

        return $this->render('PokemonBundle:Blog:list.html.twig',$params);
    }

    /**
     * @Route("/blog/{id}", name="pokemon_blog_blogpost")
     */
    public function blogShowAction($id,Request $request)
    {
        $params = $this->getDefaultTemplateParams();
        /**
         * @var Blog $post
         */
        if(preg_match('/^[0-9]/',$id))
            $post = $this->getDoctrine()
                         ->getRepository('PokemonBundle:Blog')
                         ->find($id);
        else
            $post = $this->getDoctrine()
                ->getRepository('PokemonBundle:Blog')
                ->findOneBy(['code'=>$id]);

        if(!$post || !$post->getEnabled())
            return $this->redirect('/blog');

        $params['post'] = $post;
        return $this->render('PokemonBundle:Blog:show.html.twig',$params);
    }
}