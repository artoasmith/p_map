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

class AuthController extends Controller
{
    /**
     * @Route("/remindPassword")
     */
    public function remindPassword(Request $request)
    {
        $params = $this->getDefaultTemplateParams();

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

        $params = $this->getDefaultTemplateParams();

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
                        'emailFrom'=>$params['email_from'],
                        'emailTo'=>$formdata['email'],
                        '%link%'=>$params['site'].'/confirmRegistration?token='.$confirmToken
                    ], $this);

                    $params['message'] = sprintf("Для подтверждения регистрации перейдите по ссылке отправленой в письме на адрес %s",$formdata['email']);
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
        $params['test'] = '<script>alert("80085");</script>';
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
        $fb = new Facebook([
            'app_id' => $params['fb_app_id'], // Replace {app-id} with your app id
            'app_secret' => $params['fb_app_secret'],
            'default_graph_version' => 'v2.2',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email']; // Optional permissions
        $params['fb_login_link'] = $helper->getLoginUrl($params['site'].'/app_dev.php/fbCallback', $permissions);
        ////
        return $this->render('PokemonBundle:Front:login.html.twig',$params);
    }

    /**
     * @Route("/fbCallback")
     */
    public function fbCallbackAction(Request $request){
        $user = $this->getUser();
        if($user)
            return $this->redirect('/profile');

        $params = $this->getDefaultTemplateParams();
        $fb = new Facebook([
            'app_id' => $params['fb_app_id'], // Replace {app-id} with your app id
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
        echo '<h3>Access Token</h3>';
        var_dump($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);

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
            echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
            exit;
        }

        echo '<h3>Long-lived</h3>';
        var_dump($accessToken->getValue());
        }

        $_SESSION['fb_access_token'] = (string) $accessToken;

        $fb->setDefaultAccessToken(strval($accessToken));
        $response = $fb->get('/me');
        $userNode = $response->getGraphUser();

        print_r($userNode->all());

        exit();
    }
}