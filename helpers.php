<?php

/**
 * Get the base path
 * 
 * @param string $path
 * @return string
*/
function basePath($path=''){
    return __DIR__ . '/' . $path;
}


/**
 * load view
 * 
 * @param string $name
 * @return void
*/
function loadView($name,$data=[]){
    $viewPath = basePath("App/views/{$name}.view.php");
    if (file_exists($viewPath)) {
        extract($data);
        require $viewPath;
    } else {
        echo "the view file : {$name} not exists";
    }
}


/**
 * load partial
 * 
 * @param string $name
 * @return void
*/
function loadPartial($name){
    $partailPath = basePath("App/views/partials/{$name}.php");
    if (file_exists($partailPath)) {
        require $partailPath;
    } else {
        echo "the partail file : {$name} not exists";
    }
}


/**
 * inspact value(s)
 * 
 * @param mixed $value
 * @return void
 */
function inspect($value){
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}


/**
 * inspact value(s) and die
 * 
 * @param mixed $value
 * @return void
 */
function inspectAndDie($value){
    echo '<pre>';
    die(var_dump($value));
    echo '</pre>';
}


/**
 * format salary
 * 
 * @param string $salary
 * @return string Formated salary
 */
function formatSalary($salary){
    return '$' . number_format(floatval($salary));
}