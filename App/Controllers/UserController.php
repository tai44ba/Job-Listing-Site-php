<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;

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

            redirect('/');
        }

    }

};
