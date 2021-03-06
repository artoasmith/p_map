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

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphPicture;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use BW\Vkontakte as VK;
use MetzWeb\Instagram\Instagram;

class AuthController extends Controller
{
    /**
     * @Route("/remindPassword")
     */
    public function remindPassword(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams($request);
        $params['show_ball'] = false;
        $params['form_user'] = [
            'key'=>'form_user',
            'ms'=>'',
            'fields'=>[
                'user'=>[
                    'value'=>'',
                    'error'=>''
                ]
            ]
        ];

        $form_pass = [
            'key'=>'form_pass',
            'error'=>'',
            'ms'=>'',
            'token'=>''
        ];
        /**
         * @var User $user
         */
        $userManager = $this->get('fos_user.user_manager');
        if($request->getMethod() == "POST")
        {
            $userPass = $request->request->get($form_pass['key']);
            if(!empty($userPass)){
                $params['form_pass'] = $form_pass;
                $params['form_pass']['error'] = 'Ошибка передачи данных';
                if(isset($userPass['token']) && isset($userPass['password']) && isset($userPass['repassword'])){
                    $params['form_pass']['token'] = $userPass['token'];
                    $pass = $userPass['password'];
                    if($userPass['password'] != $userPass['repassword']) {
                        $params['form_pass']['error'] = 'Пароли не совкадают';
                        $pass = false;
                    }

                    if($pass && strlen($pass)<8){
                        $pass = false;
                        $params['form_pass']['error'] = 'Пароль должен быть не меньше 8 сымволов';
                    }

                    if($pass) {
                        $user = $userManager->findUserByConfirmationToken($userPass['token']);
                        if ($user && $user->getCreateTokenAt() && time() - $user->getCreateTokenAt()->getTimestamp() < 86400) {
                            $encoder_service = $this->get('security.encoder_factory');
                            $encoder = $encoder_service->getEncoder($user);
                            $encoded_pass = $encoder->encodePassword($pass, $user->getSalt());
                            $user->setPassword($encoded_pass);
                            $userManager->updateUser($user);

                            $params['form_pass']['error'] = '';
                            $params['form_pass']['ms'] = 'Пароль успешно изменен.';
                        }
                    }
                }
            } else {
                $user = $request->request->get($params['form_user']['key']);
                if(isset($user['user']) && !empty($user['user'])){
                    //check user
                    $thisIsEmail = filter_var($user['user'],FILTER_VALIDATE_EMAIL);
                    if($thisIsEmail){
                        $user = $userManager->findUserByEmail($user['user']);
                    } else {
                        $user = $userManager->findUserByUsername($user['user']);
                    }
                    if(!$user || !$user->isEnabled())
                        $params['form_user']['fields']['user']['error'] = 'На сайте нет пользователя с такими данными';
                    else {
                        //check cache

                        // generate token and sent email
                        $token = md5(time().'randomStringTextChangePw'.rand(1,100).$user->getId());
                        $user->setConfirmationToken($token)
                             ->setCreateTokenAt(new \DateTime());
                        $userManager->updateUser($user);

                        //send email
                        EmailTemplate::sendEmail('remind_pass',[
                            'emailFrom'=>$params['email_from'],
                            'emailTo'=>$user->getEmail(),
                            '%link%'=>$params['site'].'/remindPassword?token='.$token
                        ], $this);

                        $params['form_user']['ms'] = 'На адрес электронной почты выслано письмо с ссылкой на смену регистрации';
                    }
                }
            }

        } else {
            $token = $request->query->get('token');
            if (!empty($token)) {
                $params['form_user']['ms'] = 'Неверный или неактивный код активации';
                $user = $userManager->findUserByConfirmationToken($token);
                if($user && $user->getCreateTokenAt() && time()-$user->getCreateTokenAt()->getTimestamp()<86400){
                    //show change form
                    $params['form_pass'] = $form_pass;
                    $params['form_pass']['token'] = $token;
                }
            }
        }
//$this->renderApiJson($params);
        return $this->render('PokemonBundle:Front:remind_pass.html.twig',$params);
    }

