<?php

class Controller
{
    protected $config;
    protected $db;

    public $createErrors = [];

    public function __construct()
    {
        $this->config = include('config.php');
        $this->db = new DB();
    }

    public function getConfig($key)
    {
        return $this->config[$key];
    }

    public function isFilter()
    {
        return (
            (isset($_GET['action']) && $_GET['action'] == 'filter') &&
            (isset($_GET['email']) && !empty($_GET['email']))
        );
    }

    public function isCreate()
    {

        return (

        (isset($_POST['action']) && $_POST['action'] == 'create')

        );
    }

    public function getAllUsers()
    {
        // query the users table and return all rows
        return $this->db->query("SELECT * FROM users");

    }


    public function filterUsersByEmail($mail)
    {
        $quotedMail = $this->db->quote($mail);
        return $this->db->query("SELECT * FROM users WHERE email LIKE {$quotedMail}");
    }

    private function insertUser($name, $email, $password){
        $quotedName = $this->db->quote($name);
        $quotedMail = $this->db->quote($email);
        $quotedPass = $this->db->quote(md5($password));

        //echo "INSERT INTO (name, email, password) VALUES ({$quotedName}, {$quotedMail}, {$quotedPass})"; die();

        return $this->db->query("INSERT INTO users (name, email, password) VALUES ({$quotedName}, {$quotedMail}, {$quotedPass})");
    }

    public function createUser()
    {
        $email = ($_POST['email']);
        $name = $_POST['username'];
        $password = $_POST['password'];

        if (empty($name)) {
            $this->createErrors[] = "The name field is required";
        }

        //validate the email field
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->createErrors[] = "The email field is required and must be a valid email address";
        }

        //if the email field is valid check if the email already exist
        elseif($this->filterUsersByEmail($email)->rowCount()) {
            $this->createErrors[] = "User with this email already exist";
        }

        //check if the password field not empty
        if (empty($password)) {
            // if the password is empty push message to the
            // "createErrors" array message
            $this->createErrors[] = "The password field is required";
        }

        // if the "createErrors" array not empty
        if (!empty($this->createErrors)) 
            return;
        

        //if everything is ok and there are no errors
        //insert the new row to the users table
        $this->insertUser($name, $email, $password);

        //refresh the page and exit from the script
        header("Location: " . $_SERVER['REQUEST_URI']);

        exit;
    }

}