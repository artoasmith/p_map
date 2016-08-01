<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 29.07.16
 * Time: 16:27
 */

namespace PokemonBundle\Controller;

use PokemonBundle\Entity\Point;
use PokemonBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PokemonBundle\Base\Controller;
use Sonata\UserBundle\Entity\UserManager;
use Symfony\Component\Security\Core\SecurityContext;

class AuthController extends Controller
{
    /**
     * @Route("/profile")
     */
    public function profileAction(){
        $a = $this->getUser();
        if(!$a)
            return $this->redirect('/login');


        print_r($a);
        exit();
        //->setSharedMaxAge(600);

    }

    /**
     * @Route("/login")
     */
    public function loginAction(){
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams();
        /**
         * @var UserManager $userManager
         */
        $userManager = $this->get('fos_user.user_manager');
        /////
        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');


        $params['last_username'] = $lastUsername;
        $params['error'] = $error;
        $params['csrf_token'] = $csrfToken;
        /////
        return $this->render('PokemonBundle:Front:login.html.twig',$params);
    }
}