    /**
     * @Route("/confirmRegistration")
     */
    public function confirmRegistrationAction(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $token = $request->query->get('token');
        $errorMessage = 'Неверный код активации';

        $params = $this->getDefaultTemplateParams($request);
        $params['show_ball'] = false;

        if(empty($token)) {
            $params['message_error'] = $errorMessage;
            return $this->render('PokemonBundle:Front:confirm_reg.html.twig',$params);
        }

        /**
         * @var UserManager $userManager
         */
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByConfirmationToken($token);
        if(!$user || $user->isEnabled()) {
            $params['message_error'] = $errorMessage;
            return $this->render('PokemonBundle:Front:confirm_reg.html.twig',$params);
        }

        $user->setEnabled(true);
        $userManager->updateUser($user);
        $params['message'] = 'Активация пройшла успешно, можете авторизироватся на сайте';
        return $this->render('PokemonBundle:Front:confirm_reg.html.twig',$params);
    }

    /**
     * @Route("/registration")
     */
    public function RegistrationAction(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams($request);
        $params['show_ball'] = false;
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
            if($formdata['repassword'] != $formdata['password']){
                $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['password']['error'] = 'Неверное подтверждение пароля';
            }
            if(strlen($formdata['password'])<8)
                $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['password']['error'] = 'Пароль должен быть не меньше 8 символов';
            if(!preg_match("/^[a-zA-Z][a-zA-Z0-9_]+$/",$formdata['login']))
                $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['login']['error'] = 'Логин недопустимого формата';
            if(strlen($formdata['login'])<5)
                $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['login']['error'] = 'Логин должен быть не меньше 5 символов';
            if(empty($formdata['email']))
                $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['email']['error'] = 'Недопустимый адрес электронной почты';

            $params['reg_form']['fields']['login']['value'] = $formdata['login'];
            $params['reg_form']['fields']['email']['value'] = $formdata['email'];

            $params['reg_form']['ff'] = $formdata;
            if($error === false){
                /**
                 * @var UserManager $userManager
                 */
                $userManager = $this->get('fos_user.user_manager');

                $u = $userManager->findUserByEmail($formdata['email']);
                if($u)
                    $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['email']['error'] = 'Адрес электронной почты уже используется';

                $u = $userManager->findUserByUsername($formdata['login']);
                if($u)
                    $params['reg_form']['error'][] = $error = $params['reg_form']['fields']['login']['error'] = 'Логин уже используется';

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
                        'emailFrom'=>$params['email_from'],
                        'emailTo'=>$formdata['email'],
                        '%link%'=>$params['site'].'/confirmRegistration?token='.$confirmToken
                    ], $this);

                    $params['message'] = sprintf("Для подтверждения регистрации перейдите по ссылке отправленой в письме на адрес %s",$formdata['email']);

