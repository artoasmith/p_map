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

    }

    /**
     * @Route("/login")
     */
    public function loginAction(){

        exit('blea');

    }
}