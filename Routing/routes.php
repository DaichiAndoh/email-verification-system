<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticate;
use Helpers\CrossSiteForgeryProtection;
use Helpers\MailSend;
use Helpers\ValidationHelper;
use Models\User;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;
use Types\ValueType;

return [
    '/' => Route::create('/', function (): HTTPRenderer {
        return new HTMLRenderer('top', []);
    }),
    '/mypage' => Route::create('/mypage', function(): HTTPRenderer {
        return new HTMLRenderer('mypage', []);
    })->setMiddleware(['auth']),
    '/register' => Route::create('/register', function(): HTTPRenderer {
        return new HTMLRenderer('register');
    })->setMiddleware(['guest']),
    '/form/register' => Route::create('/form/register', function(): HTTPRenderer {
        try {
            // リクエストメソッドがPOSTかどうかをチェック
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method!');

            $required_fields = [
                'username' => ValueType::STRING,
                'email' => ValueType::EMAIL,
                'password' => ValueType::PASSWORD,
                'confirm_password' => ValueType::PASSWORD,
            ];

            $userDao = DAOFactory::getUserDAO();

            // シンプルな検証
            $validatedData = ValidationHelper::validateFields($required_fields, $_POST);

            // パスワードと確認用パスワードが一致しているかを確認
            if($validatedData['confirm_password'] !== $validatedData['password']){
                FlashData::setFlashData('error', 'Invalid Password!');
                return new RedirectRenderer('/register');
            }

            // Eメールがすでに使用されていないかを確認
            if($userDao->getByEmail($validatedData['email'])){
                FlashData::setFlashData('error', 'Email is already in use!');
                return new RedirectRenderer('/register');
            }

            // 新しいUserオブジェクトを作成
            $user = new User(
                username: $validatedData['username'],
                email: $validatedData['email'],
            );

            // データベースにユーザーを作成
            $success = $userDao->create($user, $validatedData['password']);
            if (!$success) throw new Exception('Failed to create new user!');

            // メール認証用URLを作成
            $verifyRoute = Route::create('/verify/email', function(){});
            $queryParameters = [
                'id' => $user->getId(),
                'expiration' => time() + 3600,
            ];
            $signedURL = Route::create('/verify/email', function(){})->getSignedURL($queryParameters);

            // 認証メールを送信
            $sendResult = MailSend::sendVerificationMail($signedURL, $validatedData['email'], $validatedData['username']);
            if (!$sendResult) throw new Exception('Failed to send virification mail!');

            FlashData::setFlashData('success', 'A verification email has been sent. Please check your inbox.');
            return new RedirectRenderer('/verify/resend');
        } catch (\InvalidArgumentException $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'Invalid Data.');
            return new RedirectRenderer('/register');
        } catch (Exception $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'An error occurred.');
            return new RedirectRenderer('/register');
        }
    })->setMiddleware(['guest']),
    '/verify/resend' => Route::create('/verify/resend', function(): HTTPRenderer {
        return new HTMLRenderer('verify_resend');
    })->setMiddleware(['guest']),
    '/verify/email' => Route::create('/verify/email', function(): HTTPRenderer {
        $required_fields = [
            'id' => ValueType::INT,
            'expiration' => ValueType::INT,
        ];

        $validatedData = ValidationHelper::validateFields($required_fields, $_GET);

        $userDao = DAOFactory::getUserDAO();
        $user = $userDao->getById($validatedData['id']);
        $userDao->updateEmailConfirmedAt($user);

        Authenticate::loginAsUser($user);

        FlashData::setFlashData('success', 'Account successfully created.');
        return new RedirectRenderer('/');
    })->setMiddleware(['guest', 'signature']),
    '/login' => Route::create('/login', function(): HTTPRenderer{
        return new HTMLRenderer('login');
    })->setMiddleware(['guest']),
    '/form/login' => Route::create('/form/login', function(): HTTPRenderer{
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method!');

            $required_fields = [
                'email' => ValueType::EMAIL,
                'password' => ValueType::STRING,
            ];

            $validatedData = ValidationHelper::validateFields($required_fields, $_POST);

            Authenticate::authenticate($validatedData['email'], $validatedData['password']);

            FlashData::setFlashData('success', 'Logged in successfully.');
            return new RedirectRenderer('/');
        } catch (AuthenticationFailureException $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'Failed to login, wrong email and/or password.');
            return new RedirectRenderer('login');
        } catch (\InvalidArgumentException $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'Invalid Data.');
            return new RedirectRenderer('login');
        } catch (Exception $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'An error occurred.');
            return new RedirectRenderer('login');
        }
    })->setMiddleware(['guest']),
    '/logout' => Route::create('/logout', function(): HTTPRenderer {
        Authenticate::logoutUser();
        FlashData::setFlashData('success', 'Logged out.');
        CrossSiteForgeryProtection::removeToken();
        return new RedirectRenderer('/');
    })->setMiddleware(['auth']),
];
