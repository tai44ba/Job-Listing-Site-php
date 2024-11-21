<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Session;
use Framework\Validation;

class ListingController
{
    protected $db;
    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    public function index()
    {
        $listings = $this->db->query("SELECT * FROM listings ORDER BY created_at DESC")->fetchAll();
        loadView('listings/index', [
            'listings' => $listings
        ]);
    }

    /**
     * show page of create job listing
     *
     * @return void
     */
    public function create()
    {
        loadView('/listings/create');
    }

    /**
     * show single job listing detail
     *
     * @return void
     */
    public function show($params)
    {
        $id = $params['id'] ?? '';
        $params = [
            'id' => $id
        ];
        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        if (!$listing) {
            ErrorController::notFound('listing is not found');
            return;
        }

        loadView('listings/show', [
            'listing' => $listing
        ]);
    }

    /**
     * store ab date to DB
     * 
     * @return void
     */
    public function store()
    {
        $aloewdField = ['title','description','salary', 'tag','company','address','city','state','phone','email','requriements','benefits'];
        $newListingData = array_intersect_key($_POST,array_flip($aloewdField));

        $newListingData['user_id'] = Session::get('user')['id'];
        $newListingData = array_map('sanitize',$newListingData);

        $requriedFields = ['title','description','salary','city','state','email'];
        $errors = [];

        foreach ($requriedFields as $field) {
            if (empty($newListingData[$field])||!Validation::string($newListingData[$field])) {
                $errors[$field] = ucfirst($field) . ' is requried';
            }
        }

        if (!empty($errors)) {
            // Reroad the view with errors
            loadView('/listings/create',[
                'errors' => $errors,
                'listing'=> $newListingData
            ]);  
        } else {
            // Submit data
            $fields = [];
            foreach ($newListingData as $field => $value) {
                $fields[] = $field;
            }
            $fields = implode(' ,',$fields);

            $values = [];
            foreach ($newListingData as $field => $value) {
                if ($value==='') {
                    $newListingData[$field] = null;
                }
                $values[] = ':' .$field;
            }
            $values = implode(' ,',$values);

            $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";
            $this->db->query($query,$newListingData);

            redirect('/listings');
        }
    }

    /**
     * delete listing
     * 
     * @param string $params
     * @return void
     */
    public function destroy($params)
    {
        $id = $params['id'];
        $params = [
            'id'=>$id
        ];
        $listing = $this->db->query("SELECT * FROM listings WHERE id = :id",$params)->fetch();

        if (!$listing) {
            ErrorController::notFound('listing not found');
            return;
        }
        // Authorization
        if (Session::get('user')['id']!==$listing->user_id) {
            $_SESSION['error_message'] = 'You are not authorized to delete this listing';
            return redirect('/listings/'.$listing->id);
        }

        $this->db->query("DELETE FROM listings WHERE id = :id",$params);

        $_SESSION['success_message'] = 'Listing deleted successfully';
        redirect('/listings');
    }

    /**
     * show single job listing detail
     *
     * @return void
     */
    public function edit($params)
    {
        $id = $params['id'] ?? '';
        $params = [
            'id' => $id
        ];
        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        if (!$listing) {
            ErrorController::notFound('listing is not found');
            return;
        }

        loadView('listings/edit', [
            'listing' => $listing
        ]);
    }

    /**
     * store ab date to DB
     * 
     * @return void
     */
    public function update($params)
    {
        $id = $params['id'] ?? '';
        $params = ['id' => $id];
        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        if (!$listing) {
            ErrorController::notFound('listing is not found');
            return;
        }

        $aloewdField = ['title','description','salary', 'tag','company','address','city','state','phone','email','requriements','benefits'];
        $updateValues = array_intersect_key($_POST,array_flip($aloewdField));

        $updateValues = array_map('sanitize',$updateValues);

        $requriedFields = ['title','description','salary','city','state','email'];
        $errors = [];

        foreach ($requriedFields as $field) {
            if (empty($updateValues[$field])||!Validation::string($updateValues[$field])) {
                $errors[$field] = ucfirst($field) . ' is requried';
            }
        }

        if (!empty($errors)) {
            // Reroad the view with errors
            loadView('/listings/edit',[
                'errors' => $errors,
                'listing'=> $listing
            ]);  
        } else {
            // update data
            $updateFields = [];
            foreach (array_keys($updateValues) as $field) {
                $updateFields[] = "{$field} = :{$field}";
            }
            $updateFields = implode(' ,',$updateFields);
            $updateQuery = "UPDATE listings SET $updateFields WHERE id = :id";
            $updateValues['id'] = $id;
            $this->db->query($updateQuery,$updateValues);

            $_SESSION['success_message'] = 'Listing updated successfully';
            redirect('/listings/'.$id);
        }
    }

}