                    $params['reg_form']['message'] = $params['message'];
                    $params['reg_form']['success'] = true;
                }
            }
        }

        //if ajax registration
        if ( $request->isXmlHttpRequest() )
            $this->renderApiJson($params['reg_form']);

        return $this->render('PokemonBundle:Front:registration.html.twig',$params);
    }

    /**
     * @Route("/changeProfileData")
     */
    public function changeProfileData(Request $request){

        if($request->getMethod() != 'POST')
            return $this->redirect('/');

        /**
         * @var User $a
         */
        $a = $this->getUser();
        if(!$a)
            return $this->redirect('/');

        $new_username = $request->request->get('user_name');
        $new_email = $request->request->get('user_email');
        $new_pass = $request->request->get('password');

        //update username
        if($new_username && $a->getUsername() != $new_username)
            $a->setUsername($new_username);

        /**
         * @var UserManager $userManager
         */
        $userManager = $this->get('fos_user.user_manager');

        //update email
        if($new_email && $new_email != $a->getEmail() && filter_var($new_email,FILTER_VALIDATE_EMAIL)){
            //unique check
            $u = $userManager->findUserByEmail($new_email);
            if($u)
                $this->renderApiJson(['message'=>'Адрес электронной почты уже используется.']);

            $a->setEmail($new_email);
        }

        //update pass
        if($new_pass && is_array($new_pass) && isset($new_pass['old']) && isset($new_pass['new']) && isset($new_pass['confirm'])){
            $encoder_service = $this->get('security.encoder_factory');
            $encoder = $encoder_service->getEncoder($a);
            $encoded_pass = $encoder->encodePassword($new_pass['old'], $a->getSalt());

            if($encoded_pass != $a->getPassword())
                $this->renderApiJson(['message'=>'Неверный пароль.']);

            if($new_pass['new'] != $new_pass['confirm'])
                $this->renderApiJson(['message'=>'Пароли не совпадают.']);

            if(strlen($new_pass['new'])<8)
                $this->renderApiJson(['message'=>'Пароль должен быть не меньше 8 символов.']);

            $encoded_pass = $encoder->encodePassword($new_pass['new'], $a->getSalt());
            $a->setPassword($encoded_pass);
        }

        $userManager->updateUser($a);
        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/profile")
     */
    public function profileAction(Request $request){
        /**
         * @var User $a
         */
        $a = $this->getUser();
        if(!$a)
            return $this->redirect('/');

        $params = array_merge($this->getProfileInfo($a),$this->getDefaultTemplateParams($request));

        $params['user_pay_check'] = [];
        foreach ($this->getDoctrine()
                     ->getRepository('PokemonBundle:PayCheck')
                     ->findBy(['user'=>$a->getId()],['createdAt'=>'DESC','id'=>'DESC']) as $item){
            $params['user_pay_check'][$item->getPoint()->getId()] = $item;
        }
        /**
         * @var Point $item
         */
        $params['user_points'] = [];
        foreach ($this->getDoctrine()
            ->getRepository('PokemonBundle:Point')
            ->findBy(['author'=>$a->getId()],['createAt'=>'DESC','id'=>'DESC']) as $item){
            $params['user_points'][$item->getId()] = [
                'id'=>$item->getId(),
                'locationX'=>$item->getLocationX(),
                'locationY'=>$item->getLocationY(),
                'pokemon'=>$item->getPokemon()->getId(),
                'jsonInfo'=>$item->getJsonInfo(),
                'confirm'=>$item->getConfirm(),
                'enabled'=>$item->getEnabled(),
                'address'=>$item->getAddress(),
                'reward'=>(isset($params['user_pay_check'][$item->getId()])?$params['user_pay_check'][$item->getId()]->getValue():0)
            ];
        }

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

        /////
        return $this->render('PokemonBundle:Front:profile.html.twig',$params)->setSharedMaxAge(0);
    }

    /**
     * @Route("/login")
     */
    public function loginAction(Request $request){
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams($request);
        $params['show_ball'] = false;
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

        return $this->redirect('/');
        //return $this->render('PokemonBundle:Front:login.html.twig',$params);
    }

    /**
     * @Route("/fbCallback")
     */
    public function fbCallbackAction(Request $request){
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams($request);
        $fb = new Facebook([
            'app_id' => $params['fb_app_id'],
            'app_secret' => $params['fb_app_secret'],
            'default_graph_version' => 'v2.2',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $error = false;
        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error = 'Graph returned an error: ' . $e->getMessage();
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                $error = [
                    "Error: " . $helper->getError(),
                    "Error Code: " . $helper->getErrorCode(),
                    "Error Reason: " . $helper->getErrorReason(),
                    "Error Description: " . $helper->getErrorDescription()
                ];
            } else {
                $error = 'Bad request';
            }
        }

        if($error)
            $this->renderApiJson($error);


        // Logged in
      //  echo '<h3>Access Token</h3>';
      //  var_dump($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
    //    echo '<h3>Metadata</h3>';
    //    var_dump($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
      //  $tokenMetadata->validateAppId($params['fb_app_id']); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                $this->renderApiJson("Error getting long-lived access token: " . $helper->getMessage());

            }

        //echo '<h3>Long-lived</h3>';
        //var_dump($accessToken->getValue());
        }

        $error = false;
        try {
            $fb->setDefaultAccessToken(strval($accessToken));
            $response = $fb->get('/me?fields=id,name,picture');
            $userNode = $response->getGraphUser();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error = 'Graph returned an error: ' . $e->getMessage();
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        if($error)
            $this->renderApiJson($error);

        $fbUser = $userNode->all();
        /**
         * @var UserManager $userManager
         * @var User $User
         */
        $userManager = $this->get('fos_user.user_manager');
        $u = $userManager->findUserBy(['facebookUid'=>$fbUser['id']]);

        $response = $this->redirect('/profile');
        if($u){ //login
            try {
                $this->container->get('fos_user.security.login_manager')->loginUser(
                    $this->container->getParameter('fos_user.firewall_name'),
                    $u,
                    $response);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }
        } else { // registration
            $User = $userManager->createUser();

            $confirmToken = md5(time().'randomStringText'.rand(1,100));

            $User->setPassword($confirmToken)
                ->setUsername('fb'.$fbUser['id'])
                ->setEmail($this->getRandEmail())
                ->setEnabled(true)
                ->setFacebookUid($fbUser['id'])
                ->setFacebookName($fbUser['name'])
                ->setSuperAdmin(false)
                ->setFirstname($fbUser['name'])
            ;
            if(isset($fbUser['picture']) && is_object($fbUser['picture'])){
                /**
                 * @var GraphPicture $pic
                 */
                $pic = $fbUser['picture'];
                $User->upload('Image',$pic->getUrl());
            }
            $userManager->updateUser($User);

            try {
                $this->container->get('fos_user.security.login_manager')->loginUser(
                    $this->container->getParameter('fos_user.firewall_name'),
                    $User,
                    $response);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }
        }
        return $response;
    }

    /**
     * @Route("/vkCallback")
     */
    public function vkCallbackAction(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams($request);
        $vk = new VK([
            'client_id' => $params['vk_app_id'],
            'client_secret' => $params['vk_app_secret'],
            'redirect_uri' => $params['site'].'/vkCallback'
        ]);

        $vk->authenticate();
        $userId = $vk->getUserId();

        if(!$userId)
            return $this->redirect('/login');

        /**
         * @var UserManager $userManager
         * @var User $User
         */
        $userManager = $this->get('fos_user.user_manager');
        $u = $userManager->findUserBy(['vkontakteUid'=>$userId]);

        $response = $this->redirect('/profile');
        if($u){ //login
            try {
                $this->container->get('fos_user.security.login_manager')->loginUser(
                    $this->container->getParameter('fos_user.firewall_name'),
                    $u,
                    $response);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }
        } else { // registration

            $user = $vk->api('users.get', [
                'user_id' => $userId,
                'fields' => [
                    'photo_100'
                ],
            ]);
            $name = (isset($user[0]['first_name'])?$user[0]['first_name']:'').' '.(isset($user[0]['last_name'])?$user[0]['last_name']:'');
            $name = trim($name);

            $User = $userManager->createUser();

            $confirmToken = md5(time().'randomStringText'.rand(1,100));

            $User->setPassword($confirmToken)
                ->setUsername('vk'.$userId)
                ->setEmail($this->getRandEmail())
                ->setVkontakteUid($userId)
                ->setEnabled(true)
                ->setSuperAdmin(false)
                ->setFirstname($name)
            ;

            if(isset($user[0]['photo_100']))
                $User->upload('Image',$user[0]['photo_100']);

            $userManager->updateUser($User);

            try {
                $this->container->get('fos_user.security.login_manager')->loginUser(
                    $this->container->getParameter('fos_user.firewall_name'),
                    $User,
                    $response);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }
        }
        return $response;
    }

    /**
     * @Route("/gpCallback")
     */
    public function gpCallbackAction(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        if (!$request->query->get('code'))
            return $this->redirect('/login');

        $params = $this->getDefaultTemplateParams($request);

        $client = new \Google_Client();
        $client->setClientId($params['gp_app_id']);
        $client->setClientSecret($params['gp_app_secret']);
        $client->setRedirectUri($params['site'].'/gpCallback');
        $client->addScope("email");
        $client->addScope("profile");

        $service = new \Google_Service_Oauth2($client);
        $token = $client->fetchAccessTokenWithAuthCode($request->query->get('code'));
        $client->setAccessToken($token);

        /**
         * @var \Google_Service_Oauth2_Userinfoplus $user
         */
        $user = $service->userinfo->get();
        $id = $user->getId();

        /**
         * @var UserManager $userManager
         * @var User $User
         */
        $userManager = $this->get('fos_user.user_manager');
        $u = $userManager->findUserBy(['gplusUid'=>$id]);

        $response = $this->redirect('/profile');
        if($u){ //login
            try {
                $this->container->get('fos_user.security.login_manager')->loginUser(
                    $this->container->getParameter('fos_user.firewall_name'),
                    $u,
                    $response);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }
        } else { // registration
            $User = $userManager->createUser();

            $confirmToken = md5(time().'randomStringText'.rand(1,100));

            $User->setPassword($confirmToken)
                ->setUsername('gp'.$id)
                ->setEmail($this->getRandEmail())
                ->setGplusUid($id)
                ->setEnabled(true)
                ->setFirstname($user->getName())
                ->setSuperAdmin(false);
            $User->upload('Image',$user->getPicture());
            $userManager->updateUser($User);

            try {
                $this->container->get('fos_user.security.login_manager')->loginUser(
                    $this->container->getParameter('fos_user.firewall_name'),
                    $User,
                    $response);
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
            }
        }
        return $response;
    }

    /**
     * @Route("/inCallback")
     */
    public function inCallbackAction(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        if (!$request->query->get('code'))
            return $this->redirect('/login');

        $params = $this->getDefaultTemplateParams($request);

        $instagram = new Instagram(array(
            'apiKey'      => $params['in_app_id'],
            'apiSecret'   => $params['in_app_secret'],
            'apiCallback' => $params['site'].'/inCallback'
        ));

        $data = $instagram->getOAuthToken($request->query->get('code'));
        if(@$data->user->id){
            $id = $data->user->id;
            /**
             * @var UserManager $userManager
             * @var User $User
             */
            $userManager = $this->get('fos_user.user_manager');
            $u = $userManager->findUserBy(['instagramUid'=>$id]);

            $response = $this->redirect('/profile');
            if($u){ //login
                try {
                    $this->container->get('fos_user.security.login_manager')->loginUser(
                        $this->container->getParameter('fos_user.firewall_name'),
                        $u,
                        $response);
                } catch (AccountStatusException $ex) {
                    // We simply do not authenticate users which do not pass the user
                    // checker (not enabled, expired, etc.).
                }
            } else { // registration
                $User = $userManager->createUser();

                $confirmToken = md5(time().'randomStringText'.rand(1,100));

                $User->setPassword($confirmToken)
                    ->setUsername('in'.$id)
                    ->setEmail($this->getRandEmail())
                    ->setInstagramUid($id)
                    ->setFirstname($data->user->full_name)
                    ->setEnabled(true)
                    ->setSuperAdmin(false);
                $User->upload('Image',$data->user->profile_picture);
                $userManager->updateUser($User);

                try {
                    $this->container->get('fos_user.security.login_manager')->loginUser(
                        $this->container->getParameter('fos_user.firewall_name'),
                        $User,
                        $response);
                } catch (AccountStatusException $ex) {
                    // We simply do not authenticate users which do not pass the user
                    // checker (not enabled, expired, etc.).
                }
            }
            return $response;
        }
        print_r($data); exit();
    }
}