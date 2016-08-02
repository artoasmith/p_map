<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 29.07.16
 * Time: 16:27
 */

namespace PokemonBundle\Controller;

use PokemonBundle\Form\Type\RegistrationType;
use PokemonBundle\Entity\Point;
use PokemonBundle\Entity\EmailTemplate;
use PokemonBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PokemonBundle\Base\Controller;
use Sonata\UserBundle\Entity\UserManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    /**
     * @Route("/registration")
     */
    public function RegistrationAction(Request $request)
    {
        $params = $this->getDefaultTemplateParams();
        $params['reg_form'] = [
            'key'=>'registration',
            'error'=>[],
            'fields'=>[
                'login'=>[
                    'value'=>'',
                    'error'=>''
                ],
                'email'=>[
                    'value'=>'',
                    'error'=>''
                ],
                'password'=>[
                    'value'=>'',
                    'error'=>''
                ]
            ]
        ];

        if($request->getMethod() == "POST")
        {
            $form = $this->createForm(new RegistrationType());
            $form->handleRequest($request);
            $formdata = $form->getData();
            $error = false;
            if($formdata['repassword'] != $formdata['password'])
                $error = $params['reg_form']['fields']['password']['error'] = 'Неверное подтверждение пароля';
            if(strlen($formdata['password'])<8)
                $error = $params['reg_form']['fields']['password']['error'] = 'Пароль должен быть не меньше 8 символов';
            if(!preg_match("/^[a-zA-Z][a-zA-Z0-9_]+$/",$formdata['login']))
                $error = $params['reg_form']['fields']['login']['error'] = 'Логин недопустимого формата';
            if(strlen($formdata['login'])<5)
                $error = $params['reg_form']['fields']['login']['error'] = 'Логин должен быть не меньше 5 символов';
            if(empty($formdata['email']))
                $error = $params['reg_form']['fields']['email']['error'] = 'Недопустимый адрес электронной почты';

            $params['reg_form']['fields']['login']['value'] = $formdata['login'];
            $params['reg_form']['fields']['email']['value'] = $formdata['email'];

            if($error === false){
                /**
                 * @var UserManager $userManager
                 */
                $userManager = $this->get('fos_user.user_manager');

                $u = $userManager->findUserByEmail($formdata['email']);
                if($u)
                    $error = $params['reg_form']['fields']['email']['error'] = 'Адрес электронной почты уже используется';

                $u = $userManager->findUserByUsername($formdata['login']);
                if($u)
                    $error = $params['reg_form']['fields']['login']['error'] = 'Логин уже используется';

                if($error === false){
                    $User = $userManager->createUser();

                    $confirmToken = md5(time().'randomStringText'.rand(1,100));

                    $User->setPassword($formdata['password'])
                        ->setEmail($formdata['email'])
                        ->setUsername($formdata['login'])
                        ->setConfirmationToken($confirmToken)
                        ->setEnabled(false)
                        ->setSuperAdmin(false);
                    $userManager->updateUser($User);

                    $encoder_service = $this->get('security.encoder_factory');
                    $encoder = $encoder_service->getEncoder($User);
                    $encoded_pass = $encoder->encodePassword($formdata['password'], $User->getSalt());
                    $User->setPassword($encoded_pass);
                    $userManager->updateUser($User);

                    //send email
                    EmailTemplate::sendEmail('reg_confirm',[
                        'emailFrom'=>'ua567@mail.ru',
                        'emailTo'=>$formdata['email'],
                        '%link%'=>$params.'/confirmRegistration?token='.$confirmToken
                    ]);

                }
            }
        }

        return $this->render('PokemonBundle:Front:registration.html.twig',$params);
    }

    /**
     * @Route("/profile")
     */
    public function profileAction(){
        $a = $this->getUser();
        if(!$a)
            return $this->redirect('/login');

        $params = array_merge($this->getProfileInfo($a),$this->getDefaultTemplateParams());
        /////
        return $this->render('PokemonBundle:Front:profile.html.twig',$params)->setSharedMaxAge(0);
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