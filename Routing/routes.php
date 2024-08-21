<?php

use Database\DataAccess\DAOFactory;
use Exceptions\AuthenticationFailureException;
use Helpers\Authenticate;
use Helpers\ValidationHelper;
use Models\User;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;
use Types\ValueType;

return [
    '/' => function(): HTTPRenderer {
        return new HTMLRenderer('top', []);
    },
    '/register' => function(): HTTPRenderer {
        if(Authenticate::isLoggedIn()){
            FlashData::setFlashData('error', 'Cannot register as you are already logged in.');
            return new RedirectRenderer('/');
        }

        return new HTMLRenderer('register');
    },
    '/form/register' => function(): HTTPRenderer {
        // ユーザが現在ログインしている場合、登録ページにアクセスすることは不可
        if (Authenticate::isLoggedIn()) {
            FlashData::setFlashData('error', 'Cannot register as you are already logged in.');
            return new RedirectRenderer('/');
        }

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

            // ユーザーログイン
            Authenticate::loginAsUser($user);

            FlashData::setFlashData('success', 'Account successfully created.');
            return new RedirectRenderer('/');
        } catch (\InvalidArgumentException $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'Invalid Data.');
            return new RedirectRenderer('/register');
        } catch (Exception $e) {
            error_log($e->getMessage());

            FlashData::setFlashData('error', 'An error occurred.');
            return new RedirectRenderer('/register');
        }
    },
    '/login'=>function(): HTTPRenderer{
        if (Authenticate::isLoggedIn()) {
            FlashData::setFlashData('error', 'You are already logged in.');
            return new RedirectRenderer('/');
        }

        return new HTMLRenderer('login');
    },
    '/form/login'=>function(): HTTPRenderer{
        if (Authenticate::isLoggedIn()) {
            FlashData::setFlashData('error', 'You are already logged in.');
            return new RedirectRenderer('/');
        }

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
    },
    '/logout' => function(): HTTPRenderer {
        if (!Authenticate::isLoggedIn()) {
            FlashData::setFlashData('error', 'Already logged out.');
            return new RedirectRenderer('/');
        }

        Authenticate::logoutUser();
        FlashData::setFlashData('success', 'Logged out.');
        return new RedirectRenderer('/');
    },
];
