<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;

class UserController {
    protected $db;
    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    /**
     * show the login page
     * 
     * @return void
     */
    public function login()
    {
        loadView('users/login');
    }

    /**
     * show the register page
     * 
     * @return void
     */
    public function create()
    {
        loadView('users/register');
    }

    /**
     * store user to DB
     * 
     * @return void
     */
    public function store()
    {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $password = $_POST['password'];
        $passwordConfirmation = $_POST['password_confirmation'];

        $errors = [];

        if (!Validation::email($email)) {
            $errors['email'] = 'Please enter valid email'; 
        }
        if (!Validation::string($name, 2 , 50)) {
            $errors['name'] = 'Name should be between 2 to 50 characters'; 
        }
        if (!Validation::string($password, 6,  50)) {
            $errors['password'] = 'Password should be at least 6 characters'; 
        }
        if (!Validation::match($password,$passwordConfirmation)) {
            $errors['password_confirmation'] = 'Password do not match'; 
        }
        

        if (!empty($errors)) {
            loadView('users/register',[
                'errors'=>$errors,
                'user' => [
                    'name'=>$name,
                    'email'=>$email,
                    'city'=>$city,
                    'state'=>$state,
                ]
            ]);
            exit;
        } else {
            $params = [
                'email' => $email
            ];
            $user = $this->db->query("SELECT * FROM users WHERE email = :email",$params)->fetch();

            if ($user) {
                $errors['email'] = "this email already exists";
                loadView('/users/register',[
                    'errors'=>$errors,
                ]);
                exit;
            }

            $params = [
                'name' => $name,
                'email' => $email,
                'city' => $city,
                'state' => $state,
                'password' => password_hash($password,PASSWORD_DEFAULT)
            ];

            $this->db->query("INSERT INTO users (name,email,city,state,password) VALUES (:name,:email,:city,:state,:password)" ,$params);

            //Get new user id
            $userID = $this->db->conn->lastInsertId();
            Session::set('user',[
                'id'=>$userID,
                'name'=>$name,
                'email'=>$email,
                'city'=>$city,
                'state'=>$state,
            ]);
            redirect('/');
        }

    }

    /**
     * user logout and kill the session
     * 
     * @return void
     */
    public function logout()
    {
        Session::clearAll();
        $params = session_get_cookie_params();
        setcookie('PHPSESSID','',time()-86400,$params['path'],$params['domain']);
        redirect('/');
    }

    /**
     * authenticate user with email and password
     * 
     * @return void
     */
    public function authenticate()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $errors = [];

        if (!Validation::email($email)) {
            $errors['email'] = 'Please enter valid email';
        }
        if (!Validation::string($password,6,50)) {
            $errors['email'] = 'password must be at least 6 character';
        }
        if (!empty($errors)) {
            loadView('/users/login',[
                'errors'=>$errors
            ]);
            exit;
        }

        $params = [
            'email'=>$email
        ];
        $user = $this->db->query('SELECT * FROM users WHERE email = :email',$params)->fetch();
        if (!$user) {
            $errors['email'] = 'Incorrect Credentials';
            loadView('/users/login',[
                'errors'=>$errors
            ]);
            exit;
        }
        // check if the password correct or not
        if (!password_verify($password,$user->password)) {
            $errors['password'] = 'Incorrect Credentials';
            loadView('/users/login',[
                'errors'=>$errors
            ]);
            exit;
        }
        // set the session
        Session::set('user',[
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email,
            'city'=>$user->city,
            'state'=>$user->state,
        ]);

        redirect('/');
    }
};
